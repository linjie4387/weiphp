<?php

namespace Addons\Hospital;

use Common\Controller\Addon;

/**
 * 中科执信医疗插件
 * 
 * @author zjx
 */
class HospitalAddon extends Addon {
	public $info = array (
			'name' => 'Hospital',
			'title' => '执信医疗',
			'description' => '执信医疗采购管理系统',
			'status' => 1,
			'author' => '微澜科技',
			'version' => '0.1' 
	);
	public function install() {
		return true;
	}
	public function uninstall() {
		return true;
	}
	
	// 实现的weixin钩子方法
	public function weixin($param) {
	}
}