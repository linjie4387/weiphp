<?php

namespace Addons\Hospital\Controller;
use Addons\Hospital\Model\WeichatuserModel;

class AboutController extends BaseController {
	
	/**
	 * 商品信息首页
	 */
	function goods() {
		$this->display ( "About/goodsMsgjchx" );
	}
	
	/**
	 * 商品详情首页
	 */
	function company() {
			$this->display ( "About/companyMsgjchx" );
	}
}