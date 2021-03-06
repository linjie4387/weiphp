<?php

namespace Addons\Hospital\Controller;

use Addons\Hospital\Model\DatadictModel;
use Addons\Hospital\Model\HospitalorderModel;
use Addons\Hospital\Model\WeichatuserModel;
use Addons\Hospital\Model\DeliveryModel;
use Addons\Hospital\Model\EvaluateModel;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
// use Home\Controller\AddonsController;
class EvaluateController extends BaseController {
	
	public function evaluate(){
		$user = $this->validuser ();
		
		//if ($user) {
			$withgoods_id = I("wid");
			$token = I("token");
			$evaluate = D('evaluate')->where("withgoods_id=".$withgoods_id)->find();
			$delivery = D('delivery')->where("delivery_id=".$evaluate['delivery_id'])->find();
			$evaluate['url'] = U ( '/addon/Hospital/Evaluate/editevaluate/wid/'.$withgoods_id.'/token/'.$token);
			$this->evaluate = $evaluate;
			$this->delivery = $delivery;
			
			$this->display ( "Evaluate/evaluate" );
		//} else {
		//	redirect ( "/index.php?s=/addon/Hospital/Hospital/regist");
		//}
	}
	
	public function editevaluate(){
		//$user = $this->validuser ();
		//if ($user) {
			$withgoods_id = $_POST['withgoods_id'];
			$token = I("token");
	
			$data['level'] = $_POST['level'];
			$data['remark'] = $_POST['remark'];	
			$data['sign_time'] = date ( "Y-m-d H:i:s" );
			$rs = D('evaluate')->where("withgoods_id=".$withgoods_id)->save($data);
			$url = U ( '/addon/Hospital/Evaluate/evaluate/wid/'.$withgoods_id.'/token/'.$token);
			redirect($url );
		//} else {
		//	redirect ( "/index.php?s=/addon/Hospital/Hospital/regist");
		//}
	}
	//http://wqlmk.chens.mobi/index.php?s=/addon/Hospital/Evaluate/evaluatelist/oid/497.html
	public function evaluatelist(){
			$order_id = I("oid");
			//������
			$evaluated = D('evaluate')->where("ifnull(level,0)>0 and order_id=".$order_id)->select();
			//$evaluated = D('evaluate')->where("ifnull(level,0)>0")->select();
			//δ����
			$unevaluate = D('evaluate')->where("ifnull(level,0)<=0 and order_id=".$order_id)->select();
			//$unevaluate = D('evaluate')->where("ifnull(level,0)<=0")->select();
			
			$this->assign('evaluated',$evaluated);
			$this->assign('unevaluate',$unevaluate);

			$this->display ( "Evaluate/evallist" );
	}
	public function unevaluatelist(){
		$user = $this->validuser ();
		echo get_openid ();
	}
	public function listevaluate(){
		$user = $this->validuser ();
		echo get_openid ();
	}
}
