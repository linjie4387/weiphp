<?php
namespace Addons\Hospital\Model;

use Home\Model\WeixinModel;

class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		//$map ['token'] = get_token ();
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		$url = addons_url ( 'Hospital://Hospital/show', $param );
		$res = $this->replyText( $url );
	}
	
	function subscribe($data) {
		error_log("subscribe。。。");
		$openid = $data['FromUserName'];
		$config = getAddonConfig("Hospital");
		$param['openid']=$openid;
		$param['status']=1;
		$url = $config['admin.server.url'];
		if($url) {
			post_data($url, $param);
		}else{
			error_log("url not config!");
		}
	}
	
	function unsubscribe($data) {
		//
		error_log("unsubscribe。。。");
		$openid = $data['FromUserName'];
		$config = getAddonConfig("Hospital");
		$param['openid']=$openid;
		$param['status']=2;
		$url = $config['admin.server.url'];
		if($url) {
			post_data($url, $param);
		}else{
			error_log("url not config!");
		}
	}
}