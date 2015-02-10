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

	status_header( 200 );
	header("Content-Type: text/plain; charset=utf-8");
	header("X-Content-Type-Options: nosniff");

	global $wpdb;

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


	if(!empty($shozaichiken_data)) {

		$sql = "SELECT DTR.rosen_id, DTR.rosen_name";
		$sql = $sql . " FROM ".$wpdb->prefix."train_area_rosen AS DTAR";
		$sql = $sql . " INNER JOIN ".$wpdb->prefix."train_rosen AS DTR ON DTAR.rosen_id = DTR.rosen_id";
		$sql = $sql . " WHERE DTAR.middle_area_id in (".$shozaichiken_data.")";
		$sql = $sql . " ORDER BY DTR.rosen_name";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );

		$rstCount = 0;
		$GetDat = '';
		if(!empty($metas)) {
		
			foreach ( $metas as $meta ) {

				if ($rstCount==1){
					$GetDat = $GetDat. ",";
				}

				$meta_id = $meta['rosen_id'];
				$meta_valu = $meta['rosen_name'];

				$GetDat = $GetDat . "{'id':'".$meta_id."','name':'".$meta_valu."'}";
				$rstCount=1;

			}
			$SetDat = "{'rosen':[".$GetDat."]}";


		}else{
			$SetDat = "{'rosen':'','Err':'Err'}";
		}
	}else{
		$SetDat = "{'rosen':'','Err':'Err'}";
	}

	echo $SetDat;


//$wpdb->print_error();
