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
 * AMD module for the HLS Player activity.
 *
 * Initialises Video.js and handles progress tracking, seek restriction,
 * and resume functionality.
 *
 * @module     mod_hlsplayer/player
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax', 'core/log'], function(Ajax, Log) {

    /**
     * Save progress to the server via AJAX.
     *
     * @param {object} player     Video.js player instance.
     * @param {number} hlsplayerid  HLS player instance ID.
     * @param {number} maxViewedTime Maximum viewed time in seconds.
     * @param {number} currentPos   Current playback position in seconds.
     * @param {HTMLElement} display Progress percentage display element.
     */
    var saveProgress = function(player, hlsplayerid, maxViewedTime, currentPos, display) {
        var percentage = 0;
        if (player.duration() > 0) {
            percentage = Math.floor((maxViewedTime / player.duration()) * 100);
        }
        if (percentage > 100) {
            percentage = 100;
        }

        if (display) {
            display.textContent = percentage;
        }

        Ajax.call([{
            methodname: 'mod_hlsplayer_submit_progress',
            args: {
                hlsplayerid:  hlsplayerid,
                progress:     Math.floor(maxViewedTime),
                percentage:   percentage,
                lastposition: Math.floor(currentPos)
            }
        }]);
    };

    return {
        /**
         * Initialise the HLS player for a given video element.
         *
         * @param {string} playerId The HTML id of the video element.
         */
        init: function(playerId) {
            var container = document.querySelector('[data-player-id="' + playerId + '"]');
            if (!container) {
                Log.warn('mod_hlsplayer/player: container not found for player id ' + playerId);
                return;
            }

            var hlsplayerid      = parseInt(container.dataset.hlsplayerid, 10);
            var maxViewedTime    = parseInt(container.dataset.initialProgress, 10) || 0;
            var lastPosition     = parseInt(container.dataset.initialLastposition, 10) || 0;
            var allowSeeking     = container.dataset.allowseeking === '1';
            var lastValidTime    = lastPosition;
            var lastSaveTime     = 0;
            var display          = document.getElementById('progress-percentage-' + playerId);

            /* global videojs */
            if (typeof videojs === 'undefined') {
                Log.warn('mod_hlsplayer/player: Video.js is not loaded.');
                return;
            }

            var player = videojs(playerId);

            player.ready(function() {
                Log.debug('HLS Player ready. Max viewed: ' + maxViewedTime + ', Resume: ' + lastPosition);

                // Update percentage display once duration is known.
                player.on('loadedmetadata', function() {
                    if (player.duration() > 0 && maxViewedTime > 0) {
                        var pct = Math.min(100, Math.floor((maxViewedTime / player.duration()) * 100));
                        if (display) {
                            display.textContent = pct;
                        }
                    }
                });

                // Resume from last saved position.
                if (lastPosition > 0) {
                    player.currentTime(lastPosition);
                }

                // Track max viewed time and save periodically.
                player.on('timeupdate', function() {
                    var currentTime = player.currentTime();

                    // Track the furthest valid position for seek revert.
                    if (currentTime <= maxViewedTime + 2.0) {
                        lastValidTime = currentTime;
                    }

                    // Advance maxViewedTime only during normal playback (not seeking).
                    if (currentTime > maxViewedTime && (currentTime - maxViewedTime) < 1.0) {
                        maxViewedTime = currentTime;
                    }

                    // Save every 10 seconds.
                    var now = Date.now();
                    if (now - lastSaveTime > 10000) {
                        saveProgress(player, hlsplayerid, maxViewedTime, currentTime, display);
                        lastSaveTime = now;
                    }
                });

                player.on('pause', function() {
                    saveProgress(player, hlsplayerid, maxViewedTime, player.currentTime(), display);
                });

                player.on('ended', function() {
                    saveProgress(player, hlsplayerid, maxViewedTime, player.currentTime(), display);
                });

                // Restrict forward seeking if disabled.
                if (!allowSeeking) {
                    player.on('seeking', function() {
                        var currentTime = player.currentTime();
                        if (currentTime > maxViewedTime + 1) {
                            Log.debug('Seek restricted: ' + currentTime + ' -> ' + lastValidTime);
                            player.currentTime(lastValidTime);
                        }
                    });
                }
            });
        }
    };
});
