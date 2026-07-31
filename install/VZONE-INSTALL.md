# Install V-zone Panel (one command, full branding)

## Install

```bash
wget https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/vzone-install.sh
bash vzone-install.sh
```

You will only see **V-zone Panel** banners (no Hestia logo / MOTD).  
After reboot, UI + SSH MOTD are applied automatically.

Panel: `https://YOUR_IP:8083`

## Re-apply branding on an existing server

```bash
cd /usr/local/src/vzonepanel && git pull
bash install/vzone-apply.sh
```

This updates the web UI, Application Manager, and `/etc/motd` / SSH login banner.
