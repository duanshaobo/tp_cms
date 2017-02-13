<?php
/**
 * JS_API支付demo
 * ====================================================
 * 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
 * 成功调起支付需要三个步骤：
 * 步骤1：网页授权获取用户openid
 * 步骤2：使用统一支付接口，获取prepay_id
 * 步骤3：使用jsapi调起支付
*/
	error_reporting(E_ALL);
	include_once("WxPayPubHelper/WxPayConf.php");
	include_once("WxPayPubHelper/WxPayPubHelper.php");
    include_once("class/db.class.php");
	//================获取订单信息====================//
	$db=new Connection();
	if($order_id=$_GET['order_id']){
		$query=$db->query("select * from tp_order where id='$order_id'");
		$order=$db->get_one($query);
		if($order['pay_status']==1){
			header('location:http://'.$_SERVER['HTTP_HOST'].'/index.php?g=Weixin&m=Ucenter&a=order_detail&order_id='.$order_id);
		}
		/*$query=$db->query("select * from tp_order_goods where order_id=$order_id");
		$goods=$db->get_one($query);*/
		
		$query=$db->query("select * from tp_wechat_user where id={$order['uid']}");
		$user=$db->get_one($query);
	}
	
	
	//微信支付商户信息
	$query=$db->query("select * from tp_wechat_config where id=1");
	$shop_info=$db->get_one($query);
	WxPayConf::$APPID=$shop_info['appid'];
	WxPayConf::$APPSECRET=$shop_info['appsecret'];
	WxPayConf::$MCHID=$shop_info['mchid'];
	WxPayConf::$KEY=$shop_info['partnerkey'];
	WxPayConf::$NOTIFY_URL='http://'.$_SERVER['HTTP_HOST'].'/wxpay/notify_url.php';
	
    //================获取订单信息====================//
	//使用jsapi接口
	$jsApi = new JsApi_pub();

	//=========步骤1：网页授权获取用户openid============//
//	if (!isset($_GET['code']))  //通过code获得openid
//	{
//		//触发微信返回code码
//		$url = $jsApi->createOauthUrlForCode(WxPayConf_pub::JS_API_CALL_URL);
//		Header("Location: $url"); 
//	}else
//	{
//		//获取code码，以获取openid
//                $code = $_GET['code'];
//		$jsApi->setCode($code);
//                $openid = $jsApi->getOpenId();       
//	}
    //=========步骤1：网页授权获取用户openid============//
	
	session_start();
	$uid=$_SESSION['user_id'];
	$query=$db->query("select * from tp_wechat_user where id=$uid");
	$pay_user=$db->get_one($query);
	$openid=$pay_user['wechatid'];			//代支付openid
	if(empty($openid)){
		$openid=$user['wechatid'];
	}
	//=========步骤2：使用统一支付接口，获取prepay_id============//
	//使用统一支付接口
	$unifiedOrder = new UnifiedOrder_pub();
	
	//设置统一支付接口参数
	//设置必填参数
	//appid已填,商户无需重复填写
	//mch_id已填,商户无需重复填写
	//noncestr已填,商户无需重复填写
	//spbill_create_ip已填,商户无需重复填写
	//sign已填,商户无需重复填写
	$unifiedOrder->setParameter("openid",$openid);//商品描述
	$unifiedOrder->setParameter("body",'心育心理在线咨询');//商品描述
	//自定义订单号，此处仅作举例
	$unifiedOrder->setParameter("out_trade_no",$order['out_trade_no'].rand(1111,9999));//商户订单号 
	//$unifiedOrder->setParameter("out_trade_no",$order['out_trade_no']);//商户订单号 
	$unifiedOrder->setParameter("total_fee",$order['total_fee']*100);       //总金额(分)
	$unifiedOrder->setParameter("notify_url",'http://'.$_SERVER['HTTP_HOST'].'/wxpay/notify_url.php');//通知地址 
	$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
	//非必填参数，商户可根据实际情况选填
	//$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
	//$unifiedOrder->setParameter("device_info","XXXX");//设备号 
	//$unifiedOrder->setParameter("attach","XXXX");//附加数据 
	//$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
	//$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
	//$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
	//$unifiedOrder->setParameter("openid","XXXX");//用户标识
	//$unifiedOrder->setParameter("product_id","XXXX");//商品ID

	$prepay_id = $unifiedOrder->getPrepayId();
	//=========步骤3：使用jsapi调起支付============
	$jsApi->setPrepayId($prepay_id);
	$jsApiParameters = $jsApi->getParameters();
	$domain=$_SERVER['HTTP_HOST'];
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta content="initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>在线支付</title>
<script type="text/javascript">
window.history.forward(1); //禁止页面后退
var order_id="<?php echo $order_id;?>";
	var domain="<?php echo $domain;?>";
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				 // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。 
				 //get_brand_wcpay_request:ok
				if(res.err_msg=="get_brand_wcpay_request:ok") {
					//支付成功，跳转至支付成功页面
					//var success_url="http://"+domain+"/index.php?g=Weixin&m=Ucenter&a=call_back_url&id="+order_id;
					var success_url="http://"+domain+"/index.php?g=Weixin&m=Ucenter&a=order_detail&id="+order_id;
					location.href=success_url;		 
				}else{
					//alert(res.err_code+res.err_desc+res.err_msg);
					var fail_url="http://"+domain+"/index.php?g=Weixin&m=Ucenter&a=order_list&id="+order_id;
					location.href=fail_url;	
				}
				
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
			if( document.addEventListener ){
				document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			}else if (document.attachEvent){
				document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
				document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			}
		}else{
			jsApiCall();
		}
	}
	</script>
</head>
<body onLoad="callpay();" style="display:none">
        <div id="box">
            <div id='alt'>订单信息</div>
            <div id="content">
                <p>订单编号：<?php echo $order_info['id'];?></p>
                <p>订单名称：<?php echo $orderName;?></p>
                <p>订单金额：&yen;<?php echo $order_info['price'];?></p>
            </div>
        </div>
	<div align="center">
		<button class="btn" type="button" onClick="callpay()" >确认支付</button>
	</div>
</body>
<style>
    #box{
        box-shadow: 1px 1px 1px 1px #CCC;
        border-radius: 4px;
        margin: 10px 0px;
        padding: 0px;
        text-align: left;
    }
    #alt{
        background: #27AE16;
        border-radius: 3px;
        padding: 10px;
        color: white;
        font-size: 15px;
    }
    #content{
        padding: 10px;
        line-height: 22px;
    }
    .btn{
        padding: 10px;
        width:100%;
        background-color:#27AE16; 
        border:0px #27AE16 solid; 
        cursor: pointer;  
        color:white;  
        font-size:16px;
        border-radius: 5px;
    }
</style>
</html>