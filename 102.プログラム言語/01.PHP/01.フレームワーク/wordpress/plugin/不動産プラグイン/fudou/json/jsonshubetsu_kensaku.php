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
	global $work_setsubi;
	
	$setsubi_dat = '';

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
	$set_id = isset($_POST['set']) ? $_POST['set'] : '';
	if(empty($set_id))
		$set_id = isset($_GET['set']) ? $_GET['set'] : '';

	//num check
	$array_set_id = $set_id;
	$set_id = array();
	if(!empty($array_set_id)){
		foreach( $array_set_id as $data ) {
			if( (int)$data ){
				$set_id[] = $data;
			}
		}
	}


	if( !empty($shu_data) ){

		//設備・条件
		$widget_seach_setsubi = maybe_unserialize( get_option('widget_seach_setsubi') );

		$sql  =  "SELECT DISTINCT PM.meta_value as setsubi";
		$sql .=  " FROM (($wpdb->posts as P";
		$sql .=  " INNER JOIN $wpdb->postmeta as PM   ON P.ID = PM.post_id) ";
		$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
		$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
		$sql .=  " AND PM_1.meta_key='bukkenshubetsu'";
		$sql .=  " AND CAST( PM_1.meta_value AS SIGNED ) ".$shu_data."";
		$sql .=  " AND PM.meta_key='setsubi'";
		$sql .=  " ORDER BY CAST( PM.meta_value AS SIGNED )";

	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );
		$array_setsubi = array();

		if(!empty($metas)) {
			foreach($work_setsubi as $meta_box){

				foreach ( $metas as $meta ) {
					$setsubi_data = $meta['setsubi'];

					if( strpos($setsubi_data,$meta_box['code']) ){
						//echo $meta_box['code'].' ';
						//echo $meta_box['name']."\n";

						$setsubi_code = $meta_box['code'];
						$setsubi_name = $meta_box['name'];

						$data = array( $setsubi_code => array("code" => $setsubi_code,"name" => $setsubi_name));

						foreach($array_setsubi as $meta_box2){
							if ( $setsubi_code == $meta_box2['code'])
								$data = '';
						}
						if(!empty($data))
						$array_setsubi = array_merge( $data , $array_setsubi);
					}
				}
			}
		}

		if(!empty($array_setsubi)) {

			krsort($array_setsubi);
			$setsubi_dat ='<span class="jsearch_setsubi">設備・条件</span><br />';

			foreach($array_setsubi as $meta_box3){

				//$widget_seach_setsubi
				if(is_array($widget_seach_setsubi)) {
					$k=0;
					foreach($widget_seach_setsubi as $meta_box5){
						if($widget_seach_setsubi[$k] == $meta_box3['code']){

							$setsubi_dat .= '<span style="display: inline-block">';
							$setsubi_dat .= '<input type="checkbox" name="set[]"  value="'.$meta_box3['code'].'" id="'.$meta_box3['code'].'"';
								if(is_array($set_id)) {
									foreach($set_id as $meta_box4)
										if( $meta_box4 == $meta_box3['code'] ) $setsubi_dat .= ' checked="checked"';
								}
							$setsubi_dat .= '">';
							$setsubi_dat .= '<label for="'.$meta_box3['code'].'">'.$meta_box3['name'].'</label>';
							$setsubi_dat .= '</span>';


						}
						$k++;
					}
				}else{


							$setsubi_dat .= '<span style="display: inline-block">';
							$setsubi_dat .= '<input type="checkbox" name="set[]"  value="'.$meta_box3['code'].'" id="'.$meta_box3['code'].'"';
								if(is_array($set_id)) {
									foreach($set_id as $meta_box4)
										if( $meta_box4 == $meta_box3['code'] ) $setsubi_dat .= ' checked="checked"';
								}
							$setsubi_dat .= '">';
							$setsubi_dat .= '<label for="'.$meta_box3['code'].'">'.$meta_box3['name'].'</label>';
							$setsubi_dat .= '</span>';


				}

			}
		}
		echo $setsubi_dat;
	}

//$wpdb->print_error();
