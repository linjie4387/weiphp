<?php

namespace Addons\redpack;
use Common\Controller\Addon;

/**
 * 红包插件
 * @author sunbuyer
 */

    class redpackAddon extends Addon{

        public $info = array(
            'name'=>'redpack',
            'title'=>'红包',
            'description'=>'这是一个红包活动',
            'status'=>1,
            'author'=>'sunbuyer',
            'version'=>'0.1',
            'has_adminlist'=>1
        );

	public function install() {
		$install_sql = './Addons/redpack/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/redpack/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }