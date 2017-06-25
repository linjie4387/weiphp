<?php
namespace Addons\Hospital\Model;
use Think\Model;

/**
 * 医院订单表模型
 */
class HospitalorderModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
	//订单状态-预订单下单
	const STATUS_NEW = 1;
	//订单状态-预订单处理中
	const STATUS_CONFIRMED = 2;
	//订单状态-预订单完毕
	const STATUS_FINISHED = 3;
	
	//订单类型-医院下单
	const ORDER_TYPE_HOSPITAL = 1;
	//订单类型-代理下单
	const ORDER_TYPE_AGENT = 2;
}
