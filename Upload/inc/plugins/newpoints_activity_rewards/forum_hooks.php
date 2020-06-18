<?php

/***************************************************************************
 *
 *	Newpoints Activity Rewards plugin (/inc/plugins/newpoints/newpoints_activity_rewards/forum_hooks.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2020 Omar Gonzalez
 *
 *	Website: https://ougc.network
 *
 *	Allow users to request points rewards in exchange of activity.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

namespace NewpointsActivityRewards\ForumHooks;

function newpoints_default_menu(&$menu)
{
	global $mybb, $lang, $templates;

	\NewpointsActivityRewards\Core\load_language();

	$menu[] = "{$i}<a href=\"{$mybb->settings['bburl']}/newpoints.php?action=activity_rewards\">Coupon Codes</a>";

	$i = $mybb->get_input('action') == 'activity_rewards' ? '&raquo; ' : '';

	$menu[] = eval($templates->render('newpointsactivityrewards_menu'));
}

function newpoints_start()
{
	global $mybb;

	if($mybb->get_input('action') != 'activity_rewards')
	{
		return;
	}

	global $db, $lang, $theme, $header, $templates, $headerinclude, $footer, $options, $cache;

	$trow = alt_trow();

	$uid = (int)$mybb->user['uid'];

	$aid = $mybb->get_input('aid', \MyBB::INPUT_INT);

	$packages = $cache->read('newpoints_activity_rewards');

	if($mybb->request_method == "post")
	{
		verify_post_check($mybb->get_input('my_post_code'));

		if(empty($packages[$aid]) || !$packages[$aid]['active'])
		{
			error($lang->newpoints_activity_rewards_error_invalid);
		}

		$package = &$packages[$aid];

		if(!is_member($package['groups']))
		{
			error_no_permission();
		}

		\NewpointsActivityRewards\Core\get_activity_count($package, $current_count);

		$package['amount'] = (int)$package['amount'];

		if($current_count < $package['amount'])
		{
			error($lang->newpoints_activity_rewards_error_count);
		}

		$package['points'] = (float)$package['points'];

		newpoints_log('newpoints_activity_rewards', my_serialize([
			'aid' => $aid,
			'amount' => $package['amount'],
			'points' => $package['points'],
		]));

		newpoints_addpoints($mybb->user['uid'], $package['points']);

		$db->insert_query('newpoints_activity_rewards_log', [
			'aid' => $aid,
			'uid' => $uid,
			'dateline' => TIME_NOW
		]);

		redirect($mybb->settings['bburl'].'/newpoints.php?action=activity_rewards', $lang->newpoints_activity_rewards_success);
	}

	if($packages)
	{
		foreach($packages as $aid => $package)
		{
			$package['active'] = (int)$package['active'];

			if(!$package['active'] || !is_member($package['groups']))
			{
				continue;
			}

			$package['aid'] = $aid = (int)$aid;

			$package['title'] = htmlspecialchars_uni($package['title']);

			$package['description'] = htmlspecialchars_uni($package['description']);
	
			$package['amount'] = (int)$package['amount'];
	
			$package['points'] = (float)$package['points'];
	
			$package['hours'] = (int)$package['hours'];

			$lang_var = 'newpoints_activity_rewards_type_'.$package['type'];

			$type = $lang->{$lang_var};
	
			$var = $package['type']. '_package_list';

			$amount = my_number_format($package['amount']);
	
			$points = newpoints_format_points($package['points']);
	
			$hours = my_number_format($package['hours']);

			$interval = TIME_NOW - (60 * 60 * $package['hours']);

			$query = $db->simple_select('newpoints_activity_rewards_log', '*', "aid='{$aid}' AND uid='{$uid}' AND dateline>'{$interval}'");

			$logs = $db->num_rows($query);

			$disabled = '';

			$current_count = 0;

			if($logs)
			{
				$disabled = ' disabled="disabled"';

				$current_count = $amount;
			}
			else
			{
				\NewpointsActivityRewards\Core\get_activity_count($package, $current_count);

				if($current_count < $package['amount'])
				{
					$disabled = ' disabled="disabled"';
				}
			}
	
			$current_count = my_number_format($current_count);

			${$var} .= eval($templates->render('newpointsactivityrewards_package'));
		}
	}

	foreach(['post', 'thread', 'rep'] as $type)
	{
		$var = $type. '_package_list';

		if(empty(${$var}))
		{
			${$var} = '';
		}
	}

	if(empty($post_package_list) && empty($thread_package_list) && empty($rep_package_list))
	{
		$post_package_list = eval($templates->render('newpointsactivityrewards_empty'));
	}

	$page = eval($templates->render('newpointsactivityrewards'));

	output_page($page);
}