<?php
class AjaxAction extends Action{
	/*
		订单退款
	*/
	public function order_refund(){
		$db=M('order_info');
		if($arr=$this->_post()){
			$id=$arr['id'];			//订单id
			$order=$db->where(array('id'=>$id))->find();
			if(!empty($order)){
				//修改订单支付状态为2[已退款]
				$db->where(array('id'=>$order['id']))->save(array('pay_status'=>2));
				//佣金撤回
				yongjin_refund($order['id']);
				echo 1;
			}
			
		}	
	}
	
	/*
		搜索用户
	*/
	public function user_search(){
		$db=M('wechat_user');
		if($arr=$this->_post()){
			$map="(id LIKE '%{$arr['search_key']}%') OR (nickname LIKE '%{$arr['search_key']}%')";
			$list=$db->where($map)->field('id,p_1,p_2,p_3,nickname,username')->select();
			echo json_encode($list);
			die();
		}
	}
	
	/*
		修改用户关系
	*/
	public function update_user_relation(){
		$db=M('wechat_user');
		if($arr=$this->_post()){
			//顶级用户
			if($arr['p_1']==0){
				$data['p_1']=$data['p_2']=$data['p_3']=0;
				$db->where(array('id'=>$arr['uid']))->save($data);
				echo 1;
				die();
			}else{
				$p_1=$db->where(array('id'=>$arr['p_1']))->find();				
				$user=$db->where(array('id'=>$arr['uid']))->find();
				if($arr['uid']!=$arr['p_1']){					
					$data['p_1']=$p_1['id'];
					$data['p_2']=$p_1['p_1']>0&&$p_1['p_1']!=$arr['uid']?$p_1['p_1']:0;
					$data['p_3']=$p_1['p_2']>0&&$p_1['p_2']!=$arr['uid']?$p_1['p_2']:0;
					$db->where(array('id'=>$arr['uid']))->save($data);
					//更新下一级
					$data1['p_1']=$user['id'];
					$data1['p_2']=$data['p_1'];
					$data1['p_3']=$data['p_2'];
					$db->where(array('p_1'=>$arr['uid']))->save($data1);
					
					//更新下二级
					$data2['p_2']=$user['id'];
					$data2['p_3']=$data['p_1'];
					$db->where(array('p_2'=>$arr['uid']))->save($data2);

					
					/*if($user['id']==$p_1['p_1']){
						$db->where(array('id'=>$p_1['id']))->save(array('p_1'=>0,'p_2'=>0,'p_3'=>0));
					}
					if($user['id']==$p_1['p_2']){
						$db->where(array('id'=>$p_1['id']))->save(array('p_1'=>0,'p_2'=>0,'p_3'=>0));
					}
					if($user['id']==$p_1['p_3']){
						$db->where(array('id'=>$p_1['id']))->save(array('p_1'=>0,'p_2'=>0,'p_3'=>0));
					}*/
					
					
				}
			}
			
		}
	}
	
	/*
		资金变更
	*/
	public function money_change(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$info['money']=intval($info['money']);
		if($arr=$this->_post()){
			$arr['money']=intval($arr['money']);
			$money=0;
			if($info['money']>=$arr['money']){
				$money=$info['money']-$arr['money'];
				$type=2;			//减少
			}elseif($info['money']<$arr['money']){
				$money=$arr['money']-$info['money'];
				$type=1;			//增加
			}
			//$uid,$type,$amount,$way,$remark,$order_id
			if($money>0){
				money_change($id,$type,$money,'admin_change','管理员变更',0);
			}
			echo 1;
		}
	}
	
	/*
		积分变更
	*/
	public function jifen_change(){
		$db=M('wechat_user');
		$id=I('get.id');
		$info=$db->where(array('id'=>$id))->find();
		$info['jifen']=intval($info['jifen']);
		if($arr=$this->_post()){
			$arr['jifen']=intval($arr['jifen']);
			if($info['jifen']>=$arr['jifen']){
				$jifen=$info['jifen']-$arr['jifen'];
				$type=2;			//减少
			}elseif($info['jifen']<$arr['jifen']){
				$jifen=$arr['jifen']-$info['jifen'];
				$type=1;			//增加
			}
			//$user_id,$type,$amount,$way,$remark
			jifen_change($id,$type,$jifen,'admin_change','管理员变更');
			echo 1;
		}
	}
	
	public function index(){
		import("@.ORG.Page");
		$db=M('photo');
		if($wechatid=I('wechatid')){
			$map=array('wechatid'=>$wechatid);	
		}else{
			$map=array();
		}
		$count = $db->where($map)->count();
		$Page = new Page($count,20);		
		$list=$db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['thumb']=get_thumb($val['photo']);
			$list[$key]['uname']=M('wechatuser')->where(array('wechatid'=>$val['wechatid']))->getField('uname');
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();    
	}
	public function del(){
		if($id=I('get.id')){
			$db=M('photo');
			$db->where(array('id'=>$id))->delete();
			$this->redirect('index');
		}	
	}
	public function upload(){
		import("@.ORG.Thumb");
		$date=date('Ymd',time());
        $folder="./Data/upload/slide/$date";
        if(!file_exists($folder)){
            mkdir($folder);
        }
        $targetFolder=$folder;
		if (!empty($_FILES)){
			$return=array('flag'=>false);
			if($_FILES['Filedata']['size']>2*1000*1000){
				$return['msg']='图片大小不能超过2M';
				echo json_encode($return);
				die();
			}
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $targetFolder ;//$_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			//重新构造图片名称
			$fileParts=pathinfo($_FILES['Filedata']['name']);
			$picname=rand(1111,9999).time().'.'.$fileParts['extension'];
			$targetFile=rtrim($targetPath,'/') . '/' . $picname;
			$thumbFile=rtrim($targetPath,'/') . '/thumb_' . $picname;
			//$targetFile=rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
			
			// Validate the file type
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				//生成缩略图
				$thumb=new ResizeImage($targetFile, '120', '90', '0',$thumbFile);
				/*$source = imagecreatefromjpeg($targetFile);
				list($width, $height) = getimagesize($targetFile);
				list($newwidth,$newheight)=array(200,170);
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
				if($fileParts['extension']=='png'){
					imagepng($thumb,$thumbFile);
				}elseif($fileParts['extension']=='jpg'){
					imagejpeg($thumb,$thumbFile);
				}elseif($fileParts['extension']=='gif'){
					imagegif($thumb,$thumbFile);
				}*/
				$return['flag']=true;
				$return['url']=substr($targetFile,1);
				echo json_encode($return);
				die();
			}else{
				$return['msg']='图片格式不正确';
				echo json_encode($return);
				die();
			}
		}
	}
	//地区联动
	/*
		省份
	*/
	public function province(){
		$db=M('region');
		$map=array('region_type'=>1);
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		echo json_encode($list);die();
	}
	/*
		城市
	*/
	public function city(){
		$db=M('region');
		$map=array('region_type'=>2,'parent_id'=>I('post.parent_id'));
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		echo json_encode($list);die();
	}
	/*
		区县
	*/
	public function district(){
		$db=M('region');
		$map=array('region_type'=>3,'parent_id'=>I('post.parent_id'));
		$list=$db->where($map)->field('id,parent_id,region_name')->select();
		echo json_encode($list);die();
	}
}