<?php
namespace Api\Controller;

class WeixinAuthController extends ApiController {
	/**
	 * 获取微信公众号access_token
	 */
	public function getAccessToken(){
		$param = $this->getParam();
		$appid = $param["appid"];
		$secret = $param["secret"];
		if(empty($appid)||empty($secret)){
			$this->error("系统异常：缺少参数。");
		}
		$access_token = get_access_token_by_apppid($appid, $secret);
		$data['access_token'] = $access_token;
		$this->success($data);
	}
}