<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for the HLS Player module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename']        = 'HLS Player';
$string['modulename_help']   = 'Use the HLS Player module to embed m3u8 video streams directly into your course. You can provide a URL or upload a file.';
$string['modulenameplural']  = 'HLS Players';
$string['pluginname']        = 'HLS Player';
$string['pluginadministration'] = 'HLS Player Administration';
$string['hlsplayer:addinstance'] = 'Add a new HLS Player';
$string['hlsplayer:view']    = 'View HLS Player';
$string['name']              = 'Name';
$string['content']           = 'Video Content';
$string['sourcetype']        = 'Source Type';
$string['sourcetype_url']    = 'External URL';
$string['sourcetype_file']   = 'Uploaded File';
$string['videourl']          = 'Video URL (.m3u8)';
$string['videofile']         = 'Video File (.m3u8)';
$string['novideo']           = 'No valid video source found. Please configure the activity settings.';
$string['error_invalidurl']  = 'URL must end with .m3u8';
$string['allowspeeds']       = 'Allow Playback Speed Adjustment';
$string['allowseeking']      = 'Allow Seeking';
$string['allowseeking_help'] = 'If enabled, students can seek freely within the video. If disabled, they cannot seek past the point they have already watched.';
$string['viewreport']        = 'View Progress Report';
$string['hlsplayer:viewreport'] = 'View HLS Player Progress Report';
$string['progress']          = 'Progress';
$string['lastaccess']        = 'Last Access';
$string['report']            = 'Report';
$string['completion']        = 'Completion Requirements';
$string['completionminview'] = 'Require view percentage';
$string['completionminview_help'] = 'Students must view at least this percentage of the video to complete the activity.';
$string['completionminview_desc'] = 'Student must view at least {$a}% of the video.';
$string['yourprogress']      = 'Your Progress';
$string['maximumchars']      = 'Maximum of {$a} characters';

// Events.
$string['eventcoursemoduleviewed']              = 'HLS Player viewed';
$string['eventcoursemoduleinstancelistviewed']  = 'HLS Player instance list viewed';

// Privacy API.
$string['privacy:metadata:hlsplayer_progress']              = 'Stores the progress of users watching HLS videos.';
$string['privacy:metadata:hlsplayer_progress:userid']       = 'The ID of the user.';
$string['privacy:metadata:hlsplayer_progress:progress']     = 'The maximum time viewed in seconds.';
$string['privacy:metadata:hlsplayer_progress:percentage']   = 'The maximum percentage viewed.';
$string['privacy:metadata:hlsplayer_progress:lastposition'] = 'The last playback position timestamp.';
$string['privacy:metadata:hlsplayer_progress:timemodified'] = 'The time when the progress was last updated.';
