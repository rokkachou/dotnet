<?php
/*
 * 不動産プラグインデーターベース設定
 * @package WordPress3.8
 * @subpackage Fudousan Plugin
 * Version: 20140401-1
*/

function databaseinstallation_fudo($state){
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	include_once('fudo_database.php');

	$char = defined("DB_CHARSET") ? DB_CHARSET : "utf8";

	//テーブル area_middle_area
	$table_name = $wpdb->prefix . "area_middle_area";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$table1 = "CREATE TABLE " . $table_name . " (";
		$table1 .= "	middle_area_id int(11) NOT NULL,";
		$table1 .= "	middle_area_name varchar(8) NOT NULL,";
		$table1 .= "	PRIMARY KEY  (middle_area_id)";
		$table1 .= "	) DEFAULT CHARSET=$char;";
		$result = $wpdb->query($table1);
		dbDelta($table1);
		fudo_createdata($table_name);
	}


	//テーブル area_narrow_area
	$table_name = $wpdb->prefix . "area_narrow_area";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$table2 = "CREATE TABLE " . $table_name . " (";
		$table2 .= "	narrow_area_id int(11) NOT NULL,";
		$table2 .= "	narrow_area_name varchar(20) NOT NULL,";
		$table2 .= "	middle_area_id int(11) NOT NULL,";
		$table2 .= "	KEY narrow_middle_area_id (narrow_area_id,middle_area_id),";
		$table2 .= "	KEY middle_area_id (middle_area_id)";
		$table2 .= "	) DEFAULT CHARSET=$char;";
		$result = $wpdb->query($table2);
		dbDelta($table2);
		fudo_createdata($table_name);

		update_option("fudo_area_db_version", "20120303");
	}


	//テーブル train_area_rosen
	$table_name = $wpdb->prefix . "train_area_rosen";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$table3 = "CREATE TABLE " . $table_name . " (";
		$table3 .= "	middle_area_id int(11) NOT NULL,";
		$table3 .= "	rosen_id int(11) NOT NULL,";
		$table3 .= "	KEY  middle_area_id (middle_area_id),";
		$table3 .= "	KEY  rosen_id (rosen_id)";
		$table3 .= "	) DEFAULT CHARSET=$char;";
		$result = $wpdb->query($table3);
		dbDelta($table3);
		fudo_createdata($table_name);
	}


	//テーブル train_rosen
	$table_name = $wpdb->prefix . "train_rosen";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$table4 = "CREATE TABLE " . $table_name . " (";
		$table4 .= "	rosen_id int(11) NOT NULL,";
		$table4 .= "	rosen_name varchar(32) NOT NULL,";
		$table4 .= "	PRIMARY KEY  (rosen_id) ,";
		$table4 .= "	KEY  rosen_name (rosen_name)";
		$table4 .= "	) DEFAULT CHARSET=$char;";
		$result = $wpdb->query($table4);
		dbDelta($table4);
		fudo_createdata($table_name);
	}


	//テーブル train_station
	$table_name = $wpdb->prefix . "train_station";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$table5 = "CREATE TABLE " . $table_name . " (";
		$table5 .= "	rosen_id int(11) NOT NULL,";
		$table5 .= "	station_id int(11) NOT NULL,";
		$table5 .= "	station_name varchar(32) NOT NULL,";
		$table5 .= "	station_ranking int(11) NOT NULL,";
		$table5 .= "	middle_area_id int(11) NOT NULL,";
		$table5 .= "	PRIMARY KEY  (rosen_id,station_id) ,";
		$table5 .= "	KEY rosen_id (rosen_id) ,";
		$table5 .= "	KEY station_id (station_id)";
		$table5 .= "	) DEFAULT CHARSET=$char;";
		$result = $wpdb->query($table5);
		dbDelta($table5);
		fudo_createdata($table_name);

		update_option("fudo_train_db_version", "20140401-1");
	}


}
?>
