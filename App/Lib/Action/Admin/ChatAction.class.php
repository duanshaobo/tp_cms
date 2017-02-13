<?php 
/*
	聊天记录管理
*/
class ChatAction extends PublicAction{
		/*public function _initialize(){
			parent::_initialize();
		}*/
		
		/*
			聊天记录
		*/
		public function index(){
			import("@.ORG.Page");
			$db=M('plugin_chat');
			//$map=array('type'=>1);
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['f_user']=M('wechat_user')->where(array('id'=>$val['f_uid']))->field('id,nickname,name')->find();
				$list[$key]['t_user']=M('wechat_user')->where(array('id'=>$val['t_uid']))->field('id,nickname,name')->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();
		}
		

		
		
}