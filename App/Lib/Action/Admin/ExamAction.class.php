<?php
/*
	考试试题管理
*/
class ExamAction extends PublicAction{

	public function _initialize(){
		parent::_initialize();
		import("@.ORG.Page");
		$pager=M('exam_pager')->where()->select();
		$this->assign('pager',$pager);
		
	}
	
	public function ajax_topic_save(){
		$db=M('exam_topic');
		if($arr=$this->_post()){
			$map=array('id'=>$arr['id']);
			unset($arr['id']);
			$db->where($map)->save($arr);
			echo 1;
		}
	}
	
	/*
		考试试题列表
	*/
	public function index(){
		
		$map=array();
		
		if($p_id=I('get.p_id')){
			$map['p_id']=$p_id;
		}
		
		if($keyword=I('get.val')){
			$map['title']=array('like','%'.$keyword.'%');	
		}
		$db=M('exam_topic');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	

	/*
		编辑试题
	*/
	
	public function edit(){
		$id=I('get.id');
		$info=M('exam_topic')->find($id);
		$this->assign('info',$info);
		if($data=$this->_post()){
			$w['id']=I('get.id');
			M('exam_topic')->where($w)->save($data);
			$this->success('保存成功',U('index'));
		}else{
			$this->display();		
		}
	}
	/*
		删除试题
	*/
	public function del(){
		$id=I('get.id');
		if(M('exam_topic')->delete($id)){
			$this->success('操作成功！');
		}
	}
	
	/*
		新增试题
	*/
	public function add(){
		if($data=$this->_post()){
			M('exam_topic')->data($data)->add();
			$this->redirect('index');
		}
		$this->display();
	}
	
		
	/*
		 试题分类管理
	*/	
	public function pager_list(){
		$map=array();
		if($keyword=I('get.val')){
			$map['title']=array('like','%'.$keyowrd.'%');	
		}
		$db=M('exam_pager');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	
	/*
		 新增分类
	*/	
	public function pager_add(){
		$db=M('exam_pager');
		if($data=$this->_post()){
			$db->add($data);
			$this->redirect('pager_list');
		}else{
			$this->display();
		}
	}
	
	/*
		  编辑分类
	*/	
	public function pager_edit(){
		$db=M('exam_pager');
		$id=I('get.id');
		$map=array('id'=>$id);
		$info=$db->where($map)->find();
		$this->assign('info',$info);
		if($data=$this->_post()){
			$db->where($map)->save($data);
			$this->redirect('pager_list');
		}else{
			$this->display();
		}
	}
	
	public function pager_del(){
		$id=I('get.id');
		if(M('exam_pager')->delete($id)){
			$this->success('操作成功！');
		}
	}
	
	/*
		考试记录
	*/
	public function exam_log(){
		$map=array();
		$db=M('exam_log');
		$count = $db->where($map)->count();
		$Page = new Page($count,10);

		$list=$db->where($map)->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach($list as $key=>$val){
			$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->find();
			$list[$key]['pager']=M('exam_pager')->where(array('id'=>$val['p_id']))->find();
		}
		$show = $Page->show();
		$this->assign('show',$show);
		$this->assign('list',$list);
		$this->display();
	}
	/*
		删除考试记录
	*/
	public function exam_log_del(){
		$id=I('get.id');
		if(M('exam_log')->delete($id)){
			$this->redirect('exam_log');
		}
	}
	
	
	/*
		试题导入【导入excel】
	*/
	public function import_excel(){
		header('content-type:text/html;charset=utf-8');
		import('@.ORG.PHPExcel');
		import('@.ORG.PHPExcel.IOFactory');
		$db=M('exam_topic');
		if($this->_post()){
			//$uploadfile="./Data/upload/file/20150731/20150731200716_58477.xlsx";
			$uploadfile='.'.I('post.excel');	
			//dump($uploadfile);
			//如果上传文件成功，就执行导入 excel操作  
			if($uploadfile){ 
				   // $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2003   
				   $objReader =PHPExcel_IOFactory::createReader('Excel2007');//use excel2003 和  2007 format   
				   // $objPHPExcel = $objReader->load($uploadfile); //这个容易造成httpd崩溃   
				   $objPHPExcel =PHPExcel_IOFactory::load($uploadfile);//改成这个写法就好了   
			  
				   $sheet = $objPHPExcel->getSheet(0);    
				   $highestRow = $sheet->getHighestRow(); // 取得总行数    
				   $highestColumn = $sheet->getHighestColumn(); // 取得总列数   
				   
					//循环读取excel文件,读取一条,插入一条   
					for($j=2;$j<=$highestRow;$j++)   
					{    
						for($k='A';$k<=$highestColumn;$k++)   
						 {    
							 //$str .= iconv('gbk','utf-8',$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue()).'\\';		
							 $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'\\';		
							 //读 取单元格  
						}   
						//explode:函 数把字符串分割为数组。  
						$strs=explode("\\",$str);  
						  
						$_data['qid']=$strs[0];				//题号
						
						if($strs[1]=='是'){
							$_data['level']=1;				//初级
						}
						if($strs[2]=='是'){
							$_data['level']=2;				//中级
						}
						if($strs[3]=='是'){
							$_data['level']=3;				//高级
						}
						$_data['title']=$strs[4];			//题干
						$_data['A']=$strs[5];
						$_data['B']=$strs[6];
						$_data['C']=$strs[7];
						$_data['D']=$strs[8];
						$_data['answer']=$strs[9];
						if(strlen($strs[9])==1){
							$_data['style']=1;		//单选
						}else{
							$_data['style']=2;		//多选
						}
						$_data['remark']=$strs[10];
						$db->add($_data);
						unset($_data);
						$str ="";  
				   }   
				   /*echo '<pre>';
				   var_dump ($strs);  
				   die();  */
				   unlink ($uploadfile); //删除上传的excel文件  
				  // $msg = "导入成功！";  
				  $this->success('导入成功！',U('index'));
				}else{  
				   //$msg = "导入失败！";   
				   $this->success('导入失败！',U('index'));
				}   
				return $msg;   
				
				//导入数据完成
		}else{
			$this->display();
		}
		
	}
}