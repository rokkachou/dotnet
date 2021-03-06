<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Edit or configure scripts used with My Calendar
function edit_my_calendar_behaviors() {
	global $wpdb;
	$mcdb = $wpdb;
	  
	if ( isset( $_POST['mc-js-save'] ) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce( $nonce,'my-calendar-nonce' ) ) die( "Security check failed" );
		$mc_caljs = $_POST['mc_caljs'];
		$mc_listjs = $_POST['mc_listjs'];
		$mc_minijs = $_POST['mc_minijs'];
		$mc_ajaxjs = $_POST['mc_ajaxjs'];
	
		$use_custom_js = ( isset( $_POST['mc_use_custom_js'] ) ) ? 1 : 0;
		update_option( 'mc_use_custom_js', $use_custom_js );
	
		update_option( 'mc_calendar_javascript', ( empty( $_POST['calendar_js'] ) ) ? 0 : 1 );
		update_option( 'mc_list_javascript', ( empty( $_POST['list_js'] ) ) ? 0 : 1 );
		update_option( 'mc_mini_javascript', ( empty( $_POST['mini_js'] ) ) ? 0 : 1 );
		update_option( 'mc_ajax_javascript', ( empty( $_POST['ajax_js'] ) ) ? 0 : 1 );
		// set js
		if ( isset( $_POST['mc_caljs'] ) ) {
			update_option( 'mc_listjs',$mc_listjs );
			update_option( 'mc_minijs',$mc_minijs );
			update_option( 'mc_caljs',$mc_caljs );
			update_option( 'mc_ajaxjs',$mc_ajaxjs );
		}
		  
		$mc_show_js = ($_POST['mc_show_js']=='')?'':$_POST['mc_show_js'];
		update_option('mc_show_js',$mc_show_js);   
		echo "<div class=\"updated\"><p><strong>".__('Behavior Settings saved','my-calendar').".</strong></p></div>";
	}

	$mc_listjs = stripcslashes(get_option('mc_listjs'));
	$list_js = get_option('mc_list_js');
	$mc_caljs = stripcslashes(get_option('mc_caljs'));
	$calendar_js = get_option( 'mc_calendar_js' );
	$mc_minijs = stripcslashes(get_option('mc_minijs'));
	$mini_js = get_option('mc_mini_js'); 
	$mc_ajaxjs = stripcslashes(get_option('mc_ajaxjs'));
	$ajax_js = get_option('mc_ajax_js'); 
	$mc_show_js = stripcslashes(get_option('mc_show_js'));
	// Now we render the form ?>
<div class="wrap jd-my-calendar">
	<?php my_calendar_check_db(); ?>
    <h2><?php _e('My Calendar Scripting','my-calendar'); ?></h2>
	<div class="postbox-container jcd-wide">
		<div class="metabox-holder">

		<div class="ui-sortable meta-box-sortables">
			<div class="postbox" id="cdiff">
			
				<h3><?php _e( 'My Calendar Script Manager','my-calendar' ); ?></h3>
				<div class="inside">	
				<form id="my-calendar" method="post" action="<?php echo admin_url( 'admin.php?page=my-calendar-behaviors' ); ?>">
				<div><input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'my-calendar-nonce' ); ?>" /></div>
				<p>
					<input type="checkbox" name="mc_use_custom_js" id="mc_use_custom_js" <?php mc_is_checked( 'mc_use_custom_js', 1 ); ?> /> <label for="mc_use_custom_js"><?php _e( 'Use Custom JS', 'my-calendar' ); ?></label>
				</p>
				<p>
					<label for="mc_show_js"><?php _e('Insert scripts on these pages (comma separated post IDs)','my-calendar'); ?></label> <input type="text" id="mc_show_js" name="mc_show_js" value="<?php echo $mc_show_js; ?>" />
				</p>  	
				<div class='controls'>
					<ul class="checkboxes">
						<li><input type="checkbox" id="calendar_js" name="calendar_js" value="1" <?php mc_is_checked('mc_calendar_js',1); ?>/> <label for="calendar_js"><?php _e('Disable Grid JS','my-calendar'); ?></label></li>
						<li><input type="checkbox" id="list_js" name="list_js" value="1" <?php mc_is_checked('mc_list_js',1); ?> /> <label for="list_js"><?php _e('Disable List JS','my-calendar'); ?></label></li>
						<li><input type="checkbox" id="mini_js" name="mini_js" value="1" <?php mc_is_checked('mc_mini_js',1); ?> /> <label for="mini_js"><?php _e('Disable Mini JS','my-calendar'); ?></label></li>
						<li><input type="checkbox" id="ajax_js" name="ajax_js" value="1" <?php mc_is_checked('mc_ajax_js',1); ?> /> <label for="ajax_js"><?php _e('Disable AJAX','my-calendar'); ?></label></li>
					</ul>
				</div>
				<?php if ( get_option( 'mc_use_custom_js' ) == 1 ) { ?>
					<p>
						<label for="calendar-js"><?php _e('Calendar Behaviors: Grid View','my-calendar'); ?></label><br /><textarea id="calendar-js" name="mc_caljs" rows="12" cols="80"><?php echo $mc_caljs; ?></textarea>
					</p>	
					<p>
						<label for="list-js"><?php _e('Calendar Behaviors: List View','my-calendar'); ?></label><br /><textarea id="list-js" name="mc_listjs" rows="12" cols="80"><?php echo $mc_listjs; ?></textarea>
					</p>
					<p>
						<label for="mini-js"><?php _e('Calendar Behaviors: Mini Calendar View','my-calendar'); ?></label><br /><textarea id="mini-js" name="mc_minijs" rows="12" cols="80"><?php echo $mc_minijs; ?></textarea>
					</p>
					<p>
						<label for="ajax-js"><?php _e('Calendar Behaviors: AJAX','my-calendar'); ?></label><br /><textarea id="ajax-js" name="mc_ajaxjs" rows="12" cols="80"><?php echo $mc_ajaxjs; ?></textarea>
					</p>
				<?php } ?>
				<p>
					<input type="submit" name="mc-js-save" class="button-primary" value="<?php _e('Save','my-calendar'); ?>" />
				</p>	
				</form>
				</div>
			</div>
		</div>
	</div>
</div>
 <?php mc_show_sidebar(); ?>
 </div>
<?php 
}