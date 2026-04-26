# Activity Completion

HLS Player integrates with Moodle's activity completion system via a custom completion rule: **Require view percentage** (`completionminview`).

---

## How It Works

### The `completionminview` Rule

When you set a **Require view percentage** value (e.g., 95), Moodle marks the activity complete for a student when their recorded viewing percentage reaches or exceeds that threshold.

- Default value: **95** (student must watch at least 95% of the video)
- Valid range: **0–100**
- Setting to **0** disables the custom rule — only Moodle's standard "viewed" completion applies

### How the Percentage Is Calculated

```
percentage = floor( maxViewedTime / videoDuration × 100 )
```

- `maxViewedTime` — the furthest second the student has reached (never decreases)
- `videoDuration` — obtained from the Video.js `duration()` API after video metadata loads
- The result is floored to an integer and capped at **100**

!!! warning "If video duration cannot be determined"
    If the video metadata fails to load (e.g., network error), `duration` may be 0 or unavailable. In this case, percentage cannot be calculated correctly. Ensure your stream is accessible and the `.m3u8` playlist is valid.

### When Completion Is Triggered

Every time the player saves progress (every 10 seconds, on pause, on ended), the server checks:

```
if percentage >= completionminview:
    mark activity COMPLETION_COMPLETE
    update grade book
```

This happens automatically in the background — no student action is required beyond watching the video.

---

## Grade Integration

Completion and grading are linked:

- When the threshold is met → student receives the **Maximum Grade** (e.g., 100)
- When the threshold is not yet met → grade is **0**
- Grading is binary; there is no partial credit

Both completion state and grade update in the same server call.

---

## Edge Cases

!!! note "Changing the threshold after students have started"
    If you increase or decrease the **Require view percentage** after students have already been watching:

    - Students who already completed the activity **remain completed** — their state is not re-evaluated.
    - Students who have not yet completed will need to reach the **new** threshold on their next save.

!!! note "Student never opens the activity"
    Students with no progress record show 0% in the report. Their completion state remains incomplete until they watch.

---

## Enabling Completion in Your Course

Moodle completion tracking must be enabled at the site and course level before activity completion works.

1. **Site level**: Site administration > Advanced features > Enable completion tracking ✓
2. **Course level**: Course settings > Completion tracking > Enable ✓
3. **Activity level**: The **Completion** section appears in the HLS Player settings form — set **Require view percentage** to your desired value.
