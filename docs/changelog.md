# Changelog

All notable changes to this project are documented here.

---

## [v0.1.0] — 2025-04-23

Initial release.

### Added

- HLS video playback via [Video.js 8.10.0](https://videojs.com/) (CDN)
- Dual source types: external `.m3u8` URL and file upload (`.m3u8`, `.ts`, `.aac`, `.mp4`, `.key`)
- Real-time progress tracking: maximum seconds watched and percentage displayed below the player
- Progress saved every 10 seconds, on pause, and on video end via AJAX
- Resume playback: student returns to last playback position on re-open
- Seek restriction: optional block on forward scrubbing past unwatched content
- Configurable playback speed: 0.5×, 1×, 1.5×, 2× (optional)
- Custom completion rule: `completionminview` — auto-complete when percentage threshold reached (default 95%)
- Grade book integration: full grade awarded when threshold met (binary pass/fail)
- Teacher progress report: per-student table with watch time (H:MM:SS), percentage, and last access
- GDPR Privacy API: metadata declaration, data export, and data deletion
- Full Moodle 2 backup/restore support
- PHPUnit test suite (`external_test.php`, `lib_test.php`)
- CI via GitHub Actions: PHP 8.2 and 8.3, Moodle 4.05 STABLE, PostgreSQL 13
- Language support: English (`en`) and Japanese (`ja`)
