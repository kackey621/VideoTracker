# Architecture & Code Reference

## Plugin Metadata

| Property | Value |
|----------|-------|
| Component | `mod_hlsplayer` |
| Version | `2025042305` (v0.1.0) |
| Maturity | ALPHA |
| Requires | Moodle 4.5+ (`2024100700`) |
| PHP | 8.2+ |

---

## Directory Structure

```
hlsplayer/
├── version.php                           # Plugin metadata
├── lib.php                               # Core functions
├── mod_form.php                          # Activity settings form
├── view.php                              # Student-facing player page
├── report.php                            # Teacher progress report
├── index.php                             # Course activity index
├── LICENSE                               # GPL v3
├── amd/
│   ├── src/player.js                     # AMD source: Video.js init & tracking
│   └── build/player.min.js              # Grunt-compiled minified output
├── classes/
│   ├── external.php                      # AJAX API: submit_progress
│   ├── completion/custom_completion.php  # completionminview rule
│   ├── event/course_module_viewed.php
│   ├── event/course_module_instance_list_viewed.php
│   └── privacy/provider.php             # GDPR Privacy API
├── db/
│   ├── install.xml                       # XMLDB schema
│   ├── access.php                        # Capability definitions
│   ├── services.php                      # Web service registration
│   └── upgrade.php                       # DB upgrade steps
├── backup/moodle2/                       # Backup/restore classes
├── templates/player.mustache            # Video.js player HTML template
├── lang/
│   ├── en/hlsplayer.php                 # English strings
│   └── ja/hlsplayer.php                 # Japanese strings
├── pix/
│   ├── icon.png
│   └── monologo.png
└── tests/
    ├── external_test.php
    ├── lib_test.php
    └── generator/lib.php
```

---

## File Reference

| File | Purpose |
|------|---------|
| `version.php` | Declares component, version, Moodle requirement, maturity |
| `lib.php` | `hlsplayer_add_instance`, `hlsplayer_update_instance`, `hlsplayer_delete_instance`, `hlsplayer_supports`, grade functions, file serving (`hlsplayer_pluginfile`), settings nav hook |
| `mod_form.php` | Activity form class; defines all fields including the `completionminview` completion rule UI |
| `view.php` | Resolves stream URL (URL or pluginfile), loads Video.js from CDN, renders `player.mustache` |
| `report.php` | Requires `mod/hlsplayer:viewreport`; joins enrolled users with progress table; renders HTML table |
| `db/install.xml` | XMLDB: `hlsplayer` table (settings) + `hlsplayer_progress` table (per-user progress) |
| `db/services.php` | Registers `mod_hlsplayer_submit_progress` as an AJAX-enabled web service |
| `db/access.php` | Three capabilities: `addinstance`, `view`, `viewreport` |
| `classes/external.php` | `submit_progress($hlsplayerid, $progress, $percentage, $lastposition)`: validates context, upserts progress (never decreases), triggers completion and grading |
| `classes/completion/custom_completion.php` | `get_state()` returns `COMPLETION_COMPLETE` if `percentage >= completionminview` |
| `classes/privacy/provider.php` | Metadata declaration, context lookup, export, and two deletion paths |
| `amd/src/player.js` | AMD module: initialises Video.js, manages `maxViewedTime`, seek restriction, 10-second save interval |
| `templates/player.mustache` | HTML for the video container; data attributes carry PHP values into JavaScript |

---

## Database Schema

### `hlsplayer` (activity settings)

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `course` | int | Course ID (indexed) |
| `name` | varchar(255) | Activity name |
| `intro` | text | Description |
| `introformat` | int | Description format |
| `sourcetype` | varchar(10) | `'url'` or `'file'` |
| `videourl` | text | External `.m3u8` URL |
| `allowspeeds` | int | 0 or 1 |
| `allowseeking` | int | 0 or 1 |
| `completionminview` | int | 0–100% threshold |
| `grade` | int | Maximum grade value |
| `timecreated` | int | Unix timestamp |
| `timemodified` | int | Unix timestamp |

### `hlsplayer_progress` (per-user progress)

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | Primary key |
| `hlsplayerid` | int | FK → `hlsplayer.id` |
| `userid` | int | FK → `user.id` (indexed) |
| `progress` | int | Max seconds watched |
| `percentage` | int | Max percentage watched (0–100) |
| `lastposition` | int | Resume position in seconds |
| `timemodified` | int | Unix timestamp |

Unique index on `(hlsplayerid, userid)` — one record per student per activity.

---

## Web Service API

### `mod_hlsplayer_submit_progress`

| Property | Value |
|----------|-------|
| Type | write, ajax |
| Capability required | `mod/hlsplayer:view` |
| Class | `mod_hlsplayer\external` |

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `hlsplayerid` | int | Activity instance ID |
| `progress` | int | Max seconds watched |
| `percentage` | int | Max percentage watched |
| `lastposition` | int | Current playback position |

**Returns:** `{'status': 'ok'}`

**Logic:**
1. Validate parameters and context
2. Retrieve or create `hlsplayer_progress` record
3. Update only if new values exceed stored values (`progress` and `percentage` never decrease)
4. Always update `lastposition`
5. If `percentage >= completionminview`: mark activity complete and update grade

---

## JavaScript AMD Module (`amd/src/player.js`)

**Module ID:** `mod_hlsplayer/player`

**Key variables:**

| Variable | Purpose |
|----------|---------|
| `maxViewedTime` | Furthest second reached; initialised from `data-initial-progress` |
| `lastValidTime` | Last position at or before `maxViewedTime + 2.0`; seek restriction revert target |
| `lastSaveTime` | Timestamp of last AJAX save |

**Seek restriction logic:**

```javascript
player.on('seeking', function() {
    if (player.currentTime() > maxViewedTime + 1) {
        player.currentTime(lastValidTime);
    }
});
```

**Save trigger:** Every 10 seconds (via `setInterval`), on `pause`, and on `ended`.

**Percentage formula:**
```javascript
Math.min(100, Math.floor((maxViewedTime / player.duration()) * 100))
```

---

## Capabilities

| Capability | Context | Default roles |
|------------|---------|--------------|
| `mod/hlsplayer:addinstance` | Course | editingteacher, manager |
| `mod/hlsplayer:view` | Module | guest, student, teacher, editingteacher, manager |
| `mod/hlsplayer:viewreport` | Module | teacher, editingteacher, manager |

---

## CI Pipeline

Defined in `.github/workflows/ci.yml`. Runs on push and pull request.

**Matrix:** PHP 8.2, 8.3 × Moodle 4.05 STABLE × PostgreSQL 13

**Checks:** PHP Lint, Copy/Paste Detector, Mess Detector, Moodle Code Checker, PHPDoc Checker, Plugin Validation, Upgrade Savepoints, Mustache Lint, Grunt, PHPUnit
