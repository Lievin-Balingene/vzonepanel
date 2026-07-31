# Install V-zone Panel (one command)

V-zone Panel installs in **one step**. You do not install Hestia separately.

## Requirements

- Fresh Debian 11/12/13 or Ubuntu 22.04/24.04 VPS
- Root access
- Public GitHub repo: `Lievin-Balingene/vzonepanel`

## Install

```bash
wget https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/vzone-install.sh
bash vzone-install.sh
```

Follow the prompts (email, hostname, admin password).  
The server may **reboot** at the end — that is normal.  
After reboot, V-zone UI + Application Manager are applied automatically.

Open: `https://YOUR_IP:8083`

## If the panel core is already installed

```bash
bash /usr/local/src/vzonepanel/install/vzone-install.sh
# or
bash /usr/local/src/vzonepanel/install/vzone-apply.sh
```

## SSH blocked after install?

On Contabo use **VNC/Console**, then:

```bash
bash /usr/local/src/vzonepanel/install/vzone-apply.sh
# or
/usr/local/hestia/bin/v-add-firewall-rule ACCEPT 0.0.0.0/0 22 TCP SSH
iptables -I INPUT -p tcp --dport 22 -j ACCEPT
systemctl restart ssh
```

## Update V-zone later

```bash
cd /usr/local/src/vzonepanel
git pull
bash install/vzone-apply.sh
```
