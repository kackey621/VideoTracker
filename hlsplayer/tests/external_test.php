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
 * Unit tests for the HLS Player external API.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hlsplayer\tests;

use mod_hlsplayer\external;

/**
 * Unit tests for mod_hlsplayer\external.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_hlsplayer\external::submit_progress
 */
class external_test extends \advanced_testcase {
    /**
     * Test submit_progress records progress correctly.
     */
    public function test_submit_progress(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create course and activity via generator.
        $course    = $this->getDataGenerator()->create_course();
        $module    = $this->getDataGenerator()->create_module('hlsplayer', [
            'course'           => $course->id,
            'name'             => 'Test HLS',
            'grade'            => 100,
            'completionminview' => 95,
        ]);

        // Enrol user.
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Initial progress submission.
        $result = external::submit_progress($module->id, 10, 10, 100);
        $result = \core_external\external_api::clean_returnvalue(external::submit_progress_returns(), $result);
        $this->assertEquals('ok', $result['status']);

        // Verify DB record.
        $progress = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $module->id, 'userid' => $user->id]);
        $this->assertNotFalse($progress);
        $this->assertEquals(10, $progress->progress);
        $this->assertEquals(10, $progress->percentage);
        $this->assertEquals(100, $progress->lastposition);

        // Update with higher progress.
        external::submit_progress($module->id, 50, 50, 500);
        $progress = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $module->id, 'userid' => $user->id]);
        $this->assertEquals(50, $progress->progress);
        $this->assertEquals(50, $progress->percentage);
        $this->assertEquals(500, $progress->lastposition);

        // Update with same progress, different position.
        external::submit_progress($module->id, 50, 50, 200);
        $progress = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $module->id, 'userid' => $user->id]);
        $this->assertEquals(50, $progress->progress); // Max unchanged.
        $this->assertEquals(200, $progress->lastposition); // Last position updated.
    }

    /**
     * Test that completion and grading trigger at threshold.
     */
    public function test_submit_progress_completion_and_grading(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();

        require_once($CFG->libdir . '/completionlib.php');

        // Create course with completion enabled.
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $module = $this->getDataGenerator()->create_module('hlsplayer', [
            'course'            => $course->id,
            'name'              => 'Test HLS',
            'completion'        => COMPLETION_TRACKING_AUTOMATIC,
            'completionminview' => 95,
            'grade'             => 100,
        ]);

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Submit progress below threshold.
        external::submit_progress($module->id, 50, 50, 50);

        $cm         = get_coursemodule_from_instance('hlsplayer', $module->id);
        $completion = new \completion_info($course);
        $data       = $completion->get_data($cm, false, $user->id);
        $this->assertNotEquals(COMPLETION_COMPLETE, $data->completionstate);

        // Submit progress meeting the threshold.
        external::submit_progress($module->id, 100, 95, 100);

        $data = $completion->get_data($cm, false, $user->id);
        $this->assertEquals(COMPLETION_COMPLETE, $data->completionstate);

        // Verify grade.
        require_once($CFG->libdir . '/gradelib.php');
        $gradeitem = \grade_item::fetch([
            'courseid'     => $course->id,
            'itemtype'     => 'mod',
            'itemmodule'   => 'hlsplayer',
            'iteminstance' => $module->id,
        ]);
        if ($gradeitem) {
            $grade = $gradeitem->get_grade($user->id);
            $this->assertEquals(100, $grade->finalgrade);
        }
    }
}
