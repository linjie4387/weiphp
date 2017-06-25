<?php
namespace Addons\Hospital\Model;
use Think\Model;

/**
 * 医院表模型
 */
class HospitalModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
}
