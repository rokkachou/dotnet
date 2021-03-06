<?php

if ( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();
	
if ( !defined('SALON_UPLOAD_DIR') ){
	$uploads = wp_upload_dir();
	define( 'SALON_UPLOAD_DIR', $uploads['basedir'].DIRECTORY_SEPARATOR.'salon'.DIRECTORY_SEPARATOR);
}

	

function salon_delete_plugin() {
	global $wpdb;

	delete_option( 'salon_holiday' );

	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_reservation" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_sales" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_customer" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_branch" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_staff" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_working" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_position" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_item" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_log" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_photo" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_promotion" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_customer_extension" );

	$id = get_option('salon_confirm_page_id');
	if (! empty($id)  ){
		if (wp_delete_post( $id, true ) === false) error_log('delete post error ID:'.$id."\n", 3, ABSPATH.'/'.date('Y').'.txt');
	}
	delete_option('salon_confirm_page_id');
	delete_option( 'salon_installed' );
	delete_option( 'SALON_CONFIG' );
	delete_option( 'SALON_CONFIG_BRANCH' );
	delete_option( 'salon_initial_user' );

	if(file_exists(SALON_UPLOAD_DIR)){
		remove_directory(SALON_UPLOAD_DIR);
	}
}

function remove_directory($dir) {
	if ($handle = opendir("$dir")) {
		while (false !== ($item = readdir($handle))) {
			if ($item != "." && $item != "..") {
				if (is_dir("$dir/$item")) {
					remove_directory("$dir/$item");
				} else {
					unlink("$dir/$item");
				}
			}
		}
		closedir($handle);
		rmdir($dir);
	}
}

salon_delete_plugin();
