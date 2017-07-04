<?php

namespace Addons\Hospital\Controller;

use Home\Controller\AddonsController;

class OrderEventController extends AddonsController {
	/**
	 * 订单接收事件：发送订单接收模版消息
	 */
	public function test(){
		D('car');
		echo 'cat';
	}
	/**
	 * 订单更换司机：发送模版消息 状态更新提醒
	 */
	public function changeDeliveryman() {
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		error_log ( $param );
		$input = json_decode ( $param );
		$token = $input->token;
		$openid = $input->open_id;
		$did = $input->did;
		$status = $input->status;
		// 取所有审核通过的成员列表
		// 发送模版消息
		$content ['first'] = "司机您好，您的送货单状态发生了变化。";
		$content ['keyword1'] = "送货单号" . $did;
		$content ['keyword2'] = $status;
		$content ['keyword3'] = date ( "Y年m月d日 H:i" );
		$content ['remark'] = "请注意查看，送货单最新状态。";
		$config = getAddonConfig("Hospital");
		//$tpl = "26BeaYS4TM1OqG-Th_tGYWlO2KKKg8P6V_say_Z2UhU";
		$tpl = $config["template.order.confirm"];
		// $token = get_token();
		error_log ( "order event controller:token:" . $token.":tpl:".$tpl );
		//$url = U ( 'addon/Hospital/Hospital/myorderdetail/oid/' . $oid .'/token/'.$token );
		$url = '';
		$res = $this->_sendTemplateMsg ( $openid, $content, $tpl, $url, $token,'#FF0000' );
		_sendTemplateMsgReturn($res);
	}
	
	/**
	 * 订单作废事件：发送订单接收模版消息
	 */
	public function orderdel() {
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		error_log ( $param );
		$input = json_decode ( $param );
		$token = $input->token;
		$openid = $input->open_id;
		$oid = $input->order_id;
		// 取所有审核通过的成员列表
		// 发送模版消息
		$content ['first'] = "您好，您的订单状态发生了变化。";
		$content ['keyword1'] = "订单号：" . $oid;
		$content ['keyword2'] = "已作废";
		$content ['keyword3'] = date ( "Y年m月d日 H:i" );
		$content ['remark'] = "请点击“详情”对订单进行确认。";
		$config = getAddonConfig("Hospital");
		//$tpl = "26BeaYS4TM1OqG-Th_tGYWlO2KKKg8P6V_say_Z2UhU";
		$tpl = $config["template.order.confirm"];
		// $token = get_token();
		error_log ( "order event controller:token:" . $token.":tpl:".$tpl );
		$url = U ( 'addon/Hospital/Hospital/myorderdetail/oid/' . $oid .'/token/'.$token );
		$res = $this->_sendTemplateMsg ( $openid, $content, $tpl, $url, $token);
		_sendTemplateMsgReturn($res);
	}
	
	public function orderacceptnew() {
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		error_log ( $param );
		$input = json_decode ( $param );
		$token = 'gh_f65f6e4b3478';
		$openid = 'o4wVF1Te2vhBi8SKeukvH0IyQxA4';
		$oid = '409';
		// 取所有审核通过的成员列表
		// 发送模版消息
		$content ['first'] = "您好，您的订单状态发生了变化。";
		$content ['keyword1'] = "订单号" . $tid;
		$content ['keyword2'] = "已受理";
		$content ['keyword3'] = date ( "Y年m月d日 H:i" );
		$content ['remark'] = "请点击“详情”对订单进行确认。";
		$config = getAddonConfig("Hospital");
		//$tpl = "26BeaYS4TM1OqG-Th_tGYWlO2KKKg8P6V_say_Z2UhU";
		$tpl = $config["template.order.confirm"];
		// $token = get_token();
		error_log ( "order event controller:token:" . $token.":tpl:".$tpl );
		$url = U ( 'addon/Hospital/Hospital/myorderdetail/oid/' . $oid .'/token/'.$token );
		$res = $this->_sendTemplateMsg ( $openid, $content, $tpl, $url, $token );
		$this->_sendTemplateMsgReturn($res);
	}
	
	public function orderaccept() {
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		error_log ( $param );
		$input = json_decode ( $param );
		$token = $input->token;
		$openid = $input->open_id;
		$oid = $input->order_id;
		// 取所有审核通过的成员列表
		// 发送模版消息
		$content ['first'] = "您好，您的订单状态发生了变化。";
		$content ['keyword1'] = "订单号" . $tid;
		$content ['keyword2'] = "已受理";
		$content ['keyword3'] = date ( "Y年m月d日 H:i" );
		$content ['remark'] = "请点击“详情”对订单进行确认。";
		$config = getAddonConfig("Hospital");
		//$tpl = "26BeaYS4TM1OqG-Th_tGYWlO2KKKg8P6V_say_Z2UhU";
		$tpl = $config["template.order.confirm"];
		// $token = get_token();
		error_log ( "order event controller:token:" . $token.":tpl:".$tpl );
		$url = U ( 'addon/Hospital/Hospital/myorderdetail/oid/' . $oid .'/token/'.$token );
		$res = $this->_sendTemplateMsg ( $openid, $content, $tpl, $url, $token );
		$this->_sendTemplateMsgReturn($res);
	}

	/**
	 * 生成送货单事件：生成送货单后发送模版消息
	 */
	public function deliveryaccept() {
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		error_log ( $param );
		$input = json_decode ( $param );
		$token = $input->token;
		$oid = $input->id;	
		$goods_name = $input->goods_name;	
		$consignee = $input->consignee;		
		$address = $input->address;		
		$status = $input->status;	
		$users = $input->users;
		
		// 取所有审核通过的成员列表
		// 发送模版消息
		$content ['first'] = "您有送货单需要处理";
		$content ['keyword1'] = $oid;
		$content ['keyword2'] = $goods_name;
		$content ['keyword3'] = $consignee;
		$content ['keyword4'] = $address;
		$content ['keyword5'] = $status;
		$content ['remark'] = "请装车完成后点击“详情”确认发车";
		
		$config = getAddonConfig("Hospital");
		//$tpl = "26BeaYS4TM1OqG-Th_tGYWlO2KKKg8P6V_say_Z2UhU";
		$tpl = $config["template.delivery.confirm"];
		// $token = get_token();
		error_log ( "order event controller:token:" . $token.":tpl:".$tpl );
		$url = U ( 'addon/Hospital/Hospital/mydelivery/id/' . $oid .'/token/'.$token);
		//echo $url;
		$res = array();
		foreach($users as $val){
			$openid = $val->open_id;
			error_log ($openid);
			$res = $this->_sendTemplateMsg ( $openid, $content, $tpl, $url, $token );
		}
		$this->_sendTemplateMsgReturn($res);
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
	
	private function _sendTemplateMsgReturn($res){
		if ($res ['errcode'] != 0) {
			// $this->error ( $res ['errmsg'], $res ['errcode'] );
			$this->ajaxReturn ( $res );
		} else {
			$data ['msgid'] = $res ['msgid'];
			$this->ajaxReturn ( $res );
		}	
	}
	
	/*
	public function sendMsgReturn($res){
		if ($res ['errcode'] != 0) {
			// $this->error ( $res ['errmsg'], $res ['errcode'] );
			$this->ajaxReturn ( $res );
		} else {
			$data ['msgid'] = $res ['msgid'];
			$this->ajaxReturn ( $res );
		}	
	}
	*/
}