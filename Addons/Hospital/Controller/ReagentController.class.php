<?php

namespace Addons\Hospital\Controller;
use Addons\Hospital\Model\WeichatuserModel;

class ReagentController extends BaseController {
	
	/**
	 * 商品信息首页
	 */
	function goods() {
		$user = $this->validuser ();
		//if($user['type'] != WeichatuserModel::USER_TYPE_AGENT)$this->error ('该页面您无权限查看.');
		if(I('path.5') == 'list' || I('path.5') == 'list.html'){
			$page = I ( "page" ) ? I ( "page" ) : 1;
			$goods_id = I ( "goods_id" );
			$maps = array();
			if(I('name')){
				$map['name']  = array('like','%'.I('name').'%');
				$map['goods_no']  = array('like','%'.I('name').'%');
				$map['_logic'] = 'or';
				$maps['_complex'] = $map;
			}
			$deviceList = D ( 'goodsmeta' )->where($maps)->page ( $page, $pagesize )->select ();
			$count = D ( 'goodsmeta' )->where($maps)->count ();
			$data ['devicelist'] = $deviceList;
			$data ['page'] = $page;
			$data ['pagesize'] = I("pagesize")?(int)I( "pagesize"):10;
			$data ['totalpage'] = $count / $pageSize;
			$this->success ( $data, "", true );
		}
		else{
			$this->display ( "Hospital/goods" );
		}
	}
	
	/**
	 * 商品详情首页
	 */
	function goodsdetail() {
		$user = $this->validuser ();
		//if($user['type'] != WeichatuserModel::USER_TYPE_AGENT)$this->error ('该页面您无权限查看.');
		//echo I('goods_id');
		//exit;
		if(I('goods_id')){
			$goods_id = I ( "goods_id" );
			if(D ( 'goodsmeta' )->where ('goods_id='.$goods_id)->count () == 0)$this->error ('该商品信息不存在.');
			$data['goods'] = D ( 'goodsmeta' )->where ('goods_id='.$goods_id)->find ();
			$data['img'] = array();
			if(D('goodsimgs')->where(array('goods_no'=>(string)$data['goods']['goods_no']))->count() > 0){
				$data['img'] = D ('goodsimgs')->where ('goods_no='.$data['goods']['goods_no'])->find();
			}
			$this->assign('goods',$data['goods']);
			$this->assign('img',$data['img']);
			$this->display ( "Hospital/goodsdetail" );
		}
		else $this->error ('缺少商品标识.');
	}
	
	
	/**
	 * 试剂查询首页
	 */
	function listreagent() {
		$user = $this->validuser ();
		if ($user) {
			if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
				$page = I ( "page" ) ? I ( "page" ) : 1;
				$pagesize = I ( "pagesize" ) ? I ( "pagesize" ) : 10;
				$deviceList = D ( 'ReagentDevice' )->page ( $page, $pagesize )->select ();
				$count = D ( 'ReagentDevice' )->count ();
				if (! $_POST) {
					$this->assign ( "page", $page );
					$this->assign ( "pagesize", $pageSize );
					$this->assign ( "totalpage", $count / $pageSize );
					$this->assign ( "devicelist", $deviceList );
					$this->display ( "Reagent/list" );
				} else {
					$data ['devicelist'] = $deviceList;
					$data ['page'] = $page;
					$data ['pagesize'] = $pageSize;
					$data ['totalpage'] = $count / $pageSize;
					$this->success ( $data, "", true );
				}
			} else {
				$this->display ( "Hospital/show" );
			}
		} else {
			redirect ( addons_url ( "Hospital://Hospital/regist" ) );
		}
	}
	
	/**
	 * 试剂查询首页
	 */
	function listmodels() {
		$page = I ( "page" ) ? I ( "page" ) : 1;
		$pagesize = I ( "pagesize" ) ? I ( "pagesize" ) : 10;
		$device_id = I ( "device_id" );
		// $param
		$where = "device_id=" . $device_id;
		$modelList = D ( 'ReagentModel' )->where ( $where )->page ( $page, $pagesize )->select ();
		$count = D ( 'ReagentModel' )->where ( $where )->count ();
		$reagent = D ( 'ReagentDevice' )->where ( $where )->find ();
		if (! $_POST) {
			$this->assign ( "page", $page );
			$this->assign ( "pagesize", $pageSize );
			$this->assign ( "totalpage", $count / $pageSize );
			$this->assign ( "modellist", $modelList );
			$this->assign ( "device_id", $device_id );
			$this->assign ( "device_name", $reagent ["device_name"] );
			$this->display ( "Reagent/model_list" );
		} else {
			$data ['modellist'] = $modelList;
			$data ['page'] = $page;
			$data ['pagesize'] = $pageSize;
			$data ['totalpage'] = $count / $pageSize;
			$data ['device_id'] = $device_id;
			$data ['device_name'] = $reagent ["device_name"];
			$this->success ( $data, "", true );
		}
	}
	
	/**
	 * 试剂明细信息
	 */
	function listreagentdetail() {
		$user = $this->validuser ();
		if ($user) {
			if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
				// 厂家
				$page = I ( "page" ) ? I ( "page" ) : 1;
				$pagesize = I ( "pagesize" ) ? I ( "pagesize" ) : 10;
				$device_id = I ( "device_id" );
				$model_id = I ( "model_id" );
				$reagentlist = D ( 'reagent' )->where ( "device=" . $device_id . " and model=" . $model_id )->page ( $page, $pagesize )->select ();
				error_log ( count ( $reagentlist ) );
				$count = D ( 'reagent' )->where ( "device=" . $device_id . " and model=" . $model_id )->count ();
				
				$device = D ( 'ReagentDevice' )->where ( "device_id=" . $device_id )->find ();
				$model = D ( 'ReagentModel' )->where ( "model_id=" . $model_id )->find ();
				if (! $_POST) {
					$this->assign ( "page", $page );
					$this->assign ( "pagesize", $pageSize );
					$this->assign ( "totalpage", $count / $pageSize );
					$this->assign ( "reagentlist", $reagentlist );
					$this->assign ( "device_id", $device_id );
					$this->assign ( "device_name", $device['device_name'] );
					$this->assign ( "model_id", $model_id );
					$this->assign ( "model_name", $model['model_name'] );
					$this->display ( "Reagent/reagent_list" );
				} else {
					$data ['reagentlist'] = $reagentlist;
					$data ['page'] = $page;
					$data ['pagesize'] = $pageSize;
					$data ['device_id'] = $device_id;
					$data ['device_name'] = $device['device_name'];
					$data ['model_id'] = $model_id;
					$data ['model_name'] = $model['model_name'];
					$data ['totalpage'] = $count / $pageSize;
					$this->success ( $data, "", true );
				}
			} else {
				$this->display ( "Hospital/show" );
			}
		} else {
			redirect ( addons_url ( "Hospital://Hospital/regist" ) );
		}
	}
	
	/**
	 * 试剂明细信息详情
	 */
	function listreagentdetailitem() {
		$user = $this->validuser ();
		if ($user) {
			if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
				// 厂家
				$reagent_id = I ( "id" );
				$reagent = D ( 'reagent' )->where ( "reagent_id=" . $reagent_id )->find ();
				
				$this->assign ( "data", $reagent );
				$this->display ( "Reagent/reagent_detail" );
			} else {
				$this->display ( "Hospital/show" );
			}
		} else {
			redirect ( addons_url ( "Hospital://Hospital/regist" ) );
		}
	}
}