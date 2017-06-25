<?php
namespace Addons\OrderShip;

use Common\Controller\Addon;

/**
 * o2o订单配送插件
 *
 * @author zjx
 */
class OrderShipAddon extends Addon {
	public $info = array (
			'name' => 'OrderShip',
			'title' => 'O2O订单配送',
			'description' => '农夫科技O2O订单管理系统',
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