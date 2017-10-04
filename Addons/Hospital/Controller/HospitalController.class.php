<?php

namespace Addons\Hospital\Controller;

use Addons\Hospital\Model\DatadictModel;
use Addons\Hospital\Model\HospitalorderModel;
use Addons\Hospital\Model\WeichatuserModel;
use Addons\Hospital\Model\DeliveryModel;
use Home\Controller\AddonsController;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
// use Home\Controller\AddonsController;
class HospitalController extends BaseController {
	
	/**
	 * 拍照下单页面
	 */
	function placeOrder() {
		$user = $this->validuser ();
		if ($user) {
			if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
				$map ['hospital_id'] = $user ['hospital_id'];
				$officelist = D ( 'office' )->where ( $map )->select ();
				$this->assign ( "officelist", $officelist );
				$this->assign ( "office_id", $user ['office_id'] );
				$this->assign ( "hospital_name", $user ['hospital_name'] );
				$this->assign("user_type", $user['type']);
				$companylist = D ( 'datadict' )->where ( "key_id = ".DatadictModel::KEY_ORDER_COMPANY )->select ();
				$this->assign("companylist", $companylist);
				
				$this->display ( "Hospital/order" );
			} else {
				$this->display ( "Hospital/show" );
			}
		} else {
			redirect ( U ( "regist" ) );
		}
	}
	
	private function fetchQiniuPic($mediaId){
		$token = get_token ();
		$accessToken = get_access_token ( $token );
		$url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=" . $accessToken . "&media_id=" . $mediaId;
		vendor ( 'autoload', VENDOR_PATH . 'Qiniu' );
		$config = C ( 'UPLOAD_QINIU_CONFIG' );
		$auth = new Auth ( $config ['accessKey'], $config ['secrectKey'] );
		$bmgr = new BucketManager ( $auth );
		$key = 'wx_order_' . uniqid ();
		error_log($key);
		list ( $ret, $err ) = $bmgr->fetch ( $url, $config ['bucket'], $key );
		if ($err !== null || $err!='') {
			error_log ( "ret:" . $ret );
		} else {
			error_log ( "err:" . $err );
		}
		return $config ['domain'] . $ret ['key'];
	}
	
	/**
	 * 提交订单
	 */
	function submitOrder() {
		$mediaId = I ( "serverId" );
		if (empty ( $mediaId )) {
			$this->error ( "请上传采购单图片！" );
		}
		// 七牛图片抓取
		if(!is_array($mediaId)) {
			$pics[] = self::fetchQiniuPic($mediaId);
		}else{
			foreach ($mediaId as $pic_url) {
				error_log($pic_url);
				$pics[] = self::fetchQiniuPic($pic_url);
			}
		}
		// 科室
		$officeId = I ( "offiece_id" );
		if (empty ( $officeId )) {
			$this->error ( "请选择科室！" );
		}
		$openid = get_openid ( NULL );
		$map ['open_id'] = $openid;
		$user = D ( 'weichatuser' )->where ( $map )->find ();
		$data ['weichatuser_id'] = $user ['weichatuser_id'];
		$data ['name'] = $user ['name'];
		$data ['mobile'] = $user ['mobile'];
		
		if($user['type'] == 1){
			$data ['is_agent'] = 1;
			$data ['hospital_id'] = $user ['hospital_id'];
			$data ['hospital_name'] = $user ['hospital_name'];
			$data ['office_id'] = $officeId;
			$data ["order_company"] = ( int ) $user ['order_company'];
			$office = D ( 'office' )->where ( "office_id=$officeId" )->find ();
			if ($office) {
				$data ['office_name'] = $office ['name'];
			}
		}elseif($user['type'] == 3){
			// 医院
			$hospitalId = I ( "hospital_id" );
			if (empty ( $hospitalId )) {
				$this->error ( "请选择医院！" );
			}
			$orderCompany = I( 'order_company' );
			if (empty ( $orderCompany ) || $orderCompany == -1) {
				$this->error ( "请选择接单公司！" );
			}
			$data ['is_agent'] = 2; 
			$data ['hospital_id'] = $hospitalId;
			$hospital = D ( 'hospital' )->where ( "hospital_id=$hospitalId" )->find ();
			if ($hospital) {
				$data ['hospital_name'] = $hospital ['name'];
			}			
			$data ['office_id'] = $officeId;
			$office = D ( 'office' )->where ( "office_id=$officeId" )->find ();
			if ($office) {
				$data ['office_name'] = $office ['name'];
			}			
			$data ["order_company"] = $orderCompany;
			$data ["is_agent"] = 2;
		}
		
		$data ['status'] = HospitalorderModel::STATUS_NEW;
		$data ['preapply_time'] = date ( "Y-m-d H:i:s" );
		$data ['remark'] = I ( "remark" );
		error_log ( "order_company:" . $data ['order_company'] );
		$orderId = D ( 'hospitalorder' )->add ( $data );
		if ($orderId) {
			if($pics){
				foreach ($pics as $pic) {
					$orderpic['order_id'] = $orderId;
					$orderpic['pic_url'] = $pic;
					$orderpic['create_time'] = date("Y-m-d H:i:s");
					$picid = D('OrderPic')->add($orderpic);
					if(!$picid){
						$this->error("新增订单图片失败：".  D('OrderPic')->getDbError () );
					}
				}
			}
			
			redirect ( U ( 'listOrder' ) );
		} else {
			$this->error ( "系统异常：" . D ( 'hospitalorder' )->getDbError () );
		}
	}
	
	/**
	 * 我的订单
	 */
	function listOrder() {
		$user = $this->validuser ();
		if ($user) {
			if($user['type']==3){//如果是代下单用户，则查看本人处理过的历史配送任务。
				$driver = D('deliveryman')->where("weichatuser_id = ".$user['weichatuser_id']." and is_driver = 1")->find();
				$deliveryman = D ( 'deliveryman' )->query("select a.* from smpss_deliveryman a,smpss_weichatuser b where a.weichatuser_id = b.weichatuser_id and b.open_id='".$user ['open_id']."'");
				$delivery = D ( 'delivery' )->where ("driver_deliveryman_id=".$deliveryman[0]['deliveryman_id']." and status >2" )->page ( $page, $pageSize )->select ();
				
				foreach ( $delivery as &$deli ) {
					$map = array (
							"key_id" => DatadictModel::KEY_DELIVERY_STATUS, // 送单状态
							"value" => $deli ['status'] 
					);
					$dict = D ( 'datadict' )->where ( $map )->find ();
					$deli ['status_name'] = $dict ['name'];
					
					if($driver){
						if($driver['deliveryman_id'] == $deli['driver_deliveryman_id']){
							$deli['is_driver'] = 1;
						}else{
							$deli['is_driver'] = 0;	
						}
					}else{
						$deli['is_driver'] = 0;
					}
				}
	
				$this->assign("deliverylist", $delivery);
				$this->display ( "Hospital/undelivery" );				
			}else{  //如果是医院用户，则查看历史订单
				if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
					$map ['weichatuser_id'] = $user ['weichatuser_id'];
					$page = I ( 'page' ) ? I ( 'page' ) : 1;
					$pageSize = I ( 'pagesize' ) ? I ( 'pagesize' ) : 10;
					$orders = D ( 'hospitalorder' )->where ( $map )->order ( 'hospitalorder_id desc' )->page ( $page, $pageSize )->select ();
					$count = D ( 'hospitalorder' )->where ( $map )->count ();
					foreach ( $orders as &$order ) {
						$map = array (
								"key_id" => DatadictModel::KEY_ORDER_STATUS, // 订单状态
								"value" => $order ['status'] 
						);
						$dict = D ( 'datadict' )->where ( $map )->find ();
						$order ['status_name'] = $dict ['name'];
						$piclist = D('OrderPic')->where("order_id={$order['hospitalorder_id']} and type=1")->field('pic_url')->select();
						$p_company = array (
								"key_id" => DatadictModel::KEY_ORDER_COMPANY, // 订单状态
								"value" => $order ['order_company']
						);
						$company = D("datadict")->where($p_company)->find();
						$order['piclist'] = $piclist;
						$order['order_company_name'] = $company['name'];
						if($order['is_agent']==HospitalorderModel::ORDER_TYPE_HOSPITAL){
							$order['order_type_name'] = '医院下单';
						}else {
							$order['order_type_name'] = '代下单';
						}
						$myord = D('order')->where('hospitalorder_id='.$order['hospitalorder_id'])->find();
						if($myord){
							$order['order_id']=$myord['order_id'];
						}
					}
					if (! $_POST) {
						$this->assign ( "page", $page );
						$this->assign ( "pagesize", $pageSize );
						$this->assign ( "totalpage", $count / $pageSize );
						$this->assign ( "orders", $orders );
						$this->display ( "Hospital/orderlist" );
					} else {
						$data ['orders'] = $orders;
						$data ['page'] = $page;
						$data ['pagesize'] = $pageSize;
						$data ['totalpage'] = $count / $pageSize;
						$this->success ( $data, "", true );
					}
				} else {
					$this->display ( "Hospital/show" );
				}
			}
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	
	/**
	 * 刷新我的订单
	 */
	function reloadOrderOne() {
		$user = $this->validuser ();
		
		if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
			$hospitalorder_id = I ( 'hospitalorder_id' ) ? I ( 'hospitalorder_id' ) : -1;
			$map ['weichatuser_id'] = $user ['weichatuser_id'];
			$map ['hospitalorder_id'] = $hospitalorder_id;
			
			$order = D ( 'hospitalorder' )->where ( $map )->find ();
			if($order){
				$map = array (
						"key_id" => DatadictModel::KEY_ORDER_STATUS, // 订单状态
						"value" => $order ['status'] 
				);
				$dict = D ( 'datadict' )->where ( $map )->find ();
				$order ['status_name'] = $dict ['name'];
				$piclist = D('OrderPic')->where("order_id={$order['hospitalorder_id']} and type=1")->field('pic_url')->select();
				$p_company = array (
						"key_id" => DatadictModel::KEY_ORDER_COMPANY, // 订单状态
						"value" => $order ['order_company']
				);
				$company = D("datadict")->where($p_company)->find();
				$order['piclist'] = $piclist;
				$order['order_company_name'] = $company['name'];
				if($order['is_agent']==HospitalorderModel::ORDER_TYPE_HOSPITAL){
					$order['order_type_name'] = '医院下单';
				}else {
					$order['order_type_name'] = '代下单';
				}
			}else{
				$data ['order'] = NULL;	
			}			
		}else{
			$data ['order'] = NULL;
		}
		$data ['order'] = $order;
		$this->success ( $data, "", true );
	}
	
	/**
	 * 订单明细
	 */
	function myorderdetail(){
		$openid = get_openid ();
		$orderId = I("oid");
		$order = D ( 'hospitalorder' )->where ( "hospitalorder_id=".$orderId )->find ();
		if($order['is_agent']==HospitalorderModel::ORDER_TYPE_HOSPITAL){
			$order['order_type_name'] = '医院下单';
		}else {
			$order['order_type_name'] = '代下单';
		}
		$p_company = array (
				"key_id" => DatadictModel::KEY_ORDER_COMPANY, // 订单状态
				"value" => $order ['order_company']
		);
		$company = D("datadict")->where($p_company)->find();
		$order['order_company_name'] = $company['name'];
		
		$piclist = D('OrderPic')->where("order_id={$order['hospitalorder_id']} and type=1")->field('pic_url')->select();
		$this->assign("piclist", $piclist);
		$this->assign("order", $order);
		$this->display("orderdetail");
	}
	/**
	 * 订单确认
	 */
	function confirmOrder(){
		$user = $this->validuser ();
		//echo json_encode($user);	
		//exit;
		$orderId = I("hospitalorder_id");
		$o = D("hospitalorder")->where("hospitalorder_id=".$orderId)->find();
		if(!$o) {
			$this->error("订单不存在：".$orderId);
		}else if($o['status']!=2){
			$this->error("订单状态不正常，不能确认！");
		}
		$order = D("hospitalorder");
		$data['status'] = 3;
		if($order->where("hospitalorder_id=".$orderId)->save($data)){
			if(!IS_AJAX){
				redirect ( U ( 'listOrder' ) );
			}else {
				$this->ajaxReturn($rs);
			}
		}else {
			if(!IS_AJAX){
				$this->error("订单确认失败：".$order->getDbError());
			}else {
				$rs["errcode"] =0;
				$rs["errmsg"] ="订单确认失败：".$order->getDbError();
				$this->ajaxReturn($rs);
			}
		}
	}
	
	/**
	 * 用户注册页面
	 */
	function regist() {
		$openid = get_openid ();
		if (! $openid || $openid == - 1 || $openid == - 2) {
			// 清空session重新取一次
			$token = get_token ();
			session ( 'openid_' . $token, NULL );
			$openid = get_openid ();
			//还取不到就抛异常
			if(! $openid || $openid == - 1 || $openid == - 2){
				$this->error ( "系统异常：请稍后再试！" );
			}
		}
		$param ['open_id'] = $openid;
		$param ['is_valid'] = 1;
		$user = D ( 'weichatuser' )->where ( $param )->find ();
		if (! $user) {
			$this->assign ( "user_type", WeichatuserModel::USER_TYPE_HOSPITAL );
			$this->display ( "Hospital/signup" );
		} else {
			$this->assign ( "title", "用户已注册" );
			$this->assign ( "content", "您已注册，欢迎使用会员服务。" );
			$this->display ( "Hospital/show" );
		}
	}
	/**
	 * 发送短信
	 */
	function send_sms(){
		$mobile = I("mobile");
		if(!$mobile){
			$data['errcode'] = -1;
			$data['errmsg']="请输入手机号码。";
			$this->ajaxReturn($data);
		}
		$openid = get_openid();
		$cache_code = S("verify_code_".$openid);
		if($cache_code) {
			$data['errcode'] = -1;
			$data['errmsg']="短信发送太频繁，请稍后再试。";
			$this->ajaxReturn($data);
		}else {
			$randNum = rand(1000,9999);
			error_log("随机验证码:".$randNum);
			$config = getAddonConfig("Hospital");
			$juheServer = $config["juhe.api.url"];
			$smsData = array(
				"key"=>	$config["juhe.api.key"],
				"mobile"=> $mobile,
				"tpl_id"=> 12650,
				"tpl_value"=>"#code#=".$randNum
			);
			$content = $this->juhecurl($juheServer, $smsData,1 );
			$ret = json_decode($content,true);
			if($ret['error_code']==0){
				//状态为0，说明短信发送成功
				S("verify_code_".$openid, $randNum, 120);
				$data['errcode'] = 0;
				error_log("短信发送成功，短信ID:".$ret['result']['sid']);
				$data['errmsg']="短信发送成功，短信ID:".$ret['result']['sid'];
				$this->ajaxReturn($data);
			}else{
				//状态非0，说明失败
				$data['errcode'] = -1;
				$data['errmsg']="短信发送失败：".$ret["reason"];
				error_log("短信发送失败：".$ret["reason"]);
				$this->ajaxReturn($data);
			}
		}
	}
	
	/**
	 * 请求接口返回内容
	 * @param  string $url [请求的URL地址]
	 * @param  string $params [请求的参数]
	 * @param  int $ipost [是否采用POST形式]
	 * @return  string
	 */
	function juhecurl($url,$params=false,$ispost=0){
		$httpInfo = array();
		$ch = curl_init();
	
		curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
		curl_setopt( $ch, CURLOPT_USERAGENT , 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22' );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 30 );
		curl_setopt( $ch, CURLOPT_TIMEOUT , 30);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
		if( $ispost )
		{
			curl_setopt( $ch , CURLOPT_POST , true );
			curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
			curl_setopt( $ch , CURLOPT_URL , $url );
		}
		else
		{
			if($params){
				curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
			}else{
				curl_setopt( $ch , CURLOPT_URL , $url);
			}
		}
		$response = curl_exec( $ch );
		if ($response === FALSE) {
			//echo "cURL Error: " . curl_error($ch);
			return false;
		}
		$httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
		$httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
		curl_close( $ch );
		return $response;
	}
	
	/**
	 * 查询医院及科室列表
	 */
	function listhospital() {
		$key = I ( "key" );
		if (empty ( $key )) {
			$this->error ( "请输入医院名称！", "", true );
		}
		$map ['name'] = array (
				'like',
				"%$key%" 
		);
		$list = D ( 'hospital' )->where ( $map )->select ();
		if (empty ( $list )) {
			$this->error ( "找不到类似名称的医院，换个名称试试吧。", "", true );
		}
		foreach ( $list as &$hospital ) {
			$param ['hospital_id'] = $hospital ['hospital_id'];
			$office = D ( 'office' )->where ( $param )->select ();
			$hospital ['office'] = $office;
		}
		$this->success ( $list, "", true );
	}
	
	/**
	 * 用户提交注册信息
	 */
	function join() {
		$openid = get_openid ();
		$config = getAddonConfig("Hospital");
		error_log($config['sms.verify.switch']);
		if($config['sms.verify.switch']=='on') {
			$verifyCode = I("verify_code");
			if(!$verifyCode){
				$this->error ( "请输入验证码。" );
			}
			$cache_code = S("verify_code_".$openid);
			if($cache_code) {
				if($cache_code!=$verifyCode){
					$this->error ( "无效的验证码。" );
				}
			}else {
				$this->error ( "验证码已失效，请重新发送。" );
			}
		}
		
		$userType = I ( 'type' );
		if (empty ( $userType )) {
			$this->error ( "请选择用户类型。" );
		}
		$name = I ( "name" );
		$mobile = I ( "mobile" );
		if (empty ( $name ) || empty ( $mobile )) {
			$this->error ( "请输入姓名与手机号码。" );
		}
		$data ['type'] = $userType;
		$data ['open_id'] = $openid;
		$data ['name'] = $name;
		$data ['mobile'] = $mobile;
		if ($userType == WeichatuserModel::USER_TYPE_HOSPITAL) {
			$hospitalId = I ( 'hospital_id' );
			if (empty ( $hospitalId )) {
				$this->error ( "请选择您所在的医院！" );
			}
			$data ['hospital_id'] = $hospitalId;
			$hospital = D ( 'hospital' )->where ( "hospital_id=$hospitalId" )->find ();
			$data ['hospital_name'] = $hospital ['name'];
			
			$officeId = I ( 'office_id' );
			if (empty ( $officeId )) {
				$this->error ( "请选择您所在的科室!" );
			}
			$data ['office_id'] = $officeId;
			$office = D ( 'office' )->where ( "office_id=$officeId" )->find ();
			$data ['office_name'] = $office ['name'];
		}else if($userType == WeichatuserModel::USER_TYPE_AGENT){
			$data ['hospital_id'] ="";
			$data ['hospital_name'] ="";
			$data ['office_id'] = "";
			$data ['office_name'] = "";
			$data ['level'] = 1;
		}
		
		$data ['status'] = WeichatuserModel::USER_STATUS_TO_AUDIT; // 待审核
		$data ['apply_time'] = date ( "Y-m-d H:i:s" );
		$weichatUserObj = D ( "weichatuser" );
		$param ['open_id'] = $openid;
		$user = $weichatUserObj->where ( $param )->find ();
		if (empty ( $user )) {
			$uid = $weichatUserObj->add ( $data );
		} else {
			$data ['is_valid'] = 1;
			$data ['status'] = WeichatuserModel::USER_STATUS_TO_AUDIT;
			$rs = $weichatUserObj->where ( $param )->save ( $data );
			$uid = $user ['weichatuser_id'];
		}
		if ($uid) {
			$this->assign ( "title", "注册审核中" );
			$this->assign ( "content", "您已成功提交用户注册申请，请耐心等待管理员审核通过后即可正常使用。" );
			$this->display ( "Hospital/show" );
			// echo("您已成功提交用户注册申请，请耐心等待管理员审核通过后即可正常使用。".$uid);
		} else {
			error_log ( "用户注册失败：" . D ( 'weichatuser' )->getDbError () );
			// $this->error ( "用户注册失败！", "", true );
			echo ("用户注册失败：" . D ( 'weichatuser' )->getDbError ());
		}
	}
	
	/**
	 * 送货单
	 */
	function mydelivery() {
		$user = $this->validuser ();
		if ($user) {
			$delivery_id = I("id");
			$delivery = D("delivery")->where("delivery_id = $delivery_id")->find();
			$delivery_status = D ( 'datadict' )->where ( "key_id = ".DeliveryModel::KEY_DELIVERY_STATUS." and value = ".$delivery['status'] )->find();
			$delivery['status_name'] = $delivery_status['name'];
			$driver = D('deliveryman')->where("weichatuser_id = ".$user['weichatuser_id']." and is_driver = 1")->find();
			
			if($driver){
				if($driver['deliveryman_id'] == $delivery['driver_deliveryman_id']){
					$delivery['is_driver'] = 1;
				}else{
					$delivery['is_driver'] = 0;	
				}
			}else{
				$delivery['is_driver'] = 0;
			}
			//error_log("-----------------is_driver:".$delivery['is_driver']);
			//error_log("user_id:".$user['weichatuser_id']);
			$this->assign("delivery", $delivery);
			$this->display ( "Hospital/delivery" );
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	/**
	 * 未派送的送货单
	 */
	function myundelivery() {
		$user = $this->validuser ();
		//if($user){
		//}else{
		//	$user['weichatuser_id'] ='226';
		//	$user['open_id'] ='of7oJsx6_djUxdO66koZ90cDa6DY';
		//}
		if ($user) {
			$page = I ( 'page' ) ? I ( 'page' ) : 1;
			//$pageSize = I ( 'pagesize' ) ? I ( 'pagesize' ) : 10;
			$pageSize = 100;
			//司机、代下单用户，查看未配送的订单
			if($user['type'] == '3'){
				$driver = D('deliveryman')->where("weichatuser_id = ".$user['weichatuser_id']." and is_driver = 1")->find();
				$deliveryman = D ( 'deliveryman' )->query("select a.* from smpss_deliveryman a,smpss_weichatuser b where a.weichatuser_id = b.weichatuser_id and b.open_id='".$user ['open_id']."'");
				$count = D ( 'delivery' )->where("driver_deliveryman_id=".$deliveryman[0]['deliveryman_id']." and status <=2" )->count ();
				$delivery = D ( 'delivery' )->where ("driver_deliveryman_id=".$deliveryman[0]['deliveryman_id']." and status <=2" )->page ( $page, $pageSize )->select ();
				
				foreach ( $delivery as &$deli ) {
					$map = array (
							"key_id" => DatadictModel::KEY_DELIVERY_STATUS, // 送单状态
							"value" => $deli ['status'] 
					);
					$dict = D ( 'datadict' )->where ( $map )->find ();
					$deli ['status_name'] = $dict ['name'];
					
					if($driver){
						if($driver['deliveryman_id'] == $deli['driver_deliveryman_id']){
							$deli['is_driver'] = 1;
						}else{
							$deli['is_driver'] = 0;	
						}
					}else{
						$deli['is_driver'] = 0;
					}
				}
	
				$this->assign("deliverylist", $delivery);
				$this->display ( "Hospital/undelivery" );
			}else{
				//查出待处理的预订单
				$orderlist = D ( 'hospitalorder' )->where ("weichatuser_id=".$user['weichatuser_id']." and status =2" )->page ( $page, $pageSize )->select ();
				foreach ( $orderlist as &$order ) {
					$map = array (
							"key_id" => DatadictModel::KEY_ORDER_STATUS, // 送单状态
							"value" => $order ['status'] 
					);
					$dict = D ( 'datadict' )->where ( $map )->find ();
					$order ['status_name'] = $dict ['name'];
				}
				$config = getAddonConfig("Hospital");
				$tpl = $config["template.delivery.notify"];
				$token = $config["template.token"];
				$this->assign("orderlist", $orderlist);
				$this->assign("token",$token);
				$this->display( "Hospital/undealorderlist" );
			}
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	/**
	 * 送货单 - 货品明细
	 */
	function mydeliverygoods() {
		$user = $this->validuser ();
		if ($user) {
			$delivery_id = I("id");
			//$from = I("from");
			$delivery = D("delivery")->where("delivery_id = $delivery_id")->find();
			$delivery_status = D ( 'datadict' )->where ( "key_id = ".DeliveryModel::KEY_DELIVERY_STATUS." and value = ".$delivery['status'] )->find();
			$delivery['status_name'] = $delivery_status['name'];
			$driver = D('deliveryman')->where("weichatuser_id = ".$user['weichatuser_id']." and is_driver = 1")->find();
			
			if($driver){
				if($driver['deliveryman_id'] == $delivery['driver_deliveryman_id']){
					$delivery['is_driver'] = true;
				}else{
					$delivery['is_driver'] = false;	
				}
			}else{
				$delivery['is_driver'] = false;
			}
			
			$goods = D('deliverywithgoods')->where("delivery_id = $delivery_id")->select();
			$is_finish = 1;
			foreach($goods as $key => $val){
				$order = D('order')->where("order_id={$val['order_id']}")->find();
				if($order['is_valid']==1) {
					if($val['delivery_status']==0) {//未发车
						$goods[$key]['sign_name'] = '待发车';
					}else{//已发货
						$goods_sign = D ( 'datadict' )->where ( "key_id = ".DeliveryModel::KEY_DELIVERY_SIGN." and value = ".$val['sign_status'] )->find();
						$goods[$key]['sign_name'] = $goods_sign['name'];
										
						if($val['sign_status']==2){//已签收
							$goods[$key]['sign_name'] = '<span style="color:#5eb95e;">'.$goods_sign['name'].'<span>';
						}else if($val['sign_status']==3){//被拒签
							$goods[$key]['sign_name'] = '<span style="color:#dd514c;">'.$goods_sign['name'].'<span>';
						}
					}
				}else {
					$goods[$key]['sign_name'] = '<span style="color:#dd514c;">订单作废<span>';
				}
				$goods[$key]['is_for_goods_str'] = ($goods[$key]['is_for_goods'] == 0) ? "发票" : "商品";
				
				$order_id = $val['order_id'];
				$orderInfo = D('order')->where("order_id = $order_id")->find();
				$goods[$key]['hospital_name'] = $orderInfo['hospital_name'];
				$goods[$key]['office_name'] = $orderInfo['office_name'];
				//接单备注
				$goods[$key]['order_remark'] =  $orderInfo['remark'];
				
				$hospitalInfo = D('hospital')->where("hospital_id = ".$orderInfo['hospital_id'])->find();
				$goods[$key]['address'] = $hospitalInfo['address'];
				
				$goods[$key]['consignee'] = $orderInfo['name'];
				$goods[$key]['mobile'] = $orderInfo['mobile'];
				$goods[$key]['delivery_no'] = $orderInfo['delivery_no'];	
				$goods[$key]['invoice_no'] = $orderInfo['invoice_no'];	
				
				$goods[$key]['is_valid'] = $order['is_valid'];
				if($order['is_valid']==1 && $goods[$key]['delivery_status']==0){//订单有效且有明细未发货，则发货单状态为未完成
					$is_finish = 0;
				}
			}
			$this->assign("delivery", $delivery);
			$this->assign("goods", $goods);
			$this->assign("is_finish", $is_finish);
			//$this->assign("from", $from);
			$this->display ( "Hospital/deliverygoods" );
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	
	/**
	 * 送货单 - 送货员明细
	 */
	function mydeliverydrivers() {
		
		$delivery_id = I("id");	
		
		$drivers = D('deliverywithman')->where("delivery_id = $delivery_id")->select();
		foreach($drivers as $key => $val){
			$info = D('deliveryman')->where("deliveryman_id = ".$val['deliveryman_id'])->find();
			$drivers[$key]['name'] = $info['name'];
			$drivers[$key]['mobile'] = $info['mobile'];
		}
		$this->assign("drivers", $drivers);
		$this->display ( "Hospital/deliverydrivers" );
		
	}
	
	/**
	 * 送货单 - 发车
	 */
	function deliverysend() {
		$user = $this->validuser ();
		if ($user) {
			$delivery_id = I("id");	
			
			$data['status'] = 2;
			$data['delivery_time'] = date ( "Y-m-d H:i:s" );
			$res = D('delivery')->where("delivery_id=".$delivery_id)->save($data);
			if($res){		
				$rs["errcode"] = 0;
				$this->ajaxReturn($rs);
			}else {			
				$rs["errcode"] =1;
				$rs["errmsg"] ="发车失败：".$order->getDbError();
				$this->ajaxReturn($rs);
			}
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	/**
	 * 送货单明细	- 发车
	 */
	function deliverySendGoods(){
		$user = $this->validuser ();
		
		if ($user) {
			$delivery_id = I("id");
			$deliverGoodsList = I("deliveryList");

			if($deliverGoodsList){
				$param['status'] = 2;//已发车
				$param['delivery_time'] = date ( "Y-m-d H:i:s" );
				D('delivery')->where("delivery_id={$delivery_id}")->save($param);//修改发货单为发车状态
				$delivery = D('delivery')->where("delivery_id={$delivery_id}")->find();
				error_log("man:".$delivery['driver_deliveryman_id']);
				$driver = D('deliveryman')->where("deliveryman_id={$delivery['driver_deliveryman_id']}")->find();
				$db = D('deliverywithgoods');
				$arr_order = array();
				foreach ($deliverGoodsList as $withgoods_id) {
					//error_log("----:".$withgoods_id.":----");
					$data['delivery_status'] = 1;
					$data['delivery_time']  = date ( "Y-m-d H:i:s" );
					$data['modify_time'] = date ( "Y-m-d H:i:s" );
					$param['sign_status'] = 1;
					$param['sign_time'] = null;
					$res = $db->where("withgoods_id=".$withgoods_id)->data($data)->save();
					$deliveryWithGoods = $db->where("withgoods_id=".$withgoods_id)->find();
					$order = D('order')->where("order_id={$deliveryWithGoods['order_id']}")->find();
					if($order['weichatuser_id']&&!in_array($order['order_id'], $arr_order)) {//微信端提交的订单，则发现消息通知
						$customer = D('weichatuser')->join('smpss_order on smpss_order.weichatuser_id=smpss_weichatuser.weichatuser_id')
										->where("smpss_order.order_id={$order['order_id']}")->find();
						error_log("open_id:".$customer['open_id']);
						$openid = $customer['open_id'];
						$content ['first'] = "您好，您的货物正在配送中，请做好接货准备。";
						$content ['keyword1'] = $order['order_id'];//订单编号
						$content ['keyword2'] = $order['name'];//收货人
						$content ['keyword3'] = $order['hospital_name'].$order['office_name'];//收获地址
						$content ['keyword4'] = $driver['name'];//送货人员
						$content ['keyword5'] = $driver['mobile'];//送货电话
						$content ['remark'] = "";
						$config = getAddonConfig("Hospital");
						$tpl = $config["template.delivery.notify"];
						$token = $config["template.token"];
						
						$this->_sendTemplateMsg($openid, $content, $tpl, "", $token);
					}
					if(!$res){
						$this->error ( "系统异常：" . $db->getDbError () );
					}
				}
				//redirect ( U ( 'mydelivery' ) );
				//error_log("---------id:".$delivery_id);
				$this->redirect("mydelivery", array("id"=>$delivery_id));
			}else {
				redirect ( U ( 'mydeliverygoods' ) );
			}
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	
	/**
	 * 送货单 - 签收
	 */
	function deliverysign() {
		$user = $this->validuser ();
		if ($user) {
			$withgoods_id = I("gid");	
			$status = I("status")?I("status"):2;//默认是签收
			$data['sign_status'] = $status;
			$data['remark'] = I('remark');
			$data['sign_time'] = date ( "Y-m-d H:i:s" );
			$data['modify_time'] = date ( "Y-m-d H:i:s" );
			$withGoods = D('deliverywithgoods')->where("withgoods_id=".$withgoods_id)->find();
			$res = D('deliverywithgoods')->where("withgoods_id=".$withgoods_id)->save($data);
			
			
			if($res){
				$param['sign_status'] = $status;//签收
				if($withGoods['is_for_goods']==1) {
					$param['is_goods_signed'] = 1;
				}else {
					$param['is_invoice_signed'] = 1;
				}
				//修改订单的签收状态
				D('order')->where("order_id={$withGoods['order_id']}")->save($param);
				$this->releaseManAndCar($withGoods['delivery_id']);
				//生成评价记录，并通知用户
				$this->createEvaluate($withGoods,$user['user_id'],$user['user_name'],$status);
		
				$rs["errcode"] = 0;
				$this->ajaxReturn($rs);
			}else {			
				$rs["errcode"] =1;
				$rs["errmsg"] ="签收失败：".D('deliverywithgoods')->getDbError();
				$this->ajaxReturn($rs);
			}
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	/**
	 * 送货单 - 拒签
	 */
	function deliveryunsign() {
		$user = $this->validuser ();
		if ($user) {
			$withgoods_id = I("gid");
			$reason = I("rs");	
			
			$data['sign_status'] = 3;
			$data['sign_time'] = date ( "Y-m-d H:i:s" );
			$data['remark'] = $reason;
			//$data['delivery_status'] = 0;//重新配送
			//$data['delivery_time'] = null;
			$data['modify_time'] = date ( "Y-m-d H:i:s" );
			$res = D('deliverywithgoods')->where("withgoods_id=".$withgoods_id)->save($data);
			
			if($res){	
				$rs["errcode"] = 0;
				$withGoods = D('deliverywithgoods')->where("withgoods_id=".$withgoods_id)->find();
				if($withGoods){
					/*
					if($withGoods['is_for_goods']==1) {
						$param['is_goods_signed'] = 1;
					}else {
						$param['is_invoice_signed'] = 1;
					}
					//修改订单的签收状态
					D('order')->where("order_id={$withGoods['order_id']}")->save($param);
					*/
					$this->releaseManAndCar($withGoods['delivery_id']);
					$this->createEvaluate($withGoods,$user['user_id'],$user['user_name'],$data['sign_status']);

				}
				$this->ajaxReturn($rs);
			}else {			
				$rs["errcode"] =1;
				$rs["errmsg"] ="拒签失败：".D('deliverywithgoods')->getDbError();
				$this->ajaxReturn($rs);
			}
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	/**
	 * 订单评价
	 */
	function appraiseorder(){
		$user = $this->validuser ();
		if ($user) {
			$hospitalorder_id = I("hospitalorder_id"); 
			$param['appraise'] = I("rank");
			$param['appraise_comment'] = I("appraise_comment");
			$param['appraise_time'] = date ( "Y-m-d H:i:s" );
			$res = D('hospitalorder')->where("hospitalorder_id={$hospitalorder_id}")->save($param);
			if($res){
				$rs["errcode"] = 0;
				$this->ajaxReturn($rs);
			}else {
				$rs["errcode"] =1;
				$rs["errmsg"] ="评价失败：".D('hospitalorder')->getDbError();
				$this->ajaxReturn($rs);
			}
		} else {
			redirect ( U ( 'regist' ) );
		}
	}
	
	//释放司机和车
	function releaseManAndCar($deliveryid){
		$cntres = D('deliverywithgoods')->where("delivery_id=".$deliveryid." and sign_status<=1")->find();
		if($cntres){
		}else{
			$dilivwm = D('delivery')->where("delivery_id=".$deliveryid)->find();
			if($dilivwm){
				//$delstatus['status']='3';
				//D('delivery')->where("delivery_id=".$deliveryid)->save($delstatus)
				$data['isrun']=0;
				D('car')->where("car_id=".$dilivwm['car_id'])->save($data);
				D('deliveryman')->where("deliveryman_id=".$dilivwm['driver_deliveryman_id'])->save($data);
				
				$deliverydata['status']=5;
				$deliverydata['modify_time']=date ( "Y-m-d H:i:s" );
				D('delivery')->where("delivery_id=".$deliveryid)->save($deliverydata);

			}
		}
	}
	
	function createEvaluateTest(){
		//echo $tpl;
		$withGoods = D('deliverywithgoods')->where("withgoods_id=1059")->find();
		$this->createEvaluate($withGoods,'1','admin','3');
	}
	//签收、部分签收、拒签后，给客户发送消息。
	function createEvaluate($withGoods,$userid,$username,$status){
		$delivery = D('delivery')->where("delivery_id=".$withGoods['delivery_id'])->find();
		$driver = D('deliveryman')->where("deliveryman_id=".$delivery['driver_deliveryman_id'])->find();

		$order = D('order')->where("order_id=".$withGoods['order_id'])->find();
		$weichatuser = D('weichatuser')->where("weichatuser_id=".$order['weichatuser_id'])->find();
		if($withGoods['is_for_goods']==1) {
			$goodstype = '商品';
		}else {
			$goodstype = '发票';
		}
		$evaluate['delivery_id']=$withGoods['delivery_id'];
		$evaluate['withgoods_id']=$withGoods['withgoods_id'];
		$evaluate['order_id']=$withGoods['order_id'];
		$evaluate['level']=0;
		$evaluate['deliveryman_id']=$userid;
		$evaluate['deliveryman']=$username;
		$evaluate['open_id']=$weichatuser['open_id'];
		$evaluate['weichatuser_id']=$weichatuser['weichatuser_id'];
		$evaluate['weichatuser_name']=$weichatuser['name'];
		$evaluate['create_time']= date ( "Y-m-d H:i:s" );
		$evaluate['is_for_goods']= $goodstype;
		$evaluate['sign_time']= date ( "Y-m-d H:i:s" );
		
		//echo json_encode($evaluate);
		$res = D('evaluate')->where("withgoods_id=".$withGoods['withgoods_id'])->delete();
		$res = D('evaluate')->add($evaluate);
				 
		if($status=='2'){
			$oper ='签收';
		}elseif($status=='3'){
			$oper ='被拒签';
		}elseif($status=='4'){
			$oper ='部分签收';
		}
		
		//$token = 'gh_f65f6e4b3478';// get_token ();
		//$tpl = "KraI_HWm5Vhep0t4X-NbMsY_FZkIFF_aN59Le09a8oE";
		$config = getAddonConfig("Hospital");
		$tpl = $config["template.delivery.notify"];
		$token = $config["template.token"];

		$url = U ( '/addon/Hospital/Evaluate/evaluate/wid/' . $withGoods['withgoods_id'] .'/token/'.$token);

		$res = array();
		$openid = $weichatuser['open_id'];
		error_log ($openid);
		
		$content ['first'] = "您好，您的货物已".$oper."，送货单号".$withGoods['delivery_id']."。";
		$content ['keyword1'] = $withGoods['order_id'];//订单编号
		$content ['keyword2'] = $weichatuser['name'];//收货人
		$content ['keyword3'] = $order['hospital_name'].$order['office_name'];//收获地址
		$content ['keyword4'] = $driver['name'];//送货人员
		$content ['keyword5'] = $driver['mobile'];//送货电话
		$content ['remark'] = "如有疑问请联系送货人员，点击“详情”评价。";
		//echo json_encode($content);
		$res = $this->_sendTemplateMsg ( $openid, $content, $tpl, $url, $token );
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
		$res = post_data ( $url, json_encode($param) );
		return $res;
	}
}
