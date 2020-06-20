<?php

/***************************************************************************
 *
 *	OUGC Points Activity Rewards plugin (/inc/plugins/ougc_points_activity_rewards.php)
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

define('OUGC_POINTS_ACTIVITY_REWARDS_ROOT', MYBB_ROOT . 'inc/plugins/ougc_points_activity_rewards');

require_once OUGC_POINTS_ACTIVITY_REWARDS_ROOT.'/core.php';

// Add our hooks
if(defined('IN_ADMINCP'))
{
	require_once OUGC_POINTS_ACTIVITY_REWARDS_ROOT.'/admin.php';

	require_once OUGC_POINTS_ACTIVITY_REWARDS_ROOT.'/admin_hooks.php';

	\OUGCPointsActivityRewards\Core\addHooks('OUGCPointsActivityRewards\AdminHooks');
}
else
{
	require_once OUGC_POINTS_ACTIVITY_REWARDS_ROOT.'/forum_hooks.php';

	\OUGCPointsActivityRewards\Core\addHooks('OUGCPointsActivityRewards\ForumHooks');
}

// Plugin API
function ougc_points_activity_rewards_info()
{
	return \OUGCPointsActivityRewards\Admin\_info();
}

// Activate the plugin.
function ougc_points_activity_rewards_activate()
{
	\OUGCPointsActivityRewards\Admin\_activate();
}

// Deactivate the plugin.
function ougc_points_activity_rewards_deactivate()
{
	\OUGCPointsActivityRewards\Admin\_deactivate();
}

// Install the plugin.
function ougc_points_activity_rewards_install()
{
	\OUGCPointsActivityRewards\Admin\_install();
}

// Check if installed.
function ougc_points_activity_rewards_is_installed()
{
	return \OUGCPointsActivityRewards\Admin\_is_installed();
}

// Unnstall the plugin.
function ougc_points_activity_rewards_uninstall()
{
	\OUGCPointsActivityRewards\Admin\_uninstall();
}