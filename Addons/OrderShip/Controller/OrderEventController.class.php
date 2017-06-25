<?php

namespace Addons\OrderShip\Controller;

use Home\Controller\AddonsController;

class OrderEventController extends AddonsController {
	/**
	 * 新订单事件：发送订单到达模版消息
	 */
	public function neworder() {
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		error_log ( $param );
		$input = json_decode ( $param );
		$token = $input->token;
		// 取所有审核通过的成员列表
		$userlist = D ( "weichatuser" )->where ( "status=2 and is_valid=1" )->select ();
		foreach ( $userlist as $user ) {
			// 发送模版消息
			$content ['first'] = "尊敬的会员，您有新的消息通知。";
			$content ['keyword1'] = "有新的订单到达，点击认领。";
			$content ['keyword2'] = "广播通知";
			$content ['keyword3'] = date ( "Y-m-d H:i:s" );
			$content ['remark'] = "点击查看详情";
			//$tpl = "RDo_laGad4aWgA0sMlhfsrHWCt8a1B4t5Mo1gs9YrWk";
			$config = getAddonConfig ( "OrderShip" );
			$tpl = $config ['tpl.order.new'];
			// $token = get_token();
			error_log ( "order event controller:token:" . $token );
			$url = $_SERVER['HTTP_HOST']."/index.php?s=/addon/OrderShip/OrderShip/listunclaimedorder&token=" . $token;
			$this->_sendTemplateMsg ( $user ['open_id'], $content, $tpl, $url, $token );
		}
	}
	public function dispatchorder() {
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		error_log ( $param );
		$input = json_decode ( $param );
		$token = $input->token;
		// 发送模版消息
		$content ['first'] = "有一个新的指派订单，快去瞧瞧吧。";
		$content ['keyword1'] = $input->tid;
		$content ['keyword2'] = $input->products;
		$content ['keyword3'] = $input->total_fee;
		$content ['keyword4'] = $input->contacts;
		$content ['keyword5'] = "线上支付";
		$content ['remark'] = "点击查看详情";
		$config = getAddonConfig ( "OrderShip" );
		$tpl = $config ['tpl.order.dispatch'];
		$url = $_SERVER['HTTP_HOST']."/index.php?s=/addon/OrderShip/OrderShip/myorderdetail&tid=" . $input->tid;
		$res = $this->_sendTemplateMsg ( $input->open_id, $content, $tpl, $url, $token );
		if ($res ['errcode'] != 0) {
			$this->ajaxReturn ( $res );
		} else {
			$data ['msgid'] = $res ['msgid'];
			$this->ajaxReturn ( $res );
		}
	}
	
	/**
	 * 取消订单事件
	 */
	public function cancelorder() {
		// 发送模版消息
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		$input = json_decode ( $param );
		$tid = $input->tid;
		$token = $input->token;
		if (empty ( $tid )) {
			$res ["errcode"] = - 1;
			$res ["errmsg"] = "缺少订单号";
			$this->ajaxReturn ( $res );
		}
		$order = D ( "TradeDetail" )->where ( "tid='" . $tid . "'" )->find ();
		if (empty ( $order )) {
			$res ["errcode"] = - 2;
			$res ["errmsg"] = "订单不存在";
			$this->ajaxReturn ( $res );
		}
		$orderDetails = D ( "TradeOrder" )->where ( "tid='" . $tid . "'" )->select ();
		foreach ( $orderDetails as $product ) {
			$productStr = $productStr . $product ['title'] . "; ";
		}
		
		$user = D ( "TradeDetail" )->where ( "yz_trade_detail.tid='" . $tid . "'" )->join ( "yz_trade_dispatch on yz_trade_dispatch.tid=yz_trade_detail.tid" )->field ( "yz_trade_dispatch.user_id" )->find ();
		$weichatUser = D ( "weichatuser" )->where ( "weichatuser_id=" . $user ['user_id'] )->find ();
		$content ['first'] = "您认领的订单已被用户取消。";
		$content ['orderProductPrice'] = $order ['total_fee'] . "元";
		$content ['orderProductName'] = $productStr;
		$content ['orderAddress'] = $order ['receiver_address'];
		$content ['orderName'] = $tid;
		$content ['remark'] = "";
		$tpl = "fnwNQXaOqFM4BhA9koyNhot9A2Mcy_DPEb0FM5-6jYo";
		
		error_log ( "order event controller:token:" . $token );
		$url = $_SERVER['HTTP_HOST']."/index.php?s=/addon/OrderShip/OrderShip/orderdetail&tid=" . $tid . "&token=" . $token;
		$res = $this->_sendTemplateMsg ( $weichatUser ['open_id'], $content, $tpl, $url, $token );
		if ($res ['errcode'] != 0) {
			// $this->error ( $res ['errmsg'], $res ['errcode'] );
			$this->ajaxReturn ( $res );
		} else {
			$data ['msgid'] = $res ['msgid'];
			$this->ajaxReturn ( $res );
		}
	}
	
	/* 发送回复模板消息到微信平台 */
	private function _sendTemplateMsg($touser, $content, $template_id, $jumpUrl = '', $token) {
		$param ['touser'] = $touser;
		$param ['template_id'] = $template_id;
		$param ['url'] = $jumpUrl;
		foreach ( $content as $key => $value ) {
			$param ['data'] [$key] ['value'] = $value;
			$param ['data'] [$key] ['color'] = "#173177";
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . get_access_token ( $token );
		$res = post_data ( $url, $param );
		return $res;
	}
}