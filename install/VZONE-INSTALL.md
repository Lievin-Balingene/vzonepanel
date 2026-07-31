# Install V-zone Panel on a VPS

## Why `wget …/hst-install.sh` returned 404

Your GitHub repository is **private**.  
`raw.githubusercontent.com` cannot serve private files without a token → **404**.

**Fix:** GitHub → repo **vzonepanel** → **Settings** → **General** → **Danger Zone** → **Change repository visibility** → **Public**.

---

## Recommended install (Debian / Ubuntu)

### 1. Install the Hestia stack (packages)

```bash
wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh
bash hst-install.sh
```

Use a **fresh** VPS. Follow the interactive prompts (email, hostname, password).

### 2. Apply V-zone Panel (UI + Application Manager)

```bash
apt-get update && apt-get install -y git nodejs npm rsync
cd /usr/local/src
git clone https://github.com/Lievin-Balingene/vzonepanel.git
cd vzonepanel
bash install/vzone-apply.sh
```

### 3. Sync web templates (optional but recommended)

```bash
v-update-web-templates
```

Open `https://YOUR_SERVER_IP:8083` — you should see **V-zone Panel** and **Applications**.

---

## Alternative (repo public): download wrapper from your fork

```bash
wget https://raw.githubusercontent.com/Lievin-Balingene/vzonepanel/main/install/hst-install.sh
bash hst-install.sh
# then still run vzone-apply.sh as above
```

> Note: the OS installer still installs Hestia packages from `apt.hestiacp.com`.  
> Your V-zone UI/apps are applied with `install/vzone-apply.sh`.
