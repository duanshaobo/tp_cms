<?php
//订单管理
class OrderAction extends PublicAction{
	public function index(){
		import("@.ORG.Page");
		$db=M('order');
		if(isset($_GET['order_status'])){
			$order_status=I('get.order_status');
			$map=array('order_status'=>$order_status);
		}else{
			$map='';
		}
		//支付状态
		$pay_status=I('get.pay_status');
		if($pay_status=='nopay'){
			$map['pay_status']=0;
		}
		
		$so_key=I('get.key');
		$so_val=I('get.val');
		
		$begin_time=strtotime(I('get.begin_time'));
		$end_time=strtotime(I('get.end_time'));
		
		if(in_array($so_key,array('out_trade_no','mobile','consignee'))){
			if(!empty($so_val)&&!empty($so_val)){
				$map[$so_key]=array('like','%'.$so_val.'%');
			}
		}
		if($user_id=I('get.user_id')){
			$map['user_id']=$user_id;
		}
		
		if($begin_time>0){
			$map['order_time']=array('egt',$begin_time);
		}
		
		if($end_time>0){
			$map['order_time']=array('elt',$end_time);
		}
		
		
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		
		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['yongjin']=self::count_yj($val['id']);
			$list[$key]['expert']=M('wechat_user')->where(array('id'=>$val['expert_id']))->find();
		}
		
		$show = $Page->show();

		
		$this->assign('list',$list);
		
		//订单筛选条件
		$_SESSION['export_map']=$map;
		$this->assign('show',$show);
		$this->display();
	}
	
	/*
		计算订单佣金
	*/
	public function count_yj($order_id){
		$db=M('order_goods');
		$yongjin=0;
		$goods=$db->where(array('order_id'=>$order_id))->select();
		foreach($goods as $val){
			$yongjin+=$val['yongjin']*$val['goods_nums'];
		}
		return $yongjin;
	}
	
	
	//编辑
	public function edit(){
            $order_id=$map['id']=I('get.id');
            $db=M('order');
            //订单信息
            $data=$db->where($map)->find();
            if(empty($data)){
                $this->error('该订单已不存在！');
            }else{
				$data['order_user']=M('wechat_user')->field('id,nickname,name')->find($data['user_id']);
			}
			$expert=M('wechat_user')->where(array('expert_id'=>$data['uid']))->find();  
			$this->assign('expert',$expert);         
            //商品信息
           /* $order_goods=M('order_goods')->where(array('order_id'=>I('get.id')))->order('id desc')->select();
            $this->assign('order_goods',$order_goods);
			$goods_list=M('order_goods')->where(array('order_id'=>$order_id))->select();*/

            if($arr=$this->_post()){
               //订单处理逻辑
            }
			//下单用户信息
			$user=M('wechat_user')->find($data['uid']);
			$this->assign('user',$user);
			
			//佣金信息
			$yongjin=$data['yongjin']=self::count_yj($order_id);
			
			if($yongjin>0){
				
				$config=M('resale_config')->find(1);		//分佣配置
			
					$resaler=array();
					
					if($user['p_1']>0){
						$resaler1=M('wechat_user')->find($user['p_1']);			//一级分销
						$resaler1['yongjin']=$yongjin*($config['parent_1']*0.01);
						$resaler1['percent']=$config['parent_1'];
						$resaler1['role_name']='一级分销';
						//if($resaler1['role_id']==2){				//只有分销商才能分佣
							$resaler[1]=$resaler1;
						//}
						
					}
					
					if($user['p_2']>0){
						$resaler2=M('wechat_user')->find($user['p_2']);	//二级分销
						$resaler2['yongjin']=$yongjin*($config['parent_2']*0.01);
						$resaler2['percent']=$config['parent_2'];
						$resaler2['role_name']='二级分销';
						//if($resaler2['role_id']==2){				//只有分销商才能分佣
							$resaler[2]=$resaler2;
						//}
					}
					
					if($user['p_3']>0){
						$resaler3=M('wechat_user')->find($user['p_3']);	//三级分销
						$resaler3['yongjin']=$yongjin*($config['parent_3']*0.01);
						$resaler3['percent']=$config['parent_3'];
						$resaler3['role_name']='三级分销';
						//if($resaler3['role_id']==2){				//只有分销商才能分佣
							$resaler[3]=$resaler3;
						//}
					}
					
					//分销商信息
					$this->assign('resaler',$resaler);
					
				
			}
			
			
			
			//订单信息
			 $this->assign('data',$data);
			
			
			
            $this->display();
	}
	
	
	 /*
		修改订单状态 	
	 */
 
	 public function order_status(){
		$db=M('order_info');
		$id=I('get.id');			//订单id
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			
			if($arr['order_status']==2){
				$order_status='已发货';	
				//更新销量&&库存
				$this->update_goods_info($id);
				
			}elseif($arr['order_status']==3){
				$order_status=' 已签收';
			}
			//订单状态更新通知
			order_status_notice($id,$order_status);
			
			if($arr['order_status']==3){		//签收	【交易完成，升级为分销商】
				up_resaler($id);
			}
			echo 1;	
		}	
	 }
	
	/*
		更新销量和库存
	*/
	public function update_goods_info($order_id){
		$db=M('order_info');
		$order=$db->where(array('id'=>$order_id))->find();
		$goods=$db->where(array('order_id'=>$order_id))->select();
		foreach($goods as $key=>$val){
			$item=M('goods')->where(array('id'=>$val['goods_id']))->find();
			$data['sale_num']=$item['sale_num']+$val['goods_nums'];			//增加销量
			if($item['store_num']>=$val['store_num']){
				$data['store_num']=$item['store_num']-$val['store_num'];
			}else{
				$data['store_num']=0;
			}
			M('goods')->where(array('id'=>$val['goods_id']))->save($data);
		}
	}
	
	 /*
	 	更新订单信息
	 */	
	 public function order_update(){
		$db=M('order_info');
		$id=I('get.id');			//订单id
		if($arr=$this->_post()){
			$db->where(array('id'=>$id))->save($arr);
			echo 1;	
		}	
	 }

 
	 /*
		订单分佣【ajax】
	 */
	 public function fenyong(){
		 $db=M('order_info');
		 $id=I('get.id');			//订单id
		 $errcode=0;
		 if($arr=$this->_post()){
			 //订单信息
			$order=$db->where(array('id'=>$id))->find();
			//下单人信息
			$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
			//订单商品信息
			$goods=M('order_goods')->where(array('order_id'=>$order['id']))->select();
			//分佣配置信息
			$conf=M('resale_config')->find(1);
			//分销商信息
			$p_1=M('wechat_user')->where(array('id'=>$user['p_1']))->find();		//上一级
			$p_2=M('wechat_user')->where(array('id'=>$user['p_2']))->find();		//上二级
			$p_3=M('wechat_user')->where(array('id'=>$user['p_3']))->find();		//上三级
			
			//计算佣金
			$yongjin=0;
			foreach($goods as $val){
				$yongjin+=$val['yongjin']*$val['goods_nums'];
			}
			
			if($order['fy_status']==1){
				$errcode=1;								//订单已经分佣
				echo $errcode;
				die();
			}
			
			if($yongjin==0){
				$errcode=2;								//无佣金
				echo $errcode;
				die();
			}
			
			//佣金总额大于0 && 未分佣状态，进行分佣
			if($yongjin>0&&$order['fy_status']==0){
				$yj_1=$yongjin*$conf['parent_1']*0.01;			//一级分销佣金
				$yj_2=$yongjin*$conf['parent_2']*0.01;			//二级分销佣金
				$yj_3=$yongjin*$conf['parent_3']*0.01;			//三级分销佣金
				
				//一级分佣
				if($user['p_1']>0&&$yj_1>0){

					fenyong(array('uid'=>$user['p_1'],'money'=>$yj_1,'order_id'=>$id));
				}
				
				if($user['p_2']>0&&$yj_2>0){

					fenyong(array('uid'=>$user['p_2'],'money'=>$yj_2,'order_id'=>$id));
				}
				
				if($user['p_3']>0&&$yj_3>0){

					fenyong(array('uid'=>$user['p_3'],'money'=>$yj_3,'order_id'=>$id));
				}
				//修改订单分佣状态【已分佣】
				$db->where(array('id'=>$id))->save(array('fy_status'=>1));
			}
			
			echo $errcode;
			
			
		 }//end  if
		
	 }
	
	
	/*
		分佣信息页面
	*/
	public function _fenyong(){
		$db=M('order_info');
		$id=I('get.id');			//订单id
		
		$config=M('resale_config')->find(1);		//分佣配置
		
		$order=$db->find($id);
		
		$this->assign('order',$order);
		
		$goods=M('order_goods')->where(array('order_id'=>$id))->select();
		foreach($goods as $val){
			$goods_info=M('goods')->find($val['goods_id']);
			$yongjin+=$val['goods_nums']*$goods_info['yongjin'];
		}
		
		if($order['shop_id']){
			$resaler1=M('wechat_user')->find($order['shop_id']);			//一级分销
			$resaler1['yongjin']=$yongjin*($config['parent_1']*0.01);
			$resaler1['percent']=$config['parent_1'];
			$this->assign('resaler1',$resaler1);
			if($resaler1['parent_id']>0){
				$resaler2=M('wechat_user')->find($resaler1['parent_id']);	//二级分销
				$resaler2['yongjin']=$yongjin*($config['parent_2']*0.01);
				$resaler2['percent']=$config['parent_2'];
				$this->assign('resaler2',$resaler2);
			}
		}
		
		$this->assign('goods',$goods);
		$this->assign('yongjin',$yongjin);
		$water_db=M('money_water');
		if($this->_post()){
			//表单令牌
			if($db->autoCheckToken($_POST)){
				if(!empty($resaler1)){
					M('wechat_user')->where(array('id'=>$resaler1['id']))->setInc('money_account',$resaler1['yongjin']);
					//记录资金流水
					
					$water['user_id']=$resaler1['id'];
					$water['order_id']=$id;					//订单id
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
					$water['order_id']=$id;					//订单id
					$water['type']=1;						//收入
					$water['amount']=$resaler2['yongjin'];	//金额
					$water['way']='yongjin';						//分销佣金
					$water['way_name']='分销佣金';
					$water['posttime']=time();
					$water_db->add($water);
				}
				//修改订单分佣状态【已分佣】
				$db->where(array('id'=>$id))->save(array('fenyong_status'=>1));
				
				$this->success('操作成功',U('fenyong',array('id'=>$id)));
				
			}
		}else{
			$this->display();	
		}
	}
	
	/*
		更新支付状态
	*/
	public function update_pay_status(){
		$order_id=I('get.order_id');
		$db=M('order_info');
		$info=$db->find($order_id);
		switch($info['pay_status']){
			case '0':
				$data=array('pay_status'=>1,'pay_time'=>time());
			break;
			case '1':
				$data=array('pay_status'=>0);
			break;
			/*case '2':
				$data=array('pay_status'=>0);
			break;*/
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
		退款申请
	*/
	public function refund_list(){
		import("@.ORG.Page");
		$db=M('order_refund');
		
		$map='';
		$count = $db->where($map)->count();
		$Page = new Page($count,10);
		$show=$Page->show();
		$this->assign('show',$show);
		
		$list=$db->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach($list as $key=>$val){
			$list[$key]['order']=M('order_info')->find($val['order_id']);
		}
		
		$this->assign('list',$list);
		$this->display();
		
	}
	
	/*
		退款详情
	*/
	public function refund_detail(){
		$db=M('order_refund');
		$id=I('get.id');
		//退款申请
		$info=$db->find($id);
		$this->assign('info',$info);
		//订单信息
		$data=M('order_info')->find($info['order_id']);
		$this->assign('data',$data);
		//下单人
		$user=M('wechat_user')->find($info['user_id']);
		$this->assign('user',$user);
		//商品信息
		$order_goods=M('order_goods')->where(array('order_id'=>$info['order_id']))->order('id desc')->select();
		$this->assign('order_goods',$order_goods);
		
		$this->display();
	}
	
	/*
		售后处理
	*/
	public function refund_handle(){
		$db=M('order_refund');
		$id=I('get.id');
		$refund=$db->where(array('id'=>$id))->find();
		if($arr=$this->_post()){
			$arr['admin_user']=I('session.username');
			$arr['admin_id']=I('session.uid');
			$db->where(array('id'=>$id))->save($arr);
			if($refund['type']==1){			//退款
				//修改订单支付状态为2[已退款]
				M('order_info')->where(array('id'=>$refund['order_id']))->save(array('pay_status'=>2));
				//佣金撤回
				self::yongjin_refund($refund['order_id']);
			}
			
			$this->success('操作成功',U('refund_detail',array('id'=>$id)));		
		}
	}
	
	/*
		佣金撤回
	*/
	private function yongjin_refund($order_id){
		//当前订单的分佣历史记录
		$fy_log=M('money_water')->where(array('order_id'=>$order_id,'type'=>1))->select();
		if(!empty($fy_log)){
			foreach($fy_log as $key=>$val){
				$log['uid']=$val['uid'];
				$log['type']=2;
				$log['amount']=$val['amount'];
				$log['way']='yongjin_refund';		//佣金撤回
				$log['remark']='订单退款，佣金撤回';
				$log['order_id']=$val['order_id'];
				//$uid,$type,$amount,$way,$remark,$order_id
				money_change($log['uid'],$log['type'],$log['amount'],$log['way'],$log['remark'],$log['order_id']);
			}	
		}
	}
	
	
	
	/*
		 立即退款
	*/
	public function refund(){
		$db=M('order_refund');
		$id=I('get.id');
		//退款申请
		$info=$db->find($id);
		$this->assign('info',$info);
		//订单信息
		$data=M('order_info')->find($info['order_id']);
		$this->assign('data',$data);
		$this->assign('info',$info);
		$this->display();
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
	
	/*
		导出订单excel

    	导出数据为excel表格
    	@param $data    一个二维数组,结构如同从数据库查出来的数组
    	@param $title   excel的第一行标题,一个数组,如果为空则没有标题
    	@param $filename 下载的文件名
    	@examlpe 
    	$stu = M ('User');
    	$arr = $stu -> select();
    	
		exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
	
		//$data=array(),$title=array(),$filename='report'
	*/
 public function export_excel(){
	 $map=$_SESSION['export_map']?$_SESSION['export_map']:'';
	 
	//excel名称
	$filename=date('YmdHis');
	
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");  
    header("Content-Disposition:attachment;filename=".$filename.".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
	
	
	$field='id,out_trade_no,total_fee,pay_way,pay_status,consignee,mobile,province,city,district,address,order_time';
	$data=M('order_info')->where($map)->field($field)->order('id desc')->select();

	//dump($data);die();

	foreach($data as $key=>$val){
		if($val['pay_status']==1){
			$data[$key]['pay_status']='已支付';
		}else{
			$data[$key]['pay_status']='未支付';
		}
		
		if($val['pay_way']==1){
			$data[$key]['pay_way']='微信支付';
		}elseif($val['pay_way']==2){
			$data[$key]['pay_way']='支付宝';
		}elseif($val['pay_way']==3){
			$data[$key]['pay_way']='银联支付';
		}
		
		$data[$key]['out_trade_no']=$val['out_trade_no'];
		$data[$key]['address']=$val['province'].'-'.$val['city'].'-'.$val['district'].'-'.$val['address'];
		unset($data[$key]['province']);
		unset($data[$key]['city']);
		unset($data[$key]['district']);
		$data[$key]['order_time']=date('Y-m-d H:i',$val['order_time']);
		$goods=M('order_goods')->where(array('order_id'=>$val['id']))->select();
		foreach($goods as $item){
			if(!empty($_SESSION['export_goods_name'])){
				if(strpos($item['goods_name'],$_SESSION['export_goods_name'])!==false){
					$data[$key]['goods_name']=$item['goods_name'];
					$data[$key]['goods_nums']=$item['goods_nums'];
				}
			}else{
				$data[$key]['goods'].='【商品名称：'.$item['goods_name'].'-'.$item['goods_norm'].',商品数量:'.$item['goods_nums'].'】';
			}
			
			
		}
		unset($goods);
		unset($data[$key]['id']);
	}
	if(!empty($_SESSION['export_goods_name'])){
		$title=array('订单编号','订单金额','支付方式','是否支付','收货人','联系电话','收货地址','下单日期','商品信息','商品数量');
	}else{
		$title=array('订单编号','订单金额','支付方式','是否支付','收货人','联系电话','收货地址','下单日期','商品信息');
	}
	
	
	
	
    //导出xls 开始
    if (!empty($title)){
        foreach ($title as $k => $v) {
            $title[$k]=iconv("UTF-8", "GB2312",$v);
        }
        $title= implode("\t", $title);
        echo "$title\n";
    }
    if (!empty($data)){
        foreach($data as $key=>$val){
            foreach ($val as $ck => $cv) {
                $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
            }
            $data[$key]=implode("\t", $data[$key]);
            
        }
        echo implode("\n",$data);
    }
	
	
	
 }
 

 
	

}