<?php

namespace Addons\OrderShip\Controller;

use Home\Controller\AddonsController;
use Addons\OrderShip\Model\WeichatuserModel;
use Addons\OrderShip\Model\TradeDetailModel;
use Think\Controller;

class OrderShipController extends AddonsController {
	private function checkRights() {
		$openid = get_openid ();
		$param ['open_id'] = $openid;
		$param ['is_valid'] = 1;
		$user = D ( 'weichatuser' )->where ( $param )->find ();
		if ($user) {
			if ($user ['status'] == 2) { // 审核通过
				return true;
			} else {
				$this->assign ( "title", "注册审核中" );
				$this->assign ( "content", "您已成功提交用户注册申请，请耐心等待管理员审核通过后即可正常使用。" );
				$this->display ( "OrderShip/show" );
				return false;
			}
		} else {
			redirect ( U ( "regist" ) );
			return false;
		}
	}
	
	/**
	 * 用户注册页面
	 */
	function regist() {
		error_log ( "uid:" . $this->uid );
		$openid = get_openid ();
		error_log ( "nick_name:" . get_nickname ( $this->uid ) );
		$param ['open_id'] = $openid;
		$param ['is_valid'] = 1;
		$user = D ( 'weichatuser' )->where ( $param )->find ();
		if (! $user) {
			$shoplist = D ( 'shop' )->select ();
			$this->assign ( "shoplist", $shoplist );
			$this->display ( "OrderShip/signup" );
		} else {
			$this->assign ( "title", "用户已注册" );
			$this->assign ( "content", "您已注册，欢迎使用会员服务。" );
			$this->display ( "OrderShip/show" );
		}
	}
	
	/**
	 * 用户提交注册信息
	 */
	function signup() {
		$openid = get_openid ();
		$name = I ( "name" );
		$mobile = I ( "mobile" );
		$shop = I ( "shop" );
		if (empty ( $name ) || empty ( $mobile )) {
			$this->error ( "请输入姓名与手机号码。" );
		}
		$data ['open_id'] = $openid;
		$data ['nick_name'] = get_nickname ( $this->uid );
		$data ['name'] = $name;
		$data ['mobile'] = $mobile;
		$data ['shop_id'] = "{$shop}";
		$data ['status'] = WeichatuserModel::USER_STATUS_TO_AUDIT; // 待审核
		$data ['apply_time'] = date ( "Y-m-d H:i:s" );
		$weichatUserObj = D ( "weichatuser" );
		$param ['open_id'] = $openid;
		$user = $weichatUserObj->where ( $param )->find ();
		if (empty ( $user )) {
			$uid = $weichatUserObj->add ( $data );
		} else {
			$data ['is_valid'] = 1;
			$data ['status'] = WeichatuserModel::USER_STATUS_TO_AUDIT;
			$rs = $weichatUserObj->where ( $param )->save ( $data );
			$uid = $user ['weichatuser_id'];
		}
		if ($uid) {
			$this->assign ( "title", "注册审核中" );
			$this->assign ( "content", "您已成功提交用户注册申请，请耐心等待管理员审核通过后即可正常使用。" );
			$this->display ( "OrderShip/show" );
			// echo("您已成功提交用户注册申请，请耐心等待管理员审核通过后即可正常使用。".$uid);
		} else {
			// error_log ( "用户注册失败：" . D ( 'weichatuser' )->getDbError () );
			// $this->error ( "用户注册失败！", "", true );
			$this->error ( "用户注册失败：" . D ( 'weichatuser' )->getDbError () );
		}
	}
	
	function getuserdispatch(){
		$openid = get_openid ();
		$weichatUserObj = D ( "Weichatuser" );
		$weichat = $weichatUserObj->where("open_id = '{$openid}'")->find();
		if($weichat){
			$wid = $weichat['weichatuser_id'];
			$shopadminObj = D ( "ShopAdmin" );
			$shopadmin = $shopadminObj->where("weichat_user_id = '{$wid}'")->select();
			if($shopadmin){
				$shoparr = array();
				foreach($shopadmin as $val){
					$shoparr[] = $val['shop_id'];
				}
				$weichat['dispatch_type'] = 1; //门店
				$weichat['dispatch_area'] = implode(",",$shoparr);
			}else{
				$weichat['dispatch_type'] = 2; //区域
				$weichat['dispatch_area'] = $weichat['area_code'];
			}
			
			return $weichat;
		}else{
			return false;	
		}
	}
	/**
	 * 查询未认领订单
	 */
	function listunclaimedorder() {
		if (! $this->checkRights ()) {
			exit ();
		}
		$page = I ( "page", 1 );
		$pagesize = I ( "pagesize", 20 );
		
		$userinfo = $this->getuserdispatch();
		
		if($userinfo){			
			//$tradeDetailObj = D ( "TradeDetail" );
			//$tradeTable = $tradeDetailObj->getTableName ();
			
			$tradeDispatchObj = D ( "TradeDispatch" );
			$where = "(yz_trade_dispatch.is_dispatch is null or yz_trade_dispatch.is_dispatch=0) and yz_trade_detail.status='WAIT_SELLER_SEND_GOODS'";
			if($userinfo['dispatch_type'] == 1){
				$where .= " and yz_trade_dispatch.shop_id in (".$userinfo['dispatch_area'].")";
			}elseif($userinfo['dispatch_type'] == 2){
				$where .= " and yz_trade_dispatch.area_code = '".$userinfo['dispatch_area']."'";
			}
			
			$join = "left join yz_trade_detail on yz_trade_dispatch.tid=yz_trade_detail.tid";
			$fields = 'yz_trade_detail.payment,yz_trade_detail.tid,yz_trade_detail.buyer_nick,yz_trade_detail.receiver_name,yz_trade_detail.receiver_address,yz_trade_detail.total_fee, yz_trade_dispatch.is_dispatch,yz_trade_detail.status,yz_trade_detail.created,yz_trade_detail.feedback, yz_trade_detail.shipping_type';
			$orderList = $tradeDispatchObj->where ( $where )->page ( $page, $pagesize )->join ( $join )->field ( $fields )->order ( "created desc" )->select ();
			
			$orderList = $this->_getFetchInfo($orderList);
			if (IS_POST) {
				$this->ajaxReturn ( $orderList, 'JSON' );
			}
		}else{
			$this->ajaxReturn ( NULL, 'JSON' );
		}
		$this->assign ( "orderlist", $orderList );
		$this->display ( "OrderShip/orderlist" );
	}
	
	/**
	 * 查询订单详情
	 */
	function orderdetail() {
		$tradeId = I ( 'tid' );
		if (empty ( $tradeId )) {
			$this->error ( "订单号不允许为空" );
		}
		$this->_getOrderDetail ( $tradeId );
		// $shoplist = D("Shop")->select();
		//$openid = get_openid ();
		//$user = D ( "Weichatuser" )->where ( "open_id='" . $openid . "'" )->find ();
		$userinfo = $this->getuserdispatch();
		if($userinfo['dispatch_type'] == 2){
			$shoplist = D ( "Shop" )->where("dis_code = '".$userinfo['dispatch_area']."'")->select();
		}else{			
			$shoplist = D ( "Shop" )->getAdminShopList ( $userinfo ['weichatuser_id'] );
		}
		
		$this->assign ( "shoplist", $shoplist );
		$this->display ( "OrderShip/orderdetail" );
	}
	
	/**
	 * 查询我的订单详情
	 */
	function myorderdetail() {
		$tradeId = I ( 'tid' );
		if (empty ( $tradeId )) {
			$this->error ( "订单号不允许为空" );
		}
		$this->_getOrderDetail ( $tradeId );
		$this->display ( "OrderShip/myorderdetail" );
	}
	/**
	 * 获取订单详情
	 * @param unknown $tradeId
	 */
	private function _getOrderDetail($tradeId) {
		$trade = D ( "TradeDetail" )->where ( "tid='" . $tradeId . "'" )->find ();
		$products = D ( "TradeOrder" )->where ( "tid='" . $tradeId . "'" )->select ();
		$dispatch = D ( "TradeDispatch" )->where ( "tid='" . $tradeId . "'" )->field ( "is_dispatch" )->find ();
		foreach ( $products as &$product ) {
			$messages = D ( "TradeBuyerMessage" )->where ( "oid='" . $product ['oid'] . "'" )->select ();
			$product ['messagelist'] = $messages;
		}
		if ($dispatch) {
			$trade ['is_dispatch'] = $dispatch ['is_dispatch'];
		} else {
			$trade ['is_dispatch'] = 0;
		}
		//自提
		if ($trade ["shipping_type"] == "fetch") {
			$tid = $trade['tid'];
			$fetch = D ( "TradeFetch" )->where ( "tid='{$tid}'" )->find ();
			if ($fetch) {
				$trade["receiver_name"] = $fetch["fetcher_name"];
				$trade["receiver_address"] = $fetch["shop_name"];
			}
		}
		$this->assign ( "trade", $trade );
		$this->assign ( "productlist", $products );
	}
	/**
	 * 接单
	 */
	function claimorder() {
		$tradeId = I ( 'tid' );
		$shopId = I ( 'shop_id' );
		if (empty ( $tradeId )) {
			$this->error ( "订单号不允许为空。" );
		}
		if (empty ( $shopId )) {
			$this->error ( "请指定接单门店。" );
		}
		$openid = get_openid ();
		$user = D ( "Weichatuser" )->where ( "open_id='" . $openid . "'" )->find ();
		$shop = D ( "Shop" )->where ( "shop_id=" . $shopId )->find ();
		$param = array (
				"tid" => $tradeId,
				"shop_id" => $shop ['shop_id'],
				"shop_name" => $shop ['shop_name'],
				"shop_mobile" => $shop ['phone'],
				"shop_address" => $shop ['address'],
				"user_id" => $user ['weichatuser_id'],
				"user_name" => $user ['name'],
				"user_mobile" => $user ['mobile'] 
		);
		// 调用管理台接口
		$config = getAddonConfig ( "OrderShip" );
		$url = $config ['trade.server.url'] . "dispatchtrade";
		$res = post_data ( $url, $param );
		error_log ( "res code:" . $res ['code'] );
		if ($res ['code'] == 0) { // 接单成功
			error_log ( 'success:' . $res ['msg'] );
			// redirect ( U("orderdetail",array("tid"=> $tradeId)) );
			$this->success ( "接单成功！", U ( "pagemyorder" ), 3 );
		} else {
			// error_log('failed:'.$res['msg']);
			// $this->assign("title","接单失败");
			// $this->assign("content", $res['msg']);
			// $this->display( "OrderShip/show" );
			// error_log(U("listmyorder"));
			$this->error ( "接单失败：" . $res ['msg'] );
		}
	}
	/**
	 * 修改订单信息
	 */
	function updateOrderInfo() {
		$tid = I ( "tid" );
		$dateymd = I ( "dateymd" );
		$remark = I ( "remark" );
		if (empty ( $tid )) {
			$rs ['errcode'] = - 1;
			$rs ['errmsg'] = "缺少订单编号！";
			$this->ajaxReturn ( $rs, 'JSON' );
		}
		$obj = D ( "TradeDetail" );
		$data = array (
				'send_time' => $dateymd,
				'remark' => $remark 
		);
		$result = $obj->where ( "tid='{$tid}'" )->setField ( $data );
		if ($result !== false) {
			$rs ['errcode'] = 0;
			$rs ['errmsg'] = "操作成功！";
			$this->ajaxReturn ( $rs, 'JSON' );
		} else {
			$rs ['errcode'] = - 1;
			$rs ['errmsg'] = $obj->getDbError ();
			$this->ajaxReturn ( $rs, 'JSON' );
		}
	}
	
	/**
	 * 我接的单首页
	 */
	function pagemyorder() {
		if (! $this->checkRights ()) {
			exit ();
		}
		$this->display ( "OrderShip/myorderlist" );
	}
	
	/**
	 * 我接的单-全部
	 */
	function listmyorder() {
		$page = I ( "page", 1 );
		$pagesize = I ( "pagesize", 20 );
		$tradeDetailObj = D ( "TradeDetail" );
		$openid = get_openid ();
		$user = D ( "weichatuser" )->where ( "open_id='" . $openid . "'" )->find ();
		$where ["yz_trade_dispatch.is_dispatch"] = 1;
		$where ["yz_trade_dispatch.user_id"] = $user ['weichatuser_id'];
		$dateymd = I ( "dateymd" );
		if ($dateymd) {
			$where ["date(yz_trade_dispatch.dispatch_time)"] = $dateymd;
		}
		$key = I ( "key" );
		if ($key) {
			$where ['_string'] = "yz_trade_detail.buyer_nick like '%" . $key . "%' or yz_trade_detail.receiver_name like '%" . $key . "%' or yz_trade_detail.receiver_mobile like '%" . $key . "%'";
		}
		$join = "join yz_trade_dispatch on yz_trade_dispatch.tid=yz_trade_detail.tid";
		$fields = "yz_trade_detail.payment,yz_trade_detail.shipping_type, yz_trade_detail.tid,yz_trade_detail.buyer_nick,yz_trade_detail.receiver_name,yz_trade_detail.receiver_address,yz_trade_detail.total_fee, yz_trade_dispatch.user_id,yz_trade_detail.status,yz_trade_detail.created";
		$orderList = $tradeDetailObj->where ( $where )->page ( $page, $pagesize )->join ( $join )->field ( $fields )->order ( "yz_trade_dispatch.dispatch_time desc" )->select ();
		$orderList = $this->_getFetchInfo($orderList);
		$data ['orderlist'] = $orderList;
		$this->ajaxReturn ( $data, 'JSON' );
	}
	//获取自提信息
	private function _getFetchInfo($orderList){
		foreach ( $orderList as &$order ) {
			error_log("ship_type:".$order["shipping_type"]);
			if ($order ["shipping_type"] == "fetch") {
				$tid = $order['tid'];
				$fetch = D ( "TradeFetch" )->where ( "tid='{$tid}'" )->find ();
				error_log("fetch:".$fetch);
				if ($fetch) {
					error_log("fetcher_name:".$fetch["fetcher_name"]);
					$order["receiver_name"] = $fetch["fetcher_name"];
					$order["receiver_address"] = $fetch["shop_name"];
				}
			}
		}
		return $orderList;
	}
	
	/**
	 * 我接的单-已发货
	 */
	function listmydeliveredorder() {
		$page = I ( "page", 1 );
		$pagesize = I ( "pagesize", 20 );
		$tradeDetailObj = D ( "TradeDetail" );
		$openid = get_openid ();
		$user = D ( "weichatuser" )->where ( "open_id='" . $openid . "'" )->find ();
		// 查询条件
		$where ["yz_trade_dispatch.is_dispatch"] = 1;
		$where ["yz_trade_dispatch.user_id"] = $user ['weichatuser_id'];
		$dateymd = I ( "dateymd" );
		if ($dateymd) {
			$where ["date(yz_trade_dispatch.dispatch_time)"] = $dateymd;
		}
		$key = I ( "key" );
		if ($key) {
			$where ['_string'] = "yz_trade_detail.buyer_nick like '%" . $key . "%' or yz_trade_detail.receiver_name like '%" . $key . "%' or yz_trade_detail.receiver_mobile like '%" . $key . "%'";
		}
		$where ['_string'] = "yz_trade_detail.status='" . TradeDetailModel::STATUS_DELIVERED . "' or yz_trade_detail.status='" . TradeDetailModel::STATUS_BUYER_SIGNED . "'";
		$sql = $tradeDetailObj->where ( $where )->page ( $page, $pagesize )->join ( $join )->field ( $fields )->order ( "yz_trade_dispatch.dispatch_time desc" )->fetchSql ( true )->select ();
		error_log ( $sql );
		$join = "join yz_trade_dispatch on yz_trade_dispatch.tid=yz_trade_detail.tid";
		$fields = 'yz_trade_detail.payment,yz_trade_detail.shipping_type,yz_trade_detail.tid,yz_trade_detail.buyer_nick,yz_trade_detail.receiver_name,yz_trade_detail.receiver_address,yz_trade_detail.total_fee, yz_trade_dispatch.user_id,yz_trade_detail.status,yz_trade_detail.created';
		$orderList = $tradeDetailObj->where ( $where )->page ( $page, $pagesize )->join ( $join )->field ( $fields )->order ( "yz_trade_dispatch.dispatch_time desc" )->select ();
		$orderList = $this->_getFetchInfo($orderList);
		$data ['orderlist'] = $orderList;
		$this->ajaxReturn ( $data, 'JSON' );
	}
	/**
	 * 我接的单-待发货
	 */
	function listmyundeliverorder() {
		$page = I ( "page", 1 );
		$pagesize = I ( "pagesize", 20 );
		$tradeDetailObj = D ( "TradeDetail" );
		$openid = get_openid ();
		$user = D ( "weichatuser" )->where ( "open_id='" . $openid . "'" )->find ();
		
		// 查询条件
		$where ["yz_trade_dispatch.is_dispatch"] = 1;
		$where ["yz_trade_dispatch.user_id"] = $user ['weichatuser_id'];
		$dateymd = I ( "dateymd" );
		if ($dateymd) {
			$where ["date(yz_trade_dispatch.dispatch_time)"] = $dateymd;
		}
		$key = I ( "key" );
		if ($key) {
			$where ['_string'] = "yz_trade_detail.buyer_nick like '%" . $key . "%' or yz_trade_detail.receiver_name like '%" . $key . "%' or yz_trade_detail.receiver_mobile like '%" . $key . "%'";
		}
		$where ['yz_trade_detail.status'] = TradeDetailModel::STATUS_TO_DELIVER;
		$join = "join yz_trade_dispatch on yz_trade_dispatch.tid=yz_trade_detail.tid";
		$fields = 'yz_trade_detail.payment,yz_trade_detail.shipping_type,yz_trade_detail.tid,yz_trade_detail.buyer_nick,yz_trade_detail.receiver_name,yz_trade_detail.receiver_address,yz_trade_detail.total_fee, yz_trade_dispatch.user_id,yz_trade_detail.status,yz_trade_detail.created';
		$orderList = $tradeDetailObj->where ( $where )->page ( $page, $pagesize )->join ( $join )->field ( $fields )->order ( "yz_trade_dispatch.dispatch_time desc" )->select ();
		$orderList = $this->_getFetchInfo($orderList);
		$data ['orderlist'] = $orderList;
		$this->ajaxReturn ( $data, 'JSON' );
	}
	/**
	 * 发货
	 */
	function deliverorder() {
		$tradeId = I ( 'tid' );
		if (empty ( $tradeId )) {
			$this->error ( "订单号不允许为空" );
		}
		$trade = D ( "TradeDetail" )->where ( "tid='" . $tradeId . "'" )->find ();
		if (empty ( $trade )) {
			$this->error ( "订单号不存在" );
		}
		$openid = get_openid ();
		$user = D ( "weichatuser" )->where ( "open_id='" . $openid . "'" )->find ();
		$param = array (
				"tid" => $tradeId,
				"user_name" => $user ['name'],
				"user_mobile" => $user ['mobile'] 
		);
		// 调用管理台接口
		$config = getAddonConfig ( "OrderShip" );
		$url = $config ['trade.server.url'] . "settradetosend";
		$res = post_data ( $url, $param );
		if ($res ['code'] == 0) { // 发货成功
		                     // $this->_getOrderDetail($tradeId);
		                     // $this->assign("title", "发货成功");
		                     // $this->assign("content", "编号".tid."的订单请尽快送给".$trade['receiver_name']);
		                     // $this->display("OrderShip/show");
			$this->success ( "编号" . $tid . "的订单请尽快送给" . $trade ['receiver_name'], U ( "pagemyorder" ) );
		} else {
			// $this->assign("title", "发货失败");
			// $this->assign("content", $res['msg']);
			// $this->display("OrderShip/show");
			$this->error ( "发货失败:" . $res ['msg'] );
		}
	}
	/**
	 * 取消认领订单
	 */
	function unclaimorder() {
		$tradeId = I ( 'tid' );
		if (empty ( $tradeId )) {
			$this->error ( "订单号不允许为空" );
		}
		$trade = D ( "TradeDetail" )->where ( "tid='" . $tradeId . "'" )->find ();
		if (empty ( $trade )) {
			$this->error ( "订单号不存在" );
		}
		
		$param = array (
				"tid" => $tradeId 
		);
		// 调用管理台接口
		$config = getAddonConfig ( "OrderShip" );
		$url = $config ['trade.server.url'] . "canceldispatchtrade";
		$res = post_data ( $url, $param );
		if ($res ['code'] == 0) { // 取消成功
		                     // $this->assign("title", "放弃认领成功");
		                     // $this->assign("content", "您已放弃认领编号".tid."的订单，该订单将重新进入等待接单的状态。");
		                     // $this->display("OrderShip/show");
			$this->success ( "您已放弃认领编号" . $tid . "的订单，该订单将重新进入等待接单的状态。", U ( "pagemyorder" ) );
		} else {
			// $this->assign("title", "放弃认领失败");
			// $this->assign("content", $res['msg']);
			// $this->display("OrderShip/show");
			$this->error ( "放弃认领失败" . $res ['msg'] );
		}
	}
	
	/**
	 * 用户中心 - 个人信息
	 */
	function usercenter() {
		if (! $this->checkRights ()) {
			exit ();
		}
		$openid = get_openid ();
		$param ['open_id'] = $openid;
		
		// $user = D ( 'weichatuser' )->where ( $param )->find ();
		$user = D ( 'weichatuser' )->where ( "smpss_weichatuser.open_id='" . $openid . "'" )->join ( "join smpss_shop on smpss_shop.shop_id=smpss_weichatuser.shop_id" )->field ( 'smpss_weichatuser.*,smpss_shop.shop_name' )->find ();
		if ($user) {
			switch ($user ['status']) {
				case 1 :
					$user ["status_name"] = "待审核";
					break;
				case 2 :
					$user ["status_name"] = "正常";
					break;
				case 3 :
					$user ["status_name"] = "审核不通过";
					break;
				case 4 :
					$user ["status_name"] = "关闭权限";
					break;
			}
		}
		$this->assign ( "user", $user );
		$this->display ( "OrderShip/usercenter" );
	}
}