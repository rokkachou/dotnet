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


	$work_madori =
	array(	
		"0" => array("code" => "10","name" => "R"),
		"1" => array("code" => "20","name" => "K"),
		"2" => array("code" => "25","name" => "SK"),
		"3" => array("code" => "30","name" => "DK"),
		"4" => array("code" => "35","name" => "SDK"),
		"5" => array("code" => "40","name" => "LK"),
		"6" => array("code" => "45","name" => "SLK"),
		"7" => array("code" => "50","name" => "LDK"),
		"8" => array("code" => "55","name" => "SLDK")
	);


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


	//チェックボックス
	$mad_id = isset($_POST['mad']) ? $_POST['mad'] : '';
	if(empty($mad_id))
		$mad_id = isset($_GET['mad']) ?  $_GET['mad'] : '';

	//num check
	$array_mad_id = $mad_id;
	$mad_id = array();
	if(!empty($array_mad_id)){
		foreach( $array_mad_id as $data ) {
			if( (int)$data ){
				$mad_id[] = $data;
			}
		}
	}

	if(!empty($shu_data)) {

		//間取り
		$sql  =  "SELECT DISTINCT PM.meta_value AS madorisu,PM_2.meta_value AS madorisyurui";
		$sql .=  " FROM ((($wpdb->posts as P";
		$sql .=  " INNER JOIN $wpdb->postmeta as PM   ON P.ID = PM.post_id)) ";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id ";
		$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
		$sql .=  " AND PM_1.meta_key='bukkenshubetsu'";
		$sql .=  " AND CAST( PM_1.meta_value AS SIGNED ) ".$shu_data."";
		$sql .=  " AND PM.meta_key='madorisu'";
		$sql .=  " AND PM_2.meta_key='madorisyurui'";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );

		if(!empty($metas)) {

			//ソート
			foreach($metas as $key => $row1){
				$foo1[$key] = $row1["madorisu"];
				$bar1[$key] = $row1["madorisyurui"];
			}
			array_multisort($foo1,SORT_ASC,$bar1,SORT_ASC,$metas);



			$madori_dat = '<span class="jsearch_madori">間取り</span><br />';

			foreach ( $metas as $meta ) {
				$madorisu_data = $meta['madorisu'];
				$madorisyurui_data = $meta['madorisyurui'];
				$madori_code = $madorisu_data;
				$madori_code .= $madorisyurui_data;

				if( $madorisu_data < 10 ){
					foreach( $work_madori as $meta_box ){
						if( $madorisyurui_data == $meta_box['code'] ){
							$madori_dat .= '<span style="display: inline-block"><input name="mad[]" value="'.$madori_code.'" id="mad'.$madori_code.'" type="checkbox" ';

								if(is_array($mad_id)) {
									foreach($mad_id as $meta_box4)
										if( $meta_box4 == $madori_code ) $madori_dat .= ' checked="checked"';
								}

							 $madori_dat .= '/><label for="mad'.$madori_code.'">'.$madorisu_data.$meta_box['name'].'</label></span>';
						}
					}
				}
			}
		}

		if($madori_dat != '<span class="jsearch_madori">間取り</span><br />')
			echo $madori_dat;
	}


//$wpdb->print_error();
