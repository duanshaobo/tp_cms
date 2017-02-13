<?php
//系统设置
class WxusersAction extends PublicAction{
	
	private $wechatid;
	private $pubwechat;	//公众号信息

	public function _initialize(){
		parent::_initialize();
		import("@.ORG.Page");
		$this->wechatid=I('get.wechatid');
		$this->pubwechat=M('wechat_config')->find(1);	//公众号信息
		//会员等级
		$_user_level=M('user_level')->select();
		foreach($_user_level as $val){
			$user_level[$val['id']]=$val;
		}
		$this->assign('user_level',$user_level);
		
	}
	
	
	/*
		待审核专家
	*/
	public function reg_list(){
		$db=M('wechat_user');
		$map=array('status'=>1);
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		 专家审核
	*/
	public function status(){
		$db=M('wechat_user');
		$id=I('get.id');
		$type=I('get.type');
		if($type=='pass'){				//通过
			$db->where(array('id'=>$id))->save(array('status'=>2,'role_id'=>2));
		}elseif($type=='refuse'){		//拒绝
			$db->where(array('id'=>$id))->save(array('status'=>0,'role_id'=>1));
		}
		$this->success('操作成功');
	}
	
	public function index(){
		$db=M('wechat_user');
		//$map=array('nickname'=>array('neq',''));
		$map['role_id']=array('in','1,2');					//普通会员,2 分销商
		
		if($role_id=I('get.role_id')){
			$map['role_id']=$role_id;
		}
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('id','nickname','mobile','username'))){
			if(!empty($so_key)&&!empty($so_val)){
				if(is_numeric($so_val)){
					$map[$so_key]=$so_val;
				}else{
					$map[$so_key]=array('like','%'.$so_val.'%');
				}
				
			}
		}
		
		if($begin_time>0){
			$map['posttime']=array('egt',$begin_time);
		}
		
		if($end_time>0){
			$map['posttime']=array('elt',$end_time);
		}
		
		

		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		//$groups=F('wx_groups');
		foreach($list as $key=>$val){
			//$wxuser[$key]['redpack_amount']=$val['redpack_amount']/100;
			//$wxuser[$key]['group_name']=$groups[$val['group_id']]['name'];
			$where="p_1={$val['id']} or p_2={$val['id']} or p_3={$val['id']}";
			$list[$key]['son_count']=$db->where($where)->count();
			$list[$key]['order_total_fee']=M('order_info')->where(array('uid'=>$val['id'],'pay_status'=>1))->sum('total_fee');
			$list[$key]['skill']=skill_id2name($val['skill']);
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		if($role_id==2){
			$this->display('expert_list');
		}else{
			$this->display();
		}
		
	}

	public function edit(){
		$id=I('get.id');
		$p=I('post.p',1);			//页码
		
		$info=M('wechat_user')->find($id);
		$this->assign('info',$info);
		//用户列表
		//$map=array('id'=>array('neq',$id));
		//$user_list=M('wechat_user')->where($map)->field('id,p_1,p_2,p_3,nickname,name')->select();
		foreach($user_list as $key=>$val){
			if($val['id']==$info['p_1']){
				unset($user_list[$key]);
			}
		}
		$this->assign('user_list',$user_list);
		
		//资金明细
		$map=array('uid'=>$info['id']);
		$count = M('money_water')->where($map)->count();
		$Page = new Page($count,10);
		$money_list=M('money_water')->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($money_list as $key=>$val){
			if(!empty($val['order_id'])){
				$money_list[$key]['order']=M('order_info')->where(array('id'=>$val['order_id']))->find();
			}
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('money_list',$money_list);
		
		if($data=$this->_post()){
			$w['id']=I('get.id');
			M('wechat_user')->where($w)->save($data);
			$this->redirect('index',array('p'=>$p));
			//$this->success('保存成功',U('edit',array('id'=>$w['id'])));
		}else{
			$this->display();		
		}
		
		//dump($user_list);
	}
	/*
		删除微信用户信息
	*/
	public function del(){
		$user_id=I('get.id');
		if(M('wechat_user')->delete($user_id)){
			/*M('coupon')->where(array('user_id'=>$user_id))->delete();
			M('user_relation')->where(array('user_id'=>$user_id))->delete();
			M('score_log')->where(array('user_id'=>$user_id))->delete();
			M('order_info')->where(array('user_id'=>$user_id))->delete();*/
			$this->success('操作成功！');
			$this->redirect('index');
		}
	}
	public function add(){
		if(IS_POST){
			$data=$this->_post();
			$data['rtime']=time();
			$rs=M('wechat_user')->data($data)->add();
			if($rs){
				$this->redirect('index');
			}else{
				$this->error('用户名已存在！');
			}
			
		}
		$this->display();
	}
	//红包记录
	public function list_redpack(){
		import("@.ORG.Page");
		$db=M('redpack_record');
		$userinfo=M('wechat_user')->find(I('get.id'));

		$map=array('wechatid'=>$userinfo['wechatid']);
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['money']=$val['money']/100;
		}
		$show = $Page->show();
		
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	//获取用户最新微信资料
	public function get_wxinfo(){
		import("@.ORG.Wxhelper");
		$helper=new Wxhelper($this->pubwechat);
		$db=M('wechat_user');
		$uid=I('get.id');
		//查询用户信息
		$info=$db->find($uid);
		//获取用户微信资料
		$return=$helper->get_user_info($info['wechatid']);
		if($return['errcode']){
			echo "获取失败,错误信息：{errcode:{$return['errcode']},errmsg:{$return['errmsg']}}";die();
		}elseif(!empty($return['headimgurl'])){
			//下载微信头像
			import("@.ORG.Http");
			import('@.ORG.Image.ThinkImage');
			$headimg="./Data/upload/headimg/".$uid.'.jpg';
			if(!is_file($headimg)||filesize($headimg)==0){
				//下载图片
				Http::curlDownload($return['headimgurl'],$headimg);
				$return['headimgurl']=$headimg;
			}
			//保存用户最新微信资料
			$wxdata=array(
				'subscribe'=>$return['subscribe'],
				'nickname'=>$return['nickname'],
				'sex'=>$return['sex'],
				'city'=>$return['city'],
				'province'=>$return['province'],
				'headimgurl'=>$headimg,//$return['headimgurl'],
				'subscribe_time'=>$return['subscribe_time'],
			);
			$db->where(array('id'=>$uid))->save($wxdata);
			$href=U('index',array('p'=>I('get.p',1),'group_id'=>I('get.group_id')));
			echo "获取微信资料成功，请<a href='$href'>刷新</a>页面查看！";
		}else{
			echo "获取微信资料成功！";
		}
	}
	//拉取粉丝列表
	public function list_wxfans(){
		import('@.ORG.Wxhelper');
		$helper=new Wxhelper($this->pubwechat);
		$next_openid=I('post.next_openid');
		$list=$helper->get_wxfans($next_openid);
		$this->assign('list',$list);
		$this->display();
	}
	//粉丝统计分析
	public function fans_analyze(){
		import("@.ORG.Wxhelper");
		$helper=new Wxhelper($this->pubwechat);
		$list=$helper->get_wxfans();
		$this->assign('list',$list);
		$month=date('m',time());
		$day=date('d',time());
		$year=date('Y',time());
		$today=mktime(0,0,0,$month,$day,$year);
		$today_sub_num=M('wechat_user')->where(array('subscribe_time'=>array('gt',$today)))->count();
		$this->assign('today_sub_num',$today_sub_num);	//今日关注人数
		
		$today_unsub_num=M('wechat_user')->where(array('subscribe'=>0,'posttime'=>array('gt',$today)))->count();
		$this->assign('today_unsub_num',$today_unsub_num);	//今日取消关注人数
		
		$this->display();	
		/*$date_range=array('begin_date'=>date('Y-m-d',time()-3600*24*7),'end_date'=>date('Y-m-d',time()));
		$res=$helper->getusersummary(json_encode($date_range));
		echo "<pre>";
		print_r($res);*/
	}
	
	/*
		资金明细
	*/
	public function money_detail(){
		import("@.ORG.Page");
		$db=M('score_log');
		$user_id=I('get.user_id');
		$user_info=M('wechat_user')->find($user_id);
		$this->assign('user_info',$user_info);
		$map=array('user_id'=>$user_id);
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$show = $Page->show();
		$this->assign('page',$show);
		
		$list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$order=M('order_info')->find($val['order_id']);
			$buyer=M('wechat_user')->find($val['buyer_id']);
			$list[$key]['order_sn']=$order['order_sn'];
			$list[$key]['buyer_id']=$buyer['id'];
			$list[$key]['buyer_nickname']=$buyer['nickname'];
			$list[$key]['buyer_name']=$buyer['name'];
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	
	
		/*
			会员等级管理
		*/
		public function level(){
			import("@.ORG.Page");
			$db=M('user_level');
			$map='';
			$count = $db->where($map)->count();
			$Page = new Page($count,10);
			$show = $Page->show();
			$this->assign('show',$show);
			$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('list',$list);
			$this->display();    
		}
		/*
			新增会员等级
		*/
		public function level_add(){
			$db=M('user_level');
			if($arr=$this->_post()){
				$db->add($arr);
				$this->redirect('level');
			}
			$this->display();    
		}
		/*
			新增会员等级
		*/
		public function level_edit(){
			$db=M('user_level');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$db->where(array('id'=>$id))->save($arr);
				$this->redirect('level');
			}
			$this->display();    
		}
		/*
			删除会员等级
		*/
		public function level_del(){
			$id=I('get.id');
			M('user_level')->delete($id);
			$this->redirect('level');
		}
		
		/*
			升级分销商
		*/
		public function upgrade_resaler(){
			$db=M('wechat_user');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if(!empty($info)){
				$db->where(array('id'=>$id))->save(array('role_id'=>3));
				$this->success('操作成功',U('Wxusers/edit',array('id'=>$id)));
			}
		}
		
		public function _upgrade_resaler(){
			$db=M('wechat_user');
			$id=I('get.id');
			$info=$db->find($id);
			$this->assign('info',$info);
			if($arr=$this->_post()){
				$record=$db->where(array('username'=>$arr['username']))->find();
				if(!empty($record)){
					$this->error('账户名已经存在，请重新分配账户名');
				}else{
					$arr['password']=md5($arr['password']);
					$arr['role_id']=3;					//分销商
					
					//生成邀请码
					$invite_code=randStr();
					$is_exist=$db->where(array('invite_code'=>$invite_code))->find();
					while(!empty($is_exist)){
						$invite_code=randStr();
					}
					$arr['invite_code']=$invite_code;
					
					$db->where(array('id'=>$id))->save($arr);
					$this->redirect('upgrade_resaler',array('id'=>$id));
				}
				
			}
			$this->display();
		}
	/*
		修改密码
	*/
	public function pwd(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($arr=$this->_post()){
			$data['password']=md5($arr['password']);
			$db->where(array('id'=>$id))->save($data);
			$this->success('密码修改成功！');
		}else{
			$this->display();	
		}
		
	}
	
	/*
		用户管理【上下线用户】
	*/
	public function user_relation(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$this->assign('info',$info);
		//上级用户
		$parent=$db->where(array('id'=>array('in',array($info['p_1'],$info['p_2'],$info['p_3']))))->select();
		foreach($parent as $key=>$val){
			if($val['id']==$info['p_1']){
				$parent[$key]['relation']='上一级';
			}	
			if($val['id']==$info['p_2']){
				$parent[$key]['relation']='上二级';
			}	
			if($val['id']==$info['p_3']){
				$parent[$key]['relation']='上三级';
			}		
		}
		$this->assign('parent',$parent);
		
		
		//下级用户
		$map="p_1=$id or p_2=$id or p_3=$id";
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		
		$show = $Page->show();
		$this->assign('show',$show);
		
		$son_list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id asc')->select();
		foreach($son_list as $key=>$val){
			if($val['p_1']==$id){
				$son_list[$key]['relation']='下一级';
			}elseif($val['p_2']==$id){
				$son_list[$key]['relation']='下二级';
			}elseif($val['p_3']==$id){
				$son_list[$key]['relation']='下三级';
			}
		}
		$this->assign('son_list',$son_list);
		$this->display();
	}
	
	public function son_list(){
		$db=M('wechat_user');
		$id=I('get.id');
		//直接下级
		$map=array('parent_id'=>$id);
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		
		$show = $Page->show();
		$this->assign('show',$show);
		
		$son_list=$db->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
		$this->assign('son_list',$son_list);
		$this->display();
	}
	
}