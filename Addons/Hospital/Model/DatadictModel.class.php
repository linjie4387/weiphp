<?php
namespace Addons\Hospital\Model;
use Think\Model;

/**
 * 数据字典表模型
 */
class DatadictModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
	//微信用户类型
	const KEY_USER_TYPE = 1001;
	//微信工程部用户等级
	const KEY_USER_LEVEL = 1002;
	//微信用户状态
	const KEY_USER_STATUS = 1003;
	//订单状态
	const KEY_ORDER_STATUS = 1004;
	//接单公司
	const KEY_ORDER_COMPANY = 1005;
	//送货单状态
	const KEY_DELIVERY_STATUS = 1008;
}
