<?php
namespace Addons\Hospital\Model;
use Think\Model;

/**
 * 仪器类型表模型
 */
class DeviceTypeModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
}
