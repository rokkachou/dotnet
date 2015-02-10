<?php

/**
 * LICENSE: The MIT License {{{
 *
 * Copyright (c) <2009> <Denis Kobozev>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Denis Kobozev <d.v.kobozev@gmail.com>
 * @copyright 2009 Denis Kobozev
 * @license   The MIT License
 * }}}
 */


/** 
 * @author    nendeb <nendeb@gmail.com>
 * @copyright 2014 nendeb
 * @package WordPress3.9
 * @Version: 1.4.5
 */

add_action('admin_menu', 'csv_admin_menu_fudo');
function csv_admin_menu_fudo() {
	require_once ABSPATH . '/wp-admin/admin.php';
	$plugin = new CSVImporterPlugin_fudo;
	add_management_page('edit.php', '物件CSV取込み', 'edit_pages', __FILE__, array($plugin, 'form'));
}

class CSVImporterPlugin_fudo {

	//homes
	var $defaults = array(
		'建物名(物件名)' => null,	//記事のタイトル 
		'備考' => null,	//記事の内容
		'物件の特徴' => null,	//記事の要約
		'自社管理修正日時' => null,	//記事の投稿日（過去日付も先日付も対応。先日付にすれば、予約投稿になります。）
		'csv_post_tags' => null,	//タグ（複数の場合は、カンマ（,）で区切る） 
		'csv_post_categories' => null,	//カテゴリ（複数の場合は、カンマ（,）で区切る）
		'csv_post_author' => null,	
		'csv_post_slug' => null,	
	);

	//k_rains
	var $defaults_k_rains = array(
		'' => null,	//記事のタイトル 
		'備考' => null,	//記事の内容
		'備考補足' => null,	//記事の要約
		'登録日付' => null,	//記事の投稿日（過去日付も先日付も対応。先日付にすれば、予約投稿になります。）
		'csv_post_tags' => null,	//タグ（複数の場合は、カンマ（,）で区切る） 
		'csv_post_categories' => null,	//カテゴリ（複数の場合は、カンマ（,）で区切る）
		'csv_post_author' => null,	
		'csv_post_slug' => null,	
	);

	//chintai_c21
	var $defaults_c21 = array(
		'' => null,	//記事のタイトル 
		'備考' => null,	//記事の内容
		'備考補足' => null,	//記事の要約
		'登録日付' => null,	//記事の投稿日（過去日付も先日付も対応。先日付にすれば、予約投稿になります。）
		'csv_post_tags' => null,	//タグ（複数の場合は、カンマ（,）で区切る） 
		'csv_post_categories' => null,	//カテゴリ（複数の場合は、カンマ（,）で区切る）
		'csv_post_author' => null,	
		'csv_post_slug' => null,	
	);


	var $log = array();

	var $skipped = 0;
	var $imported = 0;
	var $comments = 0;
	var $updated = 0;

	var $addnew = true;


	// determine value of option $name from database, $default value or $params,
	// save it to the db if needed and return it

	function process_option($name, $default, $params) {

		if (array_key_exists($name, $params)) {
			$value = stripslashes($params[$name]);
		} elseif (array_key_exists('_'.$name, $params)) {
			// unchecked checkbox value
			$value = stripslashes($params['_'.$name]);
		} else {
			$value = null;
		}
		$stored_value = get_option($name);
		if ($value == null) {
			if ($stored_value === false) {
				if (is_callable($default) && method_exists($default[0], $default[1])) {
					$value = call_user_func($default);
				} else {
					$value = $default;
				}
			add_option($name, $value);
			} else {
				$value = $stored_value;
			}
		} else {
			if ($stored_value === false) {
				add_option($name, $value);
			} elseif ($stored_value != $value) {
				update_option($name, $value);
			}
		}
		return $value;
	}



	// Plugin's interface
	function form() {
	  	global $opt_csv,$opt_overwrite,$opt_draft,$opt_kaiin;

		$opt_draft = $this->process_option('csv_importer_import_as_draft','publish', $_POST);
		$opt_type = $this->process_option('csv_importer_import_post_type','post', $_POST);
		$opt_csv = $this->process_option('csv_importer_import_csv_type', 'csv', $_POST);
		$opt_overwrite = $this->process_option('csv_importer_import_csv_overwrite', 'no', $_POST);

		$opt_kaiin = $this->process_option('csv_importer_import_csv_kaiin', '', $_POST);


		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$this->post(compact('opt_draft','opt_type','opt_csv','$opt_overwrite'));
		} 

?>
<style type="text/css"> 
<!--
.k_checkbox {
	display: inline-block;
	margin: 0 1em 0 0;
}

#post-body-content {
/*	margin-left:8px; */
	margin: 0 auto;;
	line-height: 1.5;


	padding:16px 16px 30px;
	border-radius:11px;
	background:#fff;
	border:1px solid #e5e5e5;
	box-shadow:rgba(200, 200, 200, 1) 0 4px 18px;
	width: 90%;
	font-size: 12px;
}

// -->
</style>

	<div class="wrap">
		<div id="icon-tools" class="icon32"><br /></div>
		<h2>物件CSV取込み</h2>
		<div id="poststuff">


		<div id="post-body">
		<div id="post-body-content">


		<form class="add:the-list: validate" method="post" enctype="multipart/form-data">

		<!-- Import as draft -->
		<p>
		<input name="_csv_importer_import_as_draft" type="hidden" value="publish" />
		<label><input name="csv_importer_import_as_draft" type="checkbox" <?php if ('draft' == $opt_draft) { echo 'checked="checked"'; } ?> value="draft" /> インポート時に「下書き」として登録</label>
		</p>

		<!-- Import pages or posts -->
		<input name="_csv_importer_import_post_type" type="hidden" value="fudo" />
		<input name="csv_importer_import_post_type" type="hidden" value="fudo" />

		<!-- Import as update or skip -->
		<input name="_csv_importer_import_csv_overwrite" type="hidden" value="no" />
		<p>物件番号重複処理
		<select name="csv_importer_import_csv_overwrite">
		<option value="no" <?php if ('no' == $opt_overwrite) { echo 'selected="selected"'; } ?>>スキップする</option>
		<option value="yes" <?php if ('yes' == $opt_overwrite) { echo 'selected="selected"'; } ?>>上書きする</option>
		</select> *「上書きする」は時間がかかります。
		</p>

		<!-- Import as kaiin or ippan -->
		<input name="_csv_importer_import_csv_kaiin" type="hidden" value="0" />
		<p>会員物件
		<select name="csv_importer_import_csv_kaiin">
		<option value="0" <?php if ('0' == $opt_kaiin) { echo 'selected="selected"'; } ?>>一般</option>
		<option value="1" <?php if ('1' == $opt_kaiin) { echo 'selected="selected"'; } ?>>会員</option>
		</select> *会員にする場合は「不動産会員プラグイン」が必要です。
		</p>


		<!-- Import as csv type -->
		<p>CSVタイプ
		<select name="csv_importer_import_csv_type">
		<option value="k_rains" <?php if ('k_rains' == $opt_csv) { echo 'selected="selected"'; } ?>>旧近畿レインズ</option>
		<option value="homes" <?php if ('homes' == $opt_csv) { echo 'selected="selected"'; } ?>>ホームズ</option>
		<option value="h_rains" <?php if ('h_rains' == $opt_csv) { echo 'selected="selected"'; } ?>>東日本レインズ</option>
		<option value="c21" <?php if ('c21' == $opt_csv) { echo 'selected="selected"'; } ?>>センチュリ21</option>
		</select>
		</p>

	        <!-- File input -->
	        <p>CSVアップロードファイル<br/>
	            <input name="csv_import" id="csv_import" type="file" value="" aria-required="true" /></p>
		<p class="submit"><input type="submit" class="button" name="submit" value="取込み" /></p>


		</form>


		</div>
		</div>
		</div>


	</div><!-- end wrap -->
<?php

}



	function print_messages() {

		if (!empty($this->log)) {
?>
		<div class="wrap">
			<?php if (!empty($this->log['error'])): ?>
				<div class="error">    
					<?php foreach ($this->log['error'] as $error): ?>
					<p><?php echo $error; ?></p>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($this->log['notice'])): ?>
				<div class="updated fade">
				<?php foreach ($this->log['notice'] as $notice): ?>
				<p><?php echo $notice; ?></p>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div><!-- end wrap -->
<?php

		$this->log = array();
		}
	}

	// Handle POST submission

	function post($options) {

		global $imported,$updated,$skipped;
		global $addnew,$opt_overwrite;

	//	set_time_limit(120);


		if (empty($_FILES['csv_import']['tmp_name'])) {
			$this->log['error'][] = 'No file uploaded, aborting.';
			$this->print_messages();
			return;
		}

		require_once 'datasource.php';


		$time_start = microtime(true);
		$csv = new File_CSV_DataSource;
		$file = $_FILES['csv_import']['tmp_name'];

		//UTF-8
		//$this->stripBOM($file);

		if (!$csv->load($file)) {
			$this->log['error'][] = 'Failed to load file, aborting.';
			$this->print_messages();
			return;
		}

		// pad shorter rows with empty values
		$csv->symmetrize();

		// WordPress sets the correct timezone for date functions somewhere
		// in the bowels of wp_insert_post(). We need strtotime() to return
		// correct time before the call to wp_insert_post().
		$tz = get_option('timezone_string');
		if ($tz && function_exists('date_default_timezone_set')) {
			date_default_timezone_set($tz);
		}


		echo '<div id="running">';
		foreach ($csv->connect() as $csv_data) {

			if ($post_id = $this->create_post($csv_data, $options)) {

				//上書き許可
				if( $addnew == true ){
					$this->create_custom_fields($post_id, $csv_data);
						echo str_pad('',256);
						echo '*';
						flush();

				}else{
					if( $opt_overwrite == 'yes' ){
						$this->create_custom_fields($post_id, $csv_data);
						echo str_pad('',256);
						echo '*';
						flush();
					}else{
				                $skipped++;
				                $imported--;
					}
				}

			} else {
				$skipped++;
				$imported--;
			}
		}
		echo '</div>';

/*
		//optionsに再登録(op)
		$children = array();
		$terms = get_terms('bukken', array('get' => 'all', 'orderby' => 'slug', 'fields' => 'id=>parent') );
		foreach ( $terms as $term_id => $parent ) {
			if ( $parent > 0 )
				$children[$parent][] = $term_id;
		}
		update_option('bukken_children', $children);
*/


		if (file_exists($file)) {
			@unlink($file);
		}

		$exec_time = microtime(true) - $time_start;

		if ($skipped) {
			$this->log['notice'][] = "<b>{$skipped} 件、登録できませんでした。</b>";
		}

		if($imported =='') $imported=0;
		if($updated =='') $updated=0;

		$this->log['notice'][] = sprintf("<b>新規 {$imported} 件、更新 {$updated} 件、 登録しました。( %.2f sec).</b>", $exec_time);
		$this->print_messages();

		echo "\n";
		echo '<style type="text/css">';
		echo '<!--';
		echo "\n";
		echo 'div#running { display:none; }';
		echo "\n";
		echo '// -->';
		echo '</style>';
		echo "\n";

	}

	function create_post($data, $options) {

		global $wpdb,$imported,$updated;
		global $opt_csv;
		global $addnew;
		global $opt_overwrite,$opt_draft;
		global $user_ID;

		global $shubetsu_data;


		extract($options);

		$data = array_merge($this->defaults, $data);


		if($opt_csv == 'c21'){

			$tmp_opt_draft_c = $opt_draft;

			//c21
			if($data['売り止め物件'] == "売り止め"){
				$tmp_opt_draft_c = "private";
			}

			//インターネット公開の場合に公開
			if($data['インターネット公開'] !="公開"){
				$tmp_opt_draft_c = "private";
			}


			//物件種類が無ければ賃貸
			if( isset($data['物件種類']) ){
				//タイトル
				$post_title_data = convert_chars($data['所在地（区市町村)']).''.convert_chars($data['所在地（大字・通称）']).''.convert_chars($data['所在地（字名・丁目)']);
				$post_title_data .= '　'.convert_chars($data['物件種類']).' '.convert_chars($data['物件種目']);

				//物件種別
				$shubetsu_data = '';
				$bukken_shub = $data['物件種別'];
				$bukken_shur = $data['物件種類'];
				$bukken_shum = $data['物件種目'];

				//売買・投資(事業)
				if($bukken_shub !="" && $bukken_shur !="" ){

					if($bukken_shub == '居住用'){
						switch ($bukken_shur) {
							case '売土地':
								$shubetsu_data = '1101'; $bukkenshubetsu_data = '売地'; break;
							case '売一戸建':
								switch ($bukken_shum) {
									case '新築一戸建':
										$shubetsu_data = '1201'; $bukkenshubetsu_data = '新築戸建'; break;
									case '中古一戸建':
										$shubetsu_data = '1202'; $bukkenshubetsu_data = '中古戸建'; break;
									case '新築テラスハウス':
										$shubetsu_data = '1203'; $bukkenshubetsu_data = '新築テラスハウス'; break;
									case '中古テラスハウス':
										$shubetsu_data = '1204'; $bukkenshubetsu_data = '中古テラスハウス'; break;
								}
								break;
							case '売マンション':
								switch ($bukken_shum) {
									case '新築マンション':
										$shubetsu_data = '1301'; $bukkenshubetsu_data = '新築マンション'; break;
									case '中古マンション':
										$shubetsu_data = '1302'; $bukkenshubetsu_data = '中古マンション'; break;
									case '新築公団':
										$shubetsu_data = '1303'; $bukkenshubetsu_data = '新築公団'; break;
									case '中古公団':
										$shubetsu_data = '1304'; $bukkenshubetsu_data = '中古公団'; break;
									case '新築公社':
										$shubetsu_data = '1305'; $bukkenshubetsu_data = '新築公社'; break;
									case '中古公社':
										$shubetsu_data = '1306'; $bukkenshubetsu_data = '中古公社'; break;
									case '新築タウンハウス':
										$shubetsu_data = '1307'; $bukkenshubetsu_data = '新築タウンハウス'; break;
									case '中古タウンハウス':
										$shubetsu_data = '1308'; $bukkenshubetsu_data = '中古タウンハウス'; break;
									case 'リゾートマンション':
										$shubetsu_data = '1309'; $bukkenshubetsu_data = 'リゾートマンション'; break;
								}
								break;
						}
					}

					if($bukken_shub == '投資用'){
						switch ($bukken_shur) {
							//【売住宅以外の建物全部】
							case '一棟':
								switch ($bukken_shum) {
									case '店舗':
										$shubetsu_data = '1401'; $bukkenshubetsu_data = '店舗'; break;
									case '店舗付住宅':
										$shubetsu_data = '1403'; $bukkenshubetsu_data = '店舗付住宅'; break;
									case '事務所':
										$shubetsu_data = '1405'; $bukkenshubetsu_data = '事務所'; break;
									case 'ビル':
										$shubetsu_data = '1407'; $bukkenshubetsu_data = 'ビル'; break;
									case '工場':
										$shubetsu_data = '1408'; $bukkenshubetsu_data = '工場'; break;
									case 'マンション':
										$shubetsu_data = '1409'; $bukkenshubetsu_data = 'マンション'; break;
									case '倉庫':
										$shubetsu_data = '1410'; $bukkenshubetsu_data = '倉庫'; break;
									case 'アパート':
										$shubetsu_data = '1411'; $bukkenshubetsu_data = 'アパート'; break;
									case '寮':
										$shubetsu_data = '1412'; $bukkenshubetsu_data = '寮'; break;
									case '旅館':
										$shubetsu_data = '1413'; $bukkenshubetsu_data = '旅館'; break;
									case 'ホテル':
										$shubetsu_data = '1414'; $bukkenshubetsu_data = 'ホテル'; break;
									case '一戸建':
									case 'その他':
										$shubetsu_data = '1499'; $bukkenshubetsu_data = 'その他'; break;
								}

								break;
							//【売住宅以外の建物一部】
							case '区分':
								switch ($bukken_shum) {
									case '店舗':
										$shubetsu_data = '1502'; $bukkenshubetsu_data = '店舗'; break;
									case '店舗付住宅':
										$shubetsu_data = '1502'; $bukkenshubetsu_data = '店舗付住宅'; break;
									case '事務所':
										$shubetsu_data = '1505'; $bukkenshubetsu_data = '事務所'; break;
									case 'マンション':
										$shubetsu_data = '1509'; $bukkenshubetsu_data = 'マンション'; break;
								}
								break;
						}
					}
				}

			}else{
				//タイトル
				$post_title_data = convert_chars($data['市区町村']).''.convert_chars($data['大字･通称']).''.convert_chars($data['字名・丁目']);
				$post_title_data .= '　'.convert_chars($data['物件種目']);

				//物件種別
				$shubetsu_data = '';
				$bukken_shub = $data['物件種別'];
				$bukken_shum = $data['物件種目'];

				//賃貸
				if($bukken_shub !="" && $bukken_shum !=""){

					if($bukken_shub == '居住用'){
								switch ($bukken_shum) {
									case 'マンション':
										$shubetsu_data = '3101'; $bukkenshubetsu_data = 'マンション'; break;
									case 'アパート':
										$shubetsu_data = '3102'; $bukkenshubetsu_data = 'アパート'; break;
									case '貸家':
										$shubetsu_data = '3103'; $bukkenshubetsu_data = '貸家'; break;
									case '店舗付住宅':
										$shubetsu_data = '3103'; $bukkenshubetsu_data = '店舗付住宅'; break;
									case 'テラスハウス':
										$shubetsu_data = '3104'; $bukkenshubetsu_data = 'テラスハウス'; break;
									case 'タウンハウス':
										$shubetsu_data = '3105'; $bukkenshubetsu_data = 'タウンハウス'; break;
									case '間借り':
										$shubetsu_data = '3106'; $bukkenshubetsu_data = '間借り'; break;
									case 'コーポ':
										$shubetsu_data = '3122'; $bukkenshubetsu_data = 'コーポ'; break;
									case 'ハイツ':
										$shubetsu_data = '3123'; $bukkenshubetsu_data = 'ハイツ'; break;
								}

					}

					if($bukken_shub == '事業用'){
								switch ($bukken_shum) {
									case '店舗（一戸建）':
										$shubetsu_data = '3201'; $bukkenshubetsu_data = '店舗（一戸建）'; break;
									case '店舗（建物一部）':
										$shubetsu_data = '3202'; $bukkenshubetsu_data = '店舗（建物一部）'; break;
									case '事務所':
										$shubetsu_data = '3203'; $bukkenshubetsu_data = '事務所'; break;
									case '店舗・事務所':
										$shubetsu_data = '3204'; $bukkenshubetsu_data = '店舗・事務所'; break;
									case '工場':
										$shubetsu_data = '3205'; $bukkenshubetsu_data = '工場'; break;
									case '倉庫':
										$shubetsu_data = '3206'; $bukkenshubetsu_data = '倉庫'; break;
									case '貸家（事業用）':
										$shubetsu_data = '3207'; $bukkenshubetsu_data = '貸家（事業用）'; break;
									case 'マンション（事業用）':
										$shubetsu_data = '3208'; $bukkenshubetsu_data = 'マンション（事業用）'; break;
									case '旅館':
										$shubetsu_data = '3209'; $bukkenshubetsu_data = '旅館'; break;
									case '寮':
										$shubetsu_data = '3210'; $bukkenshubetsu_data = '寮'; break;
									case '別荘':
										$shubetsu_data = '3211'; $bukkenshubetsu_data = '別荘'; break;
									case '貸地':
										$shubetsu_data = '3212'; $bukkenshubetsu_data = '貸地'; break;
									case 'ビル':
										$shubetsu_data = '3213'; $bukkenshubetsu_data = 'ビル'; break;
									case '住宅付店舗（一戸建）':
										$shubetsu_data = '3214'; $bukkenshubetsu_data = '住宅付店舗（一戸建）'; break;
									case '住宅付店舗（建物一部）':
										$shubetsu_data = '3215'; $bukkenshubetsu_data = '住宅付店舗（建物一部）'; break;
									case 'その他':
										$shubetsu_data = '3299'; $bukkenshubetsu_data = 'その他'; break;
								}
					}
				}
			}


			if($post_title_data=='')
				$post_title_data=' ';

			//C21チェック
			if( isset($data['シリアルコード']) ){
			        $new_post = array(
			            'post_title' => $post_title_data,						//記事のタイトル 
			            'post_content' => convert_chars($data['備考']),				//記事の内容
			            'post_status' => $tmp_opt_draft_c, 						//投稿ステータス 
			            'post_type' => $opt_type,							//投稿種別
			            'post_date' => $this->parse_date($data['物件更新日']),			//記事の投稿日（過去日付も先日付も対応。先日付にすれば、予約投稿になります。）
			            'post_excerpt' => convert_chars($data['セールスポイント']),			//記事の要約
			            'post_name' => $data['csv_post_slug'],					//投稿スラッグ 
			            'post_author' => $user_ID							//投稿者のユーザID 
			        );
			}
		}



		if($opt_csv == 'homes'){

			$tmp_opt_draft = $opt_draft;

			//1:空有/売出中の場合に公開
			if( $data['状態'] !="1" ){
				$tmp_opt_draft = "draft";
			}

			//成約日がある場合は非公開
			if( $data['成約日'] !="" ){
				$tmp_opt_draft = "private";
			}


			//タイトル
			if($data['所在地'] != ""){
				$shozaichiken_data = $data['所在地'];
				$shozaichiken_data = myLeft($shozaichiken_data,2);

				$shozaichicode_data = $data['所在地'];
				$shozaichicode_data = myLeft($shozaichicode_data,5);
				$shozaichicode_data = myRight($shozaichicode_data,3);

				$sql =  "SELECT MA.middle_area_name, NA.narrow_area_name";
				$sql .= " FROM ".$wpdb->prefix."area_narrow_area  AS NA";
				$sql .= " INNER JOIN ".$wpdb->prefix."area_middle_area  AS MA ON NA.middle_area_id = MA.middle_area_id";
				$sql .= " WHERE MA.middle_area_id=$shozaichiken_data AND NA.narrow_area_id=$shozaichicode_data";
			//	$sql = $wpdb->prepare($sql,'');
				$metas = $wpdb->get_row( $sql );

				$post_title_data = $metas->narrow_area_name.convert_chars($data['所在地名称']) ;

			}

			if($data['物件種別'] != ""){

				switch ($data['物件種別']) {
					case "1101":	$bukkenshubetsu_data = '売地'; 		 break;
					case "1102":	$bukkenshubetsu_data = '借地権譲渡';		 break;
					case "1103":	$bukkenshubetsu_data = '底地権譲渡';		 break;
					case "1201":	$bukkenshubetsu_data = '新築戸建';		 break;
					case "1202":	$bukkenshubetsu_data = '中古戸建';		 break;
					case "1203":	$bukkenshubetsu_data = '新築テラスハウス';		 break;
					case "1204":	$bukkenshubetsu_data = '中古テラスハウス';		 break;
					case "1301":	$bukkenshubetsu_data = '新築マンション';		 break;
					case "1302":	$bukkenshubetsu_data = '中古マンション';		 break;
					case "1303":	$bukkenshubetsu_data = '新築公団';		 break;
					case "1304":	$bukkenshubetsu_data = '中古公団';		 break;
					case "1305":	$bukkenshubetsu_data = '新築公社';		 break;
					case "1306":	$bukkenshubetsu_data = '中古公社';		 break;
					case "1307":	$bukkenshubetsu_data = '新築タウン';		 break;
					case "1308":	$bukkenshubetsu_data = '中古タウン';		 break;
					case "1309":	$bukkenshubetsu_data = 'リゾートマン';		 break;
					case "1401":	$bukkenshubetsu_data = '店舗';		 break;
					case "1403":	$bukkenshubetsu_data = '店舗付住宅';		 break;
					case "1404":	$bukkenshubetsu_data = '住宅付店舗';		 break;
					case "1405":	$bukkenshubetsu_data = '事務所';		 break;
					case "1406":	$bukkenshubetsu_data = '店舗・事務所';		 break;
					case "1407":	$bukkenshubetsu_data = 'ビル';		 break;
					case "1408":	$bukkenshubetsu_data = '工場';		 break;
					case "1409":	$bukkenshubetsu_data = 'マンション';		 break;
					case "1410":	$bukkenshubetsu_data = '倉庫';		 break;
					case "1411":	$bukkenshubetsu_data = 'アパート';		 break;
					case "1412":	$bukkenshubetsu_data = '寮';		 break;
					case "1413":	$bukkenshubetsu_data = '旅館';		 break;
					case "1414":	$bukkenshubetsu_data = 'ホテル';		 break;
					case "1415":	$bukkenshubetsu_data = '別荘';		 break;
					case "1416":	$bukkenshubetsu_data = 'リゾートマン';		 break;
					case "1420":	$bukkenshubetsu_data = '社宅';		 break;
					case "1499":	$bukkenshubetsu_data = 'その他';		 break;
					case "1502":	$bukkenshubetsu_data = '店舗';		 break;
					case "1505":	$bukkenshubetsu_data = '事務所';		 break;
					case "1506":	$bukkenshubetsu_data = '店舗・事務所';		 break;
					case "1507":	$bukkenshubetsu_data = 'ビル';		 break;
					case "1509":	$bukkenshubetsu_data = 'マンション';		 break;
					case "1599":	$bukkenshubetsu_data = 'その他';		 break;
					case "3101":	$bukkenshubetsu_data = 'マンション';		 break;
					case "3102":	$bukkenshubetsu_data = 'アパート';		 break;
					case "3103":	$bukkenshubetsu_data = '一戸建';		 break;
					case "3104":	$bukkenshubetsu_data = 'テラスハウス';		 break;
					case "3105":	$bukkenshubetsu_data = 'タウンハウス';		 break;
					case "3106":	$bukkenshubetsu_data = '間借り';		 break;
					case "3110":	$bukkenshubetsu_data = '寮・下宿';		 break;
					case "3201":	$bukkenshubetsu_data = '店舗(建物全部)';		 break;
					case "3202":	$bukkenshubetsu_data = '店舗(建物一部)';		 break;
					case "3203":	$bukkenshubetsu_data = '事務所';		 break;
					case "3204":	$bukkenshubetsu_data = '店舗・事務所';		 break;
					case "3205":	$bukkenshubetsu_data = '工場';		 break;
					case "3206":	$bukkenshubetsu_data = '倉庫';		 break;
					case "3207":	$bukkenshubetsu_data = '一戸建';		 break;
					case "3208":	$bukkenshubetsu_data = 'マンション';		 break;
					case "3209":	$bukkenshubetsu_data = '旅館';		 break;
					case "3210":	$bukkenshubetsu_data = '寮';		 break;
					case "3211":	$bukkenshubetsu_data = '別荘';		 break;
					case "3212":	$bukkenshubetsu_data = '土地';		 break;
					case "3213":	$bukkenshubetsu_data = 'ビル';		 break;
					case "3214":	$bukkenshubetsu_data = '住宅付店舗(一戸建)';		 break;
					case "3215":	$bukkenshubetsu_data = '住宅付店舗(建物一部)';		 break;
					case "3282":	$bukkenshubetsu_data = '駐車場';		 break;
					case "3299":	$bukkenshubetsu_data = 'その他';		 break;
					case "3122":	$bukkenshubetsu_data = 'コーポ';		 break;
					case "3123":	$bukkenshubetsu_data = 'ハイツ';		 break;
					case "3124":	$bukkenshubetsu_data = '文化住宅';		 break;
					case "1104":	$bukkenshubetsu_data = '建付土地';		 break;
				}

				$post_title_data .= ' '.$bukkenshubetsu_data ;
			}

			if($post_title_data=='')
				$post_title_data=' ';

			//$post_content_data = convert_chars($data['備考1']);
			$post_content_data = '';


			if( isset($data['建物名(物件名)']) ){		//ホームズチェック
			        $new_post = array(
			            'post_title' => $post_title_data,						//記事のタイトル 
			            'post_content' => $post_content_data,					//記事の内容
			            'post_status' => $tmp_opt_draft, 						//投稿ステータス 
			            'post_type' => $opt_type,							//投稿種別
			            'post_date' => $this->parse_date($data['自社管理修正日時']),		//記事の投稿日（過去日付も先日付も対応。先日付にすれば、予約投稿になります。）
			            'post_excerpt' => convert_chars($data['物件の特徴']),			//記事の要約
			            'post_name' => $data['csv_post_slug'],					//投稿スラッグ 
			            'post_author' => $user_ID						//投稿者のユーザID 
			        );
			}
		}

		if($opt_csv == 'k_rains'){

			$tmp_opt_draft_k = $opt_draft;

			if( strpos($data['備考補足'], '不可') !== false)
				$tmp_opt_draft_k = "draft";
			if( strpos($data['備考補足'], '厳禁') !== false)
				$tmp_opt_draft_k = "draft";


			if( isset($data['価格／賃料（万円）']) || isset($data['価格／賃料']) ){		//レインズチェック
			        $new_post = array(
			            'post_title' => convert_chars($data['所在地']).' '.convert_chars($data['物件種目']),	//記事のタイトル 
			            'post_content' => convert_chars($data['備考']),				//記事の内容
			            'post_status' => $tmp_opt_draft_k, 						//投稿ステータス 
			            'post_type' => $opt_type,							//投稿種別
			            'post_date' => $this->parse_date($data['登録日付']),			//記事の投稿日（過去日付も先日付も対応。先日付にすれば、予約投稿になります。）
			            'post_excerpt' =>' ',							//記事の要約
			            'post_name' => $data['csv_post_slug'],					//投稿スラッグ 
			            'post_author' => $user_ID							//投稿者のユーザID 
			        );
			}
		}


		if($opt_csv == 'h_rains'){

			$tmp_opt_draft_h = $opt_draft;

			if( strpos($data['備考1'], '不可') !== false)
				$tmp_opt_draft_h = "draft";
			if( strpos($data['備考1'], '厳禁') !== false)
				$tmp_opt_draft_h = "draft";
			if( strpos($data['備考1'], '掲載禁止') !== false)
				$tmp_opt_draft_h = "draft";


			if( strpos($data['備考2'], '不可') !== false)
				$tmp_opt_draft_h = "draft";
			if( strpos($data['備考2'], '厳禁') !== false)
				$tmp_opt_draft_h = "draft";
			if( strpos($data['備考2'], '掲載禁止') !== false)
				$tmp_opt_draft_h = "draft";

			if( $data['変更年月日'] !='' ){
				$tmp_post_date_h = $this->parse_date($data['変更年月日']);
			}else{
				$tmp_post_date_h = $this->parse_date($data['登録年月日']);
			}



			//タイトル

			//物件種別
			$shubetsu_data = '';
			$data_shu = intval($data['データ種類']);
			$bukken_shub = intval($data['物件種別']);
			$bukken_shum = intval($data['物件種目']);

			if($data_shu!="" && $bukken_shub!="" && $bukken_shum!=""){

				switch ($data_shu) {

					//売買物件
					case 1:
						switch ($bukken_shub) {
							//土地
							case 1:
								switch ($bukken_shum) {
									case 1:	//売地
										$shubetsu_data = '1101'; $bukkenshubetsu_data = '売地'; 	break;
									case 2:	//借地権
										$shubetsu_data = '1102'; $bukkenshubetsu_data = '借地権'; 	break;
									case 3:	//底地権
										$shubetsu_data = '1103'; $bukkenshubetsu_data = '底地権'; 	break;
								}
								break;
							//戸建
							case 2:
								switch ($bukken_shum) {
									case 1:	//新築戸建
										$shubetsu_data = '1201'; $bukkenshubetsu_data = '新築戸建'; 	break;
									case 2:	//中古戸建
										$shubetsu_data = '1202'; $bukkenshubetsu_data = '中古戸建'; 	break;
									case 3:	//新築テラス
										$shubetsu_data = '1203'; $bukkenshubetsu_data = '新築テラス'; 	break;
									case 4:	//中古テラス
										$shubetsu_data = '1204'; $bukkenshubetsu_data = '中古テラス'; 	break;
								}
								break;
							//マン
							case 3:
								switch ($bukken_shum) {
									case 1:	//新築マン
										$shubetsu_data = '1301'; $bukkenshubetsu_data = '新築マンション'; 	break;
									case 2:	//中古マン
										$shubetsu_data = '1302'; $bukkenshubetsu_data = '中古マンション'; 	break;
									case 3:	//新築タウン
										$shubetsu_data = '1307'; $bukkenshubetsu_data = '新築タウン'; 	break;
									case 4:	//中古タウン
										$shubetsu_data = '1308'; $bukkenshubetsu_data = '中古タウン'; 	break;
									case 5:	//新築リゾートマン
									case 6:	//中古リゾートマン
										$shubetsu_data = '1309'; $bukkenshubetsu_data = 'リゾートマンション'; 	break;
									case 9:	//その他
									case 99: //その他
										$shubetsu_data = '1399'; $bukkenshubetsu_data = 'マンションその他'; 	break;
								}
								break;

							//外全
							case 4:
								switch ($bukken_shum) {
									case 1:	//店舗
										$shubetsu_data = '1401'; $bukkenshubetsu_data = '店舗'; 	break;
									case 2:	//店付住宅
										$shubetsu_data = '1403'; $bukkenshubetsu_data = '店付住宅'; 	break;
									case 3:	//住付店舗
										$shubetsu_data = '1404'; $bukkenshubetsu_data = '住付店舗'; 	break;
									case 4:	//事務所
										$shubetsu_data = '1405'; $bukkenshubetsu_data = '事務所'; 	break;
									case 5:	//店舗事務
										$shubetsu_data = '1406'; $bukkenshubetsu_data = '店舗事務'; 	break;
									case 6:	//ビル
										$shubetsu_data = '1407'; $bukkenshubetsu_data = 'ビル'; 	break;
									case 7:	//工場
										$shubetsu_data = '1408'; $bukkenshubetsu_data = '工場'; 	break;
									case 8:	//マンション
										$shubetsu_data = '1409'; $bukkenshubetsu_data = 'マンション'; 	break;
									case 9:	//倉庫
										$shubetsu_data = '1410'; $bukkenshubetsu_data = '倉庫'; 	break;
									case 10:	//アパート
										$shubetsu_data = '1411'; $bukkenshubetsu_data = 'アパート'; 	break;
									case 11:	//寮
										$shubetsu_data = '1412'; $bukkenshubetsu_data = '寮'; 	break;
									case 12:	//旅館
										$shubetsu_data = '1413'; $bukkenshubetsu_data = '旅館'; 	break;
									case 13:	//ホテル
										$shubetsu_data = '1414'; $bukkenshubetsu_data = 'ホテル'; 	break;
									case 14:	//別荘
										$shubetsu_data = '1415'; $bukkenshubetsu_data = '別荘'; 	break;
									case 15:	//リゾートマン
										$shubetsu_data = '1416'; $bukkenshubetsu_data = 'リゾートマンション'; 	break;
									case 16:	//文化住宅
										$shubetsu_data = '1421'; $bukkenshubetsu_data = '文化住宅'; 	break;
									case 99:	//その他
										$shubetsu_data = '1499'; $bukkenshubetsu_data = '外全その他'; 	break;
								}
								break;

							//外一
							case 5:
								switch ($bukken_shum) {
									case 1:	//店舗':
										$shubetsu_data = '1502'; $bukkenshubetsu_data = '店舗'; 	break;
									case 2:	//事務所':
										$shubetsu_data = '1505'; $bukkenshubetsu_data = '事務所'; 	break;
									case 3:	//店舗事務':
										$shubetsu_data = '1506'; $bukkenshubetsu_data = '店舗事務'; 	break;
									case 9:	//その他':
									case 99:	//その他':
										$shubetsu_data = '1599'; $bukkenshubetsu_data = '外一その他'; 	break;
								}
								break;
						}
						break;



					//賃貸物件
					case 3:
						switch ($bukken_shub) {
							//事業 賃貸土地
							case 1:
								switch ($bukken_shum) {
									case 1:
									case 2:	//土地
										$shubetsu_data = '3212'; $bukkenshubetsu_data = '賃貸土地'; 	break;
								}
								break;

							//事業 賃貸外全
							case 4:
								switch ($bukken_shum) {
									case 1:	//店舗戸建
										$shubetsu_data = '3201'; $bukkenshubetsu_data = '店舗戸建'; 	break;
									case 2:	//事務所
										$shubetsu_data = '3203'; $bukkenshubetsu_data = '事務所'; 	break;
									case 3:	//工場
										$shubetsu_data = '3205'; $bukkenshubetsu_data = '工場'; 	break;
									case 4:	//倉庫
										$shubetsu_data = '3206'; $bukkenshubetsu_data = '倉庫'; 	break;
									case 5:	//マンション
										$shubetsu_data = '3208'; $bukkenshubetsu_data = '事業用マンション'; 	break;
									case 6:	//旅館
										$shubetsu_data = '3209'; $bukkenshubetsu_data = '旅館'; 	break;
									case 7:	//寮
										$shubetsu_data = '3210'; $bukkenshubetsu_data = '事業用寮'; 	break;
									case 8:	//別荘
										$shubetsu_data = '3211'; $bukkenshubetsu_data = '事業用別荘'; 	break;
									case 9:	//ビル
										$shubetsu_data = '3213'; $bukkenshubetsu_data = '事業用ビル'; 	break;
									case 10:	//住店舗戸建
										$shubetsu_data = '3214'; $bukkenshubetsu_data = '住店舗戸建'; 	break;
									case 11:	//店舗・事務
										$shubetsu_data = '3204'; $bukkenshubetsu_data = '店舗・事務所'; 	break;
									case 99:	//その他
										$shubetsu_data = '3299'; $bukkenshubetsu_data = '事業用その他'; 	break;
								}
								break;

							//事業 賃貸外一
							case 5:
								switch ($bukken_shum) {
									case 1:	//店舗一部
										$shubetsu_data = '3202'; $bukkenshubetsu_data = '店舗一部'; 	break;
									case 2:	//事務所
										$shubetsu_data = '3203'; $bukkenshubetsu_data = '事務所'; 	break;
									case 3:	//店舗・事務:
										$shubetsu_data = '3204'; $bukkenshubetsu_data = '店舗・事務所'; 	break;
									case 4:	//住店舗一部
										$shubetsu_data = '3215'; $bukkenshubetsu_data = '住店舗一部'; 	break;
									case 5:	//マンション
										$shubetsu_data = '3208'; $bukkenshubetsu_data = '事業用マンション'; 	break;
									case 9:	//その他
									case 99:	//その他
										$shubetsu_data = '3299'; $bukkenshubetsu_data = '事業用その他'; 	break;
								}
								break;

							//居住 賃貸一戸建 賃貸マンション
							case 2:
								switch ($bukken_shum) {
									case 1;	//貸家
										$shubetsu_data = '3103'; $bukkenshubetsu_data = '貸家'; 	break;
									case 2;	//テラスハウス
										$shubetsu_data = '3104'; $bukkenshubetsu_data = 'テラスハウス'; 	break;
								}
								break;

							case 3:
								switch ($bukken_shum) {
									case 1;	//マンション
										$shubetsu_data = '3101'; $bukkenshubetsu_data = 'マンション'; 	break;
									case 2;	//アパート
										$shubetsu_data = '3102'; $bukkenshubetsu_data = 'アパート'; 	break;
									case 3;	//タウンハウス':
										$shubetsu_data = '3105'; $bukkenshubetsu_data = 'タウンハウス'; 	break;
									case 4;	//間借り':
										$shubetsu_data = '3106'; $bukkenshubetsu_data = '間借り'; 	break;
									case 5;	//文化住宅
										$shubetsu_data = '3124'; $bukkenshubetsu_data = '文化住宅'; 	break;
								}
								break;
						}
						break;
				}

			}


			$post_title_data = convert_chars($data['所在地名1']).''.convert_chars($data['所在地名2']);
			$post_title_data .= ' '.$bukkenshubetsu_data ;

			if($post_title_data=='')
				$post_title_data=' ';



			if( isset($data['物件番号']) && isset($data['沿線略称(1)']) ){		//レインズチェック
			        $new_post = array(
			            'post_title' => $post_title_data,						//記事のタイトル 
			            'post_content' => convert_chars($data['備考1']),				//記事の内容
			            'post_status' => $tmp_opt_draft_h, 						//投稿ステータス 
			            'post_type' => $opt_type,							//投稿種別
			            'post_date' => $tmp_post_date_h,						//記事の投稿日（過去日付も先日付も対応。先日付にすれば、予約投稿になります。）
			            'post_excerpt' =>' ',							//記事の要約
			            'post_name' => $data['csv_post_slug'],					//投稿スラッグ 
			            'post_author' => $user_ID							//投稿者のユーザID 
			        );
			}
		}



		//重複チェック
		if($opt_csv == 'homes'){
			$check_shikibesu=$data['自社管理物件番号'];
		}
		if($opt_csv == 'k_rains'){
			$check_shikibesu=$data['物件番号'];
		}
		if($opt_csv == 'h_rains'){
			$check_shikibesu=$data['物件番号'];
		}

		if($opt_csv == 'c21'){
			$check_shikibesu=$data['物件コード'];
		}


		if($check_shikibesu !=""){

			$sql = "SELECT post_id FROM $wpdb->postmeta WHERE `meta_key` = 'shikibesu' AND `meta_value` ='" .$check_shikibesu."'";
		//	$sql = $wpdb->prepare($sql,'');
			$metas = $wpdb->get_row( $sql );

			//重複の場合は上書き
			if($metas->post_id !=""){
				$addnew = false;
				//上書き許可
				if($opt_overwrite == 'yes'){
					$new_post['ID'] = $metas->post_id;
				        // update
				        $id = wp_update_post($new_post);
				        $updated++;
			        }


			}else{
				// create
				$id = wp_insert_post($new_post);
				$addnew = true;
				$imported++;
			}
		}else{
			// create
			$id = wp_insert_post($new_post);
			$imported++;
		}

	        return $id;

	}



	//カスタムフィールド登録
	function create_custom_fields($post_id, $data) {

		global $work_homes,$work_k_rains,$work_k_rains_rosen;
		global $work_h_rains,$work_h_rains_rosen;
		global $work_c21,$work_c21_2,$work_c21_rosen;
		global $opt_kaiin;

		if( FUDOU_IMG_MAX > 10 ){
		  $work_c21 = array_merge($work_c21, $work_c21_2);
		}



	  	global $opt_csv;
		global $wpdb;
		global $addnew;

		//CSVタイプ
		if($addnew==true){
			$sql_txt = "(".$post_id.",'csvtype','".$opt_csv."')";
		}else{
			update_post_meta($post_id,'csvtype', $opt_csv);
		}

		//会員設定
		if( $opt_kaiin != '' ){
			if($addnew==true){
				$sql_txt .= ",(".$post_id.",'kaiin','".$opt_kaiin."')";
			}else{
				update_post_meta($post_id,'kaiin', $opt_kaiin);
			}
		}else{
			if($addnew==true){
				$sql_txt .= ",(".$post_id.",'kaiin','')";
			}
		}

		//設備(c21)
		$setsubi_data = "99900";

		////
		//global $tmp_txt;
		////
		//loop

		global $shubetsu_data;
		global $work_c21_setsubi;

		$shuuhensonota = '';


	        foreach ($data as $k => $v) {

			//センチュリ21
			if($opt_csv == 'c21'){

				//物件種別

				if($shubetsu_data != ''){
					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'bukkenshubetsu','".$shubetsu_data."')";
					}else{
						update_post_meta($post_id, 'bukkenshubetsu', $shubetsu_data);
					}
					$tmp_shubetsu_data = $shubetsu_data;
					$shubetsu_data = '';
				}

				if($k=="緯度(WGS)" && $v=="0") $v='';
				if($k=="経度(WGS)" && $v=="0") $v='';
				if($k=="物件コード" && $v=="") $v=$post_id;	//自動採番
				if($k=="名称を公開" && $v=="名称を公開する") $v=1;
				if($k=="売り止め物件" && $v=="売り止め") $v=4;
				if($k=="価格" && $v!="") $v=$v*10000;
				if($k=="坪単価" && $v!="") $v=$v*10000;
				if($k=="車１" && $v!="") $v= '車 '.$v.'分';
				if($k=="築年月" && $v!="") $v=$this->parse_date3($v);

				if($v=="0") $v='';

				//間取詳細タイプ
				//【改REINS】1:和室 2:洋室 3:DK 4:LDK 5:L 6:D 7:K 9:その他 21:LK 22:LD 23:S

				if($k=="間取詳細１（タイプ）" || $k=="間取詳細２（タイプ）" || $k=="間取詳細３（タイプ）" || $k=="間取詳細４（タイプ）" || $k=="間取詳細５（タイプ）" || $k=="間取詳細６（タイプ）" || $k=="間取詳細７（タイプ）" ){
					switch ($v) {
						case "和":		$v="1"; break;
						case "洋":		$v="2"; break;
						case "Ｌ":		$v="5"; break;
						case "Ｄ":		$v="6"; break;
						case "Ｓ":		$v="23"; break;
						case "Ｋ":		$v="7"; break;
						case "Ｒ":		$v="9"; break;
						case "ＤＫ":		$v="3"; break;
						case "ＬＫ":		$v="22"; break;
						case "ＳＤＫ":		$v="9"; break;
						case "ＬＤＫ":		$v="4"; break;
						case "ＳＬＤＫ":	$v="9"; break;
						case "ＬＤ":		$v="9"; break;
					}
				}

				//項目つけ合わせ
				foreach($work_c21 as $meta_box2){
					if($k == $meta_box2['h_name'] && $meta_box2['d_name'] !=''){
					//	if( $v != ''){
							if($addnew==true){
								$sql_txt .= ",(".$post_id.",'". $meta_box2['d_name']."','".$v."')";
							}else{
								update_post_meta($post_id, $meta_box2['d_name'], $v);
							}
					//	break;
					//	}
					}
				}

				//例外処理
				if($k=="小学校区" && $v!=""){
					$v = str_replace( "小学校" ,"" , $v);
					$v =  $v ."小学校"  ;

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'shuuhenshougaku','".$v."')";
					}else{
						update_post_meta($post_id, 'shuuhenshougaku', $v);
					}

				}
				if($k=="中学校区" && $v!=""){
					$v = str_replace( "中学校" ,"" , $v);
					$v =  $v ."中学校"  ;
					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'shuuhenchuugaku','".$v."')";
					}else{
						update_post_meta($post_id, 'shuuhenchuugaku', $v);
					}
				}

				if($k=="小学区" && $v!=""){
					$v = str_replace( "小学校" ,"" , $v);
					$v =  $v ."小学校"  ;
					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'shuuhenshougaku','".$v."')";
					}else{
						update_post_meta($post_id, 'shuuhenshougaku', $v);
					}
				}
				if($k=="中学区" && $v!=""){
					$v = str_replace( "中学校" ,"" , $v);
					$v =  $v ."中学校"  ;
					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'shuuhenchuugaku','".$v."')";
					}else{
						update_post_meta($post_id, 'shuuhenchuugaku', $v);
					}
				}

				//賃貸現況
				if($k == "現況" && $tmp_shubetsu_data > 3000 && $v !=''){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'nyukyogenkyo','".$v."')";
						}else{
							update_post_meta($post_id, 'nyukyogenkyo', $v);
						}
				}

				//売買現況
				if($k == "建物現況" && $tmp_shubetsu_data > 3000){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'nyukyogenkyo','".$v."')";
						}else{
							update_post_meta($post_id, 'nyukyogenkyo', $v);
						}
				}

				//売買土地現況
				if($k == "土地現況" && $tmp_shubetsu_data < 1400 ){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'nyukyogenkyo','".$v."')";
						}else{
							update_post_meta($post_id, 'nyukyogenkyo', $v);
						}
				}

				//投資現況
				if($k == "建物現況" && $tmp_shubetsu_data > 1400 && $tmp_shubetsu_data < 1600 ){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'nyukyogenkyo','".$v."')";
						}else{
							update_post_meta($post_id, 'nyukyogenkyo', $v);
						}
				}

				if($k=="緯度(WGS)" && $v!="") $bukkenido=$v;
				if($k=="経度(WGS)" && $v!="") $bukkenkeido=$v;

				if($k=="地図公開" && $v=="表示"){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'bukkenido','".$bukkenido."')";
							$sql_txt .= ",(".$post_id.",'bukkenkeido','".$bukkenkeido."')";
						}else{
							update_post_meta($post_id, 'bukkenido', $bukkenido);
							update_post_meta($post_id, 'bukkenkeido', $bukkenkeido);
						}
						$bukkenido='';
						$bukkenkeido='';
				}

				if($k=="管理費" && $v !="") $kanrihi = $v;
				if($k=="共益費"){
					if($v !='') $kanrihi = $kanrihi + $v;
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'kakakukyouekihi','".$kanrihi."')";
						}else{
							update_post_meta($post_id, 'kakakukyouekihi', $kanrihi);
						}
					$kanrihi = 0;
				}

				if($k=="敷引" && $v !="") $kakakushikibiki = $v;
				if($k=="償却金"){
					if($v !='') $kakakushikibiki = $kakakushikibiki + $v;
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'kakakushikibiki','".$kakakushikibiki."')";
						}else{
							update_post_meta($post_id, 'kakakushikibiki', $kakakushikibiki);
						}
					$kakakushikibiki = 0;
				}

				if( $tmp_shubetsu_data < 3000 ){
					if($k=="引渡時期" && $v!=""){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'nyukyojiki','".$v."')";
						}else{
							update_post_meta($post_id, 'nyukyojiki', $v);
						}
					}
				}else{
					if($k=="引渡条件" && $v!=""){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'nyukyojiki','".$v."')";
						}else{
							update_post_meta($post_id, 'nyukyojiki', $v);
						}
					}
				}


				if($k=="引渡時期（年）" && $v!="")	$nyukyonengetsu = $v;
				if($k=="引渡時期（月）" && $v!="")	$nyukyonengetsu2 = $nyukyonengetsu . '/' . $v;

				if($k=="引渡日（年）" && $v!="")	$nyukyonengetsu = $v;
				if($k=="引渡日（月）" && $v!="")	$nyukyonengetsu2 = $nyukyonengetsu . '/' . $v;

				if($nyukyonengetsu2 != '' ){
					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'nyukyonengetsu','".$nyukyonengetsu2."')";
					}else{
						update_post_meta($post_id, 'nyukyonengetsu', $nyukyonengetsu2);
					}
					$nyukyonengetsu = '';
					$nyukyonengetsu2 = '';
				}

				//設備
				if($k=="建築条件" && $v =='建築条件付' ) $setsubi_data .= "/11001";
				if($k=="建築条件" && $v =='なし' ) $setsubi_data .= "/11002";

				if($k=="ガス" && $v !='' ){
					switch ($v) {
						case "都市ガス":	$setsubi_data .= "/20101"; break;
						case "ＬＰＧ":		$setsubi_data .= "/20102"; break;
						case "天然":		$setsubi_data .= "/20101"; break;
						case "その他":		$setsubi_data .= "/20199"; break;
					}
				}

				if($k=="水道" && $v !='' ){
					switch ($v) {
						case "公営水道":	$setsubi_data .= "/20001"; break;
						case "簡易水道":	$setsubi_data .= "/20099"; break;
						case "井戸":		$setsubi_data .= "/20002"; break;
						case "その他":		$setsubi_data .= "/20099"; break;
						case "無":		$setsubi_data .= "/20099"; break;
					}
				}

				if($k=="汚水" && $v !='' ){
					switch ($v) {
						case "公共下水":	$setsubi_data .= "/20201"; break;
						case "集中浄化槽":	$setsubi_data .= "/20202"; break;
						case "個別浄化槽":	$setsubi_data .= "/20202"; break;
						case "汲取り":		$setsubi_data .= "/20203"; break;
						case "その他":		$setsubi_data .= "/20299"; break;
					}
				}

				if($k=="雑排水" && $v !='' ){
					switch ($v) {
						case "公共下水":	$setsubi_data .= "/20201"; break;
						case "浄化槽":	$setsubi_data .= "/20202"; break;
						case "側溝":	$setsubi_data .= "/20299"; break;
						case "その他":	$setsubi_data .= "/20299"; break;
					}
				}

				if($k=="風呂" && $v !='' ){
					switch ($v) {
						case "無":	$setsubi_data .= "/20303"; break;
			//			case "有":	$setsubi_data .= "/"; break;
						case "ユニットバス":	$setsubi_data .= "/26006"; break;
						case "共同":	$setsubi_data .= "/20302"; break;
					}
				}

				if($k=="トイレ" && $v !='' ){
					switch ($v) {
						case "水洗":	$setsubi_data .= "/"; break;
						case "改良":	$setsubi_data .= "/"; break;
						case "汲取":	$setsubi_data .= "/"; break;
						case "共同":	$setsubi_data .= "/20402"; break;
						case "無":	$setsubi_data .= "/20403"; break;
					}             
				}

				if($k=="楽器相談" && $v =='可' ) $setsubi_data .= "/10001";
				if($k=="事務所" && $v =='可' ) $setsubi_data .= "/10101";
				if($k=="事務所" && $v =='不可' ) $setsubi_data .= "/10102";
				if($k=="二人入居" && $v =='可' ) $setsubi_data .= "/10301";
				if($k=="二人入居" && $v =='不可' ) $setsubi_data .= "/10302";
				if($k=="性別限定" && $v =='男性' ) $setsubi_data .= "/10401";
				if($k=="性別限定" && $v =='女性' ) $setsubi_data .= "/10402";

				if($k=="単身者" && $v !='' ){
					switch ($v) {
						case "限定":	$setsubi_data .= "/10501"; break;
						case "希望":	$setsubi_data .= "/10502"; break;
						case "不可":	$setsubi_data .= "/10503"; break;
					}             
				}
				if($k=="法人" && $v !='' ){
					switch ($v) {
						case "限定":	$setsubi_data .= "/10601"; break;
						case "希望":	$setsubi_data .= "/10602"; break;
						case "不可":	$setsubi_data .= "/10603"; break;
					}             
				}

				if($k=="学生" && $v =='限定' ) $setsubi_data .= "/10701";
				if($k=="学生" && $v =='歓迎' ) $setsubi_data .= "/10702";
				if($k=="高齢者" && $v =='限定' ) $setsubi_data .= "/10801";
				if($k=="高齢者" && $v =='歓迎' ) $setsubi_data .= "/10802";
				if($k=="公庫利用" && $v =='利用可' ) $setsubi_data .= "/11101";
				if($k=="手付金保証" && $v =='有') $setsubi_data .= "/11201";
				if($k=="角地" && $v =='角地') $setsubi_data .= "/26002";
				if($k=="駐車場状況" && $v !=''){
					if( strpos($v, '有') !== false)
						$setsubi_data .= "/25005";
				}

				if($k=="設備詳細" && $v !='' ){
					foreach($work_c21_setsubi as $meta_box3){

						$pos = strpos($v, $meta_box3['name']);
						if ($pos === false) {
						} else {
							$setsubi_data .= "/" . $meta_box3['code'] ;
						}
					}
				}

				if($k=="画像コメント８" ){
					if($setsubi_data != "99900"){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'setsubi','".$setsubi_data."')";
						}else{
							update_post_meta($post_id, 'setsubi', $setsubi_data);
						}
					}
				}



				//間取部屋種類 10:R 20:K 25:SK 30:DK 35:SDK 40:LK 45:SLK 50:LDK 55:SLDK
				if($k=="間取" && $v !=""){
					$madorisu = myLeft($v,1);

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'madorisu','".$madorisu."')";
					}else{
						update_post_meta($post_id, 'madorisu', $madorisu);
					}

					$v2 = myRight($v,mb_strlen($v)-1);

					switch ($v2) {
						case "R":		$madorisyurui="10"; break;
						case "K":		$madorisyurui="20"; break;
						case "SK":		$madorisyurui="25"; break;
						case "DK":		$madorisyurui="30"; break;
						case "SDK":		$madorisyurui="35"; break;
						case "LK":		$madorisyurui="40"; break;
						case "SLK":		$madorisyurui="45"; break;
						case "LDK":		$madorisyurui="50"; break;
						case "SLDK":		$madorisyurui="55"; break;
					}
					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'madorisyurui','".$madorisyurui."')";
					}else{
						update_post_meta($post_id, 'madorisyurui', $madorisyurui);
					}
					$madorisu = '';
					$madorisyurui='';
				}



				//所在地
				if( ($k=="都道府県" || $k=="所在地(都道府県）" ) && $v!="" ){
					$shozaichiken_data = $v;
					$shozaichiken_code = fudo_ken_id($shozaichiken_data);

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'shozaichiken','".$shozaichiken_code."')";
					}else{
						update_post_meta($post_id, 'shozaichiken', $shozaichiken_code);
					}
				}


				if( ($k=="市区町村" || $k=="所在地（区市町村)"  ) && $v!="" && $shozaichiken_code !=''){

						$sql = "SELECT narrow_area_id FROM ".$wpdb->prefix."area_narrow_area WHERE middle_area_id=".$shozaichiken_code." AND narrow_area_name = '".$v."'";
					//	$sql = $wpdb->prepare($sql,'');
						$metas = $wpdb->get_row( $sql );
						$shozaichi_code = $metas->narrow_area_id;

						$shozaichi = $shozaichiken_code.$shozaichi_code."000000" ;


						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'shozaichicode','".$shozaichi."')";
						}else{
							update_post_meta($post_id, 'shozaichicode', $shozaichi);
						}
						$shozaichi ="";
				}



				if($k=="沿線１" && $v!=""){
					foreach($work_c21_rosen as $meta_box){
						if ($meta_box['ken_id'] ==  $shozaichiken_code && $meta_box['rosen_name'] == $v){
							$koutsurosen1 = $meta_box['rosen_id'];
							if($addnew==true){
								$sql_txt .= ",(".$post_id.",'koutsurosen1','".$koutsurosen1."')";
							}else{
								update_post_meta($post_id, 'koutsurosen1', $koutsurosen1);
							}
						}
					}
				}

				if($k=="駅１" && $v !="" && $koutsurosen1 !="" ){
					$v_sub = '';
					$v_sub2 = '';
					$findme = 'ヶ';
					$findme2 = 'ケ';

					$pos = strpos($v, $findme);
					if ($pos === false) {
					} else {
						$v_sub = str_replace( $findme ,$findme2 , $v);
					}
					$pos = strpos($v, $findme2);
					if ($pos === false) {
					} else {
						$v_sub2 = str_replace( $findme2 ,$findme , $v);
					}

					$sql = "SELECT DTS.station_id";
					$sql = $sql . " FROM ".$wpdb->prefix."train_station AS DTS";
					$sql = $sql . " WHERE DTS.rosen_id=".$koutsurosen1." AND DTS.middle_area_id=".$shozaichiken_code."";
					$sql = $sql . " AND ( DTS.station_name='".$v."'";
					if($v_sub != '')
						$sql = $sql . " OR DTS.station_name='".$v_sub."' ";
					if($v_sub2 != '')
						$sql = $sql . " OR DTS.station_name='".$v_sub2."' ";
					$sql = $sql . " )";

				//	$sql = $wpdb->prepare($sql,'');
					$metas = $wpdb->get_row( $sql );
					$meta = $metas->station_id;

					if($meta!=''){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'koutsueki1','".$meta."')";
						}else{
							update_post_meta($post_id, 'koutsueki1', $meta);
						}
					}
				//	$shozaichiken_code = '';
				}



				if($k=="沿線２" && $v!=""){
					foreach($work_c21_rosen as $meta_box){
						if ($meta_box['ken_id'] ==  $shozaichiken_code && $meta_box['rosen_name'] == $v){
							$koutsurosen2 = $meta_box['rosen_id'];
							if($addnew==true){
								$sql_txt .= ",(".$post_id.",'koutsurosen2','".$koutsurosen2."')";
							}else{
								update_post_meta($post_id, 'koutsurosen2', $koutsurosen2);
							}
						}

					}
				}

				if($k=="駅２" && $v !="" && $koutsurosen2 !="" ){
					$v_sub = '';
					$v_sub2 = '';
					$findme = 'ヶ';
					$findme2 = 'ケ';

					$pos = strpos($v, $findme);
					if ($pos === false) {
					} else {
						$v_sub = str_replace( $findme ,$findme2 , $v);
					}
					$pos = strpos($v, $findme2);
					if ($pos === false) {
					} else {
						$v_sub2 = str_replace( $findme2 ,$findme , $v);
					}

					$sql = "SELECT DTS.station_id";
					$sql = $sql . " FROM ".$wpdb->prefix."train_station AS DTS";
					$sql = $sql . " WHERE DTS.rosen_id=".$koutsurosen2." AND DTS.middle_area_id=".$shozaichiken_code."";
					$sql = $sql . " AND ( DTS.station_name='".$v."'";
					if($v_sub != '')
						$sql = $sql . " OR DTS.station_name='".$v_sub."' ";
					if($v_sub2 != '')
						$sql = $sql . " OR DTS.station_name='".$v_sub2."' ";
					$sql = $sql . " )";

				//	$sql = $wpdb->prepare($sql,'');
					$metas = $wpdb->get_row( $sql );
					$meta = $metas->station_id;

					if($meta!=''){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'koutsueki2','".$meta."')";
						}else{
							update_post_meta($post_id, 'koutsueki2', $meta);
						}
					}
					$shozaichiken_code = '';
				}

		        }
			//c21








			//ホームズ
			if($opt_csv == 'homes'){

				if($k =='区画面積' && $v=="0") $v='';
				//借地
				if($k =='契約期間(年)' && $v!="") $shakuchikikan_data = $v.'/';
				if($k =='契約期間(月)' && $v!=""){
					$shakuchikikan_data .= $v;
					if($shakuchikikan_data != ''){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'shakuchikikan','".$shakuchikikan_data."')";
						}else{
							update_post_meta($post_id, 'shakuchikikan', $shakuchikikan_data);
						}
						$shakuchikikan_data = '';
					}
				}


				// その他周辺環境
				if($k=="小学校名" && $v!="")		$shuuhensonota .= '[' . $v . ']';
				if($k=="小学校距離" && $v!="")		$shuuhensonota .= ' ' . $v . 'm';
				if($k=="中学校名" && $v!="")		$shuuhensonota .= '　[' . $v . ']';
				if($k=="中学校距離" && $v!="")		$shuuhensonota .= ' ' . $v . 'm';
				if($k=="コンビニ距離" && $v!="")	$shuuhensonota .= '　[コンビニ]' . $v . 'm';
				if($k=="スーパー距離" && $v!="")	$shuuhensonota .= '　[スーパー]' . $v . 'm';
				if($k=="総合病院距離" && $v!="")	$shuuhensonota .= '　[総合病院]' . $v . 'm';
				if( $addnew==true && $shuuhensonota != '' ){
					$sql_txt .= ",(".$post_id.",'". 'shuuhensonota'."','".$shuuhensonota."')";
				}else{
					update_post_meta($post_id, 'shuuhensonota', $shuuhensonota);
				}


				if($k=="設備・条件" && $v!=""){
					$v = str_replace( "25001" ,"25012" , $v);
				}



				foreach($work_homes as $meta_box){

					if($k == $meta_box['h_name'] && $meta_box['d_name'] !=''){
						//自動採番
						if($k=="自社管理物件番号" && $v=="") $v=$post_id;
						if($k=="分配率(客付分)" && $v=="110") $v="分かれ";
						if($k=="分配率(客付分)" && $v=="117") $v="正規手数料";

						//if($k=="築年月" && $v!="") $v=$this->parse_date2($v);
						//if($k=="引渡/入居年月" && $v!="") $v=$this->parse_date2($v);

						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'". $meta_box['d_name']."','".$v."')";
						}else{
							update_post_meta($post_id, $meta_box['d_name'], $v);
						}
					}

					/*
					//緯度/経度 日本測地(Tokyo97) 35.40.3/139.46.31
					if($k=="緯度/経度" && $v!=""){

						$bukkenido   = $this->str_cut_l($v , '/' );
						$bukkenkeido = $this->str_cut_r($v , '/' );

						if($bukkenido != '0' && $bukkenkeido  != '0'){
							//緯度
							$map_str = $bukkenido;
							$map_all = mb_strlen( $map_str, 'utf-8');
							$map_fun = $this->str_cut_l($map_str , '.' ) ;
							$map_do  = $this->str_cut_map_fun($map_str, 3 );
							$map_byou = $this->str_cut_r(myRight($map_str,$map_all - 3 ) , '.' ) ;
							$map_la   = $map_fun +  ((($map_do * 60+ $map_byou)*1000)/3600000);

							//経度
							$map_str = $bukkenkeido;
							$map_all = mb_strlen( $map_str, 'utf-8');
							$map_fun = $this->str_cut_l($map_str , '.' ) ;
							$map_do  = $this->str_cut_map_fun($map_str, 4 );
							$map_byou = $this->str_cut_r(myRight($map_str,$map_all - 4 ) , '.' ) ;
							$map_ln   = $map_fun +  ((($map_do * 60+ $map_byou)*1000)/3600000);

							//$bukkenido = $la + $la * 0.00010696 - $ln * 0.000017467 - 0.0046020;
							//$bukkenkeido = $ln + $la * 0.000046047 + $ln * 0.000083049 - 0.010041;

							if($addnew==true){
								$sql_txt .= ",(".$post_id.",'". 'bukkenido'."','".$map_la."')";
								$sql_txt .= ",(".$post_id.",'". 'bukkenkeido'."','".$map_ln."')";
							}else{
								update_post_meta($post_id, 'bukkenido', $map_la);
								update_post_meta($post_id, 'bukkenkeido', $map_ln);
							}
						}
					}
					*/
				}
		        }
			//ホームズ










			//東日本レインズ
			if($opt_csv == 'h_rains'){

				foreach($work_h_rains as $meta_box){
					if($k == $meta_box['h_name'] && $meta_box['d_name'] !=''){

						if($k=="取引態様" && $v!="") $v="6";
						if($k=="物件番号" && $v!="") $v=trim($v);

					//	if($k=="特優賃区分" && $v!="") $v="99900/11701";


						if($addnew==true){
							if(strpos($k, '画像') === false){
								$sql_txt .= ",(".$post_id.",'". $meta_box['d_name']."','".$v."')";
							}else{
								if( $v!="" )
									$sql_txt .= ",(".$post_id.",'". $meta_box['d_name']."','".$v."')";
							}
						}else{
							if(strpos($k, '画像') === false){
								update_post_meta($post_id, $meta_box['d_name'], $v);
							}else{
								if( $v!="" )
									update_post_meta($post_id, $meta_box['d_name'], $v);
							}

						}
					}
				}

				//建物面積 専有面積
				if( $data['建物面積1'] != ''){
     						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'tatemonomenseki','".$data['建物面積1']."')";
						}else{
							update_post_meta($post_id, 'tatemonomenseki', $data['建物面積1']);
						}
						$data['建物面積1'] = '';

				}else{
					if( $data['使用部分面積'] != ''){
     						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'tatemonomenseki','".$data['使用部分面積']."')";
						}else{
							update_post_meta($post_id, 'tatemonomenseki', $data['使用部分面積']);
						}
						$data['使用部分面積'] = '';
					}else{
						if( $data['専有面積'] != ''){
	     						if($addnew==true){
								$sql_txt .= ",(".$post_id.",'tatemonomenseki','".$data['専有面積']."')";
							}else{
								update_post_meta($post_id, 'tatemonomenseki', $data['専有面積']);
							}
							$data['専有面積'] = '';
						}else{

							if( $data['専有面積/使用部分面積'] != ''){
		     						if($addnew==true){
									$sql_txt .= ",(".$post_id.",'tatemonomenseki','".$data['専有面積/使用部分面積']."')";
								}else{
									update_post_meta($post_id, 'tatemonomenseki', $data['専有面積/使用部分面積']);
								}
								$data['専有面積/使用部分面積'] = '';
							}
						}
					}
				}




				//7種別	物件種別
				if($shubetsu_data != ''){

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'bukkenshubetsu','".$shubetsu_data."')";
					}else{
						update_post_meta($post_id, 'bukkenshubetsu', $shubetsu_data);
					}
					$shubetsu_data ='';
				}


				//借地
				if($k=="借地期間" && $v!=""){
					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'shakuchikubun','2')";
					}else{
						update_post_meta($post_id, 'shakuchikubun', '2');
					}
				
				}else{
					if($k=="借地期限(西暦)" && $v!=""){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'shakuchikubun','1')";
						}else{
							update_post_meta($post_id, 'shakuchikubun', '1');
						}
					}
				}


				// 203	報酬形態 分配率(客付分)
				if($k=="報酬形態" && $v!=""){

					if(intval($data['データ種類']) == 3){
						switch ($v) {
							case "1";
								$houshoukeitai_data = '分かれ'; break;
							case "2";
								$houshoukeitai_data = '当方不払'; break;
							case "3";
								$houshoukeitai_data = '当方全額'; break;
							case "4";
								$houshoukeitai_data = '当方半額'; break;
							case "5";
								$houshoukeitai_data = '貸主折半'; break;
							case "6";
								$houshoukeitai_data = '借主折半'; break;
							case "9";
								$houshoukeitai_data = '相談'; break;
						}
					}

					if(intval($data['データ種類']) == 1){
						switch ($v) {
							case "1";
								$houshoukeitai_data = '分かれ'; break;
							case "2";
								$houshoukeitai_data = '当方不払'; break;
							case "4";
								$houshoukeitai_data = '当方片手'; break;
							case "5";
								$houshoukeitai_data = '代理折半'; break;
							case "9";
								$houshoukeitai_data = '相談'; break;
						}
					}



					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'houshoukeitai','".$houshoukeitai_data."')";
					}else{
						update_post_meta($post_id, 'houshoukeitai',$houshoukeitai_data);
					}

				}



				// 89	間取り	間取部屋種類 10:R 20:K 25:SK 30:DK 35:SDK 40:LK 45:SLK 50:LDK 55:SLDK
				if($k=="間取タイプ(1)" && $v!=""){
					$madorisyurui = intval($v);

					$madorisyurui_data = '';
					
					switch ($madorisyurui) {
						case 1:		// R
							$madorisyurui_data = '10'; break;
						case 2:		// K
							$madorisyurui_data = '20'; break;
						case 3: 	// DK
							$madorisyurui_data = '30'; break;
						case 4: 	// LK
							$madorisyurui_data = '40'; break;
						case 5: 	// LDK
							$madorisyurui_data = '50'; break;
						case 6:		// SK
							$madorisyurui_data = '25'; break;
						case 7:		// SDK
							$madorisyurui_data = '35'; break;
						case 8:		// SLK
							$madorisyurui_data = '45'; break;
						case 9:		// SLDK
							$madorisyurui_data = '55'; break;
					}

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'madorisyurui','".$madorisyurui_data."')";
					}else{
						update_post_meta($post_id, 'madorisyurui',$madorisyurui_data);
					}
				}



				//所在地
				if($k=="都道府県名" && $v!=""){
					$shozaichiken_data = $v;
					$shozaichiken_code = fudo_ken_id($shozaichiken_data);

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'shozaichiken','".$shozaichiken_code."')";
					}else{
						update_post_meta($post_id, 'shozaichiken', $shozaichiken_code);
					}
				}


				if($k=="所在地名1" && $v!="" && $shozaichiken_code !=''){

						$sql = "SELECT narrow_area_id FROM ".$wpdb->prefix."area_narrow_area WHERE middle_area_id=".$shozaichiken_code." AND narrow_area_name = '".$v."'";
					//	$sql = $wpdb->prepare($sql,'');
						$metas = $wpdb->get_row( $sql );
						$shozaichi_code = $metas->narrow_area_id;

						$shozaichi = $shozaichiken_code.$shozaichi_code."000000" ;


						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'shozaichicode','".$shozaichi."')";
						}else{
							update_post_meta($post_id, 'shozaichicode', $shozaichi);
						}
						$shozaichi ="";
				}



				if($k=="沿線略称(1)" && $v!=""){
					foreach($work_h_rains_rosen as $meta_box){
						if($meta_box['name'] == $v){
							$koutsurosen1 = $meta_box['code'];

							if($addnew==true){
								$sql_txt .= ",(".$post_id.",'koutsurosen1','".$koutsurosen1."')";
							}else{
								update_post_meta($post_id, 'koutsurosen1', $koutsurosen1);
							}
						}
					}
				}

				if($k=="駅名(1)" && $v !="" && $koutsurosen1 !="" ){
					$v_sub = '';
					$v_sub2 = '';
					$findme = 'ヶ';
					$findme2 = 'ケ';

					$pos = strpos($v, $findme);
					if ($pos === false) {
					} else {
						$v_sub = str_replace( $findme ,$findme2 , $v);
					}
					$pos = strpos($v, $findme2);
					if ($pos === false) {
					} else {
						$v_sub2 = str_replace( $findme2 ,$findme , $v);
					}

					$sql = "SELECT DTS.station_id";
					$sql = $sql . " FROM ".$wpdb->prefix."train_station AS DTS";
					$sql = $sql . " WHERE DTS.rosen_id=".$koutsurosen1." AND DTS.middle_area_id=".$shozaichiken_code."";
					$sql = $sql . " AND ( DTS.station_name='".$v."'";
					if($v_sub != '')
						$sql = $sql . " OR DTS.station_name='".$v_sub."' ";
					if($v_sub2 != '')
						$sql = $sql . " OR DTS.station_name='".$v_sub2."' ";
					$sql = $sql . " )";

				//	$sql = $wpdb->prepare($sql,'');
					$metas = $wpdb->get_row( $sql );
					$meta = $metas->station_id;

					if($meta!=''){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'koutsueki1','".$meta."')";
						}else{
							update_post_meta($post_id, 'koutsueki1', $meta);
						}
					}
					$shozaichiken_code = '';
				}



				if($k=="建築条件" && $v!=""){
					switch ($v) {
						case '有':
							$setsubi_data = '99900/11001'; break;
						case '無':
							$setsubi_data = '99900/11002'; break;
					}

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'setsubi','".$setsubi_data."')";
					}else{
						update_post_meta($post_id, 'setsubi',$setsubi_data);
					}
				}


			}
			//東日本レインズ








			//近畿レインズ
			if($opt_csv == 'k_rains'){

				foreach($work_k_rains as $meta_box){

					if($k == $meta_box['h_name'] && $meta_box['d_name'] !=''){
						//自動採番
						if($k=="物件番号" && $v=="") $v=$post_id;
						if($k=="物件番号" && $v!="") $v=trim($v);

						if($k=="価格／賃料（万円）" && $v!="") $v=$v*10000;
						if($k=="価格／賃料" && $v!="") $v=$v*10000;
						if($k=="坪単価（万円）" && $v!="") $v=$v*10000;
						if($k=="坪単価" && $v!="") $v=$v*10000;
						if($k=="保証金（万円）" && $v!="") $v=$v*10000;
						if($k=="保証金" && $v!="") $v=$v*10000;
						if($k=="敷金（万円）" && $v!="") $v=$v*10000;
						if($k=="敷金" && $v!="") $v=$v*10000;
						if($k=="権利金（万円）" && $v!="") $v=$v*10000;
						if($k=="権利金" && $v!="") $v=$v*10000;
						if($k=="礼金（万円）" && $v!="") $v=$v*10000;
						if($k=="礼金" && $v!="") $v=$v*10000;
						if($k=="消費税額" && $v!="") $v=$v*10000;

					//	if($k=="築年月" && $v!="") $v=$this->parse_date2($v);
						if($k=="引渡年月" && $v!="") $v=$this->parse_date2($v);

						if($k=="取引態様" && $v!="") $v="6";



						if($k=="所在地１" && $v!=""){ 
							$v=$v.'000000';
							$shozaichicode = $v;

							if($addnew==true){
								$sql_txt .= ",(".$post_id.",'shozaichiken','".myLeft($shozaichicode,2)."')";
							}else{
								update_post_meta($post_id, 'shozaichiken', myLeft($shozaichicode,2));
							}
						}

						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'". $meta_box['d_name']."','".$v."')";
						}else{
							update_post_meta($post_id, $meta_box['d_name'], $v);
						}

					}
				}

				if($k=="沿線" && $v!=""){
					foreach($work_k_rains_rosen as $meta_box){
						if($meta_box['name'] == $v){
							$koutsurosen1 = $meta_box['code'];

							if($addnew==true){
								$sql_txt .= ",(".$post_id.",'koutsurosen1','".$koutsurosen1."')";
							}else{
								update_post_meta($post_id, 'koutsurosen1', $koutsurosen1);
							}


						}
					}
				}

				if($k=="駅" && $v !="" && $koutsurosen1 !="" ){

					$sql = "SELECT DTS.station_id";
					$sql = $sql . " FROM ".$wpdb->prefix."train_station AS DTS";
					$sql = $sql . " WHERE DTS.rosen_id=".$koutsurosen1." AND DTS.middle_area_id=".$shozaichiken_data."";
					$sql = $sql . " AND DTS.station_name='".$v."'";

				//	$sql = $wpdb->prepare($sql,'');
					$metas = $wpdb->get_row( $sql );
					$meta = $metas->station_id;

					if($meta!=''){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'koutsueki1','".$meta."')";
						}else{
							update_post_meta($post_id, 'koutsueki1', $meta);
						}
					}
				}


				//所在地
				if($k=="所在地" && $v!="") $shozaichi = $v;

				if($shozaichi != ""){
					
					if($shozaichicode !=''){

						$shozaichiken_data = myLeft($shozaichicode,2);
						$shozaichicode_data = myLeft($shozaichicode,5);
						$shozaichicode_data = myRight($shozaichicode_data,3);

						$sql = "SELECT narrow_area_name FROM ".$wpdb->prefix."area_narrow_area WHERE middle_area_id=".$shozaichiken_data." AND narrow_area_id=".$shozaichicode_data."";
					//	$sql = $wpdb->prepare($sql,'');
						$metas = $wpdb->get_row( $sql );
						$meta = $metas->narrow_area_name;

						$shozaichi =str_replace ("大阪府", "", $shozaichi) ;
						$shozaichi =str_replace ("兵庫県", "", $shozaichi) ;
						$shozaichi =str_replace ("京都府", "", $shozaichi) ;
						$shozaichi =str_replace ("和歌山県", "", $shozaichi) ;
						$shozaichi =str_replace ("奈良県", "", $shozaichi) ;
						$shozaichi =str_replace ("滋賀県", "", $shozaichi) ;

						$shozaichi =str_replace ("　", "", $shozaichi) ;
						$shozaichi =str_replace (" ", "", $shozaichi) ;

						$shozaichi =str_replace ($meta, "", $shozaichi) ;


						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'shozaichimeisho','".$shozaichi."')";
						}else{
							update_post_meta($post_id, 'shozaichimeisho', $shozaichi);
						}


						$shozaichi ="";
					}
				}




				//7種別	物件種別 ※
				if($k=="データ種類" && $v!="") $data_shu = $v;
				if($k=="物件種別" && $v!="") $bukken_shub = $v;
				if($k=="物件種目" && $v!="") $bukken_shum = $v;

				if($data_shu!="" && $bukken_shub!="" && $bukken_shum!=""){
					switch ($data_shu) {
						case '賃貸物件':

							switch ($bukken_shub) {
								case '居住':
									switch ($bukken_shum) {
										case 'マンション':
											$shubetsu_data = '3101'; $data_shu =""; break;
										case 'アパート':
											$shubetsu_data = '3102'; $data_shu =""; break;
										case '一戸建':
											$shubetsu_data = '3103'; $data_shu =""; break;
										case '貸家':
											$shubetsu_data = '3103'; $data_shu =""; break;
										case 'テラスハウス':
											$shubetsu_data = '3104'; $data_shu =""; break;
										case 'タウンハウス':
											$shubetsu_data = '3105'; $data_shu =""; break;
										case '間借り':
											$shubetsu_data = '3106'; $data_shu =""; break;
										case '寮・下宿':
											$shubetsu_data = '3110'; $data_shu =""; break;

										case 'コーポ':
											$shubetsu_data = '3122'; $data_shu =""; break;
										case 'ハイツ':
											$shubetsu_data = '3123'; $data_shu =""; break;
										case '文化住宅':
											$shubetsu_data = '3124'; $data_shu =""; break;
									}
									break;

								case '事業':
									switch ($bukken_shum) {
										case '店舗戸建':
											$shubetsu_data = '3201'; $data_shu =""; break;
										case '店舗一部':
											$shubetsu_data = '3202'; $data_shu =""; break;
										case '事務所':
											$shubetsu_data = '3203'; $data_shu =""; break;
										case '店舗・事務':
											$shubetsu_data = '3204'; $data_shu =""; break;
										case '工場':
											$shubetsu_data = '3205'; $data_shu =""; break;
										case '倉庫':
											$shubetsu_data = '3206'; $data_shu =""; break;
										case '一戸建':
											$shubetsu_data = '3207'; $data_shu =""; break;
										case '貸家':
											$shubetsu_data = '3207'; $data_shu =""; break;
										case 'マンション':
											$shubetsu_data = '3208'; $data_shu =""; break;
										case '旅館':
											$shubetsu_data = '3209'; $data_shu =""; break;
										case '寮':
											$shubetsu_data = '3210'; $data_shu =""; break;
										case '別荘':
											$shubetsu_data = '3211'; $data_shu =""; break;
										case '土地':
											$shubetsu_data = '3212'; $data_shu =""; break;
										case 'ビル':
											$shubetsu_data = '3213'; $data_shu =""; break;
										case '住店舗戸建':
											$shubetsu_data = '3214'; $data_shu =""; break;
										case '住店舗一部':
											$shubetsu_data = '3215'; $data_shu =""; break;
										case '駐車場':
											$shubetsu_data = '3282'; $data_shu =""; break;
										case 'その他':
											$shubetsu_data = '3299'; $data_shu =""; break;
									}
									break;

							}
							break;

						case '売物件':
							switch ($bukken_shub) {
								case '土地':
									switch ($bukken_shum) {
										case '売地':
											$shubetsu_data = '1101'; $data_shu =""; break;
										case '借地権':
											$shubetsu_data = '1102'; $data_shu =""; break;
										case '底地権':
											$shubetsu_data = '1103'; $data_shu =""; break;

										case '建付土地':
											$shubetsu_data = '1104'; $data_shu =""; break;
									}
									break;

								case '戸建':
									switch ($bukken_shum) {
										case '新築戸建':
											$shubetsu_data = '1201'; $data_shu =""; break;
										case '中古戸建':
											$shubetsu_data = '1202'; $data_shu =""; break;
										case '新築テラス':
											$shubetsu_data = '1203'; $data_shu =""; break;
										case '中古テラス':
											$shubetsu_data = '1204'; $data_shu =""; break;
									}
									break;

								case 'マン':
									switch ($bukken_shum) {
										case '新築マン':
											$shubetsu_data = '1301'; $data_shu =""; break;
										case '中古マン':
											$shubetsu_data = '1302'; $data_shu =""; break;
										case '新築公団':
											$shubetsu_data = '1303'; $data_shu =""; break;
										case '中古公団':
											$shubetsu_data = '1304'; $data_shu =""; break;
										case '新築公社':
											$shubetsu_data = '1305'; $data_shu =""; break;
										case '中古公社':
											$shubetsu_data = '1306'; $data_shu =""; break;
										case '新築タウン':
											$shubetsu_data = '1307'; $data_shu =""; break;
										case '中古タウン':
											$shubetsu_data = '1308'; $data_shu =""; break;
										case 'リゾートマン':
											$shubetsu_data = '1309'; $data_shu =""; break;
									}
									break;

								case '外全':
									switch ($bukken_shum) {
										case '店舗':
											$shubetsu_data = '1401'; $data_shu =""; break;
										case '店付住宅':
											$shubetsu_data = '1403'; $data_shu =""; break;
										case '住付店舗':
											$shubetsu_data = '1404'; $data_shu =""; break;
										case '事務所':
											$shubetsu_data = '1405'; $data_shu =""; break;
										case '店舗事務':
											$shubetsu_data = '1406'; $data_shu =""; break;
										case '店舗・事務':
											$shubetsu_data = '1406'; $data_shu =""; break;
										case 'ビル':
											$shubetsu_data = '1407'; $data_shu =""; break;
										case '工場':
											$shubetsu_data = '1408'; $data_shu =""; break;
										case 'マンション':
											$shubetsu_data = '1409'; $data_shu =""; break;
										case '倉庫':
											$shubetsu_data = '1410'; $data_shu =""; break;
										case 'アパート':
											$shubetsu_data = '1411'; $data_shu =""; break;
										case '寮':
											$shubetsu_data = '1412'; $data_shu =""; break;
										case '旅館':
											$shubetsu_data = '1413'; $data_shu =""; break;
										case 'ホテル':
											$shubetsu_data = '1414'; $data_shu =""; break;
										case '別荘':
											$shubetsu_data = '1415'; $data_shu =""; break;
										case 'リゾートマン':
											$shubetsu_data = '1416'; $data_shu =""; break;
										case '社宅':
											$shubetsu_data = '1420'; $data_shu =""; break;
										case 'その他':
											$shubetsu_data = '1499'; $data_shu =""; break;
									}
									break;

								case '外一':

									//売住宅以外の建物一部
									switch ($bukken_shum) {
										case '店舗':
											$shubetsu_data = '1502'; $data_shu =""; break;
										case '事務所':
											$shubetsu_data = '1505'; $data_shu =""; break;
										case '店舗事務':
											$shubetsu_data = '1506'; $data_shu =""; break;
										case '店舗・事務':
											$shubetsu_data = '1506'; $data_shu =""; break;
										case 'ビル':
											$shubetsu_data = '1507'; $data_shu =""; break;
										case 'マンション':
											$shubetsu_data = '1509'; $data_shu =""; break;
										case 'その他':
											$shubetsu_data = '1599'; $data_shu =""; break;
									}
									break;
							}
							break;
					}


					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'bukkenshubetsu','".$shubetsu_data."')";
					}else{
						update_post_meta($post_id, 'bukkenshubetsu', $shubetsu_data);
					}

				}


				// 40	私道負担面積	tochishido
				// 86	バルコニー面積	heyabarukoni
				if(( $k=="私道面積／バルコニー面積（平米）" ||$k=="私道面積／バルコニー面積") && $v!=""){
					if($bukken_shub == '売地' || $bukken_shub == '借地権' || $bukken_shub == '底地権' || $bukken_shub == '建付土地' ){
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'tochishido','".$v."')";
						}else{
							update_post_meta($post_id, 'tochishido', $v);
						}

					}else{
						if($addnew==true){
							$sql_txt .= ",(".$post_id.",'heyabarukoni','".$v."')";
						}else{
							update_post_meta($post_id, 'heyabarukoni', $v);
						}
					}
				}


				// 89	間取り	間取部屋種類 10:R 20:K 25:SK 30:DK 35:SDK 40:LK 45:SLK 50:LDK 55:SLDK
				if($k=="間取り" && $v!=""){

					$v1 = myLeft($v,1);
					$pos = substr($v,1,20);

					switch ($pos) {
						case 'ワンルーム':
							$madorisyurui_data = '10'; break;
						case 'Ｋ':
						case 'K':
							$madorisyurui_data = '20'; break;
						case 'ＳＫ':
						case 'SK':
							$madorisyurui_data = '25'; break;
						case 'ＤＫ':
						case 'DK':
							$madorisyurui_data = '30'; break;
						case 'ＳＤＫ':
						case 'SDK':
							$madorisyurui_data = '35'; break;
						case 'ＬＫ':
						case 'LK':
							$madorisyurui_data = '40'; break;
						case 'ＳＬＫ':
						case 'SLK':
							$madorisyurui_data = '45'; break;
						case 'LDK':
						case 'ＬＤＫ':
							$madorisyurui_data = '50'; break;
						case 'SLDK':
						case 'ＳＬＤＫ':
							$madorisyurui_data = '55'; break;
					}

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'madorisu','".$v1."')";
						$sql_txt .= ",(".$post_id.",'madorisyurui','".$madorisyurui_data."')";
					}else{
						update_post_meta($post_id, 'madorisu', $v1);
						update_post_meta($post_id, 'madorisyurui',$madorisyurui_data);
					}

				}


				if($k=="建築条件／建物賃貸借区分" && $v!=""){
					switch ($v) {
						case '有':
							$setsubi_data = '99900/11001'; break;
						case '無':
							$setsubi_data = '99900/11002'; break;
					}

					if($addnew==true){
						$sql_txt .= ",(".$post_id.",'setsubi','".$setsubi_data."')";
					}else{
						update_post_meta($post_id, 'setsubi',$setsubi_data);
					}

				}


		        }
		        //k_rains

	        }
		//loop end

		if($addnew==true){
			$sql = "INSERT INTO $wpdb->postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ";
			$sql .= $sql_txt;
			$wpdb->query( $sql );
		}

	}


	//カスタムフィールドカテゴリ(OP)
	function create_custom_fields_cat($post_id, $data) {


		//種別カテゴリ親ID取得
		$term_rent = get_term_by('slug', 'rent', 'bukken');
			//なければ作成
			if(!$term_rent){
				$term = wp_insert_term('賃貸地域','bukken', array('slug'=>'rent') );
				$term_rent = get_term_by('slug', 'rent', 'bukken');
			}
		$term_rent_ID = $term_rent->term_id;


		//種別カテゴリ親ID取得
		$term_sale = get_term_by('slug', 'sale', 'bukken');
			//なければ作成
			if(!$term_sale){
				$term = wp_insert_term('売買地域','bukken', array('slug'=>'sale') );
				$term_sale = get_term_by('slug', 'sale', 'bukken');
			}
		$term_sale_ID = $term_sale->term_id;


		//種別カテゴリ親ID取得
		$term_rent_r = get_term_by('slug', 'rent_r', 'bukken');
			//なければ作成
			if(!$term_rent_r){
				$term = wp_insert_term('賃貸路線','bukken', array('slug'=>'rent_r') );
				$term_rent_r = get_term_by('slug', 'rent_r', 'bukken');
			}
		$term_rent_r_ID = $term_rent_r->term_id;


		//種別カテゴリ親ID取得
		$term_sale_r = get_term_by('slug', 'sale_r', 'bukken');
			//なければ作成
			if(!$term_sale_r){
				$term = wp_insert_term('売買路線','bukken', array('slug'=>'sale_r') );
				$term_sale_r = get_term_by('slug', 'sale_r', 'bukken');
			}
		$term_sale_r_ID = $term_sale_r->term_id;


	        foreach ($data as $k => $v) {

			if (!preg_match('/^csv_/', $k) && $v != '') {
	                
				//地域カテゴリ自動登録 
				if('shozaichicode' == $k ){
					$this->create_custom_fields_cat_chiiki($post_id, $k, $v,$term_rent_ID,$term_sale_ID);
				}
				//路線カテゴリ自動登録
				if('koutsurosen1' == $k ){
					$this->create_custom_fields_cat_rosen($post_id, $k, $v,$term_rent_r_ID,$term_sale_r_ID);
				}

				//駅カテゴリ自動登録
				if('koutsueki1' == $k ){
					$this->create_custom_fields_cat_eki($post_id, $k, $v,$term_rent_r_ID,$term_sale_r_ID);
				}

			}
	        }

	}


	//カスタムフィールド地域カテゴリ自動登録
	function create_custom_fields_cat_chiiki($post_id, $k, $v,$term_rent_ID,$term_sale_ID) {
		global $wpdb;

		//種別カテゴリ自動登録 
		$bukkenshubetsu_data = get_post_meta($post_id,'bukkenshubetsu',true);
		
		if($bukkenshubetsu_data !=""){
			if($bukkenshubetsu_data>3000){
				wp_set_post_terms($post_id,intval($term_rent_ID),'bukken',true);
				$term_bukkenshubetsu_ID = $term_rent_ID;
			}else{
				wp_set_post_terms($post_id,intval($term_sale_ID),'bukken',true);
				$term_bukkenshubetsu_ID = $term_sale_ID;
			}
		}

		if($term_bukkenshubetsu_ID =="")
			$term_bukkenshubetsu_ID = 0;



		//地域カテゴリ自動登録 
		$shozaichiken_data = myLeft($v,2);

		/* 県カテゴリ登録 */
		$tmp_slug_tag = $shozaichiken_data;
		$term_ken = get_term_by('slug', $tmp_slug_tag, 'bukken');

			//なければ作成
			if(!$term_ken){
				$sql = "SELECT `middle_area_name` FROM `".$wpdb->prefix."area_middle_area` WHERE `middle_area_id`=".$tmp_slug_tag."";
			//	$sql = $wpdb->prepare($sql,'');
				$metas = $wpdb->get_row( $sql );
				$meta = $metas->middle_area_name;

				$term = wp_insert_term($meta,'bukken', array('slug'=>$tmp_slug_tag,'parent'=>$term_bukkenshubetsu_ID) );
			}

		//親ID取得
		$term_ken = get_term_by('slug', $tmp_slug_tag, 'bukken');
		$term_ken_ID = $term_ken->term_id;

		wp_set_post_terms($post_id,intval($term_ken_ID),'bukken',true);
		/* 県カテゴリ登録 */


		$shozaichicode_data = myLeft($v,5);
		$shozaichicode_data = myRight($shozaichicode_data,3);


		/* 市区カテゴリ登録 */
		$tmp_slug_tag = $shozaichiken_data.$shozaichicode_data;
		$term = get_term_by('slug', $tmp_slug_tag, 'bukken');

			//なければ作成
			if(!$term){

				$sql = "SELECT narrow_area_name FROM ".$wpdb->prefix."area_narrow_area WHERE `narrow_area_id` =".$shozaichicode_data." and `middle_area_id` =".$shozaichiken_data."";
			//	$sql = $wpdb->prepare($sql,'');
				$metas = $wpdb->get_row( $sql );
				$meta = $metas->narrow_area_name;

				//県カテゴリID取得
				$term_ken = get_term_by('slug', $shozaichiken_data, 'bukken');
				$term_ken_ID = $term_ken->term_id;

				$term = wp_insert_term($meta,'bukken', array('slug'=>$tmp_slug_tag,'parent'=>$term_ken_ID));

			}

		$term = get_term_by('slug', $tmp_slug_tag, 'bukken');
		wp_set_post_terms($post_id,intval($term->term_id),'bukken',true);
		/* 市区カテゴリ登録 */


	}



	//カスタムフィールド路線カテゴリ自動登録
	function create_custom_fields_cat_rosen($post_id, $k, $v,$term_rent_ID,$term_sale_ID) {
		global $wpdb;

		//種別カテゴリ自動登録 
		$bukkenshubetsu_data = get_post_meta($post_id,'bukkenshubetsu',true);
		
		if($bukkenshubetsu_data !=""){
			if($bukkenshubetsu_data>3000){
				wp_set_post_terms($post_id,intval($term_rent_ID),'bukken',true);
				$term_bukkenshubetsu_ID = $term_rent_ID;
			}else{
				wp_set_post_terms($post_id,intval($term_sale_ID),'bukken',true);
				$term_bukkenshubetsu_ID = $term_sale_ID;
			}
		}

		if($term_bukkenshubetsu_ID =="")
			$term_bukkenshubetsu_ID = 0;


		//路線カテゴリ自動登録 

			$tmp_slug_tag = $v;
			$term_rosen = get_term_by('slug', $v, 'bukken');

				//なければ作成
				if(!$term_rosen){
					$sql = "SELECT `rosen_id`,`rosen_name` FROM `".$wpdb->prefix."train_rosen` WHERE `rosen_id` =".$tmp_slug_tag."";
				//	$sql = $wpdb->prepare($sql,'');
					$metas = $wpdb->get_row( $sql );
					$meta = $metas->rosen_name;

					$term = wp_insert_term($meta,'bukken', array('slug'=>$tmp_slug_tag,'parent'=>$term_bukkenshubetsu_ID) );
				}

			//親ID取得
			$term_rosen = get_term_by('slug', $tmp_slug_tag, 'bukken');
			$term_rosen_ID = $term_rosen->term_id;

			wp_set_post_terms($post_id,intval($term_rosen_ID),'bukken',true);
		//路線カテゴリ自動登録 

	}

	//カスタムフィールド駅カテゴリ自動登録
	function create_custom_fields_cat_eki($post_id, $k, $v,$term_rent_ID,$term_sale_ID) {
		global $wpdb;

		//親路線カテゴリ 
		$koutsurosen1_data = get_post_meta($post_id,'koutsurosen1',true);

		$term_rosen = get_term_by('slug', $koutsurosen1_data, 'bukken');
		$term_rosen_ID = $term_rosen->term_id;



		//駅カテゴリ自動登録 

			$tmp_slug_tag = $v;
			$term_eki = get_term_by('slug', $v, 'bukken');

				//なければ作成
				if(!$term_eki){
					$sql = "SELECT `station_name` FROM `".$wpdb->prefix."train_station` WHERE `rosen_id`=".$koutsurosen1_data." and `station_id`=".$tmp_slug_tag."";
				//	$sql = $wpdb->prepare($sql,'');
					$metas = $wpdb->get_row( $sql );
					$meta = $metas->station_name;

					$term = wp_insert_term($meta,'bukken', array('slug'=>$tmp_slug_tag,'parent'=>$term_rosen_ID) );
				}

			//ID取得
			$term_eki = get_term_by('slug', $tmp_slug_tag, 'bukken');
			$term_eki_ID = $term_eki->term_id;

			wp_set_post_terms($post_id,intval($term_eki_ID),'bukken',true);
		//駅カテゴリ自動登録 

	}


	//投稿者のユーザID
	function get_auth_id($author) {
	        if (is_numeric($author)) {
	            return $author;
	        }
	        $author_data = get_userdatabylogin($author);
	        return ($author_data) ? $author_data->ID : 0;
	} 



	// Convert date in CSV file to 2010/11 format
	function parse_date3($data) {
		$data = str_replace ('年', '/', $data);
		$data = str_replace ('月', '', $data);
	        return $data;
	}



	// Convert date in CSV file to 2010/11 format
	function parse_date2($data) {
	    $timestamp = strtotime($data);
	    if (false === $timestamp) {
	        return '';
	    } else {
	        return date('Y/m', $timestamp);
	    }
	}


	// Convert date in CSV file to 1999-12-31 23:52:00 format
	function parse_date($data) {
		$data = str_replace ('.', '/', $data);
	    $timestamp = strtotime($data);
	    if (false === $timestamp) {
		$timestamp = '00:00:00';
	        return date('Y-m-d H:i:s', $timestamp);
	    } else {
	        return date('Y-m-d H:i:s', $timestamp);
	    }
	}


	function str_cut_l($str,$options) {
		$pos = mb_strpos($str , $options);
		if($pos !== false )
		$str= myLeft($str,$pos);
	        return $str;
	}

	function str_cut_r($str,$options) {
		$all = mb_strlen( $str, 'utf-8');
		$pos = mb_strpos($str , $options);
		if($pos !== false )
		$str= myRight($str,$all - $pos -1 );
	        return $str;
	}

	//$options offset
	function str_cut_map_fun($str,$options) {
		$all = mb_strlen( $str, 'utf-8');
		$str = myRight($str,$all - $options );
		$str = $this->str_cut_l($str , '.' );
	        return $str;
	}

/*
	// Left関数//左からn文字取得して返す 
	if (!function_exists('myLeft')) {
	function myLeft($str,$n){
		return mb_substr($str,0,(mb_strlen($str)-$n)*-1);
	}
	}

	// Right関数//右からn文字取得して返す 
	if (!function_exists('myRight')) {
	function myRight($str,$n){
		return mb_substr($str,($n)*-1);
	}
	}

*/



	// delete BOM from UTF-8 file
	function stripBOM($fname) {
	    $res = fopen($fname, 'rb');
	    if (false !== $res) {
	        $bytes = fread($res, 3);
	        if ($bytes == pack('CCC', 0xef, 0xbb, 0xbf)) {
	            $this->log['notice'][] = 'Getting rid of byte order mark...';
	            fclose($res);

	            $contents = file_get_contents($fname);
	            if (false === $contents) {
	                trigger_error('Failed to get file contents.', E_USER_WARNING);
	            }
	            $contents = substr($contents, 3);
	            $success = file_put_contents($fname, $contents);
	            if (false === $success) {
	                trigger_error('Failed to put file contents.', E_USER_WARNING);
	            }
	        } else {
	            fclose($res);
	        }
	    } else {
	        $this->log['error'][] = 'Failed to open file, aborting.';
	    }
	}

}	//class CSVImporterPlugin_fudoi



	//homes csv format
	$work_homes =
	array(
		"1" => array("d_name" => "shikibesu",		"h_name" => "自社管理物件番号"),
		"2" => array("d_name" => "",			"h_name" => "自社管理修正日時"),
		"3" => array("d_name" => "keisaikigenbi",	"h_name" => "情報掲載期限日"),
	//	"4" => array("d_name" => "koukai",		"h_name" => "公開可否"),
		"5" => array("d_name" => "koukaijisha",		"h_name" => "自社物フラグ"),
		"6" => array("d_name" => "jyoutai",		"h_name" => "状態"),
		"7" => array("d_name" => "bukkenshubetsu",	"h_name" => "物件種別"),
	//	"8" => array("d_name" => "",			"h_name" => "一括入力フラグ"),
	//	"9" => array("d_name" => "",			"h_name" => "投資用物件"),
		"10" => array("d_name" => "bukkenmei",		"h_name" => "建物名(物件名)"),
	//	"11" => array("d_name" => "bukkenmei",		"h_name" => "建物名フリガナ(物件名フリガナ)"),
		"12" => array("d_name" => "bukkenmeikoukai",	"h_name" => "物件名公開"),
		"13" => array("d_name" => "bukkensoukosu",	"h_name" => "総戸数・総区画数"),
	//	"14" => array("d_name" => "",			"h_name" => "空き物件数"),
		"15" => array("d_name" => "bukkennaiyo",	"h_name" => "空き物件内容"),
	//	"16" => array("d_name" => "",			"h_name" => "郵便番号"),
		"17" => array("d_name" => "shozaichicode",	"h_name" => "所在地"),
		"18" => array("d_name" => "shozaichimeisho",	"h_name" => "所在地名称"),
		"19" => array("d_name" => "shozaichimeisho2",	"h_name" => "所在地詳細_表示部"),
		"20" => array("d_name" => "shozaichimeisho3",	"h_name" => "所在地詳細_非表示部"),
	//	"21" => array("d_name" => "",			"h_name" => "緯度/経度(未使用)"),
		"22" => array("d_name" => "koutsurosen1",	"h_name" => "路線1"),
		"23" => array("d_name" => "koutsueki1",		"h_name" => "駅1"),
		"24" => array("d_name" => "koutsubusstei1",	"h_name" => "バス停名1"),
		"25" => array("d_name" => "koutsubussfun1",	"h_name" => "バス時間1"),
		"26" => array("d_name" => "koutsutoho1",	"h_name" => "徒歩距離1"),
		"27" => array("d_name" => "koutsurosen2",	"h_name" => "路線2"),
		"28" => array("d_name" => "koutsueki2",		"h_name" => "駅2"),
		"29" => array("d_name" => "koutsubusstei2",	"h_name" => "バス停名2"),
		"30" => array("d_name" => "koutsubussfun2",	"h_name" => "バス時間2"),
		"31" => array("d_name" => "koutsutoho2",	"h_name" => "徒歩距離2"),
		"32" => array("d_name" => "koutsusonota",	"h_name" => "その他交通"),
	//	"33" => array("d_name" => "",			"h_name" => "車所要時間(未使用)"),
		"34" => array("d_name" => "tochichimoku",	"h_name" => "地目"),
		"35" => array("d_name" => "tochiyouto",		"h_name" => "用途地域"),
		"36" => array("d_name" => "tochikeikaku",	"h_name" => "都市計画"),
		"37" => array("d_name" => "tochichisei",	"h_name" => "地勢"),
		"38" => array("d_name" => "tochisokutei",	"h_name" => "土地面積計測方式"),
		"39" => array("d_name" => "tochikukaku",	"h_name" => "区画面積"),
		"40" => array("d_name" => "tochishido",		"h_name" => "私道負担面積"),
	//	"41" => array("d_name" => "",			"h_name" => "私道負担割合(分子/分母)"),
	//	"42" => array("d_name" => "",			"h_name" => "土地持分(分子/分母)"),
		"43" => array("d_name" => "tochisetback",	"h_name" => "セットバック"),
		"44" => array("d_name" => "tochisetback2",	"h_name" => "セットバック量"),
		"45" => array("d_name" => "tochikenpei",	"h_name" => "建ぺい率"),
		"46" => array("d_name" => "tochiyoseki",	"h_name" => "容積率"),
		"47" => array("d_name" => "tochisetsudo",	"h_name" => "接道状況"),
		"48" => array("d_name" => "tochisetsudohouko1",	"h_name" => "接道方向1"),
		"49" => array("d_name" => "tochisetsudomaguchi1",	"h_name" => "接道間口1"),
		"50" => array("d_name" => "tochisetsudoshurui1",	"h_name" => "接道種別1"),
		"51" => array("d_name" => "tochisetsudofukuin1",	"h_name" => "接道幅員1"),
		"52" => array("d_name" => "tochisetsudoichishitei1",	"h_name" => "位置指定道路1"),
		"53" => array("d_name" => "tochisetsudohouko2",		"h_name" => "接道方向2"),
		"54" => array("d_name" => "tochisetsudomaguchi2",	"h_name" => "接道間口2"),
		"55" => array("d_name" => "tochisetsudoshurui2",	"h_name" => "接道種別2"),
		"56" => array("d_name" => "tochisetsudofukuin2",	"h_name" => "接道幅員2"),
		"57" => array("d_name" => "tochisetsudoichishitei2",	"h_name" => "位置指定道路2"),
	//	"58" => array("d_name" => "",				"h_name" => "接道方向3"),
	//	"59" => array("d_name" => "",				"h_name" => "接道間口3"),
	//	"60" => array("d_name" => "",				"h_name" => "接道種別3"),
	//	"61" => array("d_name" => "",				"h_name" => "接道幅員3"),
	//	"62" => array("d_name" => "",				"h_name" => "位置指定道路3"),
	//	"63" => array("d_name" => "",				"h_name" => "接道方向4"),
	//	"64" => array("d_name" => "",				"h_name" => "接道間口4"),
	//	"65" => array("d_name" => "",				"h_name" => "接道種別4"),
	//	"66" => array("d_name" => "",				"h_name" => "接道幅員4"),
	//	"67" => array("d_name" => "",				"h_name" => "位置指定道路4"),
		"68" => array("d_name" => "tochikenri",			"h_name" => "土地権利(借地権種類)"),
		"69" => array("d_name" => "tochikokudohou",		"h_name" => "国土法届出"),
	//	"70" => array("d_name" => "",				"h_name" => "法令上の制限"),
		"71" => array("d_name" => "tatemonokozo",		"h_name" => "建物構造"),
		"72" => array("d_name" => "tatemonohosiki",		"h_name" => "建物面積計測方式"),
		"73" => array("d_name" => "tatemonomenseki",		"h_name" => "建物面積・専有面積"),
		"74" => array("d_name" => "tatemonozentaimenseki",	"h_name" => "敷地全体面積"),
		"75" => array("d_name" => "tatemononobeyukamenseki",	"h_name" => "延べ床面積"),
		"76" => array("d_name" => "tatemonokentikumenseki",	"h_name" => "建築面積"),
		"77" => array("d_name" => "tatemonokaisu1",		"h_name" => "建物階数(地上)"),
		"78" => array("d_name" => "tatemonokaisu2",		"h_name" => "建物階数(地下)"),
		"79" => array("d_name" => "tatemonochikunenn",		"h_name" => "築年月"),
		"80" => array("d_name" => "tatemonoshinchiku",		"h_name" => "新築・未入居フラグ"),
		"81" => array("d_name" => "kanrininn",					"h_name" => "管理人"),
		"82" => array("d_name" => "kanrikeitai",				"h_name" => "管理形態"),
		"83" => array("d_name" => "kanrikumiai",				"h_name" => "管理組合有無"),
		"84" => array("d_name" => "kanrikaisha",				"h_name" => "管理会社名"),
		"85" => array("d_name" => "heyakaisu",			"h_name" => "部屋階数"),
		"86" => array("d_name" => "heyabarukoni",		"h_name" => "バルコニー面積"),
		"87" => array("d_name" => "heyamuki",			"h_name" => "向き"),
		"88" => array("d_name" => "madorisu",			"h_name" => "間取部屋数"),
		"89" => array("d_name" => "madorisyurui",		"h_name" => "間取部屋種類"),
		"90" => array("d_name" => "madorisyurui1",		"h_name" => "間取(種類)1"),
		"91" => array("d_name" => "madorijyousu1",		"h_name" => "間取(畳数)1"),
		"92" => array("d_name" => "madorikai1",			"h_name" => "間取(所在階)1"),
		"93" => array("d_name" => "madorishitsu1",		"h_name" => "間取(室数)1"),
		"94" => array("d_name" => "madorisyurui2",		"h_name" => "間取(種類)2"),
		"95" => array("d_name" => "madorijyousu2",		"h_name" => "間取(畳数)2"),
		"96" => array("d_name" => "madorikai2",			"h_name" => "間取(所在階)2"),
		"97" => array("d_name" => "madorishitsu2",		"h_name" => "間取(室数)2"),
		"98" => array("d_name" => "madorisyurui3",		"h_name" => "間取(種類)3"),
		"99" => array("d_name" => "madorijyousu3",		"h_name" => "間取(畳数)3"),
		"100" => array("d_name" => "madorikai3",		"h_name" => "間取(所在階)3"),
		"101" => array("d_name" => "madorishitsu3",		"h_name" => "間取(室数)3"),
		"102" => array("d_name" => "madorisyurui4",		"h_name" => "間取(種類)4"),
		"103" => array("d_name" => "madorijyousu4",		"h_name" => "間取(畳数)4"),
		"104" => array("d_name" => "madorikai4",		"h_name" => "間取(所在階)4"),
		"105" => array("d_name" => "madorishitsu4",		"h_name" => "間取(室数)4"),
		"106" => array("d_name" => "madorisyurui5",		"h_name" => "間取(種類)5"),
		"107" => array("d_name" => "madorijyousu5",		"h_name" => "間取(畳数)5"),
		"108" => array("d_name" => "madorikai5",		"h_name" => "間取(所在階)5"),
		"109" => array("d_name" => "madorishitsu5",		"h_name" => "間取(室数)5"),
		"110" => array("d_name" => "madorisyurui6",		"h_name" => "間取(種類)6"),
		"111" => array("d_name" => "madorijyousu6",		"h_name" => "間取(畳数)6"),
		"112" => array("d_name" => "madorikai6",		"h_name" => "間取(所在階)6"),
		"113" => array("d_name" => "madorishitsu6",		"h_name" => "間取(室数)6"),
		"114" => array("d_name" => "madorisyurui7",		"h_name" => "間取(種類)7"),
		"115" => array("d_name" => "madorijyousu7",		"h_name" => "間取(畳数)7"),
		"116" => array("d_name" => "madorikai7",		"h_name" => "間取(所在階)7"),
		"117" => array("d_name" => "madorishitsu7",		"h_name" => "間取(室数)7"),
		"118" => array("d_name" => "madorisyurui8",		"h_name" => "間取(種類)8"),
		"119" => array("d_name" => "madorijyousu8",		"h_name" => "間取(畳数)8"),
		"120" => array("d_name" => "madorikai8",		"h_name" => "間取(所在階)8"),
		"121" => array("d_name" => "madorishitsu8",		"h_name" => "間取(室数)8"),
		"122" => array("d_name" => "madorisyurui9",		"h_name" => "間取(種類)9"),
		"123" => array("d_name" => "madorijyousu9",		"h_name" => "間取(畳数)9"),
		"124" => array("d_name" => "madorikai9",		"h_name" => "間取(所在階)9"),
		"125" => array("d_name" => "madorishitsu9",		"h_name" => "間取(室数)9"),
		"126" => array("d_name" => "madorisyurui10",		"h_name" => "間取(種類)10"),
		"127" => array("d_name" => "madorijyousu10",		"h_name" => "間取(畳数)10"),
		"128" => array("d_name" => "madorikai10",		"h_name" => "間取(所在階)10"),
		"129" => array("d_name" => "madorishitsu10",		"h_name" => "間取(室数)10"),
		"130" => array("d_name" => "madoribiko",		"h_name" => "間取り備考"),
	//	"131" => array("d_name" => "",				"h_name" => "物件の特徴"),
	//	"132" => array("d_name" => "",				"h_name" => "物件の特徴_A"),
	//	"133" => array("d_name" => "",				"h_name" => "物件の特徴_B"),
	//	"134" => array("d_name" => "",				"h_name" => "備考"),
	//	"135" => array("d_name" => "",				"h_name" => "備考OEM_A"),
	//	"136" => array("d_name" => "",				"h_name" => "備考OEM_B"),
	//	"137" => array("d_name" => "",				"h_name" => "URL"),
		"138" => array("d_name" => "shanaimemo",		"h_name" => "社内用メモ"),
		"139" => array("d_name" => "kakaku",			"h_name" => "賃料・価格"),
	//	"140" => array("d_name" => "kakakukoukai",		"h_name" => "価格公開フラグ"),
		"141" => array("d_name" => "kakakujoutai",		"h_name" => "価格状態"),
		"142" => array("d_name" => "",				"h_name" => "税金"),
		"143" => array("d_name" => "kakakuzei",			"h_name" => "税額"),
		"144" => array("d_name" => "kakakutsubo",		"h_name" => "坪単価"),
		"145" => array("d_name" => "kakakukyouekihi",		"h_name" => "共益費・管理費"),
	//	"146" => array("d_name" => "",				"h_name" => "共益費・管理費 税"),
		"147" => array("d_name" => "kakakureikin",		"h_name" => "礼金・月数"),
	//	"148" => array("d_name" => "",				"h_name" => "礼金 税"),
		"149" => array("d_name" => "kakakushikikin",		"h_name" => "敷金・月数"),
		"150" => array("d_name" => "kakakuhoshoukin",		"h_name" => "保証金・月数"),
		"151" => array("d_name" => "kakakukenrikin",		"h_name" => "権利金"),
	//	"152" => array("d_name" => "",				"h_name" => "権利金 税"),
	//	"153" => array("d_name" => "",				"h_name" => "造作譲渡金"),
	//	"154" => array("d_name" => "",				"h_name" => "造作譲渡金 税"),
		"155" => array("d_name" => "kakakushikibiki",		"h_name" => "償却・敷引金"),
	//	"156" => array("d_name" => "",				"h_name" => "償却時期"),
		"157" => array("d_name" => "kakakukoushin",		"h_name" => "更新料"),
		"158" => array("d_name" => "kakakuhyorimawari",		"h_name" => "満室時表面利回り"),
		"159" => array("d_name" => "kakakurimawari",		"h_name" => "現行利回り"),
		"160" => array("d_name" => "kakakuhoken",		"h_name" => "住宅保険料"),
		"161" => array("d_name" => "kakakuhokenkikan",		"h_name" => "住宅保険期間"),
		"162" => array("d_name" => "shakuchiryo",		"h_name" => "借地料"),
	//	"163" => array("d_name" => "",				"h_name" => "契約期間(年)"),
	//	"164" => array("d_name" => "",				"h_name" => "契約期間(月)"),
		"165" => array("d_name" => "shakuchikubun",		"h_name" => "契約期間(区分)"),
		"166" => array("d_name" => "kakakutsumitate",		"h_name" => "修繕積立金"),
	//	"167" => array("d_name" => "",				"h_name" => "修繕積立基金"),
	//	"168" => array("d_name" => "",				"h_name" => "その他費用名目1"),
	//	"169" => array("d_name" => "",				"h_name" => "その他費用1"),
	//	"170" => array("d_name" => "",				"h_name" => "その他費用名目2"),
	//	"171" => array("d_name" => "",				"h_name" => "その他費用2"),
	//	"172" => array("d_name" => "",				"h_name" => "その他費用名目3"),
	//	"173" => array("d_name" => "",				"h_name" => "その他費用3"),
	//	"174" => array("d_name" => "",				"h_name" => "成約価格"),
		"175" => array("d_name" => "seiyakubi",			"h_name" => "成約日"),
	//	"176" => array("d_name" => "",				"h_name" => "成約税金フラグ"),
	//	"177" => array("d_name" => "",				"h_name" => "成約税額"),
		"178" => array("d_name" => "chushajoryokin",		"h_name" => "駐車場料金"),
	//	"179" => array("d_name" => "",				"h_name" => "駐車場料金 税"),
		"180" => array("d_name" => "chushajokubun",		"h_name" => "駐車場区分"),
	//	"181" => array("d_name" => "",				"h_name" => "駐車場距離"),
	//	"182" => array("d_name" => "",				"h_name" => "駐車場空き台数"),
		"183" => array("d_name" => "chushajobiko",		"h_name" => "駐車場備考"),
		"184" => array("d_name" => "nyukyogenkyo",		"h_name" => "現況"),
		"185" => array("d_name" => "nyukyojiki",		"h_name" => "引渡/入居時期"),
		"186" => array("d_name" => "nyukyonengetsu",		"h_name" => "引渡/入居年月"),
		"187" => array("d_name" => "nyukyosyun",		"h_name" => "引渡/入居旬"),
		"188" => array("d_name" => "shuuhenshougaku",		"h_name" => "小学校名"),
	//	"189" => array("d_name" => "",				"h_name" => "小学校距離"),
	//	"190" => array("d_name" => "",				"h_name" => "小学校 学区コード(未使用)"),
		"191" => array("d_name" => "shuuhenchuugaku",		"h_name" => "中学校名"),
	//	"192" => array("d_name" => "",				"h_name" => "中学校距離"),
	//	"193" => array("d_name" => "",				"h_name" => "中学校 学区コード(未使用)"),
	//	"194" => array("d_name" => "",				"h_name" => "コンビニ距離"),
	//	"195" => array("d_name" => "",				"h_name" => "スーパー距離"),
	//	"196" => array("d_name" => "",				"h_name" => "総合病院距離"),
	//	"197" => array("d_name" => "",				"h_name" => "物件担当者名"),
		"198" => array("d_name" => "torihikitaiyo",		"h_name" => "取引態様"),
	//	"199" => array("d_name" => "",				"h_name" => "掲載確認日"),
	//	"200" => array("d_name" => "",				"h_name" => "客付"),
	//	"201" => array("d_name" => "",				"h_name" => "媒介契約年月日"),
	//	"202" => array("d_name" => "",				"h_name" => "仲介手数料"),
		"203" => array("d_name" => "houshoukeitai",		"h_name" => "分配率(客付分)"),
	//	"204" => array("d_name" => "",				"h_name" => "手数料負担(借主)"),
	//	"205" => array("d_name" => "",				"h_name" => "客付け業者へのメッセージ"),
	//	"206" => array("d_name" => "",				"h_name" => "名称"),
	//	"207" => array("d_name" => "",				"h_name" => "郵便番号"),
	//	"208" => array("d_name" => "",				"h_name" => "所在地コード"),
	//	"209" => array("d_name" => "",				"h_name" => "所在地詳細"),
	//	"210" => array("d_name" => "",				"h_name" => "電話番号"),
	//	"211" => array("d_name" => "",				"h_name" => "FAX番号"),
	//	"212" => array("d_name" => "",				"h_name" => "担当者名"),
	//	"213" => array("d_name" => "",				"h_name" => "備考"),
		"214" => array("d_name" => "motozukemei",		"h_name" => "名称"),
	//	"215" => array("d_name" => "",				"h_name" => "郵便番号"),
	//	"216" => array("d_name" => "",				"h_name" => "所在地コード"),
	//	"217" => array("d_name" => "",				"h_name" => "所在地詳細"),
		"218" => array("d_name" => "motozuketel",		"h_name" => "電話番号"),
	//	"219" => array("d_name" => "",				"h_name" => "FAX番号"),
	//	"220" => array("d_name" => "",				"h_name" => "備考"),
	//	"221" => array("d_name" => "",				"h_name" => "開始日"),
	//	"222" => array("d_name" => "",				"h_name" => "終了日"),
	//	"223" => array("d_name" => "",				"h_name" => "実施時間"),
	//	"224" => array("d_name" => "",				"h_name" => "備考"),
		"225" => array("d_name" => "fudoimg1",			"h_name" => "ローカルファイル名1"),
	//	"226" => array("d_name" => "",				"h_name" => "ローカル修正日時1"),
		"227" => array("d_name" => "fudoimgtype1",		"h_name" => "画像種別1"),
		"228" => array("d_name" => "fudoimgcomment1",		"h_name" => "画像コメント1"),
		"229" => array("d_name" => "fudoimg2",			"h_name" => "ローカルファイル名2"),
	//	"230" => array("d_name" => "",				"h_name" => "ローカル修正日時2"),
		"231" => array("d_name" => "fudoimgtype2",		"h_name" => "画像種別2"),
		"232" => array("d_name" => "fudoimgcomment2",		"h_name" => "画像コメント2"),
		"233" => array("d_name" => "fudoimg3",			"h_name" => "ローカルファイル名3"),
	//	"234" => array("d_name" => "",				"h_name" => "ローカル修正日時3"),
		"235" => array("d_name" => "fudoimgtype3",		"h_name" => "画像種別3"),
		"236" => array("d_name" => "fudoimgcomment3",		"h_name" => "画像コメント3"),
		"237" => array("d_name" => "fudoimg4",			"h_name" => "ローカルファイル名4"),
	//	"238" => array("d_name" => "",				"h_name" => "ローカル修正日時4"),
		"239" => array("d_name" => "fudoimgtype4",		"h_name" => "画像種別4"),
		"240" => array("d_name" => "fudoimgcomment4",		"h_name" => "画像コメント4"),
		"241" => array("d_name" => "fudoimg5",			"h_name" => "ローカルファイル名5"),
	//	"242" => array("d_name" => "",				"h_name" => "ローカル修正日時5"),
		"243" => array("d_name" => "fudoimgtype5",		"h_name" => "画像種別5"),
		"244" => array("d_name" => "fudoimgcomment5",		"h_name" => "画像コメント5"),
		"245" => array("d_name" => "fudoimg6",			"h_name" => "ローカルファイル名6"),
	//	"246" => array("d_name" => "",				"h_name" => "ローカル修正日時6"),
		"247" => array("d_name" => "fudoimgtype6",		"h_name" => "画像種別6"),
		"248" => array("d_name" => "fudoimgcomment6",		"h_name" => "画像コメント6"),
	//	"249" => array("d_name" => "",				"h_name" => "所属グループ"),
		"250" => array("d_name" => "setsubi",			"h_name" => "設備・条件"),
	//	"251" => array("d_name" => "",				"h_name" => "お勧めポイント"),


		"273" => array("d_name" => "fudoimg7",			"h_name" => "ローカルファイル名7"),
	//	"274" => array("d_name" => "",				"h_name" => "ローカル修正日時7"),
		"275" => array("d_name" => "fudoimgtype7",		"h_name" => "画像種別7"),
		"276" => array("d_name" => "fudoimgcomment7",		"h_name" => "画像コメント7"),
		"277" => array("d_name" => "fudoimg8",			"h_name" => "ローカルファイル名8"),
	//	"278" => array("d_name" => "",				"h_name" => "ローカル修正日時8"),
		"279" => array("d_name" => "fudoimgtype8",		"h_name" => "画像種別8"),
		"280" => array("d_name" => "fudoimgcomment8",		"h_name" => "画像コメント8"),
		"281" => array("d_name" => "fudoimg9",			"h_name" => "ローカルファイル名9"),
	//	"282" => array("d_name" => "",				"h_name" => "ローカル修正日時9"),
		"283" => array("d_name" => "fudoimgtype9",		"h_name" => "画像種別9"),
		"284" => array("d_name" => "fudoimgcomment9",		"h_name" => "画像コメント9"),
		"285" => array("d_name" => "fudoimg10",			"h_name" => "ローカルファイル名10"),
	//	"286" => array("d_name" => "",				"h_name" => "ローカル修正日時10"),
		"287" => array("d_name" => "fudoimgtype10",		"h_name" => "画像種別10"),
		"288" => array("d_name" => "fudoimgcomment10",		"h_name" => "画像コメント10"),
		"289" => array("d_name" => "fudoimg11",			"h_name" => "ローカルファイル名11"),
	//	"290" => array("d_name" => "",				"h_name" => "ローカル修正日時11"),
		"291" => array("d_name" => "fudoimgtype11",		"h_name" => "画像種別11"),
		"292" => array("d_name" => "fudoimgcomment11",		"h_name" => "画像コメント11"),
		"293" => array("d_name" => "fudoimg12",			"h_name" => "ローカルファイル名12"),
	//	"294" => array("d_name" => "",				"h_name" => "ローカル修正日時12"),
		"295" => array("d_name" => "fudoimgtype12",		"h_name" => "画像種別12"),
		"296" => array("d_name" => "fudoimgcomment12",		"h_name" => "画像コメント12"),
		"297" => array("d_name" => "fudoimg13",			"h_name" => "ローカルファイル名13"),
	//	"298" => array("d_name" => "",				"h_name" => "ローカル修正日時13"),
		"299" => array("d_name" => "fudoimgtype13",		"h_name" => "画像種別13"),
		"300" => array("d_name" => "fudoimgcomment13",		"h_name" => "画像コメント13"),
		"301" => array("d_name" => "fudoimg14",			"h_name" => "ローカルファイル名14"),
	//	"302" => array("d_name" => "",				"h_name" => "ローカル修正日時14"),
		"303" => array("d_name" => "fudoimgtype14",		"h_name" => "画像種別14"),
		"304" => array("d_name" => "fudoimgcomment14",		"h_name" => "画像コメント14"),
		"305" => array("d_name" => "fudoimg15",			"h_name" => "ローカルファイル名15"),
	//	"306" => array("d_name" => "",				"h_name" => "ローカル修正日時15"),
		"307" => array("d_name" => "fudoimgtype15",		"h_name" => "画像種別15"),
		"308" => array("d_name" => "fudoimgcomment15",		"h_name" => "画像コメント15"),
		"309" => array("d_name" => "fudoimg16",			"h_name" => "ローカルファイル名16"),
	//	"310" => array("d_name" => "",				"h_name" => "ローカル修正日時16"),
		"311" => array("d_name" => "fudoimgtype16",		"h_name" => "画像種別16"),
		"312" => array("d_name" => "fudoimgcomment16",		"h_name" => "画像コメント16"),
		"313" => array("d_name" => "fudoimg17",			"h_name" => "ローカルファイル名17"),
	//	"314" => array("d_name" => "",				"h_name" => "ローカル修正日時17"),
		"315" => array("d_name" => "fudoimgtype17",		"h_name" => "画像種別17"),
		"316" => array("d_name" => "fudoimgcomment17",		"h_name" => "画像コメント17"),
		"317" => array("d_name" => "fudoimg18",			"h_name" => "ローカルファイル名18"),
	//	"318" => array("d_name" => "",				"h_name" => "ローカル修正日時18"),
		"319" => array("d_name" => "fudoimgtype18",		"h_name" => "画像種別18"),
		"320" => array("d_name" => "fudoimgcomment18",		"h_name" => "画像コメント18"),
		"321" => array("d_name" => "fudoimg19",			"h_name" => "ローカルファイル名19"),
	//	"322" => array("d_name" => "",				"h_name" => "ローカル修正日時19"),
		"323" => array("d_name" => "fudoimgtype19",		"h_name" => "画像種別19"),
		"324" => array("d_name" => "fudoimgcomment19",		"h_name" => "画像コメント19"),
		"325" => array("d_name" => "fudoimg20",			"h_name" => "ローカルファイル名20"),
	//	"326" => array("d_name" => "",				"h_name" => "ローカル修正日時20"),
		"327" => array("d_name" => "fudoimgtype20",		"h_name" => "画像種別20"),
		"328" => array("d_name" => "fudoimgcomment20",		"h_name" => "画像コメント20"),
		"329" => array("d_name" => "fudoimg21",			"h_name" => "ローカルファイル名21"),
	//	"330" => array("d_name" => "",				"h_name" => "ローカル修正日時21"),
		"331" => array("d_name" => "fudoimgtype21",		"h_name" => "画像種別21"),
		"332" => array("d_name" => "fudoimgcomment21",		"h_name" => "画像コメント21"),
		"333" => array("d_name" => "fudoimg22",			"h_name" => "ローカルファイル名22"),
	//	"334" => array("d_name" => "",				"h_name" => "ローカル修正日時22"),
		"335" => array("d_name" => "fudoimgtype22",		"h_name" => "画像種別22"),
		"336" => array("d_name" => "fudoimgcomment22",		"h_name" => "画像コメント22"),
		"337" => array("d_name" => "fudoimg23",			"h_name" => "ローカルファイル名23"),
	//	"338" => array("d_name" => "",				"h_name" => "ローカル修正日時23"),
		"339" => array("d_name" => "fudoimgtype23",		"h_name" => "画像種別23"),
		"340" => array("d_name" => "fudoimgcomment23",		"h_name" => "画像コメント23"),
		"341" => array("d_name" => "fudoimg24",			"h_name" => "ローカルファイル名24"),
	//	"342" => array("d_name" => "",				"h_name" => "ローカル修正日時24"),
		"343" => array("d_name" => "fudoimgtype24",		"h_name" => "画像種別24"),
		"344" => array("d_name" => "fudoimgcomment24",		"h_name" => "画像コメント24"),
		"345" => array("d_name" => "fudoimg25",			"h_name" => "ローカルファイル名25"),
	//	"346" => array("d_name" => "",				"h_name" => "ローカル修正日時25"),
		"347" => array("d_name" => "fudoimgtype25",		"h_name" => "画像種別25"),
		"348" => array("d_name" => "fudoimgcomment25",		"h_name" => "画像コメント25"),
		"349" => array("d_name" => "fudoimg26",			"h_name" => "ローカルファイル名26"),
	//	"350" => array("d_name" => "",				"h_name" => "ローカル修正日時26"),
		"351" => array("d_name" => "fudoimgtype26",		"h_name" => "画像種別26"),
		"352" => array("d_name" => "fudoimgcomment26",		"h_name" => "画像コメント26"),
		"353" => array("d_name" => "fudoimg27",			"h_name" => "ローカルファイル名27"),
	//	"354" => array("d_name" => "",				"h_name" => "ローカル修正日時27"),
		"355" => array("d_name" => "fudoimgtype27",		"h_name" => "画像種別27"),
		"356" => array("d_name" => "fudoimgcomment27",		"h_name" => "画像コメント27"),
		"357" => array("d_name" => "fudoimg28",			"h_name" => "ローカルファイル名28"),
	//	"358" => array("d_name" => "",				"h_name" => "ローカル修正日時28"),
		"359" => array("d_name" => "fudoimgtype28",		"h_name" => "画像種別28"),
		"360" => array("d_name" => "fudoimgcomment28",		"h_name" => "画像コメント28"),
		"361" => array("d_name" => "fudoimg29",			"h_name" => "ローカルファイル名29"),
	//	"362" => array("d_name" => "",				"h_name" => "ローカル修正日時29"),
		"363" => array("d_name" => "fudoimgtype29",		"h_name" => "画像種別29"),
		"364" => array("d_name" => "fudoimgcomment29",		"h_name" => "画像コメント29"),
		"365" => array("d_name" => "fudoimg30",			"h_name" => "ローカルファイル名30"),
	//	"366" => array("d_name" => "",				"h_name" => "ローカル修正日時30"),
		"367" => array("d_name" => "fudoimgtype30",		"h_name" => "画像種別30"),
		"368" => array("d_name" => "fudoimgcomment30",		"h_name" => "画像コメント30"),

		"369" => array("d_name" => "",				"h_name" => "レコード終了マーク")
	);


	//k_rains csv format
	$work_k_rains =
	array(	
		"1" =>  array("d_name" => "shikibesu",			"h_name" => "物件番号"),
	//	"2" =>  array("d_name" => "",				"h_name" => "データ種類"),	
	//	"3" =>  array("d_name" => "",				"h_name" => "物件種別"),
	//	"4" =>  array("d_name" => "",				"h_name" => "物件種目"),
		"5" =>  array("d_name" => "motozukemei",		"h_name" => "会員名"),
		"6" =>  array("d_name" => "motozuketel",		"h_name" => "ＴＥＬ"),
	//	"7" =>  array("d_name" => "",				"h_name" => "図面"),
		"8" =>  array("d_name" => "kakaku",			"h_name" => "価格／賃料（万円）"),
		"108" =>  array("d_name" => "kakaku",			"h_name" => "価格／賃料"),
		"9" =>  array("d_name" => "kakakutsubo",		"h_name" => "坪単価（万円）"),
		"109" =>  array("d_name" => "kakakutsubo",		"h_name" => "坪単価"),
		"10" => array("d_name" => "kakakuzei",			"h_name" => "消費税額"),
	//	"11" => array("d_name" => "",				"h_name" => "元価格／元賃料（万円）"),
	//	"12" => array("d_name" => "",				"h_name" => "元坪単価（万円）"),
		"13" => array("d_name" => "tatemonohosiki",		"h_name" => "面積計測方式"),
		"14" => array("d_name" => "tochikukaku",		"h_name" => "土地面積（平米）"),
		"1014" => array("d_name" => "tochikukaku",		"h_name" => "土地面積"),
		"15" => array("d_name" => "tatemonomenseki",		"h_name" => "建物面積／専有面積（平米）"),
		"1015" => array("d_name" => "tatemonomenseki",		"h_name" => "建物面積／専有面積"),
	//	"16" => array("d_name" => "",				"h_name" => "私道面積／バルコニー面積（平米）"),
		"17" => array("d_name" => "kakakuhoshoukin",		"h_name" => "保証金（万円）"),
		"1017" => array("d_name" => "kakakuhoshoukin",		"h_name" => "保証金"),
		"18" => array("d_name" => "kakakushikikin",		"h_name" => "敷金（万円）"),
		"1018" => array("d_name" => "kakakushikikin",		"h_name" => "敷金"),
		"19" => array("d_name" => "kakakukenrikin",		"h_name" => "権利金（万円）"),
		"1019" => array("d_name" => "kakakukenrikin",		"h_name" => "権利金"),
		"20" => array("d_name" => "kakakureikin",		"h_name" => "礼金（万円）"),
		"1020" => array("d_name" => "kakakureikin",		"h_name" => "礼金"),
		"21" => array("d_name" => "kakakukyouekihi",		"h_name" => "管理費（円）"),
		"1021" => array("d_name" => "kakakukyouekihi",		"h_name" => "管理費"),
	//	"22" => array("d_name" => "",				"h_name" => "所在地"),
		"23" => array("d_name" => "shozaichicode",		"h_name" => "所在地１"),
	//	"24" => array("d_name" => "",				"h_name" => "所在地２"),
	//	"25" => array("d_name" => "",				"h_name" => "沿線"),
	//	"26" => array("d_name" => "",				"h_name" => "駅"),
		"27" => array("d_name" => "koutsubussfun1",		"h_name" => "バス（分）"),
		"1027" => array("d_name" => "koutsubussfun1",		"h_name" => "バス"),
		"28" => array("d_name" => "koutsutoho1f",		"h_name" => "徒歩その１（分）"),
		"1028" => array("d_name" => "koutsutoho1f",		"h_name" => "徒歩その１"),
		"1128" => array("d_name" => "koutsutoho1f",		"h_name" => "徒歩その1"),
		"29" => array("d_name" => "koutsutoho1",		"h_name" => "徒歩その２（ｍ）"),
		"1029" => array("d_name" => "koutsutoho1",		"h_name" => "徒歩その２"),
		"1129" => array("d_name" => "koutsutoho1",		"h_name" => "徒歩その2"),
		"30" => array("d_name" => "koutsusonota",		"h_name" => "その他交通手段"),
		"31" => array("d_name" => "nyukyogenkyo",		"h_name" => "現況"),
		"32" => array("d_name" => "nyukyojiki",			"h_name" => "引渡時期"),
		"33" => array("d_name" => "nyukyonengetsu",		"h_name" => "引渡年月"),
		"34" => array("d_name" => "nyukyosyun",			"h_name" => "引渡旬"),
		"35" => array("d_name" => "torihikitaiyo",		"h_name" => "取引態様"),
		"36" => array("d_name" => "houshoukeitai",		"h_name" => "報酬形態"),
		"37" => array("d_name" => "tochikokudohou",		"h_name" => "国土法"),
		"38" => array("d_name" => "tochichimoku",		"h_name" => "地目"),
		"39" => array("d_name" => "tochikeikaku",		"h_name" => "都市計画"),
		"40" => array("d_name" => "tochiyouto",			"h_name" => "用途"),
		"41" => array("d_name" => "tochikenpei",		"h_name" => "建ぺい率（％）"),
		"1041" => array("d_name" => "tochikenpei",		"h_name" => "建ぺい率"),
		"42" => array("d_name" => "tochiyoseki",		"h_name" => "容積率（％）"),
		"1042" => array("d_name" => "tochiyoseki",		"h_name" => "容積率"),
		"43" => array("d_name" => "bukkenmei",			"h_name" => "マンション名"),
		"44" => array("d_name" => "tochikenri",			"h_name" => "土地権利／借地権利"),
	//	"45" => array("d_name" => "",				"h_name" => "建築条件／建物賃貸借区分"),		11001 建築条件付
		"46" => array("d_name" => "tochisetsudohouko1",		"h_name" => "接道方向幅員１"),
		"1046" => array("d_name" => "tochisetsudohouko1",		"h_name" => "接道方向幅員1"),
		"47" => array("d_name" => "tochisetsudohouko2",		"h_name" => "接道方向幅員２"),
		"1047" => array("d_name" => "tochisetsudohouko2",		"h_name" => "接道方向幅員2"),
		"48" => array("d_name" => "tochisetsudo",		"h_name" => "接道状況"),
		"49" => array("d_name" => "tochisetsudoshurui1",	"h_name" => "接道種別"),
		"50" => array("d_name" => "tochisetsudomaguchi1",	"h_name" => "接道接面（ｍ）"),
		"1050" => array("d_name" => "tochisetsudomaguchi1",	"h_name" => "接道接面"),
		"51" => array("d_name" => "tochisetsudoichishitei1",	"h_name" => "接道位置指定"),
		"52" => array("d_name" => "tatemonokozo",		"h_name" => "構造材質"),
		"53" => array("d_name" => "tatemonokaisu1",		"h_name" => "地上階層（階）"),
		"1053" => array("d_name" => "tatemonokaisu1",		"h_name" => "地上階層"),
		"54" => array("d_name" => "tatemonokaisu2",		"h_name" => "地下階層（階）"),
		"1054" => array("d_name" => "tatemonokaisu2",		"h_name" => "地下階層"),
	//	"55" => array("d_name" => "",				"h_name" => "間取り"),
		"56" => array("d_name" => "bukkensoukosu",		"h_name" => "総戸数（戸）"),
		"1056" => array("d_name" => "bukkensoukosu",		"h_name" => "総戸数"),
		"57" => array("d_name" => "heyakaisu",			"h_name" => "階（階）"),
		"1057" => array("d_name" => "heyakaisu",			"h_name" => "階"),
		"58" => array("d_name" => "chushajokubun",		"h_name" => "駐車場"),
		"59" => array("d_name" => "tatemonochikunenn",		"h_name" => "築年月"),
	//	"60" => array("d_name" => "heyamuki",			"h_name" => "バルコニー方向"),
	//	"61" => array("d_name" => "",				"h_name" => "備考"),
		"62" => array("d_name" => "shanaimemo",			"h_name" => "備考補足"),
	//	"63" => array("d_name" => "",				"h_name" => "登録日付"),
		"64" => array("d_name" => "seiyakubi",			"h_name" => "成約日付")
/*
		,"225" => array("d_name" => "fudoimg1",			"h_name" => "画像名1"),
		"228" => array("d_name" => "fudoimgcomment1",		"h_name" => "画像コメント1"),
		"229" => array("d_name" => "fudoimg2",			"h_name" => "画像名2"),
		"232" => array("d_name" => "fudoimgcomment2",		"h_name" => "画像コメント2"),
		"233" => array("d_name" => "fudoimg3",			"h_name" => "画像名3"),
		"236" => array("d_name" => "fudoimgcomment3",		"h_name" => "画像コメント3"),
		"237" => array("d_name" => "fudoimg4",			"h_name" => "画像名4"),
		"240" => array("d_name" => "fudoimgcomment4",		"h_name" => "画像コメント4"),
		"241" => array("d_name" => "fudoimg5",			"h_name" => "画像名5"),
		"244" => array("d_name" => "fudoimgcomment5",		"h_name" => "画像コメント5"),
		"245" => array("d_name" => "fudoimg6",			"h_name" => "画像名6"),
		"248" => array("d_name" => "fudoimgcomment6",		"h_name" => "画像コメント6"),
		"251" => array("d_name" => "fudoimg7",			"h_name" => "画像名7"),
		"252" => array("d_name" => "fudoimgcomment7",		"h_name" => "画像コメント7"),
		"253" => array("d_name" => "fudoimg8",			"h_name" => "画像名8"),
		"254" => array("d_name" => "fudoimgcomment8",		"h_name" => "画像コメント8"),
		"255" => array("d_name" => "fudoimg9",			"h_name" => "画像名9"),
		"226" => array("d_name" => "fudoimgcomment9",		"h_name" => "画像コメント9"),
		"257" => array("d_name" => "fudoimg10",			"h_name" => "画像名10"),
		"258" => array("d_name" => "fudoimgcomment10",		"h_name" => "画像コメント10")
*/
	);


	//h_rains csv format
	$work_h_rains =
	array(	
		"1"  =>  array("d_name" => "shikibesu",			"h_name" => "物件番号"),
//		"2"  =>  array("d_name" => "",				"h_name" => "データ種類"),
//		"3"  =>  array("d_name" => "",				"h_name" => "物件種別"),
//		"4"  =>  array("d_name" => "",				"h_name" => "物件種目"),
		"5"  =>  array("d_name" => "motozukemei",		"h_name" => "会員名"),
		"6"  =>  array("d_name" => "motozuketel",		"h_name" => "代表電話番号"),
//		"7"  =>  array("d_name" => "",				"h_name" => "問合せ担当者(1)"),
//		"8"  =>  array("d_name" => "",				"h_name" => "問合せ電話番号(1)"),
//		"9"  =>  array("d_name" => "",				"h_name" => "Eメールアドレス(1)"),
//		"10" =>  array("d_name" => "",				"h_name" => "図面"),
//		"11" =>  array("d_name" => "",				"h_name" => "登録年月日"),
//		"12" =>  array("d_name" => "",				"h_name" => "変更年月日"),
		"13" =>  array("d_name" => "",				"h_name" => "取引条件の有効期限"),
		"14" =>  array("d_name" => "tatemonoshinchiku",		"h_name" => "新築中古区分"),
//		"15" =>  array("d_name" => "",				"h_name" => "都道府県名"),
//		"16" =>  array("d_name" => "",				"h_name" => "所在地名1"),
		"17" =>  array("d_name" => "shozaichimeisho",		"h_name" => "所在地名2"),
		"18" =>  array("d_name" => "shozaichimeisho2",		"h_name" => "所在地名3"),
		"19" =>  array("d_name" => "bukkenmei",			"h_name" => "建物名"),
		"20" =>  array("d_name" => "bukkennaiyo",		"h_name" => "部屋番号"),
		"21" =>  array("d_name" => "shozaichimeisho3",		"h_name" => "その他所在地表示"),
		"22" =>  array("d_name" => "",				"h_name" => "棟番号"),
//		"23" =>  array("d_name" => "",				"h_name" => "沿線略称(1)"),
//		"24" =>  array("d_name" => "",				"h_name" => "駅名(1)"),
		"25" =>  array("d_name" => "koutsutoho1f",		"h_name" => "徒歩(分)1(1)"),
		"26" =>  array("d_name" => "koutsutoho1",		"h_name" => "徒歩(m)2(1)"),
		"27" =>  array("d_name" => "koutsubussfun1",		"h_name" => "バス(1)"),
//		"28" =>  array("d_name" => "",				"h_name" => "バス路線名(1)"),
		"29" =>  array("d_name" => "koutsubusstei1",		"h_name" => "バス停名称(1)"),
		"30" =>  array("d_name" => "koutsutohob1f",		"h_name" => "停歩(分)(1)"),
//		"31" =>  array("d_name" => "",				"h_name" => "停歩(m)(1)"),
//		"32" =>  array("d_name" => "",				"h_name" => "車(km)(1)"),
		"33" =>  array("d_name" => "koutsusonota",		"h_name" => "その他交通手段"),
//		"34" =>  array("d_name" => "",				"h_name" => "交通(分)1"),
//		"35" =>  array("d_name" => "",				"h_name" => "交通(m)2"),
		"36" =>  array("d_name" => "nyukyogenkyo",		"h_name" => "現況"),
//		"37" =>  array("d_name" => "",				"h_name" => "現況予定年月"),
		"38" =>  array("d_name" => "nyukyojiki",		"h_name" => "引渡時期"),
		"39" =>  array("d_name" => "nyukyonengetsu",		"h_name" => "引渡年月(西暦)"),
		"40" =>  array("d_name" => "nyukyosyun",		"h_name" => "引渡旬"),
		"41" =>  array("d_name" => "nyukyonengetsu",		"h_name" => "入居年月(西暦)"),
//		"42" =>  array("d_name" => "",				"h_name" => "入居日"),
		"43" =>  array("d_name" => "torihikitaiyo",		"h_name" => "取引態様"),
//		"44" =>  array("d_name" => "houshoukeitai",		"h_name" => "報酬形態"),
		"45" =>  array("d_name" => "houshoukeitai",		"h_name" => "手数料割合率"),
//		"46" =>  array("d_name" => "",				"h_name" => "手数料"),
		"47" =>  array("d_name" => "kakaku",			"h_name" => "価格"),
		"48" =>  array("d_name" => "kakakuzei",			"h_name" => "価格消費税"),
		"49" =>  array("d_name" => "kakakutsubo",		"h_name" => "坪単価"),
//		"50" =>  array("d_name" => "",				"h_name" => "㎡単価"),
		"51" =>  array("d_name" => "kakakuhyorimawari",		"h_name" => "想定利回り(％)"),
		"52" =>  array("d_name" => "tatemonohosiki",		"h_name" => "面積計測方式"),
		"53" =>  array("d_name" => "tochikukaku",		"h_name" => "土地面積"),
//		"54" =>  array("d_name" => "",				"h_name" => "土地共有持分面積"),
//		"55" =>  array("d_name" => "",				"h_name" => "土地共有持分(分子)"),
//		"56" =>  array("d_name" => "",				"h_name" => "土地共有持分(分母)"),
//		"57" =>  array("d_name" => "tatemonomenseki",		"h_name" => "建物面積1"),
//		"58" =>  array("d_name" => "tatemonomenseki",		"h_name" => "専有面積"),
//		"59" =>  array("d_name" => "		",		"h_name" => "私道負担有無"),
		"60" =>  array("d_name" => "tochishido",		"h_name" => "私道面積"),
		"61" =>  array("d_name" => "heyabarukoni",		"h_name" => "バルコニー(テラス)面積"),
//		"62" =>  array("d_name" => "",				"h_name" => "専用庭面積"),
		"63" =>  array("d_name" => "tochisetback",		"h_name" => "セットバック区分"),
//		"64" =>  array("d_name" => "",				"h_name" => "後退距離(m)"),
		"65" =>  array("d_name" => "tochisetback2",		"h_name" => "セットバック面積(㎡)"),
//		"66" =>  array("d_name" => "",				"h_name" => "開発面積／総面積"),
//		"67" =>  array("d_name" => "",				"h_name" => "販売総面積"),
//		"68" =>  array("d_name" => "",				"h_name" => "販売区画数"),
//		"69" =>  array("d_name" => "",				"h_name" => "工事完了年月(西暦)"),
		"70" =>  array("d_name" => "tatemonokentikumenseki",	"h_name" => "建築面積"),
//		"71" =>  array("d_name" => "",				"h_name" => "延べ面積"),
//		"72" =>  array("d_name" => "",				"h_name" => "敷地延長の有無"),
//		"73" =>  array("d_name" => "",				"h_name" => "敷地延長(30%以上表示)"),
		"74" =>  array("d_name" => "shakuchiryo",		"h_name" => "借地料"),
//		"75" =>  array("d_name" => "shakuchikikan",		"h_name" => "借地期間"),
//		"76" =>  array("d_name" => "shakuchikikan",		"h_name" => "借地期限(西暦)"),
//		"77" =>  array("d_name" => "",				"h_name" => "施設費用項目(1)"),
//		"78" =>  array("d_name" => "",				"h_name" => "施設費用(1)"),
		"79" =>  array("d_name" => "tochikokudohou",			"h_name" => "国土法届出"),
//		"80" =>  array("d_name" => "tochichimoku",			"h_name" => "登記簿地目"),
		"81" =>  array("d_name" => "tochichimoku",			"h_name" => "現況地目"),
		"82" =>  array("d_name" => "tochikeikaku",			"h_name" => "都市計画"),
		"83" =>  array("d_name" => "tochiyouto",			"h_name" => "用途地域(1)"),
		"84" =>  array("d_name" => "",					"h_name" => "用途地域(2)"),
		"85" =>  array("d_name" => "",					"h_name" => "最適用途"),
		"86" =>  array("d_name" => "tochikenpei",			"h_name" => "建ぺい率"),
		"87" =>  array("d_name" => "tochiyoseki",			"h_name" => "容積率"),
		"88" =>  array("d_name" => "",					"h_name" => "地域地区"),
		"89" =>  array("d_name" => "tochikenri",			"h_name" => "土地権利"),
//		"90" =>  array("d_name" => "",					"h_name" => "付帯権利"),
//		"91" =>  array("d_name" => "",					"h_name" => "造作譲渡金"),
//		"92" =>  array("d_name" => "",					"h_name" => "定借権利金"),
//		"93" =>  array("d_name" => "",					"h_name" => "定借保証金"),
//		"94" =>  array("d_name" => "",					"h_name" => "定借敷金"),
		"95" =>  array("d_name" => "tochichisei",			"h_name" => "地勢"),
//		"96" =>  array("d_name" => "",					"h_name" => "建築条件"),	//11001 建築条件付
//		"97" =>  array("d_name" => "",					"h_name" => "オーナーチェンジ"),
		"98" =>  array("d_name" => "kanrikumiai",					"h_name" => "管理組合有無"),
		"99" =>  array("d_name" => "kanrikeitai",					"h_name" => "管理形態"),
		"100" =>  array("d_name" => "kanrikaisha",					"h_name" => "管理会社名"),
		"101" =>  array("d_name" => "kanrininn",					"h_name" => "管理人状況"),
		"102" =>  array("d_name" => "kakakukyouekihi",			"h_name" => "管理費"),
//		"103" =>  array("d_name" => "",					"h_name" => "管理費消費税"),
		"104" =>  array("d_name" => "kakakutsumitate",			"h_name" => "修繕積立金"),
//		"105" =>  array("d_name" => "",					"h_name" => "その他月額費名称1"),
//		"106" =>  array("d_name" => "",					"h_name" => "その他月額費用金額1"),
//		"107" =>  array("d_name" => "",					"h_name" => "施主"),
//		"108" =>  array("d_name" => "",					"h_name" => "施工会社名"),
//		"109" =>  array("d_name" => "",					"h_name" => "分譲会社名"),
//		"110" =>  array("d_name" => "",					"h_name" => "一括下請負人"),
		"111" =>  array("d_name" => "tochisetsudo",			"h_name" => "接道状況"),
		"112" =>  array("d_name" => "tochisetsudoshurui1",			"h_name" => "接道種別1"),
		"113" =>  array("d_name" => "tochisetsudomaguchi1",			"h_name" => "接道接面1"),
		"114" =>  array("d_name" => "tochisetsudoichishitei1",			"h_name" => "接道位置指定1"),
		"115" =>  array("d_name" => "tochisetsudohouko1",			"h_name" => "接道方向1"),
		"116" =>  array("d_name" => "tochisetsudofukuin1",			"h_name" => "接道幅員1"),
		"117" =>  array("d_name" => "tochisetsudoshurui2",			"h_name" => "接道種別2"),
		"118" =>  array("d_name" => "tochisetsudomaguchi2",			"h_name" => "接道接面2"),
		"119" =>  array("d_name" => "tochisetsudoichishitei2",			"h_name" => "接道位置指定2"),
		"120" =>  array("d_name" => "tochisetsudohouko2",			"h_name" => "接道方向2"),
		"121" =>  array("d_name" => "tochisetsudofukuin2",			"h_name" => "接道幅員2"),
//		"122" =>  array("d_name" => "",			"h_name" => "接道種別3"),
//		"123" =>  array("d_name" => "",			"h_name" => "接道接面3"),
//		"124" =>  array("d_name" => "",			"h_name" => "接道位置指定3"),
//		"125" =>  array("d_name" => "",			"h_name" => "接道方向3"),
//		"126" =>  array("d_name" => "",			"h_name" => "接道幅員3"),
//		"127" =>  array("d_name" => "",			"h_name" => "接道種別4"),
//		"128" =>  array("d_name" => "",			"h_name" => "接道接面4"),
//		"129" =>  array("d_name" => "",			"h_name" => "接道位置指定4"),
//		"130" =>  array("d_name" => "",			"h_name" => "接道方向4"),
//		"131" =>  array("d_name" => "",			"h_name" => "接道幅員4"),
//		"132" =>  array("d_name" => "",			"h_name" => "接道舗装"),
//		"133" =>  array("d_name" => "",			"h_name" => "間取タイプ(1)"),
		"134" =>  array("d_name" => "madorisu",				"h_name" => "間取部屋数(1)"),
//		"135" =>  array("d_name" => "",					"h_name" => "部屋位置"),
//		"136" =>  array("d_name" => "",					"h_name" => "納戸数"),
		"137" =>  array("d_name" => "madorikai1",			"h_name" => "室所在階1(1)"),
		"138" =>  array("d_name" => "madorisyurui1",			"h_name" => "室タイプ1(1)"),
		"139" =>  array("d_name" => "madorijyousu1",			"h_name" => "室広さ1(1)"),
		"140" =>  array("d_name" => "madorishitsu1",			"h_name" => "室数1(1)"),
		"141" =>  array("d_name" => "madorikai2",			"h_name" => "室所在階2(1)"),
		"142" =>  array("d_name" => "madorisyurui2",			"h_name" => "室タイプ2(1)"),
		"143" =>  array("d_name" => "madorijyousu2",			"h_name" => "室広さ2(1)"),
		"144" =>  array("d_name" => "madorishitsu2",			"h_name" => "室数2(1)"),
		"145" =>  array("d_name" => "madorikai3",			"h_name" => "室所在階3(1)"),
		"146" =>  array("d_name" => "madorisyurui3",			"h_name" => "室タイプ3(1)"),
		"147" =>  array("d_name" => "madorijyousu3",			"h_name" => "室広さ3(1)"),
		"148" =>  array("d_name" => "madorishitsu3",			"h_name" => "室数3(1)"),
		"149" =>  array("d_name" => "madorikai4",			"h_name" => "室所在階4(1)"),
		"150" =>  array("d_name" => "madorisyurui4",			"h_name" => "室タイプ4(1)"),
		"151" =>  array("d_name" => "madorijyousu4",			"h_name" => "室広さ4(1)"),
		"152" =>  array("d_name" => "madorishitsu4",			"h_name" => "室数4(1)"),
		"153" =>  array("d_name" => "madorikai5",			"h_name" => "室所在階5(1)"),
		"154" =>  array("d_name" => "madorisyurui5",			"h_name" => "室タイプ5(1)"),
		"155" =>  array("d_name" => "madorijyousu5",			"h_name" => "室広さ5(1)"),
		"156" =>  array("d_name" => "madorishitsu5",			"h_name" => "室数5(1)"),
		"157" =>  array("d_name" => "madorikai6",			"h_name" => "室所在階6(1)"),
		"158" =>  array("d_name" => "madorisyurui6",			"h_name" => "室タイプ6(1)"),
		"159" =>  array("d_name" => "madorijyousu6",			"h_name" => "室広さ6(1)"),
		"160" =>  array("d_name" => "madorishitsu6",			"h_name" => "室数6(1)"),
		"161" =>  array("d_name" => "madorikai7",			"h_name" => "室所在階7(1)"),
		"162" =>  array("d_name" => "madorisyurui7",			"h_name" => "室タイプ7(1)"),
		"163" =>  array("d_name" => "madorijyousu7",			"h_name" => "室広さ7(1)"),
		"164" =>  array("d_name" => "madorishitsu7",			"h_name" => "室数7(1)"),
		"165" =>  array("d_name" => "madoribiko",			"h_name" => "間取りその他(1)"),
		"166" =>  array("d_name" => "chushajokubun",			"h_name" => "駐車場在否"),
		"167" =>  array("d_name" => "chushajoryokin",			"h_name" => "駐車場月額"),
//		"168" =>  array("d_name" => "",					"h_name" => "駐車場月額消費税"),
//		"169" =>  array("d_name" => "",					"h_name" => "駐車場敷金(額)"),
//		"170" =>  array("d_name" => "",					"h_name" => "駐車場敷金(ヶ月)"),
//		"171" =>  array("d_name" => "",					"h_name" => "駐車場礼金(額)"),
//		"172" =>  array("d_name" => "",					"h_name" => "駐車場礼金(ヶ月)"),
		"173" =>  array("d_name" => "tatemonokozo",			"h_name" => "建物構造"),
//		"174" =>  array("d_name" => "",					"h_name" => "建物工法"),
//		"175" =>  array("d_name" => "",					"h_name" => "建物形式"),
		"176" =>  array("d_name" => "tatemonokaisu1",			"h_name" => "地上階層"),
		"177" =>  array("d_name" => "tatemonokaisu2",			"h_name" => "地下階層"),
		"178" =>  array("d_name" => "heyakaisu",			"h_name" => "所在階"),
		"179" =>  array("d_name" => "tatemonochikunenn",		"h_name" => "築年月(西暦)"),
		"180" =>  array("d_name" => "bukkensoukosu",			"h_name" => "総戸数"),
//		"181" =>  array("d_name" => "",					"h_name" => "棟総戸数"),
//		"182" =>  array("d_name" => "",					"h_name" => "連棟戸数"),
		"183" =>  array("d_name" => "heyamuki",				"h_name" => "バルコニー方向(1)"),
//		"184" =>  array("d_name" => "",					"h_name" => "増改築年月1"),
//		"185" =>  array("d_name" => "",					"h_name" => "増改築履歴1"),
//		"186" =>  array("d_name" => "",					"h_name" => "増改築年月2"),
//		"187" =>  array("d_name" => "",					"h_name" => "増改築履歴2"),
//		"188" =>  array("d_name" => "",					"h_name" => "増改築年月3"),
//		"189" =>  array("d_name" => "",					"h_name" => "増改築履歴3"),
//		"190" =>  array("d_name" => "",					"h_name" => "周辺環境1(フリー)"),
//		"191" =>  array("d_name" => "",					"h_name" => "距離1"),
//		"192" =>  array("d_name" => "",					"h_name" => "時間1"),
//		"193" =>  array("d_name" => "",					"h_name" => "周辺アクセス１"),
//		"194" =>  array("d_name" => "",					"h_name" => "備考1"),
		"195" =>  array("d_name" => "shanaimemo",				"h_name" => "備考2"),
//		"196" =>  array("d_name" => "",					"h_name" => "自社管理欄"),
//		"197" =>  array("d_name" => "",					"h_name" => "再建築不可フラグ"),

//		"200" =>  array("d_name" => "",				"h_name" => "特優賃区分"),
		"201" =>  array("d_name" => "nyukyojiki",		"h_name" => "入居時期"),
		"202" =>  array("d_name" => "nyukyosyun",		"h_name" => "入居旬"),
//		"203" =>  array("d_name" => "",			"h_name" => "負担割合貸主"),
//		"204" =>  array("d_name" => "",			"h_name" => "負担割合借主"),
//		"205" =>  array("d_name" => "",			"h_name" => "配分割合元付"),
//		"206" =>  array("d_name" => "",			"h_name" => "配分割合客付"),
//		"207" =>  array("d_name" => "",			"h_name" => "報酬ヶ月"),
//		"208" =>  array("d_name" => "",			"h_name" => "報酬額"),
		"209" =>  array("d_name" => "kakaku",			"h_name" => "賃料"),
		"2091" =>  array("d_name" => "kakaku",			"h_name" => "価格賃料"),


//		"210" =>  array("d_name" => "",				"h_name" => "賃料消費税"),
//		"211" =>  array("d_name" => "",				"h_name" => "保証金1(額)"),
		"212" =>  array("d_name" => "kakakuhoshoukin",		"h_name" => "保証金2(ヶ月)"),
		"213" =>  array("d_name" => "kakakukenrikin",		"h_name" => "権利金1(額)"),
//		"214" =>  array("d_name" => "",				"h_name" => "権利金消費税(額)"),
		"215" =>  array("d_name" => "kakakuhoshoukin",		"h_name" => "権利金2(ヶ月)"),
//		"216" =>  array("d_name" => "",				"h_name" => "礼金1(額)"),
//		"217" =>  array("d_name" => "",				"h_name" => "礼金消費税(額)"),
		"218" =>  array("d_name" => "kakakureikin",		"h_name" => "礼金2(ヶ月)"),
//		"219" =>  array("d_name" => "",				"h_name" => "敷金1(額)"),
		"220" =>  array("d_name" => "kakakushikikin",		"h_name" => "敷金2(ヶ月)"),
//		"221" =>  array("d_name" => "",				"h_name" => "償却コード"),
		"222" =>  array("d_name" => "kakakushikibiki",		"h_name" => "償却総額"),
//		"223" =>  array("d_name" => "",				"h_name" => "償却月数"),
//		"224" =>  array("d_name" => "",				"h_name" => "償却率"),
//		"225" =>  array("d_name" => "",				"h_name" => "契約期間"),
//		"226" =>  array("d_name" => "",				"h_name" => "契約期限(西暦)"),
//		"227" =>  array("d_name" => "",				"h_name" => "解約引総額"),
//		"228" =>  array("d_name" => "",				"h_name" => "解約引月数"),
//		"229" =>  array("d_name" => "",				"h_name" => "解約引率"),
//		"230" =>  array("d_name" => "tatemonomenseki",		"h_name" => "使用部分面積"),
//		"231" =>  array("d_name" => "",				"h_name" => "建物賃貸借区分"),
//		"232" =>  array("d_name" => "",				"h_name" => "建物賃貸借期間"),
//		"233" =>  array("d_name" => "",				"h_name" => "建物賃貸借更新"),
		"234" =>  array("d_name" => "kakakukyouekihi",		"h_name" => "共益費"),
//		"235" =>  array("d_name" => "",				"h_name" => "共益費消費税"),
//		"236" =>  array("d_name" => "",				"h_name" => "雑費"),
//		"237" =>  array("d_name" => "",				"h_name" => "雑費消費税"),
//		"238" =>  array("d_name" => "",				"h_name" => "更新区分"),
		"239" =>  array("d_name" => "kakakukoushin",		"h_name" => "更新料(額)"),
		"240" =>  array("d_name" => "kakakukoushin",		"h_name" => "更新料(ヶ月)"),
//		"241" =>  array("d_name" => "",				"h_name" => "保険加入義務"),
//		"242" =>  array("d_name" => "",				"h_name" => "保険名称"),
		"243" =>  array("d_name" => "kakakuhoken",		"h_name" => "保険料"),
		"244" =>  array("d_name" => "kakakuhokenkikan",		"h_name" => "保険期間"),
		"245" =>  array("d_name" => "tatemonoshinchiku",	"h_name" => "新築フラグ"),
		"246" =>  array("d_name" => "bukkensoukosu",		"h_name" => "[賃貸]賃貸戸数"),
		"247" =>  array("d_name" => "",				"h_name" => "[賃貸]棟総戸数"),
		"248" =>  array("d_name" => "",				"h_name" => "[賃貸]連棟戸数")
/*
		,"250" => array("d_name" => "fudoimg1",			"h_name" => "画像名1"),
		"251" => array("d_name" => "fudoimgcomment1",		"h_name" => "画像コメント1"),
		"252" => array("d_name" => "fudoimg2",			"h_name" => "画像名2"),
		"253" => array("d_name" => "fudoimgcomment2",		"h_name" => "画像コメント2"),
		"254" => array("d_name" => "fudoimg3",			"h_name" => "画像名3"),
		"255" => array("d_name" => "fudoimgcomment3",		"h_name" => "画像コメント3"),
		"256" => array("d_name" => "fudoimg4",			"h_name" => "画像名4"),
		"257" => array("d_name" => "fudoimgcomment4",		"h_name" => "画像コメント4"),
		"258" => array("d_name" => "fudoimg5",			"h_name" => "画像名5"),
		"259" => array("d_name" => "fudoimgcomment5",		"h_name" => "画像コメント5"),
		"260" => array("d_name" => "fudoimg6",			"h_name" => "画像名6"),
		"261" => array("d_name" => "fudoimgcomment6",		"h_name" => "画像コメント6"),
		"262" => array("d_name" => "fudoimg7",			"h_name" => "画像名7"),
		"263" => array("d_name" => "fudoimgcomment7",		"h_name" => "画像コメント7"),
		"264" => array("d_name" => "fudoimg8",			"h_name" => "画像名8"),
		"265" => array("d_name" => "fudoimgcomment8",		"h_name" => "画像コメント8"),
		"266" => array("d_name" => "fudoimg9",			"h_name" => "画像名9"),
		"267" => array("d_name" => "fudoimgcomment9",		"h_name" => "画像コメント9"),
		"268" => array("d_name" => "fudoimg10",			"h_name" => "画像名10"),
		"269" => array("d_name" => "fudoimgcomment10",		"h_name" => "画像コメント10"),

		"300" => array("d_name" => "bukkenido",			"h_name" => "緯度"),
		"301" => array("d_name" => "bukkenkeido",		"h_name" => "経度")

*/
	);


	//k_rains csv format
	$work_k_rains_rosen =
	array(	
		"1" => array("name"=> "東海道・山陽新幹線","code" =>"12"),
		"2" => array("name"=> "東海道本線", "code" =>"89"),
		"3" => array("name"=> "東海道・山陽本線", "code" =>"231"),
		"4" => array("name"=> "北陸本線", "code" =>"241"),
		"5" => array("name"=> "舞鶴・小浜線", "code" =>"243"),
		"6" => array("name"=> "関西本線", "code" =>"306"),
		"7" => array("name"=> "紀勢本線", "code" =>"320"),
		"8" => array("name"=> "近鉄大阪線", "code" =>"466"),
		"9" => array("name"=> "近鉄難波・奈良線", "code" =>"502"),
		"10" => array("name"=> "近鉄橿原線", "code" =>"497"),
		"11" => array("name"=> "近鉄天理線", "code" =>"496"),
		"12" => array("name"=> "近鉄信貴線", "code" =>"468"),
		"13" => array("name"=> "近鉄南大阪線", "code" =>"513"),
		"14" => array("name"=> "近鉄吉野線", "code" =>"514"),
		"15" => array("name"=> "近鉄長野線", "code" =>"510"),
		"16" => array("name"=> "近鉄道明寺線", "code" =>"516"),
		"17" => array("name"=> "近鉄御所線", "code" =>"515"),
		"18" => array("name"=> "近鉄京都線", "code" =>"494"),
		"19" => array("name"=> "近鉄生駒線", "code" =>"504"),
		"20" => array("name"=> "近鉄田原本線", "code" =>"507"),
		"21" => array("name"=> "近鉄けいはんな線", "code" =>"503"),
		"22" => array("name"=> "近江鉄道本線", "code" =>"762"),
		"23" => array("name"=> "近江鉄道多賀線", "code" =>"761"),
		"24" => array("name"=> "近江鉄道八日市線", "code" =>"828"),
		"25" => array("name"=> "京福嵐山本線", "code" =>"765"),
		"26" => array("name"=> "京福北野線", "code" =>"766"),
		"27" => array("name"=> "叡山電鉄", "code" =>"763"),
		"28" => array("name"=> "京都市烏丸線", "code" =>"610"),
		"29" => array("name"=> "京都市東西線", "code" =>"992"),
		"30" => array("name"=> "嵯峨野観光鉄道", "code" =>"767"),
		"31" => array("name"=> "湖西線", "code" =>"232"),
		"32" => array("name"=> "大阪環状線", "code" =>"234"),
		"33" => array("name"=> "桜島線", "code" =>"235"),
		"34" => array("name"=> "福知山線", "code" =>"236"),
		"35" => array("name"=> "山陽本線", "code" =>"254"),
		"36" => array("name"=> "加古川線", "code" =>"257"),
		"37" => array("name"=> "播但線", "code" =>"259"),
		"38" => array("name"=> "姫新線", "code" =>"261"),
		"39" => array("name"=> "赤穂線", "code" =>"262"),
		"40" => array("name"=> "タンゴ鉄道宮福線", "code" =>"673"),
		"41" => array("name"=> "山陰本線", "code" =>"293"),
		"42" => array("name"=> "タンゴ鉄道宮津線", "code" =>"672"),
		"43" => array("name"=> "若桜鉄道", "code" =>"683"),
		"44" => array("name"=> "境線", "code" =>"299"),
		"45" => array("name"=> "草津線", "code" =>"307"),
		"46" => array("name"=> "信楽高原鉄道", "code" =>"671"),
		"47" => array("name"=> "奈良線", "code" =>"309"),
		"48" => array("name"=> "桜井線", "code" =>"310"),
		"49" => array("name"=> "片町線", "code" =>"312"),
		"50" => array("name"=> "和歌山線", "code" =>"313"),
		"51" => array("name"=> "阪和線", "code" =>"316"),
		"52" => array("name"=> "関西空港線", "code" =>"450"),
		"53" => array("name"=> "ＪＲ東西線", "code" =>"995"),
		"54" => array("name"=> "おおさか東線", "code" =>"2031"),
		"55" => array("name"=> "京阪電気鉄道京阪線", "code" =>"536"),
		"56" => array("name"=> "京阪電気鉄道交野線", "code" =>"537"),
		"57" => array("name"=> "京阪電気鉄道宇治線", "code" =>"539"),
		"58" => array("name"=> "京阪電気鉄道京津線", "code" =>"541"),
		"59" => array("name"=> "京阪電気鉄道石坂線", "code" =>"542"),
		"60" => array("name"=> "阪急電鉄京都線", "code" =>"555"),
		"61" => array("name"=> "阪急電鉄千里線", "code" =>"556"),
		"62" => array("name"=> "阪急電鉄嵐山線", "code" =>"561"),
		"63" => array("name"=> "阪急電鉄神戸線", "code" =>"545"),
		"64" => array("name"=> "阪急電鉄今津線", "code" =>"547"),
		"65" => array("name"=> "阪急電鉄伊丹線", "code" =>"548"),
		"66" => array("name"=> "阪急電鉄甲陽線", "code" =>"549"),
		"67" => array("name"=> "阪急電鉄宝塚線", "code" =>"551"),
		"68" => array("name"=> "阪急電鉄箕面線", "code" =>"552"),
		"69" => array("name"=> "阪神電鉄本線", "code" =>"565"),
		"70" => array("name"=> "阪神電鉄武庫川線", "code" =>"567"),
		"71" => array("name"=> "阪神電鉄なんば線", "code" =>"566"),
		"72" => array("name"=> "能勢電鉄", "code" =>"772"),
		"73" => array("name"=> "南海電鉄南海本線", "code" =>"520"),
		"74" => array("name"=> "南海電鉄高師浜支線", "code" =>"528"),
		"75" => array("name"=> "南海電鉄多奈川線", "code" =>"529"),
		"76" => array("name"=> "南海電鉄加太支線", "code" =>"521"),
		"77" => array("name"=> "南海電鉄高野線", "code" =>"526"),
		"78" => array("name"=> "南海電鉄和歌山港線", "code" =>"522"),
		"79" => array("name"=> "阪堺電気軌道阪堺線", "code" =>"769"),
		"80" => array("name"=> "阪堺電気軌道上町線", "code" =>"770"),
		"81" => array("name"=> "南海電鉄関西空港線", "code" =>"560"),
		"82" => array("name"=> "南海電鉄汐見橋線", "code" =>"527"),
		"83" => array("name"=> "大阪市長堀鶴見緑地", "code" =>"597"),
		"84" => array("name"=> "大阪市今里筋線", "code" =>"2025"),
		"85" => array("name"=> "大阪市御堂筋線", "code" =>"591"),
		"86" => array("name"=> "大阪市谷町線", "code" =>"592"),
		"87" => array("name"=> "大阪市四つ橋線", "code" =>"593"),
		"88" => array("name"=> "大阪市中央線", "code" =>"594"),
		"89" => array("name"=> "大阪市千日前線", "code" =>"595"),
		"90" => array("name"=> "大阪市堺筋線", "code" =>"596"),
		"91" => array("name"=> "南港ポートタウン線", "code" =>"598"),
		"92" => array("name"=> "大阪高速鉄道", "code" =>"674"),
		"93" => array("name"=> "北大阪急行南北線", "code" =>"768"),
		"94" => array("name"=> "泉北高速鉄道", "code" =>"675"),
		"95" => array("name"=> "水間鉄道水間線", "code" =>"771"),
		"96" => array("name"=> "紀州鉄道", "code" =>"783"),
		"97" => array("name"=> "わかやま貴志川線", "code" =>"530"),
		"98" => array("name"=> "神戸電鉄有馬線", "code" =>"985"),
		"99" => array("name"=> "神戸電鉄三田線", "code" =>"775"),
		"100" => array("name"=> "神戸電鉄粟生線", "code" =>"987"),
		"101" => array("name"=> "神戸電鉄公園都市線", "code" =>"777"),
		"102" => array("name"=> "山陽電気鉄道本線", "code" =>"779"),
		"103" => array("name"=> "山陽電気鉄道網干線", "code" =>"780"),
		"104" => array("name"=> "神戸高速鉄道東西線", "code" =>"978"),
		"105" => array("name"=> "神戸高速鉄道南北線", "code" =>"678"),
		"106" => array("name"=> "神戸市西神山手線", "code" =>"611"),
		"107" => array("name"=> "神戸新交通", "code" =>"679"),
		"108" => array("name"=> "北神急行電鉄", "code" =>"7778"),
		"109" => array("name"=> "六甲アイランド線", "code" =>"680"),
		"110" => array("name"=> "市営地下鉄　海岸線", "code" =>"1017"),
		"111" => array("name"=> "北条鉄道", "code" =>"682"),
		"112" => array("name"=> "神戸電鉄公園都市線", "code" =>"777"),

		"150"=> array("name"=> "ＪＲ東海道新幹線","code" =>"11"),
		"151"=> array("name"=> "ＪＲ山陽新幹線","code" =>"12"),
		"152"=> array("name"=> "ＪＲ東海道本線","code" =>"89"),
		"153"=> array("name"=> "ＪＲ東海道・山陽本線","code" =>"231"),
		"154"=> array("name"=> "ＪＲ湖西線","code" =>"232"),
		"155"=> array("name"=> "ＪＲ大阪環状線","code" =>"234"),
		"156"=> array("name"=> "ＪＲ桜島線(ゆめ咲線)","code" =>"235"),
		"157"=> array("name"=> "ＪＲ福知山線","code" =>"236"),
		"158"=> array("name"=> "ＪＲ北陸本線","code" =>"241"),
		"159"=> array("name"=> "ＪＲ小浜線","code" =>"243"),
		"160"=> array("name"=> "ＪＲ山陽本線","code" =>"254"),
		"161"=> array("name"=> "ＪＲ加古川線","code" =>"257"),
		"162"=> array("name"=> "ＪＲ播但線","code" =>"259"),
		"163"=> array("name"=> "ＪＲ姫新線","code" =>"261"),
		"164"=> array("name"=> "ＪＲ赤穂線","code" =>"262"),
		"165"=> array("name"=> "ＪＲ山陰本線","code" =>"293"),
		"166"=> array("name"=> "ＪＲ舞鶴線","code" =>"296"),
		"167"=> array("name"=> "ＪＲ関西本線","code" =>"306"),
		"168"=> array("name"=> "ＪＲ草津線","code" =>"307"),
		"169"=> array("name"=> "ＪＲ奈良線","code" =>"309"),
		"170"=> array("name"=> "ＪＲ桜井線","code" =>"310"),
		"171"=> array("name"=> "ＪＲ片町線","code" =>"312"),
		"172"=> array("name"=> "ＪＲ和歌山線","code" =>"313"),
		"173"=> array("name"=> "ＪＲ阪和線","code" =>"316"),
		"174"=> array("name"=> "ＪＲ紀勢本線","code" =>"320"),
		"175"=> array("name"=> "ＪＲ関西空港線","code" =>"450"),
		"176"=> array("name"=> "近鉄西信貴ケーブル","code" =>"469"),
		"177"=> array("name"=> "近鉄奈良線","code" =>"502"),
		"178"=> array("name"=> "近鉄生駒ケーブル線","code" =>"505"),
		"179"=> array("name"=> "南海線","code" =>"520"),
		"180"=> array("name"=> "南海加太線","code" =>"521"),
		"181"=> array("name"=> "南海和歌山港線","code" =>"522"),
		"182"=> array("name"=> "南海高野線","code" =>"526"),
		"183"=> array("name"=> "南海汐見橋線","code" =>"527"),
		"184"=> array("name"=> "南海高師浜線","code" =>"528"),
		"185"=> array("name"=> "南海多奈川線","code" =>"529"),
		"186"=> array("name"=> "和歌山電鐵貴志川線","code" =>"530"),
		"187"=> array("name"=> "南海高野山ケーブル","code" =>"531"),
		"188"=> array("name"=> "京阪本線","code" =>"536"),
		"189"=> array("name"=> "京阪交野線","code" =>"537"),
		"190"=> array("name"=> "京阪電鉄ケーブル線","code" =>"538"),
		"191"=> array("name"=> "京阪宇治線","code" =>"539"),
		"192"=> array("name"=> "京阪京津線","code" =>"541"),
		"193"=> array("name"=> "京阪石山坂本線","code" =>"542"),
		"194"=> array("name"=> "阪急神戸本線","code" =>"545"),
		"195"=> array("name"=> "阪急今津線","code" =>"547"),
		"196"=> array("name"=> "阪急伊丹線","code" =>"548"),
		"197"=> array("name"=> "阪急甲陽線","code" =>"549"),
		"198"=> array("name"=> "阪急宝塚本線","code" =>"551"),
		"199"=> array("name"=> "阪急箕面線","code" =>"552"),
		"200"=> array("name"=> "阪急京都本線","code" =>"555"),
		"201"=> array("name"=> "阪急千里線","code" =>"556"),
		"202"=> array("name"=> "南海空港線","code" =>"560"),
		"203"=> array("name"=> "阪急嵐山線","code" =>"561"),
		"204"=> array("name"=> "阪神本線","code" =>"565"),
		"205"=> array("name"=> "阪神なんば線","code" =>"566"),
		"206"=> array("name"=> "阪神武庫川線","code" =>"567"),
		"207"=> array("name"=> "大阪市営御堂筋線","code" =>"591"),
		"208"=> array("name"=> "大阪市営谷町線","code" =>"592"),
		"209"=> array("name"=> "大阪市営四つ橋線","code" =>"593"),
		"210"=> array("name"=> "大阪市営中央線","code" =>"594"),
		"211"=> array("name"=> "大阪市営千日前線","code" =>"595"),
		"212"=> array("name"=> "大阪市営堺筋線","code" =>"596"),
		"213"=> array("name"=> "大阪市営長堀鶴見緑地線","code" =>"597"),
		"214"=> array("name"=> "大阪市営南港ポートタウン線","code" =>"598"),
		"215"=> array("name"=> "京都市営烏丸線","code" =>"610"),
		"216"=> array("name"=> "神戸市西神・山手線","code" =>"611"),
		"217"=> array("name"=> "信楽高原鐵道","code" =>"671"),
		"218"=> array("name"=> "北近畿タンゴ鉄道宮津線","code" =>"672"),
		"219"=> array("name"=> "北近畿タンゴ鉄道宮福線","code" =>"673"),
		"220"=> array("name"=> "大阪モノレール","code" =>"674"),
		"221"=> array("name"=> "神戸高速南北線","code" =>"678"),
		"222"=> array("name"=> "神戸新交通ポートアイランド線","code" =>"679"),
		"223"=> array("name"=> "神戸新交通六甲アイランド線","code" =>"680"),
		"224"=> array("name"=> "近江鉄道近江本線","code" =>"762"),
		"225"=> array("name"=> "叡山電鉄叡山本線","code" =>"763"),
		"226"=> array("name"=> "叡山電鉄鞍馬線","code" =>"764"),
		"227"=> array("name"=> "京福電気鉄道嵐山本線","code" =>"765"),
		"228"=> array("name"=> "京福電気鉄道北野線","code" =>"766"),
		"229"=> array("name"=> "北大阪急行電鉄","code" =>"768"),
		"230"=> array("name"=> "水間鉄道","code" =>"771"),
		"231"=> array("name"=> "能勢電鉄妙見線","code" =>"772"),
		"232"=> array("name"=> "能勢電鉄日生線","code" =>"773"),
		"233"=> array("name"=> "神鉄三田線","code" =>"775"),
		"234"=> array("name"=> "神鉄公園都市線","code" =>"777"),
		"235"=> array("name"=> "山陽電鉄本線","code" =>"779"),
		"236"=> array("name"=> "山陽電鉄網干線","code" =>"780"),
		"237"=> array("name"=> "神戸高速東西線","code" =>"978"),
		"238"=> array("name"=> "神鉄有馬線","code" =>"985"),
		"239"=> array("name"=> "神鉄粟生線","code" =>"987"),
		"240"=> array("name"=> "京都地下鉄東西線","code" =>"992"),
		"241"=> array("name"=> "国際文化公園都市モノレール","code" =>"1005"),
		"242"=> array("name"=> "神戸市海岸線","code" =>"1017"),
		"243"=> array("name"=> "智頭急行","code" =>"1021"),
		"244"=> array("name"=> "近鉄難波線","code" =>"2007"),
		"245"=> array("name"=> "京阪電鉄中之島線","code" =>"2033")

	);





	//h_rains csv format
	$work_h_rains_rosen =
	array(	
		"1"  => array("name"=> "東海新幹線","code" =>"11"),
		"2"  => array("name"=> "東北新幹線","code" =>"15"),
		"3"  => array("name"=> "上越新幹線","code" =>"17"),
		"4"  => array("name"=> "長野新幹線","code" =>"21"),
		"5"  => array("name"=> "東海道線","code" =>"89"),
		"6"  => array("name"=> "山手線","code" =>"91"),
		"7"  => array("name"=> "京浜東北線","code" =>"93"),
		"8"  => array("name"=> "横須賀線","code" =>"94"),
		"9"  => array("name"=> "埼京線","code" =>"95"),
		"10" => array("name"=> "川越線","code" =>"97"),
		"11" => array("name"=> "南武線","code" =>"99"),
		"12" => array("name"=> "鶴見線","code" =>"100"),
		"13" => array("name"=> "武蔵野線","code" =>"103"),
		"14" => array("name"=> "横浜線","code" =>"105"),
		"15" => array("name"=> "相模線","code" =>"107"),
		"16" => array("name"=> "中央線","code" =>"114"),
		"17" => array("name"=> "青梅線","code" =>"115"),
		"18" => array("name"=> "五日市線","code" =>"116"),
		"19" => array("name"=> "八高線","code" =>"117"),
		"20" => array("name"=> "小海線","code" =>"118"),
		"21" => array("name"=> "篠ノ井線","code" =>"119"),
		"22" => array("name"=> "大糸線","code" =>"120"),
	//	"23" => array("name"=> "宇都宮線","code" =>"121"),
		"24" => array("name"=> "東北線","code" =>"121"),
		"25" => array("name"=> "常磐緩行線","code" =>"129"),
		"26" => array("name"=> "常磐線","code" =>"129"),
		"27" => array("name"=> "水郡線","code" =>"135"),
		"28" => array("name"=> "高崎線","code" =>"137"),
		"29" => array("name"=> "上越線","code" =>"138"),
		"30" => array("name"=> "吾妻線","code" =>"140"),
		"31" => array("name"=> "両毛線","code" =>"141"),
		"32" => array("name"=> "水戸線","code" =>"142"),
		"33" => array("name"=> "日光線","code" =>"143"),
		"34" => array("name"=> "烏山線","code" =>"144"),
		"35" => array("name"=> "磐越西線","code" =>"162"),
		"36" => array("name"=> "只見線","code" =>"163"),
		"37" => array("name"=> "米坂線","code" =>"172"),
		"38" => array("name"=> "羽越線","code" =>"179"),
		"39" => array("name"=> "白新線","code" =>"180"),
		"40" => array("name"=> "信越線","code" =>"184"),
		"41" => array("name"=> "しなの鉄道","code" =>"185"),
		"42" => array("name"=> "飯山線","code" =>"189"),
		"43" => array("name"=> "越後線","code" =>"191"),
		"44" => array("name"=> "弥彦線","code" =>"193"),
		"45" => array("name"=> "総武中央線","code" =>"194"),
		"46" => array("name"=> "総武線","code" =>"196"),
		"47" => array("name"=> "外房線","code" =>"197"),
		"48" => array("name"=> "内房線","code" =>"198"),
		"49" => array("name"=> "京葉線","code" =>"199"),
		"50" => array("name"=> "成田線","code" =>"202"),
		"51" => array("name"=> "鹿島線","code" =>"204"),
		"52" => array("name"=> "久留里線","code" =>"205"),
		"53" => array("name"=> "東金線","code" =>"206"),
		"54" => array("name"=> "御殿場線","code" =>"212"),
		"55" => array("name"=> "身延線","code" =>"213"),
		"56" => array("name"=> "飯田線","code" =>"216"),
		"57" => array("name"=> "北陸線","code" =>"241"),
		"58" => array("name"=> "西武池袋線","code" =>"429"),
		"59" => array("name"=> "池袋秩父線","code" =>"433"),
		"60" => array("name"=> "西武有楽町","code" =>"434"),
		"62" => array("name"=> "西武狭山線","code" =>"436"),
		"63" => array("name"=> "西武新宿線","code" =>"437"),
		"64" => array("name"=> "西武拝島線","code" =>"440"),
		"65" => array("name"=> "多摩湖線","code" =>"442"),
		"66" => array("name"=> "国分寺線","code" =>"443"),
		"68" => array("name"=> "西武山口線","code" =>"445"),
		"69" => array("name"=> "西武多摩川","code" =>"446"),
		"70" => array("name"=> "銀座線","code" =>"576"),
		"71" => array("name"=> "丸ノ内線","code" =>"577"),
		"72" => array("name"=> "丸ノ内方南","code" =>"577"),
		"73" => array("name"=> "日比谷線","code" =>"579"),
		"74" => array("name"=> "東西線","code" =>"580"),
		"75" => array("name"=> "千代田線","code" =>"582"),
		"76" => array("name"=> "有楽町線","code" =>"583"),
		"77" => array("name"=> "半蔵門線","code" =>"584"),
		"78" => array("name"=> "南北線","code" =>"585"),
		"79" => array("name"=> "都営浅草線","code" =>"586"),
		"80" => array("name"=> "都営三田線","code" =>"587"),
		"81" => array("name"=> "都営新宿線","code" =>"588"),
		"82" => array("name"=> "大江戸線","code" =>"589"),
		"83" => array("name"=> "都電荒川線","code" =>"590"),
		"84" => array("name"=> "横浜ブルー","code" =>"604"),
		"85" => array("name"=> "野岩鉄道","code" =>"623"),
		"86" => array("name"=> "秩父鉄道","code" =>"625"),
		"87" => array("name"=> "箱根登山線","code" =>"629"),
		"88" => array("name"=> "大洗鹿島線","code" =>"652"),
		"89" => array("name"=> "真岡鉄道","code" =>"654"),
		"90" => array("name"=> "わたらせ線","code" =>"655"),
		"91" => array("name"=> "伊奈線","code" =>"656"),
		"92" => array("name"=> "千葉モノレ","code" =>"657"),
		"93" => array("name"=> "いすみ鉄道","code" =>"658"),
		"94" => array("name"=> "シーサイド","code" =>"659"),
		"95" => array("name"=> "ひたち海浜","code" =>"706"),
		"96" => array("name"=> "関鉄常総線","code" =>"708"),
		"97" => array("name"=> "竜ヶ崎線","code" =>"709"),
		"98" => array("name"=> "上毛電鉄","code" =>"710"),
		"99" => array("name"=> "上信電鉄","code" =>"711"),
		"100" => array("name"=> "流山線","code" =>"713"),
		"101" => array("name"=> "新京成線","code" =>"714"),
		"102" => array("name"=> "北総線","code" =>"715"),
		"103" => array("name"=> "ユーカリ線","code" =>"716"),
		"104" => array("name"=> "小湊鐵道","code" =>"717"),
		"105" => array("name"=> "銚子電鉄","code" =>"718"),
		"106" => array("name"=> "京成千原線","code" =>"719"),
		"107" => array("name"=> "東京モノレ","code" =>"720"),
		"108" => array("name"=> "湘南モノレ","code" =>"721"),
		"109" => array("name"=> "江ノ電","code" =>"722"),
		"110" => array("name"=> "大雄山線","code" =>"723"),
		"111" => array("name"=> "富士急","code" =>"744"),
		"112" => array("name"=> "長電屋代線","code" =>"745"),
		"113" => array("name"=> "長電長野線","code" =>"746"),
		"114" => array("name"=> "上田電鉄","code" =>"748"),
		"115" => array("name"=> "松本電鉄","code" =>"749"),
		"116" => array("name"=> "伊勢崎線","code" =>"830"),
		"117" => array("name"=> "東武日光線","code" =>"835"),
		"118" => array("name"=> "宇都宮線","code" =>"836"),
		"119" => array("name"=> "鬼怒川線","code" =>"837"),
		"120" => array("name"=> "東武佐野線","code" =>"839"),
		"121" => array("name"=> "東武桐生線","code" =>"840"),
		"122" => array("name"=> "東武小泉線","code" =>"841"),
		"123" => array("name"=> "東武亀戸線","code" =>"843"),
		"124" => array("name"=> "大師線","code" =>"844"),
		"125" => array("name"=> "東武野田線","code" =>"845"),
		"126" => array("name"=> "東武東上線","code" =>"848"),
		"127" => array("name"=> "東武越生線","code" =>"851"),
		"128" => array("name"=> "京成本線","code" =>"855"),
		"129" => array("name"=> "京成押上線","code" =>"858"),
		"130" => array("name"=> "京成金町線","code" =>"860"),
		"131" => array("name"=> "京成千葉線","code" =>"861"),
		"132" => array("name"=> "京王線","code" =>"863"),
		"133" => array("name"=> "京王高尾線","code" =>"864"),
		"134" => array("name"=> "相模原線","code" =>"865"),
		"137" => array("name"=> "井の頭線","code" =>"871"),
		"138" => array("name"=> "小田急線","code" =>"873"),
		"139" => array("name"=> "江ノ島線","code" =>"877"),
		"140" => array("name"=> "多摩線","code" =>"879"),
		"141" => array("name"=> "東横線","code" =>"882"),

		"142" => array("name"=> "田園都市線","code" =>"885"),

		"143" => array("name"=> "目黒線","code" =>"889"),
		"144" => array("name"=> "大井町線","code" =>"890"),
		"145" => array("name"=> "池上線","code" =>"891"),

		"147" => array("name"=> "世田谷線","code" =>"893"),
		"148" => array("name"=> "京浜急行線","code" =>"894"),
		"149" => array("name"=> "久里浜線","code" =>"895"),
		"150" => array("name"=> "京急逗子線","code" =>"896"),
		"151" => array("name"=> "京急空港線","code" =>"900"),
		"152" => array("name"=> "京急大師線","code" =>"901"),
		"153" => array("name"=> "いずみ野線","code" =>"902"),
		"154" => array("name"=> "相鉄線","code" =>"903"),
		"155" => array("name"=> "東葉高速線","code" =>"988"),
		"156" => array("name"=> "多摩モノレ","code" =>"1000"),
		"157" => array("name"=> "ゆりかもめ","code" =>"1001"),
		"158" => array("name"=> "りんかい線","code" =>"1002"),
		"159" => array("name"=> "東急多摩川","code" =>"1013"),
		"160" => array("name"=> "埼玉高速線","code" =>"1014"),
		"161" => array("name"=> "ディズニー","code" =>"1016"),
		"162" => array("name"=> "芝山鉄道","code" =>"1019"),
		"163" => array("name"=> "ほくほく線","code" =>"1020"),
		"164" => array("name"=> "みなとＭ線","code" =>"2013"),
		"165" => array("name"=> "つくばＥＸ","code" =>"2021"),
		"166" => array("name"=> "箱根鋼索線","code" =>"2022"),
		"167" => array("name"=> "日暮里舎人","code" =>"2029"),
		"168" => array("name"=> "横浜グリー","code" =>"2030"),
		"169" => array("name"=> "副都心線","code" =>"2032")
	);




	//c21 csv format
	$work_c21_rosen =
	array(	

		"1" => array( "ken_id" => "1","rosen_id" => "378","rosen_name" => "函館本線"),
		"2" => array( "ken_id" => "1","rosen_id" => "380","rosen_name" => "海峡線"),
		"3" => array( "ken_id" => "1","rosen_id" => "381","rosen_name" => "江差線"),
		"4" => array( "ken_id" => "1","rosen_id" => "383","rosen_name" => "札沼線"),
		"5" => array( "ken_id" => "1","rosen_id" => "385","rosen_name" => "千歳線"),
		"6" => array( "ken_id" => "1","rosen_id" => "388","rosen_name" => "石勝線"),
		"7" => array( "ken_id" => "1","rosen_id" => "392","rosen_name" => "室蘭本線"),
		"8" => array( "ken_id" => "1","rosen_id" => "394","rosen_name" => "日高本線"),
		"9" => array( "ken_id" => "1","rosen_id" => "395","rosen_name" => "留萌本線"),
		"10" => array( "ken_id" => "1","rosen_id" => "400","rosen_name" => "根室本線"),
		"11" => array( "ken_id" => "1","rosen_id" => "401","rosen_name" => "富良野線"),
		"12" => array( "ken_id" => "1","rosen_id" => "402","rosen_name" => "宗谷本線"),
		"13" => array( "ken_id" => "1","rosen_id" => "404","rosen_name" => "石北本線"),
		"14" => array( "ken_id" => "1","rosen_id" => "408","rosen_name" => "釧網本線"),
		"15" => array( "ken_id" => "1","rosen_id" => "599","rosen_name" => "札幌市南北線"),
		"16" => array( "ken_id" => "1","rosen_id" => "600","rosen_name" => "札幌市東西線"),
		"17" => array( "ken_id" => "1","rosen_id" => "601","rosen_name" => "札幌市東豊線"),
		"18" => array( "ken_id" => "1","rosen_id" => "602","rosen_name" => "札幌市軌道線"),
		"19" => array( "ken_id" => "1","rosen_id" => "602","rosen_name" => "札幌市電"),
		"20" => array( "ken_id" => "1","rosen_id" => "614","rosen_name" => "函館市電本線湯川線"),
		"21" => array( "ken_id" => "1","rosen_id" => "614","rosen_name" => "函館市電宝来谷地頭"),
		"22" => array( "ken_id" => "2","rosen_id" => "15","rosen_name" => "東北新幹線"),
		"23" => array( "ken_id" => "2","rosen_id" => "121","rosen_name" => "東北本線"),
		"24" => array( "ken_id" => "2","rosen_id" => "121","rosen_name" => "東北線"),
		"25" => array( "ken_id" => "2","rosen_id" => "158","rosen_name" => "八戸線"),
		"26" => array( "ken_id" => "2","rosen_id" => "159","rosen_name" => "大湊線"),
		"27" => array( "ken_id" => "2","rosen_id" => "171","rosen_name" => "奥羽本線"),
		"28" => array( "ken_id" => "2","rosen_id" => "175","rosen_name" => "五能線"),
		"29" => array( "ken_id" => "2","rosen_id" => "176","rosen_name" => "津軽線"),
		"30" => array( "ken_id" => "2","rosen_id" => "380","rosen_name" => "海峡線"),
		"31" => array( "ken_id" => "2","rosen_id" => "695","rosen_name" => "津軽鉄道"),
		"32" => array( "ken_id" => "2","rosen_id" => "697","rosen_name" => "弘南鉄道弘南線"),
		"33" => array( "ken_id" => "2","rosen_id" => "698","rosen_name" => "弘南鉄道大鰐線"),
		"34" => array( "ken_id" => "2","rosen_id" => "701","rosen_name" => "十和田観光電鉄"),
		"35" => array( "ken_id" => "2","rosen_id" => "1023","rosen_name" => "いわて銀河鉄道"),
		"36" => array( "ken_id" => "2","rosen_id" => "1024","rosen_name" => "青い森鉄道"),
		"37" => array( "ken_id" => "3","rosen_id" => "15","rosen_name" => "東北新幹線"),
		"38" => array( "ken_id" => "3","rosen_id" => "23","rosen_name" => "秋田新幹線"),
		"39" => array( "ken_id" => "3","rosen_id" => "121","rosen_name" => "東北線"),
		"40" => array( "ken_id" => "3","rosen_id" => "121","rosen_name" => "東北本線"),
		"41" => array( "ken_id" => "3","rosen_id" => "149","rosen_name" => "大船渡線"),
		"42" => array( "ken_id" => "3","rosen_id" => "150","rosen_name" => "北上線"),
		"43" => array( "ken_id" => "3","rosen_id" => "151","rosen_name" => "釜石線"),
		"44" => array( "ken_id" => "3","rosen_id" => "152","rosen_name" => "田沢湖線"),
		"45" => array( "ken_id" => "3","rosen_id" => "155","rosen_name" => "山田線"),
		"46" => array( "ken_id" => "3","rosen_id" => "156","rosen_name" => "岩泉線"),
		"47" => array( "ken_id" => "3","rosen_id" => "157","rosen_name" => "花輪線"),
		"48" => array( "ken_id" => "3","rosen_id" => "158","rosen_name" => "八戸線"),
		"49" => array( "ken_id" => "3","rosen_id" => "645","rosen_name" => "三陸鉄道南リアス線"),
		"50" => array( "ken_id" => "3","rosen_id" => "646","rosen_name" => "三陸鉄道北リアス線"),
		"51" => array( "ken_id" => "3","rosen_id" => "1023","rosen_name" => "いわて銀河鉄道"),
		"52" => array( "ken_id" => "4","rosen_id" => "15","rosen_name" => "東北新幹線"),
		"53" => array( "ken_id" => "4","rosen_id" => "121","rosen_name" => "東北本線"),
		"54" => array( "ken_id" => "4","rosen_id" => "121","rosen_name" => "東北線"),
		"55" => array( "ken_id" => "4","rosen_id" => "129","rosen_name" => "常磐線"),
		"56" => array( "ken_id" => "4","rosen_id" => "129","rosen_name" => "千代田・常磐緩行線"),
		"57" => array( "ken_id" => "4","rosen_id" => "145","rosen_name" => "仙山線"),
		"58" => array( "ken_id" => "4","rosen_id" => "146","rosen_name" => "仙石線"),
		"59" => array( "ken_id" => "4","rosen_id" => "147","rosen_name" => "石巻線"),
		"60" => array( "ken_id" => "4","rosen_id" => "148","rosen_name" => "気仙沼線"),
		"61" => array( "ken_id" => "4","rosen_id" => "149","rosen_name" => "大船渡線"),
		"62" => array( "ken_id" => "4","rosen_id" => "181","rosen_name" => "陸羽東線"),
		"63" => array( "ken_id" => "4","rosen_id" => "603","rosen_name" => "仙台市地下鉄南北線"),
		"64" => array( "ken_id" => "4","rosen_id" => "650","rosen_name" => "阿武隈急行"),
		"65" => array( "ken_id" => "4","rosen_id" => "2026","rosen_name" => "仙台空港鉄道"),
		"66" => array( "ken_id" => "5","rosen_id" => "23","rosen_name" => "秋田新幹線"),
		"67" => array( "ken_id" => "5","rosen_id" => "150","rosen_name" => "北上線"),
		"68" => array( "ken_id" => "5","rosen_id" => "152","rosen_name" => "田沢湖線"),
		"69" => array( "ken_id" => "5","rosen_id" => "157","rosen_name" => "花輪線"),
		"70" => array( "ken_id" => "5","rosen_id" => "171","rosen_name" => "奥羽本線"),
		"71" => array( "ken_id" => "5","rosen_id" => "174","rosen_name" => "男鹿線"),
		"72" => array( "ken_id" => "5","rosen_id" => "175","rosen_name" => "五能線"),
		"73" => array( "ken_id" => "5","rosen_id" => "179","rosen_name" => "羽越線"),
		"74" => array( "ken_id" => "5","rosen_id" => "179","rosen_name" => "羽越本線"),
		"75" => array( "ken_id" => "5","rosen_id" => "647","rosen_name" => "秋田内陸縦貫鉄道"),
		"76" => array( "ken_id" => "5","rosen_id" => "648","rosen_name" => "由利高原鉄道"),
		"77" => array( "ken_id" => "6","rosen_id" => "19","rosen_name" => "山形新幹線"),
		"78" => array( "ken_id" => "6","rosen_id" => "145","rosen_name" => "仙山線"),
		"79" => array( "ken_id" => "6","rosen_id" => "171","rosen_name" => "奥羽本線"),
		"80" => array( "ken_id" => "6","rosen_id" => "172","rosen_name" => "米坂線"),
		"81" => array( "ken_id" => "6","rosen_id" => "173","rosen_name" => "左沢線"),
		"82" => array( "ken_id" => "6","rosen_id" => "179","rosen_name" => "羽越本線"),
		"83" => array( "ken_id" => "6","rosen_id" => "179","rosen_name" => "羽越線"),
		"84" => array( "ken_id" => "6","rosen_id" => "181","rosen_name" => "陸羽東線"),
		"85" => array( "ken_id" => "6","rosen_id" => "183","rosen_name" => "陸羽西線"),
		"86" => array( "ken_id" => "6","rosen_id" => "649","rosen_name" => "山鉄フラワー長井線"),
		"87" => array( "ken_id" => "7","rosen_id" => "15","rosen_name" => "東北新幹線"),
		"88" => array( "ken_id" => "7","rosen_id" => "19","rosen_name" => "山形新幹線"),
		"89" => array( "ken_id" => "7","rosen_id" => "121","rosen_name" => "東北本線"),
		"90" => array( "ken_id" => "7","rosen_id" => "121","rosen_name" => "東北線"),
		"91" => array( "ken_id" => "7","rosen_id" => "129","rosen_name" => "常磐線"),
		"92" => array( "ken_id" => "7","rosen_id" => "129","rosen_name" => "千代田・常磐緩行線"),
		"93" => array( "ken_id" => "7","rosen_id" => "135","rosen_name" => "水郡線"),
		"94" => array( "ken_id" => "7","rosen_id" => "160","rosen_name" => "磐越東線"),
		"95" => array( "ken_id" => "7","rosen_id" => "162","rosen_name" => "磐越西線"),
		"96" => array( "ken_id" => "7","rosen_id" => "163","rosen_name" => "只見線"),
		"97" => array( "ken_id" => "7","rosen_id" => "171","rosen_name" => "奥羽本線"),
		"98" => array( "ken_id" => "7","rosen_id" => "622","rosen_name" => "会津鉄道"),
		"99" => array( "ken_id" => "7","rosen_id" => "623","rosen_name" => "野岩鉄道会津鬼怒川"),
		"100" => array( "ken_id" => "7","rosen_id" => "650","rosen_name" => "阿武隈急行"),
		"101" => array( "ken_id" => "7","rosen_id" => "704","rosen_name" => "福島交通飯坂線"),
		"102" => array( "ken_id" => "8","rosen_id" => "121","rosen_name" => "東北線"),
		"103" => array( "ken_id" => "8","rosen_id" => "121","rosen_name" => "東北本線"),
		"104" => array( "ken_id" => "8","rosen_id" => "129","rosen_name" => "常磐線"),
		"105" => array( "ken_id" => "8","rosen_id" => "129","rosen_name" => "千代田・常磐緩行線"),
		"106" => array( "ken_id" => "8","rosen_id" => "135","rosen_name" => "水郡線"),
		"107" => array( "ken_id" => "8","rosen_id" => "142","rosen_name" => "水戸線"),
		"108" => array( "ken_id" => "8","rosen_id" => "204","rosen_name" => "鹿島線"),
		"109" => array( "ken_id" => "8","rosen_id" => "652","rosen_name" => "鹿島臨海鉄道"),
		"110" => array( "ken_id" => "8","rosen_id" => "654","rosen_name" => "真岡鉄道"),
		"111" => array( "ken_id" => "8","rosen_id" => "706","rosen_name" => "ひたちなか海浜鉄道"),
		"112" => array( "ken_id" => "8","rosen_id" => "708","rosen_name" => "関東鉄道常総線"),
		"113" => array( "ken_id" => "8","rosen_id" => "709","rosen_name" => "関東鉄道竜ヶ崎線"),
		"114" => array( "ken_id" => "8","rosen_id" => "2021","rosen_name" => "つくばエクスプレス"),
		"115" => array( "ken_id" => "8","rosen_id" => "2035","rosen_name" => "湘南新宿宇"),
		"116" => array( "ken_id" => "9","rosen_id" => "15","rosen_name" => "東北新幹線"),
		"117" => array( "ken_id" => "9","rosen_id" => "121","rosen_name" => "東北本線"),
		"118" => array( "ken_id" => "9","rosen_id" => "121","rosen_name" => "東北線"),
		"119" => array( "ken_id" => "9","rosen_id" => "141","rosen_name" => "両毛線"),
		"120" => array( "ken_id" => "9","rosen_id" => "142","rosen_name" => "水戸線"),
		"121" => array( "ken_id" => "9","rosen_id" => "143","rosen_name" => "日光線"),
		"122" => array( "ken_id" => "9","rosen_id" => "144","rosen_name" => "烏山線"),
		"123" => array( "ken_id" => "9","rosen_id" => "623","rosen_name" => "野岩鉄道会津鬼怒川"),
		"124" => array( "ken_id" => "9","rosen_id" => "654","rosen_name" => "真岡鉄道"),
		"125" => array( "ken_id" => "9","rosen_id" => "655","rosen_name" => "わたらせ渓谷鉄道"),
		"126" => array( "ken_id" => "9","rosen_id" => "830","rosen_name" => "東武伊勢崎・大師線"),
		"127" => array( "ken_id" => "9","rosen_id" => "835","rosen_name" => "東武鉄道日光線"),
		"128" => array( "ken_id" => "9","rosen_id" => "836","rosen_name" => "東武鉄道宇都宮線"),
		"129" => array( "ken_id" => "9","rosen_id" => "837","rosen_name" => "東武鉄道鬼怒川線"),
		"130" => array( "ken_id" => "9","rosen_id" => "839","rosen_name" => "東武鉄道佐野線"),
		"131" => array( "ken_id" => "9","rosen_id" => "2035","rosen_name" => "湘南新宿宇"),
		"132" => array( "ken_id" => "10","rosen_id" => "17","rosen_name" => "上越新幹線"),
		"133" => array( "ken_id" => "10","rosen_id" => "21","rosen_name" => "長野新幹線"),
		"134" => array( "ken_id" => "10","rosen_id" => "117","rosen_name" => "八高線"),
		"135" => array( "ken_id" => "10","rosen_id" => "137","rosen_name" => "高崎線"),
		"136" => array( "ken_id" => "10","rosen_id" => "138","rosen_name" => "上越線"),
		"137" => array( "ken_id" => "10","rosen_id" => "140","rosen_name" => "吾妻線"),
		"138" => array( "ken_id" => "10","rosen_id" => "141","rosen_name" => "両毛線"),
		"139" => array( "ken_id" => "10","rosen_id" => "184","rosen_name" => "信越線"),
		"140" => array( "ken_id" => "10","rosen_id" => "184","rosen_name" => "信越本線"),
		"141" => array( "ken_id" => "10","rosen_id" => "655","rosen_name" => "わたらせ渓谷鉄道"),
		"142" => array( "ken_id" => "10","rosen_id" => "710","rosen_name" => "上毛電気鉄道上毛線"),
		"143" => array( "ken_id" => "10","rosen_id" => "711","rosen_name" => "上信電鉄上信線"),
		"144" => array( "ken_id" => "10","rosen_id" => "830","rosen_name" => "東武伊勢崎・大師線"),
		"145" => array( "ken_id" => "10","rosen_id" => "835","rosen_name" => "東武鉄道日光線"),
		"146" => array( "ken_id" => "10","rosen_id" => "839","rosen_name" => "東武鉄道佐野線"),
		"147" => array( "ken_id" => "10","rosen_id" => "840","rosen_name" => "東武鉄道桐生線"),
		"148" => array( "ken_id" => "10","rosen_id" => "841","rosen_name" => "東武鉄道小泉線"),
		"149" => array( "ken_id" => "10","rosen_id" => "2036","rosen_name" => "湘南新宿高"),
		"150" => array( "ken_id" => "11","rosen_id" => "15","rosen_name" => "東北新幹線"),
		"151" => array( "ken_id" => "11","rosen_id" => "17","rosen_name" => "上越新幹線"),
		"152" => array( "ken_id" => "11","rosen_id" => "21","rosen_name" => "長野新幹線"),
		"153" => array( "ken_id" => "11","rosen_id" => "93","rosen_name" => "根岸線"),
		"154" => array( "ken_id" => "11","rosen_id" => "93","rosen_name" => "京浜東北・根岸線"),
		"155" => array( "ken_id" => "11","rosen_id" => "95","rosen_name" => "埼京線"),
		"156" => array( "ken_id" => "11","rosen_id" => "97","rosen_name" => "川越線"),
		"157" => array( "ken_id" => "11","rosen_id" => "103","rosen_name" => "武蔵野線"),
		"158" => array( "ken_id" => "11","rosen_id" => "117","rosen_name" => "八高線"),
		"159" => array( "ken_id" => "11","rosen_id" => "121","rosen_name" => "東北線"),
		"160" => array( "ken_id" => "11","rosen_id" => "121","rosen_name" => "東北本線"),
		"161" => array( "ken_id" => "11","rosen_id" => "137","rosen_name" => "高崎線"),
		"162" => array( "ken_id" => "11","rosen_id" => "429","rosen_name" => "西武池袋・豊島線"),
		"163" => array( "ken_id" => "11","rosen_id" => "433","rosen_name" => "西武池袋西武秩父線"),
		"164" => array( "ken_id" => "11","rosen_id" => "436","rosen_name" => "西武鉄道狭山線"),
		"165" => array( "ken_id" => "11","rosen_id" => "437","rosen_name" => "西武鉄道新宿線"),
		"166" => array( "ken_id" => "11","rosen_id" => "445","rosen_name" => "西武鉄道山口線"),
		"167" => array( "ken_id" => "11","rosen_id" => "583","rosen_name" => "東京地下鉄有楽町線"),
		"168" => array( "ken_id" => "11","rosen_id" => "589","rosen_name" => "東京都大江戸線"),
		"169" => array( "ken_id" => "11","rosen_id" => "625","rosen_name" => "秩父鉄道本線"),
		"170" => array( "ken_id" => "11","rosen_id" => "656","rosen_name" => "埼玉新都市交通"),
		"171" => array( "ken_id" => "11","rosen_id" => "830","rosen_name" => "東武伊勢崎・大師線"),
		"172" => array( "ken_id" => "11","rosen_id" => "835","rosen_name" => "東武鉄道日光線"),
		"173" => array( "ken_id" => "11","rosen_id" => "845","rosen_name" => "東武鉄道野田線"),
		"174" => array( "ken_id" => "11","rosen_id" => "848","rosen_name" => "東武鉄道東上線"),
		"175" => array( "ken_id" => "11","rosen_id" => "851","rosen_name" => "東武鉄道越生線"),
		"176" => array( "ken_id" => "11","rosen_id" => "1014","rosen_name" => "埼玉高速鉄道"),
		"177" => array( "ken_id" => "11","rosen_id" => "2021","rosen_name" => "つくばエクスプレス"),
		"178" => array( "ken_id" => "11","rosen_id" => "2032","rosen_name" => "東京地下鉄副都心線"),
		"179" => array( "ken_id" => "11","rosen_id" => "2032","rosen_name" => "副都心線"),
		"180" => array( "ken_id" => "11","rosen_id" => "2035","rosen_name" => "湘南新宿宇"),
		"181" => array( "ken_id" => "11","rosen_id" => "2036","rosen_name" => "湘南新宿高"),
		"182" => array( "ken_id" => "12","rosen_id" => "103","rosen_name" => "武蔵野線"),
		"183" => array( "ken_id" => "12","rosen_id" => "129","rosen_name" => "常磐線"),
		"184" => array( "ken_id" => "12","rosen_id" => "129","rosen_name" => "千代田・常磐緩行線"),
		"185" => array( "ken_id" => "12","rosen_id" => "194","rosen_name" => "総武線"),
		"186" => array( "ken_id" => "12","rosen_id" => "194","rosen_name" => "総武・中央緩行線"),
		"187" => array( "ken_id" => "12","rosen_id" => "196","rosen_name" => "総武本線"),
		"188" => array( "ken_id" => "12","rosen_id" => "197","rosen_name" => "外房線"),
		"189" => array( "ken_id" => "12","rosen_id" => "198","rosen_name" => "内房線"),
		"190" => array( "ken_id" => "12","rosen_id" => "199","rosen_name" => "京葉線"),
		"191" => array( "ken_id" => "12","rosen_id" => "202","rosen_name" => "成田線"),
		"192" => array( "ken_id" => "12","rosen_id" => "204","rosen_name" => "鹿島線"),
		"193" => array( "ken_id" => "12","rosen_id" => "205","rosen_name" => "久留里線"),
		"194" => array( "ken_id" => "12","rosen_id" => "206","rosen_name" => "東金線"),
		"195" => array( "ken_id" => "12","rosen_id" => "580","rosen_name" => "東京地下鉄東西線"),
		"196" => array( "ken_id" => "12","rosen_id" => "588","rosen_name" => "東京都新宿線"),
		"197" => array( "ken_id" => "12","rosen_id" => "657","rosen_name" => "千葉都市モノレール"),
		"198" => array( "ken_id" => "12","rosen_id" => "658","rosen_name" => "いすみ鉄道"),
		"199" => array( "ken_id" => "12","rosen_id" => "713","rosen_name" => "流鉄流山線"),
		"200" => array( "ken_id" => "12","rosen_id" => "714","rosen_name" => "新京成電鉄"),
		"201" => array( "ken_id" => "12","rosen_id" => "715","rosen_name" => "北総鉄道"),
		"202" => array( "ken_id" => "12","rosen_id" => "716","rosen_name" => "山万ユーカリが丘線"),
		"203" => array( "ken_id" => "12","rosen_id" => "717","rosen_name" => "小湊鐵道"),
		"204" => array( "ken_id" => "12","rosen_id" => "718","rosen_name" => "銚子電気鉄道"),
		"205" => array( "ken_id" => "12","rosen_id" => "719","rosen_name" => "京成電鉄千原線"),
		"206" => array( "ken_id" => "12","rosen_id" => "845","rosen_name" => "東武鉄道野田線"),
		"207" => array( "ken_id" => "12","rosen_id" => "855","rosen_name" => "京成電鉄本・空港線"),
		"208" => array( "ken_id" => "12","rosen_id" => "861","rosen_name" => "京成電鉄千葉線"),
		"209" => array( "ken_id" => "12","rosen_id" => "988","rosen_name" => "東葉高速鉄道"),
		"210" => array( "ken_id" => "12","rosen_id" => "1016","rosen_name" => "ディズニーリゾート"),
		"211" => array( "ken_id" => "12","rosen_id" => "1019","rosen_name" => "芝山鉄道"),
		"212" => array( "ken_id" => "12","rosen_id" => "2021","rosen_name" => "つくばエクスプレス"),
		"213" => array( "ken_id" => "13","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"214" => array( "ken_id" => "13","rosen_id" => "15","rosen_name" => "東北新幹線"),
		"215" => array( "ken_id" => "13","rosen_id" => "17","rosen_name" => "上越新幹線"),
		"216" => array( "ken_id" => "13","rosen_id" => "21","rosen_name" => "長野新幹線"),
		"217" => array( "ken_id" => "13","rosen_id" => "89","rosen_name" => "東海道本線"),
		"218" => array( "ken_id" => "13","rosen_id" => "91","rosen_name" => "山手線"),
		"219" => array( "ken_id" => "13","rosen_id" => "93","rosen_name" => "根岸線"),
		"220" => array( "ken_id" => "13","rosen_id" => "93","rosen_name" => "京浜東北・根岸線"),
		"221" => array( "ken_id" => "13","rosen_id" => "94","rosen_name" => "横須賀線"),
		"222" => array( "ken_id" => "13","rosen_id" => "95","rosen_name" => "埼京線"),
		"223" => array( "ken_id" => "13","rosen_id" => "99","rosen_name" => "南武線"),
		"224" => array( "ken_id" => "13","rosen_id" => "103","rosen_name" => "武蔵野線"),
		"225" => array( "ken_id" => "13","rosen_id" => "105","rosen_name" => "横浜線"),
		"226" => array( "ken_id" => "13","rosen_id" => "110","rosen_name" => "中央線"),
		"227" => array( "ken_id" => "13","rosen_id" => "114","rosen_name" => "中央本線"),
		"228" => array( "ken_id" => "13","rosen_id" => "115","rosen_name" => "青梅線"),
		"229" => array( "ken_id" => "13","rosen_id" => "116","rosen_name" => "五日市線"),
		"230" => array( "ken_id" => "13","rosen_id" => "117","rosen_name" => "八高線"),
		"231" => array( "ken_id" => "13","rosen_id" => "121","rosen_name" => "東北本線"),
		"232" => array( "ken_id" => "13","rosen_id" => "121","rosen_name" => "東北線"),
		"233" => array( "ken_id" => "13","rosen_id" => "129","rosen_name" => "千代田・常磐緩行線"),
		"234" => array( "ken_id" => "13","rosen_id" => "129","rosen_name" => "常磐線"),
		"235" => array( "ken_id" => "13","rosen_id" => "137","rosen_name" => "高崎線"),
		"236" => array( "ken_id" => "13","rosen_id" => "194","rosen_name" => "総武線"),
		"237" => array( "ken_id" => "13","rosen_id" => "194","rosen_name" => "総武・中央緩行線"),
		"238" => array( "ken_id" => "13","rosen_id" => "196","rosen_name" => "総武本線"),
		"239" => array( "ken_id" => "13","rosen_id" => "199","rosen_name" => "京葉線"),
		"240" => array( "ken_id" => "13","rosen_id" => "429","rosen_name" => "西武池袋・豊島線"),
		"241" => array( "ken_id" => "13","rosen_id" => "434","rosen_name" => "西武有楽町線"),
		"242" => array( "ken_id" => "13","rosen_id" => "435","rosen_name" => "西武池袋・豊島線"),
		"243" => array( "ken_id" => "13","rosen_id" => "437","rosen_name" => "西武鉄道新宿線"),
		"244" => array( "ken_id" => "13","rosen_id" => "440","rosen_name" => "西武鉄道拝島線"),
		"245" => array( "ken_id" => "13","rosen_id" => "442","rosen_name" => "西武鉄道多摩湖線"),
		"246" => array( "ken_id" => "13","rosen_id" => "443","rosen_name" => "西武鉄道国分寺線"),
		"247" => array( "ken_id" => "13","rosen_id" => "445","rosen_name" => "西武鉄道山口線"),
		"248" => array( "ken_id" => "13","rosen_id" => "446","rosen_name" => "西武鉄道多摩川線"),
		"249" => array( "ken_id" => "13","rosen_id" => "576","rosen_name" => "東京地下鉄銀座線"),
		"250" => array( "ken_id" => "13","rosen_id" => "577","rosen_name" => "東京地下鉄方南支線"),
		"251" => array( "ken_id" => "13","rosen_id" => "577","rosen_name" => "東京地下鉄丸ノ内線"),
		"252" => array( "ken_id" => "13","rosen_id" => "579","rosen_name" => "東京地下鉄日比谷線"),
		"253" => array( "ken_id" => "13","rosen_id" => "580","rosen_name" => "東京地下鉄東西線"),
		"254" => array( "ken_id" => "13","rosen_id" => "582","rosen_name" => "東京地下鉄千代田線"),
		"255" => array( "ken_id" => "13","rosen_id" => "583","rosen_name" => "東京地下鉄有楽町線"),
		"256" => array( "ken_id" => "13","rosen_id" => "584","rosen_name" => "東京地下鉄半蔵門線"),
		"257" => array( "ken_id" => "13","rosen_id" => "585","rosen_name" => "東京地下鉄南北線"),
		"258" => array( "ken_id" => "13","rosen_id" => "586","rosen_name" => "東京都浅草線"),
		"259" => array( "ken_id" => "13","rosen_id" => "587","rosen_name" => "東京都三田線"),
		"260" => array( "ken_id" => "13","rosen_id" => "588","rosen_name" => "東京都新宿線"),
		"261" => array( "ken_id" => "13","rosen_id" => "589","rosen_name" => "東京都大江戸線"),
		"262" => array( "ken_id" => "13","rosen_id" => "590","rosen_name" => "東京都荒川線"),
		"263" => array( "ken_id" => "13","rosen_id" => "715","rosen_name" => "北総鉄道"),
		"264" => array( "ken_id" => "13","rosen_id" => "720","rosen_name" => "東京モノレール羽田"),
		"265" => array( "ken_id" => "13","rosen_id" => "720","rosen_name" => "東京モノレ"),
		"266" => array( "ken_id" => "13","rosen_id" => "830","rosen_name" => "東武伊勢崎・大師線"),
		"267" => array( "ken_id" => "13","rosen_id" => "843","rosen_name" => "東武鉄道亀戸線"),
		"268" => array( "ken_id" => "13","rosen_id" => "844","rosen_name" => "東武大師線"),
		"269" => array( "ken_id" => "13","rosen_id" => "848","rosen_name" => "東武鉄道東上線"),
		"270" => array( "ken_id" => "13","rosen_id" => "855","rosen_name" => "京成電鉄本・空港線"),
		"271" => array( "ken_id" => "13","rosen_id" => "858","rosen_name" => "京成電鉄押上線"),
		"272" => array( "ken_id" => "13","rosen_id" => "860","rosen_name" => "京成電鉄金町線"),
		"273" => array( "ken_id" => "13","rosen_id" => "863","rosen_name" => "京王電鉄京王線"),
		"274" => array( "ken_id" => "13","rosen_id" => "864","rosen_name" => "京王電鉄高尾線"),
		"275" => array( "ken_id" => "13","rosen_id" => "865","rosen_name" => "京王電鉄相模原線"),
		"276" => array( "ken_id" => "13","rosen_id" => "871","rosen_name" => "京王電鉄井の頭線"),
		"277" => array( "ken_id" => "13","rosen_id" => "873","rosen_name" => "小田急電鉄小田原線"),
		"278" => array( "ken_id" => "13","rosen_id" => "879","rosen_name" => "小田急電鉄多摩線"),
		"279" => array( "ken_id" => "13","rosen_id" => "882","rosen_name" => "東急東横線"),
		"280" => array( "ken_id" => "13","rosen_id" => "885","rosen_name" => "東急田園都市線"),
		"281" => array( "ken_id" => "13","rosen_id" => "889","rosen_name" => "東急目黒線"),
		"282" => array( "ken_id" => "13","rosen_id" => "890","rosen_name" => "東急大井町線"),
		"283" => array( "ken_id" => "13","rosen_id" => "891","rosen_name" => "東急池上線"),
		"284" => array( "ken_id" => "13","rosen_id" => "893","rosen_name" => "東急世田谷線"),
		"285" => array( "ken_id" => "13","rosen_id" => "894","rosen_name" => "京浜急行電鉄本線"),
		"286" => array( "ken_id" => "13","rosen_id" => "900","rosen_name" => "京浜急行電鉄空港線"),
		"287" => array( "ken_id" => "13","rosen_id" => "1000","rosen_name" => "多摩モノレール"),
		"288" => array( "ken_id" => "13","rosen_id" => "1001","rosen_name" => "ゆりかもめ"),
		"289" => array( "ken_id" => "13","rosen_id" => "1002","rosen_name" => "東京臨海高速鉄道"),
		"290" => array( "ken_id" => "13","rosen_id" => "1013","rosen_name" => "東急多摩川線"),
		"291" => array( "ken_id" => "13","rosen_id" => "1014","rosen_name" => "埼玉高速鉄道"),
		"292" => array( "ken_id" => "13","rosen_id" => "2021","rosen_name" => "つくばエクスプレス"),
		"293" => array( "ken_id" => "13","rosen_id" => "2029","rosen_name" => "日暮里舎人ライナー"),
		"294" => array( "ken_id" => "13","rosen_id" => "2032","rosen_name" => "副都心線"),
		"295" => array( "ken_id" => "13","rosen_id" => "2032","rosen_name" => "東京地下鉄副都心線"),
		"296" => array( "ken_id" => "13","rosen_id" => "2035","rosen_name" => "湘南新宿宇"),
		"297" => array( "ken_id" => "13","rosen_id" => "2036","rosen_name" => "湘南新宿高"),
		"298" => array( "ken_id" => "14","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"299" => array( "ken_id" => "14","rosen_id" => "89","rosen_name" => "東海道本線"),
		"300" => array( "ken_id" => "14","rosen_id" => "93","rosen_name" => "根岸線"),
		"301" => array( "ken_id" => "14","rosen_id" => "93","rosen_name" => "京浜東北・根岸線"),
		"302" => array( "ken_id" => "14","rosen_id" => "94","rosen_name" => "横須賀線"),
		"303" => array( "ken_id" => "14","rosen_id" => "99","rosen_name" => "南武線"),
		"304" => array( "ken_id" => "14","rosen_id" => "100","rosen_name" => "鶴見線"),
		"305" => array( "ken_id" => "14","rosen_id" => "105","rosen_name" => "横浜線"),
		"306" => array( "ken_id" => "14","rosen_id" => "107","rosen_name" => "相模線"),
		"307" => array( "ken_id" => "14","rosen_id" => "114","rosen_name" => "中央本線"),
		"308" => array( "ken_id" => "14","rosen_id" => "212","rosen_name" => "御殿場線"),
		"309" => array( "ken_id" => "14","rosen_id" => "604","rosen_name" => "横浜市ブルーライン"),
		"310" => array( "ken_id" => "14","rosen_id" => "629","rosen_name" => "箱根登山鉄道"),
		"311" => array( "ken_id" => "14","rosen_id" => "659","rosen_name" => "シーサイド"),
		"312" => array( "ken_id" => "14","rosen_id" => "721","rosen_name" => "湘南モノレール"),
		"313" => array( "ken_id" => "14","rosen_id" => "722","rosen_name" => "江ノ島電鉄"),
		"314" => array( "ken_id" => "14","rosen_id" => "723","rosen_name" => "伊豆箱根大雄山線"),
		"315" => array( "ken_id" => "14","rosen_id" => "865","rosen_name" => "京王電鉄相模原線"),
		"316" => array( "ken_id" => "14","rosen_id" => "873","rosen_name" => "小田急電鉄小田原線"),
		"317" => array( "ken_id" => "14","rosen_id" => "877","rosen_name" => "小田急電鉄江ノ島線"),
		"318" => array( "ken_id" => "14","rosen_id" => "879","rosen_name" => "小田急電鉄多摩線"),
		"319" => array( "ken_id" => "14","rosen_id" => "882","rosen_name" => "東急東横線"),
		"320" => array( "ken_id" => "14","rosen_id" => "885","rosen_name" => "東急田園都市線"),
		"321" => array( "ken_id" => "14","rosen_id" => "889","rosen_name" => "東急目黒線"),
		"322" => array( "ken_id" => "14","rosen_id" => "890","rosen_name" => "東急大井町線"),
		"323" => array( "ken_id" => "14","rosen_id" => "894","rosen_name" => "京浜急行電鉄本線"),
		"324" => array( "ken_id" => "14","rosen_id" => "895","rosen_name" => "京急久里浜線"),
		"325" => array( "ken_id" => "14","rosen_id" => "896","rosen_name" => "京浜急行電鉄逗子線"),
		"326" => array( "ken_id" => "14","rosen_id" => "901","rosen_name" => "京浜急行電鉄大師線"),
		"327" => array( "ken_id" => "14","rosen_id" => "902","rosen_name" => "相模鉄道いずみ野線"),
		"328" => array( "ken_id" => "14","rosen_id" => "903","rosen_name" => "相模鉄道本線"),
		"329" => array( "ken_id" => "14","rosen_id" => "2013","rosen_name" => "横浜高速鉄道ＭＭ線"),
		"330" => array( "ken_id" => "14","rosen_id" => "2022","rosen_name" => "箱根登山ケーブル線"),
		"331" => array( "ken_id" => "14","rosen_id" => "2030","rosen_name" => "横浜市グリーンＬ"),
		"332" => array( "ken_id" => "14","rosen_id" => "2035","rosen_name" => "湘南新宿宇"),
		"333" => array( "ken_id" => "14","rosen_id" => "2036","rosen_name" => "湘南新宿高"),
		"334" => array( "ken_id" => "15","rosen_id" => "17","rosen_name" => "上越新幹線"),
		"335" => array( "ken_id" => "15","rosen_id" => "120","rosen_name" => "大糸線"),
		"336" => array( "ken_id" => "15","rosen_id" => "138","rosen_name" => "上越線"),
		"337" => array( "ken_id" => "15","rosen_id" => "162","rosen_name" => "磐越西線"),
		"338" => array( "ken_id" => "15","rosen_id" => "163","rosen_name" => "只見線"),
		"339" => array( "ken_id" => "15","rosen_id" => "172","rosen_name" => "米坂線"),
		"340" => array( "ken_id" => "15","rosen_id" => "179","rosen_name" => "羽越線"),
		"341" => array( "ken_id" => "15","rosen_id" => "179","rosen_name" => "羽越本線"),
		"342" => array( "ken_id" => "15","rosen_id" => "180","rosen_name" => "白新線"),
		"343" => array( "ken_id" => "15","rosen_id" => "184","rosen_name" => "信越線"),
		"344" => array( "ken_id" => "15","rosen_id" => "184","rosen_name" => "信越本線"),
		"345" => array( "ken_id" => "15","rosen_id" => "189","rosen_name" => "飯山線"),
		"346" => array( "ken_id" => "15","rosen_id" => "191","rosen_name" => "越後線"),
		"347" => array( "ken_id" => "15","rosen_id" => "193","rosen_name" => "弥彦線"),
		"348" => array( "ken_id" => "15","rosen_id" => "241","rosen_name" => "北陸線"),
		"349" => array( "ken_id" => "15","rosen_id" => "1020","rosen_name" => "北越急行ほくほく線"),
		"350" => array( "ken_id" => "15","rosen_id" => "1020","rosen_name" => "ほくほく線"),
		"351" => array( "ken_id" => "16","rosen_id" => "220","rosen_name" => "高山線"),
		"352" => array( "ken_id" => "16","rosen_id" => "241","rosen_name" => "北陸線"),
		"353" => array( "ken_id" => "16","rosen_id" => "247","rosen_name" => "城端線"),
		"354" => array( "ken_id" => "16","rosen_id" => "249","rosen_name" => "氷見線"),
		"355" => array( "ken_id" => "16","rosen_id" => "728","rosen_name" => "富山地鉄本線"),
		"356" => array( "ken_id" => "16","rosen_id" => "728","rosen_name" => "地鉄本線"),
		"357" => array( "ken_id" => "16","rosen_id" => "730","rosen_name" => "地鉄立山線"),
		"358" => array( "ken_id" => "16","rosen_id" => "731","rosen_name" => "富山地鉄不二越上滝"),
		"359" => array( "ken_id" => "16","rosen_id" => "731","rosen_name" => "不二越線"),
		"360" => array( "ken_id" => "16","rosen_id" => "732","rosen_name" => "富山地鉄富山市内線"),
		"361" => array( "ken_id" => "16","rosen_id" => "732","rosen_name" => "富山市内線"),
		"362" => array( "ken_id" => "16","rosen_id" => "733","rosen_name" => "富山地鉄富山市内線"),
		"363" => array( "ken_id" => "16","rosen_id" => "733","rosen_name" => "富山市内線"),
		"364" => array( "ken_id" => "16","rosen_id" => "734","rosen_name" => "黒部峡谷"),
		"365" => array( "ken_id" => "16","rosen_id" => "735","rosen_name" => "万葉線"),
		"366" => array( "ken_id" => "16","rosen_id" => "2024","rosen_name" => "富山ライト"),
		"367" => array( "ken_id" => "16","rosen_id" => "2037","rosen_name" => "富山都心線"),
		"368" => array( "ken_id" => "17","rosen_id" => "241","rosen_name" => "北陸線"),
		"369" => array( "ken_id" => "18","rosen_id" => "241","rosen_name" => "北陸線"),
		"370" => array( "ken_id" => "18","rosen_id" => "243","rosen_name" => "小浜線"),
		"371" => array( "ken_id" => "18","rosen_id" => "244","rosen_name" => "越美北線"),
		"372" => array( "ken_id" => "18","rosen_id" => "739","rosen_name" => "勝山線"),
		"373" => array( "ken_id" => "18","rosen_id" => "741","rosen_name" => "三国芦原線"),
		"374" => array( "ken_id" => "18","rosen_id" => "742","rosen_name" => "福鉄線"),
		"375" => array( "ken_id" => "19","rosen_id" => "114","rosen_name" => "中央本線"),
		"376" => array( "ken_id" => "19","rosen_id" => "118","rosen_name" => "小海線"),
		"377" => array( "ken_id" => "19","rosen_id" => "213","rosen_name" => "身延線"),
		"378" => array( "ken_id" => "19","rosen_id" => "744","rosen_name" => "富士急行"),
		"379" => array( "ken_id" => "20","rosen_id" => "21","rosen_name" => "長野新幹線"),
		"380" => array( "ken_id" => "20","rosen_id" => "114","rosen_name" => "中央本線"),
		"381" => array( "ken_id" => "20","rosen_id" => "118","rosen_name" => "小海線"),
		"382" => array( "ken_id" => "20","rosen_id" => "119","rosen_name" => "篠ノ井線"),
		"383" => array( "ken_id" => "20","rosen_id" => "120","rosen_name" => "大糸線"),
		"384" => array( "ken_id" => "20","rosen_id" => "184","rosen_name" => "信越線"),
		"385" => array( "ken_id" => "20","rosen_id" => "184","rosen_name" => "信越本線"),
		"386" => array( "ken_id" => "20","rosen_id" => "185","rosen_name" => "しなの鉄道"),
		"387" => array( "ken_id" => "20","rosen_id" => "189","rosen_name" => "飯山線"),
		"388" => array( "ken_id" => "20","rosen_id" => "216","rosen_name" => "飯田線"),
		"389" => array( "ken_id" => "20","rosen_id" => "745","rosen_name" => "長野電鉄屋代線"),
		"390" => array( "ken_id" => "20","rosen_id" => "745","rosen_name" => "長電屋代線"),
		"391" => array( "ken_id" => "20","rosen_id" => "746","rosen_name" => "長野電鉄長野線"),
		"392" => array( "ken_id" => "20","rosen_id" => "746","rosen_name" => "長電長野線"),
		"393" => array( "ken_id" => "20","rosen_id" => "748","rosen_name" => "上田電鉄別所線"),
		"394" => array( "ken_id" => "20","rosen_id" => "748","rosen_name" => "上田電鉄"),
		"395" => array( "ken_id" => "20","rosen_id" => "749","rosen_name" => "松本電鉄"),
		"396" => array( "ken_id" => "20","rosen_id" => "749","rosen_name" => "松本電気鉄道"),
		"397" => array( "ken_id" => "21","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"398" => array( "ken_id" => "21","rosen_id" => "89","rosen_name" => "東海道本線"),
		"399" => array( "ken_id" => "21","rosen_id" => "114","rosen_name" => "中央本線"),
		"400" => array( "ken_id" => "21","rosen_id" => "220","rosen_name" => "高山線"),
		"401" => array( "ken_id" => "21","rosen_id" => "224","rosen_name" => "太多線"),
		"402" => array( "ken_id" => "21","rosen_id" => "663","rosen_name" => "長良川鉄道"),
		"403" => array( "ken_id" => "21","rosen_id" => "664","rosen_name" => "樽見線"),
		"404" => array( "ken_id" => "21","rosen_id" => "665","rosen_name" => "明知鉄道"),
		"405" => array( "ken_id" => "21","rosen_id" => "933","rosen_name" => "広見線"),
		"406" => array( "ken_id" => "21","rosen_id" => "935","rosen_name" => "名鉄本線"),
		"407" => array( "ken_id" => "21","rosen_id" => "956","rosen_name" => "犬山線"),
		"408" => array( "ken_id" => "21","rosen_id" => "957","rosen_name" => "各務原線"),
		"409" => array( "ken_id" => "21","rosen_id" => "964","rosen_name" => "竹鼻線"),
		"410" => array( "ken_id" => "21","rosen_id" => "2027","rosen_name" => "養老鉄道"),
		"411" => array( "ken_id" => "22","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"412" => array( "ken_id" => "22","rosen_id" => "89","rosen_name" => "東海道本線"),
		"413" => array( "ken_id" => "22","rosen_id" => "108","rosen_name" => "伊東線"),
		"414" => array( "ken_id" => "22","rosen_id" => "212","rosen_name" => "御殿場線"),
		"415" => array( "ken_id" => "22","rosen_id" => "213","rosen_name" => "身延線"),
		"416" => array( "ken_id" => "22","rosen_id" => "216","rosen_name" => "飯田線"),
		"417" => array( "ken_id" => "22","rosen_id" => "666","rosen_name" => "浜名湖鉄道"),
		"418" => array( "ken_id" => "22","rosen_id" => "724","rosen_name" => "駿豆線"),
		"419" => array( "ken_id" => "22","rosen_id" => "750","rosen_name" => "伊豆急"),
		"420" => array( "ken_id" => "22","rosen_id" => "751","rosen_name" => "岳南鉄道"),
		"421" => array( "ken_id" => "22","rosen_id" => "752","rosen_name" => "静鉄"),
		"422" => array( "ken_id" => "22","rosen_id" => "753","rosen_name" => "大井川鐵道"),
		"423" => array( "ken_id" => "22","rosen_id" => "754","rosen_name" => "井川線"),
		"424" => array( "ken_id" => "22","rosen_id" => "755","rosen_name" => "遠州鉄道"),
		"425" => array( "ken_id" => "22","rosen_id" => "755","rosen_name" => "遠鉄"),
		"426" => array( "ken_id" => "23","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"427" => array( "ken_id" => "23","rosen_id" => "89","rosen_name" => "東海道本線"),
		"428" => array( "ken_id" => "23","rosen_id" => "114","rosen_name" => "中央本線"),
		"429" => array( "ken_id" => "23","rosen_id" => "216","rosen_name" => "飯田線"),
		"430" => array( "ken_id" => "23","rosen_id" => "217","rosen_name" => "武豊線"),
		"431" => array( "ken_id" => "23","rosen_id" => "306","rosen_name" => "関西線"),
		"432" => array( "ken_id" => "23","rosen_id" => "477","rosen_name" => "名古屋線"),
		"433" => array( "ken_id" => "23","rosen_id" => "605","rosen_name" => "東山線"),
		"434" => array( "ken_id" => "23","rosen_id" => "606","rosen_name" => "名城線"),
		"435" => array( "ken_id" => "23","rosen_id" => "608","rosen_name" => "鶴舞線"),
		"436" => array( "ken_id" => "23","rosen_id" => "609","rosen_name" => "桜通線"),
		"437" => array( "ken_id" => "23","rosen_id" => "667","rosen_name" => "愛知環状線"),
		"438" => array( "ken_id" => "23","rosen_id" => "669","rosen_name" => "城北線"),
		"439" => array( "ken_id" => "23","rosen_id" => "756","rosen_name" => "豊橋鉄道線"),
		"440" => array( "ken_id" => "23","rosen_id" => "756","rosen_name" => "豊橋鉄道渥美線"),
		"441" => array( "ken_id" => "23","rosen_id" => "758","rosen_name" => "豊橋市内線"),
		"442" => array( "ken_id" => "23","rosen_id" => "758","rosen_name" => "豊橋鉄道東田本線"),
		"443" => array( "ken_id" => "23","rosen_id" => "915","rosen_name" => "豊川線"),
		"444" => array( "ken_id" => "23","rosen_id" => "917","rosen_name" => "蒲郡線"),
		"445" => array( "ken_id" => "23","rosen_id" => "918","rosen_name" => "西尾線"),
		"446" => array( "ken_id" => "23","rosen_id" => "933","rosen_name" => "広見線"),
		"447" => array( "ken_id" => "23","rosen_id" => "935","rosen_name" => "名鉄本線"),
		"448" => array( "ken_id" => "23","rosen_id" => "944","rosen_name" => "三河線"),
		"449" => array( "ken_id" => "23","rosen_id" => "945","rosen_name" => "豊田線"),
		"450" => array( "ken_id" => "23","rosen_id" => "947","rosen_name" => "常滑線"),
		"451" => array( "ken_id" => "23","rosen_id" => "948","rosen_name" => "河和線"),
		"452" => array( "ken_id" => "23","rosen_id" => "949","rosen_name" => "知多新線"),
		"453" => array( "ken_id" => "23","rosen_id" => "950","rosen_name" => "築港線"),
		"454" => array( "ken_id" => "23","rosen_id" => "951","rosen_name" => "瀬戸線"),
		"455" => array( "ken_id" => "23","rosen_id" => "952","rosen_name" => "尾西線"),
		"456" => array( "ken_id" => "23","rosen_id" => "954","rosen_name" => "津島線"),
		"457" => array( "ken_id" => "23","rosen_id" => "956","rosen_name" => "犬山線"),
		"458" => array( "ken_id" => "23","rosen_id" => "960","rosen_name" => "小牧線"),
		"459" => array( "ken_id" => "23","rosen_id" => "1015","rosen_name" => "ＧＢ志段味"),
		"460" => array( "ken_id" => "23","rosen_id" => "1025","rosen_name" => "上飯田線"),
		"461" => array( "ken_id" => "23","rosen_id" => "2016","rosen_name" => "あおなみ線"),
		"462" => array( "ken_id" => "23","rosen_id" => "2017","rosen_name" => "名港線"),
		"463" => array( "ken_id" => "23","rosen_id" => "2018","rosen_name" => "名鉄空港線"),
		"464" => array( "ken_id" => "23","rosen_id" => "2020","rosen_name" => "リニモ"),
		"465" => array( "ken_id" => "24","rosen_id" => "227","rosen_name" => "名松線"),
		"466" => array( "ken_id" => "24","rosen_id" => "228","rosen_name" => "参宮線"),
		"467" => array( "ken_id" => "24","rosen_id" => "306","rosen_name" => "関西線"),
		"468" => array( "ken_id" => "24","rosen_id" => "307","rosen_name" => "草津線"),
		"469" => array( "ken_id" => "24","rosen_id" => "320","rosen_name" => "紀勢線"),
		"470" => array( "ken_id" => "24","rosen_id" => "466","rosen_name" => "近鉄大阪線"),
		"471" => array( "ken_id" => "24","rosen_id" => "477","rosen_name" => "名古屋線"),
		"472" => array( "ken_id" => "24","rosen_id" => "480","rosen_name" => "伊勢志摩線"),
		"473" => array( "ken_id" => "24","rosen_id" => "481","rosen_name" => "鈴鹿線"),
		"474" => array( "ken_id" => "24","rosen_id" => "484","rosen_name" => "三岐北勢線"),
		"475" => array( "ken_id" => "24","rosen_id" => "485","rosen_name" => "湯の山線"),
		"476" => array( "ken_id" => "24","rosen_id" => "486","rosen_name" => "内部線"),
		"477" => array( "ken_id" => "24","rosen_id" => "670","rosen_name" => "伊勢鉄道"),
		"478" => array( "ken_id" => "24","rosen_id" => "759","rosen_name" => "三岐線"),
		"479" => array( "ken_id" => "24","rosen_id" => "2027","rosen_name" => "養老鉄道"),
		"480" => array( "ken_id" => "24","rosen_id" => "2028","rosen_name" => "伊賀鉄道"),
		"481" => array( "ken_id" => "25","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"482" => array( "ken_id" => "25","rosen_id" => "89","rosen_name" => "東海道本線"),
		"483" => array( "ken_id" => "25","rosen_id" => "231","rosen_name" => "東海道線"),
		"484" => array( "ken_id" => "25","rosen_id" => "231","rosen_name" => "山陽本線"),
		"485" => array( "ken_id" => "25","rosen_id" => "232","rosen_name" => "湖西線"),
		"486" => array( "ken_id" => "25","rosen_id" => "241","rosen_name" => "北陸線"),
		"487" => array( "ken_id" => "25","rosen_id" => "307","rosen_name" => "草津線"),
		"488" => array( "ken_id" => "25","rosen_id" => "541","rosen_name" => "京阪京津線"),
		"489" => array( "ken_id" => "25","rosen_id" => "542","rosen_name" => "京阪石坂線"),
		"490" => array( "ken_id" => "25","rosen_id" => "671","rosen_name" => "信楽鉄道"),
		"491" => array( "ken_id" => "25","rosen_id" => "761","rosen_name" => "多賀線"),
		"492" => array( "ken_id" => "25","rosen_id" => "762","rosen_name" => "近江鉄道線"),
		"493" => array( "ken_id" => "25","rosen_id" => "828","rosen_name" => "八日市線"),
		"494" => array( "ken_id" => "26","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"495" => array( "ken_id" => "26","rosen_id" => "231","rosen_name" => "山陽本線"),
		"496" => array( "ken_id" => "26","rosen_id" => "231","rosen_name" => "東海道線"),
		"497" => array( "ken_id" => "26","rosen_id" => "232","rosen_name" => "湖西線"),
		"498" => array( "ken_id" => "26","rosen_id" => "236","rosen_name" => "福知山線"),
		"499" => array( "ken_id" => "26","rosen_id" => "243","rosen_name" => "小浜線"),
		"500" => array( "ken_id" => "26","rosen_id" => "293","rosen_name" => "山陰線"),
		"501" => array( "ken_id" => "26","rosen_id" => "293","rosen_name" => "山陰本線"),
		"502" => array( "ken_id" => "26","rosen_id" => "296","rosen_name" => "舞鶴線"),
		"503" => array( "ken_id" => "26","rosen_id" => "306","rosen_name" => "関西線"),
		"504" => array( "ken_id" => "26","rosen_id" => "309","rosen_name" => "奈良線"),
		"505" => array( "ken_id" => "26","rosen_id" => "312","rosen_name" => "片町線"),
		"506" => array( "ken_id" => "26","rosen_id" => "494","rosen_name" => "近鉄京都線"),
		"507" => array( "ken_id" => "26","rosen_id" => "536","rosen_name" => "京阪鴨東線"),
		"508" => array( "ken_id" => "26","rosen_id" => "536","rosen_name" => "京阪本線"),
		"509" => array( "ken_id" => "26","rosen_id" => "539","rosen_name" => "京阪宇治線"),
		"510" => array( "ken_id" => "26","rosen_id" => "541","rosen_name" => "京阪京津線"),
		"511" => array( "ken_id" => "26","rosen_id" => "555","rosen_name" => "阪急京都線"),
		"512" => array( "ken_id" => "26","rosen_id" => "561","rosen_name" => "阪急嵐山線"),
		"513" => array( "ken_id" => "26","rosen_id" => "610","rosen_name" => "烏丸線"),
		"514" => array( "ken_id" => "26","rosen_id" => "672","rosen_name" => "宮津線"),
		"515" => array( "ken_id" => "26","rosen_id" => "673","rosen_name" => "宮福線"),
		"516" => array( "ken_id" => "26","rosen_id" => "763","rosen_name" => "叡電"),
		"517" => array( "ken_id" => "26","rosen_id" => "765","rosen_name" => "嵐電本線"),
		"518" => array( "ken_id" => "26","rosen_id" => "766","rosen_name" => "嵐電北野線"),
		"519" => array( "ken_id" => "26","rosen_id" => "767","rosen_name" => "嵯峨野観光"),
		"520" => array( "ken_id" => "26","rosen_id" => "992","rosen_name" => "京都東西線"),
		"521" => array( "ken_id" => "27","rosen_id" => "11","rosen_name" => "東海新幹線"),
		"522" => array( "ken_id" => "27","rosen_id" => "12","rosen_name" => "山陽新幹線"),
		"523" => array( "ken_id" => "27","rosen_id" => "231","rosen_name" => "東海道線"),
		"524" => array( "ken_id" => "27","rosen_id" => "231","rosen_name" => "山陽本線"),
		"525" => array( "ken_id" => "27","rosen_id" => "234","rosen_name" => "環状線"),
		"526" => array( "ken_id" => "27","rosen_id" => "235","rosen_name" => "桜島線"),
		"527" => array( "ken_id" => "27","rosen_id" => "236","rosen_name" => "福知山線"),
		"528" => array( "ken_id" => "27","rosen_id" => "306","rosen_name" => "関西線"),
		"529" => array( "ken_id" => "27","rosen_id" => "312","rosen_name" => "片町線"),
		"530" => array( "ken_id" => "27","rosen_id" => "316","rosen_name" => "阪和線"),
		"531" => array( "ken_id" => "27","rosen_id" => "450","rosen_name" => "関西空港線"),
		"532" => array( "ken_id" => "27","rosen_id" => "466","rosen_name" => "近鉄大阪線"),
		"533" => array( "ken_id" => "27","rosen_id" => "468","rosen_name" => "信貴線"),
		"534" => array( "ken_id" => "27","rosen_id" => "469","rosen_name" => "西信貴鋼索"),
		"535" => array( "ken_id" => "27","rosen_id" => "502","rosen_name" => "近鉄奈良線"),
		"536" => array( "ken_id" => "27","rosen_id" => "503","rosen_name" => "けいはんな"),
		"537" => array( "ken_id" => "27","rosen_id" => "510","rosen_name" => "長野線"),
		"538" => array( "ken_id" => "27","rosen_id" => "513","rosen_name" => "南大阪線"),
		"539" => array( "ken_id" => "27","rosen_id" => "516","rosen_name" => "道明寺線"),
		"540" => array( "ken_id" => "27","rosen_id" => "520","rosen_name" => "南海本線"),
		"541" => array( "ken_id" => "27","rosen_id" => "526","rosen_name" => "高野線"),
		"542" => array( "ken_id" => "27","rosen_id" => "527","rosen_name" => "汐見橋線"),
		"543" => array( "ken_id" => "27","rosen_id" => "528","rosen_name" => "高師浜線"),
		"544" => array( "ken_id" => "27","rosen_id" => "529","rosen_name" => "多奈川線"),
		"545" => array( "ken_id" => "27","rosen_id" => "536","rosen_name" => "京阪本線"),
		"546" => array( "ken_id" => "27","rosen_id" => "536","rosen_name" => "京阪鴨東線"),
		"547" => array( "ken_id" => "27","rosen_id" => "537","rosen_name" => "京阪交野線"),
		"548" => array( "ken_id" => "27","rosen_id" => "545","rosen_name" => "阪急神戸線"),
		"549" => array( "ken_id" => "27","rosen_id" => "551","rosen_name" => "阪急宝塚線"),
		"550" => array( "ken_id" => "27","rosen_id" => "552","rosen_name" => "阪急箕面線"),
		"551" => array( "ken_id" => "27","rosen_id" => "555","rosen_name" => "阪急京都線"),
		"552" => array( "ken_id" => "27","rosen_id" => "556","rosen_name" => "阪急千里線"),
		"553" => array( "ken_id" => "27","rosen_id" => "560","rosen_name" => "南海空港線"),
		"554" => array( "ken_id" => "27","rosen_id" => "565","rosen_name" => "阪神本線"),
		"555" => array( "ken_id" => "27","rosen_id" => "566","rosen_name" => "なんば線"),
		"556" => array( "ken_id" => "27","rosen_id" => "591","rosen_name" => "御堂筋線"),
		"557" => array( "ken_id" => "27","rosen_id" => "592","rosen_name" => "谷町線"),
		"558" => array( "ken_id" => "27","rosen_id" => "593","rosen_name" => "四つ橋線"),
		"559" => array( "ken_id" => "27","rosen_id" => "594","rosen_name" => "中央線"),
		"560" => array( "ken_id" => "27","rosen_id" => "595","rosen_name" => "千日前線"),
		"561" => array( "ken_id" => "27","rosen_id" => "596","rosen_name" => "堺筋線"),
		"562" => array( "ken_id" => "27","rosen_id" => "597","rosen_name" => "長堀鶴見線"),
		"563" => array( "ken_id" => "27","rosen_id" => "598","rosen_name" => "ニュトラム"),
		"564" => array( "ken_id" => "27","rosen_id" => "674","rosen_name" => "大阪モノレ"),
		"565" => array( "ken_id" => "27","rosen_id" => "675","rosen_name" => "泉北高速線"),
		"566" => array( "ken_id" => "27","rosen_id" => "768","rosen_name" => "北急線"),
		"567" => array( "ken_id" => "27","rosen_id" => "769","rosen_name" => "阪堺線"),
		"568" => array( "ken_id" => "27","rosen_id" => "770","rosen_name" => "上町線"),
		"569" => array( "ken_id" => "27","rosen_id" => "771","rosen_name" => "水間鉄道"),
		"570" => array( "ken_id" => "27","rosen_id" => "772","rosen_name" => "能勢電"),
		"571" => array( "ken_id" => "27","rosen_id" => "772","rosen_name" => "能勢電鉄"),
		"572" => array( "ken_id" => "27","rosen_id" => "995","rosen_name" => "ＪＲ東西線"),
		"573" => array( "ken_id" => "27","rosen_id" => "1005","rosen_name" => "彩都線"),
		"574" => array( "ken_id" => "27","rosen_id" => "2025","rosen_name" => "今里筋線"),
		"575" => array( "ken_id" => "27","rosen_id" => "2031","rosen_name" => "おおさか東"),
		"576" => array( "ken_id" => "27","rosen_id" => "2033","rosen_name" => "中之島線"),
		"577" => array( "ken_id" => "28","rosen_id" => "12","rosen_name" => "山陽新幹線"),
		"578" => array( "ken_id" => "28","rosen_id" => "231","rosen_name" => "山陽本線"),
		"579" => array( "ken_id" => "28","rosen_id" => "231","rosen_name" => "東海道線"),
		"580" => array( "ken_id" => "28","rosen_id" => "236","rosen_name" => "福知山線"),
		"581" => array( "ken_id" => "28","rosen_id" => "254","rosen_name" => "山陽線"),
		"582" => array( "ken_id" => "28","rosen_id" => "257","rosen_name" => "加古川線"),
		"583" => array( "ken_id" => "28","rosen_id" => "259","rosen_name" => "播但線"),
		"584" => array( "ken_id" => "28","rosen_id" => "261","rosen_name" => "姫新線"),
		"585" => array( "ken_id" => "28","rosen_id" => "262","rosen_name" => "赤穂線"),
		"586" => array( "ken_id" => "28","rosen_id" => "293","rosen_name" => "山陰線"),
		"587" => array( "ken_id" => "28","rosen_id" => "293","rosen_name" => "山陰本線"),
		"588" => array( "ken_id" => "28","rosen_id" => "545","rosen_name" => "阪急神戸線"),
		"589" => array( "ken_id" => "28","rosen_id" => "547","rosen_name" => "阪急今津線"),
		"590" => array( "ken_id" => "28","rosen_id" => "548","rosen_name" => "阪急伊丹線"),
		"591" => array( "ken_id" => "28","rosen_id" => "549","rosen_name" => "阪急甲陽線"),
		"592" => array( "ken_id" => "28","rosen_id" => "551","rosen_name" => "阪急宝塚線"),
		"593" => array( "ken_id" => "28","rosen_id" => "565","rosen_name" => "阪神本線"),
		"594" => array( "ken_id" => "28","rosen_id" => "566","rosen_name" => "なんば線"),
		"595" => array( "ken_id" => "28","rosen_id" => "567","rosen_name" => "武庫川線"),
		"596" => array( "ken_id" => "28","rosen_id" => "611","rosen_name" => "西神山手線"),
		"597" => array( "ken_id" => "28","rosen_id" => "672","rosen_name" => "宮津線"),
		"598" => array( "ken_id" => "28","rosen_id" => "678","rosen_name" => "神戸南北線"),
		"599" => array( "ken_id" => "28","rosen_id" => "678","rosen_name" => "神戸高速鉄道南北線"),
		"600" => array( "ken_id" => "28","rosen_id" => "679","rosen_name" => "神戸新交通"),
		"601" => array( "ken_id" => "28","rosen_id" => "679","rosen_name" => "ポトライナ"),
		"602" => array( "ken_id" => "28","rosen_id" => "680","rosen_name" => "アイランド"),
		"603" => array( "ken_id" => "28","rosen_id" => "680","rosen_name" => "六甲アイランド線"),
		"604" => array( "ken_id" => "28","rosen_id" => "682","rosen_name" => "北条線"),
		"605" => array( "ken_id" => "28","rosen_id" => "772","rosen_name" => "能勢電鉄"),
		"606" => array( "ken_id" => "28","rosen_id" => "772","rosen_name" => "能勢電"),
		"607" => array( "ken_id" => "28","rosen_id" => "773","rosen_name" => "能勢電鉄"),
		"608" => array( "ken_id" => "28","rosen_id" => "773","rosen_name" => "能勢電"),
		"609" => array( "ken_id" => "28","rosen_id" => "775","rosen_name" => "神鉄三田線"),
		"610" => array( "ken_id" => "28","rosen_id" => "777","rosen_name" => "公園都市線"),
		"611" => array( "ken_id" => "28","rosen_id" => "778","rosen_name" => "北神急行"),
		"612" => array( "ken_id" => "28","rosen_id" => "779","rosen_name" => "山電本線"),
		"613" => array( "ken_id" => "28","rosen_id" => "780","rosen_name" => "山電網干線"),
		"614" => array( "ken_id" => "28","rosen_id" => "978","rosen_name" => "神戸東西線"),
		"615" => array( "ken_id" => "28","rosen_id" => "978","rosen_name" => "神戸高速鉄道東西線"),
		"616" => array( "ken_id" => "28","rosen_id" => "985","rosen_name" => "神鉄有馬線"),
		"617" => array( "ken_id" => "28","rosen_id" => "987","rosen_name" => "神鉄粟生線"),
		"618" => array( "ken_id" => "28","rosen_id" => "995","rosen_name" => "ＪＲ東西線"),
		"619" => array( "ken_id" => "28","rosen_id" => "1017","rosen_name" => "海岸線"),
		"620" => array( "ken_id" => "28","rosen_id" => "1021","rosen_name" => "智頭急行"),
		"621" => array( "ken_id" => "29","rosen_id" => "306","rosen_name" => "関西線"),
		"622" => array( "ken_id" => "29","rosen_id" => "309","rosen_name" => "奈良線"),
		"623" => array( "ken_id" => "29","rosen_id" => "310","rosen_name" => "桜井線"),
		"624" => array( "ken_id" => "29","rosen_id" => "313","rosen_name" => "和歌山線"),
		"625" => array( "ken_id" => "29","rosen_id" => "466","rosen_name" => "近鉄大阪線"),
		"626" => array( "ken_id" => "29","rosen_id" => "494","rosen_name" => "近鉄京都線"),
		"627" => array( "ken_id" => "29","rosen_id" => "496","rosen_name" => "天理線"),
		"628" => array( "ken_id" => "29","rosen_id" => "497","rosen_name" => "橿原線"),
		"629" => array( "ken_id" => "29","rosen_id" => "502","rosen_name" => "近鉄奈良線"),
		"630" => array( "ken_id" => "29","rosen_id" => "503","rosen_name" => "けいはんな"),
		"631" => array( "ken_id" => "29","rosen_id" => "504","rosen_name" => "生駒線"),
		"632" => array( "ken_id" => "29","rosen_id" => "505","rosen_name" => "生駒鋼索線"),
		"633" => array( "ken_id" => "29","rosen_id" => "507","rosen_name" => "田原本線"),
		"634" => array( "ken_id" => "29","rosen_id" => "513","rosen_name" => "南大阪線"),
		"635" => array( "ken_id" => "29","rosen_id" => "514","rosen_name" => "吉野線"),
		"636" => array( "ken_id" => "29","rosen_id" => "515","rosen_name" => "御所線"),
		"637" => array( "ken_id" => "30","rosen_id" => "313","rosen_name" => "和歌山線"),
		"638" => array( "ken_id" => "30","rosen_id" => "316","rosen_name" => "阪和線"),
		"639" => array( "ken_id" => "30","rosen_id" => "320","rosen_name" => "紀勢線"),
		"640" => array( "ken_id" => "30","rosen_id" => "520","rosen_name" => "南海本線"),
		"641" => array( "ken_id" => "30","rosen_id" => "521","rosen_name" => "加太線"),
		"642" => array( "ken_id" => "30","rosen_id" => "522","rosen_name" => "和歌山港線"),
		"643" => array( "ken_id" => "30","rosen_id" => "526","rosen_name" => "高野線"),
		"644" => array( "ken_id" => "30","rosen_id" => "530","rosen_name" => "貴志川線"),
		"645" => array( "ken_id" => "30","rosen_id" => "783","rosen_name" => "紀州鉄道"),
		"646" => array( "ken_id" => "31","rosen_id" => "267","rosen_name" => "伯備線"),
		"647" => array( "ken_id" => "31","rosen_id" => "293","rosen_name" => "山陰本線"),
		"648" => array( "ken_id" => "31","rosen_id" => "293","rosen_name" => "山陰線"),
		"649" => array( "ken_id" => "31","rosen_id" => "298","rosen_name" => "因美線"),
		"650" => array( "ken_id" => "31","rosen_id" => "299","rosen_name" => "境線"),
		"651" => array( "ken_id" => "31","rosen_id" => "683","rosen_name" => "若桜鉄道"),
		"652" => array( "ken_id" => "31","rosen_id" => "1021","rosen_name" => "智頭急行"),
		"653" => array( "ken_id" => "32","rosen_id" => "279","rosen_name" => "山口線"),
		"654" => array( "ken_id" => "32","rosen_id" => "293","rosen_name" => "山陰線"),
		"655" => array( "ken_id" => "32","rosen_id" => "293","rosen_name" => "山陰本線"),
		"656" => array( "ken_id" => "32","rosen_id" => "300","rosen_name" => "木次線"),
		"657" => array( "ken_id" => "32","rosen_id" => "301","rosen_name" => "三江線"),
		"658" => array( "ken_id" => "32","rosen_id" => "784","rosen_name" => "一畑電車北松江線"),
		"659" => array( "ken_id" => "32","rosen_id" => "785","rosen_name" => "一畑電車大社線"),
		"660" => array( "ken_id" => "33","rosen_id" => "12","rosen_name" => "山陽新幹線"),
		"661" => array( "ken_id" => "33","rosen_id" => "254","rosen_name" => "山陽線"),
		"662" => array( "ken_id" => "33","rosen_id" => "261","rosen_name" => "姫新線"),
		"663" => array( "ken_id" => "33","rosen_id" => "262","rosen_name" => "赤穂線"),
		"664" => array( "ken_id" => "33","rosen_id" => "263","rosen_name" => "津山線"),
		"665" => array( "ken_id" => "33","rosen_id" => "264","rosen_name" => "吉備線"),
		"666" => array( "ken_id" => "33","rosen_id" => "265","rosen_name" => "宇野線"),
		"667" => array( "ken_id" => "33","rosen_id" => "266","rosen_name" => "本四備讃線"),
		"668" => array( "ken_id" => "33","rosen_id" => "267","rosen_name" => "伯備線"),
		"669" => array( "ken_id" => "33","rosen_id" => "271","rosen_name" => "芸備線"),
		"670" => array( "ken_id" => "33","rosen_id" => "298","rosen_name" => "因美線"),
		"671" => array( "ken_id" => "33","rosen_id" => "786","rosen_name" => "岡山電軌東山本線"),
		"672" => array( "ken_id" => "33","rosen_id" => "787","rosen_name" => "岡山電軌清輝橋線"),
		"673" => array( "ken_id" => "33","rosen_id" => "788","rosen_name" => "水島臨海鉄道"),
		"674" => array( "ken_id" => "33","rosen_id" => "999","rosen_name" => "井原鉄道"),
		"675" => array( "ken_id" => "33","rosen_id" => "1021","rosen_name" => "智頭急行"),
		"676" => array( "ken_id" => "34","rosen_id" => "12","rosen_name" => "山陽新幹線"),
		"677" => array( "ken_id" => "34","rosen_id" => "254","rosen_name" => "山陽線"),
		"678" => array( "ken_id" => "34","rosen_id" => "271","rosen_name" => "芸備線"),
		"679" => array( "ken_id" => "34","rosen_id" => "273","rosen_name" => "福塩線"),
		"680" => array( "ken_id" => "34","rosen_id" => "275","rosen_name" => "呉線"),
		"681" => array( "ken_id" => "34","rosen_id" => "277","rosen_name" => "可部線"),
		"682" => array( "ken_id" => "34","rosen_id" => "300","rosen_name" => "木次線"),
		"683" => array( "ken_id" => "34","rosen_id" => "301","rosen_name" => "三江線"),
		"684" => array( "ken_id" => "34","rosen_id" => "636","rosen_name" => "アストラムライン"),
		"685" => array( "ken_id" => "34","rosen_id" => "789","rosen_name" => "広島電鉄本線"),
		"686" => array( "ken_id" => "34","rosen_id" => "790","rosen_name" => "広島電鉄宇品線"),
		"687" => array( "ken_id" => "34","rosen_id" => "791","rosen_name" => "広島電鉄横川線"),
		"688" => array( "ken_id" => "34","rosen_id" => "792","rosen_name" => "広島電鉄江波線"),
		"689" => array( "ken_id" => "34","rosen_id" => "793","rosen_name" => "広島電鉄皆実線"),
		"690" => array( "ken_id" => "34","rosen_id" => "794","rosen_name" => "広島電鉄白島線"),
		"691" => array( "ken_id" => "34","rosen_id" => "795","rosen_name" => "広島電鉄宮島線"),
		"692" => array( "ken_id" => "34","rosen_id" => "999","rosen_name" => "井原鉄道"),
		"693" => array( "ken_id" => "35","rosen_id" => "12","rosen_name" => "山陽新幹線"),
		"694" => array( "ken_id" => "35","rosen_id" => "254","rosen_name" => "山陽線"),
		"695" => array( "ken_id" => "35","rosen_id" => "278","rosen_name" => "岩徳線"),
		"696" => array( "ken_id" => "35","rosen_id" => "279","rosen_name" => "山口線"),
		"697" => array( "ken_id" => "35","rosen_id" => "280","rosen_name" => "宇部線"),
		"698" => array( "ken_id" => "35","rosen_id" => "281","rosen_name" => "小野田線"),
		"699" => array( "ken_id" => "35","rosen_id" => "283","rosen_name" => "美祢線"),
		"700" => array( "ken_id" => "35","rosen_id" => "293","rosen_name" => "山陰本線"),
		"701" => array( "ken_id" => "35","rosen_id" => "293","rosen_name" => "山陰線"),
		"702" => array( "ken_id" => "35","rosen_id" => "684","rosen_name" => "錦川鉄道錦川清流線"),
		"703" => array( "ken_id" => "36","rosen_id" => "329","rosen_name" => "高徳線"),
		"704" => array( "ken_id" => "36","rosen_id" => "330","rosen_name" => "鳴門線"),
		"705" => array( "ken_id" => "36","rosen_id" => "333","rosen_name" => "土讃線"),
		"706" => array( "ken_id" => "36","rosen_id" => "335","rosen_name" => "徳島線"),
		"707" => array( "ken_id" => "36","rosen_id" => "337","rosen_name" => "牟岐線"),
		"708" => array( "ken_id" => "36","rosen_id" => "416","rosen_name" => "阿佐海岸阿佐東線"),
		"709" => array( "ken_id" => "37","rosen_id" => "266","rosen_name" => "本四備讃線"),
		"710" => array( "ken_id" => "37","rosen_id" => "325","rosen_name" => "予讃線"),
		"711" => array( "ken_id" => "37","rosen_id" => "329","rosen_name" => "高徳線"),
		"712" => array( "ken_id" => "37","rosen_id" => "333","rosen_name" => "土讃線"),
		"713" => array( "ken_id" => "37","rosen_id" => "796","rosen_name" => "高松琴平電鉄琴平線"),
		"714" => array( "ken_id" => "37","rosen_id" => "797","rosen_name" => "高松琴平電鉄志度線"),
		"715" => array( "ken_id" => "37","rosen_id" => "798","rosen_name" => "高松琴平電鉄長尾線"),
		"716" => array( "ken_id" => "38","rosen_id" => "325","rosen_name" => "予讃線"),
		"717" => array( "ken_id" => "38","rosen_id" => "327","rosen_name" => "内子線"),
		"718" => array( "ken_id" => "38","rosen_id" => "328","rosen_name" => "予土線"),
		"719" => array( "ken_id" => "38","rosen_id" => "799","rosen_name" => "伊予鉄道高浜線"),
		"720" => array( "ken_id" => "38","rosen_id" => "800","rosen_name" => "伊予鉄道郡中線"),
		"721" => array( "ken_id" => "38","rosen_id" => "801","rosen_name" => "伊予鉄道横河原線"),
		"722" => array( "ken_id" => "38","rosen_id" => "802","rosen_name" => "伊予鉄道環状線"),
		"723" => array( "ken_id" => "38","rosen_id" => "803","rosen_name" => "伊予鉄道城南線"),
		"724" => array( "ken_id" => "38","rosen_id" => "824","rosen_name" => "伊予鉄道本町線"),
		"725" => array( "ken_id" => "39","rosen_id" => "328","rosen_name" => "予土線"),
		"726" => array( "ken_id" => "39","rosen_id" => "333","rosen_name" => "土讃線"),
		"727" => array( "ken_id" => "39","rosen_id" => "416","rosen_name" => "阿佐海岸阿佐東線"),
		"728" => array( "ken_id" => "39","rosen_id" => "685","rosen_name" => "土佐くろしお宿毛線"),
		"729" => array( "ken_id" => "39","rosen_id" => "806","rosen_name" => "土佐電気鉄道桟橋線"),
		"730" => array( "ken_id" => "39","rosen_id" => "807","rosen_name" => "土佐電気鉄道後免線"),
		"731" => array( "ken_id" => "39","rosen_id" => "808","rosen_name" => "土佐電気鉄道伊野線"),
		"732" => array( "ken_id" => "39","rosen_id" => "1018","rosen_name" => "土佐くろしおなはり"),
		"733" => array( "ken_id" => "40","rosen_id" => "12","rosen_name" => "山陽新幹線"),
		"734" => array( "ken_id" => "40","rosen_id" => "254","rosen_name" => "山陽線"),
		"735" => array( "ken_id" => "40","rosen_id" => "321","rosen_name" => "博多南線"),
		"736" => array( "ken_id" => "40","rosen_id" => "341","rosen_name" => "鹿児島本線"),
		"737" => array( "ken_id" => "40","rosen_id" => "344","rosen_name" => "香椎線"),
		"738" => array( "ken_id" => "40","rosen_id" => "345","rosen_name" => "篠栗線"),
		"739" => array( "ken_id" => "40","rosen_id" => "355","rosen_name" => "筑肥線"),
		"740" => array( "ken_id" => "40","rosen_id" => "358","rosen_name" => "久大本線"),
		"741" => array( "ken_id" => "40","rosen_id" => "365","rosen_name" => "日豊本線"),
		"742" => array( "ken_id" => "40","rosen_id" => "366","rosen_name" => "日田彦山線"),
		"743" => array( "ken_id" => "40","rosen_id" => "369","rosen_name" => "筑豊本線"),
		"744" => array( "ken_id" => "40","rosen_id" => "371","rosen_name" => "後藤寺線"),
		"745" => array( "ken_id" => "40","rosen_id" => "570","rosen_name" => "西鉄天神大牟田線"),
		"746" => array( "ken_id" => "40","rosen_id" => "572","rosen_name" => "西日本鉄道太宰府線"),
		"747" => array( "ken_id" => "40","rosen_id" => "573","rosen_name" => "西日本鉄道甘木線"),
		"748" => array( "ken_id" => "40","rosen_id" => "574","rosen_name" => "西日本鉄道貝塚線"),
		"749" => array( "ken_id" => "40","rosen_id" => "612","rosen_name" => "福岡市空港線"),
		"750" => array( "ken_id" => "40","rosen_id" => "613","rosen_name" => "福岡市箱崎線"),
		"751" => array( "ken_id" => "40","rosen_id" => "686","rosen_name" => "北九州高速鉄道"),
		"752" => array( "ken_id" => "40","rosen_id" => "687","rosen_name" => "平成筑豊鉄道"),
		"753" => array( "ken_id" => "40","rosen_id" => "687","rosen_name" => "筑豊鉄道"),
		"754" => array( "ken_id" => "40","rosen_id" => "690","rosen_name" => "甘木鉄道"),
		"755" => array( "ken_id" => "40","rosen_id" => "809","rosen_name" => "筑豊電気鉄道"),
		"756" => array( "ken_id" => "40","rosen_id" => "2019","rosen_name" => "福岡市七隈線"),
		"757" => array( "ken_id" => "41","rosen_id" => "341","rosen_name" => "鹿児島本線"),
		"758" => array( "ken_id" => "41","rosen_id" => "352","rosen_name" => "長崎本線"),
		"759" => array( "ken_id" => "41","rosen_id" => "353","rosen_name" => "唐津線"),
		"760" => array( "ken_id" => "41","rosen_id" => "355","rosen_name" => "筑肥線"),
		"761" => array( "ken_id" => "41","rosen_id" => "356","rosen_name" => "佐世保線"),
		"762" => array( "ken_id" => "41","rosen_id" => "690","rosen_name" => "甘木鉄道"),
		"763" => array( "ken_id" => "41","rosen_id" => "712","rosen_name" => "松浦鉄道西九州線"),
		"764" => array( "ken_id" => "42","rosen_id" => "352","rosen_name" => "長崎本線"),
		"765" => array( "ken_id" => "42","rosen_id" => "356","rosen_name" => "佐世保線"),
		"766" => array( "ken_id" => "42","rosen_id" => "357","rosen_name" => "大村線"),
		"767" => array( "ken_id" => "42","rosen_id" => "712","rosen_name" => "松浦鉄道西九州線"),
		"768" => array( "ken_id" => "42","rosen_id" => "810","rosen_name" => "島原鉄道"),
		"769" => array( "ken_id" => "42","rosen_id" => "811","rosen_name" => "長崎電軌本線"),
		"770" => array( "ken_id" => "42","rosen_id" => "812","rosen_name" => "長崎電軌桜町支線"),
		"771" => array( "ken_id" => "42","rosen_id" => "813","rosen_name" => "長崎電軌大浦支線"),
		"772" => array( "ken_id" => "42","rosen_id" => "814","rosen_name" => "長崎電軌蛍茶屋支線"),
		"773" => array( "ken_id" => "43","rosen_id" => "341","rosen_name" => "鹿児島本線"),
		"774" => array( "ken_id" => "43","rosen_id" => "346","rosen_name" => "三角線"),
		"775" => array( "ken_id" => "43","rosen_id" => "349","rosen_name" => "肥薩線"),
		"776" => array( "ken_id" => "43","rosen_id" => "361","rosen_name" => "豊肥本線"),
		"777" => array( "ken_id" => "43","rosen_id" => "616","rosen_name" => "熊本市健軍線"),
		"778" => array( "ken_id" => "43","rosen_id" => "616","rosen_name" => "健軍線"),
		"779" => array( "ken_id" => "43","rosen_id" => "617","rosen_name" => "上熊本線"),
		"780" => array( "ken_id" => "43","rosen_id" => "617","rosen_name" => "熊本市上熊本線"),
		"781" => array( "ken_id" => "43","rosen_id" => "692","rosen_name" => "南阿蘇鉄道"),
		"782" => array( "ken_id" => "43","rosen_id" => "693","rosen_name" => "くま川鉄道"),
		"783" => array( "ken_id" => "43","rosen_id" => "815","rosen_name" => "熊本電気鉄道"),
		"784" => array( "ken_id" => "43","rosen_id" => "2014","rosen_name" => "九州新幹線"),
		"785" => array( "ken_id" => "43","rosen_id" => "2015","rosen_name" => "肥薩おれんじ鉄道"),
		"786" => array( "ken_id" => "44","rosen_id" => "358","rosen_name" => "久大本線"),
		"787" => array( "ken_id" => "44","rosen_id" => "361","rosen_name" => "豊肥本線"),
		"788" => array( "ken_id" => "44","rosen_id" => "365","rosen_name" => "日豊本線"),
		"789" => array( "ken_id" => "44","rosen_id" => "366","rosen_name" => "日田彦山線"),
		"790" => array( "ken_id" => "45","rosen_id" => "349","rosen_name" => "肥薩線"),
		"791" => array( "ken_id" => "45","rosen_id" => "365","rosen_name" => "日豊本線"),
		"792" => array( "ken_id" => "45","rosen_id" => "367","rosen_name" => "日南線"),
		"793" => array( "ken_id" => "45","rosen_id" => "368","rosen_name" => "吉都線"),
		"794" => array( "ken_id" => "45","rosen_id" => "1022","rosen_name" => "宮崎空港線"),
		"795" => array( "ken_id" => "46","rosen_id" => "341","rosen_name" => "鹿児島本線"),
		"796" => array( "ken_id" => "46","rosen_id" => "349","rosen_name" => "肥薩線"),
		"797" => array( "ken_id" => "46","rosen_id" => "351","rosen_name" => "指宿枕崎線"),
		"798" => array( "ken_id" => "46","rosen_id" => "365","rosen_name" => "日豊本線"),
		"799" => array( "ken_id" => "46","rosen_id" => "367","rosen_name" => "日南線"),
		"800" => array( "ken_id" => "46","rosen_id" => "368","rosen_name" => "吉都線"),
		"801" => array( "ken_id" => "46","rosen_id" => "618","rosen_name" => "鹿児島市谷山線"),
		"802" => array( "ken_id" => "46","rosen_id" => "619","rosen_name" => "鹿児島市唐湊線"),
		"803" => array( "ken_id" => "46","rosen_id" => "2014","rosen_name" => "九州新幹線"),
		"804" => array( "ken_id" => "46","rosen_id" => "2015","rosen_name" => "肥薩おれんじ鉄道"),
		"805" => array( "ken_id" => "47","rosen_id" => "2012","rosen_name" => "沖縄都市モノレール")

	);



	//c21 csv format
	$work_c21 =
	array(
		//	"1" => array( "d_name" => "","h_name" => "加盟店コード"),
		//	"2" => array( "d_name" => "","h_name" => "シリアルコード"),
			"3" => array( "d_name" => "shikibesu","h_name" => "物件コード"),
		//	"4" => array( "d_name" => "","h_name" => "物件登録日"),
		//	"5" => array( "d_name" => "","h_name" => "物件更新日"),
		//	"6" => array( "d_name" => "","h_name" => "物件公開日"),
		//	"7" => array( "d_name" => "","h_name" => "物件種別"),
		//	"9" => array( "d_name" => "","h_name" => "物件種目"),
		//	"10" => array( "d_name" => "","h_name" => "現行収入"),
			"11" => array( "d_name" => "kakakurimawari","h_name" => "現行利回り"),
		//	"12" => array( "d_name" => "","h_name" => "満室時想定収入"),
			"13" => array( "d_name" => "kakakuhyorimawari","h_name" => "満室時想定利回り"),
		//	"14" => array( "d_name" => "","h_name" => "オーナーチェンジ"),
		//	"15" => array( "d_name" => "","h_name" => "投資用コメント"),
			"16" => array( "d_name" => "tatemonoshinchiku","h_name" => "新築表示"),
		//	"17" => array( "d_name" => "","h_name" => "郵便番号"),
		//	"18" => array( "d_name" => "","h_name" => "所在地(都道府県）"),
		//	"19" => array( "d_name" => "","h_name" => "所在地（区市町村)"),
			"20" => array( "d_name" => "shozaichimeisho","h_name" => "所在地（大字・通称）"),
			"21" => array( "d_name" => "shozaichimeisho2","h_name" => "所在地（字名・丁目)"),
			"22" => array( "d_name" => "shozaichimeisho3","h_name" => "所在地（地番以下)"),
		//	"23" => array( "d_name" => "","h_name" => "都道府県"),
		//	"24" => array( "d_name" => "","h_name" => "市区町村"),
			"25" => array( "d_name" => "shozaichimeisho","h_name" => "大字･通称"),
			"26" => array( "d_name" => "shozaichimeisho2","h_name" => "字名・丁目"),
			"27" => array( "d_name" => "shozaichimeisho3","h_name" => "地番以下"),
			"28" => array( "d_name" => "bukkenmei","h_name" => "物件名称"),
		//	"29" => array( "d_name" => "","h_name" => "物件名称カナ"),
		//	"30" => array( "d_name" => "bukkenmeikoukai","h_name" => "名称を公開"),
		////	"31" => array( "d_name" => "shuuhenshougaku","h_name" => "小学校区"),
		////	"32" => array( "d_name" => "shuuhenchuugaku","h_name" => "中学校区"),
		//	"33" => array( "d_name" => "","h_name" => "セールスポイント"),
		//	"34" => array( "d_name" => "","h_name" => "備考"),
		//	"35" => array( "d_name" => "","h_name" => "おすすめ区分"),
		//	"36" => array( "d_name" => "","h_name" => "おすすめ区分２"),
		//	"37" => array( "d_name" => "","h_name" => "加盟店整理コード"),

		//	"38" => array( "d_name" => "","h_name" => "沿線１"),
		//	"39" => array( "d_name" => "","h_name" => "駅１"),
		//	"40" => array( "d_name" => "","h_name" => "沿線なし指定"),
			"41" => array( "d_name" => "koutsutoho1f","h_name" => "徒歩１"),
			"42" => array( "d_name" => "koutsubusstei1","h_name" => "バス停１"),
			"43" => array( "d_name" => "koutsubusstei1","h_name" => "バス停名１"),
			"44" => array( "d_name" => "koutsubussfun1","h_name" => "バス１"),
			"45" => array( "d_name" => "koutsutohob1f","h_name" => "停歩１"),
			"46" => array( "d_name" => "koutsusonota","h_name" => "車１"),
			"47" => array( "d_name" => "koutsutoho1","h_name" => "距離１"),

		//	"48" => array( "d_name" => "","h_name" => "沿線２"),
		//	"49" => array( "d_name" => "","h_name" => "駅２"),
			"50" => array( "d_name" => "koutsutoho2f","h_name" => "徒歩２"),
			"51" => array( "d_name" => "koutsubusstei2","h_name" => "バス停２"),
			"52" => array( "d_name" => "koutsubussfun2","h_name" => "バス２"),
			"53" => array( "d_name" => "koutsutohob2f","h_name" => "停歩２"),
		//	"54" => array( "d_name" => "","h_name" => "車２"),
			"55" => array( "d_name" => "koutsutoho2","h_name" => "距離２"),

			"56" => array( "d_name" => "kakaku","h_name" => "価格"),
			"57" => array( "d_name" => "kakaku","h_name" => "賃料"),
			"58" => array( "d_name" => "kakakuzei","h_name" => "賃料（税）"),
		//	"59" => array( "d_name" => "","h_name" => "管理費"),
		//	"60" => array( "d_name" => "","h_name" => "管理費（税）"),
		//	"61" => array( "d_name" => "kakakukyouekihi","h_name" => "共益費"),
		//	"62" => array( "d_name" => "","h_name" => "共益費（税）"),
			"63" => array( "d_name" => "kakakukenrikin","h_name" => "権利金"),
		//	"64" => array( "d_name" => "","h_name" => "権利金（単位）"),
		//	"65" => array( "d_name" => "","h_name" => "権利金（税）"),
		//	"66" => array( "d_name" => "","h_name" => "権利金なしフラグ"),
			"67" => array( "d_name" => "kakakureikin","h_name" => "礼金"),
		//	"68" => array( "d_name" => "","h_name" => "礼金（単位）"),
		//	"69" => array( "d_name" => "","h_name" => "礼金（税）"),
		//	"70" => array( "d_name" => "","h_name" => "礼金なしフラグ"),
			"71" => array( "d_name" => "kakakuhoshoukin","h_name" => "保証金"),
		//	"72" => array( "d_name" => "","h_name" => "保証金（単位）"),
		//	"73" => array( "d_name" => "","h_name" => "保証金なしフラグ"),
			"74" => array( "d_name" => "kakakushikikin","h_name" => "敷金"),
		//	"75" => array( "d_name" => "","h_name" => "敷金（単位）"),
		//	"76" => array( "d_name" => "","h_name" => "敷金なしフラグ"),
		//	"77" => array( "d_name" => "kakakushikibiki","h_name" => "敷引"),
/*
			"78" => array( "d_name" => "","h_name" => "敷引（単位）"),
			"79" => array( "d_name" => "","h_name" => "償却金"),
			"80" => array( "d_name" => "","h_name" => "償却金（単位）"),
			"81" => array( "d_name" => "","h_name" => "償却金支払時期"),
*/
			"82" => array( "d_name" => "kakakukoushin","h_name" => "更新料"),
/*
			"83" => array( "d_name" => "","h_name" => "更新料（単位）"),
			"84" => array( "d_name" => "","h_name" => "更新料（税）"),
			"85" => array( "d_name" => "","h_name" => "更新料なしフラグ"),
			"86" => array( "d_name" => "","h_name" => "火災保険"),
*/
			"87" => array( "d_name" => "kakakuhokenkikan","h_name" => "火災保険期間"),
			"88" => array( "d_name" => "kakakuhoken","h_name" => "火災保険料"),
			"89" => array( "d_name" => "chushajokubun","h_name" => "駐車場状況"),
			"90" => array( "d_name" => "chushajoryokin","h_name" => "駐車場料金"),
			"91" => array( "d_name" => "","h_name" => "駐車場料金（税）"),


		//	"119" => array( "d_name" => "","h_name" => "消費税区分"),
			"120" => array( "d_name" => "kakakutsubo","h_name" => "坪単価"),
			"121" => array( "d_name" => "shakuchiryo","h_name" => "地代"),
		//	"122" => array( "d_name" => "","h_name" => "管理費有無"),
			"123" => array( "d_name" => "kakakutsumitate","h_name" => "修繕積立費"),
		//	"124" => array( "d_name" => "","h_name" => "修繕積立費有無"),
		//	"125" => array( "d_name" => "","h_name" => "修繕積立基金"),
		//	"126" => array( "d_name" => "","h_name" => "路線価"),
			"127" => array( "d_name" => "tochikeikaku","h_name" => "都市計画"),
			"128" => array( "d_name" => "tochiyouto","h_name" => "用途地域"),
			"129" => array( "d_name" => "tochikenpei","h_name" => "建ぺい率"),
			"130" => array( "d_name" => "tochiyoseki","h_name" => "容積率"),
			"131" => array( "d_name" => "tochikokudohou","h_name" => "国土法の届出"),
			"132" => array( "d_name" => "tochichimoku","h_name" => "地目"),
			"133" => array( "d_name" => "bukkensoukosu","h_name" => "総部屋数"),
			"134" => array( "d_name" => "tochisokutei","h_name" => "土地面積基準"),
			"135" => array( "d_name" => "tochikukaku","h_name" => "土地面積・㎡"),
			"146" => array( "d_name" => "tochikukaku","h_name" => "土地面積（㎡）"),
			"136" => array( "d_name" => "tochikukaku","h_name" => "土地面積・_"),
			"147" => array( "d_name" => "tochikukaku","h_name" => "土地面積（_）"),
		//	"148" => array( "d_name" => "","h_name" => "土地面積（坪）"),
		//	"137" => array( "d_name" => "","h_name" => "土地面積・坪"),
			"138" => array( "d_name" => "tochisetsudoshurui1","h_name" => "接道種別１"),
			"139" => array( "d_name" => "tochisetsudohouko1","h_name" => "接道方向１"),
			"140" => array( "d_name" => "tochisetsudofukuin1","h_name" => "接道幅員１"),
			"141" => array( "d_name" => "tochisetsudomaguchi1","h_name" => "接道接面幅１"),
			"142" => array( "d_name" => "tochisetsudoshurui2","h_name" => "接道種別２"),
			"143" => array( "d_name" => "tochisetsudohouko2","h_name" => "接道方向２"),
			"144" => array( "d_name" => "tochisetsudofukuin2","h_name" => "接道幅員２"),
			"145" => array( "d_name" => "tochisetsudomaguchi2","h_name" => "接道接面幅２"),
			"149" => array( "d_name" => "tochisetsudoshurui1","h_name" => "接道１（道路種類）"),
			"150" => array( "d_name" => "tochisetsudohouko1","h_name" => "接道１（方向）"),
			"151" => array( "d_name" => "tochisetsudofukuin1","h_name" => "接道１（幅員）"),
			"152" => array( "d_name" => "tochisetsudomaguchi1","h_name" => "接道１（接面幅）"),
			"153" => array( "d_name" => "tochisetsudoshurui2","h_name" => "接道２（道路種類）"),
			"154" => array( "d_name" => "tochisetsudohouko2","h_name" => "接道２（方向）"),
			"155" => array( "d_name" => "tochisetsudofukuin2","h_name" => "接道２（幅員）"),
			"156" => array( "d_name" => "tochisetsudomaguchi2","h_name" => "接道２（接面幅）"),
			"157" => array( "d_name" => "tochisetsudoichishitei1","h_name" => "道路位置指定"),
		//	"158" => array( "d_name" => "","h_name" => "不適合接道"),
		//	"159" => array( "d_name" => "","h_name" => "建築条件"),
			"160" => array( "d_name" => "tochikenri","h_name" => "土地権利"),
		//	"161" => array( "d_name" => "","h_name" => "土地持分"),

		//	"166" => array( "d_name" => "","h_name" => "私道有無"),
		//	"167" => array( "d_name" => "","h_name" => "私道持分"),
		//	"168" => array( "d_name" => "","h_name" => "私道面積基準"),
			"169" => array( "d_name" => "tochishido","h_name" => "私道面積"),
			"170" => array( "d_name" => "tochisetback","h_name" => "セットバック要否"),
			"171" => array( "d_name" => "","h_name" => "セットバック部分"),
			"172" => array( "d_name" => "tochisetback2","h_name" => "セットバック面積"),
/*
			"173" => array( "d_name" => "","h_name" => "敷延有無"),
			"174" => array( "d_name" => "","h_name" => "敷延面積"),
			"175" => array( "d_name" => "","h_name" => "借地条件"),
*/

			"176" => array( "d_name" => "shakuchikikan","h_name" => "借地期間"),
/*
			"177" => array( "d_name" => "","h_name" => "借地開始日"),
			"178" => array( "d_name" => "","h_name" => "区画整理"),
			"179" => array( "d_name" => "","h_name" => "計画道路"),
			"180" => array( "d_name" => "","h_name" => "開発許可番号"),
			"181" => array( "d_name" => "","h_name" => "地域地区"),
*/
			"182" => array( "d_name" => "shanaimemo","h_name" => "特記事項"),
			"183" => array( "d_name" => "tatemonokozo","h_name" => "建物構造"),
			"184" => array( "d_name" => "tatemonokaisu1","h_name" => "階建"),
			"185" => array( "d_name" => "tatemonokaisu2","h_name" => "階建（地下）"),

/*
			"191" => array( "d_name" => "tatemonomenseki","h_name" => "専有面積・㎡"),
			"192" => array( "d_name" => "tatemonomenseki","h_name" => "専有面積・_"),
*/
			"193" => array( "d_name" => "tatemonomenseki","h_name" => "専有面積（㎡）"),
			"194" => array( "d_name" => "tatemonomenseki","h_name" => "専有面積（_）"),

			"186" => array(  "d_name" => "tatemonomenseki","h_name" => "建物面積（㎡）"),
			"187" => array(  "d_name" => "tatemonomenseki","h_name" => "建物面積（_）"),

			"1871" => array( "d_name" => "tatemonomenseki","h_name" => "建築面積"),


			"188" => array( "d_name" => "tatemononobeyukamenseki","h_name" => "建築延床面積・_"),
			"1881" => array( "d_name" => "tatemononobeyukamenseki","h_name" => "建築延床面積・㎡"),

		//	"189" => array( "d_name" => "","h_name" => "建物面積（坪）"),
		//	"190" => array( "d_name" => "","h_name" => "専有面積基準"),


		//	"195" => array( "d_name" => "","h_name" => "専有面積（坪）"),
			"196" => array( "d_name" => "heyabarukoni","h_name" => "バルコニー面積"),
		//	"197" => array( "d_name" => "","h_name" => "ルーフバルコニー面積"),
			"198" => array( "d_name" => "bukkensoukosu","h_name" => "総戸数"),
		//	"199" => array( "d_name" => "","h_name" => "販売戸数"),
		//	"200" => array( "d_name" => "","h_name" => "部屋地下フラグ"),
			"201" => array( "d_name" => "heyakaisu","h_name" => "部屋の階"),
			"202" => array( "d_name" => "bukkennaiyo","h_name" => "部屋番号"),
			"203" => array( "d_name" => "heyamuki","h_name" => "建物向き"),
			"204" => array( "d_name" => "tatemonochikunenn","h_name" => "築年月"),

			"212" => array( "d_name" => "tochikenri","h_name" => "借地権種類"),
		//	"213" => array( "d_name" => "","h_name" => "ベランダ面積"),
		//	"214" => array( "d_name" => "","h_name" => "テラス面積"),
			"215" => array( "d_name" => "heyamuki","h_name" => "部屋の向き"),
		//	"216" => array( "d_name" => "","h_name" => "間取"),

			"217" => array( "d_name" => "madorikai1","h_name" => "間取詳細１（階）"),
			"218" => array( "d_name" => "madorisyurui1","h_name" => "間取詳細１（タイプ）"),
			"219" => array( "d_name" => "madorijyousu1","h_name" => "間取詳細１（広さ）"),
			"220" => array( "d_name" => "madorijyousu1","h_name" => "間取詳細１（畳数）"),

			"221" => array( "d_name" => "madorikai2","h_name" => "間取詳細２（階）"),
			"222" => array( "d_name" => "madorisyurui2","h_name" => "間取詳細２（タイプ）"),
			"223" => array( "d_name" => "madorijyousu2","h_name" => "間取詳細２（広さ）"),
			"224" => array( "d_name" => "madorijyousu2","h_name" => "間取詳細２（畳数）"),

			"225" => array( "d_name" => "madorikai3","h_name" => "間取詳細３（階）"),
			"226" => array( "d_name" => "madorisyurui3","h_name" => "間取詳細３（タイプ）"),
			"227" => array( "d_name" => "madorijyousu3","h_name" => "間取詳細３（広さ）"),
			"228" => array( "d_name" => "madorijyousu3","h_name" => "間取詳細３（畳数）"),

			"229" => array( "d_name" => "madorikai4","h_name" => "間取詳細４（階）"),
			"230" => array( "d_name" => "madorisyurui4","h_name" => "間取詳細４（タイプ）"),
			"231" => array( "d_name" => "madorijyousu4","h_name" => "間取詳細４（広さ）"),
			"232" => array( "d_name" => "madorijyousu4","h_name" => "間取詳細４（畳数）"),

			"233" => array( "d_name" => "madorikai5","h_name" => "間取詳細５（階）"),
			"234" => array( "d_name" => "madorisyurui5","h_name" => "間取詳細５（タイプ）"),
			"235" => array( "d_name" => "madorijyousu5","h_name" => "間取詳細５（広さ）"),
			"236" => array( "d_name" => "madorijyousu5","h_name" => "間取詳細５（畳数）"),

			"237" => array( "d_name" => "madorikai6","h_name" => "間取詳細６（階）"),
			"238" => array( "d_name" => "madorisyurui6","h_name" => "間取詳細６（タイプ）"),
			"239" => array( "d_name" => "madorijyousu6","h_name" => "間取詳細６（広さ）"),
			"240" => array( "d_name" => "madorijyousu6","h_name" => "間取詳細６（畳数）"),

			"241" => array( "d_name" => "madorikai7","h_name" => "間取詳細７（階）"),
			"242" => array( "d_name" => "madorisyurui7","h_name" => "間取詳細７（タイプ）"),
			"243" => array( "d_name" => "madorijyousu7","h_name" => "間取詳細７（広さ）"),
			"244" => array( "d_name" => "madorijyousu7","h_name" => "間取詳細７（畳数）"),

			"245" => array( "d_name" => "madorikai8","h_name" => "間取詳細８（階）"),
			"246" => array( "d_name" => "madorisyurui8","h_name" => "間取詳細８（タイプ）"),
			"247" => array( "d_name" => "madorijyousu8","h_name" => "間取詳細８（広さ）"),

			"251" => array( "d_name" => "","h_name" => "設備その他"),
			"252" => array( "d_name" => "","h_name" => "設備備考"),
			"253" => array( "d_name" => "","h_name" => "設備詳細フォーマットタイプ"),
			"254" => array( "d_name" => "","h_name" => "設備詳細"),
			"255" => array( "d_name" => "torihikitaiyo","h_name" => "当社の取引態様"),
/*
			"256" => array( "d_name" => "","h_name" => "当社の担当者コード"),
			"257" => array( "d_name" => "","h_name" => "当社の担当者名"),
			"258" => array( "d_name" => "","h_name" => "当社の手数料"),
			"259" => array( "d_name" => "","h_name" => "手数料マーク"),
			"260" => array( "d_name" => "","h_name" => "物件確認書送信日"),
			"261" => array( "d_name" => "","h_name" => "物件シート有無"),
			"262" => array( "d_name" => "","h_name" => "商談中フラグ"),
			"263" => array( "d_name" => "","h_name" => "客付可否"),
			"264" => array( "d_name" => "","h_name" => "手数料マーク"),
			"265" => array( "d_name" => "","h_name" => "借家権種類"),
			"266" => array( "d_name" => "","h_name" => "契約期間（年）"),
			"267" => array( "d_name" => "","h_name" => "契約期間（月）"),
*/
			"268" => array( "d_name" => "shakuchikubun","h_name" => "定期借家契約期限"),
		//	"269" => array( "d_name" => "nyukyojiki","h_name" => "引渡条件"),
		//	"270" => array( "d_name" => "nyukyojiki","h_name" => "引渡時期"),
		//	"271" => array( "d_name" => "nyukyonengetsu","h_name" => "引渡時期（年）"),
		//	"272" => array( "d_name" => "","h_name" => "引渡時期（月）"),
		//	"273" => array( "d_name" => "","h_name" => "),
			"274" => array( "d_name" => "nyukyosyun","h_name" => "引渡時期（旬）"),
/*
			"275" => array( "d_name" => "","h_name" => "手数料負担割合（貸主）"),
			"276" => array( "d_name" => "","h_name" => "手数料負担割合（借主）"),
			"277" => array( "d_name" => "","h_name" => "手数料分配割合（元付）"),
			"278" => array( "d_name" => "","h_name" => "手数料分配割合（客付）"),
			"279" => array( "d_name" => "","h_name" => "物件確認書送信日"),
			"280" => array( "d_name" => "","h_name" => "スーパー賃貸区分"),
			"281" => array( "d_name" => "","h_name" => "物件シート有無"),
			"282" => array( "d_name" => "","h_name" => "ＱＲ看板建物情報"),
			"283" => array( "d_name" => "","h_name" => "特集コード"),
			"284" => array( "d_name" => "","h_name" => "特集名"),
			"285" => array( "d_name" => "","h_name" => "特集エリアコード"),
			"286" => array( "d_name" => "","h_name" => "特集エリア"),
			"287" => array( "d_name" => "","h_name" => "ＵＲＬ種別１"),
*/
			"288" => array( "d_name" => "targeturl","h_name" => "ＵＲＬ１"),
/*
			"310" => array( "d_name" => "nyukyogenkyo","h_name" => "物件現況"),
			"312" => array( "d_name" => "nyukyogenkyo","h_name" => "建物現況"),
			"311" => array( "d_name" => "nyukyogenkyo","h_name" => "土地現況"),
			"313" => array( "d_name" => "nyukyogenkyo","h_name" => "現況"),
			"314" => array( "d_name" => "","h_name" => "契約開始日"),
			"315" => array( "d_name" => "","h_name" => "契約満期日"),
			"316" => array( "d_name" => "","h_name" => "契約更新予定"),
			"317" => array( "d_name" => "","h_name" => "退去予定日"),
			"318" => array( "d_name" => "","h_name" => "情報提供会社コード"),
*/
			"319" => array( "d_name" => "motozukemei","h_name" => "情報提供会社名"),
			"320" => array( "d_name" => "motozuketel","h_name" => "情報提供会社電話番号"),
/*
			"321" => array( "d_name" => "","h_name" => "情報提供会社FAX"),
			"322" => array( "d_name" => "","h_name" => "提供会社取引態様"),
			"323" => array( "d_name" => "","h_name" => "提供会社手数料"),
			"324" => array( "d_name" => "","h_name" => "最新取引状況"),
			"325" => array( "d_name" => "","h_name" => "取引状況確認日"),
			"326" => array( "d_name" => "","h_name" => "広告掲載の可否"),
			"327" => array( "d_name" => "","h_name" => "インターネット掲載の可否"),
			"328" => array( "d_name" => "","h_name" => "先物物件のYahoo掲載条件"),
			"329" => array( "d_name" => "","h_name" => "掲載確認日"),
			"330" => array( "d_name" => "","h_name" => "提供会社物件番号"),
			"331" => array( "d_name" => "","h_name" => "提供会社担当者"),
			"332" => array( "d_name" => "","h_name" => "別業者"),
			"333" => array( "d_name" => "","h_name" => "情報媒体コード"),
			"334" => array( "d_name" => "","h_name" => "情報媒体名"),
*/
			"335" => array( "d_name" => "fudoimg1","h_name" => "画像１（間取）"),
			"336" => array( "d_name" => "fudoimg2","h_name" => "画像２（外観）"),
			"337" => array( "d_name" => "fudoimg3","h_name" => "画像３"),
		//	"338" => array( "d_name" => "fudoimg4","h_name" => "画像４"),
			"339" => array( "d_name" => "fudoimg5","h_name" => "画像５"),
			"340" => array( "d_name" => "fudoimg6","h_name" => "画像６"),
			"341" => array( "d_name" => "fudoimg7","h_name" => "画像７"),
		//	"342" => array( "d_name" => "fudoimg8","h_name" => "画像８"),
			"343" => array( "d_name" => "","h_name" => "パノラマ公開"),
			"344" => array( "d_name" => "","h_name" => "パノラマURL"),
			"345" => array( "d_name" => "","h_name" => "シート用文章"),

			"357" => array( "d_name" => "seiyakubi","h_name" => "売却年月日"),

		//	"359" => array( "d_name" => "jyoutai","h_name" => "),
			"360" => array( "d_name" => "","h_name" => "インターネット公開"),
			"361" => array( "d_name" => "","h_name" => "インターネット公開ＩＤ"),
			"362" => array( "d_name" => "","h_name" => "公開先フォーマットタイプ"),
			"363" => array( "d_name" => "","h_name" => "公開サイト"),
			"364" => array( "d_name" => "","h_name" => "物件公開（加盟店）"),
			"365" => array( "d_name" => "","h_name" => "物件公開（マッチングメール）"),
			"366" => array( "d_name" => "","h_name" => "物件公開（タッチパネル）"),

			"391" => array( "d_name" => "","h_name" => "交通起点・最寄施設"),
			"392" => array( "d_name" => "","h_name" => "賃貸保証料"),
			"393" => array( "d_name" => "","h_name" => "賃貸保証料（単位）"),
			"394" => array( "d_name" => "","h_name" => "賃貸保証料（区分）"),
			"395" => array( "d_name" => "","h_name" => "客付け業者へのメッセージ"),
			"396" => array( "d_name" => "","h_name" => "鍵情報"),
			"397" => array( "d_name" => "","h_name" => "業務委託手数料"),
			"398" => array( "d_name" => "","h_name" => "広告"),
			"399" => array( "d_name" => "","h_name" => "客付け業者による案内"),
			"400" => array( "d_name" => "","h_name" => "物件シート用備考"),
			"401" => array( "d_name" => "","h_name" => "インターネット初回公開日"),
/*
			"402" => array( "d_name" => "bukkenido","h_name" => "緯度(WGS)"),
			"403" => array( "d_name" => "bukkenkeido","h_name" => "経度(WGS)"),
			"404" => array( "d_name" => "","h_name" => "緯度(Tokyo97)"),
			"405" => array( "d_name" => "","h_name" => "経度(Tokyo97)"),
			"406" => array( "d_name" => "","h_name" => "地図公開"),
*/
			"407" => array( "d_name" => "","h_name" => "築年月不詳"),
			"408" => array( "d_name" => "","h_name" => "貸主連絡先"),
			"409" => array( "d_name" => "tochichisei","h_name" => "地勢"),
			"410" => array( "d_name" => "tochisetsudo","h_name" => "道路状況"),
			"411" => array( "d_name" => "tochisetsudo","h_name" => "接道状況"),
		//	"412" => array( "d_name" => "","h_name" => "),
		//	"413" => array( "d_name" => "","h_name" => "),
			"414" => array( "d_name" => "fudoimgcomment1","h_name" => "画像コメント１"),
			"415" => array( "d_name" => "fudoimgcomment2","h_name" => "画像コメント２"),
			"416" => array( "d_name" => "fudoimgcomment3","h_name" => "画像コメント３"),
		//	"417" => array( "d_name" => "fudoimgcomment4","h_name" => "画像コメント４"),
			"418" => array( "d_name" => "fudoimgcomment5","h_name" => "画像コメント５"),
			"419" => array( "d_name" => "fudoimgcomment6","h_name" => "画像コメント６"),
			"420" => array( "d_name" => "fudoimgcomment7","h_name" => "画像コメント７"),
		//	"421" => array( "d_name" => "fudoimgcomment8","h_name" => "画像コメント８")
	);

	$work_c21_2 =
	array(
			"430" => array( "d_name" => "fudoimg9","h_name" => "画像９"),
			"431" => array( "d_name" => "fudoimg10","h_name" => "画像１０"),
			"432" => array( "d_name" => "fudoimg11","h_name" => "画像１１"),
			"433" => array( "d_name" => "fudoimg12","h_name" => "画像１２"),
			"434" => array( "d_name" => "fudoimg13","h_name" => "画像１３"),
			"435" => array( "d_name" => "fudoimg14","h_name" => "画像１４"),
			"436" => array( "d_name" => "fudoimg15","h_name" => "画像１５"),
			"437" => array( "d_name" => "fudoimg16","h_name" => "画像１６"),
			"438" => array( "d_name" => "fudoimg17","h_name" => "画像１７"),
			"439" => array( "d_name" => "fudoimg18","h_name" => "画像１８"),
			"440" => array( "d_name" => "fudoimg19","h_name" => "画像１９"),
			"441" => array( "d_name" => "fudoimg20","h_name" => "画像２０"),
			"442" => array( "d_name" => "fudoimgcomment9","h_name" => "画像コメント９"),
			"443" => array( "d_name" => "fudoimgcomment10","h_name" => "画像コメント１０"),
			"444" => array( "d_name" => "fudoimgcomment11","h_name" => "画像コメント１１"),
			"445" => array( "d_name" => "fudoimgcomment12","h_name" => "画像コメント１２"),
			"446" => array( "d_name" => "fudoimgcomment13","h_name" => "画像コメント１３"),
			"447" => array( "d_name" => "fudoimgcomment14","h_name" => "画像コメント１４"),
			"448" => array( "d_name" => "fudoimgcomment15","h_name" => "画像コメント１５"),
			"449" => array( "d_name" => "fudoimgcomment16","h_name" => "画像コメント１６"),
			"450" => array( "d_name" => "fudoimgcomment17","h_name" => "画像コメント１７"),
			"451" => array( "d_name" => "fudoimgcomment18","h_name" => "画像コメント１８"),
			"452" => array( "d_name" => "fudoimgcomment19","h_name" => "画像コメント１９"),
			"453" => array( "d_name" => "fudoimgcomment20","h_name" => "画像コメント２０")
	);


	$work_c21_setsubi =
	array(	
		"10001" => array( "code" => "10001","name" => "楽器相談"),        // 楽器相談可
		"10002" => array( "code" => "10002","name" => "楽器不可"),        // 楽器不可
		"10101" => array( "code" => "10101","name" => "事務所可"),        // 事務所可
		"10202" => array( "code" => "10202","name" => "事務所不可"),        // 事務所不可
		"10301" => array( "code" => "10301","name" => "二人入居可"),        // ２人入居可
		"10302" => array( "code" => "10302","name" => "二人入居不可"),        // ２人入居不可
		"10401" => array( "code" => "10401","name" => "男性専用"),        // 男性限定
		"10402" => array( "code" => "10402","name" => "女性専用"),        // 女性限定
		"10501" => array( "code" => "10501","name" => "独身専用"),        // 単身者限定
		"10502" => array( "code" => "10502","name" => "単身希望"),        // 単身者希望
		"10503" => array( "code" => "10503","name" => "単身不可"),        // 単身者不可
		"10601" => array( "code" => "10601","name" => "法人限定"),        // 法人限定 
		"10602" => array( "code" => "10602","name" => "法人契約希望"),        // 法人希望
		"10603" => array( "code" => "10603","name" => "法人不可"),        // 法人不可
		"10701" => array( "code" => "10701","name" => "学生専用"),        // 学生限定
		"10702" => array( "code" => "10702","name" => "学生歓迎"),        // 学生歓迎
		"10801" => array( "code" => "10801","name" => "高齢者限定"),        // 高齢者限定
		"10802" => array( "code" => "10802","name" => "高齢者歓迎"),        // 高齢者歓迎
		"10901" => array( "code" => "10901","name" => "ペット対応"),        // ペット対応
		"10902" => array( "code" => "10902","name" => "ペット可"),        // ペット可能
		"10903" => array( "code" => "10903","name" => "ペット不可"),        // ペット不可
	//	"11001" => array( "code" => "11001","name" => ""),        // 建築条件付
	//	"11002" => array( "code" => "11002","name" => ""),        // 建築条件無
	//	"11101" => array( "code" => "11101","name" => ""),        // 公庫利用可
		"11201" => array( "code" => "11201","name" => "手付金保証"),        // 手付金保証有
	//	"11301" => array( "code" => "11301","name" => ""),        // 定期借家権
	//	"11401" => array( "code" => "11401","name" => ""),        // 保証付住宅
		"11501" => array( "code" => "11501","name" => "保証人要"),        // 保証人要 
		"11502" => array( "code" => "11502","name" => "保証人不要"),        // 保証人不要
/*
		"11701" => array( "code" => "11701","name" => ""),        // 特定優良賃貸住宅
		"11901" => array( "code" => "11901","name" => ""),        // 家賃保障付
		"12001" => array( "code" => "12001","name" => ""),        // 満室賃貸中
		"12201" => array( "code" => "12201","name" => ""),        // 分譲賃貸
		"20001" => array( "code" => "20001","name" => ""),        // 公営水道
		"20002" => array( "code" => "20002","name" => ""),        // 井戸
		"20099" => array( "code" => "20099","name" => ""),        // その他水道
		"20101" => array( "code" => "20101","name" => ""),        // 都市ガス
		"20102" => array( "code" => "20102","name" => ""),        // プロパンガス
		"20199" => array( "code" => "20199","name" => ""),        // その他ガス
		"20201" => array( "code" => "20201","name" => ""),        // 排水下水
		"20202" => array( "code" => "20202","name" => ""),        // 排水浄化槽
		"20203" => array( "code" => "20203","name" => ""),        // 排水汲取
		"20299" => array( "code" => "20299","name" => ""),        // 排水その他
		"20301" => array( "code" => "20301","name" => ""),        // バス専用
		"20302" => array( "code" => "20302","name" => ""),        // バス共同
		"20303" => array( "code" => "20303","name" => ""),        // バスなし
		"20401" => array( "code" => "20401","name" => ""),        // トイレ専用
		"20402" => array( "code" => "20402","name" => ""),        // トイレ共同
		"20403" => array( "code" => "20403","name" => ""),        // トイレなし
*/
		"20501" => array( "code" => "20501","name" => "バストイレ別"),        // バス・トイレ別
		"20601" => array( "code" => "20601","name" => "シャワー"),        // シャワー
		"20701" => array( "code" => "20701","name" => "ガスコンロ可"),        // ガスコンロ
		"20702" => array( "code" => "20702","name" => "電気コンロ"),        // 電気コンロ
		"20703" => array( "code" => "20703","name" => "IHクッキングヒーター"),        // IHコンロ
		"20801" => array( "code" => "20801","name" => "コンロ一口"),        // 一口コンロ
		"20802" => array( "code" => "20802","name" => "コンロ二口"),        // 二口コンロ
		"20803" => array( "code" => "20803","name" => "コンロ三口"),        // 三口コンロ
		"20804" => array( "code" => "20804","name" => "コンロ四口以上"),        // 四口以上コンロ
		"20901" => array( "code" => "20901","name" => "システムキッチン"),        // システムキッチン
		"21001" => array( "code" => "21001","name" => "給湯器"),        // 給湯
		"21101" => array( "code" => "21101","name" => "追い焚き"),        // 追い焚き
		"21201" => array( "code" => "21201","name" => "シャンプードレッサー"),        // 洗髪洗面化粧台
		"21301" => array( "code" => "21301","name" => "冷房"),        // 冷房
//		"21302" => array( "code" => "21302","name" => ""),        // 暖房
		"21303" => array( "code" => "21303","name" => "石油暖房"),        // 石油暖房
		"21304" => array( "code" => "21304","name" => "エアコン"),        // エアコン 
		"21401" => array( "code" => "21401","name" => "トランクルーム"),        // トランクルーム
		"21501" => array( "code" => "21501","name" => "床下収納"),        // 床下収納
		"21601" => array( "code" => "21601","name" => "Ｗクローゼット"),        // W.INクローゼット
		"21701" => array( "code" => "21701","name" => "ロフト"),        // ロフト付き
		"21801" => array( "code" => "21801","name" => "室内洗濯機置場"),        // 室内洗濯機置き場
		"21802" => array( "code" => "21802","name" => "洗濯機置場"),        // 洗濯機置き場有 
		"21901" => array( "code" => "21901","name" => "ＣＡＴＶ"),        // CATV
		"22001" => array( "code" => "22001","name" => "ＣＳ受信可"),        // CSアンテナ
		"22101" => array( "code" => "22101","name" => "ＢＳアンテナ"),        // BSアンテナ
		"22201" => array( "code" => "22201","name" => "有線放送"),        // 有線放送
		"22301" => array( "code" => "22301","name" => "オートロック"),        // オートロック
		"22401" => array( "code" => "22401","name" => "エレベーター"),        // エレベータ
		"22501" => array( "code" => "22501","name" => "専用庭"),        // 専用庭
		"22601" => array( "code" => "22601","name" => "出窓"),        // 出窓
		"22701" => array( "code" => "22701","name" => "バルコニー"),        // バルコニー
		"22801" => array( "code" => "22801","name" => "フローリング"),        // フローリング
		"22901" => array( "code" => "22901","name" => "冷蔵庫"),        // 冷蔵庫
		"23001" => array( "code" => "23001","name" => "宅配ボックス"),        // 宅配ボックス
		"23101" => array( "code" => "23101","name" => "駐輪場"),        // 駐輪場
		"23201" => array( "code" => "23201","name" => "バイク置き場"),        // バイク置き場
		"23301" => array( "code" => "23301","name" => "タイル貼り"),        // タイル貼り
		"23401" => array( "code" => "23401","name" => "インターネット対応済"),        // インターネット有
		"23402" => array( "code" => "23402","name" => "高速インターネット"),        // 高速インターネット
		"23403" => array( "code" => "23403","name" => "光ファイバー"),        // 光ファイバー
		"23501" => array( "code" => "23501","name" => "フリーアクセス"),        // フリーアクセス
		"23601" => array( "code" => "23601","name" => "角部屋"),        // 角部屋
		"23701" => array( "code" => "23701","name" => "床暖房"),        // 床暖房
		"23801" => array( "code" => "23801","name" => "TVドアホン"),        // TVドアホン
		"23901" => array( "code" => "23901","name" => "二世帯住宅"),        // 二世帯住宅
		"24001" => array( "code" => "24001","name" => "住宅性能保証付"),        // 住宅性能保証付
		"24101" => array( "code" => "24101","name" => "バリアフリー"),        // バリアフリー
	//	"24201" => array( "code" => "24201","name" => ""),        // 温水洗浄便座
		"24301" => array( "code" => "24301","name" => "デザイナーズ"),        // デザイナーズ
		"24401" => array( "code" => "24401","name" => "オール電化"),        // オール電化
		"24501" => array( "code" => "24501","name" => "カウンターキッチン"),        // カウンターキッチン
		"24601" => array( "code" => "24601","name" => "浴室乾燥機"),        // 浴室乾燥機
/*
		"25001" => array( "code" => "25001","name" => ""),        // 敷金礼金0
		"25002" => array( "code" => "25002","name" => ""),        // 未入居
		"25003" => array( "code" => "25003","name" => ""),        // リノベーション
		"25004" => array( "code" => "25004","name" => ""),        // ルームシェア・ハウス
		"25005" => array( "code" => "25005","name" => ""),        // 駐車場有)
		"26002" => array( "code" => "26002","name" => ""),        //角地
*/
		"26003" => array( "code" => "26003","name" => "地デジ対応"),        //地デジ対応
		"26004" => array( "code" => "26004","name" => "セキュリティシステム"),        //セキュリティシステム
		"26005" => array( "code" => "26005","name" => "カードキーシステム"),        //カードキーシステム


	);



if ( ! function_exists( 'fudo_ken_id' ) ){
function fudo_ken_id($middle_area_name) {
	$middle_area_id = '';

	if($middle_area_name !=''){
		switch ($middle_area_name) {
			case '北海道'  : $middle_area_id = '01' ; break;
			case '青森県'  : $middle_area_id = '02' ; break;
			case '岩手県'  : $middle_area_id = '03' ; break;
			case '宮城県'  : $middle_area_id = '04' ; break;
			case '秋田県'  : $middle_area_id = '05' ; break;
			case '山形県'  : $middle_area_id = '06' ; break;
			case '福島県'  : $middle_area_id = '07' ; break;
			case '茨城県'  : $middle_area_id = '08' ; break;
			case '栃木県'  : $middle_area_id = '09' ; break;
			case '群馬県'  : $middle_area_id = '10' ; break;
			case '埼玉県'  : $middle_area_id = '11' ; break;
			case '千葉県'  : $middle_area_id = '12' ; break;
			case '東京都'  : $middle_area_id = '13' ; break;
			case '神奈川県': $middle_area_id = '14' ; break;
			case '新潟県'  : $middle_area_id = '15' ; break;
			case '富山県'  : $middle_area_id = '16' ; break;
			case '石川県'  : $middle_area_id = '17' ; break;
			case '福井県'  : $middle_area_id = '18' ; break;
			case '山梨県'  : $middle_area_id = '19' ; break;
			case '長野県'  : $middle_area_id = '20' ; break;
			case '岐阜県'  : $middle_area_id = '21' ; break;
			case '静岡県'  : $middle_area_id = '22' ; break;
			case '愛知県'  : $middle_area_id = '23' ; break;
			case '三重県'  : $middle_area_id = '24' ; break;
			case '滋賀県'  : $middle_area_id = '25' ; break;
			case '京都府'  : $middle_area_id = '26' ; break;
			case '大阪府'  : $middle_area_id = '27' ; break;
			case '兵庫県'  : $middle_area_id = '28' ; break;
			case '奈良県'  : $middle_area_id = '29' ; break;
			case '和歌山県': $middle_area_id = '30' ; break;
			case '鳥取県'  : $middle_area_id = '31' ; break;
			case '島根県'  : $middle_area_id = '32' ; break;
			case '岡山県'  : $middle_area_id = '33' ; break;
			case '広島県'  : $middle_area_id = '34' ; break;
			case '山口県'  : $middle_area_id = '35' ; break;
			case '徳島県'  : $middle_area_id = '36' ; break;
			case '香川県'  : $middle_area_id = '37' ; break;
			case '愛媛県'  : $middle_area_id = '38' ; break;
			case '高知県'  : $middle_area_id = '39' ; break;
			case '福岡県'  : $middle_area_id = '40' ; break;
			case '佐賀県'  : $middle_area_id = '41' ; break;
			case '長崎県'  : $middle_area_id = '42' ; break;
			case '熊本県'  : $middle_area_id = '43' ; break;
			case '大分県'  : $middle_area_id = '44' ; break;
			case '宮崎県'  : $middle_area_id = '45' ; break;
			case '鹿児島県': $middle_area_id = '46' ; break;
			case '沖縄県'  : $middle_area_id = '47' ; break;
		}
	}
	return $middle_area_id;
}
}



?>
