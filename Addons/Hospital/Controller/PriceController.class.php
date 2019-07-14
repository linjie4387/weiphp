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
		
		$kd_y = $this->getexpress($province,$city,$weight,$long,$width,$height);
		$kd_sx = "";
		$hy_y = $this->getfreight($province,$city,$weight,$long,$width,$height);
		$hy_sx = "";
		$hk_y = $this->getair($province,$city,$weight,$long,$width,$height);
		$hk_sx = "";
		$zt_y = $this->gettrain($province,$city,$weight,$long,$width,$height);
		$zt_sx = "";

		$res['errcode']=0;
		$res['data']["kd_y"]	= $this->_formate($kd_y);
		$res['data']["kd_sx"]	= $kd_sx ;
		$res['data']["hy_y"]	= $this->_formate($hy_y) ;
		$res['data']["hy_sx"]	= $hy_sx ;
		$res['data']["hk_y"]	= $this->_formate($hk_y) ;
		$res['data']["hk_sx"]	= $hk_sx ;
		$res['data']["zt_y"]	= $this->_formate($zt_y) ;
		$res['data']["zt_sx"]	= $zt_sx ;
		$this->ajaxReturn ( $res );
	}
	

	/***********************************
	 *	航空价格
	***********************************/
	function getair($province_id,$city_id,$weight,$long,$width,$height) {
		
		$weight = floatval($weight);
		
		if (!$province_id) {
            $this->setError (0, "缺少省份");
            return false;
        }

        if (!$city_id) {
            $this->setError (0, "缺少城市");
            return false;
        }
		
		$priceRow = D('priceair')->where("province_id='".$province_id."' and city_id = ".$city_id."")->find();
		if(!$priceRow){
			$cityall = $this->getRegionall($province_id);
			$priceRow = D('priceair')->where("province_id='".$province_id."' and city_id = ".$cityall."")->find();
		}
		if(!$priceRow){
			return '';
		}
		//var_dump(json_encode($priceRow));	
		$col1_price = $this->_null2float($priceRow['col1']);
		$col2_price = $this->_null2float($priceRow['col2']);
		$col3_price = $this->_null2float($priceRow['col3']);
		$col4_price = $this->_null2float($priceRow['col4']);
		$col5_price = $this->_null2float($priceRow['col5']);
		
		//计重
		$weight = floatval($weight);
		$price1 = $this->_calculateAir($weight,$col1_price,$col2_price,$col3_price,$col4_price,$col5_price);

		//计泡
		$vol_weight = $this->_volume2weight($long,$width,$height);
		$price2 = $this->_calculateAir($vol_weight,$col1_price,$col2_price,$col3_price,$col4_price,$col5_price);
		
		if($price1 > $price2){
			return $price1;
		}
		return $price2;
	}
	
	function _calculateAir($weight,$col1_price,$col2_price,$col3_price,$col4_price,$col5_price){
		
		$col2=45;
		$col3=100;
		$col4=300;
		$col5=500;
	
		if($weight<=0){
			return '';
		}

		if($weight>=0 and $weight <$col2){
			$price = ($col2-1) * $col1_price;
		}else if($weight>=$col2 and $weight <$col3){
			$price = $col2_price * $weight;
		}else if($weight>=$col3 and $weight <$col4){
			$price = $col3_price * $weight;
		}else if($weight>=$col4 and $weight <$col5){
			$price = $col4_price * $weight;
		}else{
			if(empty($col5_price)){
				$price = '';
			}else{
				$price = $col5_price * $weight;
			}
		}
		return $price;
	}

	/***********************************
	 *	快递价格
	***********************************/
	function getexpress($province_id,$city_id,$weight,$long,$width,$height) {
		
		if (!$province_id) {
            $this->setError (0, "缺少省份");
            return false;
        }

        if (!$city_id) {
            $this->setError (0, "缺少城市");
            return false;
        }
		
		$priceRow = D('priceexpress')->where("province_id='".$province_id."' and city_id = ".$city_id."")->find();
		if(!$priceRow){
			$cityall = $this->getRegionall($province_id);
			$priceRow = D('priceexpress')->where("province_id='".$province_id."' and city_id = ".$cityall."")->find();
		}
		if(!$priceRow){
			return '';
		}
		
		
		//var_dump(json_encode($priceRow));
		$weight = floatval($weight);	
		$first_weight_price = floatval($priceRow['first_weight']);
		$next_weight_price = floatval($priceRow['next_weight']);
		//计重
		$price1 = $this->_calculateExpress($weight,$first_weight_price,$next_weight_price);
		//计泡
		$vol_weight = $this->_volume2weight($long,$width,$height);
		$price2 = $this->_calculateexpress($vol_weight,$first_weight_price,$next_weight_price);
		
		if($price1 > $price2){
			return $price1;
		}
		return $price2;
	}
	
	function _calculateExpress($weight,$first_weight_price,$next_weight_price){
		//首重3公斤
		$first_weight=3;		
		//小于首重
		if($weight <= $first_weight){
			$price = $first_weight_price;
		}else{
			//大于首重
			$over_weight = $weight - $first_weight;
			$price = $first_weight_price + $over_weight * $next_weight_price;
		}
		return $price;
	}

	/***********************************
	 *	货运价格
	***********************************/
	function getfreight($province_id,$city_id,$weight,$long,$width,$height) {
		
		if (!$province_id) {
            $this->setError (0, "缺少省份");
            return false;
        }

        if (!$city_id) {
            $this->setError (0, "缺少城市");
            return false;
        }
		
		$priceRow = D('pricefreight')->where("province_id='".$province_id."' and city_id = ".$city_id."")->find();
		if(!$priceRow){
			$cityall = $this->getRegionall($province_id);
			$priceRow = D('pricefreight')->where("province_id='".$province_id."' and city_id = ".$cityall."")->find();
		}
		if(!$priceRow){
			return '';
		}
		
		//计泡
		$long 	= floatval($long);
		$width 	= floatval($width);
		$height = floatval($height);
		$v_price = floatval($priceRow['m3']);
		
		$price1 = $this->_calculateFreight4volume($v_price,$long,$width,$height);
		
		//计重
		$weight = floatval($weight);
		$kg_price = floatval($priceRow['kg']);
		$t_price  = floatval($priceRow['t']);
		
		$price2 = $this->_calculateFreight4weight($weight,$kg_price,$t_price);

		if($price1 > $price2){
			return $price1;
		}
		return $price2;
	}
	//货运-计泡价格
	function _calculateFreight4volume($v_price,$long,$width,$height){
		
		if(empty($long) || empty($width) || empty($height)){
			return 0;
		}
		//long、width、height的单位是厘米，1立方厘米=1/1000000 立方米
		$volume = ($long * $width * $height)/1000000;
		$price = $volume * $v_price;
		
		return $price;
	}
	//货运-计重价格
	function _calculateFreight4weight($weight,$kg_price,$t_price){
		if($weight>1000){
			$weight = $weight/1000;
			$price = $t_price * $weight;
		}else{
			$price = $kg_price * $weight;
		}
		return $price;
	}


	/***********************************
	 *	中铁价格
	***********************************/
	function gettrain($province_id,$city_id,$weight,$long,$width,$height) {
		$weight = floatval($weight);
		
		if (!$province_id) {
            $this->setError (0, "缺少省份");
            return false;
        }

        if (!$city_id) {
            $this->setError (0, "缺少城市");
            return false;
        }
		
		$priceRow = D('pricetrain')->where("province_id='".$province_id."' and city_id = ".$city_id."")->find();
		if(!$priceRow){
			$cityall = $this->getRegionall($province_id);
			$priceRow = D('pricetrain')->where("province_id='".$province_id."' and city_id = ".$cityall."")->find();
		}
		if(!$priceRow){
			return '';
		}
		
		$min_price = $priceRow['min_price'];
		$unit_price = $priceRow['unit_price'];
		
		//计重
		$weight = floatval($weight);
		$price1 = $this->_calculateTrain($weight,$min_price,$unit_price);
		
		//计泡
		$vol_weight = $this->_volume2weight($long,$width,$height);
		$price2 = $this->_calculateTrain($vol_weight,$min_price,$unit_price);
		
		if($price1 > $price2){
			return $price1;
		}
		return $price2;
	}
	
	function _calculateTrain($weight,$min_price,$unit_price){
		
		$price = $unit_price * $weight;
		if($price < $min_price){
			$price = $min_price;
		}
		
		return $price;
	}	
	/****************************************************
	 *	                   公共函数                       *
	*****************************************************/
	
	//小数点精确
	function _formate($num){
		if($num<=0){
			return $num;
		}
		if(empty($num)){
			return '';
		}
		$num = sprintf('%.2f',$num);
		return $num;
	}
	
	//空串转数值
	function _null2float($var){
		if(empty($var)){
			return 0;
		}
		return $var;
	}
	
	//字符串转价格	
	function _str2float($str){
		if(empty($col5_price)){
			return 0;
		}
		$str = str_replace("-","",$str);
		$str = str_replace("—","",$str);
		$str = str_replace(" ","",$str);
		if(empty($col5_price)){
			return 0;
		}
		
		$price = floatval($str);
		return $price;
	}

	//计泡规则中，体积转重量
	function _volume2weight($long,$width,$height){

		if(empty($long) || empty($width) || empty($height)){
			return 0;
		}
		$volume = $long*$width*$height;
		$weight = $volume/6000;
		return $weight;
	}
	
	//获得省内是否有“全境”的报价，比如福建全境
	function getRegionall($province_id){

		$allcity = D('priceregion')->where("parent_id=".$province_id." and city_all='1'")->find();
		
		if($allcity){
			return $allcity['region_id'];
		}
		return -1;
	}
	
	function loadprivince() {
		$region_list = D ( "priceregion" )->where ( "parent_id=0" )->order('sort asc')->select ();
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
			$region_list = D ( "priceregion" )->where ( "parent_id='".$parent_id."'" )->order('sort asc')->select ();
			$res['data']= $region_list;
		}
		$this->ajaxReturn ( $res );
	}
}