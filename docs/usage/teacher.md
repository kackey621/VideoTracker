# Teacher Guide

This guide explains how to add, configure, and manage HLS Player activities as a teacher or course creator.

---

## Adding an HLS Player Activity

1. Open your course and click **Turn editing on**.
2. In the target section, click **Add an activity or resource**.
3. Select **HLS Player** from the activity list and click **Add**.

---

## Configuring Video Content

### Using an External URL

1. Set **Source Type** to **External URL**.
2. In the **Video URL** field, paste the full path to your `.m3u8` playlist.
3. The URL must end with `.m3u8` — Moodle validates this on save.

### Uploading a File

1. Set **Source Type** to **Uploaded File**.
2. In the **Video File** field, click **Add** and upload your `.m3u8` file.
3. If your playlist references local segment files (`.ts`), upload them in the same file area.

!!! tip
    The plugin automatically detects the `.m3u8` file as the main playlist. You do not need to specify which file is the entry point.

---

## Playback Options

| Option | Effect when enabled |
|--------|---------------------|
| **Allow Playback Speed** | Students see a speed selector (0.5×, 1×, 1.5×, 2×) in the player |
| **Allow Seeking** | Students can freely skip to any position in the video |

By default, both options are **off** — students watch at normal speed and cannot skip ahead past unwatched content.

---

## Setting Completion Requirements

Under the **Completion** section of the activity form:

1. Enable **Require view percentage**.
2. Enter the minimum percentage a student must watch (default: **95**).

Once a student's progress reaches or exceeds this threshold, the activity is automatically marked complete and the grade is recorded.

!!! warning "Changing the threshold after students have started"
    Updating the **Require view percentage** after students have begun watching does not retroactively change their completion status. Students who have already completed the activity remain completed.

---

## Viewing the Progress Report

To see how much each student has watched:

1. Open the HLS Player activity.
2. Click **View Progress Report** — visible in the top-right area or via the activity settings menu (requires teacher role).

The report shows a table with:

| Column | Description |
|--------|-------------|
| **Student Name** | Enrolled student's full name |
| **Progress** | Maximum time watched, formatted as H:MM:SS |
| **Percentage** | Maximum percentage of the video watched (0–100%) |
| **Last Access** | Date and time of the most recent progress save |

!!! note
    Students who have opened the activity but not yet played the video appear with 0% and no last access time.

---

## Grading

Grade book integration is automatic:

- When a student's percentage reaches or exceeds the completion threshold, they receive the **Maximum Grade** value.
- If the threshold is not met, their grade is 0.
- Grades update automatically — no manual action is required.

---

## Editing and Updating the Activity

You can change the source URL, uploaded file, or any settings at any time. Existing student progress data is not affected by source changes. To edit, use the standard Moodle **Edit settings** option from the activity menu.
