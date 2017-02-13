<?php
/*
	插件ajax处理控制器
*/
class PluginAjaxAction extends Action{
	public $user_id;
	public $user_info;
	public function _initialize(){
		$this->user_id=$_SESSION['user_id'];							//当前登录用户uid
		$this->user_info=M('wechat_user')->find($this->user_id);		//当前登录用户信息
	}
	/*
		发送消息	
	*/
	public function chat_send(){
		import("@.ORG.Wxhelper");
		$pwechat=M('wechat_config')->find(1);	//公众号信息
		$helper=new Wxhelper($pwechat);
		
		$db=M('plugin_chat');
		if($arr=$this->_post()){
			$arr['f_uid']=$this->user_id;		//发送用户UID
			$arr['posttime']=time();
			$insert_id=$db->add($arr);
			
			$t_user=M('wechat_user')->where(array('id'=>$arr['t_uid']))->find();
			
			$msg['touser']=$t_user['wechatid'];//'oo8LKs40LzrwE1NMmi7HVj5ZQq7U';
			$msg['msgtype']='text';
			$msg['text']=array('content'=>$arr['content']);
			$helper->send_kf_msg($msg);
			
			//发送微信模板消息
			/*$t_openid=M('wechat_user')->where(array('id'=>$arr['t_uid']))->getField('wechatid');
			$tpl_arr=array();			
			$tpl_arr['touser']='oQusRs-uFBANUQFuEqbJ7VphdO2s';			//bruce
			$tpl_arr['template_id']='5caLnApJcxhfRRM2TDBM_jauzs8PFjzD0Vy0wStDRIQ';	
			$tpl_arr['url']='http://'.I('server.HTTP_HOST').U('Weixin/Plugin/chat_list');	
			$tpl_arr['topcolor']='#FF0000';	
			$tpl_arr['data']['content']['value']='您好，您有新消息，请注意查收！';
			wx_tpl_msg($tpl_arr);*/
			
			//发送模板消息
			chat_notice($arr['f_uid'],$arr['t_uid']);
			
			
			echo $insert_id;
		}
	}


	public function httpPost($url,$data) {
		$curl = curl_init();
		//post提交方式
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);	//要求结果为字符串且输出到屏幕上
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		
		$res = curl_exec($curl);	
		curl_close($curl);
		file_put_contents('res.txt',$res);
		return $res;
	}
	
	

} 

?>