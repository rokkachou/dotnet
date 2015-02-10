<?php
/**
 * The Template for displaying fudou archive posts.
 *
 * Template: archive-fudo.php
 * 
 * @package WordPress3.9
 * @subpackage Fudousan Plugin
 * Version: 1.4.5
 */

	/**
	 * Extends the default WordPress body class 'sidebar' to archive-fudo:
	 * @since Twenty Thirteen 1.0
	 * @return array Filtered class values.
	 */
	function twentythirteen_body_class_add_sidebar( $classes ) {
		$classes[] = 'sidebar';
		return $classes;
	}
	if( get_option('template') == 'twentythirteen' ){
		add_filter( 'body_class', 'twentythirteen_body_class_add_sidebar' );
	}

	global $wpdb;
	global $work_setsubi;

	$sql = '';
	$sql2 = '';
	$joken_url  = '';
	$site = home_url( '/' );
	$plugin_url = WP_PLUGIN_URL .'/fudou/';

	//newup_mark
		$newup_mark = get_option('newup_mark');
		if($newup_mark == '') $newup_mark=14;


	//ユーザー別会員物件リスト
	$kaiin_users_rains_register = get_option('kaiin_users_rains_register');

	//路線・県ID
		$mid_id = isset($_GET['mid']) ? myIsNum_f($_GET['mid']) : '';
		if($mid_id=='')	$mid_id = "99999";

	//駅・市区ID
		$nor_id = isset($_GET['nor']) ? myIsNum_f($_GET['nor']) : '';
		if($nor_id=='')	$nor_id = "99999";

	//カテゴリ
		$bukken = isset($_GET['bukken']) ? $_GET['bukken'] : '';
		$bukken_slug_data = esc_attr( stripslashes( $bukken ));
		$taxonomy_name = '';
		if( $bukken != 'ken' && $bukken != 'shiku' && $bukken != 'rosen' && $bukken != 'station' && $bukken != 'jsearch' && $bukken != 'search')
			$taxonomy_name = 'bukken';

	//投稿タグ
		if($bukken_slug_data == ''){
			$bukken_tag = isset($_GET['bukken_tag']) ? $_GET['bukken_tag'] : '';
			$bukken_slug_data = esc_attr( stripslashes( $bukken_tag ));
			$taxonomy_name = 'bukken_tag';
		}

		$bukken_slug_data = str_replace(" ","",$bukken_slug_data);
		$bukken_slug_data = str_replace(";","",$bukken_slug_data);
		$bukken_slug_data = str_replace(",","",$bukken_slug_data);
		$bukken_slug_data = str_replace("'","",$bukken_slug_data);
		//エンコード
		$slug_data = utf8_uri_encode($bukken_slug_data,0);


	//物件キーワード検索  $s st
		$s = str_replace(" ","",$s);
		$s = str_replace(";","",$s);
		$s = str_replace(",","",$s);
		$s = str_replace("'","",$s);
		$s = str_replace("\\","",$s);

		$searchtype = isset($_GET['st']) ? esc_sql(esc_attr($_GET['st'])) : '';

	//ページ
		$bukken_page_data = isset($_GET['paged']) ? myIsNum_f($_GET['paged']) : '';
		if($bukken_page_data < 2) $bukken_page_data = "";


	//種別
		$bukken_shubetsu = isset($_GET['shu']) ? myIsNum_f($_GET['shu']) : '';

		//複数種別用 売買・賃貸判別
		$shub = isset($_GET['shub']) ? myIsNum_f($_GET['shub']) : '';


	//複数種別
		$is_tochi = false;
		if ( is_array($bukken_shubetsu) ) {
			$i=0;
			$shu_data = ' IN ( 0 ';
			foreach($bukken_shubetsu as $meta_set){

				//土地判定 (土地・戸建)
				$tmp_bs = (int)$bukken_shubetsu[$i];
				if( ($tmp_bs > 1100 && $tmp_bs < 1300) || $tmp_bs == 3212  ){
					$is_tochi = true;
				}

				$shu_data .= ','. $tmp_bs . '';
				$i++;
			}
			$shu_data .= ') ';

		} else {
			$shu_data = " > 0 ";
			if($bukken_shubetsu == '1') 
				$shu_data = '< 3000' ;	//売買
			if($bukken_shubetsu == '2') 
				$shu_data = '> 3000' ;	//賃貸

			if(intval($bukken_shubetsu) > 3 && $bukken_slug_data == 'jsearch') 
				$shu_data = '= ' . (int)$bukken_shubetsu ;

			//土地判定
			if( ($bukken_shubetsu > 1100 && $bukken_shubetsu < 1300) || $bukken_shubetsu == 3212 || $bukken_shubetsu == 1 ){
				$is_tochi = true;
			}
		} 

	//複数種別用 売買・賃貸判別
		if($bukken_shubetsu == '' && $shub != ''){
			if ($shub == '1' )
				$shu_data = '< 3000' ;	//売買
			if ($shub == '2' )
				$shu_data = '> 3000' ;	//賃貸
		}

	//ソート
		$bukken_sort_data2 = "";
		$bukken_order_data2 = " DESC";

		//ソート項目
		$bukken_sort = isset($_GET['so']) ? esc_attr( stripslashes($_GET['so'])) : '';

		//order
		$bukken_order_data = isset($_GET['ord']) ? esc_attr( stripslashes($_GET['ord'])) : '';


		if($bukken_sort=="tam"){
			$bukken_sort_data = "tatemonomenseki";
		}elseif($bukken_sort=="tac"){
			$bukken_sort_data = "tatemonochikunenn";
		}elseif($bukken_sort=="mad"){
			$bukken_sort_data = "madorisu";
		}elseif($bukken_sort=="sho"){
			$bukken_sort_data = "shozaichicode";
		}else{
			//デフォルト
				//価格 (ここだけはコメントアウト禁止)
				$bukken_sort_data = "kakaku";
				$bukken_sort = "kak";

				//建物面積
			//	$bukken_sort_data = "tatemonomenseki";
			//	$bukken_sort=="tam";


				//築年月
			//	$bukken_sort_data = "tatemonochikunenn";
			//	$bukken_sort=="tac";

				//間取り
			//	$bukken_sort_data = "madorisu";
			//	$bukken_sort=="mad";


				//住所
			//	$bukken_sort_data = "shozaichicode";
			//	$bukken_sort=="sho";


				/*
				//更新日順
				$bukken_sort = "";
				if( empty($_GET['so']) ){
					$bukken_sort_data2 = "post_modified";
					$bukken_order_data2 = " DESC";
				}
				*/
		}


		if($bukken_order_data=="d"){
			$bukken_order_data = " DESC";
			$bukken_order = "";
		}else{
			$bukken_order_data = " ASC";
			$bukken_order = "d";
		}



	//1ページに表示する物件数
		$posts_per_page = get_option('posts_per_page');
		if($bukken_page_data == ""){
			$limit_from = "0";
			$limit_to = $posts_per_page;
		}else{
			$limit_from = $posts_per_page * $bukken_page_data - $posts_per_page;
			$limit_to = $posts_per_page;
		}


	//条件検索用
	$rosen_eki = isset($_GET['re']) ? myIsNum_f($_GET['re']) : '';	//路線駅
	$ksik_id   = isset($_GET['ksik']) ? myIsNum_f($_GET['ksik']) : '';	//県市区

	if($bukken_slug_data == "jsearch"){

		$ros_id = isset($_GET['ros']) ? myIsNum_f($_GET['ros']) : '';	//路線
		$eki_id = isset($_GET['eki']) ? myIsNum_f($_GET['eki']) : '';	//駅
		$ken_id = isset($_GET['ken']) ? myIsNum_f($_GET['ken']) : '';	//県
		$sik_id = isset($_GET['sik']) ? myIsNum_f($_GET['sik']) : '';	//市区

		$ken_id = sprintf( "%02d", $ken_id );


		//複数市区
		$ksik_data = '';
		if (is_array($ksik_id)) {
			$i=0;
			$ksik_data = " IN ( '99999' ";
			foreach($ksik_id as $meta_set){
				if( myIsNum_f($ksik_id[$i]) ){
					$ksik_data .= ", '". myIsNum_f($ksik_id[$i]) . "000000'";
				}
				$i++;
			}
			$ksik_data .= ") ";
		}


		//複数駅
		$eki_data = '';
		if(is_array( $rosen_eki )  ){
			$i=0;
			$eki_data = ' IN ( 0 ';
			foreach($rosen_eki as $meta_set){
				if( intval(myLeft($rosen_eki[$i],6)) ){
					$eki_data .= ',' . intval(myLeft($rosen_eki[$i],6)) . intval(myRight($rosen_eki[$i],6));
				}
				$i++;
			}
			$eki_data .= ') ';
		}


		//設備条件
		$set_id = isset($_GET['set']) ? myIsNum_f($_GET['set']) : '';
		$setsubi_name = '';
		if(!empty($set_id)) {
			$setsubi_name = '設備条件 ';
			$i=0;
			foreach($set_id as $meta_set){
				foreach($work_setsubi as $meta_setsubi){
					if($set_id[$i] == $meta_setsubi['code'] )
						$setsubi_name .= $meta_setsubi['name'] . ' ';
				}
				$i++;
			}
		}


		//間取り
		$madori_id = isset($_GET['mad']) ? myIsNum_f($_GET['mad']) : '';
		$madori_name = '';
		if(!empty($madori_id)) {
			$madori_name = '間取り';

			$i=0;
			foreach($madori_id as $meta_box){
				$madorisu_data = $madori_id[$i];
				$madorisyurui_data = myRight($madorisu_data,2);
				$madori_name .= myLeft($madorisu_data,1);

				if($madorisyurui_data=="10")	$madori_name .= 'R ';
				if($madorisyurui_data=="20")	$madori_name .= 'K ';
				if($madorisyurui_data=="25")	$madori_name .= 'SK ';
				if($madorisyurui_data=="30")	$madori_name .= 'DK ';
				if($madorisyurui_data=="35")	$madori_name .= 'SDK ';
				if($madorisyurui_data=="40")	$madori_name .= 'LK ';
				if($madorisyurui_data=="45")	$madori_name .= 'SLK ';
				if($madorisyurui_data=="50")	$madori_name .= 'LDK ';
				if($madorisyurui_data=="55")	$madori_name .= 'SLDK ';
				$i++;
			}
		}


		//価格
		$kalb_data = isset($_GET['kalb']) ? myIsNum_f($_GET['kalb']) : '';	//価格下限
		$kahb_data = isset($_GET['kahb']) ? myIsNum_f($_GET['kahb']) : '';	//価格上限
		$kalc_data = isset($_GET['kalc']) ? myIsNum_f($_GET['kalc']) : '';	//賃料下限
		$kahc_data = isset($_GET['kahc']) ? myIsNum_f($_GET['kahc']) : '';	//賃料上限
		$kakaku_name = '';

			$kal_data =0 ;
			$kah_data =0 ;

			//売買
			if($bukken_shubetsu == '1' || ( intval($bukken_shubetsu) < 3000 && intval($bukken_shubetsu) > 1000 ) || $shub == '1') {
				$kal_data =$kalb_data*10000 ;
				$kah_data =$kahb_data*10000 ;

					//価格条件
					if($kalb_data > 0 || $kahb_data > 0 ){
						$kakaku_name = '価格';
						if($kalb_data > 0 )
							$kakaku_name .= $kalb_data.'万円';
						$kakaku_name .= '～';
						if($kahb_data > 0)
							$kakaku_name .= $kahb_data . '万円 ' ;
					}


			}
			//賃貸
			if($bukken_shubetsu == '2' || intval($bukken_shubetsu) > 3000  || $shub == '2') {
				$kal_data =$kalc_data*10000 ;
				$kah_data =$kahc_data*10000 ;

					//賃料条件
					if($kalc_data > 0 || $kahc_data > 0 ){
						$kakaku_name = '賃料';
						if($kalc_data > 0 )
							$kakaku_name .= $kalc_data.'万円';
						$kakaku_name .= '～';
						if($kahc_data > 0)
							$kakaku_name .= $kahc_data . '万円 ' ;
					}


			}


		//築年数
		$tiku_name = '';
		$tik_data = isset($_GET['tik']) ? myIsNum_f($_GET['tik']) : '';
			$tik_data = intval($tik_data);

			//築年数条件
			if($tik_data > 0 )
				$tiku_name = '築'.$tik_data.'年以内 ';


		
		//歩分
		$hofun_name = '';
		$hof_data = isset($_GET['hof']) ? myIsNum_f($_GET['hof']) : '';

			//歩分条件
			if($hof_data > 0 )
				$hofun_name = '駅徒歩'.$hof_data.'分以内 ';


		//面積下限
		$mel_data = isset($_GET['mel']) ? myIsNum_f($_GET['mel']) : '';
			$mel_data = intval($mel_data);

		//面積上限
		$meh_data = isset($_GET['meh']) ? myIsNum_f($_GET['meh']) : '';
			$meh_data = intval($meh_data);

			//面積条件
			$menseki_name = '';
			if($mel_data > 0 || $meh_data > 0 ){
				$menseki_name = '面積';

				if($mel_data > 0 )
					$menseki_name .= $mel_data.'m&sup2;';

				$menseki_name .= '～';

				if($meh_data > 0)
					$menseki_name .= $meh_data . 'm&sup2; ' ;
			}

	} //条件検索用




	//タイトル
		$org_title = '';
		if($s != ''){
			$org_title = '検索: '.$s ;

			if($searchtype == 'id'){
				$org_title = '検索 物件番号: '.$s ;
			}

			if($searchtype == 'chou'){
				$org_title = '検索 町名: '.$s ;
			}
		}else{
			if ($taxonomy_name !=''){

				if( $taxonomy_name == 'bukken' ){
					$org_title = 'カテゴリ: ';
					$joken_url  = $site .'?bukken='.$slug_data.'';
				}
				if( $taxonomy_name == 'bukken_tag'){
					$org_title = 'タグ: ';
					$joken_url  = $site .'?bukken_tag='.$slug_data.'';
				}

				$sql  = "SELECT T.name";
				$sql .= " FROM $wpdb->terms AS T ";
				$sql .= " INNER JOIN $wpdb->term_taxonomy as TT ON T.term_id = TT.term_id ";
				$sql .= " WHERE TT.taxonomy  = '".$taxonomy_name."' ";
				$sql .= " AND T.slug   = '".$slug_data."' ";

			//	$sql = $wpdb->prepare($sql,'');
				$metas = $wpdb->get_row( $sql );
				if(!empty($metas)) $org_title .= $metas->name;
			}
		}


	//種別タイトル
		global $work_bukkenshubetsu;
		//複数種別
		$shu_name = '';
		if (is_array($bukken_shubetsu)) {
			$i=0;
			foreach($bukken_shubetsu as $meta_set){
				foreach($work_bukkenshubetsu as $meta_box){
					if( $bukken_shubetsu[$i] ==  $meta_box['id'] ){
						$shu_name .= ' '. $meta_box['name'] . ' ';
					}
				}
				$i++;
			}
		} else {
				foreach($work_bukkenshubetsu as $meta_box){
					if( $bukken_shubetsu ==  $meta_box['id'] ){
						$shu_name = ' '. $meta_box['name'] . ' ';
					}
				}
		} 



	//路線タイトル
		$rosen_name = '';
		if( ($bukken_slug_data=="rosen" && $mid_id !="") || ($bukken_slug_data=="jsearch" && $ros_id != '' && $eki_id == 0 ) ){
			$rosen_id = (int)$mid_id;
			if( $bukken_slug_data=="jsearch" && (int)$ros_id )
				$rosen_id = (int)$ros_id;
			$sql = "SELECT `rosen_name` FROM `".$wpdb->prefix."train_rosen` WHERE `rosen_id` =".$rosen_id."";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_row( $sql );
			if(!empty($metas)) $org_title = $metas->rosen_name;
			$rosen_name = $org_title;
		}

	//駅タイトル
		$eki_name = '';
		if( ($bukken_slug_data=="station" && $mid_id !="" && $nor_id !="") || ($bukken_slug_data=="jsearch" && $ros_id != '' && $eki_id != '' ) ){
			$rosen_id = (int)$mid_id;
			$ekin_id = (int)$nor_id;
			if( $bukken_slug_data=="jsearch" && (int)$ros_id  && (int)$eki_id ){
				$rosen_id = (int)$ros_id;
				$ekin_id = (int)$eki_id;
			}
			$sql = "SELECT DTS.station_name,DTR.rosen_name";
			$sql .=  " FROM ".$wpdb->prefix."train_rosen AS DTR";
			$sql .=  " INNER JOIN ".$wpdb->prefix."train_station AS DTS ON DTR.rosen_id = DTS.rosen_id";
			$sql .=  " WHERE DTS.rosen_id=".$rosen_id." AND DTS.station_id=".$ekin_id."";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_row( $sql );
			if(!empty($metas)) {
				$org_title = $metas->rosen_name.''.$metas->station_name.'駅';
				$eki_name = $org_title . ' ';
			}
		}


	//県タイトル
		$ken_name = '';
		if( ($bukken_slug_data=="ken" && $mid_id !="") || ($bukken_slug_data=="jsearch" && $ken_id != '') ){
			$kenn_id = (int)$mid_id;
			if( $bukken_slug_data=="jsearch" && (int)$ken_id )
				$kenn_id = (int)$ken_id;

			if( $kenn_id != '' ){
				$sql = "SELECT `middle_area_name` FROM `".$wpdb->prefix."area_middle_area` WHERE `middle_area_id`=".$kenn_id."";
			//	$sql = $wpdb->prepare($sql,'');
				$metas = $wpdb->get_row( $sql );
				if(!empty($metas)){
					$org_title = $metas->middle_area_name;
					$ken_name = $org_title;
				}
			}
		}


	//市区タイトル
		$siku_name = '';
		if( ( $bukken_slug_data=="shiku" && $mid_id !="" && $nor_id !="") || ($bukken_slug_data=="jsearch" && $ken_id != '' && $sik_id !='') ){
			$kenn_id = (int)$mid_id;
			$sikn_id = (int)$nor_id;
			if($bukken_slug_data=="jsearch" && (int)$ken_id && (int)$sik_id ){
				$kenn_id = (int)$ken_id;
				$sikn_id = (int)$sik_id;
			}

			$sql = "SELECT narrow_area_name FROM ".$wpdb->prefix."area_narrow_area WHERE middle_area_id=".$kenn_id." and narrow_area_id =".$sikn_id."";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_row( $sql );
			if(!empty($metas)){
				$org_title = $metas->narrow_area_name;
				$siku_name = $org_title ;
			}
		}


	//複数駅タイトル
		if(is_array( $rosen_eki )  ){
			$i=0;
			foreach($rosen_eki as $meta_set){
				$f_rosen_id =  intval(myLeft($rosen_eki[$i],6));
				$f_eki_id   =  intval(myRight($rosen_eki[$i],6));

				$sql = "SELECT DTS.station_name";
				$sql .=  " FROM ".$wpdb->prefix."train_station AS DTS";
				$sql .=  " WHERE DTS.rosen_id=".$f_rosen_id." AND DTS.station_id=".$f_eki_id."";

			//	$sql = $wpdb->prepare($sql,'');
				$metas = $wpdb->get_row( $sql );
				if(!empty($metas)) $eki_name .= $metas->station_name . '駅 ';

				$i++;
			}
		}


	//複数市区タイトル $ksik_id = $_GET['ksik']; //県市区
		$ksik_name = '';
		if (is_array($ksik_id)) {
			$sql  = "SELECT narrow_area_name FROM ".$wpdb->prefix."area_narrow_area ";
			$sql .= "WHERE ";
			$i=0;
			$j=0;
			foreach($ksik_id as $meta_set){
				$tmp_kenn_id = $ksik_id[$i];
				$kenn_id = myLeft($tmp_kenn_id,2);
				$sikn_id = myRight($tmp_kenn_id,3);
				if( (int)$kenn_id && (int)$sikn_id ){
					if($j > 0 ) $sql .= " OR ";
					$sql .= "( middle_area_id=". (int)$kenn_id ." and narrow_area_id =". (int)$sikn_id .")";
					$j++;
				}
				$i++;
			}
			if ($j > 0 ){
				//$sql = $wpdb->prepare($sql,'');
				$metas = $wpdb->get_results( $sql, ARRAY_A );
				if(!empty($metas)) {

					foreach ( $metas as $meta ) {
						$ksik_name .= '' . $meta['narrow_area_name'] .' ';
					}
				}
			}
		} 


	//条件検索タイトル生成
		if($bukken_slug_data == "jsearch"){
			$org_title = '検索 ';
			$org_title .= $shu_name;
			$org_title .= $rosen_name;
			$org_title .= $eki_name;
			$org_title .= $ken_name;
			$org_title .= $siku_name;
			$org_title .= $ksik_name;
			$org_title .= $tiku_name;
			$org_title .= $hofun_name;
			$org_title .= $menseki_name;
			$org_title .= $kakaku_name;
			$org_title .= $setsubi_name;
			$org_title .= $madori_name;
		}


	//title設定
		//売買
		if($bukken_shubetsu == '1' || (intval($bukken_shubetsu) < 3000 && intval($bukken_shubetsu) > 1000) || $shub == '1' ) {
			$org_title =  '売買 > '.$org_title.' ';
		}

		//賃貸
		if($bukken_shubetsu == '2' || intval($bukken_shubetsu) > 3000 || $shub == '2' ) {
			$org_title =  '賃貸 > '.$org_title.' ';
		}


	//title設定
		function add_post_type_single_title_fudou($title = '') {
			global $org_title,$bukken_page_data;

			$title =  $org_title.' ';

			if( $bukken_page_data ){
				$title .=  '['. $bukken_page_data .'] ';
			}
			return $title;
		}
		add_action('wp_title', 'add_post_type_single_title_fudou');















/**** 検索 SQL ****/


	//SQL タクソノミー用(デフォルト)
	if( $s == ''){

		//価格・面積・築年月 ソート用

			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ($wpdb->postmeta as PM";
			$sql .=  " INNER JOIN (($wpdb->terms as T";
			$sql .=  " INNER JOIN $wpdb->term_taxonomy as TT ON T.term_id = TT.term_id) ";
			$sql .=  " INNER JOIN $wpdb->term_relationships as TR ON TT.term_taxonomy_id = TR.term_taxonomy_id) ON PM.post_id = TR.object_id)";
			$sql .=  " INNER JOIN $wpdb->posts AS P ON PM.post_id = P.ID";
			$sql .=  " WHERE T.slug='".$slug_data."'";
			$sql .=  " AND P.post_password='' ";
			$sql .=  " AND P.post_status='publish'";
			$sql .=  " AND TT.taxonomy='".$taxonomy_name."'";
			$sql .=  " AND PM.meta_key='".$bukken_sort_data."'";

			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT TR.object_id",$sql);
			//更新日順
			if($bukken_sort_data2 == "post_modified"){
					$sql2 .=  " ORDER BY P.post_modified ".$bukken_order_data2;
			}else{
				//テキスト
				if($bukken_sort_data== "tatemonochikunenn"){
					$sql2 .=  " ORDER BY PM.meta_value ".$bukken_order_data;
				}else{
				//数値
					$sql2 .=  " ORDER BY CAST(PM.meta_value AS SIGNED)".$bukken_order_data;
				}
			}
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";



			//面積 ソート用
			if($bukken_sort_data == "tatemonomenseki" ){

				$sql_a  =  " (SELECT P.ID , PM.meta_value";
				$sql_a .=  " FROM $wpdb->postmeta AS PM_1 ";
				$sql_a .=  " INNER JOIN ($wpdb->postmeta as PM ";
				$sql_a .=  " INNER JOIN (($wpdb->terms as T ";
				$sql_a .=  " INNER JOIN $wpdb->term_taxonomy as TT ON T.term_id = TT.term_id) ";
				$sql_a .=  " INNER JOIN $wpdb->term_relationships as TR ON TT.term_taxonomy_id = TR.term_taxonomy_id) ON PM.post_id = TR.object_id) ON PM_1.post_id = TR.object_id";
				$sql_a .=  " INNER JOIN $wpdb->posts AS P ON PM.post_id = P.ID";
				$sql_a .=  " WHERE T.slug='".$slug_data."'";
				$sql_a .=  " AND P.post_password='' ";
				$sql_a .=  " AND P.post_status='publish'";
				$sql_a .=  " AND TT.taxonomy='".$taxonomy_name."'";
				$sql_a .=  " AND PM_1.meta_key='bukkenshubetsu' AND ( CAST(PM_1.meta_value AS SIGNED) > 1200 OR PM_1.meta_value != '3212' ) ";
				$sql_a .=  " AND PM.meta_key='tatemonomenseki' AND PM.meta_value )";

				$sql_a .=  " UNION ";

				$sql_a .=  " (SELECT P.ID , PM.meta_value";
				$sql_a .=  " FROM $wpdb->postmeta AS PM_1 ";
				$sql_a .=  " INNER JOIN ($wpdb->postmeta as PM ";
				$sql_a .=  " INNER JOIN (($wpdb->terms as T ";
				$sql_a .=  " INNER JOIN $wpdb->term_taxonomy as TT ON T.term_id = TT.term_id) ";
				$sql_a .=  " INNER JOIN $wpdb->term_relationships as TR ON TT.term_taxonomy_id = TR.term_taxonomy_id) ON PM.post_id = TR.object_id) ON PM_1.post_id = TR.object_id";
				$sql_a .=  " INNER JOIN $wpdb->posts AS P ON PM.post_id = P.ID";
				$sql_a .=  " WHERE T.slug='".$slug_data."'";
				$sql_a .=  " AND P.post_password='' ";
				$sql_a .=  " AND P.post_status='publish'";
				$sql_a .=  " AND TT.taxonomy='".$taxonomy_name."'";
				$sql_a .=  " AND PM_1.meta_key='bukkenshubetsu' AND ( CAST(PM_1.meta_value AS SIGNED) < 1200 OR PM_1.meta_value = '3212' ) ";
				$sql_a .=  " AND PM.meta_key='tochikukaku' AND PM.meta_value )";

				$sql =  "SELECT count(DISTINCT ID) as co From ( " .$sql_a. " ) AS A";

				$sql2 = "SELECT DISTINCT A.ID AS object_id  From ( " .$sql_a. " ) AS A ORDER BY CAST(A.meta_value AS SIGNED) ".$bukken_order_data;
				$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";
			}


		//間取・所在地 ソート用
		if($bukken_sort_data== "madorisu" || $bukken_sort_data== "shozaichicode"){

			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM $wpdb->postmeta AS PM_1 ";
			$sql .=  " INNER JOIN ($wpdb->postmeta as PM ";
			$sql .=  " INNER JOIN (($wpdb->terms as T ";
			$sql .=  " INNER JOIN $wpdb->term_taxonomy as TT ON T.term_id = TT.term_id) ";
			$sql .=  " INNER JOIN $wpdb->term_relationships as TR ON TT.term_taxonomy_id = TR.term_taxonomy_id) ON PM.post_id = TR.object_id) ON PM_1.post_id = TR.object_id";
			$sql .=  " INNER JOIN $wpdb->posts AS P ON PM.post_id = P.ID";
			$sql .=  " WHERE T.slug='".$slug_data."'";
			$sql .=  " AND P.post_password='' ";
			$sql .=  " AND P.post_status='publish'";
			$sql .=  " AND TT.taxonomy='".$taxonomy_name."'";
			//数値・数値
			if($bukken_sort_data== "madorisu"){
				$sql .=  " AND PM.meta_key='madorisu' ";
				$sql .=  " AND PM_1.meta_key='madorisyurui' ";
			}
			//数値・テキスト
			if($bukken_sort_data== "shozaichicode"){
				$sql .=  " AND PM.meta_key='shozaichicode' ";
				$sql .=  " AND PM_1.meta_key='shozaichimeisho' ";
			}

			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT TR.object_id",$sql);
			//数値・数値
			if($bukken_sort_data== "madorisu"){
				$sql2 .=  " ORDER BY CAST(PM.meta_value AS SIGNED)".$bukken_order_data.", CAST(PM_1.meta_value AS SIGNED)".$bukken_order_data."";
			}
			//数値・テキスト
			if($bukken_sort_data== "shozaichicode"){
				$sql2 .=  " ORDER BY CAST(PM.meta_value AS SIGNED)".$bukken_order_data.", PM_1.meta_value".$bukken_order_data."";
			}

			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";
		}

	}



	//SQL キーワード検索  $s st
	$id_data = '';

	if( $bukken_slug_data == "search" && $s != '' ){

		if($searchtype == ''){

			//物件番号
			$sql = "SELECT DISTINCT P.ID";
			$sql .=  " FROM $wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id ";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = ''  AND P.post_type ='fudo' ";
			$sql .=  " AND PM_1.meta_key='shikibesu' AND PM_1.meta_value LIKE '%$s%' ";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql,  ARRAY_A );
			$id_data1 = '';
			if(!empty($metas)) {
				foreach ( $metas as $meta ) {
						$id_data1 .= ','. $meta['ID'];
				}
			}

			//物件名
			$sql = "SELECT DISTINCT P.ID";
			$sql .=  " FROM $wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = ''  AND P.post_type ='fudo' ";
			$sql .=  " AND PM_2.meta_key='bukkenmei' AND PM_2.meta_value LIKE '%$s%' ";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql,  ARRAY_A );
			$id_data2 = '';
			if(!empty($metas)) {
				foreach ( $metas as $meta ) {
						$id_data2 .= ','. $meta['ID'];
				}
			}

			//本文・タイトル・抜粋
			$sql = "SELECT DISTINCT P.ID";
			$sql .=  " FROM $wpdb->posts AS P";
			$sql .=  " WHERE (P.post_status='publish' AND P.post_password = ''  AND P.post_type ='fudo' )";
			$sql .=  " AND (P.post_content LIKE '%$s%' OR P.post_title LIKE '%$s%' OR  P.post_excerpt LIKE '%$s%')";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql,  ARRAY_A );
			$id_data3 = '';
			if(!empty($metas)) {
				foreach ( $metas as $meta ) {
						$id_data3 .= ','. $meta['ID'];
				}
			}
			$id_data = ' AND P.ID IN ( 0 ' .$id_data1. $id_data2 . $id_data3 . ')';
		}

		//価格・面積・築年月ソート用
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (( $wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id ) ";

			if($searchtype != ''){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id ) ";
			}else{
				$sql .=  " ) ";
			}

			if($shu_data != '')
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id ";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = ''  AND P.post_type ='fudo' ";
			$sql .=  " AND PM.meta_key='$bukken_sort_data'";

			if($searchtype == '')
			$sql .= $id_data;

			if($searchtype == 'id')
			$sql .=  "     AND PM_1.meta_key='shikibesu' AND PM_1.meta_value LIKE '%$s' ";

			if($searchtype == 'chou')
			$sql .=  "     AND PM_1.meta_key='shozaichimeisho' AND PM_1.meta_value LIKE '%$s%' ";

			if($shu_data != '')
			$sql .=  " AND PM_2.meta_key='bukkenshubetsu' AND CAST(PM_2.meta_value AS SIGNED)".$shu_data."";


			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);


			//更新日順
			if($bukken_sort_data2 == "post_modified"){
					$sql2 .=  " ORDER BY P.post_modified ".$bukken_order_data2;
			}else{
				//テキスト
				if($bukken_sort_data== "tatemonochikunenn"){
					$sql2 .=  " ORDER BY PM.meta_value ".$bukken_order_data;
				}else{
				//数値
					$sql2 .=  " ORDER BY CAST(PM.meta_value AS SIGNED)".$bukken_order_data;
				}
			}

			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";





		//間取・所在地 ソート用
		if($bukken_sort_data== "madorisu" || $bukken_sort_data== "shozaichicode"){

			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";

			if($searchtype != ''){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			}else{
				$sql .=  " ) ";
			}
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";


			if($searchtype == '')
			$sql .= $id_data;

			if($searchtype == 'id')
			$sql .=  "     AND PM_1.meta_key='shikibesu' AND PM_1.meta_value LIKE '%$s' ";

			if($searchtype == 'chou')
			$sql .=  "     AND PM_1.meta_key='shozaichimeisho' AND PM_1.meta_value LIKE '%$s%' ";


			//数値・数値
			if($bukken_sort_data== "madorisu"){
				$sql .=  " AND PM.meta_key='madorisu' ";
				$sql .=  " AND PM_3.meta_key='madorisyurui' ";
			}
			//数値・テキスト
			if($bukken_sort_data== "shozaichicode"){
				$sql .=  " AND PM.meta_key='shozaichicode' ";
				$sql .=  " AND PM_3.meta_key='shozaichimeisho' ";
			}

			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			//数値・数値
			if($bukken_sort_data== "madorisu"){
				$sql2 .=  " ORDER BY CAST(PM.meta_value AS SIGNED)".$bukken_order_data.", CAST(PM_3.meta_value AS SIGNED)".$bukken_order_data."";
			}
			//数値・テキスト
			if($bukken_sort_data== "shozaichicode"){
				$sql2 .=  " ORDER BY CAST(PM.meta_value AS SIGNED)".$bukken_order_data.", PM_3.meta_value".$bukken_order_data."";
			}
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";


		}
	}





	//SQL 地域(県)
	if($bukken_slug_data=="ken"){

			//地域(県) 数値カウント
			$sql  = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";


			//地域(県) 数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			//更新日順
			if($bukken_sort_data2 == "post_modified"){
				$sql2 .=  " ORDER BY P.post_modified ".$bukken_order_data2;
			}else{
				$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			}

			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";



		//面積 ソート用
		if($bukken_sort_data== "tatemonomenseki"){

			//地域(県) 面積カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND (PM_2.meta_key='tatemonomenseki' or PM_2.meta_key='tochikukaku')";

			//地域(県) 面積リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";
		}


		//テキストカウント ソート用
		if($bukken_sort_data== "tatemonochikunenn"){

			//地域(県) テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";


			//地域(県) テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY PM_2.meta_value ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";
		}

		//数値・数値カウント ソート用
		if($bukken_sort_data== "madorisu"){

			//地域(県) 数値・数値カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='madorisu' AND PM_3.meta_key='madorisyurui'";


			//地域(県) 数値・数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", CAST(PM_3.meta_value AS SIGNED) ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

		//数値・テキストカウント ソート用
		if($bukken_sort_data== "shozaichicode"){

			//地域(県) 数値・テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='shozaichicode' ";
			$sql .=  " AND PM_3.meta_key='shozaichimeisho'";

			//地域(県) 数値・テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", PM_3.meta_value ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

	}


	//SQL 地域(県・市区)
	if($bukken_slug_data=="shiku"){

			//地域(県・市区) 数値カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND RIGHT(LEFT(PM.meta_value,5),3)='".$nor_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";

			//地域(県・市区) 数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);

			//更新日順
			if($bukken_sort_data2 == "post_modified"){
				$sql2 .=  " ORDER BY P.post_modified ".$bukken_order_data2;
			}else{
				$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			}
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";



		//面積
		if($bukken_sort_data== "tatemonomenseki"){

			//地域(県・市区) 面積カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND RIGHT(LEFT(PM.meta_value,5),3)='".$nor_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND (PM_2.meta_key='tatemonomenseki' or PM_2.meta_key='tochikukaku')";

			//地域(県・市区) 面積リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";
		}


		//テキストカウント
		if($bukken_sort_data== "tatemonochikunenn"){

			//地域(県・市区) テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode'  AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND RIGHT(LEFT(PM.meta_value,5),3)='".$nor_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";


			//地域(県・市区) テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY PM_2.meta_value ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";
		}

		//数値・数値カウント
		if($bukken_sort_data== "madorisu"){

			//地域(県・市区) 数値・数値カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND RIGHT(LEFT(PM.meta_value,5),3)='".$nor_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='madorisu' AND PM_3.meta_key='madorisyurui'";


			//地域(県・市区) 数値・数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", CAST(PM_3.meta_value AS SIGNED) ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

		//数値・テキストカウント
		if($bukken_sort_data== "shozaichicode"){

			//地域(県・市区) 数値・テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND PM.meta_key='shozaichicode' AND LEFT(PM.meta_value,2)='".$mid_id."'";
			$sql .=  " AND RIGHT(LEFT(PM.meta_value,5),3)='".$nor_id."'";

			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";
			$sql .=  " AND PM_2.meta_key='shozaichicode' AND PM_3.meta_key='shozaichimeisho'";

			//地域(県・市区) 数値・テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", PM_3.meta_value ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

	}


	//SQL 路線
	if($bukken_slug_data=="rosen"){

			//路線 数値カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";

			//路線 数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);

			//更新日順
			if($bukken_sort_data2 == "post_modified"){
				$sql2 .=  " ORDER BY P.post_modified ".$bukken_order_data2;
			}else{
				$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			}

			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";


		//面積
		if($bukken_sort_data== "tatemonomenseki"){

			//路線 面積カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND (PM_2.meta_key='tatemonomenseki' or PM_2.meta_key='tochikukaku')";

			//路線 面積リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}


		//テキストカウント
		if($bukken_sort_data== "tatemonochikunenn"){

			//路線 テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";

			//路線 テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY PM_2.meta_value ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

		//数値・数値カウント
		if($bukken_sort_data== "madorisu"){

			//路線 数値・数値カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='madorisu' AND PM_3.meta_key='madorisyurui'";

			//路線 数値・数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", CAST(PM_3.meta_value AS SIGNED) ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

		//数値・テキストカウント
		if($bukken_sort_data== "shozaichicode"){

			//路線 数値・テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND  PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND PM_2.meta_key='shozaichicode' AND PM_3.meta_key='shozaichimeisho'";

			//路線 数値・テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", PM_3.meta_value ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

	}


	//SQL 駅
	if($bukken_slug_data=="station"){

			//駅 数値カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";
			$sql .=  " AND (PM_3.meta_key='koutsueki1' Or PM_3.meta_key='koutsueki2') ";
			$sql .=  " AND PM_3.meta_value='".$nor_id."'";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";


			//駅 数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);

			//更新日順
			if($bukken_sort_data2 == "post_modified"){
				$sql2 .=  " ORDER BY P.post_modified ".$bukken_order_data2;
			}else{
				$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			}
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";



		//面積
		if($bukken_sort_data== "tatemonomenseki"){

			//駅 面積カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED) ".$shu_data."";
			$sql .=  " AND (PM_3.meta_key='koutsueki1' Or PM_3.meta_key='koutsueki2') ";
			$sql .=  " AND PM_3.meta_value='".$nor_id."'";

			$sql .=  " AND (PM_2.meta_key='tatemonomenseki' or PM_2.meta_key='tochikukaku')";


			//駅 面積リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}



		//テキストカウント
		if($bukken_sort_data== "tatemonochikunenn"){

			//駅 テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM ((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND  PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND (PM_3.meta_key='koutsueki1' Or PM_3.meta_key='koutsueki2') ";
			$sql .=  " AND PM_3.meta_value='".$nor_id."'";

			$sql .=  " AND PM_2.meta_key='".$bukken_sort_data."'";

			//駅 テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY PM_2.meta_value ".$bukken_order_data;
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

		//数値・数値カウント
		if($bukken_sort_data== "madorisu"){

			//駅 数値・数値カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_4 ON P.ID = PM_4.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND (PM_4.meta_key='koutsueki1' Or PM_4.meta_key='koutsueki2') ";
			$sql .=  " AND PM_4.meta_value='".$nor_id."'";

			$sql .=  " AND PM_2.meta_key='madorisu' AND PM_3.meta_key='madorisyurui'";

			//駅 数値・数値リスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", CAST(PM_3.meta_value AS SIGNED) ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

		//数値・テキストカウント
		if($bukken_sort_data== "shozaichicode"){

			//駅 数値・テキストカウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_4 ON P.ID = PM_4.post_id";
			$sql .=  " WHERE P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
			$sql .=  " AND (PM.meta_key='koutsurosen1' Or PM.meta_key='koutsurosen2') ";
			$sql .=  " AND PM.meta_value='".$mid_id."'";
			$sql .=  " AND PM_1.meta_key='bukkenshubetsu' AND CAST(PM_1.meta_value AS SIGNED)".$shu_data."";

			$sql .=  " AND (PM_4.meta_key='koutsueki1' Or PM_4.meta_key='koutsueki2') ";
			$sql .=  " AND PM_4.meta_value='".$nor_id."'";

			$sql .=  " AND PM_2.meta_key='shozaichicode' AND PM_3.meta_key='shozaichimeisho'";

			//駅 数値・テキストリスト
			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID as object_id",$sql);
			$sql2 .=  " ORDER BY CAST(PM_2.meta_value AS SIGNED) ".$bukken_order_data.", PM_3.meta_value ".$bukken_order_data."";
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}

	}





	//SQL 条件検索
	if($bukken_slug_data=="jsearch"){

		$nowym= date('Ym');
		$meta_dat = '';
		$next_sql = true;

		//複数駅
		if( $eki_data != '' ){

			$sql = "SELECT DISTINCT( P.ID )";
			$sql .=  " FROM ($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id ";
			$sql .=  " WHERE  P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
		//	$sql .=    $id_data;
			$sql .=  " AND PM.meta_key='koutsurosen1' AND PM_1.meta_key='koutsueki1' ";
			$sql .=  " AND PM.meta_value !='' ";
			$sql .=  " AND concat( PM.meta_value,PM_1.meta_value) " . $eki_data . "";

		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql,  ARRAY_A );
			$id_data2 = '';
			if(!empty($metas)) {
				$id_data2 = ' OR (P.ID IN ( 0 ';
				foreach ( $metas as $meta ) {
						$id_data2 .= ','. $meta['ID'];
				}
				$id_data2 .= ') )';
			}

			$sql = "SELECT DISTINCT( P.ID )";
			$sql .=  " FROM ($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id";
			$sql .=  " WHERE ( P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";
		//	$sql .=    $id_data;
			$sql .=  " AND PM_2.meta_key='koutsurosen2' AND PM_3.meta_key='koutsueki2' ";
			$sql .=  " AND PM_2.meta_value !='' ";
			$sql .=  " AND concat( PM_2.meta_value,PM_3.meta_value) " . $eki_data . ")";
			$sql .=  " " . $id_data2 . "";


		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql,  ARRAY_A );
			$id_data = '';
			if(!empty($metas)) {
				$id_data = ' AND P.ID IN ( 0 ';
				foreach ( $metas as $meta ) {
						$id_data .= ','. $meta['ID'];
				}
				$id_data .= ') ';
			}
		}


		if( $shu_data != '' && ( ($ken_id !='' && $ken_id != 0) || ($ros_id !='' && $ros_id != 0)  || $ksik_data !='' || $eki_data != ''  ) ){

			//地域・路線駅($meta_dat)
			$sql = "SELECT DISTINCT P.ID";
			$sql .=  " FROM ((( $wpdb->posts AS P";

			//種別
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";

			//県市区 /県市区複数
			if( $ken_id !='' && $ken_id > 0 || $ksik_data !='' ){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_10 ON P.ID = PM_10.post_id) ";
			}else{
				$sql .=  " )";	
			}

			//路線
			if( $ros_id !='' && $ros_id > 0 ){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_12 ON P.ID = PM_12.post_id) ";
			}else{
				$sql .=  " )";	
			}

			//駅
			if( $eki_id !='' && $eki_id > 0 ){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_13 ON P.ID = PM_13.post_id ";
			}

			$sql .=  " WHERE ( ";

			$sql .=  " P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";

			//種別
			$sql .=  " AND PM.meta_key='bukkenshubetsu' AND CAST(PM.meta_value AS SIGNED)".$shu_data."";

			//県
			if( $ken_id !='' && $ken_id > 0 ){
				$sql .=  " AND PM_10.meta_key='shozaichicode' AND LEFT(PM_10.meta_value,2)='".$ken_id."'";
			}
			//市区
			if( $sik_id !='' && $sik_id > 0 ){
				$sql .=  " AND RIGHT(LEFT(PM_10.meta_value,5),3)='".$sik_id."'";
			}


			//県市区 複数
			if( $ksik_data !='' ){
				$sql .=  " AND PM_10.meta_key='shozaichicode' AND PM_10.meta_value ".$ksik_data."";
			}


			if( $ros_id !='' && $ros_id > 0 ){
				$sql .=  " AND PM_12.meta_key='koutsurosen1' AND PM_12.meta_value='".$ros_id."'";
			}

			if( $eki_id !='' && $eki_id > 0 ){
				$sql .=  " AND PM_13.meta_key='koutsueki1' AND PM_13.meta_value='".$eki_id."'";
			}

			//複数駅
			if( $id_data !='') $sql .=  $id_data;

			$sql .=  " ) OR ( ";



			$sql .=  " P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";

			//種別
			$sql .=  " AND PM.meta_key='bukkenshubetsu' AND CAST(PM.meta_value AS SIGNED)".$shu_data."";

			//県
			if( $ken_id !='' && $ken_id > 0 ){
				$sql .=  " AND PM_10.meta_key='shozaichicode' AND LEFT(PM_10.meta_value,2)='".$ken_id."'";
			}
			//市区
			if( $sik_id !='' && $sik_id > 0 ){
				$sql .=  " AND RIGHT(LEFT(PM_10.meta_value,5),3)='".$sik_id."'";
			}


			//県市区 複数
			if( $ksik_data !='' ){
				$sql .=  " AND PM_10.meta_key='shozaichicode' AND PM_10.meta_value ".$ksik_data."";
			}

			if( $ros_id !='' && $ros_id > 0 ){
				$sql .=  " AND PM_12.meta_key='koutsurosen2' AND PM_12.meta_value='".$ros_id."'";
			}

			if( $eki_id !='' && $eki_id > 0 ){
				$sql .=  " AND PM_13.meta_key='koutsueki2' AND PM_13.meta_value='".$eki_id."'";
			}

			//複数駅
			if( $id_data !='') $sql .=  $id_data;

			$sql .=  " ) ";

		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql, ARRAY_A );
			if(!empty($metas)) {
				$i=0;
				foreach ( $metas as $meta ) {
					if($i!=0) $meta_dat .= ",";
					$meta_dat .= $meta['ID'];
					$i++;
				}
			}else{
				$next_sql = false;
			}

		}


		if($next_sql){

			//カウント
			$sql = "SELECT count(DISTINCT P.ID) as co";
			$sql .=  " FROM (((((((((($wpdb->posts AS P";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM   ON P.ID = PM.post_id) ";		//種別

			//価格
			if($kal_data > 0 || $kah_data > 0 || $bukken_sort_data == "kakaku" ){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";	//価格
			}else{
				$sql .=  " )";	
			}

			//歩分
			if($hof_data > 0){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id)";		//歩分
			}else{
				$sql .=  " )";	
			}

			//面積
			if($mel_data > 0 || $meh_data > 0 || $bukken_sort_data == "tatemonomenseki" ){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id)";		//面積
			}else{
				$sql .=  " )";	
			}


			//築年数
			if($tik_data > 0 || $bukken_sort_data == "tatemonochikunenn" ){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_4 ON P.ID = PM_4.post_id)";		//築年数
			}else{
				$sql .=  " )";	
			}

			//設備
			if(!empty($set_id)) {
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_5 ON P.ID = PM_5.post_id)";		//設備
			}else{
				$sql .=  " )";	
			}

			//間取
			if(!empty($madori_id) || $bukken_sort_data == "madorisu" ) {
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_6 ON P.ID = PM_6.post_id)";		//間取
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_7 ON P.ID = PM_7.post_id)";		//間取
			}else{
				$sql .=  ") )";	
			}

			if( $bukken_sort_data == "shozaichicode" ) {
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_10 ON P.ID = PM_10.post_id)";	//所在地
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_11 ON P.ID = PM_11.post_id)";	//所在地2
			}else{
				$sql .=  "))";	
			}


			//歩分
			if($hof_data > 0  && $eki_id !='' && $eki_id > 0  ){
				$sql .=  " INNER JOIN $wpdb->postmeta AS PM_13 ON P.ID = PM_13.post_id ";
			}

			$sql .=  " WHERE ( ";

			$sql .=  " P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";

			//地域・路線駅
			if($meta_dat != ''){
				$sql .=  " AND P.ID IN (".$meta_dat.") ";
			}

			$sql .=  " AND PM.meta_key='bukkenshubetsu' AND CAST(PM.meta_value AS SIGNED)".$shu_data."";	//種別

			//価格
			if($kal_data > 0 || $kah_data > 0 || $bukken_sort_data == "kakaku" ){
				$sql .=  " AND PM_1.meta_key='kakaku' ";
				if( $kal_data > 0 )
					$sql .=  " AND CAST(PM_1.meta_value AS SIGNED) >= $kal_data ";
				if( $kah_data > 0 )
					$sql .=  " AND CAST(PM_1.meta_value AS SIGNED) <= $kah_data ";
			}


			//歩分
			if( $hof_data > 0 ){
				//歩分+駅
				if(  $eki_id !='' && $eki_id > 0  ){

					$sql .=  " AND ( ";

					$sql .=  " ( PM_13.meta_key='koutsueki1' AND PM_13.meta_value='".$eki_id."'";
					$sql .=  " AND PM_2.meta_key='koutsutoho1f' ";
					$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) > 0 AND CAST(PM_2.meta_value AS SIGNED) <= $hof_data )";

					$sql .=  " OR ( PM_13.meta_key='koutsueki2' AND PM_13.meta_value='".$eki_id."'";
					$sql .=  " AND PM_2.meta_key='koutsutoho2f' ";
					$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) > 0 AND CAST(PM_2.meta_value AS SIGNED) <= $hof_data )";

					$sql .=  " ) ";

				}else{
					$sql .=  " AND (PM_2.meta_key='koutsutoho1f' OR PM_2.meta_key='koutsutoho2f' )";
					$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) > 0 ";
					$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) <= $hof_data ";
				}

			}


			//面積
			if($mel_data > 0 || $meh_data > 0 || $bukken_sort_data == "tatemonomenseki" ){

				//tatemonomenseki or tochikukaku
				if( ($bukken_shubetsu > 1100 && $bukken_shubetsu < 1300) || $bukken_shubetsu == 3212 || $shu_data == '< 3000'  ){
						$sql .=  " AND  ( PM_3.meta_key='tochikukaku' OR PM_3.meta_key='tatemonomenseki' )";
				//		$sql .=  " AND  PM_3.meta_key='tochikukaku' ";
				}else{
					if( $is_tochi || $shu_data == '< 3000' ){
						$sql .=  " AND  ( PM_3.meta_key='tochikukaku' OR PM_3.meta_key='tatemonomenseki' )";
					}else{
						$sql .=  " AND  PM_3.meta_key='tatemonomenseki' ";
					}
				}


				if( $mel_data > 0 )
					$sql .=  " AND CAST(PM_3.meta_value AS SIGNED) >= $mel_data ";
				if( $meh_data > 0 )
					$sql .=  " AND CAST(PM_3.meta_value AS SIGNED) <= $meh_data ";
				$sql .=  " AND PM_3.meta_value !='' ";
			}


			//築年数
			if($tik_data > 0 || $bukken_sort_data== "tatemonochikunenn" ){
				$sql .=  " AND PM_4.meta_key='tatemonochikunenn' ";
				if( $tik_data > 0 )
				//	$sql .=  " AND ( CAST(LEFT(PM_4.meta_value,4) AS SIGNED)  *100 + CAST(RIGHT(PM_4.meta_value,2) AS SIGNED) ) >= ($nowym- $tik_data * 100) ";
					$sql .=  " AND ( CAST(LEFT(PM_4.meta_value,4) AS SIGNED)  *100 + CASE WHEN LENGTH(PM_4.meta_value)>5 THEN CAST(RIGHT(PM_4.meta_value,2) AS SIGNED) ELSE 0 END ) >= ($nowym- $tik_data * 100) ";
			}


			//設備
			if(!empty($set_id)) {
				$sql .=  " AND (PM_5.meta_key='setsubi' AND ( ";
				$i=0;
				foreach($set_id as $meta_box){
				//	if($i!=0) $sql .= " OR ";
					if($i!=0) $sql .= " AND ";
					$sql .= " PM_5.meta_value LIKE '%".$set_id[$i]."%'";
					$i++;
				}
				$sql .=  " ))";
			}

			//間取
			if(!empty($madori_id)) {
				$sql .=  " AND ( ";
				$i=0;
				foreach($madori_id as $meta_box){
					$madorisu_data = $madori_id[$i];
					if($i!=0) $sql .= " OR ";
					$sql .= " (PM_6.meta_key='madorisu' AND PM_6.meta_value ='".myLeft($madorisu_data,1)."' ";
					$sql .= " AND PM_7.meta_key='madorisyurui' AND PM_7.meta_value ='".myRight($madorisu_data,2)."')";
					$i++;
				}
				$sql .=  " ) ";
			}else{
				if( $bukken_sort_data== "madorisu" ){
					$sql .= " AND PM_6.meta_key='madorisu'";
					$sql .= " AND PM_7.meta_key='madorisyurui'";
				}
			}

			if( $bukken_sort_data== "shozaichicode" ) {
				$sql .=  " AND PM_10.meta_key='shozaichicode'";
				$sql .=  " AND PM_11.meta_key='shozaichimeisho'";
			}



		/*
			if( $bukken_sort_data == "tatemonomenseki" ){

				$sql .=  " ) OR ( ";

				$sql .=  " P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo' ";

				//地域・路線駅
				if($meta_dat != ''){
					$sql .=  " AND P.ID IN (".$meta_dat.") ";
				}

				$sql .=  " AND PM.meta_key='bukkenshubetsu' AND CAST(PM.meta_value AS SIGNED)".$shu_data."";	//種別

				//価格
				if($kal_data > 0 || $kah_data > 0 || $bukken_sort_data == "kakaku" ){
					$sql .=  " AND PM_1.meta_key='kakaku' ";
					if( $kal_data > 0 )
						$sql .=  " AND CAST(PM_1.meta_value AS SIGNED) >= $kal_data ";
					if( $kah_data > 0 )
						$sql .=  " AND CAST(PM_1.meta_value AS SIGNED) <= $kah_data ";
				}


				//歩分
				if( $hof_data > 0 ){
					//歩分+駅
					if(  $eki_id !='' && $eki_id > 0  ){

						$sql .=  " AND ( ";

						$sql .=  " ( PM_13.meta_key='koutsueki1' AND PM_13.meta_value='".$eki_id."'";
						$sql .=  " AND PM_2.meta_key='koutsutoho1f' ";
						$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) > 0 AND CAST(PM_2.meta_value AS SIGNED) <= $hof_data )";

						$sql .=  " OR ( PM_13.meta_key='koutsueki2' AND PM_13.meta_value='".$eki_id."'";
						$sql .=  " AND PM_2.meta_key='koutsutoho2f' ";
						$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) > 0 AND CAST(PM_2.meta_value AS SIGNED) <= $hof_data )";

						$sql .=  " ) ";

					}else{
						$sql .=  " AND (PM_2.meta_key='koutsutoho1f' OR PM_2.meta_key='koutsutoho2f' )";
						$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) > 0 ";
						$sql .=  " AND CAST(PM_2.meta_value AS SIGNED) <= $hof_data ";
					}

				}


				//面積
				if($mel_data > 0 || $meh_data > 0 || $bukken_sort_data == "tatemonomenseki" ){

					//tatemonomenseki or tochikukaku
					//if( $bukken_shubetsu > 1100 && $bukken_shubetsu < 1200 ){
						$sql .=  " AND  PM_3.meta_key='tochikukaku' ";
					//}else{
					//	$sql .=  " AND  PM_3.meta_key='tatemonomenseki' ";
					//}

					if( $mel_data > 0 )
						$sql .=  " AND CAST(PM_3.meta_value AS SIGNED) >= $mel_data ";
					if( $meh_data > 0 )
						$sql .=  " AND CAST(PM_3.meta_value AS SIGNED) <= $meh_data ";
					$sql .=  " AND PM_3.meta_value !='' ";
				}


				//築年数
				if($tik_data > 0 || $bukken_sort_data== "tatemonochikunenn" ){
					$sql .=  " AND PM_4.meta_key='tatemonochikunenn' ";
					if( $tik_data > 0 )
					//	$sql .=  " AND ( CAST(LEFT(PM_4.meta_value,4) AS SIGNED)  *100 + CAST(RIGHT(PM_4.meta_value,2) AS SIGNED) ) >= ($nowym- $tik_data * 100) ";
						$sql .=  " AND ( CAST(LEFT(PM_4.meta_value,4) AS SIGNED)  *100 + CASE WHEN LENGTH(PM_4.meta_value)>5 THEN CAST(RIGHT(PM_4.meta_value,2) AS SIGNED) ELSE 0 END ) >= ($nowym- $tik_data * 100) ";
				}


				//設備
				if(!empty($set_id)) {
					$sql .=  " AND (PM_5.meta_key='setsubi' AND ( ";
					$i=0;
					foreach($set_id as $meta_box){
					//	if($i!=0) $sql .= " OR ";
						if($i!=0) $sql .= " AND ";
						$sql .= " PM_5.meta_value LIKE '%".$set_id[$i]."%'";
						$i++;
					}
					$sql .=  " ))";
				}

				//間取
				if(!empty($madori_id)) {
					$sql .=  " AND ( ";
					$i=0;
					foreach($madori_id as $meta_box){
						$madorisu_data = $madori_id[$i];
						if($i!=0) $sql .= " OR ";
						$sql .= " (PM_6.meta_key='madorisu' AND PM_6.meta_value ='".myLeft($madorisu_data,1)."' ";
						$sql .= " AND PM_7.meta_key='madorisyurui' AND PM_7.meta_value ='".myRight($madorisu_data,2)."')";
						$i++;
					}
					$sql .=  " ) ";
				}else{
					if( $bukken_sort_data== "madorisu" ){
						$sql .= " AND PM_6.meta_key='madorisu'";
						$sql .= " AND PM_7.meta_key='madorisyurui'";
					}
				}

				if( $bukken_sort_data== "shozaichicode" ) {
					$sql .=  " AND PM_10.meta_key='shozaichicode'";
					$sql .=  " AND PM_11.meta_key='shozaichimeisho'";
				}
			}
		*/


			$sql .=  " ) ";


			$sql2 = str_replace("SELECT count(DISTINCT P.ID) as co","SELECT DISTINCT P.ID AS object_id",$sql);

			//更新日順
			if($bukken_sort_data2 == "post_modified"){
				$sql2 .=  " ORDER BY P.post_modified ".$bukken_order_data2;
			}else{

				//価格
				if($bukken_sort_data == "kakaku"){
					$sql2 .=  " ORDER BY CAST(PM_1.meta_value AS SIGNED) ".$bukken_order_data;
				}

				//面積
				if($bukken_sort_data == "tatemonomenseki"){
					$sql2 .=  " ORDER BY CAST(PM_3.meta_value AS SIGNED) ".$bukken_order_data;
				}

				//テキストカウント
				if($bukken_sort_data== "tatemonochikunenn"){
					$sql2 .=  " ORDER BY CAST(PM_4.meta_value AS SIGNED) ".$bukken_order_data;
				}

				//数値・数値カウント
				if($bukken_sort_data== "madorisu"){
					$sql2 .=  " ORDER BY CAST(PM_6.meta_value AS SIGNED) ".$bukken_order_data.", CAST(PM_7.meta_value AS SIGNED) ".$bukken_order_data."";
				}

				//数値・テキストカウント
				if($bukken_sort_data== "shozaichicode"){
					$sql2 .=  " ORDER BY CAST(PM_10.meta_value AS SIGNED) ".$bukken_order_data.", PM_11.meta_value ".$bukken_order_data."";
				}
			}
			$sql2 .=  " LIMIT ".$limit_from.",".$limit_to."";

		}	//$next_sql

	}

/**** 検索 SQL END ****/



	//カウント
		if($sql !=''){
			//$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_row( $sql );
			if( !empty($metas) ) $metas_co = $metas->co;	
		}else{
			$metas_co = 0;
		}

	//ソート・ページナビ
		$page_navigation = '';

		if($metas_co != 0 ){
			$kak_img = '<img src="'.$plugin_url.'img/sortbtms_.png" border="0" align="absmiddle">';
			if($bukken_sort == 'kak' && $bukken_order =='')
				$kak_img = '<img src="'.$plugin_url.'img/sortbtms_asc.png" border="0" align="absmiddle">';
			if($bukken_sort=='kak' && $bukken_order =='d')
				$kak_img = '<img src="'.$plugin_url.'img/sortbtms_desc.png" border="0" align="absmiddle">';


			if($bukken_sort_data2 == "post_modified" && $bukken_sort == '')
				$kak_img = '<img src="'.$plugin_url.'img/sortbtms_.png" border="0" align="absmiddle">';


			$tam_img = '<img src="'.$plugin_url.'img/sortbtms_.png" border="0" align="absmiddle">';
			if($bukken_sort=='tam' && $bukken_order =='')
			$tam_img = '<img src="'.$plugin_url.'img/sortbtms_asc.png" border="0" align="absmiddle">';
			if($bukken_sort=='tam' && $bukken_order =='d')
			$tam_img = '<img src="'.$plugin_url.'img/sortbtms_desc.png" border="0" align="absmiddle">';

			$mad_img = '<img src="'.$plugin_url.'img/sortbtms_.png" border="0" align="absmiddle">';
			if($bukken_sort=='mad' && $bukken_order =='')
			$mad_img = '<img src="'.$plugin_url.'img/sortbtms_asc.png" border="0" align="absmiddle">';
			if($bukken_sort=='mad' && $bukken_order =='d')
			$mad_img = '<img src="'.$plugin_url.'img/sortbtms_desc.png" border="0" align="absmiddle">';

			$sho_img = '<img src="'.$plugin_url.'img/sortbtms_.png" border="0" align="absmiddle">';
			if($bukken_sort=='sho' && $bukken_order =='')
			$sho_img = '<img src="'.$plugin_url.'img/sortbtms_asc.png" border="0" align="absmiddle">';
			if($bukken_sort=='sho' && $bukken_order =='d')
			$sho_img = '<img src="'.$plugin_url.'img/sortbtms_desc.png" border="0" align="absmiddle">';

			$tac_img = '<img src="'.$plugin_url.'img/sortbtms_.png" border="0" align="absmiddle">';
			if($bukken_sort=='tac' && $bukken_order =='')
			$tac_img = '<img src="'.$plugin_url.'img/sortbtms_asc.png" border="0" align="absmiddle">';
			if($bukken_sort=='tac' && $bukken_order =='d')
			$tac_img = '<img src="'.$plugin_url.'img/sortbtms_desc.png" border="0" align="absmiddle">';

			$page_navigation = '<div id="nav-above1" class="navigation">';
			$page_navigation .= '<div class="nav-previous">';


			//条件検索
			if($bukken_slug_data=="jsearch"){

				//url生成

				//間取り
				$madori_url = '';
				if(!empty($madori_id)) {
					$i=0;
					foreach($madori_id as $meta_box){
						$madori_url .= '&amp;mad[]='.$madori_id[$i];
						$i++;
					}
				}

				//設備条件
				$setsubi_url = '';
				if(!empty($set_id)) {
					$i=0;
					foreach($set_id as $meta_box){
						$setsubi_url .= '&amp;set[]='.$set_id[$i];
						$i++;
					}
				}

				$add_url  = '';

				//複数種別
				if( $shub !='' ) $add_url  .= '&amp;shub='.$shub;

				if (is_array($bukken_shubetsu)) {
					$i=0;
					foreach($bukken_shubetsu as $meta_set){
						$add_url  .= '&amp;shu[]='.$bukken_shubetsu[$i];
						$i++;
					}

				} else {
					$add_url  .= '&amp;shu='.$bukken_shubetsu;
				} 

			//	if($ken_id != '') $ken_id = intval($ken_id);

				$add_url .= '&amp;ros='. $ros_id;
				$add_url .= '&amp;eki='. $eki_id;
				$add_url .= '&amp;ken='. $ken_id;
				$add_url .= '&amp;sik='. $sik_id;
				$add_url .= '&amp;kalc='.$kalc_data;
				$add_url .= '&amp;kahc='.$kahc_data;
				$add_url .= '&amp;kalb='.$kalb_data;
				$add_url .= '&amp;kahb='.$kahb_data;
				$add_url .= '&amp;hof='. $hof_data;
				$add_url .= $madori_url;
				$add_url .= '&amp;tik='. $tik_data;
				$add_url .= '&amp;mel='. $mel_data;
				$add_url .= '&amp;meh='. $meh_data;
				$add_url .= $setsubi_url;

				$joken_url  = $site .'?bukken=jsearch';


				//複数市区
				if (is_array($ksik_id)) {
					$i=0;
					foreach($ksik_id as $meta_set){
						$add_url .= '&amp;ksik[]='.$ksik_id[$i];
						$i++;
					}
				}

				//複数駅
				if(is_array( $rosen_eki )  ){
					$i=0;
					foreach($rosen_eki as $meta_set){
						$add_url .= '&amp;re[]='.$rosen_eki[$i];
						$i++;
					}
				}

				$joken_url .= $add_url;
			//	$joken_url .= '&amp;btn=%E7%89%A9%E4%BB%B6%E6%A4%9C%E7%B4%A2';

				if($bukken_sort=='kak') $page_navigation .= '<b>';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=kak&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$kak_img.'価格</a> ';
				if($bukken_sort=='kak') $page_navigation .= '</b>';

				if($bukken_sort=='tam') $page_navigation .= '<b>';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=tam&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$tam_img.'面積</a> ';
				if($bukken_sort=='tam') $page_navigation .= '</b>';

				if($bukken_sort=='mad') $page_navigation .= '<b>';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=mad&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$mad_img.'間取</a> ';
				if($bukken_sort=='mad') $page_navigation .= '</b>';

				if($bukken_sort=='sho') $page_navigation .= '<b>';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=sho&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$sho_img.'住所</a> ';
				if($bukken_sort=='sho') $page_navigation .= '</b>';

				if($bukken_sort=='tac') $page_navigation .= '<b>';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=tac&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$tac_img.'築年月</a>';
				if($bukken_sort=='tac') $page_navigation .= '</b>';


			}else{

				//カテゴリ・タグ
				if( isset($_GET['bukken_tag']) ){
					$joken_url = $site.'?bukken_tag='.$slug_data.'';
				}else{
					$joken_url = $site.'?bukken='.$slug_data.'';
				}


				if($s != ''){
					$joken_url  = $site .'?s='.$s.'&bukken=search';

					if($searchtype == 'id')
						$joken_url  .= '&st=id';

					if($searchtype == 'chou')
						$joken_url  .= '&st=chou';
				}



				$add_url  = '&amp;shu='.$bukken_shubetsu;
				$add_url .= '&amp;mid='.$mid_id;
				$add_url .= '&amp;nor='.$nor_id;
			//	if( $searchtype !='' ) $add_url .= '&amp;st='.$searchtype;

				if ($taxonomy_name == '') $joken_url .= $add_url;


				if($bukken_sort=='kak') $page_navigation .= '<b>';
			//	$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=kak&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$kak_img.'価格</a> ';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=kak&amp;ord='.$bukken_order.'">'.$kak_img.'価格</a> ';
				if($bukken_sort=='kak') $page_navigation .= '</b>';

				if($bukken_sort=='tam') $page_navigation .= '<b>';
			//	$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=tam&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$tam_img.'面積</a> ';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=tam&amp;ord='.$bukken_order.'">'.$tam_img.'面積</a> ';
				if($bukken_sort=='tam') $page_navigation .= '</b>';

				if($bukken_sort=='mad') $page_navigation .= '<b>';
			//	$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=mad&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$mad_img.'間取</a> ';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=mad&amp;ord='.$bukken_order.'">'.$mad_img.'間取</a> ';
				if($bukken_sort=='mad') $page_navigation .= '</b>';

				if($bukken_sort=='sho') $page_navigation .= '<b>';
			//	$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=sho&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$sho_img.'住所</a> ';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=sho&amp;ord='.$bukken_order.'">'.$sho_img.'住所</a> ';
				if($bukken_sort=='sho') $page_navigation .= '</b>';

				if($bukken_sort=='tac') $page_navigation .= '<b>';
			//	$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=tac&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$tac_img.'築年月</a>';
				$page_navigation .= '<a href="'.$joken_url.'&amp;paged='.$bukken_page_data.'&amp;so=tac&amp;ord='.$bukken_order.'">'.$tac_img.'築年月</a>';
				if($bukken_sort=='tac') $page_navigation .= '</b>';

			}


			$page_navigation .= '</div>';
			$page_navigation .= '<div class="nav-next">';

			if($bukken_order=="d"){
				$bukken_order = "";
			}else{
				$bukken_order = "d";
			}

			//ページナビ
			$page_navigation .= f_page_navi($metas_co,$posts_per_page,$bukken_page_data,$bukken_sort,$bukken_order,$s,$joken_url);

			$page_navigation .= '</div>';
			$page_navigation .= '</div><!-- #nav-above -->';
		}


	//物件一覧ページ
	get_header(); 

?>
		<div id="container" class="site-content archive_fudo">
			<div id="content" role="main">

			<?php do_action( 'archive-fudo1' ); ?>

			<?php if( $joken_url !='' ) { ?>
				<h1 class="page-title"><a href="<?php echo $joken_url;?>"><?php echo $org_title; ?></a></h1>
			<?php  }else{  ?>
				<h1 class="page-title"><?php echo $org_title; ?></h1>
			<?php  } ?>


			<?php echo $page_navigation; ?>
			<div id="list_simplepage">
			<?php
				//loop SQL
				if($sql !=''){
					//$sql2 = $wpdb->prepare($sql2,'');
					$metas = $wpdb->get_results( $sql2, ARRAY_A );
					if(!empty($metas)) {

						foreach ( $metas as $meta ) {
							$meta_id = $meta['object_id'];	//post_id
							$meta_data = get_post( $meta_id ); 
							$meta_title =  $meta_data->post_title;

							require 'archive-fudo-loop.php';

						}
					}else{

						echo "物件がありませんでした。";

					}
				}else{
						echo "条件があいませんでした。";
				}
				//loop SQL END
			?>
			</div><!-- list_simplepage -->

			<?php echo $page_navigation; ?>

			<?php do_action( 'archive-fudo2' ); ?>

			<br /><p align="right" class="pageback"><a href="#" onClick="history.back(); return false;">前のページにもどる</a></p>

			</div><!-- #content -->
		</div><!-- #container -->


<?php get_sidebar(); ?>
<?php get_footer(); ?>




<?php
/**
 * 坪単価
 *
 * @since Fudousan Plugin 1.0.0
 *
 * @param int $post_id Post ID.
 */
function my_custom_kakakutsubo_print($post_id) {
	$kakakutsubo_data = get_post_meta($post_id,'kakakutsubo',true);
	if(is_numeric($kakakutsubo_data)){
		echo floatval($kakakutsubo_data)/10000;
		echo "万円";
	}
}


/**
 * 物件種別
 *
 * @since Fudousan Plugin 1.4.3 *
 * @param int $post_id Post ID.
 */
function my_custom_bukkenshubetsu_print($post_id) {
	$bukkenshubetsu_txt= '';
	$bukkenshubetsu_data = get_post_meta($post_id,'bukkenshubetsu',true);

	global $work_bukkenshubetsu;
	foreach ($work_bukkenshubetsu as $meta_box ){

		if( $bukkenshubetsu_data == $meta_box['id'] ){
			$bukkenshubetsu_txt = $meta_box['name'];
			$bukkenshubetsu_txt = str_replace( "【売地】" ,"" , $bukkenshubetsu_txt);
			$bukkenshubetsu_txt = str_replace( "【売戸建】" ,"" , $bukkenshubetsu_txt);
			$bukkenshubetsu_txt = str_replace( "【売マン】" ,"" , $bukkenshubetsu_txt);
			$bukkenshubetsu_txt = str_replace( "【売建物全部】" ,"" , $bukkenshubetsu_txt);
			$bukkenshubetsu_txt = str_replace( "【売建物一部】" ,"" , $bukkenshubetsu_txt);
			$bukkenshubetsu_txt = str_replace( "【賃貸居住】" ,"" , $bukkenshubetsu_txt);
			$bukkenshubetsu_txt = str_replace( "【賃貸事業】" ,"" , $bukkenshubetsu_txt);
			echo $bukkenshubetsu_txt;
			break;
		}
	}
}


/**
 * 所在地
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_shozaichi_print($post_id) {
	global $wpdb;

	$shozaichiken_data = get_post_meta($post_id,'shozaichicode',true);
	$shozaichiken_data = myLeft($shozaichiken_data,2);

	if($shozaichiken_data=="")
		$shozaichiken_data = get_post_meta($post_id,'shozaichiken',true);

		$sql = "SELECT `middle_area_name` FROM `".$wpdb->prefix."area_middle_area` WHERE `middle_area_id`=".$shozaichiken_data."";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_row( $sql );
		if( !empty($metas) ) echo $metas->middle_area_name;

	$shozaichicode_data = get_post_meta($post_id,'shozaichicode',true);
	$shozaichicode_data = myLeft($shozaichicode_data,5);
	$shozaichicode_data = myRight($shozaichicode_data,3);

	if($shozaichiken_data !="" && $shozaichicode_data !=""){
		$sql = "SELECT narrow_area_name FROM ".$wpdb->prefix."area_narrow_area WHERE middle_area_id=".$shozaichiken_data." and narrow_area_id =".$shozaichicode_data."";

	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_row( $sql );
		if( !empty($metas) ) echo $metas->narrow_area_name;
	}
}

/**
 * 路線 駅 バス 徒歩
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_koutsu1_print($post_id) {
	global $wpdb;
	$rosen_name = '';

	$koutsurosen_data = get_post_meta($post_id, 'koutsurosen1', true);
	$koutsueki_data = get_post_meta($post_id, 'koutsueki1', true);

	$shozaichiken_data = get_post_meta($post_id,'shozaichicode',true);
	$shozaichiken_data = myLeft($shozaichiken_data,2);

	if($koutsurosen_data !=""){
		$sql = "SELECT `rosen_name` FROM `".$wpdb->prefix."train_rosen` WHERE `rosen_id` =".$koutsurosen_data."";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_row( $sql );
		if(!empty($metas)) $rosen_name = $metas->rosen_name;
		echo "".$rosen_name;
	}

	if($koutsurosen_data !="" && $koutsueki_data !=""){
		$sql = "SELECT DTS.station_name";
		$sql .=  " FROM ".$wpdb->prefix."train_rosen AS DTR";
		$sql .=  " INNER JOIN ".$wpdb->prefix."train_station as DTS ON DTR.rosen_id = DTS.rosen_id";
		$sql .=  " WHERE DTS.station_id=".$koutsueki_data." AND DTS.rosen_id=".$koutsurosen_data."";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_row( $sql );
		if(!empty($metas)) {
			if($metas->station_name != '＊＊＊＊') 	echo $metas->station_name.'駅';
		}
	}

	$koutsubusstei=get_post_meta($post_id, 'koutsubusstei1', true);
	$koutsubussfun=get_post_meta($post_id, 'koutsubussfun1', true);
	$koutsutohob1f=get_post_meta($post_id, 'koutsutohob1f', true);

	if($koutsubusstei !="" || $koutsubussfun !=""){

		if($rosen_name == 'バス'){
			echo '(' . $koutsubusstei;
			if(!empty($koutsubussfun)) echo ' '.$koutsubussfun.'分';
		}else{
			echo ' バス(' . $koutsubusstei;
			if(!empty($koutsubussfun)) echo ' '.$koutsubussfun.'分';
		}

		if($koutsutohob1f !="" )
			echo ' 停歩'.$koutsutohob1f.'分';
		echo ')';
	}


	if(get_post_meta($post_id, 'koutsutoho1', true) !="")
		echo ' 徒歩'.get_post_meta($post_id, 'koutsutoho1', true).'m';

	if(get_post_meta($post_id, 'koutsutoho1f', true) !="")
		echo ' 徒歩'.get_post_meta($post_id, 'koutsutoho1f', true).'分';

}
function my_custom_koutsu2_print($post_id) {
	global $wpdb;
	$rosen_name = '';

	$koutsurosen_data = get_post_meta($post_id, 'koutsurosen2', true);
	$koutsueki_data = get_post_meta($post_id, 'koutsueki2', true);

	$shozaichiken_data = get_post_meta($post_id,'shozaichicode',true);
	$shozaichiken_data = myLeft($shozaichiken_data,2);

	if($koutsurosen_data !=""){
		$sql = "SELECT `rosen_name` FROM `".$wpdb->prefix."train_rosen` WHERE `rosen_id` =".$koutsurosen_data."";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_row( $sql );
		if(!empty($metas)) $rosen_name = $metas->rosen_name;
		echo "<br />".$rosen_name;
	}

	if($koutsurosen_data !="" && $koutsueki_data !=""){
		$sql = "SELECT DTS.station_name";
		$sql .=  " FROM ".$wpdb->prefix."train_rosen AS DTR";
		$sql .=  " INNER JOIN ".$wpdb->prefix."train_station AS DTS ON DTR.rosen_id = DTS.rosen_id";
		$sql .=  " WHERE DTS.station_id=".$koutsueki_data." AND DTS.rosen_id=".$koutsurosen_data."";
	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_row( $sql );
		if(!empty($metas)) {
			if($metas->station_name != '＊＊＊＊') 	echo $metas->station_name.'駅';
		}
	}

	$koutsubusstei=get_post_meta($post_id, 'koutsubusstei2', true);
	$koutsubussfun=get_post_meta($post_id, 'koutsubussfun2', true);
	$koutsutohob2f=get_post_meta($post_id, 'koutsutohob2f', true);

	if($koutsubusstei !="" || $koutsubussfun !=""){

		if($rosen_name == 'バス'){
			echo '(' . $koutsubusstei;
			if(!empty($koutsubussfun)) echo ' '.$koutsubussfun.'分';
		}else{
			echo ' バス(' . $koutsubusstei;
			if(!empty($koutsubussfun)) echo ' '.$koutsubussfun.'分';
		}

		if($koutsutohob2f !="" )
			echo ' 停歩'.$koutsutohob2f.'分';
		echo ')';
	}


	if(get_post_meta($post_id, 'koutsutoho2', true) !="")
		echo ' 徒歩'.get_post_meta($post_id, 'koutsutoho2', true).'m';

	if(get_post_meta($post_id, 'koutsutoho2f', true) !="")
		echo ' 徒歩'.get_post_meta($post_id, 'koutsutoho2f', true).'分';

	echo '';

}


/**
 * 建物構造
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_tatemonokozo_print($post_id) {
	$tatemonokozo_data = get_post_meta($post_id,'tatemonokozo',true);
	if($tatemonokozo_data=="1") 	echo '木造';
	if($tatemonokozo_data=="2") 	echo 'ブロック';
	if($tatemonokozo_data=="3") 	echo '鉄骨造';
	if($tatemonokozo_data=="4") 	echo 'RC';
	if($tatemonokozo_data=="5") 	echo 'SRC';
	if($tatemonokozo_data=="6") 	echo 'PC';
	if($tatemonokozo_data=="7") 	echo 'HPC';
	if($tatemonokozo_data=="9") 	echo 'その他';
	if($tatemonokozo_data=="10") 	echo '軽量鉄骨';
	if($tatemonokozo_data=="11") 	echo 'ALC';
	if($tatemonokozo_data=="12") 	echo '鉄筋ブロック';
	if($tatemonokozo_data=="13") 	echo 'CFT(コンクリート充填鋼管)';

	//text
	if( $tatemonokozo_data !='' && !is_numeric($tatemonokozo_data) ) echo $tatemonokozo_data;

}

/**
 * 建物面積計測方式
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_tatemonohosiki_print($post_id) {
	if(get_post_meta($post_id,'tatemonohosiki',true)=="1")	echo '壁芯';
	if(get_post_meta($post_id,'tatemonohosiki',true)=="2")	echo '内法';
	//text
	if( get_post_meta($post_id,'tatemonohosiki',true) !='' && !is_numeric(get_post_meta($post_id,'tatemonohosiki',true)) ) echo get_post_meta($post_id,'tatemonohosiki',true);

}

/**
 * 新築・未入居
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_tatemonoshinchiku_print($post_id) {
	//if(get_post_meta($post_id,'tatemonoshinchiku',true)=="0") echo '中古　';
	if(get_post_meta($post_id,'tatemonoshinchiku',true)=="1") echo '新築未入居　';
	//text
	if( get_post_meta($post_id,'tatemonoshinchiku',true) !='' && !is_numeric(get_post_meta($post_id,'tatemonoshinchiku',true)) ) echo get_post_meta($post_id,'tatemonoshinchiku',true).'　';
}



/**
 * 間取り 部屋種類
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_madorisu_print($post_id) {
	$madorisyurui_data = get_post_meta($post_id,'madorisyurui',true);
	echo get_post_meta($post_id,'madorisu',true);
	if($madorisyurui_data=="10")	echo 'R';
	if($madorisyurui_data=="20")	echo 'K';
	if($madorisyurui_data=="25")	echo 'SK';
	if($madorisyurui_data=="30")	echo 'DK';
	if($madorisyurui_data=="35")	echo 'SDK';
	if($madorisyurui_data=="40")	echo 'LK';
	if($madorisyurui_data=="45")	echo 'SLK';
	if($madorisyurui_data=="50")	echo 'LDK';
	if($madorisyurui_data=="55")	echo 'SLDK';
}


/**
 * 賃料・価格
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_kakaku_print($post_id) {
	//非公開の場合
	if(get_post_meta($post_id,'kakakukoukai',true) == "0"){

		$kakakujoutai_data = get_post_meta($post_id,'kakakujoutai',true);
		if($kakakujoutai_data=="1")	echo '相談';
		if($kakakujoutai_data=="2")	echo '確定';
		if($kakakujoutai_data=="3")	echo '入札';

	}else{
		$kakaku_data = get_post_meta($post_id,'kakaku',true);
		if(is_numeric($kakaku_data)){
			echo floatval($kakaku_data)/10000;
			echo "万円";
		}
	}
}

/**
 * 礼金・万円/月数
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_kakakureikin_print($post_id) {
	$kakakureikin_data = get_post_meta($post_id,'kakakureikin',true);
		if( $kakakureikin_data == '0' ) {
				echo "0";
		}else{
		
			if($kakakureikin_data >= 100) {
				echo floatval($kakakureikin_data)/10000;
				echo "万円";
			}else{
				echo $kakakureikin_data;
				echo "ヶ月";
			}
		}
}

/**
 * 敷金・万円/月数
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_kakakushikikin_print($post_id) {
	$kakakushikikin_data = get_post_meta($post_id,'kakakushikikin',true);
		if( $kakakushikikin_data == '0' ) {
				echo "0";
		}else{
			if($kakakushikikin_data >= 100) {
				echo floatval($kakakushikikin_data)/10000;
				echo "万円";
			}else{
				echo $kakakushikikin_data;
				echo "ヶ月";
			}
		}
}

/**
 * 保証金・万円/月数
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_kakakuhoshoukin_print($post_id) {
	$kakakuhoshoukin_data = get_post_meta($post_id,'kakakuhoshoukin',true);
		if( $kakakuhoshoukin_data == '0' ) {
				echo "0";
		}else{
			if($kakakuhoshoukin_data >= 100) {
				echo floatval($kakakuhoshoukin_data)/10000;
				echo "万円";
			}else{
				echo $kakakuhoshoukin_data;
				echo "ヶ月";
			}
		}
}

/**
 * 権利金・万円/月数
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_kakakukenrikin_print($post_id) {
	$kakakukenrikin_data = get_post_meta($post_id,'kakakukenrikin',true);
		if( $kakakukenrikin_data == '0' ) {
				echo "0";
		}else{
			if($kakakukenrikin_data >= 100) {
				echo floatval($kakakukenrikin_data)/10000;
				echo "万円";
			}else{
				echo $kakakukenrikin_data;
				echo "ヶ月";
			}
		}
}

/**
 * 償却・敷引金・%/万円/月数
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_kakakushikibiki_print($post_id) {
	$kakakushikibiki_data = get_post_meta($post_id,'kakakushikibiki',true);
		if( $kakakushikibiki_data == '0' ) {
				echo "0";
		}else{
			if($kakakushikibiki_data < 100) {
				echo $kakakushikibiki_data;
				echo "ヶ月";
			}elseif($kakakushikibiki_data>100 && $kakakushikibiki_data<=200){
				echo floatval($kakakushikibiki_data)-100;
				echo "%";
			}elseif($kakakushikibiki_data>200){
				echo floatval($kakakushikibiki_data)/10000;
				echo "万円";
			}
		}
}

/**
 * 更新料・円/月数
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_kakakukoushin_print($post_id) {
	$kakakukoushin_data = get_post_meta($post_id,'kakakukoushin',true);
		if( $kakakukoushin_data == '0' ) {
				echo "0";
		}else{

			if($kakakukoushin_data >= 100) {
				echo $kakakukoushin_data;
				echo "円";
			}else{
				echo $kakakukoushin_data;
				echo "ヶ月";
			}
		}
}

/**
 * 駐車場
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int $post_id Post ID.
 */
function my_custom_chushajo_print_archive($post_id) {

	$tmp_data = '';
	$chushajokubun_data = get_post_meta($post_id,'chushajokubun',true);
	if($chushajokubun_data=="1")	$tmp_data .=  '空有';
	if($chushajokubun_data=="2")	$tmp_data .=  '空無';
	if($chushajokubun_data=="3")	$tmp_data .=  '近隣';
	if($chushajokubun_data=="4")	$tmp_data .=  '無';
	//text
	if( $chushajokubun_data !='' && !is_numeric($chushajokubun_data) ) $tmp_data .=  $chushajokubun_data;

	$chushajoryokin_data = get_post_meta($post_id,'chushajoryokin',true);
	if($chushajoryokin_data !="")
		$tmp_data .= ' ' . $chushajoryokin_data.'円';

	if( $tmp_data != '' ) $tmp_data = '<dt>駐車場</dt><dd>' . $tmp_data . '</dd>';
	echo $tmp_data;
}

/**
 * ページナビゲション
 *
 * @since Fudousan Plugin 1.0.1 *
 * @param int $fw_record_count.
 * @param int $fw_page_size.
 * @param int $fw_page_count.
 * @param string $bukken_sort.
 * @param string $bukken_order.
 * @param string $s.
 * @param string $joken_url.
 * @return text
 */
function f_page_navi( $fw_record_count , $fw_page_size , $fw_page_count , $bukken_sort , $bukken_order , $s , $joken_url ){

	$navi_max = 5;
	$k = 0;

	$move_str = $fw_record_count.'件 ';

	if($fw_page_count=="")
		$fw_page_count =1;

	if ($fw_record_count > $fw_page_size){

		$w_max_page = intval($fw_record_count / $fw_page_size);

		if( ($fw_record_count % $fw_page_size) <> 0 )
			$w_max_page = $w_max_page + 1;

		if( intval($fw_page_count) >= intval($navi_max)){
			$w_loop_start = $fw_page_count - intval($navi_max/2);
		}else{
			$w_loop_start = 1;
		}

		if( $w_max_page < ($fw_page_count + intval($navi_max/2)))
			$w_loop_start = $w_max_page-$navi_max + 1;

		if( $w_loop_start < 1)
			$w_loop_start =  1;


		if( $fw_page_count > 1){
			$move_str .='<a href="'.$joken_url.'&amp;paged='.($fw_page_count-1).'&amp;so='.$bukken_sort.'&amp;ord='.$bukken_order.'&amp;s='.$s.'">&laquo;</a> ';
		}


		if( $w_loop_start <> 1)
			$move_str .='<a href="'.$joken_url.'&amp;paged=&amp;so='.$bukken_sort.'&amp;ord='.$bukken_order.'&amp;s='.$s.'">1</a> ';

		if( $w_loop_start > 2)
			$move_str .='.. ';


		for ($j=$w_loop_start; $j<$w_max_page+1;$j++){

			if ($j == $fw_page_count){
				$move_str .='<b>'.$j.'</b> ';
			}else{
				$move_str .='<a href="'.$joken_url.'&amp;paged='.$j.'&amp;so='.$bukken_sort.'&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$j.'</a> ';
			}
			
			$k++;
			if ($k >= $navi_max)
				break;
		}

		if($w_max_page > $j)
			$move_str .='.. ';

		if($w_max_page > $j ){
			if( $w_max_page > ($fw_page_count + intval($navi_max/2)) )
				$move_str .='<a href="'.$joken_url.'&amp;paged='.($w_max_page).'&amp;so='.$bukken_sort.'&amp;ord='.$bukken_order.'&amp;s='.$s.'">'.$w_max_page.'</a> ';
		}

		if( $fw_record_count > $fw_page_size * $fw_page_count){
			$move_str .='<a href="'.$joken_url.'&amp;paged='.($fw_page_count+1).'&amp;so='.$bukken_sort.'&amp;ord='.$bukken_order.'&amp;s='.$s.'">&raquo;</a>';
		}


		if( $fw_page_count > 1){
			$w_first_page = ($fw_page_count - 1) * $fw_page_size;
		}else{
			$w_first_page = 1;
		}

		return $move_str;
	}
}

/**
 * 物件画像タイプ
 *
 * @since Fudousan Plugin 1.0.0 *
 * @param int|string $imgtype.
 * @return text
 */
function my_custom_fudoimgtype_print($imgtype) {

	switch ($imgtype) {
		case "1" :
			$imgtype = "(間取)"; break;
		case "2" :
			$imgtype = "(外観)"; break;
		case "3" :
			$imgtype = "(地図)"; break;
		case "4" :
			$imgtype = "(周辺)"; break;
		case "5" :
			$imgtype = "(内装)"; break;
		case "9" :
			$imgtype = ""; break;	//(その他画像)
		case "10" :
			$imgtype = "(玄関)"; break;
		case "11" :
			$imgtype = "(居間)"; break;
		case "12" :
			$imgtype = "(キッチン)"; break;
		case "13" :
			$imgtype = "(寝室)"; break;
		case "14" :
			$imgtype = "(子供部屋)"; break;
		case "15" :
			$imgtype = "(風呂)"; break;
	}

	return $imgtype;
}
