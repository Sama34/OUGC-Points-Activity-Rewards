<?php

/***************************************************************************
 *
 *	OUGC Points Activity Rewards plugin (/inc/plugins/ougc_points_activity_rewards/admin.php)
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

namespace OUGCPointsActivityRewards\Admin;

function _info()
{
	global $lang;

	\OUGCPointsActivityRewards\Core\load_language();

	return [
		'name'			=> 'OUGC Points Activity Rewards',
		'description'	=> $lang->setting_group_ougc_points_activity_rewards_desc,
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.0',
		'versioncode'	=> 1800,
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_points_activity_rewards',
		'pl'			=> [
			'newpoints'	=> 211,
			'version'	=> 13,
			'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
		]
	];
}

function _activate()
{
	global $PL, $lang, $cache, $db;

	\OUGCPointsActivityRewards\Core\load_pluginlibrary();

	$PL->settings('ougc_points_activity_rewards', $lang->setting_group_ougc_points_activity_rewards, $lang->setting_group_ougc_points_activity_rewards_desc, [
		'plugin' => [
			'title' => $lang->setting_ougc_points_activity_rewards_plugin,
			'description' => $lang->setting_ougc_points_activity_rewards_plugin_desc,
			'optionscode' => "select
newpoints={$lang->setting_ougc_points_activity_rewards_plugin_newpoints}",
			'value' =>	'newpoints',
		]
	]);

	// Add templates
    $templatesDirIterator = new \DirectoryIterator(OUGC_POINTS_ACTIVITY_REWARDS_ROOT.'/templates');

	$templates = [];

    foreach($templatesDirIterator as $template)
    {
		if(!$template->isFile())
		{
			continue;
		}

		$pathName = $template->getPathname();

        $pathInfo = pathinfo($pathName);

		if($pathInfo['extension'] === 'html')
		{
            $templates[$pathInfo['filename']] = file_get_contents($pathName);
		}
    }

	if($templates)
	{
		$PL->templates('ougcpointsactivityrewards', 'OUGC Points Activity Rewards', $templates);
	}

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');

	if(!$plugins)
	{
		$plugins = [];
	}

	$_info = \OUGCPointsActivityRewards\Admin\_info();

	if(!isset($plugins['pointsactivityrewards']))
	{
		$plugins['pointsactivityrewards'] = $_info['versioncode'];
	}

	_db_verify_tables();

	/*~*~* RUN UPDATES START *~*~*/

	if($plugins['pointsactivityrewards'] <= 1800)
	{
		$query = $db->simple_select('newpoints_log', '*', "action='ougc_points_activity_rewards'");
	
		while($log = $db->fetch_array($query))
		{
			$data = my_unserialize($log['data']);
	
			if(!(is_array($data) && !empty($data)))
			{
				continue;
			}

			$db->update_query(
				'newpoints_log',
				['data' => "PID: {$data['pid']}, Amount: {$data['amount']}, Points: {$data['points']}"],
				"lid='{$log['lid']}'"
			);
		}
	}

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['pointsactivityrewards'] = $_info['versioncode'];

	$cache->update('ougc_plugins', $plugins);
}

function _install()
{
	_db_verify_tables();
}

function _is_installed()
{
	global $db;

	foreach(_db_tables() as $name => $table)
	{
		$installed = $db->table_exists($name);

		break;
	}

	return $installed;
}

function _uninstall()
{
	global $db, $PL, $cache;

	\OUGCPointsActivityRewards\Core\load_pluginlibrary();

	// Drop DB entries
	foreach(_db_tables() as $name => $table)
	{
		$db->drop_table($name);
	}

	$PL->cache_delete('ougc_points_activity_rewards_packages');

	$PL->settings_delete('ougc_points_activity_rewards');

	$PL->templates_delete('ougcpointsactivityrewards');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['pointsactivityrewards']))
	{
		unset($plugins['pointsactivityrewards']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$cache->delete('ougc_plugins');
	}
}

// List of tables
function _db_tables()
{
	global $db;

	$collation = $db->build_create_table_collation();

	return [
		'ougc_points_activity_rewards_packages'	=> [
			'pid'			=> "int UNSIGNED NOT NULL AUTO_INCREMENT",
			'title'			=> "varchar(150) NOT NULL DEFAULT ''",
			'description'	=> "varchar(250) NOT NULL DEFAULT ''",
			'type'			=> "varchar(10) NOT NULL DEFAULT ''",
			'active'		=> "tinyint(5) NOT NULL DEFAULT '1'",
			'amount'		=> "int(5) NOT NULL DEFAULT '10'",
			'points'		=> "DECIMAL(16,2) NOT NULL DEFAULT '0'",
			'groups'		=> "text NULL",
			'hours'			=> "int(5) NOT NULL DEFAULT '24'",
			'primary_key'	=> "pid"
		],
		'ougc_points_activity_rewards_logs'	=> [
			'lid'			=> "int UNSIGNED NOT NULL AUTO_INCREMENT",
			'pid'			=> "int UNSIGNED NOT NULL",
			'uid'			=> "int UNSIGNED NOT NULL",
			'dateline'		=> "int(10) NOT NULL DEFAULT '0'",
			'primary_key'	=> "lid"
		]
	];
}

// Verify DB tables
function _db_verify_tables()
{
	global $db;

	$collation = $db->build_create_table_collation();

	foreach(_db_tables() as $table => $fields)
	{
		if($db->table_exists($table))
		{
			foreach($fields as $field => $definition)
			{
				if($field == 'primary_key')
				{
					continue;
				}

				if($db->field_exists($field, $table))
				{
					$db->modify_column($table, "`{$field}`", $definition);
				}
				else
				{
					$db->add_column($table, $field, $definition);
				}
			}
		}
		else
		{
			$query = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."{$table}` (";

			foreach($fields as $field => $definition)
			{
				if($field == 'primary_key')
				{
					$query .= "PRIMARY KEY (`{$definition}`)";
				}
				else
				{
					$query .= "`{$field}` {$definition},";
				}
			}

			$query .= ") ENGINE=MyISAM{$collation};";

			$db->write_query($query);
		}
	}
}