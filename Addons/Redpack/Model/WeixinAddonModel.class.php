<?php
        	
namespace Addons\redpack\Model;
use Home\Model\WeixinModel;
        	
/**
 * redpack的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'redpack' ); // 获取后台插件的配置参数	
		//dump($config);
	}
}
        	