<?php

/***************************************************************************
 *
 *    OUGC Points Activity Rewards plugin (/inc/plugins/ougc_points_activity_rewards/forum_hooks.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow users to request points rewards in exchange of activity.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

namespace OUGCPointsActivityRewards\ForumHooks;

use MyBB;

use function OUGCPointsActivityRewards\Core\get_activity_count;
use function OUGCPointsActivityRewards\Core\load_language;
use function OUGCPointsActivityRewards\Core\update_cache;

function global_start()
{
    global $templatelist;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    if (defined('THIS_SCRIPT')) {
        if (THIS_SCRIPT == 'newpoints.php') {
            $templatelist .= ',ougcpointsactivityrewards_menu, ougcpointsactivityrewards_package, ougcpointsactivityrewards';
        }
    }
}

function newpoints_default_menu(&$menu)
{
    global $mybb, $lang, $templates;

    load_language();

    $i = $mybb->get_input('action') == 'activity_rewards' ? '&raquo; ' : '';

    $menu[] = eval($templates->render('ougcpointsactivityrewards_menu'));
}

function newpoints_start()
{
    global $mybb;

    if ($mybb->get_input('action') != 'activity_rewards') {
        return;
    }

    global $db, $lang, $theme, $header, $templates, $headerinclude, $footer, $options, $cache;

    $trow = alt_trow();

    $uid = (int)$mybb->user['uid'];

    $pid = $mybb->get_input('pid', MyBB::INPUT_INT);

    $packages = $cache->read('ougc_points_activity_rewards_packages');

    $packages || $packages = $cache->read('ougc_points_activity_rewards_packages', true);

    $packages || update_cache($packages);

    if ($mybb->request_method == 'post') {
        verify_post_check($mybb->get_input('my_post_code'));

        if (empty($packages[$pid]) || !$packages[$pid]['active']) {
            error($lang->newpoints_activity_rewards_error_invalid);
        }

        $package = &$packages[$pid];

        if (!is_member($package['groups'])) {
            error_no_permission();
        }

        get_activity_count($package, $current_count);

        $package['amount'] = (int)$package['amount'];

        if ($current_count < $package['amount']) {
            error($lang->ougc_points_activity_rewards_error_count);
        }

        $interval = TIME_NOW - (60 * 60 * $package['hours']);

        $query = $db->simple_select(
            'ougc_points_activity_rewards_logs',
            '*',
            "pid='{$pid}' AND uid='{$uid}' AND dateline>='{$interval}'"
        );

        if ($db->num_rows($query)) {
            error_no_permission();
        }

        $package['points'] = (float)$package['points'];

        newpoints_log(
            'ougc_points_activity_rewards',
            "PID: {$pid}, Amount: {$package['amount']}, Points: {$package['points']}"
        );

        newpoints_addpoints($mybb->user['uid'], $package['points']);

        $db->insert_query('ougc_points_activity_rewards_logs', [
            'pid' => $pid,
            'uid' => $uid,
            'dateline' => TIME_NOW
        ]);

        redirect(
            $mybb->settings['bburl'] . '/newpoints.php?action=activity_rewards',
            $lang->ougc_points_activity_rewards_success
        );
    }

    if ($packages) {
        foreach ($packages as $pid => $package) {
            $package['active'] = (int)$package['active'];

            if (!$package['active'] || !is_member($package['groups'])) {
                continue;
            }

            $package['pid'] = $pid = (int)$pid;

            $package['title'] = htmlspecialchars_uni($package['title']);

            $package['description'] = htmlspecialchars_uni($package['description']);

            $package['amount'] = (int)$package['amount'];

            $package['points'] = (float)$package['points'];

            $package['hours'] = (int)$package['hours'];

            $lang_var = 'ougc_points_activity_rewards_type_' . $package['type'];

            $type = $lang->{$lang_var};

            $var = $package['type'] . '_package_list';

            $amount = my_number_format($package['amount']);

            $points = newpoints_format_points($package['points']);

            $hours = my_number_format($package['hours']);

            $interval = TIME_NOW - (60 * 60 * $package['hours']);

            $query = $db->simple_select(
                'ougc_points_activity_rewards_logs',
                '*',
                "pid='{$pid}' AND uid='{$uid}' AND dateline>='{$interval}'"
            );

            $logs = $db->num_rows($query);

            $disabled = '';

            $current_count = 0;

            get_activity_count($package, $current_count);

            if ($logs) {
                $disabled = ' disabled="disabled"';

                //$current_count = $amount;
                //$current_count = 0;

                if ($current_count >= $package['amount']) {
                    $current_count -= $package['amount'];
                }
            } elseif ($current_count < $package['amount']) {
                $disabled = ' disabled="disabled"';
            } else {
                $current_count = $package['amount'];
            }

            $current_count = my_number_format($current_count);

            ${$var} .= eval($templates->render('ougcpointsactivityrewards_package'));
        }
    }

    foreach (['post', 'thread', 'rep'] as $type) {
        $var = $type . '_package_list';

        if (empty(${$var})) {
            ${$var} = '';
        }
    }

    if (empty($post_package_list) && empty($thread_package_list) && empty($rep_package_list)) {
        $post_package_list = eval($templates->render('ougcpointsactivityrewards_empty'));
    }

    $page = eval($templates->render('ougcpointsactivityrewards'));

    output_page($page);

    exit;
}