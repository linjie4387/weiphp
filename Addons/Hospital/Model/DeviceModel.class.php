<?php
namespace Addons\Hospital\Model;
use Think\Model;

/**
 * 仪器表模型
 */
class DeviceModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
}
