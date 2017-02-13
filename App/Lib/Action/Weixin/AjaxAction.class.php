<?php
/*
	Ajax请求处理控制器-【微信商城】
*/
class AjaxAction extends Action{
	public $user_id;
	public $user_info;
	public function _initialize(){
		$this->user_id=$_SESSION['user_id'];
		$this->user_info=M('wechat_user')->find($_SESSION['user_id']);
	}
	
	
	
	
	/*
		在线充值
	*/
	public function recharge(){
		$db=M('recharge');
		if($arr=$this->_post()){
			$arr['pay_status']=0;		
			$arr['uid']=$this->user_id;
			$arr['uname']=$this->user_info['nickname'];
			$arr['out_trade_no']='cz'.$this->user_id.time();
			$arr['posttime']=time();
			$id=$db->add($arr);
			echo $id;
		}

	}
	
	/*
		在线咨询下单
	*/
	public function chat_order_submit(){
		$db=M('order');
		if($arr=$this->_post()){
			$arr['out_trade_no']='XY'.date('ymdHis',time()).rand(111,999);
			$arr['uid']=$this->user_id;			//下单人
			$arr['order_time']=time();
			$order_id=$db->add($arr);
			echo $order_id;	
		}
	}
	
	/*
		支付成功，异步通知地址
	*/
	public function notify_url(){
	
		
		//订单id
		$order_id=I('get.order_id');	
		
		//订单信息
		$order=M('order_info')->where(array('id'=>$order_id))->find();
		if($order['notice_status']==0){
			//模板消息通知用户
			order_pay_ok_notice($order_id);		
			
			//通知上级
			order_pay_ok_parent_notice($order_id);
			
			//修改订单通知状态为1
			M('order_info')->where(array('id'=>$order_id))->save(array('notice_status'=>1));
		}	
		
		//下单人信息
		$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
		
		//如果买家是普通会员，升级分销商；如果买家已经是分销商，则无需执行此代码
		if($user['role_id']!=2){
			//订单金额超过360或累计消费金额查过500;升级分销商
			up_resaler($order_id);
		}
		
		//是否已经赠送
		$coupon_info=M('coupon')->where(array('uid'=>$this->user_id,'cid'=>1))->find();
		$active_status=M('resale_config')->where('id=1')->getField('active_status');
		
		//活动进行中&&未赠送过
		if($active_status==1&&empty($coupon_info)){
			//未赠送代金券；开始赠送代金券
			if($order['coupon_status']==0){
				$over_time=time()+180*24*3600;
				for($i=0;$i<10;$i++){
					$data['cid']=1;							//代金券类型；1购买赠送
					$data['uid']=$order['uid'];				//用户id
					$data['amount']=100;					//面值
					$data['posttime']=time();				//发送时间
					$data['over_time']=$over_time;	//有效期180天
					coupon_send($data);
					unset($data);
					//订单状态改为已赠送代金券
					M('order_info')->where(array('id'=>$order_id))->save(array('coupon_status'=>1));
				}		
				//模板消息通知
				$notice_data=array('uid'=>$order['uid'],'amount'=>'100','over_time'=>$over_time,'num'=>10);
				coupon_multi_notice($notice_data);
				
				
			}			
		}	
		
		if($order['fy_status']==0){
			//订单分佣
			order_fenyong($order_id);
			$db->where(array('id'=>$order_id))->save(array('fy_status'=>1));
		}
		
		
	}
	
	/*
		商品评价
	*/
	public function goods_reply(){
		$db=M('goods_reply');
		if($arr=$this->_post()){
			$record=$db->where(array('gid'=>$arr['gid'],'uid'=>$this->user_id))->find();
			if(empty($record)){
				$arr['uid']=$this->user_id;
				$arr['uname']=$this->user_info['nickname'];
				$arr['headimg']=$this->user_info['headimgurl'];
				$arr['posttime']=time();
				$db->add($arr);
				echo 1;
			}else{
				echo 2;
			}
			
		}
	}
	
	/*
		商品收藏
	*/
	public function goods_collect(){
		$db=M('goods_collect');
		$errcode=0;
		if($arr=$this->_post()){
			$map=array('gid'=>$arr['gid'],'uid'=>$this->user_id);
			$record=$db->where($map)->find();
			if(empty($record)){
				$goods=M('goods')->where(array('id'=>$arr['gid']))->find();
				$arr['name']=$goods['name'];
				$arr['spic']=$goods['spic'];
				$arr['uid']=$this->user_id;
				$arr['posttime']=time();
				$db->add($arr);
			}else{
				$errcode=1;		//已收藏
			}
			echo $errcode;
		}
	}
	
	/*
		保存购物车地址
	*/
	public function save_address(){
		$_SESSION['address_cache']=$data=$this->_post();
		//入库
		$data['user_id']=$_SESSION['user_id'];
		M('user_address')->add($data);
		echo 1;
	}
	/*
		添加购物车
	*/
	public function addcart(){
		$goods_id=I('post.goods_id');				//商品id
		$goods_nums=I('post.goods_nums');			//商品数量
		$goods_price=I('post.goods_price');			//商品价格
		$goods_norm=I('post.goods_norm');			//商品规格
		addcart($goods_id,$goods_nums,$goods_price,$goods_norm);
		foreach($_SESSION['shop_cart_info'] as $val){
			$cart_goods_nums+=$val['goods_nums'];
		}
		$_SESSION['cart_goods_nums']=$cart_goods_nums;
		echo $cart_goods_nums;
	}
	/*
		更新购物车
	*/
	public function updatecart(){
		$cart_key=I('post.cart_key');
		$act=I('post.act');		//add?增加:减少
		updatecart($cart_key,$act);
		foreach($_SESSION['shop_cart_info'] as $val){
			$cart_goods_nums+=$val['goods_nums'];
		}
		$_SESSION['cart_goods_nums']=$cart_goods_nums;
		echo 1;
	}
	public function _updatecart(){
		$goods_id=I('post.goods_id');				//商品id
		$goods_norm=I('post.goods_norm');			//规格
		$act=I('post.act');		//add?增加:减少
		updatecart($goods_id,$act,$goods_norm);
		foreach($_SESSION['shop_cart_info'] as $val){
			$cart_goods_nums+=$val['goods_nums'];
		}
		$_SESSION['cart_goods_nums']=$cart_goods_nums;
		echo 1;
	}
	/*
		删除购物车
	*/
	public function delcart(){
		if($arr=$this->_post()){
			delcart($arr['cart_key']);
			foreach($_SESSION['shop_cart_info'] as $val){
				$cart_goods_nums+=$val['goods_nums'];
			}
			$_SESSION['cart_goods_nums']=$cart_goods_nums;
			echo 1;
		}
		
		
	}
	/*
		提交订单
	*/
	public function order(){
		$time=time();
		$ymd=date('Ymd',$time);
		$ym=date('Ym',$time);
		if($arr=$this->_post()){
		
			if($addr_id=I('post.addr_id')){
				$addr_info=M('user_address')->where(array('id'=>$addr_id))->find();
				$order_data['consignee']=$addr_info['consignee'];
				$order_data['mobile']=$addr_info['mobile'];
				$order_data['zipcode']=$addr_info['zipcode'];
				
				$order_data['province_id']=$addr_info['province_id'];
				$order_data['city_id']=$addr_info['city_id'];
				$order_data['district_id']=$addr_info['district_id'];
				
				$order_data['province']=$addr_info['province'];
				$order_data['city']=$addr_info['city'];
				$order_data['district']=$addr_info['district'];
				$order_data['address']=$addr_info['address'];
			}else{
				$order_data['province_id']=$arr['province_id'];
				$order_data['city_id']=$arr['city_id'];
				$order_data['district_id']=$arr['district_id'];
				
				$order_data['province']=M('region')->where(array('id'=>$arr['province_id']))->getField('region_name');
				$order_data['city']=M('region')->where(array('id'=>$arr['city_id']))->getField('region_name');
				$order_data['district']=M('region')->where(array('id'=>$arr['district_id']))->getField('region_name');
				
				$order_data['consignee']=$arr['consignee'];
				$order_data['mobile']=$arr['mobile'];
				$order_data['address']=$arr['address'];
				
				
				
				//插入用户收货地址表
				$order_data['uid']=$this->user_id;
				M('user_address')->add($order_data);
				
				//快递信息
				/*$express=M('express')->where(array('id'=>$arr['express_id']))->find();
				
				$order_data['express_id']=$arr['express_id'];
				$order_data['express_fee']=$express['price'];
				$order_data['express_name']=$express['name'];*/
			
				
			}
			
			$order_data['uid']=$this->user_id;		//下单用户
			$order_data['out_trade_no']='YRYZ'.date('mdHis',time()).rand(1111,9999);
			$order_data['order_time']=$time;
			$order_data['ymd']=$ymd;
			$order_data['ym']=$ym;
			$order_data['pay_way']=$arr['pay_way'];		//1微信支付,2支付宝
			
			
			
			$goods_data=session('shop_cart_info');
			foreach($goods_data as $key=>$val){
				//商品总金额【商品原始价格总和】
				$order_data['total_price']+=$val['goods_price']*$val['goods_nums'];			
				
				$info=M('goods')->find($val['goods_id']);
				//$goods_data[$key]['sid']=$info['sid'];					//商家id
				$goods_data[$key]['goods_name']=$info['name'];
				$goods_data[$key]['goods_spic']=$info['spic'];
				
				//$order_data['express_fee']+=$info['express_price'];				//快递总费用
				
				//商品规格信息
				if($val['goods_norm']>0){
					$norm=M('goods_norm')->where(array('id'=>$val['goods_norm']))->find();
					if(empty($norm)){
						$goods_data[$key]['goods_norm']=$norm['title'];				//商品规格名称
						
						$goods_data[$key]['goods_price']=$info['price'];			
					}else{
						$goods_data[$key]['goods_price']=$norm['price'];			//商品价格【规格不同，价格不同】	
					}
					unset($norm);
				}
				
				$goods_data[$key]['yongjin']=$info['yongjin'];				//单个商品佣金【不同规格商品，佣金相同】
				
				
			}
			
			//商品总价+快递费用+$express['price']		
			$order_data['express_fee']=$arr['express_fee'];
			$order_data['total_fee']=$order_data['total_price']+$order_data['express_fee'];		
			if($arr['coupon_id']>0){
				$coupon=M('coupon')->where(array('id'=>$arr['coupon_id']))->find();
				$order_data['total_fee']=$order_data['total_fee']-$coupon['amount'];			//最终需要支付的金额
				$order_data['coupon_id']=$coupon['id'];
				$order_data['coupon_amount']=$coupon['amount'];
				//更新代金券状态
				M('coupon')->where(array('id'=>$coupon['id']))->save(array('status'=>1,'cost_time'=>time()));
			}
			$shop_id=session('shop_id');
			if(!empty($shop_id)){
				$order_data['shop_id']=session('shop_id');				//店铺id
			}
			
			//插入订单表
			$order_id=M('order_info')->add($order_data);
			foreach($goods_data as $key=>$val){
				$val['order_id']=$order_id;
				$val['ymd']=$ymd;
				$val['posttime']=$time;
				M('order_goods')->add($val);					//插入订单商品表
			}
			
			session('shop_cart_info',null);						//清空购物车
			
			$output=array('pay_way'=>$arr['pay_way'],'order_id'=>$order_id,'sql'=>M('order_info')->getlastsql());
			
			//发送订单提交成功通知【模板消息】
			order_add_ok_notice($order_id);
			//通知上级用户
			order_add_ok_parent_notice($order_id);
			
			echo json_encode($output);
			
		}
		
	}
	
	/*
		订单取消
	*/
	public function order_cancel(){
		$db=M('order_info');
		if($this->_post()){
			$id=I('post.order_id');
			$db->delete($id);												//删除订单信息
			M('order_goods')->where(array('order_id'=>$id))->delete();		//删除订单商品信息
			echo 1;
			exit();
		}
	}
	
	/*
		新增收货地址
	*/
	public function address_add(){
		$data=$this->_post();
		$data['user_id']=$_SESSION['user_id'];
		$data['province']=M('region')->where(array('id'=>$data['province_id']))->getField('region_name');
		$data['city']=M('region')->where(array('id'=>$data['city_id']))->getField('region_name');
		$data['district']=M('region')->where(array('id'=>$data['district_id']))->getField('region_name');
		M('user_address')->add($data);
		echo 1;
	}
	/*
		新增/编辑收货地址
	*/
	public function address_edit(){
		$db=M('user_address');
		if($data=$this->_post()){
			//$id=I('get.id');
			if($data['province_id']>0&&!empty($data['province_id'])){
				$data['province']=M('region')->where(array('id'=>$data['province_id']))->getField('region_name');
			}
			if($data['city_id']>0&&!empty($data['city_id'])){
				$data['city']=M('region')->where(array('id'=>$data['city_id']))->getField('region_name');
			}
			if($data['district_id']>0&&!empty($data['district_id'])){
				$data['district']=M('region')->where(array('id'=>$data['district_id']))->getField('region_name');
			}
			$id=$data['id'];
			if($data['id']>0){
				unset($data['id']);
				$db->where(array('id'=>$id))->save($data);				//编辑
			}else{
				$data['uid']=$this->user_id;
				$db->add($data);										//新增
			}
			echo 1;
		}
		
	}
	
	/*
		 删除地址
	*/
	public function address_del(){
		$db=M('user_address');
		if($this->_post()){
			$id=I('post.id');
			$db->delete($id);
			echo 1;
		}
		
	}
	
	/*
		申请提现
	*/
	public function apply_money(){
		$db=M('apply_money');
		$data['money']=I('post.money');
		if(empty($_SESSION['user_id'])){
			echo 2;		//登录超时
		}else{
			$data['user_id']=$_SESSION['user_id'];
			$data['apply_time']=time();
			$data['bank_card']=$_SESSION['user_info']['bank_card'];
			$data['bank_name']=$_SESSION['user_info']['bank_name'];
			$rs=$db->add($data);
			if($rs){
				echo 1;		//操作成功
			}else{
				echo 3;		//操作失败
			}
		}
		
	}
	
	/*
		加载更多商品	      
	*/
	public function product_load(){
		$db=M('goods');
		if($this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$cid=I('post.cid');
			$rank=I('post.rank');		//排序
			if($rank=='price'){
				$order='price asc';
			}elseif($rank=='hits'){
				$order='hits desc';
			}elseif($rank=='sale_nums'){
				$order='sale_nums desc';
			}else{
				$order='id desc';
			}
			if($cid>0){
				$map['cid']=array('like','%'.$cid.',%');
			}else{
				$map='';
			}
			$map['is_sale']=1;
			$list=$db->where($map)->order($order)->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['name']=mb_substr($val['name'],0,38,'utf-8');
			}
			echo json_encode($list);
		}
	}
	
	/*
		编辑信息
	*/
	public function info_update(){
		$db=M('wechat_user');
		if($arr=$this->_post()){
			if(!empty($arr['password'])){			//如果密码不为空
				$arr['password']=md5($arr['password']);
			}else{
				unset($arr['password']);
			}
			$db->where(array('id'=>$this->user_id))->save($arr);
			echo '保存成功';
			exit();
		}
	}
	
	/*
		修改密码
	*/
	public function pwd_update(){
		$db=M('wechat_user');
		$errcode=0;
		if($arr=$this->_post()){
			$i=$db->where(array('id'=>$this->user_id))->find();
			if($i['password']!=md5($arr['old_pwd'])){
				$errcode=1;
			}else{
				$data['password']=md5($arr['password']);
				$db->where(array('id'=>$this->user_id))->save($data);
			}
			echo $errcode;exit();
		}
	}
	/*
		积分兑换订单
	*/
	public function jifen_order(){
		$db=M('jifen_order');
		$errcode=0;
		if($arr=$this->_post()){
			$goods_nums=$arr['goods_nums'];
			unset($arr['goods_nums']);
			/*if(isset($arr['addr_id'])){
				$addr=M('user_address')->find($addr_id);
				$arr['consignee']=$addr['consignee'];
				$arr['mobile']=$addr['mobile'];
				$arr['province']=$addr['province'];
				$arr['city']=$addr['city'];
				$arr['district']=$addr['district'];
				$arr['address']=$addr['address'];
				unset($arr['addr_id']);	
			}else{
				
			}*/
			$arr['province']=M('region')->where(array('id'=>$arr['province']))->getField('region_name');
			$arr['city']=M('region')->where(array('id'=>$arr['city']))->getField('region_name');
			$arr['district']=M('region')->where(array('id'=>$arr['district']))->getField('region_name');
			
			$goods=M('jifen_goods')->where(array('id'=>$arr['goods_id']))->find();
			unset($arr['goods_id']);
			$arr['total_fee']=$goods['price']*$goods_nums;
			if($this->user_info['jifen']>=$arr['total_fee']){
				$arr['user_id']=$this->user_id;
				$arr['out_trade_no']='JF'.date('mdHis').rand(1111,9999);
				$arr['order_time']=time();
				$order_id=$db->add($arr);			//插入订单数据
				$order_goods['order_id']=$order_id;
				$order_goods['goods_id']=$goods['id'];
				$order_goods['goods_name']=$goods['name'];
				$order_goods['goods_price']=$goods['price'];
				$order_goods['goods_spic']=$goods['spic'];
				$order_goods['goods_nums']=$goods_nums;
				M('jifen_order_goods')->add($order_goods);
				jifen_change($this->user_id,2,$arr['total_fee'],'exchange','兑换商品');
			}else{
				$errcode=1;		//积分余额不足
			}
			
			echo $errcode;exit();
			
		}
	}
	
	/*
		选择店铺主题
	*/
	public function shop_theme(){
		$db=M('wechat_user');
		if($this->_post()){
			$data['shop_theme']=I('post.shop_theme');
			$db->where(array(array('id'=>$this->user_id)))->save($data);
			echo 1;exit();
		}
	}
	
	/*
		头像上传
	*/
	public function upload_headimg(){
		import('@.ORG.Image.ThinkImage');
		$return=array('flag'=>0,'msg'=>'','img'=>'');
		if(empty($this->user_id)){
			$return['msg']='登录超时，请重新登录！';
			echo json_encode($return);
			exit();
		}
		$dir="./Data/upload/headimg";

		$extArr = array("jpg", "png", "gif");
		if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
			$name = $_FILES['photoimg']['name'];
			$size = $_FILES['photoimg']['size'];
			
			if(empty($name)){
				$return['msg']='请选择要上传的图片!';
				echo json_encode($return);
				exit;
			}
			$ext=extend($name);
			if(!in_array($ext,$extArr)){
				$return['msg']='图片格式错误!';
				echo json_encode($return);
				exit;
			}
			if($size>(100*1024*1024)){
				$return['msg']='图片大小不能超过100KB!';
				echo json_encode($return);
				exit;
			}
			$image_name =$this->user_id.".".$ext;
			$tmp = $_FILES['photoimg']['tmp_name'];
			if(move_uploaded_file($tmp, $dir.'/'.$image_name)){
				$return['flag']=1;
				$return['msg']='上传成功!';
				$return['img']=$dir.'/'.$image_name;
				$img_source=$dir.'/'.$image_name;
				//生成缩略图
				$thumb=$dir.'/thumb_'.$image_name;
				$img = new ThinkImage(THINKIMAGE_GD,$img_source); 
        		$img->thumb(200,200,THINKIMAGE_THUMB_FIXED)->save($thumb);
				//保存数据库
				M('wechat_user')->where(array('id'=>$this->user_id))->save(array('headimgurl'=>$return['img']));
				echo json_encode($return);
				exit;
			}else{
				$return['msg']='上传失败';
				echo json_encode($return);
				exit;
			}
		}
	}
	
	/*
		上传base64
	*/
	public function upload_base64(){
		import('@.ORG.Image.ThinkImage');
		$return=array('flag'=>0,'msg'=>'','img'=>'');
		if(empty($this->user_id)){
			$return['msg']='登录超时，请重新登录！';
			echo json_encode($return);
			exit();
		}
		$dir="./Data/upload/headimg";
		
		$rand=substr(time(),-4);
		$image_name =$this->user_id.'_'.$rand.".jpg";
		
		
		$img_str=$_POST['img_str'];
		$img_str=str_replace('data:image/jpeg;base64,','',$img_str);
		$img_str=str_replace('data:image/png;base64,','',$img_str);
		$img_str=str_replace('data:image/gif;base64,','',$img_str);
		file_put_contents($dir.'/'.$image_name,base64_decode($img_str));
		
		
		$return['flag']=1;
		$return['msg']='上传成功!';
		$return['img']=$dir.'/'.$image_name;
		$img_source=$dir.'/'.$image_name;
		//生成缩略图
		$thumb=$dir.'/thumb_'.$image_name;
		$img = new ThinkImage(THINKIMAGE_GD,$img_source); 
		$img->thumb(200,200,THINKIMAGE_THUMB_FIXED)->save($thumb);
		//保存数据库
		M('wechat_user')->where(array('id'=>$this->user_id))->save(array('headimgurl'=>$thumb));
		echo json_encode($return);
		exit;
	}
	
	/*
		商品评价
	*/
	public function goods_comment(){
		$db=M('goods_comment');
		$id=I('post.id');			//订单商品表【order_goods】id
		$output['errcode']=0;
		if($arr=$this->_post()){
			$goods=M('order_goods')->find($id);
			$data['goods_id']=$goods['goods_id'];		
			$data['goods_name']=$goods['goods_name'];
			$data['goods_spic']=$goods['goods_spic'];
			
			$data['star']=$arr['star'];				//星评
			$data['content']=$arr['content'];		//评价内容
			
			$data['user_id']=$this->user_id;		//评论者id
			$data['nickname']=$this->user_info['username'];
			$data['headimg']=$this->user_info['headimgurl'];
			
			$data['posttime']=time();
			
			if($db->add($data)){
				//改为"已评论"状态
				M('order_goods')->where(array('id'=>$id))->save(array('status'=>1));	
			}
			
			
			$output['msg']='感谢您的评价！';
			$output['order_id']=$goods['order_id'];		//订单id
			
			echo json_encode($output);exit();
		}
	}
	
	/*
		查询商品地区库存
	*/
	public function query_store_nums(){
		$output=array('errcode'=>0);
		if($arr=$this->_post()){
			//记录配送地区
			cookie('province',$arr['province']);
			cookie('city',$arr['city']);
			cookie('district',$arr['district']);
			//查询仓库信息
			$storage=M('storage')->where(array('area_list'=>array('like','%'.$arr['district'].',%')))->find();
			//file_put_contents('sql.txt',M('storage')->getlastsql());
			if(empty($storage)){				//无对应仓库信息
				$output['errcode']=1;			//库存不足
				$output['msg']='该地区无货！';
			}else{
				$map=array('goods_id'=>$arr['goods_id'],'storage_id'=>$storage['id']);	//商品id，仓库id
				$store_info=M('goods_store')->where($map)->find();
				if(empty($store_info)){
					$output['errcode']=1;			//库存不足
					$output['msg']='该地区库存不足！';
				}else{
					$output['store_nums']=$store_info['store_nums'];	//库存数量
					$output['msg']='该地区剩余库存'.$store_info['store_nums'];
				}
				
			}
			echo json_encode($output);
			exit();
		}
	}
	
	/*
		提现
	*/
	public function take_money(){
		$db=M('take_money');
		$output=array('errcode'=>0,'msg'=>'提交成功，我们会尽快处理您的申请');
		if($arr=$this->_post()){
			if($this->user_info['money']<$arr['money']){
				$output['errcode']=1;					//账户余额不足
				$output['msg']='账户余额不足';
			}else{
				$udata=array();
				$udata['mobile']=$arr['mobile'];
				if($arr['pay_way']==2){
					$udata['alipay']=$arr['alipay'];
				}elseif($arr['pay_way']==3){
					$udata['weixin']=$arr['weixin'];
				}
				//保存用户数据
				M('wechat_user')->where(array('id'=>$this->user_id))->save($udata);
				//冻结相应金额的资金
				M('wechat_user')->where(array('id'=>$this->user_id))->setDec('money',$arr['money']);
				M('wechat_user')->where(array('id'=>$this->user_id))->setInc('money_dongjie',$arr['money']);
				//记录提现信息
				$arr['user_id']=$this->user_id;
				$arr['pay_way']=$arr['pay_way'];						//提现方式
				$arr['bank_name']=$arr['bank_name'];
				$arr['bank_card']=$arr['bank_card'];
				$arr['alipay']=$arr['alipay'];
				$arr['weixin']=$arr['weixin'];
				$arr['apply_time']=time();
				$db->add($arr);
				file_put_contents('sql.txt',$db->getlastsql());
			}
			//模板消息通知用户
			take_money1($this->user_id,$arr['money']);
			echo json_encode($output);
		}
	}
	
	/*
		商品推荐
	*/
	public function goods_tui(){
		$db=M('goods_recommend');
		if($arr=$this->_post()){
			$arr['user_id']=$this->user_id;
			$arr['posttime']=time();
			$db->add($arr);	
			echo 1;
		}
	}
	
	/*
		推荐取消
	*/
	public function goods_cancel(){
		$db=M('goods_recommend');
		if($arr=$this->_post()){
			$arr['user_id']=$this->user_id;
			$db->where($arr)->delete();	
			echo 1;
		}
	}
	
	/*
		积分商城商品加载
	*/
	public function jifen_product_load(){
		$db=M('jifen_goods');
		if($this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$order='id desc';
			$map=array();
			$list=$db->where($map)->order('id desc')->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['name']=mb_substr($val['name'],0,38,'utf-8');
			}
			echo json_encode($list);
		}
	}
	
	/*
		加载资金流水
	*/
	public function fund_load(){
		$db=M('money_water');
		if($arr=$this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$map=array('user_id'=>$this->user_id);
			$list=$db->where($map)->order('id desc')->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['posttime']=date('Y-m-d H:i:s',$val['posttime']);
			}
			echo json_encode($list);
		}
	}
	
	/*
		加载积分流水
	*/
	public function jifen_load(){
		$db=M('jifen_water');
		if($arr=$this->_post()){
			$firstRow=I('post.offset');		//从第几条开始
			$listRows=8;		//每次加载条数
			$map=array('user_id'=>$this->user_id);
			$list=$db->where($map)->order('id desc')->limit($firstRow.','.$listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['posttime']=date('Y-m-d H:i:s',$val['posttime']);
			}
			echo json_encode($list);
		}
	}
	
	/*
		订单退款申请
	*/
	public function	order_refund(){
		$db=M('order_refund');
		if($arr=$this->_post()){
			$info=$db->where(array('order_id'=>$arr['order_id']))->find();
			if(empty($info)){
				$arr['posttime']=time();
				$db->add($arr);
			}else{
				$db->where(array('id'=>$info['id']))->save($arr);
			}
			echo 1;		
		}
	}
}