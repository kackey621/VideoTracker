<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the HLS Player module
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_hlsplayer_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // upgrades can be added here
    
    return true;
}
