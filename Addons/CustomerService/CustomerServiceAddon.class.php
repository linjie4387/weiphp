<?php

namespace Addons\CustomerService;
use Common\Controller\Addon;

/**
 * 多客服系统插件
 * @author Thorb
 */

    class CustomerServiceAddon extends Addon{

        public $info = array(
            'name'=>'CustomerService',
            'title'=>'多客服系统',
            'description'=>'这是一个多客服系统',
            'status'=>1,
            'author'=>'Thorb',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/CustomerService/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/CustomerService/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }