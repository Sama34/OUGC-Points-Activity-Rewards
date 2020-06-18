<?php

/***************************************************************************
 *
 *	Newpoints Activity Rewards plugin (/inc/plugins/newpoints/newpoints_activity_rewards.php)
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
 
// Die if IN_MYBB is not defined, for security reasons.
if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}

if(defined('THIS_SCRIPT') && THIS_SCRIPT == 'newpoints.php')
{
	global $templatelist;

	if(!isset($templatelist))
	{
		$templatelist = '';
	}

	$templatelist .= ',newpointsactivityrewards_menu, newpointsactivityrewards_package, newpointsactivityrewards';
}

define('NEWPOINTS_ACTIVITY_REWARDS_ROOT', MYBB_ROOT . 'inc/plugins/newpoints/newpoints_activity_rewards');

require_once NEWPOINTS_ACTIVITY_REWARDS_ROOT.'/core.php';

// Add our hooks
if(defined('IN_ADMINCP'))
{
	require_once NEWPOINTS_ACTIVITY_REWARDS_ROOT.'/admin.php';

	require_once NEWPOINTS_ACTIVITY_REWARDS_ROOT.'/admin_hooks.php';

	\NewpointsActivityRewards\Core\addHooks('NewpointsActivityRewards\AdminHooks');
}
else
{
	require_once NEWPOINTS_ACTIVITY_REWARDS_ROOT.'/forum_hooks.php';

	\NewpointsActivityRewards\Core\addHooks('NewpointsActivityRewards\ForumHooks');
}

// Plugin API
function newpoints_activity_rewards_info()
{
	return \NewpointsActivityRewards\Admin\_info();
}

// Activate the plugin.
function newpoints_activity_rewards_activate()
{
	\NewpointsActivityRewards\Admin\_activate();
}

// Deactivate the plugin.
function newpoints_activity_rewards_deactivate()
{
	\NewpointsActivityRewards\Admin\_deactivate();
}

// Install the plugin.
function newpoints_activity_rewards_install()
{
	\NewpointsActivityRewards\Admin\_install();
}

// Check if installed.
function newpoints_activity_rewards_is_installed()
{
	return \NewpointsActivityRewards\Admin\_is_installed();
}

// Unnstall the plugin.
function newpoints_activity_rewards_uninstall()
{
	\NewpointsActivityRewards\Admin\_uninstall();
}