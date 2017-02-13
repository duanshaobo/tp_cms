<?php
class UserAction extends PublicAction{
	//用户列表
	public function index(){
		import('@.ORG.Page');
		$count = D('UserRelation')->where()->count();
		$Page = new Page($count,10);	
		$pagestr = $Page->show();
		$this->assign('pagestr',$pagestr);
		$res=D('UserRelation')->field('password',true)->relation(true)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$res);
		$this->display();
	}
	/*
		服务商信息
	*/
	public function info(){
		$db=M('service');
		$info=$db->find(I('session.service_id'));
		$this->assign('info',$info);
		if($arr=$this->_post()){
			
			$db->where(array('id'=>I('session.service_id')))->save($arr);
			//echo $db->getlastsql();
			//dump($arr);die();
			$this->success('修改成功',U('info'));
		}else{
			$this->display();
		}
		
	}
	
	/*
		修改密码
	*/
	public function pwd(){
		$this->display();
	}
	public function pwd_update(){
		$db=M('service');
		if($this->_post()){
			$arr['password']=md5($arr['password']);
			$db->save($arr);
			$this->success('登录密码修改成功',U('pwd'));
		}
	}
	/*
		 服务区域
	*/
	public function area_list(){
		$db=M('service');
		$area_list=$db->where(array('id'=>I('session.service_id')))->getField('area_list');
		$area_list=array_filter(explode(',',$area_list));
		
		foreach($area_list as $key=>$val){
			$areas[]=M('region')->find($val);
		}
		
		
		foreach($areas as $key=>$val){
			if($val['region_type']==1){
				$pro[]=$val;
				unset($areas[$key]);
			}
			
		}
		
		foreach($areas as $key=>$val){
			if($val['region_type']==2){
				$city[]=$val;
				unset($areas[$key]);
			}
			
		}
		
		foreach($city as $key=>$val){
			foreach($areas as $k=>$v){
				if($v['parent_id']==$val['id']){
					$city[$key]['county'][]=$v;
				}
			}
			
		}
		
		
		foreach($pro as $key=>$val){
			foreach($city as $k=>$v){
				if($v['parent_id']==$val['id']){
					$pro[$key]['city'][]=$v;
				}
			}
			
		}
		$this->assign('areas',$pro);
		$this->display();
		//dump($pro);
	}
}