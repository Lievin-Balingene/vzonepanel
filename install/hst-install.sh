#!/bin/bash
# Compatibility wrapper — use vzone-install.sh (single installer for V-zone Panel)

echo "========================================================"
echo " V-zone Panel"
echo "========================================================"
echo
echo "Use the unified installer (no separate Hestia step):"
echo
echo "  wget https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/vzone-install.sh"
echo "  bash vzone-install.sh"
echo
echo "Launching vzone-install.sh…"
echo

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ -f "$SCRIPT_DIR/vzone-install.sh" ]; then
	exec bash "$SCRIPT_DIR/vzone-install.sh" "$@"
fi

wget -q -O /tmp/vzone-install.sh \
	https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/vzone-install.sh
exec bash /tmp/vzone-install.sh "$@"
