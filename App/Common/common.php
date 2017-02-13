<?php
/**
 * 常用公共函数库
 *
 */
 
/*
	解决json_encode汉字被编码问题
*/ 
function decodeUnicode($str){
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
        create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ),
        $str);
}
 
 function skill_id2name($id_str){
	$id_arr=array_filter(explode(',',$id_str));
	$db=M('skill');
	$catelist=$db->select();
	foreach($catelist as $key=>$val){
		$c_list[$val['id']]=$val['title'];
	}
	foreach($id_arr as $val){
		$arr_id[]=$c_list[$val];
	}
	$name_str=implode(',',$arr_id);
	return $name_str;
}

/*
	Intro:校验18位身份证号
	Param:$id
	Date:2016-04-06
	Return:
	Array
	(
		[errCode] => 0
		[retMsg] => success
		[retData] => Array
			(
				[address] => 陕西省西安市新城区
				[sex] => 1
				[birthday] => 1967-11-02
			)
	
	)
*/ 
function Id18($id){
	//校验码
	$check_arr=array('1','0','X','9','8','7','6','5','4','3','2');
	//前17位加权值
	$weight_arr=array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
	for($i=0;$i<17;$i++){
		//echo "$id[$i]=>$weight_arr[$i]<br/>";
		$sum+=$weight_arr[$i]*$id[$i];
	}
	$mod=$sum%11;
	
	$area_code=substr($id,0,6);
	$sex_code=substr($id,14,3);
	$birth_code=substr($id,6,8);

	if($id[17]==$check_arr[$mod]){
		$return['errCode']=0;
		$return['retMsg']='success';
		$info=M('area_code')->where(array('code'=>$area_code))->find();
		$return['retData']['address']=$info['area'];
		$return['retData']['sex']=$sex_code%2==0?2:1;						//1男2女
		$return['retData']['birthday']=date('Y-m-d',strtotime($birth_code));
	}else{
		$return['errCode']=1;
		$return['retMsg']='身份证号码不合法';
	}
	return $return;
}
 

/*
	佣金撤回
*/
function yongjin_refund($order_id){
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

//===================微信接口函数=================//

/*
	获取规格名称
*/
function norm_name($id){
	$res=M('goods_norm')->where(array('id'=>$id))->getField('title');
	return $res;
}

/*
	微信红包接口
	params:uid,amount
*/
function wxhb($uid,$amount){
	header('content-type:text/html;charset=utf-8');
	//引入微信红包类
	import("@.ORG.WxRedPack");
	//用户信息
	$user=M('wechat_user')->field('id,wechatid')->where(array('id'=>$uid))->find();
	$db=M('wechat_config');
	//获取公众账号信息
	$option=$db->field('appid,appsecret,mchid,partnerkey')->find(1);
	$conf=array(
		'nick_name'=>'唯道康络',
		'send_name'=>'唯道康络',
		'act_name'=>'佣金提现红包',
		'wishing'=>'祝您财源滚滚，感谢您一路上的支持',
		'remark'=>'祝您财源滚滚，感谢您一路上的支持',
		'logo_imgurl'=>'http://'.I('server.HTTP_HOST').'/Public/Weixin/images/14357466811435746681.png'
	);
	//实例化红包类
	$obj=new WxRedPack($option);
	//接口数据
	//$money=rand($conf['min_value'],$conf['max_value']);	//随机红包金额【1-2元】
	$money=$amount*100;		//【单位：分】
	$post_arr=array();
	$post_arr['mch_billno']='TX'.date('YmdHis',time()).rand(1111,9999);//订单号
	$post_arr['mch_id']=$option['mchid'];	//商户号
	$post_arr['wxappid']=$option['appid'];
	$post_arr['nick_name']=$conf['nick_name'];		//红包提供方名称
	$post_arr['send_name']=$conf['send_name'];		//红包发送方名称
	$post_arr['re_openid']=$user['wechatid'];//'oDKY5xGsf9QeBNnOI3-a2DcZl_X0';//
	
	$post_arr['total_amount']=$money;			//红包金额(分)(发放金额、最小金额、最大金额必须相等)
	//$post_arr['min_value']=$money;				//最小红包金额(发放金额、最小金额、最大金额必须相等)
	//$post_arr['max_value']=$money;				//最大红包金额(发放金额、最小金额、最大金额必须相等)
	$post_arr['total_num']=1;					//红包发放总人数(total_num必须为1)
	$post_arr['wishing']=$conf['wishing'];			//红包祝福语
	$post_arr['client_ip']=I('server.SERVER_ADDR');//调用接口的机器IP(应该是服务器IP)
	$post_arr['act_name']=$conf['act_name'];		//活动名称
	$post_arr['remark']=$conf['remark'];			//备注信息
	//========================非必填项(预留参数)==========================//
	if(!empty($conf['logo_imgurl'])){
		$post_arr['logo_imgurl']=$conf['logo_imgurl'];		//商户logo
	}
	//$post_arr['share_content']=$conf['share_content'];	//分享文案
	//$post_arr['share_url']=$conf['share_url'];			//分享链接
	//$post_arr['share_imgurl']=$conf['share_imgurl'];		//分享的图片url			
	//========================非必填项==========================//
	$post_arr['nonce_str']=$obj->createNoncestr();				//随机字符串，不长于32位
	//签名
	$post_arr['sign']=$obj->getSign($post_arr);
	//调用发送红包接口
	$return_arr=$obj->sendRedPack($post_arr);
	if($return_arr['return_code']=='SUCCESS'&&$return_arr['result_code']=='SUCCESS'){
		$return=1;				//成功
		$log['status']=1;
	}else{
		$return=2;				//失败
		$log['status']=2;
	}
	$log['uid']=$uid;
	$log['openid']=$return_arr['re_openid'];
	$log['total_amount']=$return_arr['total_amount']*0.01;			//单位：元
	$log['mch_billno']=$return_arr['mch_billno'];
	$log['return_code']=$return_arr['return_code'];
	$log['result_code']=$return_arr['result_code'];	
	$log['return_msg']=$return_arr['return_msg'];
	$log['err_code']=$return_arr['err_code'];
	$log['err_code_des']=$return_arr['err_code_des'];
	$log['send_listid']=$return_arr['send_listid'];
	$log['posttime']=time();
	//记录红包日志
	M('wechat_hb_list')->add($log);		
	return $return;
	//echo "<pre>";
	//print_r($return_arr);die();
}
/*
	查询用户是否关注公众号
*/
function is_sub($uid){
	import('@.ORG.Wxhelper');
	$wxconfig=M('wechat_config')->find(1);
	$wxhelper=new Wxhelper($wxconfig);
	//用户id
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$ret=$wxhelper->get_user_info($user['wechatid']);
	if($ret['subscribe']==1){
		return true;
	}else{
		return false;
	}
}

/*
	订单分佣
*/

function order_fenyong($order_id){
	 $db=M('order_info');
	 $id=$order_id;			//订单id
	 $errcode=0;
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
	
 }
 
 /*
	 分销佣金结算
	 param:array('uid','money','order_id')
*/
function fenyong($arr){
	//资金变动
	money_change($arr['uid'],1,$arr['money'],'yongjin','订单分佣',$arr['order_id']);
}

/*
	升级分销商
*/

function up_resaler($order_id){
	//升级条件
	$conf=M('resale_config')->find(1);
	//订单信息
	$order=M('order_info')->where(array('id'=>$order_id))->find();
	if($order['total_fee']>=$conf['resaler_single_order']){
		do_up_resaler($order['uid']);
	}else{
		//查询累计消费金额
		$total_order_fee=M('order_info')->where(array('uid'=>$order['uid']))->sum('total_fee');
		if($total_order_fee>=$conf['resaler_total_order']){
			do_up_resaler($order['uid']);
		}
	}
	
}
function do_up_resaler($uid){
	$db=M('wechat_user');
	$user=$db->where(array('id'=>$uid))->find();
	if($user['role_id']==1){
		$db->where(array('id'=>$uid))->save(array('role_id'=>2));	
		//模板消息通知用户，升级分销商
		up_resaler_notice($uid);
	}
	return 1;
}

/*
	 微信模板消息【通用函数】
	 
	$tpl_arr=array();			
	$tpl_arr['touser']='oQusRs-uFBANUQFuEqbJ7VphdO2s';			//bruce
	$tpl_arr['template_id']='5caLnApJcxhfRRM2TDBM_jauzs8PFjzD0Vy0wStDRIQ';	
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Plugin/chat_list');	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['content']['value']='您好，您有新消息，请注意查收！';
	wx_tpl_msg($tpl_arr);
*/
function wx_tpl_msg($arr){
	import('@.ORG.Wxhelper');
	$wxconfig=M('wechat_config')->find(1);
	$wxhelper=new Wxhelper($wxconfig);
	$rs=$wxhelper->send_tpl_msg($arr);
	return $rs;
}

/*
	资金变动提醒
*/
function  money_change_notice($uid,$type,$amount,$remark){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['money_change'];//'-tXdJLs7_jblv6kTEsyu3ytLH9hnHNUaDfU81rz-d-M';
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/fund');		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您的账户资金发生以下变动：';
	$tpl_arr['data']['first']['color']='red';
	if($type==1){
		$type_name='收入';
	}elseif($type==2){
		$type_name='支出';
	}
	$tpl_arr['data']['keyword1']['value']=$type_name;							//类型
	$tpl_arr['data']['keyword2']['value']=$amount.'元';							//金额						
	$tpl_arr['data']['keyword3']['value']=date('Y-m-d H:i:s',time());			//时间
	$tpl_arr['data']['remark']['value']='请您注意查看资金账户,感谢您的关注！';
	$tpl_arr['data']['remark']['color']='red';
	wx_tpl_msg($tpl_arr);
}

/*
	提现申请通知
*/
function take_money1($uid,$money){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['take_money'];//'-tXdJLs7_jblv6kTEsyu3ytLH9hnHNUaDfU81rz-d-M';
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/take_money_list');		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您于'.date('Y-m-d H:i:s',time()).'成功申请提现';
	$tpl_arr['data']['first']['color']='red';
	$tpl_arr['data']['keyword1']['value']=$money.'元';									//申请金额
	$tpl_arr['data']['keyword2']['value']='0元';									//手续费
	$tpl_arr['data']['keyword3']['value']=$money.'元';								//实际到账
	$tpl_arr['data']['keyword4']['value']='唯道康络微商城提现';
	$tpl_arr['data']['remark']['value']='我们会尽快处理您的提现申请，请您耐心等待,如有疑问，请联系客服';
	$tpl_arr['data']['remark']['color']='red';
	wx_tpl_msg($tpl_arr);
}

/*
	提现到账通知
*/
function take_money2($uid,$money){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();

	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['take_money2'];	//'w4tcl-xcWuQY1BzSS2-iMWpkrcuAKq3llJ5_7nbZWUo';
		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您的提现申请已成功受理，请您注意查收';
	$tpl_arr['data']['first']['color']='red';
	$tpl_arr['data']['money']['value']=$money.'元';								//到账金额
	$tpl_arr['data']['timet']['value']=date('Y-m-d H:i:s',time());			//到账时间
	$tpl_arr['data']['remark']['value']='如有疑问，请联系客服';
	wx_tpl_msg($tpl_arr);
}


/*
	 邀请关注成功
*/
function subscribe_notice($uid){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$p_user=M('wechat_user')->where(array('id'=>$user['p_1']))->find();	//上级用户
	//file_put_contents('user',var_export($user,true));
	//file_put_contents('p_user',var_export($p_user,true));
	
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$p_user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['subscribe'];//'L_gTGA_5BLAAeoWcSrbOoVRNXkB2LopNz4H9wgABQN4';
		
	$tpl_arr['topcolor']='#D76729';	
	
	$tpl_arr['data']['first']['value']='您好，以下会员通过您的邀请关注了我们';
	$tpl_arr['data']['first']['color']='#D76729';
	$tpl_arr['data']['keyword1']['value']=$user['nickname'];								//昵称
	$tpl_arr['data']['keyword2']['value']=date('Y-m-d H:i:s',time());
	$tpl_arr['data']['keyword3']['value']=get_user($user['p_1'],'nickname');				//邀请人
	$tpl_arr['data']['remark']['value']='如有疑问，请联系客服';
	wx_tpl_msg($tpl_arr);
}

/*
	订单提交成功【提醒上级用户】
*/
function order_add_ok_parent_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	//上级用户列表
	$map=array('id'=>array('in',array($user['p_1'],$user['p_2'],$user['p_3'])));
	$parents=M('wechat_user')->where($map)->select();
	$tpl_list=M('wechat_tpl_msg')->find(1);			
	//循环发送模板消息
	foreach($parents as $val){
		//模板消息
		$tpl_arr=array();
		$tpl_arr['touser']=$val['wechatid'];
		$tpl_arr['template_id']=$tpl_list['order_add'];//'dK2f2B2Cg2MuHtXAhP1YhS2Hh9aajKy4DbbPsQXYfu8';//
		//$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
		$tpl_arr['topcolor']='#F5390D';	
		
		$tpl_arr['data']['first']['value']='您的下线用户【'.$user['nickname'].'】的订单已提交成功';
		$tpl_arr['data']['first']['color']='red';
		
		$tpl_arr['data']['orderID']['value']=$order['out_trade_no'];
		$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
		$tpl_arr['data']['backupFieldName']['value']='商品信息:';
		$tpl_arr['data']['backupFieldData']['value']=$goods['goods_name'].'...';
		//$tpl_arr['data']['remark']['value']='请您及时付款，以便我们尽快为您发货';
		$rs=wx_tpl_msg($tpl_arr);
		
	}
	
}

/*
	订单支付成功通知【上级通知】
*/
function order_pay_ok_parent_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	//上级用户列表
	$map=array('id'=>array('in',array($user['p_1'],$user['p_2'],$user['p_3'])));
	$parents=M('wechat_user')->where($map)->select();
	
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	foreach($parents as $val){
		$tpl_arr=array();
		$tpl_arr['touser']=$val['wechatid'];
		$tpl_arr['template_id']=$tpl_list['order_pay'];//'dRsdACm5kgQOj_yXGsw9J7chGenATbG9D0lhiwOej8U';	//
		//$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
		$tpl_arr['topcolor']='#F5390D';	
		$tpl_arr['data']['first']['value']='您的下线用户【'.$user['nickname'].'】已成功支付订单';
		$tpl_arr['data']['first']['color']='red';
		$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
		$tpl_arr['data']['orderProductName']['value']=$goods['goods_name'].'...';
		$tpl_arr['data']['remark']['value']='如有问题请致电或直接在微信留言，我们将第一时间为您服务！';
		$rs=wx_tpl_msg($tpl_arr);
		
	}
	
	
}

/*
	订单提交成功
	
*/
function order_add_ok_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['order_add'];//'dK2f2B2Cg2MuHtXAhP1YhS2Hh9aajKy4DbbPsQXYfu8';//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
	$tpl_arr['topcolor']='#F5390D';	
	$tpl_arr['data']['first']['value']='您的订单已提交成功';
	$tpl_arr['data']['first']['color']='red';
	
	$tpl_arr['data']['orderID']['value']=$order['out_trade_no'];
	$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
	$tpl_arr['data']['backupFieldName']['value']='商品信息:';
	$tpl_arr['data']['backupFieldData']['value']=$goods['goods_name'].'...';
	$tpl_arr['data']['remark']['value']='请您及时付款，以便我们尽快为您发货';
	$rs=wx_tpl_msg($tpl_arr);
}

/*
	订单支付成功通知
*/
function order_pay_ok_notice($order_id){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('order_id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['order_pay'];//'dRsdACm5kgQOj_yXGsw9J7chGenATbG9D0lhiwOej8U';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
	$tpl_arr['topcolor']='#F5390D';	
	$tpl_arr['data']['first']['value']='我们已收到您的货款，开始为您打包商品，请耐心等待';
	$tpl_arr['data']['first']['color']='red';
	$tpl_arr['data']['orderMoneySum']['value']=$order['total_fee'];
	$tpl_arr['data']['orderProductName']['value']=$goods['goods_name'].'...';
	$tpl_arr['data']['remark']['value']='如有问题请致电或直接在微信留言，我们将第一时间为您服务！';
	wx_tpl_msg($tpl_arr);
}

/*
	订单状态改变提醒
*/
function order_status_notice($order_id,$order_status){
	$order=M('order_info')->where(array('id'=>$order_id))->find();	
	$goods=M('order_goods')->where(array('id'=>$order_id))->find();
	$user=M('wechat_user')->where(array('id'=>$order['uid']))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['order_status'];//'7GW8pelgEaUqam2QsJTGDSW0IIxnJIrrdeL3tyYW4yQ';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/order_detail',array('id'=>$order_id));	
	$tpl_arr['topcolor']='#F5390D';	
	$tpl_arr['data']['first']['value']='尊敬的'.$user['nickname'].'，您的订单状态已更新！';
	$tpl_arr['data']['first']['color']='red';
	
	$tpl_arr['data']['OrderSn']['value']=$order['out_trade_no'];
	$tpl_arr['data']['OrderStatus']['value']=$order_status;
	$tpl_arr['data']['remark']['value']='物流信息:'.$order['express_name']."\n快递单号:".$order['express_no']."\n快递电话:".$order['express_tel'];
	wx_tpl_msg($tpl_arr);
}

/*	
	积分变动通知
*/
function jifen_change_notice($uid,$type,$jifen,$remark){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['jifen_change'];//'Gx-yvl3fpVIONZTn-1D3xV7ihi4-irAfyLb3DErIKS0';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Ucenter/index');	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您的积分账户变更如下';
	$tpl_arr['data']['first']['color']='red';
	if($type==1){
		$type_name='增加';
		$tpl_arr['data']['FieldName']['value']='收入途径';
	}elseif($type==2){
		$type_name='减少';
		$tpl_arr['data']['FieldName']['value']='支出途径';
	}
	
	$tpl_arr['data']['Account']['value']=$remark;
	
	$tpl_arr['data']['change']['value']=$type_name;
	$tpl_arr['data']['CreditChange']['value']=$jifen.'(积分)';
	$tpl_arr['data']['CreditTotal']['value']=$user['jifen'].'(积分)';
	$tpl_arr['data']['remark']['value']='您可以用积分在商城兑换礼品！';
	wx_tpl_msg($tpl_arr);
}

/*	
	访客消息
*/
function visit_notice($f_uid,$t_uid){
	$f_user=M('wechat_user')->where(array('id'=>$f_uid))->find();
	$t_user=M('wechat_user')->where(array('id'=>$t_uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	
	$tpl_arr=array();
	$tpl_arr['touser']=$t_user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['visit'];//'LEsVFKwICA38Undq7kqeBrdpJEtOs9i5FxycaAFC4Zs';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Plugin/chat_room',array('id'=>$f_uid));
	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您好，有好友访问了您的推广链接';
	$tpl_arr['data']['first']['color']='blue';
	$tpl_arr['data']['keynote1']['value']=$f_user['nickname'];
	$tpl_arr['data']['keynote2']['value']=date('Y-m-d H:i:s',time());
	//$tpl_arr['data']['remark']['value']='请点击详情打开会话页面，立即查看并回复消息！';
	wx_tpl_msg($tpl_arr);
}

/*
	好友消息提醒
*/
function chat_notice($f_uid,$t_uid){
	$f_user=M('wechat_user')->where(array('id'=>$f_uid))->find();
	$t_user=M('wechat_user')->where(array('id'=>$t_uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	
	$tpl_arr=array();
	$tpl_arr['touser']=$t_user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['chat'];//'LEsVFKwICA38Undq7kqeBrdpJEtOs9i5FxycaAFC4Zs';	//
	$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Plugin/chat_list');	
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您好，您有新消息，请注意查收';
	$tpl_arr['data']['first']['color']='blue';
	$tpl_arr['data']['keynote1']['value']=$f_user['nickname'];
	$tpl_arr['data']['keynote2']['value']=date('Y-m-d H:i:s',time());
	$tpl_arr['data']['remark']['value']='请点击详情打开会话页面，立即查看并回复消息！';
	wx_tpl_msg($tpl_arr);
}


/*
	升级分销商提醒
*/
function up_resaler_notice($uid){
	$user=M('wechat_user')->where(array('id'=>$uid))->find();
	$tpl_list=M('wechat_tpl_msg')->find(1);								//模板消息
	
	
	$tpl_arr=array();
	$tpl_arr['touser']=$user['wechatid'];
	$tpl_arr['template_id']=$tpl_list['up_resaler'];//'2_vlxJO8KffWzVdiIgu6L-koogbzDu0fySByKv0QgdU';	//
	$tpl_arr['topcolor']='#FF0000';	
	$tpl_arr['data']['first']['value']='您好，您已成功升级为分销商';
	$tpl_arr['data']['first']['color']='red';
	$tpl_arr['data']['cardNumber']['value']=$user['id'];
	$tpl_arr['data']['type']['value']='微商城';
	$tpl_arr['data']['address']['value']='唯道康络';
	$tpl_arr['data']['VIPName']['value']=$user['name'];
	$tpl_arr['data']['VIPPhone']['value']=$user['mobile'];
	$tpl_arr['data']['expDate']['value']='长期有效';
	$tpl_arr['data']['remark']['value']='如有疑问，请咨询我们的客服人员！';
	wx_tpl_msg($tpl_arr);
}


//===================微信接口函数=================//



/*
	提现状态
*/
function apply_status($state){
	$arr=array(0=>'<font color="red">等待处理</font>',
			   1=>'<font color="green">提现成功</font>',
			   2=>'<font color="red">提现失败</font>');
	return $arr[$state];
}
/*
/*
	根据id获取用户信息对应字段
*/
function get_user($uid,$field){
	$info=M('wechat_user')->where(array('id'=>$uid))->getField($field);
	return $info;
}
/*
	php无限分级
*/
function order($array,$pid=0,$level=0){
	$arr = array();
	foreach($array as $v){
		if($v['fup']==$pid){	//||$v['parent_id']==$pid
			$v['pre']=str_repeat(' — ',$level);
			$arr[] = $v;
			$arr = array_merge($arr,order($array,$v['id'],$level+1));
		}
	}
	return $arr;
}
/*
	订单状态
*/
function order_status($state){
	$arr=array(
	1=>'<font color="red">未发货</font>',
	2=>'<font color="green"><b>已发货</b></font>',
	3=>'<font color="green"><b>已签收</b></font>');
	return $arr[$state];
}
/*
	获取品牌名称
*/
function get_brandname($bid){
	$db=M('goods_brand');
	$info=$db->find($bid);
	return $info['name'];	
}
/*
	获取分类名称
*/
function get_catename($cid){
	$db=M('goods_category');
	$info=$db->find($cid);
	return $info['name'];	
}
/*
	获取性别
*/
function get_sex($sex){
	$arr=array(0=>'未知',1=>'男',2=>'女');
	return $arr[$sex];
}

function node_merge($node,$access=null,$pid=0){
	$arr=array();
	foreach($node as $v){
		if(is_array($access)){
			$v['access']=in_array($v['id'],$access)?1:0;
		}
		if($v['pid']==$pid){
			$v['child']=node_merge($node,$access,$v['id']);
			$arr[]=$v;
		
		}
	}
	return $arr;
}
function cmstype($t,$i){
	$sort[1] = array('分类','栏目','单篇');
	$sort[2] = array('文章','图片','房产');
	return $sort[$t][$i];
}



/*
	获取缩略图地址
*/
function get_thumb($picurl){
	//$picurl="./Data/upload/photo/20141121/1416550895914.png";
	$picurl=str_replace('thumb_','',$picurl);
	$pathinfo=pathinfo($picurl);
	return $pathinfo['dirname'].'/thumb_'.$pathinfo['basename'];
}

/*
	获取原图地址
*/
function get_pic($picurl){
	$picurl=str_replace('thumb_','',$picurl);
	return $picurl;
}
//+++++++++++++++++++++++++++++购物车函数+++++++++++++++++++++++++++++++++++++++++++++//

/*
	添加购物车
	param:商品id,商品数量,商品价格,商品规格
*/
function addcart($goods_id,$goods_num,$goods_price,$goods_norm){
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	if(empty($cur_cart_arr)){
		$cart_info[0]=array(
			'goods_id'=>$goods_id,
			'goods_nums'=>$goods_num,
			'goods_price'=>$goods_price,
			'goods_norm'=>$goods_norm
		);
		$_SESSION['shop_cart_info']=$cart_info;
	}elseif(!empty($cur_cart_arr)){
		//购物车中存在相同商品
        $is_exist=0;
		foreach($cur_cart_arr as $key=>$val){
			if($val['goods_id']==$goods_id&&$val['goods_norm']==$goods_norm){
				$cur_cart_arr[$key]['goods_nums']=$val['goods_nums']+$goods_num;
				$is_exist=1;
			}
		}
		//购物车中不存在相同商品
		if($is_exist==0){
			$cur_cart_arr[]=array(
			'goods_id'=>$goods_id,
			'goods_nums'=>$goods_num,
			'goods_price'=>$goods_price,
			'goods_norm'=>$goods_norm
			) ;
		}
		$_SESSION['shop_cart_info']=$cur_cart_arr;
	}	
	
}

/*
	删除购物车
*/
function delcart($cart_key){
	$cur_goods_arr=$_SESSION['shop_cart_info'];
	//删除该商品在数组中的位置
	unset($cur_goods_arr[$cart_key]);
	$_SESSION['shop_cart_info']=$cur_goods_arr;
}

/*
	修改购物车
	param: 商品id，增加？减少，商品规格
*/
function updatecart($cart_key,$action='add'){
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	
	foreach($cur_cart_arr as $key=>$val){
		if($key==$cart_key){
			
			if($action=='add'){
				$cur_cart_arr[$key]['goods_nums']+=1;
			}else{
				$cur_cart_arr[$key]['goods_nums']-=1;
				if($cur_cart_arr[$key]['goods_nums']==0){
					unset($cur_cart_arr[$key]);
				}
			}
			
				
		}
	}
	
	$_SESSION['shop_cart_info']=$cur_cart_arr;
}

function _updatecart($goods_id,$action='add',$goods_norm){
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	
	foreach($cur_cart_arr as $key=>$val){
		if($val['goods_id']==$goods_id&&$val['goods_norm']==$goods_norm){
			
			if($action=='add'){
				$cur_cart_arr[$key]['goods_nums']+=1;
			}else{
				$cur_cart_arr[$key]['goods_nums']-=1;
				if($cur_cart_arr[$key]['goods_nums']==0){
					unset($cur_cart_arr[$key]);
				}
			}
			
				
		}
	}
	
	$_SESSION['shop_cart_info']=$cur_cart_arr;
}
/*
	计算购物车商品总数
*/
function  cart_count(){
	$cart_count=0;
	$list=$_SESSION['shop_cart_info'];
	foreach($list as $val){
		$cart_count+=$val['goods_nums'];
	}
	return $cart_count;
	
}
//+++++++++++++++++++++++++++++++++++++++++++++++/购物车结束++++++++++++++++++++++++++++++++++++++++++++++++++++++//
/*
	获取当前url
*/
function get_curr_url() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}


function replace_pic($content){
	preg_match_all('/\[.*?\]/is',$content,$arr);
	if($arr[0]){
		$pic=F('pic','','./data/');
		foreach($arr[0] as $v){
			foreach($pic as $key=>$val){
				if($v=='['.$val.']'){
					$content=str_replace($v,'<img src="'.__ROOT__.'/Public/Images/phiz/'.$key.'.gif"/>',$content);
				}
				continue;
			}
		}
	}
	return $content;
}

/*
	按键值对查找数组
*/
function seekarr($arr=array(),$key,$val){
	$res = array();
	$str = json_encode($arr);
	preg_match_all("/\{[^\{]*\"".$key."\"\:\"".$val."\"[^\}]*\}/",$str,$m);
	if($m && $m[0]){
		foreach($m[0] as $val) $res[] = json_decode($val,true);
	}
	return $res;
}
/*
	递归-按照分类子级关系重排栏目
*/
function sarr($arr,$id){
	global $ic;
	$thisa=array();
	$aarr=seekarr($arr,'fup',$id);	//fup 上级
	if(count($aarr)>0){
		for($i=0;$i<count($aarr);$i++){
			$thisa[$ic]=$aarr[$i];
			$ic+=1;
			$o=$aarr[$i]['id'];	//fid 栏目id
			$toarr=sarr($arr,$o);
			if(count($toarr)>0){
				$thisa=array_merge($thisa,$toarr);
			}
		}
	return $thisa;
	}
}
/*
	对二维数组按键值排序
*/
function array_sort($arr,$keys,$type='asc'){
	$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
	return $new_array;
}

/**

 * 生成随机字符串，由小写英文和数字组成。去掉了容易混淆的0o1l之类

 * @param int $int 生成的随机字串长度

 * @param boolean $caps 大小写，默认返回小写组合。true为大写，false为小写

 * @return string 返回生成好的随机字串

 */

function randStr($int = 6, $caps = false) {

	$strings = 'abcdefghjkmnpqrstuvwxyz23456789';

	$return = '';

	for ($i = 0; $i < $int; $i++) {

		srand();

		$rnd = mt_rand(0, 30);

		$return = $return . $strings[$rnd];

	}

	return $caps ? srttoupper($return) : $return;

}

/*
	判断是否为"微信浏览器"
*/
function is_weixin(){
	
	$agent = $_SERVER['HTTP_USER_AGENT']; 
	if(strpos($agent,"icroMessenger")===false) {
		$return=false;  						//不是微信
		//file_put_contents('a.txt','liulanqi');
	}else{
		//file_put_contents('a.txt','weixin');
		$return=true;							//是微信
	}
	return $return;
}

/*
	判断是否为移动设备
*/
function is_mobile()
{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
}
/*
	获取文件后缀名
*/
function extend($file_name){
	$extend = pathinfo($file_name);
	$extend = strtolower($extend["extension"]);
	return $extend;
}

/*
	资金变更
*/

function money_change($uid,$type,$amount,$way,$remark,$order_id){
	//资金日志数据
	$log['uid']=$uid;
	$log['type']=$type;
	$log['amount']=$amount;
	$log['way']=$way;
	$log['remark']=$remark;
	$log['order_id']=$order_id;
	$log['posttime']=time();
	
	$info=M('wechat_user')->where(array('id'=>$uid))->find();
	
	if($type==1){			//收入		
		$money=$info['money']+$amount;				//变更后的金额
	}elseif($type==2){		//支出
		if($info['money']>=$amount){
			$money=$info['money']-$amount;			//变更后的金额
		}else{
			$money=0;
		}
	}
	M('wechat_user')->where(array('id'=>$uid))->save(array('money'=>$money));
	//记录日志
	M('money_water')->add($log);
	//模板消息通知
	money_change_notice($uid,$type,$amount,$remark);				
}

/*
	积分变更
*/

function jifen_change($user_id,$type,$amount,$way,$remark){
	//积分日志数据
	$log['user_id']=$user_id;
	$log['type']=$type;
	$log['amount']=$amount;
	$log['way']=$way;
	$log['way_name']=$remark;
	$log['posttime']=time();
	
	$info=M('wechat_user')->where(array('id'=>$user_id))->find();
	
	if($type==1){			//收入		
		$jifen=$info['jifen']+$amount;
	}elseif($type==2){		//支出
		if($info['jifen']>=$amount){
			$jifen=$info['jifen']-$amount;
		}else{
			$jifen=0;
		}
	}
	M('wechat_user')->where(array('id'=>$user_id))->save(array('jifen'=>$jifen));
	//记录日志
	M('jifen_water')->add($log);
	//模板消息通知
	jifen_change_notice($user_id,$type,$log['amount'],$remark);				
}

/*
	 积分策略
	 @param $type  1收,2支出
	 @param $act 积分动作
	 @param $user_id	用户id
*/
function return_jifen($type,$act,$user_id){
	//查询积分策略
	$jifen_conf=M('jifen_config')->find(1);
	//积分日志数据
	$log['type']=$type;
	$log['user_id']=$user_id;
	$log['posttime']=time();			
	switch($act){
		//注册
		case 'reg':
			$log['way']='reg';				
			$log['way_name']='注册';
			$log['amount']=$jifen_conf['reg'];		//积分数量		
		break;
		//推荐注册
		case 'reg_tui':
			$log['way']='reg_tui';				
			$log['way_name']='推荐用户注册';
			$log['amount']=$jifen_conf['reg_tui'];		//积分数量		
		break;
		//登录
		case 'login':
			$log['way']='login';				
			$log['way_name']='每日登录';
			$log['amount']=$jifen_conf['login'];		//积分数量		
		break;
		//分享
		case 'share':
			$log['way']='share';				
			$log['way_name']='分享';
			$log['amount']=$jifen_conf['share'];		//积分数量		
		break;
		//签到
		case 'sign':
			$log['way']='sign';				
			$log['way_name']='签到';
			$log['amount']=$jifen_conf['sign'];		//积分数量		
		break;
	}
	if($type==1){			//收入		
		M('wechat_user')->where(array('id'=>$user_id))->setInc('jifen',$log['amount']);
	}elseif($type==2){		//支出
		M('wechat_user')->where(array('id'=>$user_id))->setDec('jifen',$log['amount']);
	}
	//记录日志
	if($log['amount']>0){
		M('jifen_water')->add($log);
		//模板消息通知
		jifen_change_notice($user_id,$type,$log['amount'],$log['way_name']);
	}
}


function array2object($array) {  
   
    if (is_array($array)) {  
        $obj = new StdClass();  
   
        foreach ($array as $key => $val){  
            $obj->$key = $val;  
        }  
    }  
    else { $obj = $array; }  
   
    return $obj;  
}  
   
function object2array($object) {  
    if (is_object($object)) {  
        foreach ($object as $key => $value) {  
            $array[$key] = $value;  
        }  
    }  
    else {  
        $array = $object;  
    }  
    return $array;  
}  
/**
 * 转换XML文档为数组
 *
 * @author Luis Pater
 * @date 2011-09-06
 * @param string xml内容
 * @return mixed 返回的数组，如果失败，返回false
 */
function xml2array($xml) {
	$xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
	return simplexml2array($xml);
}