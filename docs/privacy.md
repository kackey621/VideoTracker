# Privacy & GDPR

HLS Player fully implements Moodle's Privacy API and is compliant with GDPR and similar data protection regulations.

---

## Data Collected

The plugin stores one record per student per activity instance in the `hlsplayer_progress` table.

| Field | Purpose |
|-------|---------|
| `userid` | Identifies the student |
| `progress` | Maximum seconds of the video watched |
| `percentage` | Maximum percentage of the video watched (0–100%) |
| `lastposition` | Most recent playback position (used for resume) |
| `timemodified` | Timestamp of the last progress update |

### What is NOT collected

- Video content or viewing choices
- IP addresses
- Browser or device information
- Any data beyond playback progress

---

## Data Retention

Progress data persists as long as the activity instance exists in the course. When a teacher or administrator deletes an HLS Player activity, all associated progress records are deleted automatically.

---

## Data Export

Site administrators and Data Protection Officers can export a user's data via **Site administration > Privacy and policies > Data requests**.

When a user data export is requested, the plugin exports all `hlsplayer_progress` records for that user across all course contexts. Exported data includes:

- Percentage watched (formatted as "X%")
- Last playback position (in seconds)
- Timestamp (human-readable datetime)

---

## Data Deletion

Two deletion paths are supported:

| Request type | What is deleted |
|---|---|
| Delete all data for a user | All `hlsplayer_progress` records for that specific user, across all HLS Player activities |
| Delete all data in a context | All `hlsplayer_progress` records for all users within a specific course module context |

Both operations are available through Moodle's standard Data Privacy tools.

---

## AJAX Security

The progress-saving AJAX endpoint (`mod_hlsplayer_submit_progress`) validates:

1. The user is logged in and has the `mod/hlsplayer:view` capability for the specific activity
2. The `hlsplayerid` refers to an existing activity
3. All parameters are sanitised and type-checked before database writes

No sensitive data is transmitted — only integer values for seconds watched, percentage, and playback position.

---

## Metadata Declaration

The plugin declares its data storage to Moodle's metadata registry, so it appears correctly in privacy reports and audits. The declaration covers the `hlsplayer_progress` table and describes the purpose of each field.
