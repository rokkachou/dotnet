<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress3.9
 * @subpackage Fudousan Plugin
 * Version: 1.4.6
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */

define('WP_USE_THEMES', false);

/** Loads the WordPress Environment and Template */
require_once '../../../../wp-blog-header.php';


//global $post;
global $is_iphone;


the_post();

$post_id = myIsNum_f($_GET['p']);

$kaiin = 0;
if( !is_user_logged_in() && get_post_meta($post_id, 'kaiin', true) == 1 ) $kaiin = 1;


//SSL
$fudou_ssl_site_url = get_option('fudou_ssl_site_url');
if( $fudou_ssl_site_url !=''){
	$site_url = $fudou_ssl_site_url;
}else{
	$site_url = get_option('siteurl');
}


status_header( 200 );

?>
<!DOCTYPE html>
<html dir="ltr" lang="ja">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>問合せ</title>
<?php 
	if ( $is_iphone ) {
		echo '<meta name="viewport" content="width=320; initial-scale=0.9; maximum-scale=1.0; user-scalable=0;" />';
	}
	echo '<link rel="stylesheet" id="contact-form-7-css"  href="' . $site_url . '/wp-content/plugins/contact-form-7/includes/css/styles.css" type="text/css" media="all" />';
	echo '<script type="text/javascript" src="' . $site_url . '/wp-content/plugins/contact-form-7/includes/js/jquery.form.js"></script>';
	echo '<script type="text/javascript" src="' . $site_url . '/wp-content/plugins/contact-form-7/includes/js/scripts.js"></script>';
?>
<script type="text/javascript" src="<?php echo $site_url;?>/wp-includes/js/jquery/jquery.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $site_url;?>/wp-content/plugins/fudou/themes/corners2010.css" />


<style type="text/css">
<!--

<?php if ( $is_iphone ) { ?>
	#ssl_contact {
		background: none repeat scroll 0 0 #FFFFFF;
		border: 1px solid #E5E5E5;
		border-radius: 11px 11px 11px 11px;
		box-shadow: 0 4px 18px #C8C8C8;
		font-weight: normal;
		padding: 1em 1em 2em 1em;
		line-height: 1.8;
		margin: 8px;
	}
	#ssl_contact h3 {
		border-bottom: 1px dotted #CCCCCC;
		border-left: 3px solid #CCCCCC;
		margin: 14px 0 14px;
		padding: 0 0 0 10px;
		text-shadow: 1px 1px 0 #CCCCCC;
		color: #777777;
	}
	#ssl_contact p {
		overflow: hidden;	/* モダンブラウザ向け */
		zoom: 1; /* IE向け */
	}
	#ssl_contact label {
	    display: block;
	    float: left;
	    padding-right: 15px;
	}
	#ssl_contact label input { margin-left: 10px; }
	#ssl_contact input[type="text"], textarea {
	    background: none repeat scroll 0 0 #FBFBFB;
	    border: 1px solid #E5E5E5;
	    box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1) inset;
	    padding: 2px;
		margin: 2px 6px 16px 0px;
	    font-size: 20px;
		width: 97%;
	}

<?php }else{ ?>

	#ssl_contact {
		background: none repeat scroll 0 0 #FFFFFF;
		border: 1px solid #E5E5E5;
		border-radius: 11px 11px 11px 11px;
		box-shadow: 0 4px 18px #C8C8C8;
		font-weight: normal;
		padding: 5px 25px 40px;
		line-height: 1.2;
		width: 550px;
		margin: 8px;
	}

	#ssl_contact h3 {
		border-bottom: 1px dotted #CCCCCC;
		border-left: 3px solid #CCCCCC;
		margin: 14px 0 14px;
		padding: 5px 0 5px 10px;
		text-shadow: 1px 1px 0 #CCCCCC;
		color: #777777;
	}

	#ssl_contact p {
		overflow: hidden;	/* モダンブラウザ向け */
		zoom: 1; /* IE向け */
	}

	#ssl_contact label {
	    display: block;
	    float: left;
	    padding-right: 15px;
	}

	#ssl_contact label input { margin-left: 10px; }

	#ssl_contact input[type="text"], textarea {
	    background: none repeat scroll 0 0 #FBFBFB;
	    border: 1px solid #E5E5E5;
	    box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1) inset;
	    padding: 2px;
		margin: 2px 6px 2px 0px;
	    font-size: 13px;
		width: 97%;
	}

<?php } ?>
-->
</style>

</head>

<body>
<div id="ssl_contact">

<?php 

	//問合せフォーム
	if( $kaiin == 1 ) {
		if($post_id !=''){
			$content = get_option('fudo_form');
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);
			echo $content;
		}
	}else{
		if($post_id !=''){
			$content = get_option('fudo_form');
			$content = apply_filters('the_content', $content);
			$content = str_replace(']]>', ']]&gt;', $content);
			echo $content;
		}
	}

?>

<?php if ( $is_iphone ) 
	echo '<div align="right"><a href="' . get_option('siteurl') . '/?post_type=fudo&amp;p='.$post_id.'">→物件詳細へ戻る</a></div>';
	echo '</div>';
?>

</div><!-- .#ssl_contact -->
<script type='text/javascript'>
	/* <![CDATA[ */
	var _wpcf7 = {"loaderUrl":"..\/..\/contact-form-7\/images\/ajax-loader.gif","sending":"\u9001\u4fe1\u4e2d ..."};
	/* ]]> */
</script>

</body>
</html>

