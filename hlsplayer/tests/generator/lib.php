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
 * Data generator for the HLS Player activity module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * HLS Player module data generator.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_hlsplayer_generator extends testing_module_generator {
    /**
     * Creates a new instance of the HLS Player activity.
     *
     * @param array|stdClass|null $record Record with activity properties.
     * @param array|null $options Additional options.
     * @return stdClass The created module instance.
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array) $record;

        if (!isset($record->name)) {
            $record->name = 'Test HLS Player ' . $this->instancecount;
        }
        if (!isset($record->sourcetype)) {
            $record->sourcetype = 'url';
        }
        if (!isset($record->videourl)) {
            $record->videourl = 'https://example.com/video.m3u8';
        }
        if (!isset($record->allowspeeds)) {
            $record->allowspeeds = 0;
        }
        if (!isset($record->allowseeking)) {
            $record->allowseeking = 0;
        }
        if (!isset($record->completionminview)) {
            $record->completionminview = 0;
        }
        if (!isset($record->grade)) {
            $record->grade = 100;
        }

        return parent::create_instance($record, $options);
    }
}
