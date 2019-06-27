<?php

namespace Addons\Hospital\Controller;
use Addons\Hospital\Model\RegionModel;
class PriceController extends BaseController {
	/**
	 * 查询价格
	 */
	function searchprice() {
		$this->display ( "Price/price" );
		/*
		$user = $this->validuser ();
		if ($user) {
			//if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
			$this->display ( "Price/price" );
			//}
		} else {
			redirect ( addons_url ( "Hospital://Engineer/regist" ) );
		}
		*/
	}
	
	function loadprice() {
		$province = I ( "province" );
		$city = I ( "city" );
		$weight = I ( "weight" );
		$long = I ( "long" );
		$width = I ( "width" );
		$height = I ( "height" );
		
		$kd_y = "kd_y123";
		$kd_sx = "kd_sx123";
		$hy_y = "hy_y123";
		$hy_sx = "hy_sx123";
		$hk_y = "hk_y123";
		$hk_sx = "hk_sx123";

		$res['errcode']=0;
		$res['data']["kd_y"]	= $kd_y ;
		$res['data']["kd_sx"]	=$kd_sx ;
		$res['data']["hy_y"]	=$hy_y ;
		$res['data']["hy_sx"]	= $hy_sx ;
		$res['data']["hk_y"]	= $hk_y ;
		$res['data']["hk_sx"]	= $hk_sx ;
		$this->ajaxReturn ( $res );
	}
	
	function loadprivince() {
		$region_list = D ( "region" )->where ( "parent_id=1" )->select ();
		$res['errcode']=0;
		$res['data']=$region_list;
		$this->ajaxReturn ( $res );
	}
	
	function loadcity() {
		$parent_id = I ( "parent_id" );
		$arr = array();
		$res['errcode']=0;
		$res['data']= $arr;
		if($parent_id) {
			$region_list = D ( "region" )->where ( "parent_id='".$parent_id."'" )->select ();
			$res['data']= $region_list;
		}
		$this->ajaxReturn ( $res );
	}
}