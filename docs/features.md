# Features

## HLS Streaming via Video.js

The player uses [Video.js 8.10.0](https://videojs.com/) loaded from the jsDelivr/zencdn CDN. Video.js provides a consistent, accessible UI across all modern browsers and handles HLS (`application/x-mpegURL`) playback natively where supported.

**HLS (HTTP Live Streaming)** is Apple's adaptive streaming protocol. A `.m3u8` playlist file references a sequence of short `.ts` video segments. The browser fetches and plays segments sequentially, allowing the server to adjust quality based on network conditions.

---

## Dual Source Types

| Type | How it works |
|------|-------------|
| **External URL** | The browser fetches the `.m3u8` directly from your streaming server or CDN |
| **Uploaded File** | Moodle stores the `.m3u8` and segment files; `pluginfile.php` serves them with Moodle's access control |

When using file upload, the plugin automatically locates the `.m3u8` file within the uploaded file set and constructs the correct Moodle file URL.

---

## Progress Tracking

Progress is tracked client-side and saved server-side.

**Client-side (JavaScript):**

- The Video.js `timeupdate` event fires continuously during playback.
- A variable `maxViewedTime` records the furthest second the student has reached.
- `maxViewedTime` only increases — scrubbing backward does not lower it.
- The percentage display below the player updates in real time: `(maxViewedTime / duration) × 100`, capped at 100%.

**Server-side (AJAX):**

- Progress is sent to `mod_hlsplayer_submit_progress` every **10 seconds**, on **pause**, and on **ended**.
- The server never overwrites a higher value with a lower one — student progress can only go up.
- Data stored: maximum seconds watched (`progress`), maximum percentage (`percentage`), current playback position (`lastposition`), timestamp (`timemodified`).

---

## Resume Playback

On page load, the player reads the stored `lastposition` value and calls `player.currentTime(lastPosition)` once the player is ready. Playback resumes from that exact second.

`lastposition` is updated on every save (even if `progress` and `percentage` did not increase), so students always resume from their most recent position.

---

## Seek Restriction

When the teacher disables seeking, the JavaScript module listens to the Video.js `seeking` event:

1. If `currentTime > maxViewedTime + 1` (seeking more than 1 second ahead of the furthest watched point), the player reverts to `lastValidTime`.
2. `lastValidTime` tracks the last playback position that was at or before `maxViewedTime + 2.0` seconds.
3. A 1-second tolerance prevents false triggers from normal playback buffering.

Students can rewind freely — only forward seeking past the unseen portion is blocked.

---

## Playback Speed Control

When enabled, Video.js is initialised with `playbackRates: [0.5, 1, 1.5, 2]`. A speed menu appears in the player control bar. When disabled, `playbackRates` is an empty array and the menu is hidden.

---

## Completion and Grading

**Completion** is handled by a Moodle custom completion rule (`completionminview`):

- On every progress save, the server checks: `percentage >= completionminview`
- If true, Moodle's completion API marks the activity `COMPLETION_COMPLETE`
- The grade book is updated at the same time: the student receives the full `Maximum Grade` value
- Grading is binary — either 0 or the full grade; no partial credit

**Grade item** is created with `GRADE_TYPE_VALUE`, ranging from 0 to the configured maximum.

---

## Teacher Progress Report

The report (`report.php`) requires the `mod/hlsplayer:viewreport` capability (granted to teachers and above).

It fetches all enrolled users for the course, respects Moodle group settings, and joins against the `hlsplayer_progress` table. The output is an HTML table with:

- Student name (linked to profile)
- Progress formatted as `H:MM:SS`
- Percentage as an integer (0–100)
- Last access as a formatted datetime

---

## Backup and Restore

Full Moodle 2 backup/restore support is implemented via the standard `backup/moodle2/` class structure. Activity settings are backed up. Student progress records are not included in the backup by design (they are per-instance user data and would not apply to a restored copy in a new course).
