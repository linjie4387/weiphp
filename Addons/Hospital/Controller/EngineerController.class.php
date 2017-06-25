<?php
namespace Addons\Hospital\Controller;
use Home\Controller\AddonsController;
use Addons\Hospital\Model\WeichatuserModel;

class EngineerController extends AddonsController {
	public function regist(){
		$openid = get_openid ();
		$param ['open_id'] = $openid;
		$param ['is_valid'] = 1;
		$param['type'] = WeichatuserModel::USER_TYPE_DEPARTMENT;
		$user = D ( 'weichatuser' )->where ( $param )->find ();
		if (! $user) {
			$this->assign ( "user_type", WeichatuserModel::USER_TYPE_DEPARTMENT );
			$this->display ( "Engineer/signup" );
		}else {
			$this->assign ( "title", "用户已注册" );
			$this->assign ( "content", "您已注册，欢迎使用会员服务。" );
			$this->display ( "Hospital/show" );
		}
	}
}
