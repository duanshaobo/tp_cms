<?php
/**
 * 常用公共函数库
 *
 */


//===================微信接口函数=================//
/*
	 微信模板消息
*/
function wx_tpl_msg($arr){
	import('@.ORG.Wxhelper');
	$wxconfig=M('wechat_config')->find(1);
	$wxhelper=new Wxhelper($wxconfig);
	$rs=$wxhelper->send_tpl_msg($arr);
	return $rs;
}

//===================微信接口函数=================//
/*
	提现状态
*/
function apply_status($state){
	$arr=array(0=>'<font color="red">等待处理</font>',
			   1=>'<font color="green">提现成功</font>',
			   2=>'<font color="red"提现失败</font>');
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
	$arr=array(1=>'未发货',2=>'已发货',3=>'已签收');
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
*/
function addcart($goods_id,$goods_num,$goods_price){
	//$cur_cart_array =unserialize(stripslashes($_COOKIE['shop_cart_info']));
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	if(empty($cur_cart_arr)){
		$cart_info[$goods_id]['goods_id'] = $goods_id;
		$cart_info[$goods_id]['goods_nums'] = $goods_num;
        $cart_info[$goods_id]['goods_price'] = $goods_price;
		//setcookie("shop_cart_info",serialize($cart_info),time()+3600);
		$_SESSION['shop_cart_info']=$cart_info;
	}elseif($cur_cart_arr<>""){
		//遍历当前的购物车数组
		//如果键值为0且货号相同则购物车存在相同货品
        $is_exist=0;
		foreach($cur_cart_arr as $key=>$goods_current_cart){
			if($goods_current_cart['goods_id']==$goods_id){
				$cur_cart_arr[$key]['goods_nums']=$goods_current_cart['goods_nums']+$goods_num;
				$is_exist=1;
			}
		}
		if($is_exist==0){
			$cur_cart_arr[$goods_id]=array('goods_id'=>$goods_id,'goods_nums'=>$goods_num,'goods_price'=>$goods_price) ;
		}
		//setcookie("shop_cart_info",serialize($cur_cart_array),time()+3600);
		$_SESSION['shop_cart_info']=$cur_cart_arr;
	}	
	
}

/*
	删除购物车
*/
function delcart($goods_array_id){
	//$cur_goods_arr =unserialize(stripslashes($_COOKIE['shop_cart_info']));
	$cur_goods_arr=$_SESSION['shop_cart_info'];
	//删除该商品在数组中的位置
	unset($cur_goods_arr[$goods_array_id]);
	//setcookie("shop_cart_info",serialize($cur_goods_array));
	$_SESSION['shop_cart_info']=$cur_goods_arr;
}

/*
	修改购物车
*/
function updatecart($goods_id,$action='add'){
    //$cur_cart_array =unserialize(stripslashes($_COOKIE['shop_cart_info']));
	$cur_cart_arr=$_SESSION['shop_cart_info'];
	if($action=='add'){
		$cur_cart_arr[$goods_id]['goods_nums']+=1;
	}else{
		$cur_cart_arr[$goods_id]['goods_nums']-=1;
		if($cur_cart_arr[$goods_id]['goods_nums']==0){
			unset($cur_cart_arr[$goods_id]);
		}
	}
    //setcookie("shop_cart_info",serialize($cur_cart_array),time()+3600);
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
	M('jifen_water')->add($log);
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