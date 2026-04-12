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
 * External functions and web service definitions for the HLS Player module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_hlsplayer_submit_progress' => [
        'classname'   => 'mod_hlsplayer\external',
        'methodname'  => 'submit_progress',
        'description' => 'Updates user progress for an HLS player activity',
        'type'        => 'write',
        'ajax'        => true,
    ],
];

$services = [
    'HLS Player Services' => [
        'functions'           => ['mod_hlsplayer_submit_progress'],
        'requiredcapability'  => 'mod/hlsplayer:view',
        'restrictedusers'     => 0,
        'enabled'             => 1,
    ],
];
