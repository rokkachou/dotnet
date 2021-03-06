<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Config_Page extends Salon_Page {

	private $set_items = null;

	private $config = null;

	

	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		$this->set_items = array('config_branch','config_user_login','send_mail_text','config_staff_holiday_set','config_no_prefernce','before_day','after_day','timeline_y_cnt','config_show_detail_msg','config_name_order_set','config_log','config_delete_record','config_delete_record_period','regist_mail_text','maintenance_include_staff','mail_from','mail_returnPath','mobile_use','load_tab','reserve_deadline');
	}
	  
	public function set_config_datas($config) {
		$this->config = $config;
	}

	
	public function show_page() {
?>

	<script type="text/javascript" charset="utf-8">

		var $j = jQuery;
		<?php parent::echoClientItem($this->set_items); ?>	
		$j(document).ready(function() {
			$j("#salon_button_div input[type=button]").addClass("sl_button");
			<?php parent::echoSetItemLabel(false); ?>
			for(index in check_items) {
				if (check_items[index]) {
					var diff = 0;
					var id = check_items[index]["id"];
					$j("#"+id+"_lbl").children(".small").text(check_items[index]["tips"]);
					if ($j("#"+id)[0].tagName == "TEXTAREA" ) diff = 5;
					else {
						if ( $j("#"+id).parent().hasClass("config_item_wrap") ) {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).parent().outerHeight(true);
						}
						else {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).outerHeight(true);
						}
					}
					if (diff > 0 ) {
						diff += <?php echo parent::INPUT_BOTTOM_MARGIN; ?>+5;
						$j("#"+id).attr("style","margin-bottom: "+diff+"px;");
						$j("#"+id+"_lbl").children(".small").attr("style","text-align:left;");
					}
				}
			}

            $j("#button_update").click(function()	{
				fnClickUpdate();
			});

			$j("input[name=\"config_branch\"]").val([<?php echo $this->config['SALON_CONFIG_BRANCH']; ?>]);
			<?php if ( $this->config['SALON_CONFIG_USER_LOGIN'] == Salon_Config::USER_LOGIN_OK ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_user_login").attr("checked",<?php echo $set_boolean; ?>);				

			<?php if ( $this->config['SALON_CONFIG_DELETE_RECORD'] == Salon_Config::DELETE_RECORD_YES ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_delete_record").attr("checked",<?php echo $set_boolean; ?>);				
			$j("#delete_record_period").val("<?php echo $this->config['SALON_CONFIG_DELETE_RECORD_PERIOD']; ?>");

			<?php if ( $this->config['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_log_need").attr("checked",<?php echo $set_boolean; ?>);				
			<?php if ( $this->config['SALON_CONFIG_SHOW_DETAIL_MSG'] == Salon_Config::DETAIL_MSG_OK ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_show_detail_msg").attr("checked",<?php echo $set_boolean; ?>);				
			$j("#send_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n',$this->config['SALON_CONFIG_SEND_MAIL_TEXT']); ?>");
			$j("#regist_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n',$this->config['SALON_CONFIG_SEND_MAIL_TEXT_USER']); ?>");
			$j("input[name=\"config_staff_holiday_set\"]").val([<?php echo $this->config['SALON_CONFIG_STAFF_HOLIDAY_SET']; ?>]);
			$j("input[name=\"config_name_order_set\"]").val([<?php echo $this->config['SALON_CONFIG_NAME_ORDER']; ?>]);
			<?php if ( $this->config['SALON_CONFIG_NO_PREFERENCE'] == Salon_Config::NO_PREFERNCE_OK ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_is_no_preference").attr("checked",<?php echo $set_boolean; ?>);

			$j("#before_day").val("<?php echo $this->config['SALON_CONFIG_BEFORE_DAY']; ?>");
			$j("#after_day").val("<?php echo $this->config['SALON_CONFIG_AFTER_DAY']; ?>");
			$j("#timeline_y_cnt").val("<?php echo $this->config['SALON_CONFIG_TIMELINE_Y_CNT']; ?>");
			
			<?php if ( $this->config['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] == Salon_Config::MAINTENANCE_INCLUDE_STAFF ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_maintenance_include_staff").attr("checked",<?php echo $set_boolean; ?>);

			<?php if ( $this->config['SALON_CONFIG_MOBILE_USE'] == Salon_Config::MOBILE_USE_YES ) $set_boolean = 'true';
					else $set_boolean = 'false'; ?>
			$j("#config_mobile_use").attr("checked",<?php echo $set_boolean; ?>);
			
							
			$j("#mail_from").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_FROM']; ?>");
			$j("#mail_returnPath").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_RETURN_PATH']; ?>");
			
			
			$j("input[name=\"config_load_tab\"]").val([<?php echo $this->config['SALON_CONFIG_LOAD_TAB']; ?>]);
			
			<?php
				//
				$setMinutes = $this->config['SALON_CONFIG_RESERVE_DEADLINE'];
				$setIndex = Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_MIN;
				if ($this->config['SALON_CONFIG_RESERVE_DEADLINE'] % (60 * 24 ) == 0 ) {
					$setMinutes = $this->config['SALON_CONFIG_RESERVE_DEADLINE'] / (60 * 24);
					$setIndex = Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY;
				}
				elseif  ($this->config['SALON_CONFIG_RESERVE_DEADLINE'] % 60  == 0 ) {
					$setMinutes = $this->config['SALON_CONFIG_RESERVE_DEADLINE'] / 60;
					$setIndex = Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR;
				}
			?>
			$j("#reserve_deadline").val(<?php echo $setMinutes; ?>);
			$j("#config_deadline_time_unit").val(<?php echo $setIndex; ?>);						
							

		});


		function fnClickUpdate() {
			if ( ! checkItem("data_detail","config_deadline_time_unit") ) return false;
			if ( $j("input[name=\"config_staff_holiday_set\"]:checked").val() == <?php echo Salon_Config::SET_STAFF_REVERSE;?> &&
				$j("#config_is_no_preference").attr("checked") == "checked" ) {
					alert("<?php _e('can\'t check \"No Designation of Staff\"',SL_DOMAIN); ?>");
					return false;
			}
			var set_deadline = $j("#reserve_deadline").val();
			
			if ( $j("#config_deadline_time_unit").val() == <?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY; ?> ) {
				set_deadline = set_deadline * 24 * 60;
			}
			else if ($j("#config_deadline_time_unit").val() == <?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR; ?> ) {
				set_deadline = set_deadline * 60;
			}
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slconfig", 
					dataType : "json",
					data: {
						"config_branch":$j("input[name=\"config_branch\"]:checked").val()
						,"config_user_login":$j("#config_is_user_login").attr("checked")
						,"config_mail_text":$j("#send_mail_text").val()						
						,"config_mail_text_user":$j("#regist_mail_text").val()						
						,"config_log":$j("#config_is_log_need").attr("checked")
						,"config_delete_record":$j("#config_is_delete_record").attr("checked")
						,"config_delete_record_period":$j("#delete_record_period").val()
						,"config_after_day":$j("#after_day").val()						
						,"config_staff_holiday_set":$j("input[name=\"config_staff_holiday_set\"]:checked").val()
						,"config_name_order_set":$j("input[name=\"config_name_order_set\"]:checked").val()
						,"config_no_preference":$j("#config_is_no_preference").attr("checked")
						,"config_show_detail_msg":$j("#config_is_show_detail_msg").attr("checked")
						,"config_before_day":$j("#before_day").val()						
						,"config_after_day":$j("#after_day").val()						
						,"config_timeline_y_cnt":$j("#timeline_y_cnt").val()
						,"config_maintenance_include_staff":$j("#config_maintenance_include_staff").attr("checked")
						,"config_mobile_use":$j("#config_mobile_use").attr("checked")
						,"config_mail_from":$j("#mail_from").val()	
						,"config_mail_returnPath":$j("#mail_returnPath").val()	
						,"config_load_tab":$j("input[name=\"config_load_tab\"]:checked").val() 
						,"config_reserve_deadline":set_deadline 
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Config_Edit"

					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							alert(data.message);
							location.reload();
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });			
		}
	
		<?php parent::echoCheckClinet(array('chk_required','num','lenmax','chkMail')); ?>		
		
	</script>

	<h2 id="sl_admin_title"><?php _e('Environment Setting',SL_DOMAIN); ?></h2>
    <div id="salon_button_div" >
	<input id="button_update" type="button" value="<?php _e('update',SL_DOMAIN); ?>"/>
	</div>
	<div id="data_detail" >
		<div id="config_branch_wrap" class="config_item_wrap" >
			<input name="config_branch" id="config_only_branch" type="radio" value="<?php echo Salon_Config::ONLY_BRANCH; ?>" style="width:16px;margin:3px 5px 0px 10px;"/>
			<label for="config_only_branch"  style="width:auto;margin:5px;text-align:left;"><?php echo  _e('only shop',SL_DOMAIN); ?></label>
			<input name="config_branch" id="config_multi_branch" type="radio" value="<?php echo Salon_Config::MULTI_BRANCH; ?>"  style="width:16px;margin:3px 5px 0px 10px;" />
			<label for="config_multi_branch" style="width:auto;margin:5px;text-align:left;"><?php echo  _e('plural shops',SL_DOMAIN); ?></label>
		</div>
		<div id="config_is_user_login_wrap" class="config_item_wrap" >
			<input id="config_is_user_login" type="checkbox"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::USER_LOGIN_OK; ?>" />
		</div>
		<div id="config_is_log_need_wrap" class="config_item_wrap" >
			<input id="config_is_log_need" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;"  value="<?php echo Salon_Config::LOG_NEED; ?>" />
		</div>
		<div id="config_is_delete_record_wrap" class="config_item_wrap" >
			<input id="config_is_delete_record" type="checkbox"  style="width:16px;margin:3px 5px 0px 10px;"  value="<?php echo Salon_Config::DELETE_RECORD_YES; ?>" />
		</div>
		<input type="text" id="delete_record_period" />
		<div id="config_is_show_detail_msg_wrap" class="config_item_wrap" >
			<input id="config_is_show_detail_msg" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::DETAIL_MSG_OK; ?>" />
		</div>
		<div id="config_staff_holday_set_wrap" class="config_item_wrap" >
			<input id="config_staff_holiday_normal" name="config_staff_holiday_set" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SET_STAFF_NORMAL; ?>" />
			<label for="config_staff_holiday_normal"  style="margin:5px;text-align:left;width:150px;"><?php _e('unable to enter when holidays',SL_DOMAIN); ?></label>
			<input id="config_staff_holiday_reverse" name="config_staff_holiday_set" type="radio" style="display:inline-block;width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::SET_STAFF_REVERSE;?>" />
			<label for="config_staff_holiday_reverse"  style="margin:5px;display:inline-block;float:none;text-align:left;width:150px;"><?php _e('unable to enter other than when attendant',SL_DOMAIN); ?></label>
		</div>
		<div id="config_name_order_set_wrap" class="config_item_wrap" >
			<input id="config_name_order_japan" name="config_name_order_set" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::NAME_ORDER_JAPAN; ?>" />
			<label for="config_name_order_japan"  style="width:auto;margin:5px;text-align:left;"><?php _e('Sur Name first',SL_DOMAIN); ?></label>
			<input id="config_name_order_other" name="config_name_order_set" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::NAME_ORDER_OTHER;?>" />
			<label for="config_name_order_other" style="width:auto;margin:5px;text-align:left;"><?php _e('Given Name first',SL_DOMAIN); ?></label>
		</div>

		<div id="config_is_no_preference_wrap" class="config_item_wrap" >
			<input id="config_is_no_preference" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::NO_PREFERNCE_OK; ?>" />
		</div>
		<div id="config_maintenance_include_staff_wrap" class="config_item_wrap" >
			<input id="config_maintenance_include_staff" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::MAINTENANCE_INCLUDE_STAFF; ?>" />
		</div>
		<div id="config_mobile_use_wrap" class="config_item_wrap" >
			<input id="config_mobile_use" type="checkbox" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::MOBILE_USE_YES; ?>" />
		</div>
		<input type="text" id="before_day" />
		<input type="text" id="after_day" />
		<input type="text" id="timeline_y_cnt" />
		<input type="text" id="mail_from" />
		<input type="text" id="mail_returnPath" />
		<textarea id="send_mail_text"  ></textarea>
		<textarea id="regist_mail_text"  ></textarea>

		<div id="config_load_tab_wrap" class="config_item_wrap" >
			<input id="config_load_staff" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_STAFF; ?>" />
			<label for="config_load_staff"  style="width:auto;margin:5px;text-align:left;"><?php _e('Staff',SL_DOMAIN); ?></label>
			<input id="config_load_month" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_MONTH; ?>" />
			<label for="config_load_month"  style="width:auto;margin:5px;text-align:left;"><?php _e('Month',SL_DOMAIN); ?></label>
			<input id="config_load_week" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_WEEK; ?>" />
			<label for="config_load_week"  style="width:auto;margin:5px;text-align:left;"><?php _e('Week',SL_DOMAIN); ?></label>
			<input id="config_load_day" name="config_load_tab" type="radio" style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::LOAD_DAY; ?>" />
			<label for="config_load_day"  style="width:auto;margin:5px;text-align:left;"><?php _e('Day',SL_DOMAIN); ?></label>
		</div>


		<div id="config_deadline_wrap" class="config_item_wrap" >
			<input type="text" id="reserve_deadline" style="width:100px;margin:3px 5px 0px 10px;" />
			<select id="config_deadline_time_unit" style="width:100px;margin:3px 5px 0px 10px;">
				<option value="<?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_MIN; ?>"  ><?php _e('Minute',SL_DOMAIN); ?></option>
				<option value="<?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_HOUR; ?>" ><?php _e('Hour',SL_DOMAIN); ?></option>
				<option value="<?php echo Salon_Config::DEFALUT_RESERVE_DEADLINE_UNIT_DAY; ?>" ><?php _e('Day',SL_DOMAIN); ?></option>
			</select>
			
		</div>
		
		<div class="spacer"></div>
	</div>

<?php  
	}	//show_page
}		//class

