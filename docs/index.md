# HLS Player for Moodle

**HLS Player** is a free, open-source Moodle activity module that enables seamless playback of HTTP Live Streaming (HLS) videos directly within your courses — no additional plugins or external apps required.

[![CI](https://github.com/kackey621/moodle-mod_hlsplayer/actions/workflows/ci.yml/badge.svg)](https://github.com/kackey621/moodle-mod_hlsplayer/actions)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://github.com/kackey621/moodle-mod_hlsplayer/blob/main/hlsplayer/LICENSE)
[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-2.1-4baaaa.svg)](code-of-conduct.md)
[![Deploys by Netlify](https://www.netlify.com/assets/badges/netlify-badge-light.svg)](https://www.netlify.com)

<div style="margin: 0.5rem 0 1rem;">
  <a href="https://www.netlify.com" target="_blank" rel="noopener">
    <img src="https://www.netlify.com/assets/badges/netlify-badge-light.svg" alt="This site is powered by Netlify" style="height:2rem;">
  </a>
  <span style="margin-left: 0.5rem; font-size: 0.85rem; color: var(--md-default-fg-color--light);">This site is powered by Netlify</span>
</div>

---

## Features

<div class="grid cards" markdown>

- :material-play-circle: **HLS Streaming**

    Play `.m3u8` streams from external URLs or uploaded files, powered by [Video.js 8.10.0](https://videojs.com/).

- :material-chart-line: **Progress Tracking**

    Automatically records each student's viewing percentage and maximum time watched.

- :material-history: **Resume Playback**

    Students return to exactly where they left off — across sessions and devices.

- :material-fast-forward-outline: **Seek Restriction**

    Prevent students from skipping ahead past content they haven't watched yet.

- :material-speedometer: **Playback Speed Control**

    Allow students to watch at 0.5×, 1×, 1.5×, or 2× speed.

- :material-check-circle: **Completion Rules**

    Mark an activity complete automatically when a student reaches a set viewing percentage (default: 95%).

- :material-table: **Teacher Reports**

    View a per-student progress table showing watch time, percentage, and last access.

- :material-shield-check: **GDPR Compliant**

    Full integration with Moodle's Privacy API — supports data export and deletion.

</div>

---

## Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| Moodle | 4.5 | 4.5 or later |
| PHP | 8.2 | 8.2 / 8.3 |
| Database | MySQL, PostgreSQL, MariaDB | PostgreSQL 13+ |

---

## Quick Start

1. [Install the plugin](installation.md) via ZIP upload or manual copy
2. Add an **HLS Player** activity to your course
3. Paste a `.m3u8` URL or upload an HLS file
4. Configure completion and playback options
5. Students can watch and track their progress immediately

[Get Started with Installation](installation.md){ .md-button .md-button--primary }
[Teacher Guide](usage/teacher.md){ .md-button }
