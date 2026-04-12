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
 * Japanese language strings for the HLS Player module.
 *
 * @package    mod_hlsplayer
 * @copyright  2025 hlsplayer contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename']        = 'HLSプレイヤー';
$string['modulename_help']   = 'HLSプレイヤーモジュールを使用すると、m3u8ビデオストリームをコースに直接埋め込むことができます。URLを指定するか、ファイルをアップロードできます。';
$string['modulenameplural']  = 'HLSプレイヤー';
$string['pluginname']        = 'HLSプレイヤー';
$string['pluginadministration'] = 'HLSプレイヤー管理';
$string['hlsplayer:addinstance'] = '新しいHLSプレイヤーを追加する';
$string['hlsplayer:view']    = 'HLSプレイヤーを表示する';
$string['name']              = '名前';
$string['content']           = 'ビデオコンテンツ';
$string['sourcetype']        = 'ソースタイプ';
$string['sourcetype_url']    = '外部URL';
$string['sourcetype_file']   = 'アップロードされたファイル';
$string['videourl']          = 'ビデオURL (.m3u8)';
$string['videofile']         = 'ビデオファイル (.m3u8)';
$string['novideo']           = '有効なビデオソースが見つかりません。アクティビティ設定を構成してください。';
$string['error_invalidurl']  = 'URLは .m3u8 で終わる必要があります';
$string['allowspeeds']       = '再生速度の調整を許可する';
$string['allowseeking']      = 'シークを許可する';
$string['allowseeking_help'] = '有効にすると、学生はビデオ内を自由にシークできます。無効にすると、すでに視聴したポイントより先にシークすることはできません。';
$string['viewreport']        = '進捗レポートを表示';
$string['hlsplayer:viewreport'] = 'HLSプレイヤーの進捗レポートを表示';
$string['progress']          = '進捗';
$string['lastaccess']        = '最終アクセス';
$string['report']            = 'レポート';
$string['completion']        = '完了要件';
$string['completionminview'] = '視聴率を要求する';
$string['completionminview_help'] = '学生はアクティビティを完了するために、少なくともこの割合までビデオを視聴する必要があります。';
$string['completionminview_desc'] = '活動を完了するには、{$a}%の視聴が必要です。';
$string['yourprogress']      = 'あなたの進捗';
$string['maximumchars']      = '最大 {$a} 文字';

// Events.
$string['eventcoursemoduleviewed']             = 'HLSプレイヤーを閲覧しました';
$string['eventcoursemoduleinstancelistviewed'] = 'HLSプレイヤーの一覧を閲覧しました';

// Privacy API.
$string['privacy:metadata:hlsplayer_progress']              = 'HLSビデオを視聴しているユーザーの進捗状況を保存します。';
$string['privacy:metadata:hlsplayer_progress:userid']       = 'ユーザーID。';
$string['privacy:metadata:hlsplayer_progress:progress']     = '最大視聴時間（秒）。';
$string['privacy:metadata:hlsplayer_progress:percentage']   = '最大視聴率。';
$string['privacy:metadata:hlsplayer_progress:lastposition'] = '前回の再生位置のタイムスタンプ。';
$string['privacy:metadata:hlsplayer_progress:timemodified'] = '進捗状況が最後に更新された時刻。';
