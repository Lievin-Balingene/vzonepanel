#!/bin/bash
# ======================================================== #
# V-zone Panel — one-shot installer
# https://github.com/Lievin-Balingene/vzonepanel
#
# Usage (as root):
#   wget https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/vzone-install.sh
#   bash vzone-install.sh
#
# Or from a clone:
#   bash install/vzone-install.sh
#
# You do NOT need to install Hestia separately.
# ======================================================== #

set -euo pipefail

VZ_REPO="${VZ_REPO:-Lievin-Balingene/vzonepanel}"
VZ_BRANCH="${VZ_BRANCH:-main}"
VZ_SRC="/usr/local/src/vzonepanel"
# Official package installer (stable stack). V-zone UI is applied on top automatically.
HESTIA_WRAPPER_URL="https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh"

if [ "$(id -u)" -ne 0 ]; then
	echo "Error: run as root"
	exit 1
fi

echo
echo "========================================================"
echo " V-zone Panel installer"
echo "========================================================"
echo

# --- Already installed? Just (re)apply V-zone + fix SSH ---
if [ -d /usr/local/hestia/web ]; then
	echo "[ * ] Panel core already present — applying V-zone customization only."
	if [ ! -d "$VZ_SRC/.git" ]; then
		apt-get update -qq
		apt-get install -y -qq git rsync ca-certificates
		git clone --depth 1 --branch "$VZ_BRANCH" "https://github.com/${VZ_REPO}.git" "$VZ_SRC"
	else
		git -C "$VZ_SRC" fetch --depth 1 origin "$VZ_BRANCH" 2> /dev/null || true
		git -C "$VZ_SRC" checkout "$VZ_BRANCH" 2> /dev/null || true
		git -C "$VZ_SRC" pull --ff-only origin "$VZ_BRANCH" 2> /dev/null || true
	fi
	bash "$VZ_SRC/install/vzone-apply.sh" "$VZ_SRC"
	exit 0
fi

# --- Dependencies ---
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq wget curl git ca-certificates rsync

# --- Fetch / refresh V-zone sources ---
if [ -d "$VZ_SRC/.git" ]; then
	echo "[ * ] Updating V-zone sources in $VZ_SRC"
	git -C "$VZ_SRC" fetch --depth 1 origin "$VZ_BRANCH" 2> /dev/null || true
	git -C "$VZ_SRC" checkout "$VZ_BRANCH" 2> /dev/null || true
	git -C "$VZ_SRC" pull --ff-only origin "$VZ_BRANCH" 2> /dev/null || true
elif [ -f "$(dirname "$0")/vzone-apply.sh" ] && [ -d "$(cd "$(dirname "$0")/.." && pwd)/web" ]; then
	# Running from an existing clone — use it
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

# --- Clear leftover admin user/group that blocks install ---
if getent passwd admin > /dev/null 2>&1 || getent group admin > /dev/null 2>&1; then
	echo "[ * ] Removing leftover 'admin' account/group (required for a clean install)"
	userdel -r admin 2> /dev/null || userdel admin 2> /dev/null || true
	groupdel admin 2> /dev/null || true
fi

# --- Schedule V-zone finalize after reboot (Hestia installer reboots when interactive) ---
cat > /usr/local/sbin/vzone-finalize.sh << EOF
#!/bin/bash
set -euo pipefail
exec >> /var/log/vzone-finalize.log 2>&1
echo "==== \$(date -Is) V-zone finalize start ===="
for i in 1 2 3 4 5 6 7 8 9 10; do
	[ -d /usr/local/hestia/web ] && break
	sleep 3
done
if [ ! -d /usr/local/hestia/web ]; then
	echo "Panel core missing — abort finalize"
	exit 1
fi
apt-get install -y -qq nodejs npm rsync git 2>/dev/null || true
bash /usr/local/src/vzonepanel/install/vzone-apply.sh /usr/local/src/vzonepanel
if [ -x /usr/local/hestia/bin/v-update-web-templates ]; then
	/usr/local/hestia/bin/v-update-web-templates || true
fi
# Keep SSH reachable (common post-install lockout)
if [ -x /usr/local/hestia/bin/v-add-firewall-rule ]; then
	/usr/local/hestia/bin/v-add-firewall-rule ACCEPT 0.0.0.0/0 22 TCP SSH 2>/dev/null || true
fi
iptables -C INPUT -p tcp --dport 22 -j ACCEPT 2>/dev/null || iptables -I INPUT -p tcp --dport 22 -j ACCEPT
systemctl disable vzone-finalize.service 2>/dev/null || true
rm -f /etc/systemd/system/vzone-finalize.service
systemctl daemon-reload 2>/dev/null || true
echo "==== \$(date -Is) V-zone finalize done ===="
EOF
chmod 755 /usr/local/sbin/vzone-finalize.sh

cat > /etc/systemd/system/vzone-finalize.service << 'EOF'
[Unit]
Description=Finalize V-zone Panel after first boot
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

echo "[ * ] Installing panel core (this can take 15–30 minutes)…"
echo "      After reboot, V-zone branding & Application Manager apply automatically."
echo

cd /root
wget -q -O /root/hst-install.sh "$HESTIA_WRAPPER_URL"
# Always pass --force so leftover admin/group never blocks V-zone installs
bash /root/hst-install.sh --force "$@"

# If installer returned without reboot (non-interactive), finalize now
if [ -d /usr/local/hestia/web ]; then
	echo "[ * ] Core install finished without reboot — applying V-zone now"
	bash /usr/local/sbin/vzone-finalize.sh || bash "$VZ_SRC/install/vzone-apply.sh" "$VZ_SRC"
fi

echo
echo "[ OK ] V-zone Panel installation flow complete."
echo "      Panel URL: https://$(hostname -I 2> /dev/null | awk '{print $1}'):8083"
exit 0
