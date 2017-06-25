<?php

namespace Addons\OrderShip\Model;
use Think\Model;

/**
 * 订单明细表模型
 */
class TradeDetailModel extends Model{
	protected $tablePrefix ="yz_";
	protected $connection = 'DB_SMPSS';
	//订单状态-待发货
	const STATUS_TO_DELIVER = "WAIT_SELLER_SEND_GOODS";
	//订单状态-已发货
	const STATUS_DELIVERED="WAIT_BUYER_CONFIRM_GOODS";
	//订单状态-已签收
	const STATUS_BUYER_SIGNED="TRADE_BUYER_SIGNED";
	
}
