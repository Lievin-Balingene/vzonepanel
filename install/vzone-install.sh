#!/bin/bash
# ======================================================== #
# V-zone Panel — one-shot installer (fully branded)
# https://github.com/Lievin-Balingene/vzonepanel
#
#   wget https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/vzone-install.sh
#   bash vzone-install.sh
# ======================================================== #

set -euo pipefail

VZ_REPO="${VZ_REPO:-Lievin-Balingene/vzonepanel}"
VZ_BRANCH="${VZ_BRANCH:-main}"
VZ_SRC="/usr/local/src/vzonepanel"
HESTIA_RELEASE="https://raw.githubusercontent.com/hestiacp/hestiacp/release"

if [ "$(id -u)" -ne 0 ]; then
	echo "Error: run as root"
	exit 1
fi

vzone_banner() {
	clear 2> /dev/null || true
	echo
	echo "  ========================================================"
	echo "   V-zone Panel"
	echo "   Modern hosting control panel"
	echo "   https://github.com/${VZ_REPO}"
	echo "  ========================================================"
	echo
}

# Rewrite user-visible Hestia branding inside a downloaded installer script
brand_installer_script() {
	local file=$1
	python3 - "$file" << 'PY'
import pathlib, re, sys
path = pathlib.Path(sys.argv[1])
text = path.read_text(encoding="utf-8", errors="replace")

# Do NOT touch package/repo paths: apt.hestiacp.com, /usr/local/hestia, package names hestia=*
replacements = [
    (r"Welcome to the Hestia Control Panel installer!", "Welcome to the V-zone Panel installer!"),
    (r"Hestia Control Panel", "V-zone Panel"),
    (r"www\.hestiacp\.com", "github.com/Lievin-Balingene/vzonepanel"),
    (r"https://docs\.hestiacp\.com/", "https://github.com/Lievin-Balingene/vzonepanel"),
    (r"https://forum\.hestiacp\.com/", "https://github.com/Lievin-Balingene/vzonepanel"),
    (r"https://www\.github\.com/hestiacp/hestiacp", "https://github.com/Lievin-Balingene/vzonepanel"),
    (r"https://github\.com/hestiacp/hestiacp", "https://github.com/Lievin-Balingene/vzonepanel"),
    (r"https://www\.hestiacp\.com/donate", "https://github.com/Lievin-Balingene/vzonepanel"),
    (r"Thank you for downloading V-zone Panel!", "Thank you for choosing V-zone Panel!"),
    (r"Help support the V-zone Panel project by donating via PayPal:\n", ""),
    (r"The Hestia Control Panel development team", "The V-zone Panel team"),
    (r"The V-zone Panel development team", "The V-zone Panel team"),
    (r"Made with love & pride by the open-source community around the world\.", "Built for modern web hosting businesses."),
    (r"Welcome to V-zone Panel!", "Welcome to V-zone Panel!"),
]

for a, b in replacements:
    text = re.sub(a, b, text)

# Replace ASCII Hestia logo block inside install_welcome_message()
new_welcome = r'''install_welcome_message() {
	DISPLAY_VER=$(echo $HESTIA_INSTALL_VER | sed "s|~alpha||g" | sed "s|~beta||g")
	echo
	echo "  ========================================================"
	echo "   V-zone Panel"
	echo "   Modern hosting control panel"
	echo "   Version ${DISPLAY_VER}"
	echo "   github.com/Lievin-Balingene/vzonepanel"
	echo "  ========================================================"
	echo
	if [[ "$HESTIA_INSTALL_VER" =~ "beta" ]]; then
		echo "                         BETA RELEASE"
		echo
	fi
	if [[ "$HESTIA_INSTALL_VER" =~ "alpha" ]]; then
		echo "                     DEVELOPMENT SNAPSHOT"
		echo
	fi
	echo "Thank you for choosing V-zone Panel! In a few moments,"
	echo "we will begin installing the following components on your server:"
	echo
}'''

text2, n = re.subn(
    r"install_welcome_message\(\) \{.*?\n\}",
    new_welcome,
    text,
    count=1,
    flags=re.S,
)
if n == 0:
    # Fallback: leave function, branding replacements already applied
    pass
else:
    text = text2

path.write_text(text, encoding="utf-8")
print(f"[ * ] Branded installer script: {path}")
PY
}

vzone_banner

# --- Already installed? Apply V-zone only ---
if [ -d /usr/local/hestia/web ]; then
	echo "[ * ] Panel core already present — applying V-zone branding & features."
	export DEBIAN_FRONTEND=noninteractive
	apt-get update -qq
	apt-get install -y -qq git rsync ca-certificates nodejs npm 2> /dev/null || apt-get install -y -qq git rsync ca-certificates
	if [ ! -d "$VZ_SRC/.git" ]; then
		rm -rf "$VZ_SRC"
		git clone --depth 1 --branch "$VZ_BRANCH" "https://github.com/${VZ_REPO}.git" "$VZ_SRC"
	else
		git -C "$VZ_SRC" pull --ff-only origin "$VZ_BRANCH" 2> /dev/null || true
	fi
	bash "$VZ_SRC/install/vzone-apply.sh" "$VZ_SRC"
	exit 0
fi

export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq wget curl git ca-certificates rsync python3

# --- Sources ---
if [ -d "$VZ_SRC/.git" ]; then
	git -C "$VZ_SRC" pull --ff-only origin "$VZ_BRANCH" 2> /dev/null || true
elif [ -f "$(dirname "$0")/vzone-apply.sh" ] && [ -d "$(cd "$(dirname "$0")/.." && pwd)/web" ]; then
	SRC_NOW="$(cd "$(dirname "$0")/.." && pwd)"
	if [ "$SRC_NOW" != "$VZ_SRC" ]; then
		mkdir -p /usr/local/src
		rm -rf "$VZ_SRC"
		cp -a "$SRC_NOW" "$VZ_SRC"
	fi
else
	echo "[ * ] Cloning V-zone Panel → $VZ_SRC"
	rm -rf "$VZ_SRC"
	mkdir -p /usr/local/src
	git clone --depth 1 --branch "$VZ_BRANCH" "https://github.com/${VZ_REPO}.git" "$VZ_SRC"
fi

# --- Clear leftover admin ---
if getent passwd admin > /dev/null 2>&1 || getent group admin > /dev/null 2>&1; then
	echo "[ * ] Removing leftover 'admin' account/group"
	userdel -r admin 2> /dev/null || userdel admin 2> /dev/null || true
	groupdel admin 2> /dev/null || true
fi

# --- Detect OS for release installer ---
if [ ! -e /etc/os-release ]; then
	echo "Error: unsupported OS"
	exit 1
fi
# shellcheck disable=SC1091
. /etc/os-release
case "$ID" in
	ubuntu) os_type=ubuntu ;;
	debian) os_type=debian ;;
	*)
		echo "Error: only Debian/Ubuntu are supported (got $ID)"
		exit 1
		;;
esac

# --- Finalize after reboot (installer often reboots) ---
cat > /usr/local/sbin/vzone-finalize.sh << EOF
#!/bin/bash
set -euo pipefail
exec >> /var/log/vzone-finalize.log 2>&1
echo "==== \$(date -Is) V-zone finalize start ===="
for i in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15; do
	[ -d /usr/local/hestia/web ] && break
	sleep 2
done
apt-get install -y -qq nodejs npm rsync git 2>/dev/null || true
bash /usr/local/src/vzonepanel/install/vzone-apply.sh /usr/local/src/vzonepanel
systemctl disable vzone-finalize.service 2>/dev/null || true
rm -f /etc/systemd/system/vzone-finalize.service
systemctl daemon-reload 2>/dev/null || true
echo "==== \$(date -Is) V-zone finalize done ===="
EOF
chmod 755 /usr/local/sbin/vzone-finalize.sh

cat > /etc/systemd/system/vzone-finalize.service << 'EOF'
[Unit]
Description=Finalize V-zone Panel branding after install
After=network-online.target
Wants=network-online.target

[Service]
Type=oneshot
ExecStart=/usr/local/sbin/vzone-finalize.sh
RemainAfterExit=yes

[Install]
WantedBy=multi-user.target
EOF
systemctl daemon-reload
systemctl enable vzone-finalize.service

echo "[ * ] Downloading panel core installer (stable packages)…"
cd /root
wget -q -O "/root/hst-install-${os_type}.sh" "${HESTIA_RELEASE}/install/hst-install-${os_type}.sh"
chmod +x "/root/hst-install-${os_type}.sh"

echo "[ * ] Applying V-zone branding to installer messages / MOTD banners…"
brand_installer_script "/root/hst-install-${os_type}.sh"

echo "[ * ] Installing V-zone Panel (15–30 minutes)…"
echo "      After reboot, V-zone UI applies automatically."
echo

bash "/root/hst-install-${os_type}.sh" --force "$@"

if [ -d /usr/local/hestia/web ]; then
	echo "[ * ] Applying V-zone now (no reboot path)"
	bash /usr/local/sbin/vzone-finalize.sh || bash "$VZ_SRC/install/vzone-apply.sh" "$VZ_SRC"
fi

echo
echo "[ OK ] V-zone Panel installation complete."
echo "      https://$(hostname -I 2> /dev/null | awk '{print $1}'):8083"
exit 0
