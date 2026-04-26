# Installation

## Prerequisites

!!! info "Before you begin"
    - Moodle **4.5 or later** with administrator access
    - PHP **8.2 or later**
    - Database: MySQL, PostgreSQL, or MariaDB

---

## Method 1: ZIP Upload (Recommended)

=== "Step-by-step"

    1. Download the latest release ZIP from the [GitHub Releases page](https://github.com/kackey621/moodle-mod_hlsplayer/releases).
    2. Log in to Moodle as a **Site Administrator**.
    3. Go to **Site administration > Plugins > Install plugins**.
    4. Upload the ZIP file and click **Install plugin from the ZIP file**.
    5. Review the plugin information and click **Continue**.
    6. Follow the on-screen database installation wizard and click **Upgrade Moodle database now**.
    7. Confirm the plugin appears under **Site administration > Plugins > Activity modules > HLS Player**.

---

## Method 2: Manual Installation (Git / FTP)

```bash
# Clone the repository
git clone https://github.com/kackey621/moodle-mod_hlsplayer.git

# Copy the plugin folder into Moodle
cp -r moodle-mod_hlsplayer/hlsplayer /path/to/moodle/mod/hlsplayer
```

Then:

1. Log in to Moodle as a **Site Administrator**.
2. Go to **Site administration > Notifications**.
3. Moodle will detect the new plugin and run the database setup automatically.

---

## Post-Installation Verification

After installation, verify the plugin is working:

- Go to any course and turn editing on.
- Click **Add an activity or resource**.
- Confirm **HLS Player** appears in the activity list.

---

## Upgrading

1. Replace the contents of `mod/hlsplayer/` with the new version files.
2. Log in to Moodle as an administrator.
3. Go to **Site administration > Notifications** — Moodle will run any database upgrade steps automatically.

!!! note
    Student progress data is preserved across upgrades.

---

## Uninstalling

1. Go to **Site administration > Plugins > Activity modules > HLS Player**.
2. Click **Uninstall**.

!!! warning
    Uninstalling will permanently delete all HLS Player activities and all student progress data. Back up your database first.
