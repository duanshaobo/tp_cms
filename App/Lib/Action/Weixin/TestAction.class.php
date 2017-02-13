<?php
/*
	微信红包控制器
	
	微信红包接口需要安全证书，安全证书需在微信商户后台下载：
	
	apiclient_cert.pem
	apiclient_key.pem
	rootca.pem
	
	
*/

class TestAction extends Action{
	
	public function aa(){
		$pager=M('exam_pager')->where()->order('id asc')->select();
		foreach($pager as $val){
			//$_p[$val['title']]=$val['id'];
			$count=M('exam_pager')->where(array('title'=>$val['title']))->count();
			if($count>1){
				$info=M('exam_pager')->where(array('title'=>$val['title']))->order('id desc')->find();
				M('exam_pager')->delete($info['id']);
			}
		}
		/*$db=M('exam_topic');
		$map['id']=array('egt',3201);
		$list=$db->where($map)->order('id asc')->select();
		foreach($list as $val){
			$preg="/(\((\d)+\/(\d)+\))/";
			$data['title']=preg_replace($preg,'',$val['title']);
			//dump($data);
			$data['p_id']=$_p[$val['p_title']];
			$db->where(array('id'=>$val['id']))->save($data);
		}*/
	}
	
	public function pager(){
		$db=M('exam_pager');
		$list=$db->where()->select();
		foreach($list as $key=>$val){
			$data['year']=mb_substr($val['title'],-7,7,'utf-8');
			//dump($data);
			$db->where(array('id'=>$val['id']))->save($data);
		}
		/*$list=M('content')->where()->select();
		foreach($list as $key=>$val){
			$data['title']=$val['title'];
			$data['statsu']=1;
			$data['posttime']=time();
			
			$db->add($data);
			
			
		}*/
	}
	
	/*
		导出试题数据
	*/
	public function topic(){
		$db=M('exam_topic');
		//$map=array('title'=>'心优雅题库-历年真题-国家心理咨询师2级/2013.05');
		//$map=array('id'=>array('elt',100));
		//$map="id between 101 and 100";
		//$map['id']=array('in',array(121,122,123,124,125,126,127,128,129,130));
		//$map['id']=1;
		$list=M('content')->where($map)->select();
		//dump($list);die();
		foreach($list as $key=>$val){
			$data['p_title']=$val['title'];
			$topic=explode("###",$val['ask']);
			$option=explode("###",$val['xuanxiang']);
			$checked=explode("###",$val['cankao']);
			$remark=explode("###",$val['fenxi']);
			
			$count=count($topic);
			
			for($i=0;$i<1;$i++){
				//dump($option[$i]);
				$options=explode(')',$option[$i]);
				
				$data['title']=$topic[$i];
				$data['options']=$checked[$i];
				$data['parse']=$remark[$i];
				
				$options=explode('(',$option[$i]);
				$data['A']='('.$options[1];
				$data['B']='('.$options[2];
				$data['C']='('.$options[3];
				$data['D']='('.$options[4];
				if(mb_substr($data['title'],-2,2,'utf-8')=='单选'){
					$data['type']=1;
				}else{
					$data['type']=2;
				}
				$id=$db->add($data);
				//dump($data);//die();
				echo $id;echo "<br/>";
			}
			//dump($topic);die();
			//dump($remark);die();
			//dump($checked);die();
			
			//dump($option);die();
		}
		//dump($list);
	}
	
	public function wxhb(){
		wxhb(1,2);
	}
	
	public function msg(){
	
		order_pay_ok_notice(1);
		
	}
	
	/*
		删除微信access_token
	*/
	public function clear(){
		dump(is_sub(1));
		unlink("./Data/wxcache/access_token.json");
		unlink("./Data/wxcache/jsapi_ticket.json");
	}
	/*
		测试
	*/
	public function up_fx(){
		$order_id=48;
		//升级条件
		$conf=M('resale_config')->find(1);
		//订单信息
		$order=M('order_info')->where(array('id'=>$order_id))->find();
		if($order['total_fee']>=$conf['resaler_single_order']){
			//do_up_resaler($order['uid']);
			echo 'yes1';
		}else{
			//查询累计消费金额
			$total_order_fee=M('order_info')->where(array('uid'=>$order['uid']))->sum('total_fee');
			if($total_order_fee>=$conf['resaler_total_order']){
				//do_up_resaler($order['uid']);
				echo 'yes2';
			}else{
				echo 'no';
			}
		}
	}
	
	
	/*
	*	$return_arr返回参数说明：
	*	Array
		(
			[return_code] => FAIL
			[return_msg] => 帐号余额不足，请用户充值或更换支付卡后再支付.
			[result_code] => FAIL
			[err_code] => NOTENOUGH
			[err_code_des] => 帐号余额不足，请用户充值或更换支付卡后再支付.
			[mch_billno] => 02131555451625
			[mch_id] => 10011481
			[wxappid] => wxe7e5a985ba3e3b17
			[re_openid] => oGRGsuPd1v5e4OBPuJhksjRCqr4c
			[total_amount] => 100
		)
		Array
		(
			[return_code] => SUCCESS
			[return_msg] => 发放成功.
			[result_code] => SUCCESS
			[err_code] => 0
			[err_code_des] => 发放成功.
			[mch_billno] => 02131606163557
			[mch_id] => 10011481
			[wxappid] => wxe7e5a985ba3e3b17
			[re_openid] => oGRGsuPd1v5e4OBPuJhksjRCqr4c
			[total_amount] => 100
		)
	*
	*/
	//发红包
	
	public function test(){
		//引入微信红包类
		import("@.ORG.WxRedPack");
		
		$db=M('pubchatuser');
		//获取公众账号信息
		$option=$db->field('appid,appsecret,mchid,partnerkey')->find(1);
		
		//实例化红包类
		$obj=new WxRedPack($option);
		//接口数据
		$money=rand($conf['min_value'],$conf['max_value']);	//随机红包金额【1-2元】
		$post_arr=array();
		$post_arr['mch_billno']=date('mdHis',time()).rand(1111,9999);		//订单号
		$post_arr['mch_id']=$option['mchid'];	//商户号
		$post_arr['wxappid']=$option['appid'];
		$post_arr['nick_name']=$conf['nick_name'];		//红包提供方名称
		$post_arr['send_name']=$conf['send_name'];		//红包发送方名称
		$post_arr['re_openid']=$this->wechatid;			//红包接收者openid='oGRGsuPd1v5e4OBPuJhksjRCqr4c'
		$post_arr['total_amount']=$money;			//红包金额(分)(发放金额、最小金额、最大金额必须相等)
		$post_arr['min_value']=$money;				//最小红包金额(发放金额、最小金额、最大金额必须相等)
		$post_arr['max_value']=$money;				//最大红包金额(发放金额、最小金额、最大金额必须相等)
		$post_arr['total_num']=1;				//红包发放总人数(total_num必须为1)
		$post_arr['wishing']=$conf['wishing'];			//红包祝福语
		$post_arr['client_ip']=I('server.SERVER_ADDR');//调用接口的机器IP(应该是服务器IP)
		$post_arr['act_name']=$conf['act_name'];			//活动名称
		$post_arr['remark']=$conf['remark']='测试红包';			//备注信息
		//========================非必填项(预留参数)==========================//
		if(!empty($conf['logo_imgurl'])){
			$post_arr['logo_imgurl']=$conf['logo_imgurl'];		//商户logo
		}
		//$post_arr['share_content']=$conf['share_content'];	//分享文案
		//$post_arr['share_url']=$conf['share_url'];			//分享链接
		//$post_arr['share_imgurl']=$conf['share_imgurl'];	//分享的图片url			
		//========================非必填项==========================//
		$post_arr['nonce_str']=$obj->createNoncestr();				//随机字符串，不长于32位
		//签名
		$post_arr['sign']=$obj->getSign($post_arr);
		//调用发送红包接口
		$return_arr=$obj->sendRedPack($post_arr);
		/*echo "<pre>";
		print_r($return_arr);*/
		if($return_arr['result_code']=='SUCCESS'&&$return_arr['result_code']=='SUCCESS'){
			echo '发送红包成功';
		}
	}
	
	
}