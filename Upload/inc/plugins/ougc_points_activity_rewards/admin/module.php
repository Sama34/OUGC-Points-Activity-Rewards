<?php

/***************************************************************************
 *
 *	OUGC Points Activity Rewards plugin (/inc/plugins/ougc_points_activity_rewards/admin/module.php)
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
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// We reset these here..
$modules_dir = $ougc_modules_dir;
$run_module = $ougc_run_module;

\OUGCPointsActivityRewards\Core\set_url('index.php?module=newpoints-activity_rewards');

\OUGCPointsActivityRewards\Core\load_language();

\OUGCPointsActivityRewards\Core\load_pluginlibrary();

$pid = $mybb->get_input('pid', MyBB::INPUT_INT);

// Page tabs
$sub_tabs['ougc_points_activity_rewards'] = [
	'title'			=> $lang->ougc_points_activity_rewards_admin,
	'link'			=> \OUGCPointsActivityRewards\Core\get_url(),
	'description'	=> $lang->ougc_points_activity_rewards_admin_desc
];

$sub_tabs['ougc_points_activity_rewards_add'] = [
    'title'			=> $lang->ougc_points_activity_rewards_admin_add,
    'link'			=> \OUGCPointsActivityRewards\Core\build_url(['action' => 'add']),
    'description'	=> $lang->ougc_points_activity_rewards_admin_add_desc
];

if($mybb->get_input('action') == 'edit')
{
    $sub_tabs['ougc_points_activity_rewards_edit'] = [
        'title'			=> $lang->ougc_points_activity_rewards_admin_edit,
        'link'			=> \OUGCPointsActivityRewards\Core\build_url(['action' => 'edit', 'pid' => $pid]),
        'description'	=> $lang->ougc_points_activity_rewards_admin_edit_desc
    ];
}

$page->add_breadcrumb_item($lang->ougc_socialauth, $sub_tabs['ougc_socialauth_view']['link']);

if($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
{
    $add = $mybb->get_input('action') == 'add';

    if($add)
    {

    }
    else
    {
        $query = $db->simple_select('ougc_points_activity_rewards_packages', '*', "pid='{$pid}'");
    
        if(!($package = $db->fetch_array($query)))
        {
            admin_redirect(\OUGCPointsActivityRewards\Core\get_url());
        }
    }

    $_sub_tab = 'ougc_points_activity_rewards_'.($add ? 'add' : 'edit');

    $page->add_breadcrumb_item($sub_tabs[$_sub_tab]['title'], $sub_tabs[$_sub_tab]['link']);

    $page->output_header($lang->ougc_socialauth_tab_edit);

    $page->output_nav_tabs($sub_tabs, $_sub_tab);

	if($mybb->request_method == 'post')
	{
        $update_data = $errors = [];

        foreach(['title', 'description', 'type', 'points', 'amount', 'hours'] as $key)
        {
            if(empty($mybb->input[$key]))
            {
                $lang_var = 'ougc_points_activity_rewards_admin_error_'.$key;

                $errors[] = $lang->$lang_var;
            }
        }

        foreach(['title', 'description', 'groups', 'type'] as $key)
        {
            if(isset($mybb->input[$key]))
            {
                $update_data[$key] = $db->escape_string($mybb->input[$key]);
            }
        }

        foreach(['points'] as $key)
        {
            $update_data[$key] = (float)$mybb->input[$key];
        }

        foreach(['active', 'amount', 'hours'] as $key)
        {
            if(isset($mybb->input[$key]))
            {
                $update_data[$key] = (int)$mybb->input[$key];
            }
        }

        if(isset($mybb->input['type']) && !in_array($mybb->input['type'], ['post', 'thread', 'rep']))
        {
            $errors[] = $lang->ougc_points_activity_rewards_admin_error_type;
        }

        if($errors)
        {
            $page->output_inline_error($errors);
        }
        else
        {
            if($add)
            {
                $pid = $db->insert_query('ougc_points_activity_rewards_packages', $update_data);
            }
            else
            {
                $db->update_query('ougc_points_activity_rewards_packages', $update_data, "pid='{$pid}'");
            }

            \OUGCPointsActivityRewards\Core\update_cache();
    
            if($add)
            {
                flash_message($lang->ougc_points_activity_rewards_admin_success_add, 'success');
            }
            else
            {
                flash_message($lang->ougc_points_activity_rewards_admin_success_edit, 'success');
            }
    
            admin_redirect(\OUGCPointsActivityRewards\Core\build_url(['action' => 'edit', 'pid' => $pid]));
        }
    }
    else
    {
        foreach(['title', 'description', 'type', 'active', 'amount', 'points', 'groups', 'hours'] as $key)
        {
            if(!isset($mybb->input[$key]))
            {
                $mybb->input[$key] = $package[$key];
            }

            $mybb->input[$key] = htmlspecialchars_uni($mybb->input[$key]);
        }
    }

    $form = new Form(\OUGCPointsActivityRewards\Core\build_url(['action' => $add ? 'add' : 'edit', 'pid' => $pid]), 'post');

    $form_container = new FormContainer($sub_tabs[$_sub_tab]['title']);

    foreach(['title', 'description', 'points', 'groups'] as $key)
    {
        $lang_var = 'ougc_points_activity_rewards_admin_'.$key;
        $lang_var_desc = $lang_var.'_desc';

        $form_container->output_row(
            $lang->{ $lang_var},
            $lang->{ $lang_var_desc},
            $form->generate_text_box($key, $mybb->get_input($key, MyBB::INPUT_STRING)
        ));
    }

    foreach(['type'] as $key)
    {
        $lang_var = 'ougc_points_activity_rewards_admin_'.$key;
        $lang_var_desc = $lang_var.'_desc';

        $form_container->output_row(
            $lang->{ $lang_var},
            $lang->{ $lang_var_desc},
            $form->generate_select_box($key,  [
                'post' => $lang->ougc_points_activity_rewards_admin_type_post,
                'thread' => $lang->ougc_points_activity_rewards_admin_type_thread,
                'rep' => $lang->ougc_points_activity_rewards_admin_type_rep
            ], $mybb->get_input($key, MyBB::INPUT_STRING)
        ));
    }

    foreach(['amount', 'hours'] as $key)
    {
        $lang_var = 'ougc_points_activity_rewards_admin_'.$key;
        $lang_var_desc = $lang_var.'_desc';

        $form_container->output_row(
            $lang->{ $lang_var},
            $lang->{ $lang_var_desc},
            $form->generate_numeric_field($key, $mybb->get_input($key, MyBB::INPUT_INT)
        ));
    }

    foreach(['active'] as $key)
    {
        $lang_var = 'ougc_points_activity_rewards_admin_'.$key;
        $lang_var_desc = $lang_var.'_desc';

        $form_container->output_row(
            $lang->{ $lang_var},
            $lang->{ $lang_var_desc},
            $form->generate_yes_no_radio($key, $mybb->get_input($key, MyBB::INPUT_INT)
        ));
    }

	$form_container->end();

	$form->output_submit_wrapper([
        $form->generate_submit_button($lang->ougc_points_activity_rewards_admin_save),
        $form->generate_reset_button($lang->reset)
    ]);

	$form->end();

	$page->output_footer();
}
elseif($mybb->get_input('action') == 'toggle')
{
    $pid = $mybb->get_input('pid', MyBB::INPUT_INT);

    $query = $db->simple_select('ougc_points_activity_rewards_packages', '*', "pid='{$pid}'");

    if(!($package = $db->fetch_array($query)))
    {
        flash_message($lang->ougc_points_activity_rewards_admin_error_invalid, 'error');

        admin_redirect(\OUGCPointsActivityRewards\Core\get_url());
    }

    $db->update_query('ougc_points_activity_rewards_packages', ['active' => $package['active'] ? 0 : 1], "pid='{$pid}'");

	\OUGCPointsActivityRewards\Core\update_cache();

    flash_message($lang->ougc_points_activity_rewards_admin_success_edit, 'success');

    admin_redirect(\OUGCPointsActivityRewards\Core\get_url());
}
else
{
    $page->output_header($lang->ougc_points_activity_rewards_admin);

	$page->output_nav_tabs($sub_tabs, 'ougc_points_activity_rewards');

    $table = new Table;

    $table->construct_header($lang->ougc_points_activity_rewards_admin_type, ['width' => '10%']);

    $table->construct_header($lang->ougc_points_activity_rewards_admin_title, ['width' => '25%']);

    $table->construct_header($lang->ougc_points_activity_rewards_admin_description, ['width' => '35%']);

    $table->construct_header($lang->ougc_points_activity_rewards_admin_status, ['width' => '10%', 'class' => 'align_center']);

	$table->construct_header($lang->options, ['width' => '20%', 'class' => 'align_center']);

	$query = $db->simple_select('ougc_points_activity_rewards_packages', '*', '', ['order_by' => 'type']);

    if(!$db->num_rows($query))
    {
        $table->construct_cell($lang->ougc_points_activity_rewards_admin_empty, ['colspan' => 3, 'class' => 'align_center']);

        $table->construct_row();
    }
    else
    {
        while($package = $db->fetch_array($query))
        {
            $lang_var = 'ougc_points_activity_rewards_admin_type_'.$package['type'];

            $table->construct_cell(htmlspecialchars_uni($lang->$lang_var));

            $url = \OUGCPointsActivityRewards\Core\build_url(['action' => 'edit', 'pid' => $package['pid']]);

            $table->construct_cell("<a href='{$url}'>".htmlspecialchars_uni($package['title']).'</a>');

            $table->construct_cell(htmlspecialchars_uni($package['description']));

            $table->construct_cell('<img src="styles/'.$page->style.'/images/icons/bullet_'.($package['active'] ? 'on' : 'off').'.png" /> ', ['class' => 'align_center']);

            $popup = new PopupMenu('service_'.$package['pid'], $lang->options);

            $popup->add_item($lang->edit, $url);

            $popup->add_item($lang->ougc_points_activity_rewards_admin_toggle, \OUGCPointsActivityRewards\Core\build_url(['action' => 'toggle', 'pid' => $package['pid']]));

            $table->construct_cell($popup->fetch(), ['class' => 'align_center']);

            $table->construct_row();
        } 
    }

    $table->output($sub_tabs['ougc_points_activity_rewards']['description']);

	$page->output_footer();
}

exit;