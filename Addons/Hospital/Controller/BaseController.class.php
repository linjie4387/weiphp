<?php
namespace Addons\Hospital\Controller;
use Addons\Hospital\Model\WeichatuserModel;
use Home\Controller\AddonsController;

class BaseController extends AddonsController {
	protected function validuser(){
		$openid = get_openid();
		$param['open_id'] = $openid;
		$param['is_valid'] = 1;
		$user = D ( "weichatuser" )->where ($param )->find ();
		if (!empty ( $user )) {
			if ($user['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
				return $user;
			}else {
				switch ($user['status']) {
					case WeichatuserModel::USER_STATUS_AUDIT_FAILED :
						$this->assign ( "title", "审核失败" );
						$this->assign ( "content", "您的信息审核未通过，请填写正确的注册信息。" );
						break;
					case WeichatuserModel::USER_STATUS_CLOSED :
						$this->assign ( "title", "非法访问" );
						$this->assign ( "content", "您的账号已被注销。" );
						break;
					case WeichatuserModel::USER_STATUS_TO_AUDIT :
						$this->assign ( "title", "注册审核中" );
						$this->assign ( "content", "您已成功提交用户注册申请，请耐心等待管理员审核通过后即可正常使用。" );
						break;
					default :
						$this->assign ( "title", "非法访问" );
						$this->assign ( "content", "当前用户状态异常，请联系管理员。" );
				}
				return $user;
			}
		}
		return false;
	}
	
	/* 发送回复模板消息到微信平台 */
	protected function sendTemplateMsg($touser, $content, $template_id, $jumpUrl = '', $token) {
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