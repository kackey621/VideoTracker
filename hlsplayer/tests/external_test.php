<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/hlsplayer/classes/external.php');

class mod_hlsplayer_external_testcase extends externallib_advanced_testcase {

    public function test_submit_progress() {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create course and activity
        $course = $this->getDataGenerator()->create_course();
        $hlsplayer = $this->getDataGenerator()->create_module('hlsplayer', [
            'course' => $course->id,
            'name' => 'Test HLS',
            'grade' => 100,
            'completionminview' => 95
        ]);

        // Enroll user
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Call external function
        $result = mod_hlsplayer_external::submit_progress($hlsplayer->id, 10, 10, 100);
        $result = external_api::clean_returnvalue(mod_hlsplayer_external::submit_progress_returns(), $result);
        $this->assertEquals('ok', $result['status']);

        // Check DB
        global $DB;
        $progress = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id, 'userid' => $user->id]);
        $this->assertEquals(10, $progress->progress);
        $this->assertEquals(10, $progress->percentage);
        $this->assertEquals(100, $progress->lastposition);

        // Update with higher progress
        mod_hlsplayer_external::submit_progress($hlsplayer->id, 50, 50, 500);
        $progress = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id, 'userid' => $user->id]);
        $this->assertEquals(50, $progress->progress);
        $this->assertEquals(50, $progress->percentage);
        $this->assertEquals(500, $progress->lastposition); // Updated lastposition

        // Update with lower progress (resuming from earlier) - progress should NOT change, lastposition SHOULD change
        mod_hlsplayer_external::submit_progress($hlsplayer->id, 50, 50, 200); 
        $progress = $DB->get_record('hlsplayer_progress', ['hlsplayerid' => $hlsplayer->id, 'userid' => $user->id]);
        $this->assertEquals(50, $progress->progress); // Should still be max
        $this->assertEquals(200, $progress->lastposition); // Should be new position
    }

    public function test_submit_progress_completion_and_grading() {
        $this->resetAfterTest();
        $this->setAdminUser();
        global $DB, $CFG;
        require_once($CFG->libdir . '/completionlib.php');

        // Create course with completion enabled
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $hlsplayer = $this->getDataGenerator()->create_module('hlsplayer', [
            'course' => $course->id,
            'name' => 'Test HLS',
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionminview' => 95,
            'grade' => 100
        ]);
        
        // Manual grade item creation might be needed in test if standard_grading did not fire in generator,
        // but 'create_module' usually triggers lib.php's add_instance which we assume calls grading update.

        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // 1. Submit progress below threshold
        mod_hlsplayer_external::submit_progress($hlsplayer->id, 50, 50, 50);
        
        // Check completion state
        $cm = get_coursemodule_from_instance('hlsplayer', $hlsplayer->id);
        $completion = new completion_info($course);
        $data = $completion->get_data($cm, false, $user->id);
        $this->assertNotEquals(COMPLETION_COMPLETE, $data->completionstate);

        // Check grades - should be 0 or null
        $grade_item = grade_item::fetch(['courseid' => $course->id, 'itemtype' => 'mod', 'itemmodule' => 'hlsplayer', 'iteminstance' => $hlsplayer->id]);
        if ($grade_item) {
            $grade = $grade_item->get_grade($user->id);
            // $grade->finalgrade might be null or 0 depending on setup
            $this->assertTrue(empty($grade->finalgrade) || $grade->finalgrade == 0);
        }

        // 2. Submit progress meeting threshold
        mod_hlsplayer_external::submit_progress($hlsplayer->id, 100, 95, 100);

        // Check completion state
        $data = $completion->get_data($cm, false, $user->id);
        $this->assertEquals(COMPLETION_COMPLETE, $data->completionstate);

        // Check grades - should be 100
        if ($grade_item) {
            $grade = $grade_item->get_grade($user->id);
            $this->assertEquals(100, $grade->finalgrade);
        }
    }
}
