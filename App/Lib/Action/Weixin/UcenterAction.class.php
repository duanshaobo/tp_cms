<?php
/*
	用户中心控制器-【微信商城】
*/
class UcenterAction extends BaseAction{
	public function _initialize(){
		parent::_initialize();
		import('@.ORG.Page');
		import("@.ORG.Http");
        import('@.ORG.Image.ThinkImage');
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
		   //exit('此功能只能在微信浏览器中使用');
		}else{
			$headimg="./Data/upload/headimg/".$_SESSION['user_id'].'.jpg';
			$icon="./Data/upload/headimg/thumb_".$_SESSION['user_id'].'.jpg';
			if(!is_file($headimg)||!is_file($icon)){
				//下载图片
				//Http::curlDownload($_SESSION['wx_userinfo']['headimgurl'],$headimg);
				if(is_file($headimg)){
					//$img = new ThinkImage(THINKIMAGE_GD,$headimg); 
					//$img->thumb(60,60,THINKIMAGE_THUMB_FIXED)->save('./Data/upload/headimg/'.$this->user_id.'_icon.jpg');
				}
				//更新数据库
				//M('wechat_user')->where(array('id'=>$this->user_id))->save(array('headimgurl'=>$headimg));
			}
		}
		$jump=get_curr_url();
		$jump=base64_encode($jump);
		if(!$this->user_id){
			//无登录信息，跳转到登录页
			$this->redirect('Member/login',array('jump'=>$jump));
		}
		//底部导航
		$nav=M('navlink')->field('id,title,url')->where(array('fup'=>0,'cid'=>1))->order('id asc')->select();
		foreach($nav as $key=>$val){
			$nav[$key]['child']=M(navlink)->field('id,title,url')->where(array('fup'=>$val['id'],'cid'=>1))->order('list asc')->select();
		}
		$this->assign('nav',$nav);
		
		
		/*
			微信jssdk
		*/
		if(is_weixin()){
			import("@.ORG.Wxjssdk");
			$wx_config=F('wx_config');
			$jsobj=new Wxjssdk($wx_config['appid'],$wx_config['appsecret']);
			$jssign=$jsobj->getSignPackage();
			$this->assign('jssign',$jssign);
		}
		
		//当前地址
		$curr_url=get_curr_url();
		$domian=I('server.HTTP_HOST');
		$tid=I('get.tid','','intval');
		
		if($this->user_id&&$this->user_id!=$tid){
			//发送访客提醒
			visit_notice($this->user_id,$tid);
		}
		
		if($this->user_id&&strpos($curr_url,$domian)!==false){		//&&empty($tid)
			
			if(empty($tid)){
				if(strpos($curr_url,'?')===false){
					$redirect=$curr_url.'?tid='.$this->user_id;
				}else{
					$redirect=$curr_url.'&tid='.$this->user_id;
				}
				redirect($redirect);
			}
				
		}
		
	}
	
	/*
		用户中心
	*/
	public function index(){
		//用户总数
		$user_count=M('wechat_user')->count();
		$this->assign('user_count',$user_count);
		
        $this->display();
		
	}
	/*
		我的资料
	*/
	public function profile(){
		$this->display();
	}
	/*
		个人信息
	*/
	public function info(){
		$db=M('wechat_user');
		$info=$db->find($this->user_id);
		$this->assign('info',$info);
		if($data=$this->_post()){
			$rs=$db->where(array('id'=>$this->user_id))->save($data);
			$info=$db->find($this->user_id);
			$this->redirect('info');
		}
		if(empty($info['username'])){
			$tpl='set_account';
		}else{
			$tpl='info';
		}
		
		$this->display();
	}
	
	/*
		基础信息
	*/
	public function person_info(){
		$this->display();
	}
	
	
	/*
		银行卡信息
	*/
	public function bank_info(){
		$this->display();
	}
	
	/*
		积分榜
	*/
	public  function jifen_rank(){
		$db=M('wechat_user');
		$list=$db->where()->order('jifen desc')->limit(100)->select();
		foreach($list as $key=>$val){
			$list[$key]['rank']=$key+1;
			$list[$key]['son_count']=$db->where("p_1={$val['id']}")->count();
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		我的收藏
	*/
	public function collect(){
		$this->display();
	}
	
	/*
		资金管理
	*/
	public function fund(){
		import('@.ORG.Page');
		$db=M('money_water');
		
		$map=array('uid'=>$this->user_id);		//,'type'=>1
		
		$count =$db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	

	/*
		订单列表【我的购买订单】
	*/
	public function order_list(){
		import('@.ORG.Page');
		$db=M('order');
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc,pay_status asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['goods']=M('order_goods')->where(array('order_id'=>$val['id']))->find();
			$list[$key]['expert']=M('wechat_user')->where(array('id'=>$val['expert_id']))->find();
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		订单二维码
	*/
	public function order_qrcode($order_id){
		$qr_local='./Data/QR/order/'.$order_id.'.jpg';
		if(!is_file($qr)){
			import("@.ORG.Http");
			import("@.ORG.Wxhelper");
			$pubwechat=M('wechat_config')->find(1);	//公众号信息
			$wxhelper=new Wxhelper($pubwechat);
			
			$return=$wxhelper->qrcode($order_id);
			$qrcode='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$return['ticket'];
			//下载图片
			Http::curlDownload($qrcode,$qr_local);
		}
		return $qr_local;
	}
	
	/*
		 我销售订单【仅限于微店主】
	*/
	public function sale_order(){
		import('@.ORG.Page');
		$db=M('order_info');
		$map=array('shop_id'=>$this->user_id);			//订单店铺id==微店主id
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc,pay_status asc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['goods']=M('order_goods')->where(array('order_id'=>$val['id']))->find();
		}
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		订单详情
	*/
	public function order_detail(){
		$order_id=I('get.id');
		$db=M('order');
		$order=$db->find($order_id);
		
		if(empty($order)){
			$this->error('订单信息不存在',U('order_list'));
		}
		/*$order_goods=M('order_goods')->where(array('order_id'=>$order_id))->select();
		foreach($order_goods as $key=>$val){
			$info=M('goods')->find($val['goods_id']);
			$order_goods[$key]['goods_id']=$info['id'];
			$order_goods[$key]['goods_name']=$info['name'];
			$order_goods[$key]['goods_spic']=$info['spic'];
			unset($info);
		}*/
		$this->assign('order',$order);
		//$this->assign('order_goods',$order_goods);
		//售后信息
		//$refund_info=M('order_refund')->where(array('order_id'=>$order_id))->find();
		//$this->assign('refund_info',$refund_info);
		
		$expert=M('wechat_user')->where(array('id'=>$order['expert_id']))->find();
		$this->assign('expert',$expert);
		
		$qrcode=$this->order_qrcode($order_id);
		$this->assign('qrcode',$qrcode);
		$this->display();
		
	}
	
	/*
		 积分兑换订单
	*/
	public function jifen_order(){
		import('@.ORG.Page');
		$db=M('jifen_order');
		
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($list as $key=>$val){
			$list[$key]['goods']=M('jifen_order_goods')->where(array('order_id'=>$val['id']))->find();
		}
		
		$this->assign('list',$list);
		$this->display();
	}
	
	
	/*
		地址管理
	*/
	public function address_list(){
		import('@.ORG.Page');
		$db=M('user_address');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		
		if($id=I('get.id')){
			$info=$db->where(array('id'=>$id))->find();
			if(empty($info)){
				$this->redirect('Ucenter/address_list');
			}
			$this->assign('info',$info);
		}
		
		$this->display();
	}
	/*
		编辑地址
	*/
	public function address_edit(){
		$id=I('get.id');
		$db=M('user_address');
		$info=$db->find($id);
		$this->assign('info',$info);
		if($data=$this->_post()){
		   $db->where(array('id'=>$id))->save($data);
		   $this->redirect('address_list');
		}
		$this->display();

	}
	/*
		新增编辑
	*/
	public function address_add(){
		if($data=$this->_post()){
			$data['user_id']=$this->user_id;
			M('user_address')->add($data);
			$this->redirect('address_list');
		}
		$this->display();
	}
	/*
		我的二维码
	*/
	public function qrcode(){
		import('@.ORG.QRcode');
		import('@.ORG.Image.ThinkImage');
		
		if(empty($_GET['tid'])){
			$this->redirect('Ucenter/qrcode',array('tid'=>$this->user_id));
		}
		
		if(I('get.tid')){
			$tid=I('get.tid');
		}else{
			$tid=$this->user_id;
		}
		
		$t_user=M('wechat_user')->where(array('id'=>$tid))->find();
		$this->assign('t_user',$t_user);
		
		$url='http://'.I('server.HTTP_HOST').U("Index/index",array('tid'=>$tid));
		$qrcode_name='./Data/upload/qrcode/'.$tid.'.jpg';
		//$logo="./Data/upload/headimg/".$parent_id.'_icon.jpg';
		if(!is_file($qrcode_name)||filesize($qrcode_name)==0){
			QRcode::png($url, $qrcode_name, 'L',8, 2); 			//生成图片
			/*if(is_file($logo)){
				$img = new ThinkImage(THINKIMAGE_GD,$qrcode_name);
				$img->water($logo,THINKIMAGE_WATER_CENTER)->save($qrcode_name);		//添加图片水印
			}*/
		}
		//二维码
		$this->assign('qrcode',$qrcode_name);
		//推广链接
		$share_url='http://'.I('server.HTTP_HOST').U('Weixin/Index/index',array('tid'=>$tid));
		$this->assign('share_url',$share_url);
		
		
		$this->assign('tid',$tid);
		$this->display();
	}
	
	
	/*
		我的积分
	*/
	public function jifen(){
		import('@.ORG.Page');
		$db=M('jifen_water');
		
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		/*foreach($list as $key=>$val){
			$order=M('order_info')->find($val['order_id']);
			$list[$key]['order']=$order;
			unset($order);
		}*/
		
		$this->assign('list',$list);
		
		$this->display();
	}
	
	
	
	public function  take_money_list(){
		$db=M('take_money');
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	/*
		店主管理中心
	*/
	public function shop_center(){
		$this->display();
	}
	/*
		店铺设置
	*/
	public function shop_setting(){
		$this->display();
	}
	/*
		店铺信信【主题】设置
	*/
	public function shop_config(){
		if($this->user_info['role_id']==1){
			$this->redirect('index');
		}
		//店铺主题列表
		$path=C('SHOP_THEME');
		$_list=scandir($path);
		foreach($_list as $key=>$val){
			if(!in_array($val,array('.','..'))&&is_dir($path.$val)){
				$list[$key]=scandir($path.$val);
			}else{
				unset($_list[$key]);
			}
		}
		foreach($list as $key=>$val){
			foreach($val as $k=>$v){
				if(in_array($v,array('.','..'))){
					unset($list[$key][$k]);
					unset($v);
				}else{
					$theme_list[$_list[$key]]['thumb']=$path.$_list[$key].'/'.$v;
				}
				
			}
		}
		$this->assign('list',$theme_list);
		$this->display();
	}	
	
	/*
		【管理中心】分销商
	*/
	public function resaler_center(){
		$this->display();
	}
	/*
		【邀请开店】分销商
	*/
	public function resaler_invite(){
		if($this->user_info['role_id']!=3){
			$this->redirect('index');
		}
		import('@.ORG.Page');
		import("@.ORG.Wxjssdk");
		import('@.ORG.QRcode');
		import('@.ORG.Image.ThinkImage');
		if($this->user_info['role_id']==3){		//分销商角色
			if(I('get.parent_id')){
				$parent_id=I('get.parent_id');
			}else{
				$parent_id=$_SESSION['user_id'];
			}
			//邀请二维码存储的链接地址
			$url='http://'.I('server.HTTP_HOST').U("Member/shop_reg",array('auxcode'=>$this->user_info['invite_code']));
			
			$this->assign('yaoqingcode',$this->user_info['invite_code']);
			
			$qrcode_name='./Data/upload/qrcode_invite/'.$parent_id.'.png';
			//$logo="./Data/upload/headimg/".$parent_id.'_icon.jpg';
			if(!is_file($qrcode_name)||filesize($qrcode_name)==0){
				QRcode::png($url, $qrcode_name, 'L',8, 2); 			//生成图片
				/*if(is_file($logo)){
					$img = new ThinkImage(THINKIMAGE_GD,$qrcode_name);
					$img->water($logo,THINKIMAGE_WATER_CENTER)->save($qrcode_name);		//添加图片水印
				}*/
			}
			$this->assign('qrcode',$qrcode_name);
			
			$wx_config=F('wx_config');
			$jsobj=new Wxjssdk($wx_config['appid'],$wx_config['appsecret']);
			$jssign=$jsobj->getSignPackage();
			$this->assign('jssign',$jssign);
			
			$this->assign('parent_id',$parent_id);
			$this->assign('invite_url',$url);			//邀请开店地址
			//邀请记录
			$where=array('parent_id'=>$this->user_id,'role_id'=>2);
			
			$count=M('wechat_user')->where($where)->count();	//
			$Page = new Page($count,20);
			$Page->setConfig('prev', '上一页');
			$Page->setConfig('next', '下一页');
			$Page->setConfig('theme',"%upPage% %downPage%");
			$page = $Page->show();
			$this->assign('page',$page);
			
			$invite_list=M('wechat_user')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('invite_list',$invite_list);
			//dump($url);die();
		}else{
			$this->redirect('Ucenter/spread');
		}
		$this->display();
		//dump($qrcode_name);
	}
	
	/*
		我的推广
	*/
	public function spread(){
		import('@.ORG.Page');
		import("@.ORG.Wxjssdk");
		import('@.ORG.QRcode');
		
		if(I('get.parent_id')){
			$parent_id=I('get.parent_id');
		}else{
			$parent_id=$_SESSION['user_id'];
		}
		//邀请二维码存储的链接地址
		$url='http://'.I('server.HTTP_HOST').U("Index/index",array('parent_id'=>$parent_id));
		
		$qrcode_name='./Data/upload/qrcode_spread/'.$parent_id.'.png';
		if(!is_file($qrcode_name)||filesize($qrcode_name)==0){
			QRcode::png($url, $qrcode_name, 'L',8, 2); 			//生成图片
		}
		$this->assign('qrcode',$qrcode_name);
		
		$wx_config=F('wx_config');
		$jsobj=new Wxjssdk($wx_config['appid'],$wx_config['appsecret']);
		$jssign=$jsobj->getSignPackage();
		$this->assign('jssign',$jssign);
		
		$shop=M('wechat_user')->find($_SESSION['user_id']);
		//微信分享数据
		$share['title']='来逛逛奥克斯官方微商城~';
		$share['desc']='价格优惠！更有0库存创业神器 “奥克斯小老板”~轻松一键开店，赚高额佣金！';
		$share['imgUrl']=$shop['headimgurl'];
		$share['link']=$url;
		$this->assign('share',$share);
		
		$this->assign('parent_id',$parent_id);
		$this->assign('invite_url',$url);			//邀请开店地址
		
		
		//邀请记录
		$where=array('parent_id'=>$this->user_id,'role_id'=>1);
		
		$count=M('wechat_user')->where($where)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$invite_list=M('wechat_user')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$this->assign('invite_list',$invite_list);
		
		$this->display();
	}
	
	
	/*
		注册开店
	*/	
	public function shop_reg(){
		$db=M('wechat_user');
		$parent_id=I('get.parent_id');		//父类id	
		$reg_code=I('get.reg_code');		//注册码
		$this->assign('parent_id',$parent_id);
		$this->assign('reg_code',$reg_code);
		$this->display();
	}
	/*
		店铺商品管理
	*/
	public function shop_goods(){
		import('@.ORG.Page');
		if($this->user_info['role_id']==1){
			$this->redirect('index');
		}
		$db=M('goods');
		
		
		$count = $db->where()->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where()->limit($Page->firstRow.','.$Page->listRows)->select();
		$fenyongconfig=M("resale_config")->where(array('id'=>1))->getField('parent_1');
		foreach($list as $key=>$val){
			$list[$key]['shopuongjin']=($fenyongconfig/100)*$val['yongjin'];
		}
		$this->assign('list',$list);
		//我的推荐列表
		$_list=M('goods_recommend')->where(array('user_id'=>$this->user_id))->select();
		foreach($_list as $key=>$val){
			$my_list[]=$val['goods_id'];
		}
		$this->assign('my_list',$my_list);
		$this->display();
	}
	/*
		申请提现，选择提现方式
	*/
	public function take_money_index(){
		$this->display();
	}
	
	/*
		提现申请
	*/
	public function take_money(){
		$pay_way=I('get.pay_way');		//1银行卡；2支付宝；3微信
		if(empty($pay_way)){
			$this->error('参数错误',U('take_money_index'));
		}
		
		if($this->user_info['role_id']!=2){
			$this->error('您暂时不能申请提现，只有分销商才能申请提现！');
		}
		if(empty($this->user_info['bank_name'])||empty($this->user_info['bank_card'])){
			//$this->error('请先完善个人银行卡信息！',U('bank_info'));
		}
		$this->display();
	}
	/*
		修改登录
	*/
	public function pwd(){
		$this->display();
	}
	/*
		商品评价
	*/
	public function goods_comment(){
		$db=M('order_goods');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$this->assign('info',$info);
		if($info['status']==1){	
			$this->error('您已经评价过了！');
		}else{
			$this->display();
		}
	}
	
	/*
		我的评论列表
	*/
	public function comment_list(){
		
		$db=M('goods_comment');
		
		$map=array('user_id'=>$this->user_id);
		
		$count = $db->where($map)->count();	//
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		设置账户名，密码【微信用户】
	*/
	public function set_account(){
		$this->display();
	}
	
	/*
		申请退款
	*/
	public function order_refund(){
		$id=I('get.id');
		
		$refund_type=I('get.refund_type');			//退款类型（1退款,2退货，3补差）
		switch($refund_type){
			case '1':
				$title="退款";
			break;	
			
			case '2':
				$title="换货";
			break;
			
			case '3':
				$title="补差";
			break;
		}
		$db=M('order_info');
		$order=M('order_info')->where(array('id'=>$id))->find();
		
		
		if(empty($order)){
			$this->error('订单信息不存在！');
		}elseif($order['pay_status']==0){
			$this->error('未付款订单，暂时不能申请售后服务！');
		}
		
		$this->assign('order',$order);
		
		$goods=M('order_goods')->where(array('order_id'=>$id))->select();
		$this->assign('goods',$goods);
		
		
		$this->assign('title',$title);
		$this->display();
	}
	
	/*
		 我的分销
	*/
	public function resale(){
		$this->display();
	}
	/*
		我的佣金
	*/
	public function yongjin_list(){
		$db=M('money_water');
		$map=array('user_id'=>$this->user_id);
		$list=$db->where($map)->limit(20)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		我的收藏
	*/
	public function goods_collect(){
		$db=M('goods_collect');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		 代金券管理
	*/
	public function coupon_list(){
		$db=M('coupon');
		
		$map=array('uid'=>$this->user_id);
		
		$count = $db->where($map)->count();	
		$Page = new Page($count,20);
		$Page->setConfig('prev', '上一页');
		$Page->setConfig('next', '下一页');
		$Page->setConfig('theme',"%upPage% %downPage%");
		$page = $Page->show();
		$this->assign('page',$page);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	
	/*
		在线充值
	*/
	public function recharge(){
		$this->display();
	}
	
	/*
		在线充值-在线支付
	*/
	public function pay(){
		$this->display();
	}
	
	
	/*
		账号绑定
	*/
	public function account_bind(){
		$db=M('wechat_user');
		if($arr=$this->_post()){
			$db->where(array('id'=>$this->user_id))->save($arr);
			$this->redirect('account_bind');	
		}else{
			$this->display();
		}
		
	}
	
	/*
		 余额
	*/
	public function balance(){
		$this->display();
	}
	
	/*
		红包
	*/
	public function coupon(){
		$this->display();
	}
	
	/*
		申请分销
	*/
	public function apply_resale(){
		$this->display();
	}
	
	/*
		支付成功，中转页面
	*/
	public function call_back_url(){
		//订单id
		$order_id=I('get.id');		
		//模板消息通知用户
		order_pay_ok_notice($order_id);		
		//通知上级
		order_pay_ok_parent_notice($order_id);
		//订单金额超过360或累计消费金额查过500;升级分销商
		up_resaler($order_id);
		
		//订单分佣
		order_fenyong($order_id);
		
		//重定向到订单详情页
		$this->redirect('order_detail',array('id'=>$order_id));
	}
	
	public function skill(){
		$db=M('wechat_user');
		
		if($this->user_info['role_id']==1){
			$this->error('您不是专家');
		}
		$myskill=array_filter(explode(',',$this->user_info['skill']));
		$this->assign('myskill',$myskill);	
		//擅长领域
		$map=array('status'=>1);
		$skill=M('skill')->where($map)->order('list asc')->select();
		$this->assign('skill',$skill);
		
		$this->display();
	}
	
	public function expert_info(){
		$db=M('wechat_user');
		
		if($this->user_info['role_id']==1){
			$this->error('您不是专家');
		}
		
		$this->display();
	}
	
}