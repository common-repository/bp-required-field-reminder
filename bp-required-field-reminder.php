<?php
/*
Plugin Name: BP Required Field Reminder
Description: This Plugin reminds Buddypress user when logged in, to fill all required fields
Author: nitin247
License: GPLv2 or later
Version: 1.1
Text Domain: bprfr-required-field
Network: true
*/

// Constants defined

defined( 'ABSPATH' ) || exit;

define( 'BP_REQUIRED_FIELD_VERSION', '1.0' );
define( 'BP_REQUIRED_FIELD_TEXTDOMAIN', 'bprfr-required-field' );
define('BP_REQUIRED_FIELD_PLUGIN_URL', ''.plugin_dir_url(__FILE__).'');

//Admin Menu Settings

function bprfr_get_server_protocol(){

	if (isset($_SERVER['HTTPS']) &&
		    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
		    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		  $protocol = 'https://';
		}
		else {
		  $protocol = 'http://';
		}

	return $protocol;	

}


function bprfr_required_field_launch_av() 
{
	if (is_user_logged_in()) 
	{

		$protocol = bprfr_get_server_protocol(); 

		$user_id 	= wp_get_current_user()->ID;		

		$current_url  = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$redirect_url = bprfr_required_field_redirect_av($user_id);

		if (strpos($current_url, $redirect_url) === false)
		{
			global $wpdb;

			$bp_prefix  	 = $wpdb->prefix;
			$xprofile_fields = $wpdb->get_results("SELECT count(*) AS empty_fields_count FROM {$bp_prefix}bp_xprofile_fields WHERE parent_id = 0 AND is_required = 1 AND id NOT IN (SELECT field_id FROM {$bp_prefix}bp_xprofile_data WHERE user_id = {$user_id} AND `value` IS NOT NULL AND `value` != '')");

			foreach ($xprofile_fields as $field) 
			{
				if ($field->empty_fields_count > 0)	
				{
					wp_redirect($redirect_url);
					exit;
				}
			}
		}		
	}
}

/**
 * Plugin styles
 */
function bprfr_required_field_style_av()
{	
	wp_enqueue_style('bp-required-field-css',BP_REQUIRED_FIELD_PLUGIN_URL . 'assets/style.css');
}

/**
 * Plugin notice
 */
function bprfr_required_field_notification_av() 
{
	if (is_user_logged_in()) 
	{
		$protocol = bprfr_get_server_protocol(); 
		$user_id 	= wp_get_current_user()->ID;
		$current_url  = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; exit;
		$redirect_url = bprfr_required_field_redirect_av($user_id);

		if (strpos($current_url, $redirect_url) !== false)
		{
			global $wpdb;

			$bp_prefix  	 = $wpdb->prefix;
			$xprofile_fields = $wpdb->get_results("SELECT `name` FROM {$bp_prefix}bp_xprofile_fields WHERE parent_id = 0 AND is_required = 1 AND id NOT IN (SELECT field_id FROM {$bp_prefix}bp_xprofile_data WHERE user_id = {$user_id} AND `value` IS NOT NULL AND `value` != '')");
	
			$xprofile_fields_count = count($xprofile_fields);
			if ($xprofile_fields_count > 0)
			{
				$message = '<div class="bp-message-av">' . __('Please complete your profile to continue', 'bp-required-field-notification') . ' (' . $xprofile_fields_count . __(' fields are required', 'bp-required-field-notification') . ')</div>';
				//$message .= '<ul class="bp-fields-av">';
				//$cn=1;
				$message .= '<span class="err-field">';
				$str_ma = '';
				foreach ($xprofile_fields as $field) 
				{
					//$message .= '<li><span class="point">('.$cn.')</span>' . $field->name . '</li>';
					$str_ma .= $field->name.',&nbsp;';
					//$cn++;
				}
				$str_ma = substr($str_ma,0,-7);
				$message .= $str_ma.'</span>';

				echo '<div id="bp-required-field-av"><div  class="bp-container-av">' . $message . '</div></div>';
			}	
		}	
	}
}

function bprfr_required_field_redirect_av($user_id)
{
	return bp_loggedin_user_domain() . 'profile/edit/'; 
	
}
add_action('template_redirect'		, 'bprfr_required_field_launch_av');
add_action('wp_head'			, 'bprfr_required_field_style_av');
add_action('wp_footer'			,'bprfr_required_field_notification_av');
?>
