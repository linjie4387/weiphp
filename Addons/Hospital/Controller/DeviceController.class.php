<?php

namespace Addons\Hospital\Controller;
use Addons\Hospital\Model\WeichatuserModel;
class DeviceController extends BaseController {
	/**
	 * 仪器查询页初始化
	 */
	function devicepage() {
		$user = $this->validuser ();
		if ($user) {
			if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
				// 厂家
				$supplierlist = D ( "supplier" )->select ();
				// 型号
				foreach ( $supplierlist as &$supplier ) {
					$supplierId = $supplier ['supplier_id'];
					$p ['supplier_id'] = $supplierId;
					$modellist = D ( "model" )->where ( $p )->select ();
					$suppler ['model_list'] = $modellist;
				}
				$this->assign ( "supplier_list", $supplierlist );
				$this->display ( "Device/search" );
			} else {
				$this->display ( "Device/show" );
			}
		} else {
			redirect ( addons_url ( "Hospital://Engineer/regist" ) );
		}
	}
	/**
	 * 查询医院
	 */
	function listhospital() {
		$key = I ( "key" );
		if (empty ( $key )) {
			$this->error ( "请输入医院名称！" );
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
	
	function list_device(){
		$type = I('type');
		$this->assign("type", $type?$type:1);
		$code = I ( 'code' );
		if ($code) {
			$param ["code"] = $code;
			$this->assign("code", $code);
		}
		$qrCode = I ( "qrcode" );
		if ($qrCode) {
			$param ["qrcode"] = $qrCode;
			$this->assign("qrcode", $qrCode);
		}
		$idCode = I ( "id_code" );
		if ($serialCode) {
			$param ["id_code"] = $idCode;
			$this->assign("id_code", $idCode);
		}
		$serialCode = I ( "serial_code" );
		if ($serialCode) {
			$param ["serial_code"] = array (
					'like',
					'%' . $serialCode . '%'
			);
			$this->assign("serial_code", $serialCode);
		}
		$supplierId = I ( "supplier_id" );
		if ($supplierId) {
			$param ["supplierId"] = $supplierId;
			$this->assign("supplierId", $supplierId);
		}
		$modelId = I ( "model_id" );
		if ($modelId) {
			$param ["model_id"] = $modelId;
			$this->assign("model_id", $modelId);
		}
		$hospitalId = I ( "hospital_id" );
		if ($hospitalId) {
			$param ["hospital_id"] = $hospitalId;
			$this->assign("hospital_id", $hospitalId);
			
			$p ['hospital_id'] = $hospitalId;
			$officelist = D ( 'office' )->where ( $p )->select ();
			error_log("officecount:".$hospitalId.":".count($officelist));
			$this->assign("officelist", $officelist);
		}
		$officeId = I ( "office_id" );
		if ($officeId) {
			$param ["office_id"] = $officeId;
			$this->assign("office_id", $officeId);
		}
		$keyHos = I ( "key_hos" );
		if($keyHos){
			$map ['name'] = array (
					'like',
					"%$keyHos%"
			);
			$hoslist = D ( 'hospital' )->where ( $map )->select ();
			error_log("hoscount:".$keyHos.":".count($hoslist));
			$this->assign("hospitallist", $hoslist);
			$this->assign("key_hos", $keyHos);
		}
		$devicelist = D ( "device" )->where ( $param )->select ();
		foreach ($devicelist as &$device) {
			$model = D('Model')->where("model_id=".$device['model_id'])->find();
			$device['model_name'] = $model['name'];
		}
		$this->assign("devicelist", $devicelist);
		$this->display ( "Device/search" );
	}
	/**
	 * 仪器查询
	 */
	function ajax_list_device() {
		$code = I ( 'code' );
		if ($code) {
			$param ["code"] = $code;
		}
		$qrCode = I ( "qrcode" );
		if ($qrCode) {
			$param ["qrcode"] = $qrCode;
		}
		$idCode = I ( "id_code" );
		if ($serialCode) {
			$param ["id_code"] = $idCode;
		}
		$serialCode = I ( "serial_code" );
		if ($serialCode) {
			$param ["serial_code"] = array (
					'like',
					'%' . $serialCode . '%' 
			);
		}
		$supplierId = I ( "supplier_id" );
		if ($supplierId) {
			$param ["supplierId"] = $supplierId;
		}
		$modelId = I ( "model_id" );
		if ($modelId) {
			$param ["model_id"] = $modelId;
		}
		$hospitalId = I ( "hospital_id" );
		if ($hospitalId) {
			$param ["hospital_id"] = $hospitalId;
		}
		$officeId = I ( "office_id" );
		if ($officeId) {
			$param ["office_id"] = $officeId;
		}
		$devicelist = D ( "device" )->where ( $param )->select ();
		foreach ($devicelist as &$device) {
			$model = D('Model')->where("model_id=".$device['model_id'])->find();
			$device['model_name'] = $model['name']; 
		}
		$this->success ( $devicelist, "", true );
	}
	
	/**
	 * 查询设备详细信息
	 */
	function device_detail() {
		$deviceId = I ( 'device_id' );
		// 仪器信息|服务信息
		if (! empty ( $deviceId )) {
			$map ['device_id'] = $deviceId;
			$device = D ( 'device' )->where ( $map )->find ();
		} else {
			$code = I ( "code" );
			if (empty ( $code )) {
				$this->error ( "系统异常：缺少条码或二维码参数！" );
			} else {
				$codes = explode ( ",", $code );
				if (count ( $codes ) > 1) {
					$code = $codes [1];
				}
				$param ['qrcode'] = $code;
				$param ['serial_code'] = $code;
				$param ['_logic'] = 'OR';
				$device = D ( 'device' )->where ( $param )->find ();
			}
		}
		if (empty ( $device )) {
			$this->assign ( "title", "设备不存在" );
			$this->assign ( "content", "您查找的设备不存在！" );
			$this->display ( "Device/show" );
			exit ();
		}
		// 医院信息
		$p_hospital ["hospital_id"] = $device ['hospital_id'];
		$hospital = D ( 'hospital' )->where ( $p_hospital )->find ();
		$p_district ["district_id"] = $hospital ['district_id'];
		//科室
		$office = D('office')->where("office_id=".$device['office_id'])->find();
		$hospital['office_name'] = $office['name'];
		$hospital['contact_name'] = $office['contact_name'];
		$hospital['contact_phone'] = $office['contact_phone'];
		//地区
		$district = D ( 'district' )->where ( $p_district )->find ();
		$hospital ['district_name'] = $district ['name'];
		//供应商
		$supplier = D ( "supplier" )->where ( "supplier_id=" . $device ['supplier_id'] )->find ();
		$device ['supplier_name'] = $supplier ["code"];
		//型号
		$model = D ( "model" )->where ( "model_id=" . $device ['model_id'] )->find ();
		$device ['model_name'] = $model ['name'];
		
		
		$this->assign ( "device", $device );
		$this->assign ( "hospital", $hospital );
		$this->display ( "Device/equipment" );
	}
	/**
	 * 仪器类型查询
	 */
	function listdevicetype() {
		$user = $this->validuser ();
		if ($user) {
			if ($user ['status'] == WeichatuserModel::USER_STATUS_NORMAL) {
				$page = I ( "page" ) ? I ( "page" ) : 1;
				$pagesize = I ( "pagesize" ) ? I ( "pagesize" ) : 10;
				$deviceTypeList = D ( "DeviceType" )->page ( $page, $pagesize )->order("seq")->select ();
				$count = D ( "DeviceType" )->count ();
				if (! $_POST) {
					$this->assign ( "page", $page );
					$this->assign ( "pagesize", $pageSize );
					$this->assign ( "totalpage", $count / $pageSize );
					$this->assign ( "deviceTypeList", $deviceTypeList );
					$this->display ( "Device/type_list" );
				} else {
					$data ['deviceTypeList'] = $deviceTypeList;
					$data ['page'] = $page;
					$data ['pagesize'] = $pageSize;
					$data ['totalpage'] = $count / $pageSize;
					$this->success ( $data, "", true );
				}
			} else {
				$this->display ( "Device/show" );
			}
		} else {
			redirect ( addons_url ( "Hospital://Hospital/regist" ) );
		}
	}
	/**
	 * 仪器类型明细
	 */
	function devicetypedetail() {
		$typeId = I ( "typeid" );
		$device = D ( "DeviceType" )->where ( "type_id=" . $typeId )->find ();
		$this->assign ( "type_id", $typeId );
		$this->assign ( "type_name", $device ['type_name'] );
		$this->assign ( "description", $device ['description'] );
		$this->assign ( "pic_url", $device ['pic_url'] );
		$this->display ( "Device/type_detail" );
	}
}