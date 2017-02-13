<?php
class IndexAction extends Action{

	public function _initialize(){
		
	}
	public function _empty(){
		$this->redirect('Index/index');
	}
	//活动首页
	public function index(){
		
		$this->display();
	}
	public function reg(){
		
		$this->display();
	}
	public function login(){
		
		$this->display();
	}
	public function reg_ent(){
		
		$this->display();
	}
	public function product(){
		
		$this->display();
	}
	public function cart(){
		
		$this->display();
	}
}