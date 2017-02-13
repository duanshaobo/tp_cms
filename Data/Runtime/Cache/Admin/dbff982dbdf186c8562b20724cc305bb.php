<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html><!--[if IE 8]><html lang="en" class="ie8"><![endif]--><!--[if IE 9]><html lang="en" class="ie9"><![endif]--><!--[if !IE]><!--><html lang="en"><!--<![endif]--><!-- BEGIN HEAD --><head><meta charset="utf-8" /><title><?php echo ($config["web_title"]); ?>-管理中心</title><meta content="width=device-width, initial-scale=1.0" name="viewport" /><meta content="" name="description" /><meta content="" name="author" /><!-- BEGIN GLOBAL MANDATORY STYLES --><link href="__PUBLIC__/css/bootstrap.min.css" rel="stylesheet" type="text/css"/><link href="__PUBLIC__/css/bootstrap-responsive.min.css" rel="stylesheet" type="text/css"/><link href="__PUBLIC__/css/font-awesome.min.css" rel="stylesheet" type="text/css"/><link href="__PUBLIC__/css/style-metro.css" rel="stylesheet" type="text/css"/><link href="__PUBLIC__/css/style.css" rel="stylesheet" type="text/css"/><link href="__PUBLIC__/css/style-responsive.css" rel="stylesheet" type="text/css"/><link href="__PUBLIC__/css/default.css" rel="stylesheet" type="text/css" id="style_color"/><link href="__PUBLIC__/css/uniform.default.css" rel="stylesheet" type="text/css"/><!-- END GLOBAL MANDATORY STYLES --><!-- BEGIN PAGE LEVEL STYLES --><link rel="stylesheet" type="text/css" href="__PUBLIC__/css/select2_metro.css" /><link rel="stylesheet" href="__PUBLIC__/css/DT_bootstrap.css" /><!-- END PAGE LEVEL STYLES --><!--<link rel="shortcut icon" href="__PUBLIC__/image/favicon.ico" />--><link rel="shortcut icon" href="__PUBLIC__/images/icon.png" /><script src="__PUBLIC__/js/jquery-1.3.2.min.js"></script></head><!-- END HEAD --><!-- BEGIN BODY --><body class="page-header-fixed"><!-- BEGIN HEADER --><div class="header navbar navbar-inverse navbar-fixed-top"><!-- BEGIN TOP NAVIGATION BAR --><div class="navbar-inner"><div class="container-fluid"><!-- BEGIN LOGO --><a class="brand" href="<?php echo U('Index/index');?>"><img src="__PUBLIC__/image/logo.png" alt="logo" />                 &nbsp;
                <span style="color:#FFF;"><?php echo ($config["web_title"]); ?></span></a><!-- END LOGO --><!-- BEGIN RESPONSIVE MENU TOGGLER --><a href="javascript:;" class="btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse"><img src="__PUBLIC__/image/menu-toggler.png" alt="" /></a><!-- END RESPONSIVE MENU TOGGLER --><!-- BEGIN TOP NAVIGATION MENU --><ul class="nav pull-right"><!-- BEGIN NOTIFICATION DROPDOWN --><!-- END NOTIFICATION DROPDOWN --><!-- BEGIN INBOX DROPDOWN --><!-- END INBOX DROPDOWN --><!-- BEGIN TODO DROPDOWN --><!-- END TODO DROPDOWN --><!-- BEGIN USER LOGIN DROPDOWN --><li class="dropdown user"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="icon icon-user" style="font-size:28px;"></span><span class="username"><?php echo I('session.username');?></span><i class="icon-angle-down"></i></a><ul class="dropdown-menu"><li><a href="<?php echo U('Index/myinfo');?>"><i class="icon-user"></i> 个人资料</a></li><li class="divider"></li><li><a href="<?php echo U('Login/logout');?>"><i class="icon-key"></i>安全退出</a></li></ul></li><!-- END USER LOGIN DROPDOWN --></ul><!-- END TOP NAVIGATION MENU --></div></div><!-- END TOP NAVIGATION BAR --></div><!-- END HEADER --><!-- BEGIN CONTAINER --><div class="page-container row-fluid"><!-- BEGIN SIDEBAR --><div class="page-sidebar nav-collapse collapse"><!-- BEGIN SIDEBAR MENU --><ul class="page-sidebar-menu"><li><!-- BEGIN SIDEBAR TOGGLER BUTTON --><div class="sidebar-toggler hidden-phone"></div><!-- BEGIN SIDEBAR TOGGLER BUTTON --></li><li><!-- BEGIN RESPONSIVE QUICK SEARCH FORM --><!--<form class="sidebar-search"><div class="input-box"><a href="javascript:;" class="remove"></a><input type="text" placeholder="Search..."><input type="button" class="submit" value=" "></div></form>--><!-- END RESPONSIVE QUICK SEARCH FORM --></li><li class="start active"><a href="<?php echo U('Index/index');?>"><i class="icon-home"></i><span class="title">管理中心</span><span class="selected"></span></a></li><li class=""><a href="javascript:;"><i class="icon-cogs"></i><span class="title">角色权限管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('RBAC/role');?>">角色列表</a></li><li><a href="<?php echo U('RBAC/index');?>">管理员列表</a></li><li><a href="<?php echo U('RBAC/node');?>">操作节点管理</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-file-text"></i><span class="title">试题管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Exam/index');?>">试题管理</a></li><li><a href="<?php echo U('Exam/pager_list');?>">试卷管理</a></li><li><a href="<?php echo U('Exam/exam_log');?>">考试记录</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-align-justify"></i><span class="title">订单管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Order/index');?>">在线咨询订单</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-comment"></i><span class="title">咨询管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Chat/index');?>">咨询内容管理</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-group"></i><span class="title">用户管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Wxusers/index');?>">微信用户管理</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-user"></i><span class="title">专家管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Wxusers/index',array('role_id'=>2));?>">专家列表</a></li><li><a href="<?php echo U('Wxusers/reg_list');?>">待审核专家</a></li><li><a href="<?php echo U('Skill/index');?>">专家领域管理</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-cog"></i><span class="title">系统信息</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Conf/index');?>">平台信息管理</a></li><li><a href="<?php echo U('Log/index');?>">操作日志管理</a></li><li><a href="<?php echo U('Delcache/index');?>">清除系统缓存</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-comments"></i><span class="title">微信公众平台管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('WechatPub/index');?>">公众号管理</a></li><li><a href="<?php echo U('WechatPub/index');?>">关注外链</a></li><li><a href="<?php echo U('WechatMenu/index');?>">自定义菜单</a></li><li><a href="<?php echo U('WechatText/index');?>">自定义回复</a></li><li><a href="<?php echo U('WechatPub/tpl_msg');?>">模板消息</a></li></ul></li><li class="" style="display:none"><a href="javascript:;"><i class="icon-briefcase"></i><span class="title">Pages</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="page_coming_soon.html"><i class="icon-cogs"></i>                    Coming Soon</a></li><li><a href="page_calendar.html"><i class="icon-calendar"></i>                    Calendar</a></li></ul></li><li style="display:none"><a class="active" href="javascript:;"><i class="icon-sitemap"></i><span class="title">商城管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="javascript:;">                    商品管理

                    <span class="arrow"></span></a><ul class="sub-menu"><li><a href="<?php echo U('Goods/index');?>">商品列表</a></li><li><a href="<?php echo U('GoodsCate/index');?>">商品分类</a></li><li><a href="<?php echo U('Goods/reply_list');?>">商品评论</a></li></ul></li><li><a href="javascript:;">                    订单管理

                    <span class="arrow"></span></a><ul class="sub-menu"><li><a href="<?php echo U('Order/index');?>">销售订单</a></li><li><a href="<?php echo U('TakeMoney/index');?>">提现申请</a></li><li><a href="<?php echo U('Order/refund_list');?>">售后订单</a></li></ul></li></ul></li><li style="display:none"><a href="javascript:;"><i class="icon-folder-open"></i><span class="title">4 Level Menu</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="javascript:;"><i class="icon-cogs"></i>                    Item 1

                    <span class="arrow"></span></a><ul class="sub-menu"><li><a href="javascript:;"><i class="icon-user"></i>                            Sample Link 1

                            <span class="arrow"></span></a><ul class="sub-menu"><li><a href="#"><i class="icon-remove"></i> Sample Link 1</a></li></ul></li><li><a href="#"><i class="icon-user"></i>  Sample Link 1</a></li></ul></li><li><a href="javascript:;"><i class="icon-globe"></i>                    Item 2

                    <span class="arrow"></span></a><ul class="sub-menu"><li><a href="#"><i class="icon-user"></i>  Sample Link 1</a></li></ul></li></ul></li><li class=""><a href="javascript:;"><i class="icon-th"></i><span class="title">财务管理</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Money/recharge');?>">充值记录</a></li><li><a href="<?php echo U('Money/index');?>">资金明细</a></li><li><a href="<?php echo U('Money/wechat_hb_list');?>">红包发送记录</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-file-text"></i><span class="title">CMS内容管理系统</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('CmsSort/index');?>">栏目管理</a></li><li><a href="<?php echo U('CmsArt/index');?>">文章管理</a></li></ul></li><li class=""><a href="javascript:;"><i class="icon-gift"></i><span class="title">扩展工具</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Navlink/index');?>">前台导航管理</a></li><li><a href="<?php echo U('Region/index');?>">地区管理</a></li><li><a href="<?php echo U('FriendLink/index');?>">友情链接</a></li><li><a href="<?php echo U('Slide/index');?>">轮播图片管理</a></li></ul></li><!--<li class=""><a href="javascript:;"><i class="icon-hdd"></i><span class="title">数据中心</span><span class="arrow "></span></a><ul class="sub-menu"><li><a href="<?php echo U('Database/index');?>">数据备份</a></li><li><a href="<?php echo U('Database/recover');?>">备份下载</a></li></ul></li>--><li class="last" style="display:none"><a href="charts.html"><i class="icon-bar-chart"></i><span class="title">Visual Charts</span></a></li></ul><!-- END SIDEBAR MENU --></div><script type="text/javascript">$(document).ready(function() {
	var url=window.location.search;
	$('.page-sidebar-menu li ul li a').each(function(){
		var aurl='?'+$(this).attr("href").split("?")[1];
		
		var p1=$(this).parent();
		var p3=$(this).parent().parent().parent();
		var p5=$(this).parent().parent().parent().parent().parent();
		if(aurl.indexOf(url)>=0){
			//$(this).addClass("active").siblings("a").removeClass("active");
			$(".start").removeClass("active");
			p1.addClass("active");
			p3.addClass("active");
			p5.addClass("active");
			//$(this).parent().parent().css("display","block");
			
		}
	});
});
</script><!-- END SIDEBAR --><!-- BEGIN PAGE --><div class="page-content"><!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM--><div id="portlet-config" class="modal hide"><div class="modal-header"><button data-dismiss="modal" class="close" type="button"></button><h3>modal 标题</h3></div><div class="modal-body"><p>modal 内容</p></div></div><!-- END SAMPLE PORTLET CONFIGURATION MODAL FORM--><!-- BEGIN PAGE CONTAINER--><div class="container-fluid"><!-- BEGIN PAGE HEADER--><div class="row-fluid"><div class="span12"><!-- BEGIN STYLE CUSTOMIZER --><!--主题设置--><div class="color-panel hidden-phone"><a href="<?php echo U('Delcache/index');?>" title="清除系统缓存"><div class="color-mode-icons icon-color"></div></a></div><!-- END BEGIN STYLE CUSTOMIZER --><!-- BEGIN PAGE TITLE & BREADCRUMB--><h3 class="page-title">订单列表</h3><!-- END PAGE TITLE & BREADCRUMB--></div></div><!-- END PAGE HEADER--><!-- BEGIN PAGE CONTENT--><div class="row-fluid"><div class="span12"><!-- BEGIN EXAMPLE TABLE PORTLET--><!--页面主体内容--><div class="portlet box light-grey"><div class="portlet-title"><div class="caption"><i class="icon-globe"></i>订单列表</div><div class="tools"><a href="javascript:;" class="collapse"></a><a href="javascript:;" class="remove"></a></div><div class="actions"><a href="<?php echo U('export_excel');?>" class="btn green"><i class="icon-share"></i> 导出EXCEL</a></div></div><div class="portlet-body"><div class="well form-inline">                搜索：
                <select name="key" style="width:auto !important"><option value="out_trade_no"  <?php if(($_GET['key']) == "order_sn"): ?>selected<?php endif; ?> >订单编号</option><option value="consignee" <?php if(($_GET['key']) == "consignee"): ?>selected<?php endif; ?> >下单人</option><option value="mobile" <?php if(($_GET['key']) == "mobile"): ?>selected<?php endif; ?> >联系电话</option></select><input value="<?php echo ($_GET['val']); ?>" name="val" type="text" class="text-input bg-gray small-input" placeholder="关键字"/>                 下单时间：
                <input value="<?php echo ($_GET['begin_time']); ?>" name="begin_time" type="text" class="text-input bg-gray small-input" placeholder="起始时间" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})"/> ~
                <input value="<?php echo ($_GET['end_time']); ?>" name="end_time" type="text" class="text-input bg-gray small-input" placeholder="截止时间" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})"/><input id='btn-so' type="button" value="搜索" class="btn green"/>                &nbsp;&nbsp;&nbsp;
                <a  href="<?php echo U('index',array('p'=>I('get.p',1)));?>">查看全部</a>                &nbsp;&nbsp;&nbsp;
                <a class="btn <?php if(($_GET['order_status']) == "1"): ?>green<?php endif; ?>" href="<?php echo U('index',array('order_status'=>1));?>">未完成</a><a class="btn <?php if(($_GET['order_status']) == "2"): ?>green<?php endif; ?>" href="<?php echo U('index',array('order_status'=>2));?>">已完成</a><!--                <a class="btn <?php if(($_GET['order_status']) == "3"): ?>green<?php endif; ?>" href="<?php echo U('index',array('order_status'=>3));?>">已签收</a>--><a class="btn <?php if(($_GET['pay_status']) == "nopay"): ?>red<?php endif; ?>" href="<?php echo U('index',array('pay_status'=>'nopay'));?>">未支付</a></div><table class="table table-striped table-bordered table-hover"><thead><tr><th>订单ID</th><th>订单编号</th><th>下单人</th><th>专家姓名</th><th>联系电话</th><th>订单金额</th><!--<th>订单佣金</th>--><th>订单状态</th><th>支付状态</th><th>下单时间</th><th class="hidden-480">操作</th></tr></thead><tbody><?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><tr><td>【<?php echo ($v["id"]); ?>】</td><td width='120'><a title="点击查看" href="<?php echo U('edit',array('id'=>$v['id']));?>"><?php echo ($v["out_trade_no"]); ?></a></td><td><?php echo ($v["name"]); ?></td><td><a target="_blank" href="<?php echo U('Wxusers/edit',array('id'=>$v['expert_id']));?>"><?php echo ($v['expert']['nickname']); ?></a></td><td><?php echo ($v["mobile"]); ?></td><td style="color:red"><b>&yen; </b><?php echo ($v["total_fee"]); ?></td><!--<td style="color:red"><b>&yen; </b><?php echo ($v["yongjin"]); ?></td>--><td><?php switch($v["order_status"]): case "1": ?><span class="label label-warning">未完成</span><?php break; case "2": ?><b><span class="label label-success">已完成</span></b><?php break;?><!--<?php case "2": ?><b><span class="label">已退款</span></b><?php break;?>--><?php endswitch;?></td><td><a title="修改订单支付状态" href="<?php echo U('update_pay_status',array('order_id'=>$v['id'],'p'=>I('get.p','1')));?>"><?php switch($v["pay_status"]): case "0": ?><span class="label label-warning">未支付</span><?php break; case "1": ?><b><span class="label label-success">已支付</span></b><?php break; case "2": ?><b><span class="label">已退款</span></b><?php break; endswitch;?></a></td><td><?php echo (date('Y/m/d H:i:s',$v["order_time"])); ?></td><td><?php if($v['pay_status'] == 1): ?><a class="btn red btn-refund" href="javascript:" order_id="<?php echo ($v["id"]); ?>">退款</a><?php endif; ?><!--<a class="btn btn-success" href="<?php echo U('edit',array('id'=>$v['id']));?>">详情</a>--><a class="btn red" onclick="return confirm('确定删除？')" href="<?php echo U('del',array('id'=>$v['id']));?>">删除</a></td></tr><?php endforeach; endif; else: echo "" ;endif; ?></tbody></table><div class="pagination"><?php echo ($show); ?></div></div></div><style>.small-input{width:120px !important;}
</style><script type='text/javascript'>  $(function(){
	 
	  	$(document).keyup(function(event){
			if(event.keyCode==13){
				$("#btn-so").click();
			}
		});
	  
	  $("#btn-so").click(function(){
		  
		  var param='';
		  
		  var key=$("select[name='key'] option:selected").val();
		  var val=$("input[name='val']").val();
		  
		  var begin_time=$("input[name='begin_time']").val();
		  var end_time=$("input[name='end_time']").val();
		  
		 
		  
		  if(key!=''&&val!=''){
			 param+="&key="+key+'&val='+val;
		  }
		  
		  if(begin_time!=''){
			   param+='&begin_time='+begin_time;
		  }
		  
		  if(end_time!=''){
			   param+='&end_time='+end_time;
		  }
		  
		  if(param==''){
			  alert('请输入搜索条件！')
			  //artDialog({content:'请输入搜索条件！', style:'alert', lock:false}, function(){});
		  }else{
			  location.href="<?php echo U('index');?>"+param;
		  }
		  
	  });
	  
	  
	   //订单退款
	  $(".btn-refund").click(function(){
			var post_data={};
			post_data.id=$(this).attr('order_id');			//订单号
			if(confirm('确定退款？')){
				$.post("<?php echo U('Ajax/order_refund');?>",post_data,function(data){
					alert('操作成功');
					location.reload();	
				}); 
			}
			
	  });
	  
  })
</script><!--页面主体内容--><!-- END EXAMPLE TABLE PORTLET--></div></div><!-- END PAGE CONTENT--></div><!-- END PAGE CONTAINER--></div><!-- END PAGE --></div><!-- END CONTAINER --><!-- BEGIN FOOTER --><div class="footer"><div class="footer-inner"><?php echo ($config["copyright"]); ?><a href="#" title="<?php echo ($config["web_name"]); ?>" target="_blank"><?php echo ($config["web_name"]); ?></a></div><div class="footer-tools"><span class="go-top"><i class="icon-angle-up"></i></span></div></div><!-- END FOOTER --><script type="text/javascript" src="__PUBLIC__/artDialog/artDialog.js?skin=aero"></script><script type="text/javascript" src="__PUBLIC__/datepicker/WdatePicker.js"></script><script type="text/javascript" src="__PUBLIC__/js/common.js"></script><!-- BEGIN CORE PLUGINS --><script src="__PUBLIC__/js/jquery-1.10.1.min.js" type="text/javascript"></script><script src="__PUBLIC__/js/jquery-migrate-1.2.1.min.js" type="text/javascript"></script><script src="__PUBLIC__/js/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script><script src="__PUBLIC__/js/bootstrap.min.js" type="text/javascript"></script><!--[if lt IE 9]><script src="__PUBLIC__/js/excanvas.min.js"></script><script src="__PUBLIC__/js/respond.min.js"></script><![endif]--><script src="__PUBLIC__/js/jquery.slimscroll.min.js" type="text/javascript"></script><script src="__PUBLIC__/js/jquery.blockui.min.js" type="text/javascript"></script><script src="__PUBLIC__/js/jquery.cookie.min.js" type="text/javascript"></script><script src="__PUBLIC__/js/jquery.uniform.min.js" type="text/javascript" ></script><!-- END CORE PLUGINS --><!-- BEGIN PAGE LEVEL PLUGINS --><script type="text/javascript" src="__PUBLIC__/js/select2.min.js"></script><script type="text/javascript" src="__PUBLIC__/js/jquery.dataTables.js"></script><script type="text/javascript" src="__PUBLIC__/js/DT_bootstrap.js"></script><!-- END PAGE LEVEL PLUGINS --><!-- BEGIN PAGE LEVEL SCRIPTS --><script src="__PUBLIC__/js/app.js"></script><script src="__PUBLIC__/js/table-managed.js"></script><script>		jQuery(document).ready(function() {       

		   App.init();

		   TableManaged.init();

		});

	</script></body><!-- END BODY --></html>