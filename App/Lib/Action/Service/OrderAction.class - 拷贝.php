<?php
//订单管理
class OrderAction extends PublicAction{
	public function index(){
		import("@.ORG.Page");
		$db=M('order_info');
		if(isset($_GET['order_status'])){
			$order_status=I('get.order_status');
			$map=array('order_status'=>$order_status);
		}else{
			$map=array();
		}
		
		
		//$service=M('service')->find(I('session.service_id'));
		
		//$map['district_id']=array('in',$service['area_list']);
		
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		
		if(in_array($so_key,array('order_sn','mobile','consignee'))){
			if(!empty($so_val)&&!empty($so_val)){
				$map[$so_key]=array('like','%'.$so_val.'%');
			}
		}
		
		$sid=I('session.service_id');
		
		//商品订单表
		$o_goods=M('order_goods')->where(array('sid'=>$sid))->select();
		foreach($o_goods as $val){
			$o_arr[]=$val['order_id'];
		}
		$o_arr=array_unique($o_arr);				//订单id数组
		
		$map['id']=array('in',$o_arr);				//订单id在此数组中	
		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$show = $Page->show();

		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	//增加
	public function add(){
		$db=M('order_info');
		if(IS_POST){
			$data=$this->_post();
			$db->add($data);
			$this->redirect('index');
		}
		$this->display();
	}
	//编辑
	public function edit(){
            $order_id=$map['id']=I('get.id');
			$express=M('express')->where(array('status'=>1))->select();
			$this->assign('wllist',$express);
            $db=M('order_info');
            //订单信息
            $data=$db->where($map)->find();
            if(empty($data)){
                $this->error('该订单已不存在！');
            }else{
				$data['order_user']=M('wechatuser')->field('id,nickname,name')->find($data['user_id']);
			}
            $this->assign('data',$data);
            //商品信息
            $order_goods=M('order_goods')->where(array('order_id'=>I('get.id')))->order('id desc')->select();
            $this->assign('order_goods',$order_goods);
			//$goods_list=M('order_goods')->where(array('order_id'=>$order_id))->select();
            if($arr=$this->_post()){
				//表单令牌【防止重复提交】
				if($db->autoCheckToken($_POST)){
					
					//交易完成
					if($arr['order_status']==3){
						//返积分
						$goods=M('order_goods')->where(array('order_id'=>$id))->select();
						foreach($goods as $key=>$val){
							$jifen+=$val['jifen']*$val['goods_nums'];			//积分总数
						}
						//增加买家【积分账户】
						if(M('wechat_user')->where(array('id'=>$data['user_id']))->setInc('jifen',$jifen)){
							//记录积分流水
							$water['user_id']=$data['user_id'];
							$water['type']=1;						//收入
							$water['way']='order_jifen';						//订单返积分
							$water['amount']=$jifen;				//积分数量		
							$water['way_name']='订单赠送积分';
							$water['order_id']=$data['id'];			//订单id		
							$water['posttime']=time();
							M('jifen_water')->add($water);			//插入积分流水表	
						}
						//减少库存
						$storage=M('storage')->where(array('area_list'=>array('like','%'.$data['district_id'].',%')))->find();
						//循环减去对应商品的库存
						foreach($order_goods as $val){
							$store_map=array('storage_id'=>$storage['id'],'goods_id'=>$val['goods_id']);
							M('goods_store')->where($store_map)->setDec('store_nums',$val['goods_nums']);
						}
						
						//微店分佣
						self::fenyong($order_id);
					}
					
					
					$db->where($map)->save($arr);
					$this->redirect('edit',array('id'=>$order_id));
					
					
				}
				
            }
			//下单人信息
			$user=M('wechat_user')->find($data['user_id']);
			$this->assign('user',$user);
			//店铺信息
			if($data['shop_id']>0){
				$shop=M('wechat_user')->find($data['shop_id']);
				$this->assign('shop',$shop);
			}
            $this->display();
	}
	
	/*
		分佣
		@param int $orderer_id;
	*/
	public function fenyong($order_id){
		$config=M('resale_config')->find(1);		//分佣配置
		
		//订单信息
		$order=M('order_info')->find($orer_id);
		//订单商品
		$goods=M('order_goods')->where(array('order_id'=>$order_id))->select();
		foreach($goods as $val){
			$goods_info=M('goods')->find($val['goods_id']);
			//订单佣金总额
			$yongjin+=$val['goods_nums']*$goods_info['yongjin'];
		}
		
		
		if($order['shop_id']>0){		//订单shop_id>0
			$resaler1=M('wechat_user')->find($order['shop_id']);			//一级分销
			$resaler1['yongjin']=$yongjin*($config['parent_1']*0.01);
			$resaler1['percent']=$config['parent_1'];
			if($resaler1['parent_id']>0){
				$resaler2=M('wechat_user')->find($resaler1['parent_id']);	//二级分销
				$resaler2['yongjin']=$yongjin*($config['parent_2']*0.01);
				$resaler2['percent']=$config['parent_2'];
			}
		}
		$water_db=M('money_water');
		if(!empty($resaler1)){
			M('wechat_user')->where(array('id'=>$resaler1['id']))->setInc('money_account',$resaler1['yongjin']);
			//记录资金流水
			
			$water['user_id']=$resaler1['id'];
			$water['order_id']=$order_id;					//订单id
			$water['type']=1;						//收入
			$water['amount']=$resaler1['yongjin'];	//金额
			$water['way']='yongjin';						//分销佣金
			$water['way_name']='分销佣金';
			$water['posttime']=time();
			$water_db->add($water);
			unset($water);
		}
		if(!empty($resaler2)){
			M('wechat_user')->where(array('id'=>$resaler2['id']))->setInc('money_account',$resaler2['yongjin']);
			$water['user_id']=$resaler2['id'];
			$water['order_id']=$order_id;					//订单id
			$water['type']=1;						//收入
			$water['amount']=$resaler2['yongjin'];	//金额
			$water['way']='yongjin';						//分销佣金
			$water['way_name']='分销佣金';
			$water['posttime']=time();
			$water_db->add($water);
		}
		//修改订单分佣状态【已分佣】
		M('order_info')->where(array('id'=>$order_id))->save(array('fenyong_status'=>1));
	}
	
	
	/*
		更新支付状态
	*/
	public function update_pay_status(){
		$order_id=I('get.order_id');
		$db=M('order_info');
		$info=$db->find($order_id);
		if($info['pay_status']==1){
			$data=array('pay_status'=>0,'pay_time'=>0);
		}else{
			$data=array('pay_status'=>1,'pay_time'=>time());
		}
		$db->where(array('id'=>$order_id))->save($data);
		$this->redirect('index',array('p'=>I('get.p','1')));
	}
	//删除
	public function del(){
            if($id=I('get.id')){
                M('order_info')->where(array('id'=>$id))->delete();
                M('order_goods')->where(array('order_id'=>$id))->delete();
                $this->redirect('index');
            }
	}  
	
	/*
		退款成功
	*/
	public function refund_success(){
		import('@.ORG.Wxhelper');
		$order_id=I('get.order_id');
		$db=M('order');
		$order=$db->find($order_id);
		if($order['refund_status']==1){
			$user=M('wechat_users')->find();
			$log=array(
		   'order_id'=>$order['id'],
		   'user_id'=>$order['user_id'],
		   'refund_fee'=>$order['refund_fee'],
		   'posttime'=>$order['refund_time'],
		   'nickname'=>$user['nickname'],
		   );
		   //添加退款记录
		   M('refund_log')->add($log);
		   //发送退款消息【客户消息】
		   	$config['appid']=C('WECHAT_APPID');
			$config['appsecret']=C('WECHAT_APPSECRET');
			$helper=new Wxhelper($config);
			$msg_data['touser']=$user['openid'];			
			$msg_data['template_id']="1i9b4WDKkoxIVGLHqCWKiitTDLnbO6JvaE5Xz9EfDYs";				
			$msg_data['url']='http://'.I('server.HTTP_HOST').U('Wx/Member/order_detail',array('order_id'=>$order_id));
			$msg_data['topcolor']='#FF0000';
			$msg_data['data']['first']=array('value'=>"您好，您的订单已成功退款，请您注意查收");
			$msg_data['data']['keynote1']=array('value'=>$order['refund_fee']);			//退款金额
			$msg_data['data']['keynote2']=array('value'=>'退回微信余额或支付银行卡');					//退款方式
			$msg_data['data']['keynote3']=array('value'=>'参考微信支付系统消息');						//到账时间
			$msg_data['data']['keynote4']=array('value'=>$order['luxian_name']);		//商品描述
			$msg_data['data']['keynote5']=array('value'=>$order['order_sn']);			//交易单号
			$msg_data['data']['keynote6']=array('value'=>'客户申请退款');							//退款原因
			$msg_data['data']['remark']=array('value'=>'单击可查看订单详情。');
			$return=$helper->send_tpl_msg($msg_data);
			//dump($return);die();
			
			unset($msg_data);
			//发送退款消息【通知管理员】
			$msg_data['touser']='#';//'oqNjGs1b-0OmVFIaKAqT80OaXSIA';//			//李洋			
			$msg_data['template_id']="1i9b4WDKkoxIVGLHqCWKiitTDLnbO6JvaE5Xz9EfDYs";				
			//$msg_data['url']='http://'.I('server.HTTP_HOST').U('Wx/Member/order_detail',array('order_id'=>$order_id));
			$msg_data['topcolor']='#FF0000';
			$msg_data['data']['first']=array('value'=>"您好，有一笔客户退款成功，退款详情如下");
			$msg_data['data']['keynote1']=array('value'=>$order['refund_fee']);			//退款金额
			$msg_data['data']['keynote2']=array('value'=>'退回微信余额或支付银行卡');					//退款方式
			$msg_data['data']['keynote3']=array('value'=>'参考微信支付系统消息');						//到账时间
			$msg_data['data']['keynote4']=array('value'=>$order['luxian_name']);		//商品描述
			$msg_data['data']['keynote5']=array('value'=>$order['order_sn']);			//交易单号
			$msg_data['data']['keynote6']=array('value'=>'客户申请退款');							//退款原因
			$msg_data['data']['remark']=array('value'=>'客户昵称：'.$user['nickname'].'；联系人：'.$order['linkman'].'【退款详情可登录微信支付商户后台进行查看。】');
			$return=$helper->send_tpl_msg($msg_data);
			
		   $this->success('退款成功!',U('Order/edit',array('id'=>$order_id)));
		}else{
			$this->redirect('refund_fail',array('order_id'=>$order_id,'err_msg'=>'退款失败'));
		}
		
	}
	/*
		退款失败
	*/
	public function refund_fail(){
		$this->error(I('get.err_msg'),U('Order/edit',array('id'=>I('get.order_id'))));
	}

}