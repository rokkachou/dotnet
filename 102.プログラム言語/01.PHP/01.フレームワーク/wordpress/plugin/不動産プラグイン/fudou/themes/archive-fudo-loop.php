<?php
/**
 * The Template for displaying fudou archive posts.
 *
 * Template: archive-fudo-loop.php
 * 
 * @package WordPress3.8
 * @subpackage Fudousan Plugin
 * Version: 1.4.2
 */

	//会員
	$kaiin = 0;
	if( !is_user_logged_in() && get_post_meta($meta_id, 'kaiin', true) == 1 ) $kaiin = 1;

	//ユーザー別会員物件リスト
	$kaiin2 = users_kaiin_bukkenlist($meta_id,$kaiin_users_rains_register, get_post_meta($meta_id, 'kaiin', true) );

	$_post = get_post( intval($meta_id) ); 

	//newup_mark
	$post_modified_date =  vsprintf("%d-%02d-%02d", sscanf($_post->post_modified, "%d-%d-%d"));
	$post_date =  vsprintf("%d-%02d-%02d", sscanf($_post->post_date, "%d-%d-%d"));
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

?>


<div class="list_simple_boxtitle">
	<h2 class="entry-title">

	<?php if( get_post_meta($meta_id, 'kaiin', true) == 1 ) { ?>
		<span style="float:right;margin:7px"><img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/fudou/img/kaiin_s.jpg" alt="" /></span>
	<?php } ?>

	<?php if ( !my_custom_kaiin_view('kaiin_title',$kaiin,$kaiin2) ){ ?>
			<a href="<?php echo get_permalink($meta_id).$add_url; ?>" title="">会員物件<?php echo $newup_mark_img; ?></a>
	<?php }else{ ?>
		<a href="<?php echo get_permalink($meta_id).$add_url; ?>" title="<?php echo $meta_title; ?>" rel="bookmark"><?php echo $meta_title; ?><?php echo $newup_mark_img; ?></a>
	<?php } ?>
	</h2>
</div>


<div class="list_simple_box">
	<div class="entry-excerpt">
	<?php
		if ( my_custom_kaiin_view('kaiin_excerpt',$kaiin,$kaiin2) ){
			echo $_post->post_excerpt; 
		}
	?>
	</div>

	<!-- 左ブロック --> 
	<div class="list_picsam">

		<!-- 価格 -->
		<div class="dpoint1">
			<?php 
			//価格
			if ( !my_custom_kaiin_view('kaiin_kakaku',$kaiin,$kaiin2) ){
				echo "会員物件";
			}else{
				if( get_post_meta($meta_id, 'seiyakubi', true) != "" ){ echo 'ご成約済'; }else{  my_custom_kakaku_print($meta_id); } 
			} 
			?>
		</div>
		<div class="dpoint2">
		<?php
			//間取り
			if ( my_custom_kaiin_view('kaiin_madori',$kaiin,$kaiin2) ){
				my_custom_madorisu_print($meta_id);
			}

			//面積
			if ( my_custom_kaiin_view('kaiin_menseki',$kaiin,$kaiin2) ){
				if(get_post_meta($meta_id, 'bukkenshubetsu', true) < 1200 ){
					if( get_post_meta($meta_id, 'tochikukaku', true) !="" ) echo '&nbsp;'.get_post_meta($meta_id, 'tochikukaku', true).'m&sup2; ';
				}else{
					if( get_post_meta($meta_id, 'tatemonomenseki', true) !="" ) echo '&nbsp;'.get_post_meta($meta_id, 'tatemonomenseki', true).'m&sup2; ';
				}
			}
		?>
		</div>


		<!-- 画像 -->
		<div class="list_picsam_img">
		<?php
			if ( !my_custom_kaiin_view('kaiin_gazo',$kaiin,$kaiin2) ){
				echo '<img src="'.WP_PLUGIN_URL.'/fudou/img/kaiin.jpg" alt="" />';
				echo '<img src="'.WP_PLUGIN_URL.'/fudou/img/kaiin.jpg" alt="" />';
			}else{
				//サムネイル画像
				$img_path = get_option('upload_path');
				if ($img_path == '')	$img_path = 'wp-content/uploads';

				for( $imgid=1; $imgid<=2; $imgid++ ){

					$fudoimg_data = get_post_meta($meta_id, "fudoimg$imgid", true);
					$fudoimgcomment_data = get_post_meta($meta_id, "fudoimgcomment$imgid", true);
					$fudoimg_alt = $fudoimgcomment_data . my_custom_fudoimgtype_print(get_post_meta($meta_id, "fudoimgtype$imgid", true));

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
							echo '<a href="' . $guid_url . '" rel="lightbox['.$meta_id.'] lytebox['.$meta_id.']" title="'.$fudoimg_alt.'">';
							if($fudoimg_url !=''){
								echo '<img src="' . $fudoimg_url.'" alt="'.$fudoimg_alt.'" title="'.$fudoimg_alt.'" /></a>';
							}else{
								echo '<img src="' . $guid_url . '" alt="'.$fudoimg_alt.'" title="'.$fudoimg_alt.'"  />';
							}
						}else{
							echo '<img src="'.WP_PLUGIN_URL.'/fudou/img/nowprinting.jpg" alt="'.$fudoimg_data.'" />';
						}


					}else{
						echo '<img src="'.WP_PLUGIN_URL.'/fudou/img/nowprinting.jpg" alt="" />';
					}
				}
			}
		?>
		</div> <!-- #list_picsam_img -->

		<!-- 詳細リンクボタン -->
		<a href="<?php echo get_permalink($meta_id).$add_url; ?>" title="<?php echo $meta_title; ?>" rel="bookmark"><div class="list_details_button">物件の詳細を見る</div></a>
	</div>

	<!-- 右ブロック -->   
	<div class="list_detail">
		<dl class="list_price<?php if( get_post_meta($meta_id,'bukkenshubetsu',true) > 3000 ) echo ' rent'; ?>">
			<table width="100%">
				<tr>
					<td>
						<dt><?php if( get_post_meta($meta_id,'bukkenshubetsu',true) < 3000 ) { echo '価格';}else{echo '賃料';} ?></dt>
						<dd><div class="dpoint3">
							<?php 
							if ( !my_custom_kaiin_view('kaiin_kakaku',$kaiin,$kaiin2) ){
								echo "--";
							}else{
								if( get_post_meta($meta_id, 'seiyakubi', true) != "" ){ echo '--'; }else{  my_custom_kakaku_print($meta_id); }
							}
							?>
						</div></dd>

						<dd><?php my_custom_bukkenshubetsu_print($meta_id); ?></dd>
							<?php 
							if ( my_custom_kaiin_view('kaiin_madori',$kaiin,$kaiin2 ) ){
								if( get_post_meta($meta_id, 'madorisu', true) !=""){ 
							?>
										<dt>間取</dt><dd><div class="dpoint3"><?php my_custom_madorisu_print($meta_id); ?></div></dd>
							<?php
								} 
							} 
							?>

						<?php if ( my_custom_kaiin_view('kaiin_kakaku',$kaiin,$kaiin2) ){ ?>

								<?php if(get_post_meta($meta_id, 'kakakutsubo', true) !=""){?>
									<dt>坪単価</dt><dd><?php my_custom_kakakutsubo_print($meta_id) ;?></dd>
								<?php } ?>

								<?php if(get_post_meta($meta_id, 'kakakukyouekihi', true) !=""){?>
								<dt>共益費・管理費</dt><dd><?php echo get_post_meta($meta_id, 'kakakukyouekihi', true);?>円</dd>
								<?php } ?>

								<?php if( get_post_meta($meta_id, 'kakakuhyorimawari', true) !="" &&  get_post_meta($meta_id, 'kakakurimawari', true) !=""){ ;?>
								<br /><dt>満室時表面利回り</dt><dd><?php echo get_post_meta($meta_id, 'kakakuhyorimawari', true);?>%　
								<dt>現行利回り</dt>
								<dd><?php echo get_post_meta($meta_id, 'kakakurimawari', true);?>%</dd>
								<?php } ?>

								<!-- 駐車場 -->
								<?php my_custom_chushajo_print_archive($meta_id); ?>
						<?php } ?>
					</td>
				</tr>


				<?php if( get_post_meta($meta_id, 'kakakushikikin', true) !=""
					|| get_post_meta($meta_id, 'kakakureikin', true) !=""
					|| get_post_meta($meta_id, 'kakakuhoshoukin', true) !=""
					|| get_post_meta($meta_id, 'kakakukenrikin', true) !=""
					|| get_post_meta($meta_id, 'kakakushikibiki', true) !=""
					|| get_post_meta($meta_id, 'kakakukoushin', true) !="" ){ ;?>

				<?php if ( my_custom_kaiin_view('kaiin_kakaku',$kaiin,$kaiin2) ){ ?>
				<tr>
					<td>
						<?php if(get_post_meta($meta_id, 'kakakushikikin', true) !=""){?>
						<dt>敷金</dt><dd><?php my_custom_kakakushikikin_print($meta_id); ?></dd>
						<?php } ?>

						<?php if(get_post_meta($meta_id, 'kakakureikin', true) !=""){?>
						<dt>礼金</dt><dd><?php my_custom_kakakureikin_print($meta_id); ?></dd>
						<?php } ?>

						<?php if(get_post_meta($meta_id, 'kakakuhoshoukin', true) !=""){?>
						<dt>保証金</dt><dd><?php my_custom_kakakuhoshoukin_print($meta_id); ?></dd>
						<?php } ?>

						<?php if(get_post_meta($meta_id, 'kakakukenrikin', true) !=""){?>
						<dt>権利金</dt><dd><?php my_custom_kakakukenrikin_print($meta_id); ?></dd>
						<?php } ?>

						<?php if(get_post_meta($meta_id, 'kakakushikibiki', true) !=""){?>
						<dt>償却・敷引金</dt><dd><?php my_custom_kakakushikibiki_print($meta_id); ?></dd>
						<?php } ?> 

						<?php if( get_post_meta($meta_id, 'kakakukoushin', true) !=""){ ;?>
						<dt>更新料</dt><dd><?php my_custom_kakakukoushin_print($meta_id); ?></dd>
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
				<?php } ?>


			</table>
		</dl>


		<dl class="list_address">
			<table width="100%">
				<?php if ( my_custom_kaiin_view('kaiin_shozaichi',$kaiin,$kaiin2) ){ ?>
						<tr>
							<td><dt>所在地</dt></td>
							<td width="90%"><dd><?php my_custom_shozaichi_print($meta_id); ?>
							<?php echo get_post_meta($meta_id, 'shozaichimeisho', true);?></dd></td>
						</tr>
				<?php } ?>

				<?php if ( my_custom_kaiin_view('kaiin_kotsu',$kaiin,$kaiin2) ){ ?>
						<tr>
							<td><dt>交通</dt></td>
							<td width="90%"><dd><?php my_custom_koutsu1_print($meta_id); ?>
							<?php my_custom_koutsu2_print($meta_id); ?>
							<?php if(get_post_meta($meta_id, 'koutsusonota', true) !='') echo '<br />'.get_post_meta($meta_id, 'koutsusonota', true).'';?></dd></td>
						</tr>
				<?php } ?>
			</table>
		</dl>


		<dl class="list_price_others">

			<table>

				<?php if ( my_custom_kaiin_view('kaiin_tikunen',$kaiin,$kaiin2) ){ ?>
					<?php if(get_post_meta($meta_id, 'tatemonochikunenn', true) !="" || get_post_meta($meta_id, 'tatemonokozo', true) !="" ){ ?>
						<tr>
							<td>
								<dt>築年月</dt>
								<dd><?php echo get_post_meta($meta_id, 'tatemonochikunenn', true);?></dd>
							</td>
							<td colspan="2">
								<dt>構造</dt><dd><?php my_custom_tatemonokozo_print($meta_id) ?></dd>
								<dd><?php my_custom_tatemonoshinchiku_print($meta_id); ?></dd>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>


				<?php if ( my_custom_kaiin_view('kaiin_menseki',$kaiin,$kaiin2) || my_custom_kaiin_view('kaiin_kaisu',$kaiin,$kaiin2) ){ ?>
				<tr>
					<td><dt>面積</dt>
						<?php if ( my_custom_kaiin_view('kaiin_menseki',$kaiin,$kaiin2) ){ ?>
							<dd>
								<?php if( get_post_meta($meta_id, 'tatemonomenseki', true) !="" ) echo ''.get_post_meta($meta_id, 'tatemonomenseki', true).'m&sup2; ';?>
								<?php if( get_post_meta($meta_id, 'tochikukaku', true) !="" ) echo '土地 '.get_post_meta($meta_id, 'tochikukaku', true).'m&sup2; ';?>
							</dd>
						<?php } ?>
					</td>

					<?php if( get_post_meta($meta_id, 'tatemonokaisu1', true) !="" || get_post_meta($meta_id, 'tatemonokaisu2', true) !="" ){ ?>
					<td colspan="2"><dt>階数</dt>
					<?php if ( my_custom_kaiin_view('kaiin_kaisu',$kaiin,$kaiin2) ){ ?>
						<dd>
							<?php if(get_post_meta($meta_id, 'tatemonokaisu1', true)!="") echo '地上 '.get_post_meta($meta_id, 'tatemonokaisu1', true).'階 ' ;?>
							<?php if(get_post_meta($meta_id, 'tatemonokaisu2', true)!="") echo '地下 '.get_post_meta($meta_id, 'tatemonokaisu2', true).'階' ;?>
						</dd>
					<?php } ?>
					</td>
					<?php } ?>
				</tr>
				<?php } ?>

				<tr>
					<?php if ( my_custom_kaiin_view('kaiin_shikibesu',$kaiin,$kaiin2) ){ ?>
					<td colspan="2" width="50%">
								<dt>物件番号</dt><dd><?php echo get_post_meta($meta_id, 'shikibesu', true);?></dd>
					</td>
					<?php } ?>

					<?php if ( my_custom_kaiin_view('kaiin_keisaikigenbi',$kaiin,$kaiin2) ){ ?>
						<?php if( get_post_meta($meta_id,'keisaikigenbi',true)!='' ){ ?>
							<td><dt>掲載期限日</dt><dd><?php echo get_post_meta($meta_id, 'keisaikigenbi', true);?></dd></td>
						<?php } ?>
					<?php } ?>

				</tr>
			</table>

			<!-- 更新日
			<div align="right"><?php echo $post_modified_date; ?> UpDate</div>
			-->

			<?php if( $kaiin == 1 ) { ?>
				この物件は、「会員様にのみ限定公開」している物件です。
				非公開物件につき、詳細情報の閲覧には会員ログインが必要です。
				非公開物件を閲覧・資料請求するには会員登録(無料)が必要です。
			<?php } else { ?>

			<?php
				//ユーザー別会員物件リスト
				if (!$kaiin2 && get_option('kaiin_users_rains_register') == 1 && get_post_meta($meta_id, 'kaiin', true) == 1 ) {
					echo 'この物件は、「閲覧条件」に 該当していない物件です。<br />';
					echo '閲覧条件を変更をする事で閲覧が可能になります。<br />';
					echo '<div align="center">';
					echo '<div id="maching_mail"><a href="'.WP_PLUGIN_URL.'/fudoumail/fudou_user.php?KeepThis=true&TB_iframe=true&height=500&width=680" class="thickbox">';
					echo '閲覧条件・メール設定</a></div>';
					echo '</div>';
				}
			}
			?>
		</dl>
	</div>

</div><!-- end list_simple_box -->



