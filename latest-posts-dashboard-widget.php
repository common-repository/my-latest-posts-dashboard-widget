<?php

/*
Plugin Name: My Latest Posts Dashboard Widget
Plugin URI: http://wiflufu.wordpress.com
Description: Displays a customisable dashboard widget containing recent posts by the current user
Version: 0.2
Author: Duncan Brown
Author URI: http://wiflufu.wordpress.com
License: GPLv2
*/


/*  Copyright 2011  Duncan Brown  (email : duncanjbrown@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

#error_reporting(E_ALL);
#ini_set('display_errors', '1');


register_activation_hook( plugin_basename( __FILE__ ),'db_lpdw_activate');

function db_lpdw_activate() {

 	$user = wp_get_current_user();

	$user_id =  $user->ID;

	$blank_values = array();

	//initialise the options key in the usermeta table with blank array as value
	if(!get_user_meta( $user_id, 'db_lpdw_options')) {
		add_user_meta( $user_id, 'db_lpdw_options', $blank_values );
	}
	
	//version check
	$version_failure_message = "Latest Posts Dashboard Widget requires Wordpress 3.0";
	global $wp_version;
	if( version_compare( $wp_version, '3.0', '<') ) {
		deactivate_plugins( basename( __FILE__ ) );
		exit( $version_failure_message );
	}

}

if ( is_admin() ) { //only load the plugin if we're in the admin area	

	require_once( dirname(__FILE__) . '/includes/db-lpdw-core.php' );

}


?>
