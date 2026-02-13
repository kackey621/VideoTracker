<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_hlsplayer_submit_progress' => [
        'classname' => 'mod_hlsplayer_external',
        'methodname' => 'submit_progress',
        'description' => 'Updates user progress for an HLS player activity',
        'type' => 'write',
        'ajax' => true,
    ],
];

$services = [
    'HLS Player Services' => [
        'functions' => ['mod_hlsplayer_submit_progress'],
        'requiredcapability' => 'mod/hlsplayer:view',
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];
