<?php
/**
 * The Template for displaying fudou single posts.
 *
 * Template Name: page-jyoken-b2014.php
 * 
 * @package WordPress3.9
 * @subpackage Fudousan Plugin
 * @subpackage Twenty_Fourteen
 * Version: 1.4.5
 */

//require_once '../../../../wp-blog-header.php';

get_header(); 
the_post();


//売買 賃貸
$shub = 1;
$shub_txt = ' (売買)';

$site = site_url( '/' ); 

?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content jsearch" role="main">

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header>

				<div class="entry-content">
					<?php the_content(); ?>
<?php

		//echo '<h3>物件選定'.$shub_txt.'</h3>';
		echo 'ご希望の条件を選択して下さい(複数可)<br />';

		echo '<form method="get" id="searchpage" name="searchpage" action="'.$site.'" >';
		echo '<input type="hidden" name="bukken" value="jsearch" >';
		//売買
		if( $shub == 1 ){
			echo '<input type="hidden" name="shub" value="1" >';
			$shu_data = '< 3000' ;
		}
		//賃貸
		if( $shub == 2 ){
			echo '<input type="hidden" name="shub" value="2" >';
			$shu_data = '> 3000' ;
		}

		echo '<table class="form_jsearch">';

		//種別選択
		echo '<tr>';
		echo '<th>物件種別'.$shub_txt.'</th>';
		echo '<td id="shubetsu" class="shubetsu">';

			$sql  =  " SELECT DISTINCT PM.meta_value AS bukkenshubetsu";
			$sql .=  " FROM $wpdb->posts as P ";
			$sql .=  " INNER JOIN $wpdb->postmeta as PM ON P.ID = PM.post_id ";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
			$sql .=  " AND PM.meta_key='bukkenshubetsu' ";
			$sql .=  " AND CAST( PM.meta_value AS SIGNED ) ". $shu_data ;
			$sql .=  " ORDER BY PM.meta_value";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql,  ARRAY_A );

			if(!empty($metas)) {
				echo '<ul>';
				foreach ( $metas as $meta ) {
					$bukkenshubetsu_id = $meta['bukkenshubetsu'];

					foreach($work_bukkenshubetsu as $meta_box){
						if( $bukkenshubetsu_id ==  $meta_box['id'] ){
							echo '<li>';
							echo '<input type="checkbox" name="shu[]"  value="'.$meta_box['id'].'" id="'.$meta_box['id'].'">';
							echo '<label for="'.$meta_box['id'].'">'.$meta_box['name'].'</label>';
							echo '</li>';
						}
					}
				}
				echo '</ul>';
			}
		echo '</td>';
		echo '</tr>';


		echo '<tr>';
		echo '<th>路線駅</th>';
		echo '<td id="eki" class="eki">';

			$sql  = "SELECT DISTINCT DTR.rosen_name,DTR.rosen_id,DTS.station_name, DTS.station_id ,DTS.station_ranking ";
			$sql .= " FROM ((((( $wpdb->posts as P ) ";
			$sql .= " INNER JOIN $wpdb->postmeta as PM ON P.ID = PM.post_id ) ";
			$sql .= " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id ) ";
			$sql .= " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id ) ";
			$sql .= " INNER JOIN ".$wpdb->prefix."train_rosen as DTR ON CAST( PM_1.meta_value AS SIGNED ) = DTR.rosen_id) ";
			$sql .= " INNER JOIN ".$wpdb->prefix."train_station as DTS ON DTS.rosen_id = DTR.rosen_id AND  CAST( PM.meta_value AS SIGNED ) = DTS.station_id";
			$sql .= " WHERE";
			$sql .= "  ( P.post_status='publish' ";
			$sql .= " AND P.post_password = '' ";
			$sql .= " AND P.post_type ='fudo' ";
			$sql .= " AND PM.meta_key='koutsueki1' ";
			$sql .= " AND PM_1.meta_key='koutsurosen1' ";
			$sql .= " AND PM_2.meta_key='bukkenshubetsu' ";
			$sql .= " AND PM_2.meta_value $shu_data ) ";
			$sql .= " OR ";
			$sql .= " ( P.post_status='publish' ";
			$sql .= " AND P.post_password = '' ";
			$sql .= " AND P.post_type ='fudo' ";
			$sql .= " AND PM.meta_key='koutsueki2' ";
			$sql .= " AND PM_1.meta_key='koutsurosen2' ";
			$sql .= " AND PM_2.meta_key='bukkenshubetsu' ";
			$sql .= " AND PM_2.meta_value $shu_data )";

		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql, ARRAY_A );

			if(!empty($metas)) {

				//ソート
				foreach($metas as $key => $row){
					$foo[$key] = $row["rosen_name"];
					$bar[$key] = $row["station_ranking"];
				}
				array_multisort($foo,SORT_DESC,$bar,SORT_ASC,$metas);

				$tmp_rosen_id= '';

				foreach ( $metas as $meta ) {

					$rosen_name =  $meta['rosen_name'];
					$rosen_id   =  $meta['rosen_id'];
					$station_name =  $meta['station_name'];
					$station_id   =  $meta['station_id'];

					$ros_id = sprintf('%06d', $rosen_id );

					//路線表示
					if( $tmp_rosen_id != $rosen_id){
						if( $tmp_rosen_id != '') echo "</ul>\n";
						echo '<h5>'.$rosen_name.'</h5>';
						echo '<ul>';
					}
					//駅表示
						$station_id = $ros_id . ''. sprintf('%06d', $station_id);
						echo '<li><input type="checkbox" name="re[]" value="'.$station_id.'" id="eki'.$station_id.'" />';
						echo '<label for="eki'.$station_id.'">'.$station_name.'</label></li>';
					$tmp_rosen_id   = $rosen_id;
				}
				echo "</ul>\n";
			}

		echo '</td>';
		echo '</tr>';


		//市区選択
		echo '<tr>';
		echo '<th>エリア</th>';
		echo '<td id="shiku" class="shiku">';

			//営業県
			$ken_id = '';
			for( $i=1; $i<48 ; $i++ ){
				if( get_option('ken'.$i) != ''){

					$ken_id = get_option('ken'.$i);

					$sql  =  "SELECT DISTINCT NA.narrow_area_name, LEFT(PM.meta_value,5) as middle_narrow_area_id";
					$sql .=  " FROM (($wpdb->posts as P";
					$sql .=  " INNER JOIN $wpdb->postmeta as PM   ON P.ID = PM.post_id) ";
					$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
					$sql .=  " INNER JOIN ".$wpdb->prefix."area_narrow_area as NA ON CAST( RIGHT(LEFT(PM.meta_value,5),3) AS SIGNED ) = NA.narrow_area_id";
					$sql .=  " WHERE PM.meta_key='shozaichicode' ";
					$sql .=  " AND P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
					$sql .=  " AND PM_1.meta_key='bukkenshubetsu'";
					$sql .=  " AND CAST( PM_1.meta_value AS SIGNED ) ".$shu_data."";
					$sql .=  " AND CAST( LEFT(PM.meta_value,2) AS SIGNED ) =  ". $ken_id . "";
					$sql .=  " AND NA.middle_area_id = ". $ken_id . "";
					$sql .=  " ORDER BY CAST( PM.meta_value AS SIGNED )";

				//	$sql = $wpdb->prepare($sql,'');
					$metas = $wpdb->get_results( $sql,  ARRAY_A );
					if(!empty($metas)) {

						echo '<h5>'.fudo_ken_name($i).'</h5>';
						echo '<ul>';
						foreach ( $metas as $meta ) {
							$middle_narrow_area_id = $meta['middle_narrow_area_id'];
							$narrow_area_name = $meta['narrow_area_name'];
							echo '<li>';
							echo '<input type="checkbox" name="ksik[]"  value="'.$middle_narrow_area_id.'" id="'.$middle_narrow_area_id.'">';
							echo '<label for="'.$middle_narrow_area_id.'">'.$narrow_area_name.'</label>';
							echo '</li>';
						}
						echo '</ul>';
					}

				}
			}

		echo '</td>';
		echo '</tr>';


		//築年数
		echo '<tr>';
		echo '<th>築年数</th>';
			$tik_data = '';
			echo '<td id="chikunen" class="chikunen">';
			echo '<select name="tik" id="tik">';
			echo '<option value="0">指定なし</option>';
			echo '<option value="1"';			if ($tik_data == '1') echo ' selected="selected"';			echo '>1年以内(新築)</option>';
			echo '<option value="3"';			if ($tik_data == '3') echo ' selected="selected"';			echo '>3年以内</option>';
			echo '<option value="5"';			if ($tik_data == '5') echo ' selected="selected"';			echo '>5年以内</option>';
			echo '<option value="10"';			if ($tik_data == '10') echo ' selected="selected"';			echo '>10年以内</option>';
			echo '<option value="15"';			if ($tik_data == '15') echo ' selected="selected"';			echo '>15年以内</option>';
			echo '<option value="20"';			if ($tik_data == '20') echo ' selected="selected"';			echo '>20年以内</option>';
			echo '</select>';
			echo '</td>';
		echo '</tr>';


		//価格選択
		if( $shub == 1 ){
			$kalb_data = '';
			$kahb_data = '';

		echo '<tr>';
			echo '<th>価格</th>';
			echo '<td id="kakaku" class="kakaku">';
			echo '<select name="kalb" id="kalb">';
			echo '<option value="0">下限なし</option>';
			echo '<option value="300"'; 			if ($kalb_data == '300') echo ' selected="selected"';			echo '>300万円</option>';
			echo '<option value="400"';			if ($kalb_data == '400') echo ' selected="selected"';			echo '>400万円</option>';
			echo '<option value="500"';			if ($kalb_data == '500') echo ' selected="selected"';			echo '>500万円</option>';
			echo '<option value="600"';			if ($kalb_data == '600') echo ' selected="selected"';			echo '>600万円</option>';
			echo '<option value="700"';			if ($kalb_data == '700') echo ' selected="selected"';			echo '>700万円</option>';
			echo '<option value="800"';			if ($kalb_data == '800') echo ' selected="selected"';			echo '>800万円</option>';
			echo '<option value="900"';			if ($kalb_data == '900') echo ' selected="selected"';			echo '>900万円</option>';
			echo '<option value="1000"';			if ($kalb_data == '1000') echo ' selected="selected"';			echo '>1000万円</option>';
			echo '<option value="1100"';			if ($kalb_data == '1100') echo ' selected="selected"';			echo '>1100万円</option>';
			echo '<option value="1200"';			if ($kalb_data == '1200') echo ' selected="selected"';			echo '>1200万円</option>';
			echo '<option value="1300"';			if ($kalb_data == '1300') echo ' selected="selected"';			echo '>1300万円</option>';
			echo '<option value="1400"';			if ($kalb_data == '1400') echo ' selected="selected"';			echo '>1400万円</option>';
			echo '<option value="1500"';			if ($kalb_data == '1500') echo ' selected="selected"';			echo '>1500万円</option>';
			echo '<option value="1600"';			if ($kalb_data == '1600') echo ' selected="selected"';			echo '>1600万円</option>';
			echo '<option value="1700"';			if ($kalb_data == '1700') echo ' selected="selected"';			echo '>1700万円</option>';
			echo '<option value="1800"';			if ($kalb_data == '1800') echo ' selected="selected"';			echo '>1800万円</option>';
			echo '<option value="1900"';			if ($kalb_data == '1900') echo ' selected="selected"';			echo '>1900万円</option>';
			echo '<option value="2000"';			if ($kalb_data == '2000') echo ' selected="selected"';			echo '>2000万円</option>';
			echo '<option value="3000"';			if ($kalb_data == '3000') echo ' selected="selected"';			echo '>3000万円</option>';
			echo '<option value="5000"';			if ($kalb_data == '5000') echo ' selected="selected"';			echo '>5000万円</option>';
			echo '<option value="7000"';			if ($kalb_data == '7000') echo ' selected="selected"';			echo '>7000万円</option>';
			echo '<option value="10000"';			if ($kalb_data == '10000') echo ' selected="selected"';			echo '>1億円</option>';
			echo '</select>　～　';
			echo '<select name="kahb" id="kahb">';
			echo '<option value="300"';			if ($kahb_data == '300') echo ' selected="selected"';			echo '>300万円</option>';
			echo '<option value="400"';			if ($kahb_data == '400') echo ' selected="selected"';			echo '>400万円</option>';
			echo '<option value="500"';			if ($kahb_data == '500') echo ' selected="selected"';			echo '>500万円</option>';
			echo '<option value="600"';			if ($kahb_data == '600') echo ' selected="selected"';			echo '>600万円</option>';
			echo '<option value="700"';			if ($kahb_data == '700') echo ' selected="selected"';			echo '>700万円</option>';
			echo '<option value="800"';			if ($kahb_data == '800') echo ' selected="selected"';			echo '>800万円</option>';
			echo '<option value="900"';			if ($kahb_data == '900') echo ' selected="selected"';			echo '>900万円</option>';
			echo '<option value="1000"';			if ($kahb_data == '1000') echo ' selected="selected"';			echo '>1000万円</option>';
			echo '<option value="1100"';			if ($kahb_data == '1100') echo ' selected="selected"';			echo '>1100万円</option>';
			echo '<option value="1200"';			if ($kahb_data == '1200') echo ' selected="selected"';			echo '>1200万円</option>';
			echo '<option value="1300"';			if ($kahb_data == '1300') echo ' selected="selected"';			echo '>1300万円</option>';
			echo '<option value="1400"';			if ($kahb_data == '1400') echo ' selected="selected"';			echo '>1400万円</option>';
			echo '<option value="1500"';			if ($kahb_data == '1500') echo ' selected="selected"';			echo '>1500万円</option>';
			echo '<option value="1600"';			if ($kahb_data == '1600') echo ' selected="selected"';			echo '>1600万円</option>';
			echo '<option value="1700"';			if ($kahb_data == '1700') echo ' selected="selected"';			echo '>1700万円</option>';
			echo '<option value="1800"';			if ($kahb_data == '1800') echo ' selected="selected"';			echo '>1800万円</option>';
			echo '<option value="1900"';			if ($kahb_data == '1900') echo ' selected="selected"';			echo '>1900万円</option>';
			echo '<option value="2000"';			if ($kahb_data == '2000') echo ' selected="selected"';			echo '>2000万円</option>';
			echo '<option value="3000"';			if ($kahb_data == '3000') echo ' selected="selected"';			echo '>3000万円</option>';
			echo '<option value="5000"';			if ($kahb_data == '5000') echo ' selected="selected"';			echo '>5000万円</option>';
			echo '<option value="7000"';			if ($kahb_data == '7000') echo ' selected="selected"';			echo '>7000万円</option>';
			echo '<option value="10000"';			if ($kahb_data == '10000') echo ' selected="selected"';			echo '>1億円</option>';
			echo '<option value="0"';			if ($kahb_data == '0' ||$kahb_data == '' ) echo ' selected="selected"';			echo '>上限なし</option>';
			echo '</select>';
			echo '</td>';
		echo '</tr>';
		}


		//賃料選択
		if( $shub == 2 ){
			$kalc_data = '';
			$kahc_data = '';
		echo '<tr>';
			echo '<th>賃料</th>';
			echo '<td id="kakaku" class="kakaku">';
			echo '<select name="kalc" id="kalc">';
			echo '<option value="0">下限なし</option>';
			echo '<option value="3"'; 			if ($kalc_data == '3') echo ' selected="selected"';			echo '>3万円</option>';
			echo '<option value="4"';			if ($kalc_data == '4') echo ' selected="selected"';			echo '>4万円</option>';
			echo '<option value="5"';			if ($kalc_data == '5') echo ' selected="selected"';			echo '>5万円</option>';
			echo '<option value="6"';			if ($kalc_data == '6') echo ' selected="selected"';			echo '>6万円</option>';
			echo '<option value="7"';			if ($kalc_data == '7') echo ' selected="selected"';			echo '>7万円</option>';
			echo '<option value="8"';			if ($kalc_data == '8') echo ' selected="selected"';			echo '>8万円</option>';
			echo '<option value="9"';			if ($kalc_data == '9') echo ' selected="selected"';			echo '>9万円</option>';
			echo '<option value="10"';			if ($kalc_data == '10') echo ' selected="selected"';			echo '>10万円</option>';
			echo '<option value="11"';			if ($kalc_data == '11') echo ' selected="selected"';			echo '>11万円</option>';
			echo '<option value="12"';			if ($kalc_data == '12') echo ' selected="selected"';			echo '>12万円</option>';
			echo '<option value="13"';			if ($kalc_data == '13') echo ' selected="selected"';			echo '>13万円</option>';
			echo '<option value="14"';			if ($kalc_data == '14') echo ' selected="selected"';			echo '>14万円</option>';
			echo '<option value="15"';			if ($kalc_data == '15') echo ' selected="selected"';			echo '>15万円</option>';
			echo '<option value="16"';			if ($kalc_data == '16') echo ' selected="selected"';			echo '>16万円</option>';
			echo '<option value="17"';			if ($kalc_data == '17') echo ' selected="selected"';			echo '>17万円</option>';
			echo '<option value="18"';			if ($kalc_data == '18') echo ' selected="selected"';			echo '>18万円</option>';
			echo '<option value="19"';			if ($kalc_data == '19') echo ' selected="selected"';			echo '>19万円</option>';
			echo '<option value="20"';			if ($kalc_data == '20') echo ' selected="selected"';			echo '>20万円</option>';
			echo '<option value="30"';			if ($kalc_data == '30') echo ' selected="selected"';			echo '>30万円</option>';
			echo '<option value="50"';			if ($kalc_data == '50') echo ' selected="selected"';			echo '>50万円</option>';
			echo '<option value="100"';			if ($kalc_data == '100') echo ' selected="selected"';			echo '>100万円</option>';
			echo '</select>　～　';
			echo '<select name="kahc" id="kahc">';
			echo '<option value="3"';			if ($kahc_data == '3') echo ' selected="selected"';			echo '>3万円</option>';
			echo '<option value="4"';			if ($kahc_data == '4') echo ' selected="selected"';			echo '>4万円</option>';
			echo '<option value="5"';			if ($kahc_data == '5') echo ' selected="selected"';			echo '>5万円</option>';
			echo '<option value="6"';			if ($kahc_data == '6') echo ' selected="selected"';			echo '>6万円</option>';
			echo '<option value="7"';			if ($kahc_data == '7') echo ' selected="selected"';			echo '>7万円</option>';
			echo '<option value="8"';			if ($kahc_data == '8') echo ' selected="selected"';			echo '>8万円</option>';
			echo '<option value="9"';			if ($kahc_data == '9') echo ' selected="selected"';			echo '>9万円</option>';
			echo '<option value="10"';			if ($kahc_data == '10') echo ' selected="selected"';			echo '>10万円</option>';
			echo '<option value="11"';			if ($kahc_data == '11') echo ' selected="selected"';			echo '>11万円</option>';
			echo '<option value="12"';			if ($kahc_data == '12') echo ' selected="selected"';			echo '>12万円</option>';
			echo '<option value="13"';			if ($kahc_data == '13') echo ' selected="selected"';			echo '>13万円</option>';
			echo '<option value="14"';			if ($kahc_data == '14') echo ' selected="selected"';			echo '>14万円</option>';
			echo '<option value="15"';			if ($kahc_data == '15') echo ' selected="selected"';			echo '>15万円</option>';
			echo '<option value="16"';			if ($kahc_data == '16') echo ' selected="selected"';			echo '>16万円</option>';
			echo '<option value="17"';			if ($kahc_data == '17') echo ' selected="selected"';			echo '>17万円</option>';
			echo '<option value="18"';			if ($kahc_data == '18') echo ' selected="selected"';			echo '>18万円</option>';
			echo '<option value="19"';			if ($kahc_data == '19') echo ' selected="selected"';			echo '>19万円</option>';
			echo '<option value="20"';			if ($kahc_data == '20') echo ' selected="selected"';			echo '>20万円</option>';
			echo '<option value="30"';			if ($kahc_data == '30') echo ' selected="selected"';			echo '>30万円</option>';
			echo '<option value="50"';			if ($kahc_data == '50') echo ' selected="selected"';			echo '>50万円</option>';
			echo '<option value="100"';			if ($kahc_data == '100') echo ' selected="selected"';			echo '>100万円</option>';
			echo '<option value="0"';			if ($kahc_data == '0' ||$kahc_data == '' ) echo ' selected="selected"';		echo '>上限なし</option>';
			echo '</select>';
			echo '</td>';
		echo '</tr>';
		}


		//駅歩分
		echo '<tr>';
		echo '<th>駅歩分</th>';
			echo '<td id="hof" class="hof">';
			echo '<ul>';
			echo '<li><input name="hof" value="0" id="hof0" type="radio" checked="checked" /><label for="hof0">指定なし</label></li>';
			echo '<li><input name="hof" value="1" id="hof1" type="radio" /><label for="hof1">1分以内</label></li>';
			echo '<li><input name="hof" value="3" id="hof3" type="radio" /><label for="hof3">3分以内</label></li>';
			echo '<li><input name="hof" value="5" id="hof5" type="radio" /><label for="hof5">5分以内</label></li>';
			echo '<li><input name="hof" value="10" id="hof10" type="radio" /><label for="hof10">10分以内</label></li>';
			echo '<li><input name="hof" value="15" id="hof15" type="radio" /><label for="hof15">15分以内</label></li>';
			echo '</ul>';
			echo '</td>';
		echo '<tr>';


		//間取り
		if( $shu_data !='' ){

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
			$madori_dat = '';
			if(!empty($metas)) {

				//ソート
				foreach($metas as $key => $row1){
					$foo1[$key] = $row1["madorisu"];
					$bar1[$key] = $row1["madorisyurui"];
				}
				array_multisort($foo1,SORT_ASC,$bar1,SORT_ASC,$metas);

				$madori_dat .= '<ul>';
				foreach ( $metas as $meta ) {

					$madorisu_data = $meta['madorisu'];
					$madorisyurui_data = $meta['madorisyurui'];

					if( $madorisu_data == 11 ) break;

					$madori_code = $madorisu_data;
					$madori_code .= $madorisyurui_data;

					foreach( $work_madori as $meta_box ){
						if( $madorisyurui_data == $meta_box['code'] ){
							$madori_dat .= '<li><input name="mad[]" value="'.$madori_code.'" id="mad2'.$madori_code.'" type="checkbox"';
							$madori_dat .= ' /><label for="mad2'.$madori_code.'">'.$madorisu_data.$meta_box['name'].'</label></li>';
						}
					}
				}
				$madori_dat .= '</ul>';
			}
		}

		if( $madori_dat != '<ul></ul>' ){
			echo '<tr>';
			echo '<th>間取り</th>';
			echo '<td id="madori" class="madori">'. $madori_dat .'</td>';
			echo '</tr>';
		}



		//面積
		$mel_data = '';
		$meh_data = '';
		echo '<tr>';
		echo '<th>面積</th>';
		echo '<td id="menseki" class="menseki">';
			echo '<select name="mel" id="mel">';
			echo '<option value="0">下限なし</option>';
			echo '<option value="10"';			if ($mel_data == '10') echo ' selected="selected"';			echo '>10m&sup2;</option>';
			echo '<option value="15"';			if ($mel_data == '15') echo ' selected="selected"';			echo '>15m&sup2;</option>';
			echo '<option value="20"';			if ($mel_data == '20') echo ' selected="selected"';			echo '>20m&sup2;</option>';
			echo '<option value="25"';			if ($mel_data == '25') echo ' selected="selected"';			echo '>25m&sup2;</option>';
			echo '<option value="30"';			if ($mel_data == '30') echo ' selected="selected"';			echo '>30m&sup2;</option>';
			echo '<option value="35"';			if ($mel_data == '35') echo ' selected="selected"';			echo '>35m&sup2;</option>';
			echo '<option value="40"';			if ($mel_data == '40') echo ' selected="selected"';			echo '>40m&sup2;</option>';
			echo '<option value="50"';			if ($mel_data == '50') echo ' selected="selected"';			echo '>50m&sup2;</option>';
			echo '<option value="60"';			if ($mel_data == '60') echo ' selected="selected"';			echo '>60m&sup2;</option>';
			echo '<option value="70"';			if ($mel_data == '70') echo ' selected="selected"';			echo '>70m&sup2;</option>';
			echo '<option value="80"';			if ($mel_data == '80') echo ' selected="selected"';			echo '>80m&sup2;</option>';
			echo '<option value="90"';			if ($mel_data == '90') echo ' selected="selected"';			echo '>90m&sup2;</option>';
			echo '<option value="100"';			if ($mel_data == '100') echo ' selected="selected"';			echo '>100m&sup2;</option>';
			echo '<option value="200"';			if ($mel_data == '200') echo ' selected="selected"';			echo '>200m&sup2;</option>';
			echo '<option value="300"';			if ($mel_data == '300') echo ' selected="selected"';			echo '>300m&sup2;</option>';
			echo '<option value="400"';			if ($mel_data == '400') echo ' selected="selected"';			echo '>400m&sup2;</option>';
			echo '<option value="500"';			if ($mel_data == '500') echo ' selected="selected"';			echo '>500m&sup2;</option>';
			echo '<option value="600"';			if ($mel_data == '600') echo ' selected="selected"';			echo '>600m&sup2;</option>';
			echo '<option value="700"';			if ($mel_data == '700') echo ' selected="selected"';			echo '>700m&sup2;</option>';
			echo '<option value="800"';			if ($mel_data == '800') echo ' selected="selected"';			echo '>800m&sup2;</option>';
			echo '<option value="900"';			if ($mel_data == '900') echo ' selected="selected"';			echo '>900m&sup2;</option>';
			echo '<option value="1000"';			if ($mel_data == '1000') echo ' selected="selected"';			echo '>1000m&sup2;</option>';
			echo '</select>　～　';
			echo '<select name="meh" id="meh">';
			echo '<option value="10"';			if ($meh_data == '10') echo ' selected="selected"';			echo '>10m&sup2;</option>';
			echo '<option value="15"';			if ($meh_data == '15') echo ' selected="selected"';			echo '>15m&sup2;</option>';
			echo '<option value="20"';			if ($meh_data == '20') echo ' selected="selected"';			echo '>20m&sup2;</option>';
			echo '<option value="25"';			if ($meh_data == '25') echo ' selected="selected"';			echo '>25m&sup2;</option>';
			echo '<option value="30"';			if ($meh_data == '30') echo ' selected="selected"';			echo '>30m&sup2;</option>';
			echo '<option value="35"';			if ($meh_data == '35') echo ' selected="selected"';			echo '>35m&sup2;</option>';
			echo '<option value="40"';			if ($meh_data == '40') echo ' selected="selected"';			echo '>40m&sup2;</option>';
			echo '<option value="50"';			if ($meh_data == '50') echo ' selected="selected"';			echo '>50m&sup2;</option>';
			echo '<option value="60"';			if ($meh_data == '60') echo ' selected="selected"';			echo '>60m&sup2;</option>';
			echo '<option value="70"';			if ($meh_data == '70') echo ' selected="selected"';			echo '>70m&sup2;</option>';
			echo '<option value="80"';			if ($meh_data == '80') echo ' selected="selected"';			echo '>80m&sup2;</option>';
			echo '<option value="90"';			if ($meh_data == '90') echo ' selected="selected"';			echo '>90m&sup2;</option>';
			echo '<option value="100"';			if ($meh_data == '100') echo ' selected="selected"';			echo '>100m&sup2;</option>';
			echo '<option value="200"';			if ($meh_data == '200') echo ' selected="selected"';			echo '>200m&sup2;</option>';
			echo '<option value="300"';			if ($meh_data == '300') echo ' selected="selected"';			echo '>300m&sup2;</option>';
			echo '<option value="400"';			if ($meh_data == '400') echo ' selected="selected"';			echo '>400m&sup2;</option>';
			echo '<option value="500"';			if ($meh_data == '500') echo ' selected="selected"';			echo '>500m&sup2;</option>';
			echo '<option value="600"';			if ($meh_data == '600') echo ' selected="selected"';			echo '>600m&sup2;</option>';
			echo '<option value="700"';			if ($meh_data == '700') echo ' selected="selected"';			echo '>700m&sup2;</option>';
			echo '<option value="800"';			if ($meh_data == '800') echo ' selected="selected"';			echo '>800m&sup2;</option>';
			echo '<option value="900"';			if ($meh_data == '900') echo ' selected="selected"';			echo '>900m&sup2;</option>';
			echo '<option value="1000"';			if ($meh_data == '1000') echo ' selected="selected"';			echo '>1000m&sup2;</option>';
			echo '<option value="0"';			if ($meh_data == '0' ||$meh_data == '' ) echo ' selected="selected"';			echo '>上限なし</option>';
			echo '</select>';
			echo '</td>';
		echo '</tr>';



		//設備
			$setsubi_dat = '';
			if( $shu_data !='' ){
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
								$setsubi_code = $meta_box['code'];
								$setsubi_name = $meta_box['name'];
								$data = array( $setsubi_code => array("code" => $setsubi_code,"name" => $setsubi_name));
								foreach($array_setsubi as $meta_box2){
									if ( $setsubi_code == $meta_box2['code'])
										$data = '';
								}
								if(!empty($data))
								$array_setsubi = array_merge( $data , $array_setsubi);
							//	$array_setsubi = array_push( $data , $array_setsubi);
							}
						}
					}
				}
				if(!empty($array_setsubi)) {
					krsort($array_setsubi);
					$setsubi_dat ='';
					$setsubi_dat .= '<ul>';
					foreach($array_setsubi as $meta_box3){
						//$widget_seach_setsubi
						if(is_array($widget_seach_setsubi)) {
							$k=0;
							foreach($widget_seach_setsubi as $meta_box5){
								if($widget_seach_setsubi[$k] == $meta_box3['code']){
									$setsubi_dat .= '<li>';
									$setsubi_dat .= '<input type="checkbox" name="set[]"  value="'.$meta_box3['code'].'" id="set2'.$meta_box3['code'].'"';
									//	if(is_array($set_id)) {
									//		foreach($set_id as $meta_box4)
									//			if( $meta_box4 == $meta_box3['code'] ) $setsubi_dat .= ' checked="checked"';
									//	}
									$setsubi_dat .= '">';
									$setsubi_dat .= '<label for="set2'.$meta_box3['code'].'">'.$meta_box3['name'].'</label>';
									$setsubi_dat .= '</li>';
								}
								$k++;
							}
						}else{
								$setsubi_dat .= '<li>';
								$setsubi_dat .= '<input type="checkbox" name="set[]"  value="'.$meta_box3['code'].'" id="set2'.$meta_box3['code'].'"';
								//	if(is_array($set_id)) {
								//		foreach($set_id as $meta_box4)
								//			if( $meta_box4 == $meta_box3['code'] ) $setsubi_dat .= ' checked="checked"';
								//	}
								$setsubi_dat .= '">';
								$setsubi_dat .= '<label for="set2'.$meta_box3['code'].'">'.$meta_box3['name'].'</label>';
								$setsubi_dat .= '</li>';
						}
					}
					$setsubi_dat .= '</ul>';
				}
			}
		if( $setsubi_dat != '<ul></ul>' ){
			echo '<tr>';
			echo '<th>条件・設備 (絞込み)</th>';
			echo '<td id="setsubi" class="setsubi">'. $setsubi_dat .'</td>';
			echo '</tr>';
		}



		echo '</table>';
		echo '<div class="submit"><input type="submit" value="物件検索" /></div>';
		echo '</form>';

?>
				</div><!-- .entry-content -->
			</article><!-- #post-## -->

			<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() )
					comments_template();
			?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
