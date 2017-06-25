<?php
namespace Addons\OrderShip\Model;

use Home\Model\WeixinModel;

class WeixinAddonModel extends WeixinModel {
	function reply($dataArr, $keywordArr = array()) {
		$param ['token'] = get_token ();
		$param ['openid'] = get_openid ();
		$url = addons_url ( 'OrderShip://OrderShip/show', $param );
		$res = $this->replyText( $url );
	}
}