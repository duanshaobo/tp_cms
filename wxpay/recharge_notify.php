<?php
/**
 * 在线充值-通用通知接口demo
*/
	include_once("./log_.php");
	include_once("../WxPayPubHelper/WxPayPubHelper.php");
    include_once("../class/db.class.php");

    //使用通用通知接口
	$notify = new Notify_pub();

	//存储微信的回调
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	

	$notify->saveData($xml);
	
	//验证签名，并回应微信。
	//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
	//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
	//尽可能提高通知的成功率，但微信不保证通知最终能成功。
	if($notify->checkSign() == FALSE){
		$notify->setReturnParameter("return_code","FAIL");//返回状态码
		$notify->setReturnParameter("return_msg","签名失败");//返回信息
	}else{
		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	}
	$returnXml = $notify->returnXml();
	echo $returnXml;
	
	//==商户根据实际情况设置相应的处理流程==//
	
	 //====================================更新订单状态==================================//
	$obj=(array)simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
	//file_put_contents('pay.log',var_export($obj,true));
	$result_code=$obj['result_code'];       				//成功 SUCCESS
	$return_code=$obj['return_code'];       				//成功 SUCCESS
	$trade_no=$obj['trade_no'];     						//交易单号
	$out_trade_no=$obj['out_trade_no'];     				//订单号
	$total_fee=($obj['total_fee'])*0.01;           			//支付金额
	$openid=$obj['openid'];									//支付者微信openid
	$timestamp=time();
	
	
	$db=new Connection();               					//创建数据库链接
	//更新订单状态
	$sql="update `tp_recharge` set pay_status=1,pay_way=1,pay_money=$total_fee,pay_time=$timestamp,status=1 where out_trade_no='$out_trade_no'";
	$db->query($sql);
	//订单信息
	$query=$db->query("select * from `tp_recharge` where out_trade_no='$out_trade_no'");
	$order=$db->get_one($query);
	
	//增加账户金额
	if($order['status']==0){
		$query=$db->query("select * from `tp_wechat_user` where id={$order['uid']}");
		$mem=$db->get_one($query);				//用户信息
		$db->query("update `tp_wechat_user` set money={$mem['money']}+$total_fee where id={$order['uid']}");
		//记录资金流水
		$sql="update `tp_money_water` set uid={$order['uid']},type=1,way='recharge',way_name='微信在线充值',money=$total_fee,order_id={$order['id']},posttime=$timestamp";
		$db->query($sql);
	}
	//====================================更新订单状态==================================//

	//以log文件形式记录回调信息
	$log_ = new Log_();
	$log_name="./notify_url.log";//log文件路径
	$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

	if($notify->checkSign() == TRUE)
	{
		if ($notify->data["return_code"] == "FAIL") {
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
		}
		elseif($notify->data["result_code"] == "FAIL"){
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
		}
		else{
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
		}
		
		//商户自行增加处理流程,
		//例如：更新订单状态
		//例如：数据库操作
		//例如：推送支付完成信息
	}
?>