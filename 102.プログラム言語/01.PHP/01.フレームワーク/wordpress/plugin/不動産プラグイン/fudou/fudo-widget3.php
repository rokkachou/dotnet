<?php
/*
 * 不動産プラグインウィジェット
 * @package WordPress3.9
 * @subpackage Fudousan Plugin
 * Version: 1.4.5
*/


/** ウィジェットエリア syousai widgets */
if( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => '物件詳細ページ',
		'id' => 'syousai_widgets',
		'description' => '物件詳細ページ',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
}



//物件詳細関連物件表示
add_action('widgets_init', 'fudo_widgetInit_syousai');
function fudo_widgetInit_syousai() {
	register_widget('fudo_widget_syousai');
}

//物件詳細関連物件表示
class fudo_widget_syousai extends WP_Widget {

	/** constructor */
	function fudo_widget_syousai() {
		$widget_ops = array( 'description' => '物件詳細ページに関連物件を表示します。' );
		parent::WP_Widget( false , $name = '関連物件表示(物件詳細ページ)' , $widget_ops );
	}

	/** @see WP_Widget::form */	
	function form($instance) {

		global $is_fudoukaiin;

		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$item  = isset($instance['item']) ? esc_attr($instance['item']) : '';
		$sort  = isset($instance['sort']) ? esc_attr($instance['sort']) : '';

		$view2 = isset($instance['view2']) ? esc_attr($instance['view2']) : '';
		$view3 = isset($instance['view3']) ? esc_attr($instance['view3']) : '';
		$view4 = isset($instance['view4']) ? esc_attr($instance['view4']) : '';
		$view5 = isset($instance['view5']) ? esc_attr($instance['view5']) : '';
		$kaiinview = isset($instance['kaiinview']) ? esc_attr($instance['kaiinview']) : '';

		if($item=='') $item = 5;

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		タイトル <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>


		<p><label for="<?php echo $this->get_field_id('item'); ?>">
		最大表示数 <input class="widefat" id="<?php echo $this->get_field_id('item'); ?>" name="<?php echo $this->get_field_name('item'); ?>" type="text" value="<?php echo $item; ?>" /></label></p>


		<p><label for="<?php echo $this->get_field_id('sort'); ?>">
		優先絞込 <select class="widefat" id="<?php echo $this->get_field_id('sort'); ?>" name="<?php echo $this->get_field_name('sort'); ?>">
			<option value="2"<?php if($sort == 2){echo ' selected="selected"';} ?>>地域</option>
			<option value="3"<?php if($sort == 3){echo ' selected="selected"';} ?>>駅</option>
			<option value="1"<?php if($sort == 1){echo ' selected="selected"';} ?>>地図座標(近い順)</option>
		</select></label></p>


		表示項目<br />
		<p><label for="<?php echo $this->get_field_id('view2'); ?>">
		価格 <select class="widefat" id="<?php echo $this->get_field_id('view2'); ?>" name="<?php echo $this->get_field_name('view2'); ?>">
			<option value="1"<?php if($view2 == 1){echo ' selected="selected"';} ?>>表示する</option>
			<option value="2"<?php if($view2 == 2){echo ' selected="selected"';} ?>>表示しない</option>
		</select></label></p>

		<p><label for="<?php echo $this->get_field_id('view3'); ?>">
		間取り・土地面積 <select class="widefat" id="<?php echo $this->get_field_id('view3'); ?>" name="<?php echo $this->get_field_name('view3'); ?>">
			<option value="1"<?php if($view3 == 1){echo ' selected="selected"';} ?>>表示する</option>
			<option value="2"<?php if($view3 == 2){echo ' selected="selected"';} ?>>表示しない</option>
		</select></label></p>

		<p><label for="<?php echo $this->get_field_id('view4'); ?>">
		地域 <select class="widefat" id="<?php echo $this->get_field_id('view4'); ?>" name="<?php echo $this->get_field_name('view4'); ?>">
			<option value="1"<?php if($view4 == 1){echo ' selected="selected"';} ?>>表示する</option>
			<option value="2"<?php if($view4 == 2){echo ' selected="selected"';} ?>>表示しない</option>
		</select></label></p>

		<p><label for="<?php echo $this->get_field_id('view5'); ?>">
		路線・駅 <select class="widefat" id="<?php echo $this->get_field_id('view5'); ?>" name="<?php echo $this->get_field_name('view5'); ?>">
			<option value="1"<?php if($view5 == 1){echo ' selected="selected"';} ?>>表示する</option>
			<option value="2"<?php if($view5 == 2){echo ' selected="selected"';} ?>>表示しない</option>
		</select></label></p>

	<?php if($is_fudoukaiin && get_option('kaiin_users_can_register') == '1' ){ ?>
		<p><label for="<?php echo $this->get_field_id('kaiinview'); ?>">
		会員物件 <select class="widefat" id="<?php echo $this->get_field_id('kaiinview'); ?>" name="<?php echo $this->get_field_name('kaiinview'); ?>">
			<option value="1"<?php if($kaiinview == 1){echo ' selected="selected"';} ?>>会員・一般物件を表示する</option>
			<option value="2"<?php if($kaiinview == 2){echo ' selected="selected"';} ?>>会員物件を表示しない</option>
			<option value="3"<?php if($kaiinview == 3){echo ' selected="selected"';} ?>>会員物件だけ表示</option>
		</select></label></p>
	<?php } ?>

		<?php 
	}


	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {

		global $is_fudoukaiin;
		global $wpdb;
		global $post;

		$post_ID = $post->ID;

		if($post_ID !=''){

			// outputs the content of the widget

		        extract( $args );
			$title = isset($instance['title']) ? $instance['title'] : '';
			$item  = isset($instance['item']) ?  $instance['item'] : '';
			$sort  = isset($instance['sort']) ?  $instance['sort'] : '';

			$view2 = isset($instance['view2']) ? $instance['view2'] : '';
			$view3 = isset($instance['view3']) ? $instance['view3'] : '';
			$view4 = isset($instance['view4']) ? $instance['view4'] : '';
			$view5 = isset($instance['view5']) ? $instance['view5'] : '';

			$kaiinview = isset($instance['kaiinview']) ? $instance['kaiinview'] : '';
			if($kaiinview == '') $kaiinview = 1;

			if( !$is_fudoukaiin || get_option('kaiin_users_can_register') != '1' ){
				$kaiinview = 1;
			}

			if($item =="") 	$item=5;

			$newup_mark = get_option('newup_mark');
			if($newup_mark == '') $newup_mark=14;


			//map
			$ido_data = floatval(get_post_meta($post_ID,'bukkenido',true));
			$keido_data = floatval(get_post_meta($post_ID,'bukkenkeido',true));

			//種別
			$bukkenshubetsu_data = get_post_meta($post_ID,'bukkenshubetsu',true);

			//価格
			$kakaku_data = intval(get_post_meta($post_ID,'kakaku',true));

			//面積
			$tatemonomenseki_data = floatval(get_post_meta($post_ID,'tatemonomenseki',true));

			//面積
			$tochikukaku_data = floatval(get_post_meta($post_ID,'tochikukaku',true));

			//所在地
			$shozaichicode_data = get_post_meta($post_ID,'shozaichicode',true);

			//路線・駅
			$koutsurosen1_data = get_post_meta($post_ID,'koutsurosen1',true);
			$koutsueki1_data = get_post_meta($post_ID,'koutsueki1',true);

			$koutsurosen2_data = get_post_meta($post_ID,'koutsurosen2',true);
			$koutsueki2_data = get_post_meta($post_ID,'koutsueki2',true);


			$sql = "SELECT DISTINCT P.ID,P.post_title,P.post_modified,P.post_date";
			$sql .=  " FROM (((((((((($wpdb->posts as P";
			$sql .=  " INNER JOIN $wpdb->postmeta as PM   ON P.ID = PM.post_id) ";		
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_1 ON P.ID = PM_1.post_id) ";

			//面積
			if ($tatemonomenseki_data >1 ){
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_2 ON P.ID = PM_2.post_id) ";
			}else{
			$sql .=  ")";
			}

			//面積
			if ($tochikukaku_data >1 ){
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_3 ON P.ID = PM_3.post_id) ";
			}else{
			$sql .=  ")";
			}

			//地域
			if ($sort == 2 || $sort == ''){
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_10 ON P.ID = PM_10.post_id) "; 
			}else{
			$sql .=  ")";
			}

			//路線・駅
			if ($sort == 3 && $koutsurosen1_data !='' && $koutsueki1_data != '' ){
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_12 ON P.ID = PM_12.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_13 ON P.ID = PM_13.post_id) ";
			}else{
			$sql .=  "))";
			}

			//座標
			if ($sort == 1 && $ido_data > 1 && $keido_data > 1 ){
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_15 ON P.ID = PM_15.post_id) ";
			$sql .=  " INNER JOIN $wpdb->postmeta AS PM_16 ON P.ID = PM_16.post_id) ";
			}else{
			$sql .=  "))";
			}

			//会員
			if( $kaiinview != 1 ){
				$sql = $sql . " INNER JOIN $wpdb->postmeta AS PM_4 ON P.ID = PM_4.post_id) ";
			}else{
				$sql = $sql . " )";
			}

			$sql .=  " WHERE ";

			$sql .=  " P.post_status='publish' AND P.post_password = '' AND P.post_type ='fudo'";

			$sql .=  " AND P.ID !=".$post_ID."";

			//種別
			$sql .=  " AND PM.meta_key='bukkenshubetsu' AND PM.meta_value = '".$bukkenshubetsu_data."'";

			$sql .=  " AND PM_1.meta_key='kakaku' ";

			if ($tatemonomenseki_data >1 ){
			$sql .=  " AND PM_2.meta_key='tatemonomenseki'";
			}
			if ($tochikukaku_data >1 ){
			$sql .=  " AND PM_3.meta_key='tochikukaku' ";
			}

			//地域
			if ($sort == 2 || $sort == ''){
			$sql .=  " AND PM_10.meta_key='shozaichicode' AND PM_10.meta_value = '".$shozaichicode_data."'";
			}

			//路線・駅
			if ($sort == 3 && $koutsurosen1_data !='' && $koutsueki1_data != '' ){
			$sql .=  " AND PM_12.meta_key='koutsurosen1' AND PM_12.meta_value = '".$koutsurosen1_data."'";
			$sql .=  " AND PM_13.meta_key='koutsueki1' AND PM_13.meta_value = '".$koutsueki1_data."'";
			}

			//座標
			if ($sort == 1 && $ido_data > 1 && $keido_data > 1 ){
			$sql .=  " AND PM_15.meta_key='bukkenido' AND PM_16.meta_key ='bukkenkeido'";
			}

			//会員
			if( $kaiinview == 2 ){
				$sql = $sql . " AND PM_4.meta_key='kaiin' AND PM_4.meta_value !='1' ";
			}

			if( $kaiinview == 3 ){
				$sql = $sql . " AND PM_4.meta_key='kaiin' AND PM_4.meta_value = '1' ";
			}


			$order_by = '';


			//地域
			if ($sort == 2 || $sort == ''){
				$order_by .=  " ORDER BY PM_10.meta_value ASC";
			}

			//路線・駅
			if ($sort == 3 && $koutsurosen1_data !='' && $koutsueki1_data != '' ){
				$order_by .=  " ORDER BY PM_12.meta_value ASC";
			}

			//座標
			if ($sort == 1 && $ido_data > 1 && $keido_data > 1 ){
				$order_by .=  " ORDER BY ABS( PM_15.meta_value  - ".$ido_data.") + ABS( PM_16.meta_value - ".$keido_data.") ASC";
			}


			//面積
			if ($tatemonomenseki_data > 1 ){
				if($order_by == ''){
					$order_by .=  "  ORDER BY ABS( PM_2.meta_value - $tatemonomenseki_data) ASC";
				}else{
					$order_by .=  " , ABS( PM_2.meta_value - $tatemonomenseki_data) ASC";
				}
			}
			if ($tochikukaku_data > 1 ){
				if($order_by == ''){
					$order_by .=  " ORDER BY ABS( PM_3.meta_value - $tochikukaku_data) ASC";
				}else{
					$order_by .=  " , ABS( PM_3.meta_value - $tochikukaku_data) ASC";
				}
			}


			//価格に近い物件
				if($order_by == ''){
					$order_by .=  " ORDER BY ABS( PM_1.meta_value - $kakaku_data) ASC";
				}else{
					$order_by .=  " , ABS( PM_1.meta_value - $kakaku_data) ASC";
				}



			$sql .=  $order_by . " limit ".$item."";

		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_results( $sql, ARRAY_A );

			//ユーザー別会員物件リスト
			$kaiin_users_rains_register = get_option('kaiin_users_rains_register');

			if(!empty($metas)) {

				echo $before_widget;

				if ( $title ){
					echo $before_title . $title . $after_title; 
				}


				$img_path = get_option('upload_path');
				if ($img_path == '')
					$img_path = 'wp-content/uploads';


				echo '<div id="syousai_box">';
				echo '<ul id="'.$args['widget_id'].'_1" class="syousai-content kanren">';

				foreach ( $metas as $meta ) {

					$view_height = 120;

					$meta_id =  $meta['ID'];
					$post_title =  $meta['post_title'];
					$post_url =  str_replace('&p=','&amp;p=',get_permalink($meta_id));


					//newup_mark
					$post_modified =  $meta['post_modified'];
					$post_date =   $meta['post_date'];
					$post_modified_date =  vsprintf("%d-%02d-%02d", sscanf($post_modified, "%d-%d-%d"));
					$post_date =  vsprintf("%d-%02d-%02d", sscanf($post_date, "%d-%d-%d"));

					$newup_mark_img=  '';
					if( $newup_mark != 0 && is_numeric($newup_mark) ){
						if( ( abs(strtotime($post_modified_date) - strtotime(date("Y/m/d"))) / (60 * 60 * 24) ) < $newup_mark ){
							if($post_modified_date == $post_date ){
								$newup_mark_img = '<div class="new_mark">new</div>';
							}else{
								$newup_mark_img =  '<div class="new_mark">up</div>';
							}
						}
					}


					//会員
					$kaiin = 0;
					if( !is_user_logged_in() && get_post_meta($meta_id, 'kaiin', true) == 1 ) $kaiin = 1;

					//ユーザー別会員物件リスト
					$kaiin2 = users_kaiin_bukkenlist($meta_id,$kaiin_users_rains_register,get_post_meta($meta_id, 'kaiin', true));

					echo '<li class="'.$args['widget_id'].' syousai-content-li">';


					echo $newup_mark_img;


					//会員項目表示判定
					if ( !my_custom_kaiin_view('kaiin_gazo',$kaiin,$kaiin2) ){
						echo '<a href="' . $post_url . '">';
						echo '<img class="box2image" src="'.WP_PLUGIN_URL.'/fudou/img/kaiin.jpg" alt="" />';
						echo '</a>';
					}else{
						$fudoimg_data = '';
						//サムネイル画像
						$fudoimg1_data = get_post_meta($meta_id, 'fudoimg1', true);
						if($fudoimg1_data != '')	$fudoimg_data=$fudoimg1_data;
						$fudoimg2_data = get_post_meta($meta_id, 'fudoimg2', true);
						if($fudoimg2_data != '')	$fudoimg_data=$fudoimg2_data;
						$fudoimg_alt = str_replace("　"," ",$post_title);

						echo '<a href="' . $post_url . '">';

						if($fudoimg_data !="" ){

							$sql  = "";
							$sql .=  "SELECT P.ID,P.guid";
							$sql .=  " FROM $wpdb->posts as P";
							$sql .=  " WHERE P.post_type ='attachment' AND P.guid LIKE '%/$fudoimg_data' ";
						//	$sql = $wpdb->prepare($sql,'');
							$metas = $wpdb->get_row( $sql );

							$attachmentid = '';
							if ( $metas != '' ){
								$attachmentid  =  $metas->ID;
								$guid_url  =  $metas->guid;
							}

							if($attachmentid !=''){
								//thumbnail、medium、large、full 
								$fudoimg_data1 = wp_get_attachment_image_src( $attachmentid, 'thumbnail');
								$fudoimg_url = $fudoimg_data1[0];

								if($fudoimg_url !=''){
									echo '<img class="box2image" src="' . $fudoimg_url.'" alt="'.$fudoimg_alt.'" title="'.$fudoimg_alt.'" />';
								}else{
									echo '<img class="box2image" src="' . $guid_url . '" alt="'.$fudoimg_alt.'" title="'.$fudoimg_alt.'"  />';
								}
							}else{
								echo '<img class="box2image" src="'.WP_PLUGIN_URL.'/fudou/img/nowprinting.jpg" alt="'.$fudoimg_alt.'" title="'.$fudoimg_alt.'" />';
							}

						}else{
							echo '<img class="box2image" src="'.WP_PLUGIN_URL.'/fudou/img/nowprinting.jpg"  alt="'.$fudoimg_alt.'" title="'.$fudoimg_alt.'" />';
						}
						echo '</a>';
					}
					//	echo '<br />';


					//価格
					if ( my_custom_kaiin_view('kaiin_kakaku',$kaiin,$kaiin2) ){

						if($view2=="1"){
							echo '<span class="top_price">';
							if( get_post_meta($meta_id, 'seiyakubi', true) != "" ){
								echo 'ご成約済　';
							}else{

								if(get_post_meta($meta_id,'kakakukoukai',true) == "0"){
									$kakakujoutai_data = get_post_meta($meta_id,'kakakujoutai',true);
									if($kakakujoutai_data_data=="1")	echo '相談　';
									if($kakakujoutai_data_data=="2")	echo '確定　';
									if($kakakujoutai_data_data=="3")	echo '入札　';

								}else{
									$kakaku_data = get_post_meta($meta_id,'kakaku',true);
									if(is_numeric($kakaku_data)){
										echo floatval($kakaku_data)/10000;
										echo "万円 ";
									}
								}					
							}
							echo '</span>';
						}
					}

					//間取り・土地面積
					if($view3=="1"){
						//間取り
						if ( my_custom_kaiin_view('kaiin_madori',$kaiin,$kaiin2) ){
							echo '<span class="top_madori">';
							$madorisyurui_data = get_post_meta($meta_id,'madorisyurui',true);
							echo get_post_meta($meta_id,'madorisu',true);
							if($madorisyurui_data=="10")	echo 'R ';
							if($madorisyurui_data=="20")	echo 'K ';
							if($madorisyurui_data=="25")	echo 'SK ';
							if($madorisyurui_data=="30")	echo 'DK ';
							if($madorisyurui_data=="35")	echo 'SDK ';
							if($madorisyurui_data=="40")	echo 'LK ';
							if($madorisyurui_data=="45")	echo 'SLK ';
							if($madorisyurui_data=="50")	echo 'LDK ';
							if($madorisyurui_data=="55")	echo 'SLDK ';
							echo '</span>';
						}

						//土地面積
						if ( my_custom_kaiin_view('kaiin_menseki',$kaiin,$kaiin2) ){
							echo '<span class="top_menseki">';
							if ( get_post_meta($meta_id,'bukkenshubetsu',true) < 1200 ) {
								if( get_post_meta($meta_id, 'tochikukaku', true) !="" ) 
									echo ' '.get_post_meta($meta_id, 'tochikukaku', true).'m&sup2;';
							}
							echo '</span>';
						}
					}

					echo '<span>';

					//所在地
					if ( !my_custom_kaiin_view('kaiin_shozaichi',$kaiin,$kaiin2) ){
						if($view4=="1"){
							$view_height += 30;
						}
					}else{
						if($view4=="1"){
							echo '<span class="top_shozaichi">';
							$shozaichiken_data = get_post_meta($meta_id,'shozaichicode',true);
							$shozaichiken_data = myLeft($shozaichiken_data,2);
							$shozaichicode_data = get_post_meta($meta_id,'shozaichicode',true);
							$shozaichicode_data = myLeft($shozaichicode_data,5);
							$shozaichicode_data = myRight($shozaichicode_data,3);

							if($shozaichiken_data !="" && $shozaichicode_data !=""){
								$sql = "SELECT narrow_area_name FROM ".$wpdb->prefix."area_narrow_area WHERE middle_area_id=".$shozaichiken_data." and narrow_area_id =".$shozaichicode_data."";

							//	$sql = $wpdb->prepare($sql,'');
								$metas = $wpdb->get_row( $sql );
								echo "<br />".$metas->narrow_area_name." ";
							}
							echo get_post_meta($meta_id, 'shozaichimeisho', true);
							$view_height += 30;
							echo '</span>';
						}
					}

					//交通路線
					if ( !my_custom_kaiin_view('kaiin_kotsu',$kaiin,$kaiin2) ){
						if($view5=="1"){
							$view_height += 30;
						}
					}else{

						//交通路線
						if($view5=="1"){
							echo '<span class="top_kotsu">';
							$koutsurosen_data = get_post_meta($meta_id, 'koutsurosen1', true);
							$koutsueki_data = get_post_meta($meta_id, 'koutsueki1', true);
							$shozaichiken_data = get_post_meta($meta_id,'shozaichicode',true);
							$shozaichiken_data = myLeft($shozaichiken_data,2);

							if($koutsurosen_data !=""){
								$sql = "SELECT `rosen_name` FROM `".$wpdb->prefix."train_rosen` WHERE `rosen_id` =".$koutsurosen_data."";
							//	$sql = $wpdb->prepare($sql,'');
								$metas = $wpdb->get_row( $sql );
								if( !empty($metas) ) echo "<br />".$metas->rosen_name;
							}

							//交通駅
							if($koutsurosen_data !="" && $koutsueki_data !=""){
								$sql = "SELECT DTS.station_name";
								$sql = $sql . " FROM ".$wpdb->prefix."train_rosen AS DTR";
								$sql = $sql . " INNER JOIN ".$wpdb->prefix."train_station as DTS ON DTR.rosen_id = DTS.rosen_id";
								$sql = $sql . " WHERE DTS.station_id=".$koutsueki_data." AND DTS.rosen_id=".$koutsurosen_data."";
							//	$sql = $wpdb->prepare($sql,'');
								$metas = $wpdb->get_row( $sql );
								if( !empty($metas) ) echo $metas->station_name.'駅';
							}
							$view_height += 30;
							echo '</span>';
						}
					}
					echo '</span>';


					if( get_post_meta($meta_id, 'kaiin', true) == 1 )
					echo '<span style="float:left;"><img src="'. WP_PLUGIN_URL . '/fudou/img/kaiin_s.jpg" alt="" width="30" /></span>';

					echo '</li>';

				}
				echo '</ul>';
				echo '</div>';


				echo $after_widget;


				echo "\n";
				echo '<style type="text/css">';
				echo "\n<!--\n";
				echo ' #'.$args['widget_id'].' li{height: '.$view_height.'px}';
				echo "\n-->\n";
				echo '</style>';
			}
		}


	}
} // Class fudo_widget_syousai




