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

	$shozaichiken_data = isset($_POST['shozaichiken']) ? esc_sql(esc_attr($_POST['shozaichiken'])) : '';
	if(empty($shozaichiken_data)) 
		$shozaichiken_data = isset($_GET['shozaichiken']) ? esc_sql(esc_attr($_GET['shozaichiken'])) : '';

	//num check
	$array_shozaichiken_data = explode( ",", $shozaichiken_data );
	$shozaichiken_data = '';
	$i = 0;
	foreach( $array_shozaichiken_data as $data ) {
		if( (int)$data ){
			if( $i > 0 ) $shozaichiken_data .= ",";
			$shozaichiken_data .= $data;
			$i++;
		}
	}

	$koutsurosen_data = isset($_POST['koutsurosen']) ? myIsNum_f($_POST['koutsurosen']) : '';
	if(empty($koutsurosen_data))
		$koutsurosen_data = isset($_GET['koutsurosen']) ? myIsNum_f($_GET['koutsurosen']) : '';


	if( !empty($shozaichiken_data) && !empty($koutsurosen_data) ){

		$sql = "SELECT DTS.station_id, DTS.station_name";
		$sql = $sql . " FROM ".$wpdb->prefix."train_station AS DTS";
		$sql = $sql . " WHERE  DTS.rosen_id=".$koutsurosen_data." AND DTS.middle_area_id in (".$shozaichiken_data.")";
		$sql = $sql . " ORDER BY DTS.station_ranking";
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
