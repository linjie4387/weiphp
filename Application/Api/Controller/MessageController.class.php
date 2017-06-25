<?php

namespace Api\Controller;

class MessageController extends ApiController {
	/**
	 * 发送模版消息
	 */
	public function sendTemplateMessage() {
		$param = $this->getParam ();
		$template_id = $param->tpl;
		$content = $param->content;
		// $sender = $param['from_user'];
		$to_user = $param->to_user;
		$url = $param->url;
		$token = $param->token;
		$this->_replyData ( $to_user, $content, $template_id, $url, $token );
	}
	/**
	 * 群发模版消息
	 */
	public function groupTemplateMessage() {
		$param = $this->getParam ();
		$template_id = $param->tpl;
		$content = $param->content;
		// $sender = $param['from_user'];
		$group = $param->group_id;
		$url = $param->url;
		$token = $param->token;
		// 查询状态正常的群组成员
		$map ['token'] = $token;
		$map ['has_subscribe'] = 1;
		$map ['status'] = 2;
		$map ['groupid'] = empty ( $goup ) ? 0 : $group;
		$users = M ()->table ( $px . 'public_follow as f' )->join ( $px . 'user as u ON f.uid=u.uid' )->field ( 'f.openid' )->where ( $map )->select ();
		// 发送消息
		foreach ( $users as $to_user ) {
			$this->_replyData ( $to_user, $content, $template_id, $url, $token );
		}
	}
	
	/* 发送回复模板消息到微信平台 */
	private function _replyData($touser, $content, $template_id, $jumpUrl = '', $token) {
		$param ['touser'] = $touser;
		$param ['template_id'] = $template_id;
		$param ['url'] = $jumpUrl;
		foreach ( $content as $key => $value ) {
			$param ['data'] [$key] ['value'] = $value;
			$param ['data'] [$key] ['color'] = "#173177";
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . get_access_token ( $token );
		$res = post_data ( $url, $param );
		if ($res ['errcode'] != 0) {
			$this->error ( $res ['errmsg'], $res ['errcode'] );
		} else {
			$data ['msgid'] = $res ['msgid'];
			$this->success ( $data );
		}
	}
}