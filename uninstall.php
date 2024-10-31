<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN') )
	exit();

//delete option from options table

 	$user = wp_get_current_user();

	$user_id =  $user->ID;
	
	delete_user_meta( $user_id, 'db_lpdw_options' );

?>

