
<?php
/*
	 在线考试
*/
class ExamAction extends BaseAction{
	public $jump;
	public function _initialize(){
		parent::_initialize();
		if(!is_weixin()) {
		   //exit('此功能只能在微信浏览器中使用');
		}
		if(is_weixin()){
			//微信js接口
			import("@.ORG.Wxjssdk");
			$wx_config=M('wechat_config')->find(1);
			$jsobj=new Wxjssdk($wx_config['appid'],$wx_config['appsecret']);
			$jssign=$jsobj->getSignPackage();
			$this->assign('jssign',$jssign);
		}
		//微信js接口
		//底部导航
		/*$nav=M('navlink')->field('id,title,url')->where(array('fup'=>0,'cid'=>1))->order('id asc')->select();
		
		foreach($nav as $key=>$val){
			$nav[$key]['child']=M('navlink')->field('id,title,url')->where(array('fup'=>$val['id'],'cid'=>1))->order('list asc')->select();
		}
		$this->assign('nav',$nav);*/
		//当前地址
		$curr_url=get_curr_url();
		$this->jump=base64_encode($curr_url);
		
		//需要登录的操作，无登录信息，跳转到登录页
		if(!$this->user_id&&in_array(ACTION_NAME,array('cart','cart2'))){
			$this->redirect('Member/login',array('jump'=>$this->jump));
		}
		
		$domian=I('server.HTTP_HOST');
		$tid=I('get.tid','','intval');
		if($this->user_id&&$this->user_id!=$tid){
			//发送访客提醒
			visit_notice($this->user_id,$tid);
		}
	}
	
	/*
		case 
	*/
	public function case_load(){
		
		$db=M('exam_topic');
		$class=2;				//1基础训练	2技能题库
		$level=2;				//2级OR3级
		
		$map=array('level'=>$level,'class'=>$class);
		$map['id']=array('between','6646,7451');				//2级
		$list=$db->where($map)->select();						//->group('content')
		foreach($list as $key=>$val){
			/*$data['level']=3;
			$data['content']=$val['content'];
			$id=M('exam_case')->add($data);
			echo $id.'==';*/
			$case=M('exam_case')->where(array('content'=>$val['content']))->find();
			$db->where(array('id'=>$val['id']))->save(array('c_id'=>$case['id']));
		}
	}
	
	/*
		考试首页
	*/
	public function index(){
		$pager=M('exam_pager')->group('year')->select();	//
		foreach($pager as $val){
			if($val['level']==2){
				$_pager[2][]=$val;
			}elseif($val['level']==3){
				$_pager[3][]=$val;
			}
		}
		$this->assign('pager',$pager);
		$this->display();	
	}
	
	/*
		技能题库-列表
	*/
	public function case_list(){
		import('@.ORG.Page');
		header('content-type:text/html;charset=utf-8');
		$db=M('exam_case');
		$class=I('get.class',2);				//1基础训练	2技能题库
		$level=I('get.level',2);				//2级OR3级
		$map['level']=$level;
		$map['class']=$class;
		$map['id']=array('not in','1,2,3,4,5,6,7,8,98');
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		$this->assign('count',$count);
		
		
		$list=$db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();

		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		技能题库-详情
	*/
	public function case_show(){
		$db=M('exam_topic');
		$id=I('get.id');				//案例id
		$case=M('exam_case')->where(array('id'=>$id))->find();
		$this->assign('case',$case);
		
		$list=$db->where(array('c_id'=>$id))->select();
		$this->assign('list',$list);
		$this->display();
	}
	/*
		真题列表
	*/
	public function lists(){
		import('@.ORG.Page');
		$db=M('exam_pager');
		$map=array();
		if($level=I('get.level')){
			$map['level']=$level;	
		}
		
		if($year=I('get.year')){
			$map['year']=$year;
		}
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		
		$list=$db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		在线练习
	*/
	public function exercise(){
		$db=M('exam_topic');
		$class=I('get.class');				//1基础训练	2技能题库
		$level=I('get.level');				//2级OR3级
		if($class==2){
			$title='技能题库';
		}elseif($class==1){
			$title='基础训练';
		}
		$list=$db->where(array('level'=>$level,'class'=>$class))->order('rand()')->limit('25')->select();
		$this->assign('title',$title);
		
		$this->assign('list',$list);
		$this->assign('pager',$pager);
		
		if(empty($list)){
			$this->error('暂无先关内容');
		}else{
			$this->display();
		}
		
		
	}
	
	/*
		在线考试
	*/
	public function train(){
		$db=M('exam_topic');
		$class=I('get.class');				//1基础训练	2技能题库
		$level=I('get.level');				//2级OR3级
		if($class==2){
			$title='技能题库';
			$list=$db->where(array('level'=>$level,'class'=>2))->order('id asc')->select();
		}elseif($class==1){
			$title='基础训练';
			$list=$db->where(array('level'=>$level,'class'=>2))->order('id asc')->select();
		}else{
			$id=I('get.id');
			$pager=M('exam_pager')->where(array('id'=>$id))->find();
			$title=$pager['title'];
			$list=$db->where(array('p_id'=>$id))->limit(100)->order('id asc')->select();
		}
		$this->assign('title',$title);
		
		$this->assign('list',$list);
		$this->assign('pager',$pager);
		
		$this->display();
	}
	
	/*
		考试记录
	*/
	public function exam_list(){
		import('@.ORG.Page');
		$db=M('exam_log');
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		
		$list=$db->where($map)->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$pager=M('exam_pager')->where("id={$val['p_id']}")->find();
			$list[$key]['pager']=$pager;
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		在线得分
	*/
	
	public function ajax_exam(){
		$db=M('exam_log');
		if($arr=$this->_post()){
			$total_score=M('exam_topic')->where("p_id={$arr['p_id']}")->count();
			$data['uid']=$this->user_id;
			$data['p_id']=$arr['p_id'];				//试卷id
			$data['score']=$arr['score'];			//得分
			$data['total_score']=$total_score;
			$data['posttime']=time();				//考试时间
			$id=$db->add($data);
			echo $id;
		}
	}
}