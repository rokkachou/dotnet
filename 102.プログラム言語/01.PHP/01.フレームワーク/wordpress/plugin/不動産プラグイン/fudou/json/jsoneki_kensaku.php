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


	$koutsurosen_data =  isset($_POST['ros']) ? myIsNum_f($_POST['ros']) : '';
	if(empty($koutsurosen_data)) {
		$koutsurosen_data =  isset($_GET['ros']) ? myIsNum_f($_GET['ros']) : '';
	}


	if( !empty($shu_data) && !empty($koutsurosen_data) ){

		$sql  =  " SELECT DISTINCT PM.meta_value AS station_id ";
		$sql .=  " FROM ((( $wpdb->posts as P ";
		$sql .=  " INNER JOIN $wpdb->postmeta as PM ON P.ID = PM.post_id )";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id )";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id )";
		$sql .=  " WHERE";
		$sql .=  " (";
		$sql .=  " 	P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
		$sql .=  " 	AND PM.meta_key='koutsueki1'";
		$sql .=  " 	AND PM_1.meta_key='koutsurosen1' AND PM_1.meta_value = ".$koutsurosen_data."";
		$sql .=  " 	AND PM_2.meta_key='bukkenshubetsu' AND PM_2.meta_value ".$shu_data."";
		$sql .=  " )";
		$sql .=  " or";
		$sql .=  " (";
		$sql .=  " 	P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
		$sql .=  " 	AND PM.meta_key='koutsueki2'";
		$sql .=  " 	AND PM_1.meta_key='koutsurosen2' AND PM_1.meta_value = ".$koutsurosen_data."";
		$sql .=  " 	AND PM_2.meta_key='bukkenshubetsu' AND PM_2.meta_value ".$shu_data."";
		$sql .=  " )";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );

		if(!empty($metas)) {
			$tmp_eki = '0';
			foreach ( $metas as $meta ) {
				if($meta['station_id'] != '')
					$tmp_eki .= ','. $meta['station_id'];
			}
		}

		$sql  =  " SELECT DISTINCT DTS.station_name , DTS.station_id ";
		$sql .=  " FROM ".$wpdb->prefix."train_station as DTS";
		$sql .=  " WHERE DTS.rosen_id=".$koutsurosen_data." AND DTS.station_id in (".$tmp_eki.") ";
		$sql .=  " ORDER BY DTS.station_ranking";

	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );

		$rstCount = 0;
		$GetDat = '';
		if(!empty($metas)) {
		
			foreach ( $metas as $meta ) {

				if ($rstCount==1){
					$GetDat = $GetDat. ",";
				}

				$meta_id = $meta['station_id'];
				$meta_valu = $meta['station_name'];

				$GetDat = $GetDat . "{'id':'".$meta_id."','name':'".$meta_valu."'}";
				$rstCount=1;

			}
			$SetDat = "{'eki':[".$GetDat."]}";


		}else{
			$SetDat = "{'eki':'','Err':'Err1'}";
		}

		echo $SetDat;

	}else{
		$SetDat = "{'eki':'','Err':'Err2'}";
		echo $SetDat;
	}


//$wpdb->print_error();
