# Install V-zone Panel (propre, from scratch)

**Prérequis :** Debian 11/12 ou Ubuntu 22.04/24.04 **fraîchement réinstallé**, en root.

## 1) Installation (une seule commande)

```bash
wget -O /root/vzone-install.sh https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/vzone-install.sh
chmod +x /root/vzone-install.sh
bash /root/vzone-install.sh
```

- Durée : 15–30 minutes
- Le serveur **redémarre** à la fin
- Après reboot, le branding V-zone (UI + MOTD) s’applique automatiquement (~2–5 min)

Pendant l’install, choisis :

- email admin
- hostname (ex. `vmi….vzonecloud.co.uk`)
- mot de passe admin

## 2) Après le reboot

Attends 2–3 minutes, puis reconnecte-toi en SSH et vérifie :

```bash
grep APP_NAME /usr/local/hestia/conf/hestia.conf
# attendu : APP_NAME='V-zone Panel'

tail -n 40 /var/log/vzone-finalize.log
```

Si `APP_NAME` n’est pas V-zone, force l’overlay :

```bash
cd /usr/local/src/vzonepanel
git fetch --depth 1 origin main && git reset --hard origin/main
bash install/vzone-apply.sh
```

## 3) Connexion

- URL : `https://IP_DU_SERVEUR:8083`
- Navigateur : **Ctrl+F5**
- User / mot de passe : ceux choisis à l’install

Ouvrir SSH si besoin (VNC Contabo) :

```bash
/usr/local/hestia/bin/v-add-firewall-rule ACCEPT 0.0.0.0/0 22 TCP SSH
```

## Mise à jour ultérieure

```bash
cd /usr/local/src/vzonepanel && git pull
bash install/vzone-apply.sh
```
