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


	if(!empty($shu_data)) {

		//県
		$sql  =  "SELECT DISTINCT MA.middle_area_name, MA.middle_area_id";
		$sql .=  " FROM (($wpdb->posts as P";
		$sql .=  " INNER JOIN $wpdb->postmeta as PM   ON P.ID = PM.post_id) ";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
		$sql .=  " INNER JOIN ".$wpdb->prefix."area_middle_area as MA ON CAST( LEFT(PM.meta_value,2) AS SIGNED ) = MA.middle_area_id";
		$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
		$sql .=  " AND PM.meta_key='shozaichicode' ";
		$sql .=  " AND PM_1.meta_key='bukkenshubetsu'";
		$sql .=  " AND CAST( PM_1.meta_value AS SIGNED ) ".$shu_data."";
		$sql .=  " ORDER BY CAST( PM.meta_value AS SIGNED )";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );

		$rstCount = 0;
		$GetDat = '';
		if(!empty($metas)) {
		
			foreach ( $metas as $meta ) {

				if ($rstCount==1){
					$GetDat = $GetDat. ",";
				}

				$meta_id = $meta['middle_area_id'];
				$meta_id = sprintf("%02d",$meta_id);

				$meta_valu = $meta['middle_area_name'];

				$GetDat = $GetDat . "{'id':'".$meta_id."','name':'".$meta_valu."'}";
				$rstCount=1;

			}
			$SetDat = "{'ken':[".$GetDat."]}";


		}else{
			$SetDat = "{'ken':'','Err':'Err'}";
		}

	}else{
		$SetDat = "{'ken':'','Err':'Err'}";
	}

	echo $SetDat;


//$wpdb->print_error();
