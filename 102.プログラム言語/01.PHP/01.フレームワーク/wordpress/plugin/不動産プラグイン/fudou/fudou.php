<?php
/*
Plugin Name: Fudousan Plugin
Plugin URI: http://nendeb.jp/
Description: Fudousan Plugin for Real Estate
Version: 1.4.6
Author: nendeb
Author URI: http://nendeb.jp/
License: GPLv2 or later
*/

// Define current version constant
define( 'FUDOU_VERSION', '1.4.6' );


/*  Copyright 2014 nendeb (email : nendeb@gmail.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if (!defined('WP_CONTENT_URL'))
      define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if (!defined('WP_CONTENT_DIR'))
      define('WP_CONTENT_DIR', ABSPATH.'wp-content');
if (!defined('WP_PLUGIN_URL'))
      define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
if (!defined('WP_PLUGIN_DIR'))
      define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

if (!defined('FUDOU_SSL_MODE'))
      define('FUDOU_SSL_MODE', 1);

if (!defined('FUDOU_IMG_MAX'))
      define('FUDOU_IMG_MAX', 30);

if (!defined('FUDOU_TRA_COMMENT'))
      define('FUDOU_TRA_COMMENT', 0);



include_once( dirname(__FILE__).'/data/work-fudo.php');
require_once 'fudo-functions.php';
require_once 'fudo-widget.php';
require_once 'csv_import/csv_import.php';
require_once 'admin_fudou.php';
require_once 'admin_fudou2.php';
require_once 'fudo-widget2.php';
require_once 'fudo-widget3.php';
require_once 'fudo-widget4.php';



remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
/*
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_enqueue_scripts', 1); 
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0); 
remove_action('wp_head', 'feed_links_extra',3,0); 
remove_action('wp_head', 'index_rel_link'); 
remove_action('wp_head', 'parent_post_rel_link'); 
remove_action('wp_head', 'start_post_rel_link'); 
remove_action('wp_head', 'rel_canonical'); 
*/

/*
//WordPressのバージョンアップ通知を非表示にする
add_filter( 'pre_site_transient_update_core', '__return_zero' );
remove_action( 'wp_version_check', 'wp_version_check' );
remove_action( 'admin_init', '_maybe_update_core' );
*/

//すべての自動更新を無効化する
//add_filter( 'automatic_updater_disabled', '__return_true' );


/*
 * doing_it_wrong
 * @since WordPress3.9
*/
//add_action( 'doing_it_wrong_trigger_error', '__return_false' );


/*
 * xml-rpc機能を無効
*/
//add_filter( 'xmlrpc_methods' , function( $methods ) { unset( $methods['pingback.ping'] ); return $methods; });



//rss 会員
remove_filter('do_feed_rdf', 'do_feed_rdf', 10);
remove_filter('do_feed_rss', 'do_feed_rss', 10);
remove_filter('do_feed_rss2', 'do_feed_rss2', 10);
remove_filter('do_feed_atom', 'do_feed_atom', 10);

//rss 会員
function custom_feed_rdf_fudou() {
	$template_file = WP_PLUGIN_DIR . '/fudou/themes/feed-rdf.php';
	load_template( $template_file );
}
add_action('do_feed_rdf', 'custom_feed_rdf_fudou', 10, 1);

function custom_feed_rss_fudou() {
	$template_file = WP_PLUGIN_DIR . '/fudou/themes/feed-rss.php';
	load_template( $template_file );
}
add_action('do_feed_rss', 'custom_feed_rss_fudou', 10, 1);

function custom_feed_rss2_fudou( $for_comments ) {
	$template_file = WP_PLUGIN_DIR . '/fudou/themes/feed-rss2' . ( $for_comments ? '-comments' : '' ) . '.php';
	load_template( $template_file );
}
add_action('do_feed_rss2', 'custom_feed_rss2_fudou', 10, 1);

function custom_feed_atom_fudou( $for_comments ) {
	$template_file = WP_PLUGIN_DIR . '/fudou/themes/feed-atom' . ( $for_comments ? '-comments' : '' ) . '.php';
	load_template( $template_file );
}
add_action('do_feed_atom', 'custom_feed_atom_fudou', 10, 1); 

//RSS フィード
function rss_get_posts_fudou( $query ) {
	// theme check
	if ( function_exists('wp_get_theme') ) {
		$theme_ob = wp_get_theme();
		$template_name = $theme_ob->template;
	}else{
		$template_name = get_option('template');
	}

	if( $template_name != 'twentyfourteen' ) {
	        if ( ( is_home() && empty( $query->query_vars['suppress_filters'] ) ) || is_feed() ) {
			$query->set( 'post_type', array( 'post','page','fudo' ) );
		}
	}else{
	        if ( is_feed() ) {
			$query->set( 'post_type', array( 'post','page','fudo' ) );
		}
	}
	return $query;
}
add_filter( 'pre_get_posts', 'rss_get_posts_fudou' );



//データーベース設定
function init_data_tables_fudou() {
	include_once( dirname(__FILE__).'/data/fudo-configdatabase.php');
	databaseinstallation_fudo(0);
}
register_activation_hook(__FILE__,'init_data_tables_fudou');

//データーベース他 チェック
function databaseinstallation_warnings_fudou() {
	global $wpdb;
	$table_name1 = $wpdb->prefix . "area_middle_area";
	$table_name2 = $wpdb->prefix . "area_narrow_area";
	$table_name3 = $wpdb->prefix . "train_area_rosen";
	$table_name4 = $wpdb->prefix . "train_rosen";
	$table_name5 = $wpdb->prefix . "train_station";

	$results1 = $wpdb->get_var("show tables like '$table_name1'") ;
	$results2 = $wpdb->get_var("show tables like '$table_name2'") ;
	$results3 = $wpdb->get_var("show tables like '$table_name3'") ;
	$results4 = $wpdb->get_var("show tables like '$table_name4'") ;
	$results5 = $wpdb->get_var("show tables like '$table_name5'") ;

	if( empty($results1) || empty($results2) || empty($results3) || empty($results4) || empty($results5) ){
		function databaseinstallation_notices_main() {
			echo '<div class="error" style="text-align: center;"><p>データーベースを登録できませんでした。サーバーに問題があるのかも知れません。[Fudousan Plugin]</p></div>';
		}
		add_action('admin_notices', 'databaseinstallation_notices_main');
	}

	// ダッシュボードウィジェット
	add_action('wp_dashboard_setup', 'fudo_add_dashboard_widgets' );
	add_action('wp_dashboard_setup', 'fudodl_add_dashboard_widgets' );
	if ( is_multisite() ) {
		function multi_site_notices() {
			echo '<div class="error" style="text-align: center;"><p>マルチサイトでは利用できません。</p></div>';
		}
		add_action('admin_notices', 'multi_site_notices');
	}
	//パーマリンクチェック
	$permalink_structure = get_option('permalink_structure');
	if ( $permalink_structure != '' ) {
		function permalink_notices() {
			echo '<div class="error" style="text-align: center;"><p>パーマリンクはデフォルトにしてください。　<a href="options-permalink.php">パーマリンク設定</a></p></div>';
		}
		add_action('admin_notices', 'permalink_notices');
	}
}
add_action('admin_init', 'databaseinstallation_warnings_fudou');



/**
 *
 * 不動産プラグインチェック
 *
 * @since Fudousan Plugin 1.4.4
 */
function fudou_active_plugins_check(){
	global $is_fudouktai,$is_fudoumap,$is_fudoukaiin,$is_fudoumail,$is_fudourains,$is_fudoucsv,$is_fudouapaman,$is_fudouhistory,$is_fudoutopslider;
	global $is_fudourains_nishi,$is_fudourains_chubu;

	$fudo_active_plugins = get_option('active_plugins');
	if(is_array($fudo_active_plugins)) {
		foreach($fudo_active_plugins as $meta_box){
			if( $meta_box == 'fudouapaman/fudouapaman.php') $is_fudouapaman=true;
			if( $meta_box == 'fudoucsv/fudoucsv.php') $is_fudoucsv=true;
			if( $meta_box == 'fudouhistory/fudouhistory.php') $is_fudouhistory=true;
			if( $meta_box == 'fudoukaiin/fudoukaiin.php') $is_fudoukaiin=true;
			if( $meta_box == 'fudouktai/fudouktai.php') $is_fudouktai=true;
			if( $meta_box == 'fudoumail/fudoumail.php') $is_fudoumail=true;
			if( $meta_box == 'fudoumap/fudoumap.php') $is_fudoumap=true;
			if( $meta_box == 'fudourains/fudourains.php') $is_fudourains=true;
			if( $meta_box == 'fudoutopslider/fudoutopslider.php') $is_fudoutopslider=true;
			if( $meta_box == 'fudourains_nishi/fudourains_nishi.php') $is_fudourains_nishi=true;
			if( $meta_box == 'fudourains_chubu/fudourains_chubu.php') $is_fudourains_chubu=true;
		}
	}
}
add_action('init', 'fudou_active_plugins_check');



/**
 *
 * SSL利用時に add thickbox
 *
 * @since Fudousan Plugin 1.0.0
 */
function fudou_active_thickbox(){
	global $is_iphone;
	$fudou_ssl_site_url = get_option('fudou_ssl_site_url');
	if( !$is_iphone && $fudou_ssl_site_url !=''){
		if (function_exists('add_thickbox')) add_thickbox();
	}
}
add_action('init', 'fudou_active_thickbox');



/**
 *
 * 物件詳細テンプレート切替
 *
 * @since Fudousan Plugin 1.4.2
 */
function get_post_type_single_template_fudou($template = '') {
	if ( !is_multisite() ) {
		global $wp_query;
		$object = $wp_query->get_queried_object();
		if( !empty( $object->post_type ) ){
			if($object->post_type == 'fudo'){
				// theme check
				if ( function_exists('wp_get_theme') ) {
					$theme_ob = wp_get_theme();
					$template_name = $theme_ob->template;
				}else{
					$template_name = get_option('template');
				}

				if( $template_name== 'twentyfourteen' ){
					$template = locate_template(array('../../plugins/fudou/themes/single-fudo2014.php', 'single-fudo.php'));
				}else{
					$template = locate_template(array('../../plugins/fudou/themes/single-fudo.php', 'single-fudo.php'));
				}
			}
		}
	}
	return $template;
}
add_filter('template_include', 'get_post_type_single_template_fudou');



/**
 *
 * 物件リストテンプレート切替
 *
 * @since Fudousan Plugin 1.4.2
 */
function fudo_body_class($class) {
	$class[0] = 'archive archive-fudo';
	return $class;
}
function get_post_type_archive_template_fudou($template = '') {
	if ( !is_multisite() ) {
		if ( isset( $_GET['bukken'] ) || isset( $_GET['bukken_tag'] ) ) {
			status_header( 200 );

			// theme check
			if ( function_exists('wp_get_theme') ) {
				$theme_ob = wp_get_theme();
				$template_name = $theme_ob->template;
			}else{
				$template_name = get_option('template');
			}

			if( $template_name== 'twentyfourteen' ){
				$template = locate_template(array('../../plugins/fudou/themes/archive-fudo2014.php', 'archive.php'));
			}else{
				$template = locate_template(array('../../plugins/fudou/themes/archive-fudo.php', 'archive.php'));
			}
			add_filter('body_class', 'fudo_body_class');
		}
	}
	return $template;
}
add_filter('template_include', 'get_post_type_archive_template_fudou');



/**
 *
 * テーマ別 ヘッダーに jsや CSSを 追加
 *
 * @since Fudousan Plugin 1.4.2
 */
function add_header_css_js_fudou() {
	if ( !is_multisite() ) {

		echo "\n";
		//echo '<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />';
		//echo "\n";

		if ( function_exists('wp_get_theme') ) {
			$theme_ob = wp_get_theme();
			$template_name = $theme_ob->template;
		}else{
			$template_name = get_option('template');
		}

		switch ( $template_name ) {
			case "twentyten" :
				//twentyten
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/style2010.css" />';
				echo "\n";
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/corners2010.css" />';
				break;
			case "twentyeleven" :
				//twentyeleven 
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/style2011.css" />';
				echo "\n";
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/corners2011.css" />';
				break;
			case "twentytwelve" :
				//twentytwelve 
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/style2012.css" />';
				echo "\n";
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/corners2012.css" />';
				//for IE8/7
				$ua = getenv('HTTP_USER_AGENT');
				switch (true) {
					case (preg_match('/MSIE 7/', $ua)):
						echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
						break;
					case (preg_match('/MSIE 8/', $ua)):
						echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
						break;
				}
				//for debug
				//echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
				break;
			case "twentythirteen" :
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/style2013.css" />';
				echo "\n";
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/corners2013.css" />';
				//for IE8/7
				$ua = getenv('HTTP_USER_AGENT');
				switch (true) {
					case (preg_match('/MSIE 7/', $ua)):
						echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
						break;
					case (preg_match('/MSIE 8/', $ua)):
						echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
						break;
				}
				//for debug
				//echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
				break;


			case "twentyfourteen" :
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/style2014.css" />';
				echo "\n";
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/corners2014.css" />';
				//for IE8/7
				$ua = getenv('HTTP_USER_AGENT');
				switch (true) {
					case (preg_match('/MSIE 7/', $ua)):
					//	echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
						break;
					case (preg_match('/MSIE 8/', $ua)):
						echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
						break;
				}
				//for debug
				//echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/respond.min.js"></script>';
				break;

			default:
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/style2012.css" />';
				echo "\n";
				echo '<link rel="stylesheet" type="text/css" media="all" href="'.WP_PLUGIN_URL.'/fudou/themes/corners2012.css" />';
				break;

		}

		echo "\n\n";
		echo '<!-- Fudousan Plugin Ver.'.FUDOU_VERSION.' -->';
		echo "\n\n";

		//ヘッダ埋め込みタグ
		if( get_option('fudo_head_tag') != '' ) echo get_option('fudo_head_tag') . "\n";
	}
}
add_action('wp_head', 'add_header_css_js_fudou');



/**
 *
 * トップテンプレート切替
 *
 * @since Fudousan Plugin 1.4.2
 * @param  $template
 * @return $template
 */
function get_post_type_top_template_fudou( $template = '' ) {
	if ( is_front_page() ) {

		if ( function_exists('wp_get_theme') ) {
			$theme_ob = wp_get_theme();
			$template_name = $theme_ob->template;
		}else{
			$template_name = get_option('template');
		}
		if( $template_name== 'twentyfourteen' ){
			$template = locate_template(array('../../plugins/fudou/themes/home2014.php', 'index.php'));
		}else{
			$template = locate_template(array('../../plugins/fudou/themes/home.php', 'index.php'));
		}
	}
	return $template;
}
add_filter('template_include', 'get_post_type_top_template_fudou');




/**
 *
 * Contact Form 7 フォームにデーター追加
 *
 * @since Fudousan Plugin 1.0.0
 * @param  array $tag
 * @return array $tag
 */
function wpcf7_form_tag_filter_fudou( $tag ){
	global $post;
	if ( isset( $post->ID ) ){
		$post_id = $post->ID;
	}else{
		$post_id = '';
	}
	if ( ! is_array( $tag ) ) return $tag;

	if($post_id != ""){
		$name = $tag['name'];
		if($name == 'your-subject'){
			$tag_val = get_post_meta($post_id,'shikibesu',true);
			$tag_val .= " ".get_the_title();

			$kakaku_data = get_post_meta($post_id,'kakaku',true);
			if( get_post_meta($post_id, 'seiyakubi', true) != "" ) $kakaku_data = 'ご成約済'; 

			$kakakujoutai_data = get_post_meta($post_id,'kakakujoutai',true);
			if($kakakujoutai_data=="1")	$kakaku_data = '相談';
			if($kakakujoutai_data=="2")	$kakaku_data = '確定';
			if($kakakujoutai_data=="3")	$kakaku_data = '入札';
			
			
			if(is_numeric($kakaku_data)){
				$tag_val .= " ".floatval($kakaku_data)/10000;
				$tag_val .= ""."万円";
			}else{
				$tag_val .= " ".$kakaku_data;
			}
			$tag['values'] = (array)$tag_val;
		}

		if( is_user_logged_in() ){
			global $current_user;
			get_currentuserinfo();
			$pos = strpos( $current_user->user_email , 'pseudo.twitter.com' );
			if ($pos === false) {
				if($name == 'your-email') $tag['values'] = (array)$current_user->user_email;
				if($name == 'your-name')  $tag['values'] = (array)($current_user->user_lastname .' '. $current_user->user_firstname);
			}
		}
	}
	return $tag;
}
add_filter('wpcf7_form_tag', 'wpcf7_form_tag_filter_fudou', 11);



/**
 *
 * フッター埋め込みタグ
 *
 * @since Fudousan Plugin 1.0.0
 */
function footer_post_fudou() {
	if ( is_front_page() ){
		echo '<div id="nendebcopy"><a href="http://nendeb.jp" target="_blank" rel="nofollow" title="WordPress 不動産プラグイン">Fudousan Plugin Ver.'.FUDOU_VERSION.'</a></div>';
	}else{
		echo '<div id="nendebcopy">Fudousan Plugin Ver.'.FUDOU_VERSION.'</div>';
	}
	echo '<script type="text/javascript" src="'.WP_PLUGIN_URL.'/fudou/js/jquery.flatheights.js"></script>';
	//フッター埋め込みタグ
	if( get_option('fudo_footer_tag') != '' ) echo "\n" . get_option('fudo_footer_tag') . "\n";
	echo '<!-- Fudousan Plugin Ver.'.FUDOU_VERSION.' -->';
}
add_filter( 'wp_footer', 'footer_post_fudou' );



/**
 *
 * ビジュアルリッチエディターにボタンを追加
 *
 * @since Fudousan Plugin 1.0.0
 * @param  array $buttons
 * @return array $buttons
 */
function ilc_mce_buttons_fudou( $buttons ){
	array_push($buttons, "backcolor", "fontsizeselect", "cleanup");
	return $buttons;
}
add_filter("mce_buttons", "ilc_mce_buttons_fudou");



/**
 *
 * 物件番号検索
 *
 * @since Fudousan Plugin 1.0.0
 * @param  $where
 * @return $where
 */
function search_where_fudou( $where ){
	global $wpdb;
	if( is_search() ){
		$s = isset($_GET['s']) ? esc_sql(esc_attr( stripslashes($_GET['s']))) : '';
		$s = str_replace("&#039;","",$s);
		if ( $s !='' ) {
			$sql = $wpdb->prepare("SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = 'shikibesu' AND meta_value = %s",$s);
			$where .= " OR $wpdb->posts.ID in ($sql)";
			$sql2 = $wpdb->prepare("SELECT DISTINCT ID FROM $wpdb->posts WHERE post_status='publish' AND post_password = '' AND post_title LIKE %s" , like_escape($s) );
			$where .= " OR $wpdb->posts.ID in ($sql2)";
		}
	}
	return $where;
}
add_filter('posts_where', 'search_where_fudou' );


/**
 *
 * SEO keywords description.
 *
 * @since Fudousan Plugin 1.0.0
 */
function keywords_description_fudou() {
	global $post;
	if ( is_single() ){
		$fudokeywords = get_post_meta($post->ID,'fudokeywords',true);
		if($fudokeywords != ''){
			echo "\n";
			echo '<meta name="keywords" content="'.$fudokeywords.'" />';
		}

		$fudodescription = get_post_meta($post->ID,'fudodescription',true);
		if($fudodescription != ''){
			echo "\n";
			echo '<meta name="description" content="'.$fudodescription.'" />';
		}
      }
}
add_action('wp_head', 'keywords_description_fudou');


/**
 *
 * 半角数字チェック
 *
 * @since Fudousan Plugin 1.4.5
 * @param num|string|array $value.
 * @return num $value
 */
if (!function_exists('myIsNum_f')) {
	function myIsNum_f( $value ) {
		$data = array();
		if( is_array( $value ) ){
			foreach ( $value as $k => $v ) {
				if ( is_array($v) ){
					$data[$k] = myIsNum_f( $v );
				}else{
					if ( preg_match( "/\A[0-9]+\z/", $v ) ) {
						$data[] = $v;
					}
				}
			}
		}else{
			if ( preg_match( "/\A[0-9]+\z/", $value ) ) {
				return $value;
			}
		}
		if( !empty($data) ){
			return $data;
		}else{
			return '';
		}
	}
}


/**
 *
 * Adds template class to the array of body classes.
 *
 * @since Fudousan Plugin 1.3.0
 */
function fudou_template_body_class( $classes ) {

	if ( function_exists('wp_get_theme') ) {
		$theme_ob = wp_get_theme();
		$template_name = $theme_ob->template;
	}else{
		$template_name = get_option('template');
	}

	if ( $template_name != '' )
		$classes[] = $template_name;

	return $classes;
}
add_filter( 'body_class', 'fudou_template_body_class' );


/**
 *
 * Fixed nav menu class.
 *
 * @since Fudousan Plugin 1.4.2
*/
function fixed_custom_class_fudou( $classes = array(), $menu_item = false ) {
    	if( isset( $_GET['page'] ) && $_GET['page'] == 'map'){
		if( isset($classes[5]) ){
		if( $classes[5] == 'menu-item-home' && $classes[4] == 'current-menu-item' ){
			$classes[4] = '';
		}
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'fixed_custom_class_fudou', 10, 2 );


/**
 *
 * admin login user_check fudous.
 * checks for USER_AGENT,Dummy_Item,CSRF,wp-submit.
 *
 * @since Fudousan Plugin 1.4.0
 *
 */
if ( !function_exists('fudou_add_spam_login_user_check') && !function_exists('fudou_add_spam_login_nonce') ) {
	// admin login user_check fudou
	function fudou_add_spam_login_user_check() {
		$is_spams = false;
		//Empty USER AGENT
		$useragent = esc_attr($_SERVER["HTTP_USER_AGENT"]);
		if ( empty($useragent) ) $is_spams = true;

		$user_login = isset( $_POST["log"] ) ? $_POST["log"] : '';
		if(!$user_login) $user_login = isset( $_GET["log"] ) ? $_GET["log"] : '';
		$user_pass  = isset( $_POST["pwd"] ) ? $_POST["pwd"] : '';
		if(!$user_pass) $user_pass  = isset( $_GET["pwd"] ) ? $_GET["pwd"] : '';

		//Dummy Item
		$user_url = isset( $_POST["url"] ) ? $_POST["url"] : '';
		if(!$user_url) $user_url = isset( $_GET["url"] ) ? $_GET["url"] : '';
		if ( $user_url != '' )  $is_spams = true;

		//CSRF
		$login_nonce = isset($_POST['fudou_login_nonce']) ?  $_POST['fudou_login_nonce'] : '';
		if( !$login_nonce ) $login_nonce = isset($_GET['fudou_login_nonce']) ?  $_GET['fudou_login_nonce'] : '';
		if ( !$is_spams && $user_login && !$login_nonce )  $is_spams = true;
		if ( !$is_spams && $user_login && !wp_verify_nonce( $login_nonce, 'fudou_login_nonce') )  $is_spams = true;

		//wp-submit
		$wp_submit = isset($_POST['wp-submit']) ?  $_POST['wp-submit'] : '';
		if( !$wp_submit ) $wp_submit = isset( $_GET["wp-submit"] ) ? $_GET["wp-submit"] : '';
		if ( !$is_spams && $user_login && !$wp_submit )  $is_spams = true;
		//if ( !$is_spams && $user_login && $wp_submit == '%E3%83%AD%E3%82%B0%E3%82%A4%E3%83%B3' )  $is_spams = true;

		if ( $is_spams ){
			status_header( 403 );
			exit();
		}
	}
	add_action( 'login_init', 'fudou_add_spam_login_user_check', 2 );

	// admin login user_check CSRF & dummy item
	function fudou_add_spam_login_nonce() {
		echo '<input type="hidden" name="fudou_login_nonce" value="' .wp_create_nonce( 'fudou_login_nonce' ) . '" />';
		echo '<p class="form-url" style="display:none"><label>URL</label><input type="text" name="url" id="url" class="input" size="30" /></p>';
	}
	add_action( 'login_form', 'fudou_add_spam_login_nonce', 10 );
}
