# HLS Player for Moodle

A Moodle activity module that enables HLS (HTTP Live Streaming) video playback using Video.js. This plugin supports `.m3u8` playlists from both external URLs and file uploads. It includes advanced features for education, such as seek restrictions, progress tracking, and automatic completion based on watch percentage.

## Features

- **HLS Playback**: Native HLS support via [Video.js](https://videojs.com/).
- **Flexible Sources**: Support for both external `.m3u8` URLs and uploaded `.m3u8` files (with segment handling).
- **Strict Validation**: Enforces `.m3u8` extension to ensure correct format.
- **Seek Restriction**: Prevents students from scrubbing forward past the furthest point they have watched.
- **Playback Speed Control**: Configurable option to allow or disallow playback speed adjustments (0.5x, 1x, 1.5x, 2x).
- **Progress Tracking**:
    - Real-time tracking of the maximum percentage viewed.
    - Displays "Your Progress: X%" to the student below the player.
    - Teachers can view a detailed progress report for all students.
- **Completion Tracking**: Integration with Moodle's completion system. Mark activity as complete when a student watches a specific percentage (e.g., 95%).

## Installation

1.  **Download**: Clone or download this repository.
2.  **Deploy**: Rename the folder to `hlsplayer` and place it in your Moodle `mod/` directory:
    ```bash
    /path/to/moodle/mod/hlsplayer
    ```
3.  **Install**: Log in to Moodle as an Administrator and go to **Site administration > Notifications** to trigger the database installation.

## Usage

### For Teachers (Course Creators)

1.  Turn editing on in your course.
2.  Click **Add an activity or resource** and select **HLS Player**.
3.  **General**:
    - **Name**: Enter the activity name.
4.  **Content**:
    - **Source Type**: Select "External URL" or "Uploaded File".
    - **Video URL**: (If URL selected) Enter the link to the `.m3u8` file.
    - **Video File**: (If File selected) Upload the `.m3u8` file. *Note: If your m3u8 references local segment files, ensure they are uploaded or accessible.*
    - **Allow Playback Speed Adjustment**: Check this to let students change playback speed.
5.  **Completion Requirements**:
    - **Require view percentage**: Set the percentage required to mark the activity as complete (default: 95).
6.  Save the activity.

**Viewing Reports:**
Inside the activity, teachers can click the **"View Progress Report"** link (top right or via settings) to see a table of student progress, including completion percentage and last access time.

### For Students

- Open the activity to play the video.
- You cannot skip ahead to parts you haven't watched yet.
- Your progress is saved automatically. The percentage watched is displayed below the video.
- If allowed, use the gear icon to change playback speed.

## Development

### Directory Structure

- `classes/external.php`: External API for AJAX progress updates.
- `db/install.xml`: Database schema (`hlsplayer` and `hlsplayer_progress` tables).
- `db/services.php`: Web service registration.
- `lang/en/hlsplayer.php`: Language strings.
- `templates/player.mustache`: HTML and JS for the video player.
- `lib.php`: Core module functions and completion logic.
- `mod_form.php`: Activity settings form.
- `view.php`: Main activity page.
- `report.php`: Teacher's progress report page.

### Compatibility

- **Moodle**: 5.0+ (and 4.5+)
- **PHP**: 8.2+

### License

GPL v3 or later.
