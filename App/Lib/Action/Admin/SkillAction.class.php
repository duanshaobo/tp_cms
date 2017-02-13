<?php
/*
	专家擅长领域操作
*/
class SkillAction extends PublicAction{

	public function _initialize(){
		parent::_initialize();
		import("@.ORG.Page");
		//顶级分类
		$plist=M('skill')->where(array('pid'=>0))->select();
		$this->assign('plist',$plist);
		
	}
	/*
		专业技能列表
	*/
	public function index(){
		
		$map=array();
		$db=M('skill');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$_list=$db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$list=order($_list);
		
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}

	
	
	public function edit(){
		$id=I('get.id');
		$info=M('skill')->find($id);
		$this->assign('info',$info);
		if($data=$this->_post()){
			$w['id']=I('get.id');
			M('skill')->where($w)->save($data);
			$this->redirect('index');
		}else{
			$this->display();		
		}
	}
	/*
		删除微信用户信息
	*/
	public function del(){
		$user_id=I('get.id');
		if(M('skill')->delete($user_id)){
/*			M('coupon')->where(array('user_id'=>$user_id))->delete();
			M('user_relation')->where(array('user_id'=>$user_id))->delete();
			M('score_log')->where(array('user_id'=>$user_id))->delete();
			M('order_info')->where(array('user_id'=>$user_id))->delete();*/
			$this->success('操作成功！');
		}
	}
	public function add(){
		$db=M('skill');
		if($data=$this->_post()){
			$i=$db->where(array('title'=>$data['title']))->find();
			if(!empty($i)){
				$this->error('领域名称已经存在！');
			}else{
				$db->data($data)->add();
				$this->redirect('index');
			}	
		}else{
			$this->display();
		}
		
	}
	
		
		
		
	
}