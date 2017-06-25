<?php

namespace Api\Controller;

use Think\Controller;

class ApiController extends Controller {
	protected function success($data, $msg = '') {
		! empty ( $msg ) || $msg = "操作成功";
		$rs ['err_code'] = 0;
		$rs ['err_msg'] = $msg;
		$rs ['data'] = $data;
		echo (json_encode ( $rs ));
		exit ();
	}
	protected function error($msg = '', $err_code = -1) {
		! empty ( $msg ) || $msg = "操作失败";
		$rs ['err_code'] = $err_code;
		$rs ['err_msg'] = $msg;
		echo (json_encode ( $rs ));
		exit ();
	}
	
	protected function getParam($assoc=FALSE){
		$param = $GLOBALS ['HTTP_RAW_POST_DATA'];
		return json_decode ( $param, $assoc);
	}
}