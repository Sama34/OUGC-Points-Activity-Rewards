<?php

/***************************************************************************
 *
 *	OUGC Points Activity Rewards plugin (/inc/languages/english/admin/ougc_points_activity_rewards.lang.php)
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

$l = [
	'setting_group_ougc_points_activity_rewards' => 'OUGC Points Activity Rewards',
	'setting_group_ougc_points_activity_rewards_desc' => 'Allow users to request points rewards in exchange of activity.',
	'setting_ougc_points_activity_rewards_debug' => 'Debug',
	'setting_ougc_points_activity_rewards_debug_desc' => 'debug',
	'ougc_points_activity_rewards_permission' => 'permission',
	'ougc_points_activity_rewards_admin' => 'Activity Rewards',
	'ougc_points_activity_rewards_admin_desc' => 'Manage activity rewards packages.',
	'ougc_points_activity_rewards_admin_status' => 'Status',
	'ougc_points_activity_rewards_admin_edit' => 'Edit',
	'ougc_points_activity_rewards_admin_edit_desc' => 'Edit an existing package.',
	'ougc_points_activity_rewards_admin_add' => 'Add',
	'ougc_points_activity_rewards_admin_add_desc' => 'Add a new package',
	'ougc_points_activity_rewards_admin_title' => 'Title',
	'ougc_points_activity_rewards_admin_title_desc' => 'Package title.',
	'ougc_points_activity_rewards_admin_description' => 'Description',
	'ougc_points_activity_rewards_admin_description_desc' => 'Add a short package description.',
	'ougc_points_activity_rewards_admin_type' => 'Type',
	'ougc_points_activity_rewards_admin_type_desc' => 'Select the activity type for this package.',
	'ougc_points_activity_rewards_admin_type_post' => 'Posts',
	'ougc_points_activity_rewards_admin_type_thread' => 'Threads',
	'ougc_points_activity_rewards_admin_type_rep' => 'Reputations',
	'ougc_points_activity_rewards_admin_active' => 'Active',
	'ougc_points_activity_rewards_admin_active_desc' => 'Enable or disable this package.',
	'ougc_points_activity_rewards_admin_amount' => 'Activity Amount',
	'ougc_points_activity_rewards_admin_amount_desc' => 'Amount of activity to account for.',
	'ougc_points_activity_rewards_admin_points' => 'Points',
	'ougc_points_activity_rewards_admin_points_desc' => 'Points users receive when they request this reward.',
	'ougc_points_activity_rewards_admin_groups' => 'Groups',
	'ougc_points_activity_rewards_admin_groups_desc' => 'Insert the groups allowed to request this reward. Use <code>-1</code> for all, leave empty for none.',
	'ougc_points_activity_rewards_admin_hours' => 'Hours',
	'ougc_points_activity_rewards_admin_hours_desc' => 'Hours interval for requesting this reward.',
	'ougc_points_activity_rewards_admin_error_type' => 'The selected type is invalid.',
	'ougc_points_activity_rewards_admin_error_invalid' => 'The selected package is invalid',
	'ougc_points_activity_rewards_admin_success_add' => 'The package was successfully added.',
	'ougc_points_activity_rewards_admin_success_edit' => 'The package was successfully updated.',
	'ougc_points_activity_rewards_admin_success' => '',
	'ougc_points_activity_rewards_admin_error' => '',
	'ougc_points_activity_rewards_admin_' => '',
	'ougc_points_activity_rewards_admin_empty' => 'There are currently no packages available.',
];