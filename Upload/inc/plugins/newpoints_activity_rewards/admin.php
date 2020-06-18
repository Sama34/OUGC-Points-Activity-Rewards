<?php

/***************************************************************************
 *
 *	Newpoints Activity Rewards plugin (/inc/plugins/newpoints/newpoints_activity_rewards/admin.php)
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

namespace NewpointsActivityRewards\Admin;

function _info()
{
	global $lang;

	\NewpointsActivityRewards\Core\load_language();

	return [
		'name'			=> 'Newpoints Activity Rewards',
		'description'	=> $lang->setting_group_newpoints_activity_rewards_desc,
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.0',
		'versioncode'	=> 1800,
		'compatibility'	=> '21*',
		'codename'		=> 'newpoints_activity_rewards',
		'pl'			=> [
			'version'	=> 13,
			'url'		=> 'https://community.mybb.com/mods.php?action=view&pid=573'
		]
	];
}

function _activate()
{
	global $PL, $lang, $cache;

	\NewpointsActivityRewards\Core\load_pluginlibrary();

	$PL->settings('newpoints_activity_rewards', $lang->setting_group_newpoints_activity_rewards, $lang->setting_group_newpoints_activity_rewards_desc, [
		'debug' => [
			'title' => $lang->setting_newpoints_activity_rewards_debug,
			'description' => $lang->setting_newpoints_activity_rewards_debug_desc,
			'optionscode' => 'onoff',
			'value' =>	0,
		]
	]);

	// Add templates
    $templatesDirIterator = new \DirectoryIterator(NEWPOINTS_ACTIVITY_REWARDS_ROOT.'/templates');

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
		$PL->templates('newpointsactivityrewards', 'Newpoints Activity Rewards', $templates);
	}

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');

	if(!$plugins)
	{
		$plugins = [];
	}

	$_info = \NewpointsActivityRewards\Admin\_info();

	if(!isset($plugins['newpoints_activity_rewards']))
	{
		$plugins['newpoints_activity_rewards'] = $_info['versioncode'];
	}

	_db_verify_tables();

	_db_verify_columns();

	_db_verify_indexes();

	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

	//find_replace_templatesets('usercp_nav_misc', '#'.preg_quote('').'#', '');
	//find_replace_templatesets('stats', '#'.preg_quote('').'#', "");

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['newpoints_activity_rewards'] = $_info['versioncode'];

	$cache->update('ougc_plugins', $plugins);
}

function _deactivate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';

	//find_replace_templatesets('usercp_nav_misc', '#'.preg_quote('').'#i', '', 0);
	//find_replace_templatesets('stats', '#'.preg_quote('').'#i', '', 0);
}

function _install()
{
	_db_verify_tables();

	_db_verify_columns();

	_db_verify_indexes();
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

	\NewpointsActivityRewards\Core\load_pluginlibrary();

	// Drop DB entries
	foreach(_db_tables() as $name => $table)
	{
		$db->drop_table($name);
	}

	foreach(_db_columns() as $table => $columns)
	{
		foreach($columns as $name => $definition)
		{
			!$db->field_exists($name, $table) || $db->drop_column($table, $name);
		}
	}

	$PL->cache_delete('newpoints_activity_rewards');

	$PL->settings_delete('newpoints_activity_rewards');

	$PL->templates_delete('newpointsactivityrewards');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['newpoints_activity_rewards']))
	{
		unset($plugins['newpoints_activity_rewards']);
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
		'newpoints_activity_rewards'	=> [
			'aid'			=> "int UNSIGNED NOT NULL AUTO_INCREMENT",
			'title'			=> "varchar(150) NOT NULL DEFAULT ''",
			'description'	=> "varchar(250) NOT NULL DEFAULT ''",
			'type'			=> "varchar(10) NOT NULL DEFAULT ''",
			'active'		=> "tinyint(5) NOT NULL DEFAULT '1'",
			'amount'		=> "int(5) NOT NULL DEFAULT '10'",
			'points'		=> "DECIMAL(16,2) NOT NULL DEFAULT '0'",
			'groups'		=> "text NULL",
			'hours'			=> "int(5) NOT NULL DEFAULT '24'",
			'primary_key'	=> "aid"
		],
		'newpoints_activity_rewards_log'	=> [
			'lid'			=> "int UNSIGNED NOT NULL AUTO_INCREMENT",
			'aid'			=> "int UNSIGNED NOT NULL",
			'uid'			=> "int UNSIGNED NOT NULL",
			'dateline'		=> "int(10) NOT NULL DEFAULT '0'",
			'primary_key'	=> "lid"
		]
	];
}

// List of columns
function _db_columns()
{
	return [
		'users'	=> [
		],
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

// Verify DB columns
function _db_verify_columns()
{
	global $db;

	foreach(_db_columns() as $table => $columns)
	{
		foreach($columns as $field => $definition)
		{
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
}

// Verify DB indexes
function _db_verify_indexes()
{
}