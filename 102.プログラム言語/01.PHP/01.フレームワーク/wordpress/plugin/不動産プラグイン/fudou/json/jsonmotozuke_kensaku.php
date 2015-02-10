<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress3.9
 * @subpackage Fudousan Plugin
 * Version: 1.4.5
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', false);

/** Loads the WordPress Environment and Template */
require_once '../../../../wp-blog-header.php';

//$wpdb->show_errors();

	global $wpdb;

	status_header( 200 );
	header("Content-Type: text/plain; charset=utf-8");
	header("X-Content-Type-Options: nosniff");


	$shu_data = isset($_POST['shu']) ? myIsNum_f($_POST['shu']) : '';
	if(empty($shu_data))
		$shu_data = isset($_GET['shu']) ? myIsNum_f($_GET['shu']) : '';

	if($shu_data == '1') 
		$shu_data = '< 3000' ;
	if($shu_data == '2') 
		$shu_data = '> 3000' ;

	if(intval($shu_data) > 3) 
		$shu_data = '= ' .$shu_data ;


	if( !empty($shu_data) ){

		$sql = "SELECT DISTINCT PM_16.meta_value AS motozukemei";
		$sql .=  " FROM (($wpdb->posts AS P";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_16 ON P.ID = PM_16.post_id)";
		$sql .=  " WHERE P.post_type ='fudo' ";
		$sql .=  " AND PM_1.meta_key='bukkenshubetsu'";
		$sql .=  " AND CAST( PM_1.meta_value AS SIGNED ) ".$shu_data."";
		$sql .=  " AND PM_16.meta_key='motozukemei' AND PM_16.meta_value != '' ";
		$sql .=  " ORDER BY PM_16.meta_value";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );

		$rstCount = 0;
		$GetDat = '';
		if(!empty($metas)) {
		
			foreach ( $metas as $meta ) {

				if ($rstCount==1){
					$GetDat = $GetDat. ",";
				}

				$motozukemei = $meta['motozukemei'];

				$GetDat = $GetDat . "{'id':'".$motozukemei."','name':'".$motozukemei."'}";
				$rstCount=1;

			}
			$SetDat = "{'motozuke':[".$GetDat."]}";

		}
		echo $SetDat;
	}
//$wpdb->print_error();
