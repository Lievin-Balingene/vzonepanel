#!/bin/bash
# info: apply V-zone Panel customizations onto an installed Hestia tree
# options: [SOURCE_DIR]
#
# Run AFTER a normal Hestia install. Copies UI, Application Manager CLI,
# and Nginx app templates from this repository into /usr/local/hestia.

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
	echo "Error: run as root"
	exit 1
fi

HESTIA="${HESTIA:-/usr/local/hestia}"
SRC="${1:-}"

if [ -z "$SRC" ]; then
	SCRIPT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
	SRC="$SCRIPT_DIR"
fi

if [ ! -d "$HESTIA/web" ]; then
	echo "Error: Hestia not found at $HESTIA — install Hestia first."
	exit 1
fi

if [ ! -d "$SRC/web" ] || [ ! -d "$SRC/bin" ]; then
	echo "Error: source tree incomplete: $SRC"
	exit 1
fi

echo "[ * ] Applying V-zone Panel from $SRC → $HESTIA"

# Web UI + installers
rsync -a --delete --exclude 'css/themes/' "$SRC/web/" "$HESTIA/web/" 2> /dev/null || cp -a "$SRC/web/." "$HESTIA/web/"

# Application Manager CLI
for f in v-add-web-app v-delete-web-app v-list-web-app v-restart-web-app v-get-web-app-port; do
	if [ -f "$SRC/bin/$f" ]; then
		install -m 755 "$SRC/bin/$f" "$HESTIA/bin/$f"
	fi
done

# Nginx proxy templates for Django / Node.js
TPL_SRC="$SRC/install/deb/templates/web/nginx/php-fpm"
TPL_DST="$HESTIA/data/templates/web/nginx"
mkdir -p "$TPL_DST"
for f in django.tpl django.stpl nodejs.tpl nodejs.stpl; do
	if [ -f "$TPL_SRC/$f" ]; then
		cp -f "$TPL_SRC/$f" "$TPL_DST/$f"
	fi
done
# Also keep under install tree used by some rebuilds
mkdir -p "$HESTIA/install/deb/templates/web/nginx/php-fpm"
for f in django.tpl django.stpl nodejs.tpl nodejs.stpl; do
	if [ -f "$TPL_SRC/$f" ]; then
		cp -f "$TPL_SRC/$f" "$HESTIA/install/deb/templates/web/nginx/php-fpm/$f"
	fi
done

# Branding default name (if conf exists)
if [ -f "$HESTIA/conf/hestia.conf" ]; then
	if grep -q "^APP_NAME=" "$HESTIA/conf/hestia.conf"; then
		sed -i "s/^APP_NAME=.*/APP_NAME='V-zone Panel'/" "$HESTIA/conf/hestia.conf"
	else
		echo "APP_NAME='V-zone Panel'" >> "$HESTIA/conf/hestia.conf"
	fi
fi

# Build frontend assets when Node is available
if command -v npm > /dev/null 2>&1 && [ -f "$SRC/package.json" ]; then
	echo "[ * ] Building CSS/JS (npm run build)"
	(
		cd "$SRC"
		npm ci --omit=dev 2> /dev/null || npm install --omit=dev
		npm run build
	)
	mkdir -p "$HESTIA/web/css/themes" "$HESTIA/web/js/dist"
	cp -f "$SRC/web/css/themes/"*.min.css* "$HESTIA/web/css/themes/" 2> /dev/null || true
	cp -af "$SRC/web/js/dist/." "$HESTIA/web/js/dist/" 2> /dev/null || true
else
	echo "[ ! ] npm not found — copy prebuilt themes from the build machine if the UI looks unstyled."
fi

# Permissions
chown -R hestiaweb:hestiaweb "$HESTIA/web" 2> /dev/null || true
find "$HESTIA/bin" -name 'v-*-web-app' -exec chmod 755 {} \;
chmod 755 "$HESTIA/bin/v-get-web-app-port" 2> /dev/null || true

if [ -x "$HESTIA/bin/v-restart-service" ]; then
	"$HESTIA/bin/v-restart-service" hestia 2> /dev/null || true
fi

echo "[ OK ] V-zone Panel applied."
echo "      Open the panel and check /list/apps/ (Application Manager)."
exit 0
