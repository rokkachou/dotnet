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
require_once '../../../wp-blog-header.php';

//$wpdb->show_errors();



if ( current_user_can( 'edit_posts' ) ) {

	global $is_fudoukaiin,$is_fudoumail;

	status_header( 200 );

	global $wpdb;

	$u_date = 1;
	if( !empty($u_date) ){


		$sql  = "SELECT DISTINCT U.ID, U.user_login, U.user_email";
		$sql .=  " FROM $wpdb->users  AS U";
		$sql .=  " INNER JOIN $wpdb->usermeta AS UM ON U.ID = UM.user_id  ";
		$sql .=  " WHERE UM.meta_key  = '".$wpdb->prefix."user_level' AND UM.meta_value ='0'";
		$sql .=  " ORDER BY U.ID ASC";

	//	$sql = $wpdb->prepare($sql,'');
		$metas = $wpdb->get_results( $sql,  ARRAY_A );

		if(!empty($metas)) {

			ob_start();

			$header_filename = 'user'.date('Y-m-j-his') . '.xls';     
			$header_filename = 'Content-Disposition: attachment; filename="'.$header_filename.'"';
			header("Content-Type: application/vnd.ms-excel");
			header($header_filename);


?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<style>
<!--
td{
	mso-style-parent:style0;
	mso-number-format:"\@";
	font-family:"ＭＳ Ｐゴシック", monospace;
}
-->
</style>
</head>
<body>
<?php


			echo '<table border="1">';
				echo '<tr bgcolor="#cccccc">';
				echo '<td>ユーザーID</td>';
				echo '<td>ユーザー名</td>';
				echo '<td>名前</td>';
				echo '<td>メールアドレス</td>';

				echo '<td>郵便番号</td>';
				echo '<td>住所</td>';
				echo '<td>電話番号</td>';

			if($is_fudoumail){
				echo '<td>ログイン数</td>';
				echo '<td>最終ログイン</td>';

				echo '<td>メール配信</td>';
				echo '<td>メール送信数</td>';
				echo '<td>最終送信日</td>';
			}
				echo '</tr>';

			$rst_co = 0;

			foreach ( $metas as $meta ) {

				$rst_co = $rst_co + 1 ;

				$user_id= $meta['ID'];
				$user_login= $meta['user_login'];
				$user_email= $meta['user_email'];

				$user_mail = get_user_meta( $user_id, 'user_mail', true);


				$first_name	= get_user_meta( $user_id, 'first_name', true);
			//	if($first_name !='') $first_name = mb_convert_encoding($first_name,"SJIS","UTF-8");

				$last_name	= get_user_meta( $user_id, 'last_name', true);
			//	if($last_name !='') $first_name	= mb_convert_encoding($last_name,"SJIS","UTF-8");

				$user_zip = get_user_meta( $user_id, 'user_zip', true);
				$user_adr = get_user_meta( $user_id, 'user_adr', true);
				$user_tel = get_user_meta( $user_id, 'user_tel', true);

				$login_count = get_user_meta( $user_id, 'login_count', true);
				$login_date = get_user_meta( $user_id, 'login_date', true);

				$mail_count = get_user_meta( $user_id, 'mail_count', true);
				$mail_date = get_user_meta( $user_id, 'mail_date', true);
			//	if( $mail_date != '' ) $mail_date = date('Y-m-d H:i:s' ,$mail_date);



				echo '<tr>';
				//ユーザーID
					echo '<td>' . $user_id . '</td>';
				//ユーザー名
					echo '<td>' . $user_login . '</td>';
				//名前
					echo '<td>' . $last_name.' '.$first_name . '</td>';
				//メールアドレス
					echo '<td>' . $user_email . '</td>';


				//郵便番号
					echo '<td>' . $user_zip . '</td>';
				//住所
					echo '<td>' . $user_adr . '</td>';
				//電話番号
					echo '<td>' . $user_tel . '</td>';

			if($is_fudoumail){


				//ログイン数
					echo '<td>' . $login_count . '</td>';
				//最終ログイン
					echo '<td>' . $login_date . '</td>';


				//メルマガ配信
				if($user_mail == 1 ){
					echo '<td>可</td>';
				}else{
					echo '<td>不可</td>';
				}

				//メール送信数
					echo '<td>' . $mail_count . '</td>';
				//最終送信日
					echo '<td>' . $mail_date . '</td>';

			}
				echo '</tr>';

			}

			echo '</table>';
			echo '</body>';
			echo '</html>';

			ob_end_flush();

		}else{
			echo '物件がありませんでした';
		}

	}else{
		echo '物件がありませんでした。';
	}
}	//is_user_logged_in() 

//$wpdb->print_error();


?>
