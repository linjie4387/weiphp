<?php

namespace Addons\OrderShip\Model;
use Think\Model;

/**
 * 微信用户表模型
 */
class ShopModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
	
	/**
	 * 查询指定管理员负责的门店列表
	 * @param unknown $user
	 */
	public function getAdminShopList($user){
		$join = "smpss_shop_admin on smpss_shop_admin.shop_id=smpss_shop.shop_id";
		return $this->where("weichat_user_id={$user}")->join($join)->select();
	}
}
