# Completion Rules Refactor

The "Require view percentage" completion condition layout has been refactored to better integrate with Moodle's standard "Add requirements" UI.

## Changes
- **Ungrouping**: The checkbox and text field were previously grouped using `addGroup`. This has been removed to allow Moodle to render them as distinct standard elements, which usually fixes alignment issues in the completion settings form.
- **Enabled Logic**: The completion condition is primarily driven by the `completionminview` value (integer). The checkbox `completionminviewenabled` acts as a UI toggle.
- **Validation**: Corrected the return value of `add_completion_rules` to `['completionminview']` to match the database field.

## Why?
Moodle's form API, specifically the newer completion interface, works best with individual elements. Grouping them can sometimes confuse the "Add requirement" dropdown parser or result in non-standard rendering (putting the entire group outside the expected list). Ungrouping them allows the checkbox to serve as the visual label/toggle and the text box to appear contextually.
