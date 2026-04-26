# Configuration

HLS Player has no site-wide admin settings — all options are configured per activity when creating or editing an instance.

---

## Settings Reference

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| **Name** | Text (required) | — | Activity title shown in the course |
| **Description** | Rich text | — | Displayed above the player |
| **Source Type** | Dropdown | External URL | Choose between URL or file upload |
| **Video URL** | Text | — | Must end with `.m3u8` |
| **Video File** | File manager | — | Upload `.m3u8` + segment files |
| **Allow Playback Speed** | Checkbox | Off | Enables 0.5×, 1×, 1.5×, 2× speed selector |
| **Allow Seeking** | Checkbox | Off | If off, forward seeking past unwatched content is blocked |
| **Require View Percentage** | Integer (0–100) | 95 | Minimum % watched to mark activity complete |
| **Maximum Grade** | Number | 100 | Grade book value awarded on completion |

---

## Source Type

=== "External URL"

    Paste the full URL to your `.m3u8` playlist.

    **Requirements:**
    - The URL must end with `.m3u8` (validation is enforced on save)
    - The server must allow cross-origin requests (CORS) from your Moodle domain
    - The stream must be publicly accessible or protected by a method your browser supports

    **Example:**
    ```
    https://example.com/streams/lecture01/playlist.m3u8
    ```

=== "Uploaded File"

    Upload your HLS content directly into Moodle's file storage.

    **Accepted file types:** `.m3u8`, `.ts`, `.aac`, `.mp4`, `.key`

    **How it works:**
    - Upload the `.m3u8` playlist file — this is detected automatically as the main stream source
    - If your playlist references segment files (`.ts`), upload them alongside the `.m3u8`
    - Moodle serves the files securely via `pluginfile.php`

    !!! tip
        There is no file size limit. However, for large video libraries, an external URL (CDN or streaming server) is more efficient than file upload.

---

## Playback Options

### Allow Seeking

When **unchecked** (default), students cannot skip forward past the furthest point they have already watched. They can freely rewind to any previously viewed section.

This is useful for:
- Content that must be watched in sequence
- Compliance training where full viewing must be verified

When **checked**, students can seek freely to any point in the video.

### Allow Playback Speed

When **checked**, a speed selector (0.5×, 1×, 1.5×, 2×) appears in the Video.js player controls.

When **unchecked** (default), the video plays at normal speed only.

---

## Completion Settings

The **Require View Percentage** field (0–100) sets the threshold for automatic completion.

- Default: **95** (student must watch at least 95% of the video)
- Set to **0** to disable the custom completion rule (only Moodle's standard "viewed" completion applies)

See [Completion](completion.md) for full details on how the percentage is calculated and how completion is triggered.

---

## Grading

When a student meets the minimum view percentage, they automatically receive the **Maximum Grade** value in the Moodle grade book. If the threshold is not met, their grade is 0. Grading is binary — there is no partial credit.
