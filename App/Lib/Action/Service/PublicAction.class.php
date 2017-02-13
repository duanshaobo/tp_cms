<?php
class PublicAction extends Action{
	//判断用户是否登录
	public function _initialize(){
		if(!isset($_SESSION['service_id'])){
			$this->redirect('Login/index');
		}
		$config=M('cms_config')->find(1);
		$this->assign('config',$config);
	}
}