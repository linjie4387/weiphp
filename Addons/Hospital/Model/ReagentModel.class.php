<?php
namespace Addons\Hospital\Model;
use Think\Model;

/**
 * 试剂表模型
 */
class ReagentModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
}
