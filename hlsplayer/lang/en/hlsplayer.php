<?php
defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'HLS Player';
$string['modulename_help'] = 'Use the HLS Player module to embed m3u8 video streams directly into your course. You can provide a URL or upload a file.';
$string['modulenameplural'] = 'HLS Players';
$string['pluginname'] = 'HLS Player';
$string['pluginadministration'] = 'HLS Player Administration';
$string['hlsplayer:addinstance'] = 'Add a new HLS Player';
$string['hlsplayer:view'] = 'View HLS Player';
$string['name'] = 'Name';
$string['content'] = 'Video Content';
$string['sourcetype'] = 'Source Type';
$string['sourcetype_url'] = 'External URL';
$string['sourcetype_file'] = 'Uploaded File';
$string['videourl'] = 'Video URL (.m3u8)';
$string['videofile'] = 'Video File (.m3u8)';
$string['novideo'] = 'No valid video source found. Please configure the activity settings.';
$string['error_invalidurl'] = 'URL must end with .m3u8';
$string['allowspeeds'] = 'Allow Playback Speed Adjustment';
$string['allowseeking'] = 'Allow Seeking';
$string['allowseeking_help'] = 'If enabled, students can seek freely within the video. If disabled, they cannot seek past the point they have already watched.';
$string['viewreport'] = 'View Progress Report';
$string['hlsplayer:viewreport'] = 'View HLS Player Progress Report';
$string['progress'] = 'Progress';
$string['lastaccess'] = 'Last Access';
$string['report'] = 'Report';
$string['completion'] = 'Completion Requirements';
$string['completionminview'] = 'Require view percentage';
$string['completionminview_help'] = 'Students must view at least this percentage of the video to complete the activity.';
$string['completionminview_desc'] = 'Student must view at least {$a}% of the video.';
$string['yourprogress'] = 'Your Progress';
$string['maximumchars'] = 'Maximum of {$a} characters';

// Privacy API
$string['privacy:metadata:hlsplayer_progress'] = 'Stores the progress of users watching HLS videos.';
$string['privacy:metadata:hlsplayer_progress:userid'] = 'The ID of the user.';
$string['privacy:metadata:hlsplayer_progress:progress'] = 'The maximum time viewed in seconds.';
$string['privacy:metadata:hlsplayer_progress:percentage'] = 'The maximum percentage viewed.';
$string['privacy:metadata:hlsplayer_progress:lastposition'] = 'The last playback position timestamp.';
$string['privacy:metadata:hlsplayer_progress:timemodified'] = 'The time when the progress was last updated.';
