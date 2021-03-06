<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Branch_Col_Edit extends Salon_Page {
	
	private $table_data = null;
	
	public function __construct() {
		parent::__construct(true);
	}
	
	
	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}

	public function check_request() {

		if ( empty($_POST['branch_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		$check_item = '';
		switch (intval($_POST['column'])) {
			case 2:
				$check_item = 'branch_name';
				break;
			case 3:
				$check_item = 'remark';
				break;
			
		}
		if (empty($check_item)) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		$msg = '';
		if (Salon_Page::serverCheck(array(),$msg) == false) return;
		if (Salon_Page::serverColumnCheck($_POST['value'],$check_item,$msg) == false ) {
			throw new Exception($msg );
		}
	}

	public function show_page() {
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode(htmlspecialchars($this->table_data['value'],ENT_QUOTES)).' }';
	}


}