<?php 
/*
	资金流水
*/
class MoneyAction extends PublicAction{
		/*public function _initialize(){
			parent::_initialize();
		}*/
		
		public function index(){
			import("@.ORG.Page");
			$db=M('money_water');
			//$map=array('way'=>array('in','yongjin,yongjin_refund'));		//'type'=>1,
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('id,nickname,name,username')->find();
				$list[$key]['order']=M('order_info')->where(array('id'=>$val['order_id']))->field('id,out_trade_no')->find();
				/*switch($val['way']){
					case 'yongjin':
						$list[$key]['way_name']='订单返佣';
					break;
					
					case 'yongjin_refund':
						$list[$key]['way_name']='佣金撤回';
					break;
					
					case 'admin_change':
						$list[$key]['way_name']='管理员操作';
					break;
					
					case 'take_money':
						$list[$key]['way_name']='用户提现';
					break;
				}*/
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();  
		}
		/*
			 红包提现记录
		*/
		public function wechat_hb_list(){
			import("@.ORG.Page");
			$db=M('wechat_hb_list');
			$map=array('type'=>1);
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('id,nickname,name,username')->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();   
		}
		
		/*
			充值记录
		*/
		public function recharge(){
			import("@.ORG.Page");
			$db=M('recharge');
			//$map=array('type'=>1);
			$count = $db->where($map)->count();
			$Page = new Page($count,20);
			$list = $db->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			foreach($list as $key=>$val){
				$list[$key]['user']=M('wechat_user')->where(array('id'=>$val['uid']))->field('id,nickname,name,username')->find();
			}
			$show = $Page->show();
			$this->assign('show',$show);
			$this->assign('list',$list);
			$this->display();
		}
		
		
}