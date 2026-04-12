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
 * Unit tests for lib.php functions of the HLS Player module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hlsplayer\tests;

global $CFG;
require_once($CFG->dirroot . '/mod/hlsplayer/lib.php');

/**
 * Unit tests for hlsplayer lib functions.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::hlsplayer_supports
 * @covers     ::hlsplayer_add_instance
 * @covers     ::hlsplayer_update_instance
 * @covers     ::hlsplayer_delete_instance
 */
class lib_test extends \advanced_testcase {

    /**
     * Test that hlsplayer_supports returns correct values.
     */
    public function test_hlsplayer_supports(): void {
        $this->assertSame(MOD_ARCHETYPE_RESOURCE, hlsplayer_supports(FEATURE_MOD_ARCHETYPE));
        $this->assertFalse(hlsplayer_supports(FEATURE_GROUPS));
        $this->assertTrue(hlsplayer_supports(FEATURE_COMPLETION_TRACKS_VIEWS));
        $this->assertTrue(hlsplayer_supports(FEATURE_GRADE_HAS_GRADE));
        $this->assertTrue(hlsplayer_supports(FEATURE_SHOW_DESCRIPTION));
        $this->assertTrue(hlsplayer_supports(FEATURE_COMPLETION_HAS_RULES));
        $this->assertNull(hlsplayer_supports('unknown_feature'));
    }

    /**
     * Test that hlsplayer_add_instance creates a record.
     */
    public function test_hlsplayer_add_instance(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course              = $this->getDataGenerator()->create_course();
        $hlsplayer           = new \stdClass();
        $hlsplayer->course   = $course->id;
        $hlsplayer->name     = 'Test HLS Player';
        $hlsplayer->intro    = 'Intro';
        $hlsplayer->introformat = FORMAT_MOODLE;
        $hlsplayer->sourcetype = 'url';
        $hlsplayer->videourl = 'https://example.com/video.m3u8';
        $hlsplayer->allowspeeds = 1;
        $hlsplayer->allowseeking = 0;
        $hlsplayer->completionminview = 0;

        $id = hlsplayer_add_instance($hlsplayer);
        $this->assertNotEmpty($id);

        global $DB;
        $record = $DB->get_record('hlsplayer', ['id' => $id]);
        $this->assertEquals('Test HLS Player', $record->name);
        $this->assertEquals(1, $record->allowspeeds);
    }

    /**
     * Test that hlsplayer_update_instance updates a record.
     */
    public function test_hlsplayer_update_instance(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course              = $this->getDataGenerator()->create_course();
        $hlsplayer           = new \stdClass();
        $hlsplayer->course   = $course->id;
        $hlsplayer->name     = 'Test HLS Player';
        $hlsplayer->sourcetype = 'url';
        $hlsplayer->videourl = 'https://example.com/video.m3u8';
        $hlsplayer->completionminview = 0;
        $id = hlsplayer_add_instance($hlsplayer);

        $hlsplayer->instance = $id;
        $hlsplayer->name     = 'Updated HLS Player';
        $hlsplayer->id       = $id;

        $result = hlsplayer_update_instance($hlsplayer);
        $this->assertTrue($result);

        global $DB;
        $record = $DB->get_record('hlsplayer', ['id' => $id]);
        $this->assertEquals('Updated HLS Player', $record->name);
    }

    /**
     * Test that hlsplayer_delete_instance removes the record.
     */
    public function test_hlsplayer_delete_instance(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course              = $this->getDataGenerator()->create_course();
        $hlsplayer           = new \stdClass();
        $hlsplayer->course   = $course->id;
        $hlsplayer->name     = 'Test HLS Player';
        $hlsplayer->sourcetype = 'url';
        $hlsplayer->videourl = 'https://example.com/video.m3u8';
        $hlsplayer->completionminview = 0;
        $id = hlsplayer_add_instance($hlsplayer);

        $result = hlsplayer_delete_instance($id);
        $this->assertTrue($result);

        global $DB;
        $this->assertFalse($DB->record_exists('hlsplayer', ['id' => $id]));
    }
}
