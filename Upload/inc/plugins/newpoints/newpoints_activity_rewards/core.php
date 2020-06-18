<?php

/***************************************************************************
 *
 *	Newpoints Activity Rewards plugin (/inc/plugins/newpoints/newpoints_activity_rewards/core.php)
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

namespace NewpointsActivityRewards\Core;

function load_language()
{
	isset($lang->setting_group_newpoints_activity_rewards) || newpoints_lang_load('newpoints_activity_rewards');
}

function load_pluginlibrary()
{
	global $PL, $lang;

	\NewpointsActivityRewards\Core\load_language();

	$_info = \NewpointsActivityRewards\Admin\_info();

	if($file_exists = file_exists(PLUGINLIBRARY))
	{
		global $PL;
	
		$PL or require_once PLUGINLIBRARY;
	}

	if(!$file_exists || $PL->version < $_info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->newpoints_activity_rewards_pluginlibrary, $_info['pl']['url'], $_info['pl']['version']), 'error');

		admin_redirect('index.php?module=config-plugins');
	}
}

function addHooks(string $namespace)
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

	foreach($definedUserFunctions as $callable)
	{
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

		if(substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase.'\\')
		{
            $hookName = substr_replace($callable, null, 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

			if(is_numeric(substr($hookName, -2)))
			{
                $hookName = substr($hookName, 0, -2);
			}
			else
			{
                $priority = 10;
            }

            $plugins->add_hook($hookName, $callable, $priority);
        }
    }
}

function update_cache()
{
	global $mybb, $db;

	$query = $db->simple_select('newpoints_activity_rewards', '*', "active='1' AND points>'0'");

	$packages = [];

	while($package = $db->fetch_array($query))
	{
		$packages[(int)$package['aid']] = $package;
	}

	$mybb->cache->update('newpoints_activity_rewards', $packages);
}

// Set url
function set_url($url=null)
{
	static $current_url = '';

	if(($url = trim($url)))
	{
		$current_url = $url;
	}

	return $current_url;
}

// Set url
function get_url()
{
	return set_url();
}

// Build an url parameter
function build_url($urlappend=[])
{
	global $PL;

	if(!is_object($PL))
	{
		return get_url();
	}

	if($urlappend && !is_array($urlappend))
	{
		$urlappend = explode('=', $urlappend);

		$urlappend = [$urlappend[0] => $urlappend[1]];
	}

	return $PL->url_append(get_url(), $urlappend, '&amp;', true);
}

function get_activity_count($package, &$count)
{
	global $db, $mybb;

	$interval = TIME_NOW - (60 * 60 * $package['hours']);

	$uid = (int)$mybb->user['uid'];

	switch($package['type'])
	{
		case 'post':
			$query = $db->simple_select(
				'posts p LEFT JOIN '.$db->table_prefix.'threads t ON(p.tid=t.tid)',
				'COUNT(p.pid) as total_posts',
				"p.uid='{$uid}' AND p.dateline>'{$interval}' AND p.visible='1' AND t.visible='1'"
			);
			$count = (int)$db->fetch_field($query, 'total_posts');

			break;
		case 'thread':
			$query = $db->simple_select(
				'threads',
				'COUNT(tid) as total_threads',
				"uid='{$uid}' AND dateline>'{$interval}' AND visible='1'"
			);

			$count = (int)$db->fetch_field($query, 'total_threads');
			break;
		default:
			$query = $db->simple_select(
				'reputation',
				'SUM(reputation) as total_reputation',
				"uid='{$uid}' AND dateline>'{$interval}'"
			);

			$count = (int)$db->fetch_field($query, 'total_posts');
			break;
	}
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.68
function control_object(&$obj, $code) {
	static $cnt = 0;
	$newname = '_objcont_'.(++$cnt);
	$objserial = serialize($obj);
	$classname = get_class($obj);
	$checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
	$checkstr_len = strlen($checkstr);
	if(substr($objserial, 0, $checkstr_len) == $checkstr) {
		$vars = array();
		// grab resources/object etc, stripping scope info from keys
		foreach((array)$obj as $k => $v) {
			if($p = strrpos($k, "\0"))
				$k = substr($k, $p+1);
			$vars[$k] = $v;
		}
		if(!empty($vars))
			$code .= '
				function ___setvars(&$a) {
					foreach($a as $k => &$v)
						$this->$k = $v;
				}
			';
		eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
		$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
		if(!empty($vars))
			$obj->___setvars($vars);
	}
	// else not a valid object or PHP serialize has changed
}