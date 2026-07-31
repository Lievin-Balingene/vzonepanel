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
# Keep Composer vendor trees — they are gitignored in the repo and must not be deleted
if command -v rsync > /dev/null 2>&1; then
	rsync -a --delete \
		--exclude 'css/themes/custom/' \
		--exclude 'src/vendor/' \
		--exclude 'inc/vendor/' \
		"$SRC/web/" "$PANEL_ROOT/web/"
else
	cp -a "$SRC/web/." "$PANEL_ROOT/web/"
fi

# Composer dependencies for Application Manager (Symfony Process, etc.)
# Symfony 7.4 supports PHP 8.2/8.3 (VPS). Symfony 8 requires PHP 8.4+.
mkdir -p "$PANEL_ROOT/web/src"
if [ -f "$SRC/web/src/composer.json" ]; then
	cp -f "$SRC/web/src/composer.json" "$PANEL_ROOT/web/src/composer.json"
fi
# Drop incompatible lock files (e.g. Symfony 8 locked for PHP 8.4)
if [ -f "$SRC/web/src/composer.lock" ]; then
	cp -f "$SRC/web/src/composer.lock" "$PANEL_ROOT/web/src/composer.lock"
else
	rm -f "$PANEL_ROOT/web/src/composer.lock"
fi

need_composer=0
if [ ! -f "$PANEL_ROOT/web/src/vendor/autoload.php" ]; then
	need_composer=1
fi
if [ ! -f "$PANEL_ROOT/web/src/vendor/symfony/process/Process.php" ]; then
	need_composer=1
fi
# Force refresh if an old Symfony 8 tree is present on PHP < 8.4
if [ -f "$PANEL_ROOT/web/src/vendor/symfony/process/composer.json" ]; then
	if grep -q '"version": "v8\.' "$PANEL_ROOT/web/src/vendor/symfony/process/composer.json" 2> /dev/null \
		|| grep -q '"php": ">=8.4' "$PANEL_ROOT/web/src/vendor/symfony/process/composer.json" 2> /dev/null; then
		need_composer=1
	fi
fi

if [ "$need_composer" -eq 1 ] || [ "${VZONE_FORCE_COMPOSER:-}" = "1" ]; then
	echo "[ * ] Installing PHP Composer dependencies (web/src) for Application Manager…"
	export DEBIAN_FRONTEND=noninteractive
	apt-get update -qq 2> /dev/null || true
	apt-get install -y -qq php-cli php-xml php-mbstring php-curl unzip curl ca-certificates 2> /dev/null || true

	# Prefer a working Composer PHAR (apt composer can be broken on some images)
	composer_phar="/usr/local/bin/composer.phar"
	if [ ! -s "$composer_phar" ]; then
		curl -fsSL https://getcomposer.org/download/latest-stable/composer.phar -o "$composer_phar" \
			|| wget -q -O "$composer_phar" https://getcomposer.org/download/latest-stable/composer.phar
		chmod 755 "$composer_phar"
	fi

	php_bin=""
	for candidate in /usr/bin/php8.3 /usr/bin/php8.2 /usr/bin/php "$PANEL_ROOT/php/bin/php"; do
		if [ -x "$candidate" ] && "$candidate" -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);' 2> /dev/null; then
			php_bin="$candidate"
			break
		fi
	done
	if [ -z "$php_bin" ]; then
		echo "Error: PHP 8.2+ CLI required for Composer."
		exit 1
	fi

	echo "      Using PHP: $($php_bin -v | head -n1)"
	(
		cd "$PANEL_ROOT/web/src"
		rm -rf vendor
		COMPOSER_ALLOW_SUPERUSER=1 "$php_bin" "$composer_phar" update --no-dev --optimize-autoloader --no-interaction
	)
fi

if [ ! -f "$PANEL_ROOT/web/src/vendor/symfony/process/Process.php" ]; then
	echo "Error: Symfony Process still missing after composer update."
	echo "Run manually:"
	echo "  cd $PANEL_ROOT/web/src && php /usr/local/bin/composer.phar update --no-dev -o"
	exit 1
fi

for f in v-add-web-app v-delete-web-app v-list-web-app v-restart-web-app v-get-web-app-port; do
	if [ -f "$SRC/bin/$f" ]; then
		install -m 755 "$SRC/bin/$f" "$PANEL_ROOT/bin/$f"
	fi
done

TPL_SRC="$SRC/install/deb/templates/web/nginx/php-fpm"
for dest in \
	"$PANEL_ROOT/data/templates/web/nginx" \
	"$PANEL_ROOT/data/templates/web/nginx/php-fpm" \
	"$PANEL_ROOT/install/deb/templates/web/nginx/php-fpm"; do
	mkdir -p "$dest"
	for f in django.tpl django.stpl nodejs.tpl nodejs.stpl; do
		[ -f "$TPL_SRC/$f" ] && cp -f "$TPL_SRC/$f" "$dest/$f"
	done
done

# Ensure Python/Node runtime packages for Application Manager
export DEBIAN_FRONTEND=noninteractive
apt-get install -y -qq python3 python3-venv python3-pip 2> /dev/null || true
# Node is optional but recommended for Node.js apps
apt-get install -y -qq nodejs npm 2> /dev/null || true

# Ensure no-php backend template exists when php-fpm is used
NO_PHP_SRC="$SRC/install/deb/templates/web/php-fpm/no-php.tpl"
for dest in "$PANEL_ROOT/data/templates/web/php-fpm" "$PANEL_ROOT/install/deb/templates/web/php-fpm"; do
	mkdir -p "$dest"
	[ -f "$NO_PHP_SRC" ] && cp -f "$NO_PHP_SRC" "$dest/no-php.tpl"
done

# File Manager (FileGator) session fix + V-zone branding + repair if broken
FM_SRC="$SRC/install/deb/filemanager/filegator"
FM_INSTALL_TPL="$PANEL_ROOT/install/deb/filemanager/filegator"
if [ -f "$FM_SRC/configuration.php" ]; then
	mkdir -p "$FM_INSTALL_TPL/backend/Services/Session/Adapters"
	cp -f "$FM_SRC/configuration.php" "$FM_INSTALL_TPL/configuration.php"
	if [ -f "$FM_SRC/backend/Services/Session/Adapters/SessionStorage.php" ]; then
		cp -f "$FM_SRC/backend/Services/Session/Adapters/SessionStorage.php" \
			"$FM_INSTALL_TPL/backend/Services/Session/Adapters/SessionStorage.php"
	fi
fi

# Detect broken / missing File Manager and reinstall (empty zip upgrades, missing index, etc.)
fm_broken=0
if [ ! -d "$PANEL_ROOT/web/fm" ]; then
	fm_broken=1
elif [ ! -f "$PANEL_ROOT/web/fm/index.php" ] || [ ! -f "$PANEL_ROOT/web/fm/configuration.php" ]; then
	fm_broken=1
elif [ ! -d "$PANEL_ROOT/web/fm/vendor" ] && [ ! -f "$PANEL_ROOT/web/fm/composer.json" ]; then
	fm_broken=1
fi

if [ "$fm_broken" -eq 1 ] || [ "${VZONE_REINSTALL_FM:-}" = "1" ]; then
	echo "[ * ] File Manager missing or broken — reinstalling…"
	# Ensure install templates are in place for v-add-sys-filemanager
	mkdir -p "$PANEL_ROOT/install/deb/filemanager/filegator"
	cp -a "$SRC/install/deb/filemanager/filegator/." "$PANEL_ROOT/install/deb/filemanager/filegator/"
	if [ -x "$PANEL_ROOT/bin/v-add-sys-filemanager" ]; then
		# Refresh CLI too (download validation improvements)
		[ -f "$SRC/bin/v-add-sys-filemanager" ] && install -m 755 "$SRC/bin/v-add-sys-filemanager" "$PANEL_ROOT/bin/v-add-sys-filemanager"
		"$PANEL_ROOT/bin/v-delete-sys-filemanager" quiet 2> /dev/null || true
		"$PANEL_ROOT/bin/v-add-sys-filemanager" quiet || echo "[ ! ] File Manager reinstall failed — run: v-add-sys-filemanager"
	fi
fi

# Always patch live FM configuration when present
if [ -d "$PANEL_ROOT/web/fm" ] && [ -f "$FM_SRC/configuration.php" ]; then
	echo "[ * ] Patching File Manager session handling"
	cp -f "$FM_SRC/configuration.php" "$PANEL_ROOT/web/fm/configuration.php"
	if [ -f "$FM_SRC/backend/Services/Session/Adapters/SessionStorage.php" ]; then
		mkdir -p "$PANEL_ROOT/web/fm/backend/Services/Session/Adapters"
		cp -f "$FM_SRC/backend/Services/Session/Adapters/SessionStorage.php" \
			"$PANEL_ROOT/web/fm/backend/Services/Session/Adapters/SessionStorage.php"
	fi
	if [ -f "$PANEL_ROOT/conf/hestia.conf" ]; then
		# shellcheck disable=SC1090
		source "$PANEL_ROOT/conf/hestia.conf" 2> /dev/null || true
		app_name="${APP_NAME:-V-zone Panel}"
		sed -i "s|File Manager - .*\"|File Manager - ${app_name}\"|g" "$PANEL_ROOT/web/fm/configuration.php" 2> /dev/null || true
	fi
	chown hestiaweb:hestiaweb "$PANEL_ROOT/web/fm/configuration.php" 2> /dev/null || true
fi

if [ -f "$PANEL_ROOT/conf/hestia.conf" ]; then
	if grep -q "^APP_NAME=" "$PANEL_ROOT/conf/hestia.conf"; then
		sed -i "s/^APP_NAME=.*/APP_NAME='V-zone Panel'/" "$PANEL_ROOT/conf/hestia.conf"
	else
		echo "APP_NAME='V-zone Panel'" >> "$PANEL_ROOT/conf/hestia.conf"
	fi
	# Prefer light V-zone theme unless admin already chose another custom theme
	if grep -q "^THEME=" "$PANEL_ROOT/conf/hestia.conf"; then
		sed -i "s/^THEME=.*/THEME='default'/" "$PANEL_ROOT/conf/hestia.conf"
	else
		echo "THEME='default'" >> "$PANEL_ROOT/conf/hestia.conf"
	fi
fi

# Always refresh compiled CSS/JS from source tree when present (do not rely only on timestamps)
if [ -f "$SRC/web/css/themes/default.min.css" ]; then
	mkdir -p "$PANEL_ROOT/web/css/themes" "$PANEL_ROOT/web/js/dist"
	cp -f "$SRC/web/css/themes/"*.min.css "$PANEL_ROOT/web/css/themes/" 2> /dev/null || true
	cp -f "$SRC/web/css/themes/"*.min.css.map "$PANEL_ROOT/web/css/themes/" 2> /dev/null || true
	if [ -d "$SRC/web/js/dist" ]; then
		cp -af "$SRC/web/js/dist/." "$PANEL_ROOT/web/js/dist/" 2> /dev/null || true
	fi
fi

# Ensure compiled assets exist (rebuild if missing)
need_build=0
if [ ! -s "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
	need_build=1
fi
if [ ! -s "$PANEL_ROOT/web/js/dist/main.min.js" ]; then
	need_build=1
fi

# Rebuild when source tokens are newer than deployed bundle (best-effort)
if [ -f "$SRC/web/css/src/vzone/tokens.css" ] && [ -s "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
	if [ "$SRC/web/css/src/vzone/tokens.css" -nt "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
		need_build=1
	fi
fi

# Force rebuild if CSS looks like stock Hestia (no V-zone token marker)
if [ -s "$PANEL_ROOT/web/css/themes/default.min.css" ]; then
	if ! grep -q "vz-turquoise\|--vz-" "$PANEL_ROOT/web/css/themes/default.min.css" 2> /dev/null; then
		echo "[ * ] Deployed CSS is not V-zone branded — forcing rebuild"
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
