#!/bin/bash
# info: apply V-zone Panel files onto the installed panel core
# options: [SOURCE_DIR]
#
# Copies UI + compiled CSS/JS so the control panel styles render correctly.

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
	echo "Error: run as root"
	exit 1
fi

PANEL_ROOT="${HESTIA:-/usr/local/hestia}"
SRC="${1:-}"

if [ -z "$SRC" ]; then
	SCRIPT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
	SRC="$SCRIPT_DIR"
fi

if [ ! -d "$PANEL_ROOT/web" ]; then
	echo "Error: panel core not found at $PANEL_ROOT"
	echo "Run: bash install/vzone-install.sh"
	exit 1
fi

if [ ! -d "$SRC/web" ] || [ ! -d "$SRC/bin" ]; then
	echo "Error: incomplete V-zone source tree: $SRC"
	exit 1
fi

echo "[ * ] Applying V-zone Panel from $SRC → $PANEL_ROOT"

# Full web tree INCLUDING compiled css/themes + js/dist
if command -v rsync > /dev/null 2>&1; then
	rsync -a --delete \
		--exclude 'css/themes/custom/' \
		"$SRC/web/" "$PANEL_ROOT/web/"
else
	cp -a "$SRC/web/." "$PANEL_ROOT/web/"
fi

for f in v-add-web-app v-delete-web-app v-list-web-app v-restart-web-app v-get-web-app-port; do
	if [ -f "$SRC/bin/$f" ]; then
		install -m 755 "$SRC/bin/$f" "$PANEL_ROOT/bin/$f"
	fi
done

TPL_SRC="$SRC/install/deb/templates/web/nginx/php-fpm"
for dest in "$PANEL_ROOT/data/templates/web/nginx" "$PANEL_ROOT/install/deb/templates/web/nginx/php-fpm"; do
	mkdir -p "$dest"
	for f in django.tpl django.stpl nodejs.tpl nodejs.stpl; do
		[ -f "$TPL_SRC/$f" ] && cp -f "$TPL_SRC/$f" "$dest/$f"
	done
done

if [ -f "$PANEL_ROOT/conf/hestia.conf" ]; then
	if grep -q "^APP_NAME=" "$PANEL_ROOT/conf/hestia.conf"; then
		sed -i "s/^APP_NAME=.*/APP_NAME='V-zone Panel'/" "$PANEL_ROOT/conf/hestia.conf"
	else
		echo "APP_NAME='V-zone Panel'" >> "$PANEL_ROOT/conf/hestia.conf"
	fi
fi

# Ensure compiled assets exist (rebuild if missing)
need_build=0
if [ ! -f "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
	need_build=1
fi
if [ ! -f "$PANEL_ROOT/web/js/dist/main.min.js" ]; then
	need_build=1
fi

# Rebuild when source is newer than deployed bundle (best-effort)
if [ -f "$SRC/web/css/src/vzone/tokens.css" ] && [ -f "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
	if [ "$SRC/web/css/src/vzone/tokens.css" -nt "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
		need_build=1
	fi
fi

if [ "$need_build" -eq 1 ] || [ "${VZONE_FORCE_BUILD:-}" = "1" ]; then
	echo "[ * ] Building CSS/JS assets (required for styled UI)"
	export DEBIAN_FRONTEND=noninteractive
	if ! command -v npm > /dev/null 2>&1; then
		apt-get update -qq
		apt-get install -y -qq nodejs npm
	fi
	if ! command -v npm > /dev/null 2>&1; then
		echo "Error: npm is required to build V-zone CSS. Install nodejs/npm and re-run."
		exit 1
	fi
	(
		cd "$SRC"
		# Build tools (esbuild, lightningcss) are in devDependencies — do NOT omit them
		npm install
		npm run build
	)
	mkdir -p "$PANEL_ROOT/web/css/themes" "$PANEL_ROOT/web/js/dist"
	cp -f "$SRC/web/css/themes/"*.min.css "$PANEL_ROOT/web/css/themes/"
	cp -f "$SRC/web/css/themes/"*.min.css.map "$PANEL_ROOT/web/css/themes/" 2> /dev/null || true
	cp -af "$SRC/web/js/dist/." "$PANEL_ROOT/web/js/dist/"
fi

if [ ! -s "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
	echo "Error: default.min.css missing after apply — UI will look unstyled."
	exit 1
fi

# Permissions for hestia-nginx / hestiaweb
chown -R hestiaweb:hestiaweb "$PANEL_ROOT/web" 2> /dev/null || true
find "$PANEL_ROOT/web/css" "$PANEL_ROOT/web/js" -type f -exec chmod 644 {} \; 2> /dev/null || true
chmod 755 "$PANEL_ROOT/bin"/v-*-web-app "$PANEL_ROOT/bin/v-get-web-app-port" 2> /dev/null || true

# Bust browser cache for assets after each apply
if [ -f "$PANEL_ROOT/conf/hestia.conf" ]; then
	ts="$(date +%s)"
	if grep -q "^THEME_BUILD=" "$PANEL_ROOT/conf/hestia.conf"; then
		sed -i "s/^THEME_BUILD=.*/THEME_BUILD='$ts'/" "$PANEL_ROOT/conf/hestia.conf"
	else
		echo "THEME_BUILD='$ts'" >> "$PANEL_ROOT/conf/hestia.conf"
	fi
fi

# --- SSH MOTD / login banner: 100% V-zone ---
IP_GUESS="$(hostname -I 2> /dev/null | awk '{print $1}')"
cat > /etc/motd << EOF

  ========================================================
   V-zone Panel
   Modern hosting control panel
   https://${IP_GUESS}:8083
  ========================================================

EOF

mkdir -p /etc/update-motd.d /etc/motd.d
chmod a-x /etc/update-motd.d/* 2> /dev/null || true
cat > /etc/update-motd.d/00-vzone << EOF
#!/bin/sh
printf '\\n'
printf '  ========================================================\\n'
printf '   V-zone Panel\\n'
printf '   Modern hosting control panel\\n'
printf '   https://%s:8083\\n' "${IP_GUESS}"
printf '  ========================================================\\n'
printf '\\n'
EOF
chmod 755 /etc/update-motd.d/00-vzone
cat > /etc/motd.d/vzone << EOF
V-zone Panel — https://${IP_GUESS}:8083
EOF

if [ -x "$PANEL_ROOT/bin/v-add-firewall-rule" ]; then
	"$PANEL_ROOT/bin/v-add-firewall-rule" ACCEPT 0.0.0.0/0 22 TCP SSH 2> /dev/null || true
fi
iptables -C INPUT -p tcp --dport 22 -j ACCEPT 2> /dev/null || iptables -I INPUT -p tcp --dport 22 -j ACCEPT || true

if [ -x "$PANEL_ROOT/bin/v-update-web-templates" ]; then
	"$PANEL_ROOT/bin/v-update-web-templates" 2> /dev/null || true
fi

if [ -x "$PANEL_ROOT/bin/v-restart-service" ]; then
	"$PANEL_ROOT/bin/v-restart-service" hestia 2> /dev/null || true
fi

echo "[ OK ] V-zone Panel is ready — styles deployed."
echo "      CSS: $PANEL_ROOT/web/css/themes/default.min.css ($(wc -c < "$PANEL_ROOT/web/css/themes/default.min.css") bytes)"
echo "      URL: https://${IP_GUESS}:8083"
echo "      Tip: hard-refresh the browser (Ctrl+F5) if an old CSS is cached."
exit 0
