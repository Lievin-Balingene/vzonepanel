#!/bin/bash
# info: apply V-zone Panel files onto the installed panel core
# options: [SOURCE_DIR]
#
# Used by vzone-install.sh (automatic). Can also be re-run to update the UI.

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

if command -v rsync > /dev/null 2>&1; then
	rsync -a --exclude 'css/themes/' "$SRC/web/" "$PANEL_ROOT/web/"
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

if command -v npm > /dev/null 2>&1 && [ -f "$SRC/package.json" ]; then
	echo "[ * ] Building CSS/JS"
	(
		cd "$SRC"
		npm ci --omit=dev 2> /dev/null || npm install --omit=dev
		npm run build
	)
	mkdir -p "$PANEL_ROOT/web/css/themes" "$PANEL_ROOT/web/js/dist"
	cp -f "$SRC/web/css/themes/"*.min.css* "$PANEL_ROOT/web/css/themes/" 2> /dev/null || true
	cp -af "$SRC/web/js/dist/." "$PANEL_ROOT/web/js/dist/" 2> /dev/null || true
else
	apt-get install -y -qq nodejs npm 2> /dev/null || true
	if command -v npm > /dev/null 2>&1; then
		(
			cd "$SRC"
			npm install --omit=dev
			npm run build
		)
		mkdir -p "$PANEL_ROOT/web/css/themes" "$PANEL_ROOT/web/js/dist"
		cp -f "$SRC/web/css/themes/"*.min.css* "$PANEL_ROOT/web/css/themes/" 2> /dev/null || true
		cp -af "$SRC/web/js/dist/." "$PANEL_ROOT/web/js/dist/" 2> /dev/null || true
	else
		echo "[ ! ] npm unavailable — UI CSS may need a manual build later"
	fi
fi

chown -R hestiaweb:hestiaweb "$PANEL_ROOT/web" 2> /dev/null || true
chmod 755 "$PANEL_ROOT/bin"/v-*-web-app "$PANEL_ROOT/bin/v-get-web-app-port" 2> /dev/null || true

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
# Prefer our banner only (disable other update-motd scripts)
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

# SSH: avoid lockout after firewall activation
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

# Panel notification for first admin user
if [ -x "$PANEL_ROOT/bin/v-add-user-notification" ] && [ -d "$PANEL_ROOT/data/users" ]; then
	for uconf in "$PANEL_ROOT"/data/users/*/user.conf; do
		[ -f "$uconf" ] || continue
		u="$(basename "$(dirname "$uconf")")"
		role="$(grep "^ROLE=" "$uconf" 2> /dev/null | cut -d"'" -f2)"
		if [ "$role" = "admin" ] || [ "$u" = "admin" ]; then
			"$PANEL_ROOT/bin/v-add-user-notification" "$u" "Welcome to V-zone Panel!" \
				'<p>Your server is ready. Add <a href="/add/web/">web domains</a> or open <a href="/list/apps/">Applications</a> to deploy Python &amp; Node.js apps.</p>' \
				2> /dev/null || true
			break
		fi
	done
fi

echo "[ OK ] V-zone Panel is ready."
echo "      URL: https://${IP_GUESS}:8083"
echo "      Apps: /list/apps/"
exit 0
