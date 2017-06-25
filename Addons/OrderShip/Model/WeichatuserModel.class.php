<?php

namespace Addons\OrderShip\Model;
use Think\Model;

/**
 * 微信用户表模型
 */
class WeichatuserModel extends Model{
	protected $tablePrefix ="smpss_";
	protected $connection = 'DB_SMPSS';
	//微信用户类型-医院用户
	const USER_TYPE_HOSPITAL = 1;
	//微信用户类型-工程部用户
	const USER_TYPE_DEPARTMENT = 2;
	
	//微信用户状态-待审核
	const USER_STATUS_TO_AUDIT = 1;
	//微信用户状态-正常
	const USER_STATUS_NORMAL = 2;
	//微信用户状态-审核不通过
	const USER_STATUS_AUDIT_FAILED = 3;
	//微信用户状态-关闭权限
	const USER_STATUS_CLOSED = 4;
	
}
