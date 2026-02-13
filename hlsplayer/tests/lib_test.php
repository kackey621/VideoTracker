<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/hlsplayer/lib.php');

class mod_hlsplayer_lib_testcase extends advanced_testcase {

    public function test_hlsplayer_supports() {
        $this->assertTrue(hlsplayer_supports(FEATURE_MOD_ARCHETYPE) === MOD_ARCHETYPE_RESOURCE);
        $this->assertTrue(hlsplayer_supports(FEATURE_GROUPS) === false);
        $this->assertTrue(hlsplayer_supports(FEATURE_COMPLETION_TRACKS_VIEWS) === true);
        $this->assertTrue(hlsplayer_supports(FEATURE_GRADE_HAS_GRADE) === true);
        $this->assertTrue(hlsplayer_supports(FEATURE_SHOW_DESCRIPTION) === true);
        $this->assertNull(hlsplayer_supports('unknown_feature'));
    }

    public function test_hlsplayer_add_instance() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $hlsplayer = new stdClass();
        $hlsplayer->course = $course->id;
        $hlsplayer->name = 'Test HLS Player';
        $hlsplayer->intro = 'Intro';
        $hlsplayer->introformat = FORMAT_MOODLE;
        $hlsplayer->sourcetype = 'url';
        $hlsplayer->videourl = 'http://example.com/video.m3u8';
        $hlsplayer->allowspeeds = 1;

        $id = hlsplayer_add_instance($hlsplayer);
        $this->assertNotEmpty($id);

        global $DB;
        $db_hlsplayer = $DB->get_record('hlsplayer', ['id' => $id]);
        $this->assertEquals('Test HLS Player', $db_hlsplayer->name);
        $this->assertEquals(1, $db_hlsplayer->allowspeeds);
    }

    public function test_hlsplayer_update_instance() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $hlsplayer = new stdClass();
        $hlsplayer->course = $course->id;
        $hlsplayer->name = 'Test HLS Player';
        $hlsplayer->sourcetype = 'url';
        $hlsplayer->videourl = 'http://example.com/video.m3u8';
        $id = hlsplayer_add_instance($hlsplayer);

        $hlsplayer->instance = $id;
        $hlsplayer->name = 'Updated HLS Player';
        $hlsplayer->id = $id; 
        
        $result = hlsplayer_update_instance($hlsplayer);
        $this->assertTrue($result);

        global $DB;
        $db_hlsplayer = $DB->get_record('hlsplayer', ['id' => $id]);
        $this->assertEquals('Updated HLS Player', $db_hlsplayer->name);
    }

    public function test_hlsplayer_delete_instance() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $hlsplayer = new stdClass();
        $hlsplayer->course = $course->id;
        $hlsplayer->name = 'Test HLS Player';
        $hlsplayer->sourcetype = 'url';
        $hlsplayer->videourl = 'http://example.com/video.m3u8';
        $id = hlsplayer_add_instance($hlsplayer);

        $result = hlsplayer_delete_instance($id);
        $this->assertTrue($result);

        global $DB;
        $this->assertFalse($DB->record_exists('hlsplayer', ['id' => $id]));
    }
}
