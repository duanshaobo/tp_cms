$.extend($,{
	_:function(s){if($.no(s))s=$.t();document.title=s;},
	//获取对象(不带缓存)
	$:function(o){return $.isS(o)?$('#'+o):$(o);},
	//获取对象(带缓存)
	o:function(o){if(!$.isS(o))return $(o);var obj=$.os[o];if(obj)return obj;obj=$('#'+o);if(obj.length>0)$.os[o]=obj;return obj;},os:{},
	//获取对象值
	v:function(o,v){if(v)$.$(o).val(v);else{return $.trim($.$(o).val());}},
	//返回当前时间
	t:function(){return $.d().getTime();},
	//返回当前时间对象
	d:function(t){if(t)return new Date(t);return new Date()},
	//转换成整数
	n:function(s){return parseInt(s);},
	//转换成浮点数
	f:function(s){return parseFloat(s);},
	//判断对象是否存在
	no:function(){var as=arguments;for(var i=0;i<as.length;i++)if(as[i]==null || as[i]==undefined)return true;return false;},
	//判断对象类型
	isS:function(o){return typeof o=="string"},
	isN:function(o){return typeof o=="number"},
	isB:function(o){return typeof o=="boolean"},
	isO:function(o){return typeof o=="object"},
	isInt:function(o){return $.isN(o)&&Math.round(o)==o},
	//是否包含指定内容
	cc:function(cs,c,n){var e=!$.no(n);for(var i=0;i<cs.length;i++)if((e && cs[i][n]==c) || (!e && cs[i]==c))return i;return -1;},
	//返回RegExp
	re:function(s,c){var r=new RegExp(s);if(c)return r.test(c);return r;},
	//是否IE浏览器
	ie:function(v){if(!$.browser.msie)return false;if(v)return $.browser.version==v || $.browser.version.indexOf(v+'.')==0;return true;},
	//创建DOC对象
	ceok:false,
	ce:function(n){return $(document.createElement(n));},
	winPos:function(timestamp){return window.location.href+(timestamp?+"?t="+new Date().valueOf():"")},
	//从字符串中获取第一个数值
	nv:function(s,sv){var si=-1,ei=-1,i=0;if(sv){i=s.indexOf(sv);if(i<0)i=0;}for(;i<s.length;i++)if(si==-1){if(s.charAt(i)>='0' && s.charAt(i)<='9')si=i;}else{if(s.charAt(i)<'0' || s.charAt(i)>'9'){ei=i;break;}}return $.n(si==-1 && ei==-1 ? -1 : (ei==-1 ? s.substr(si) : s.substring(si,ei)));},
	//数值四舍五入
	round:function(n,mantissa){if(!mantissa)mantissa=0;if(mantissa<=0)return Math.round(n);var v=1;for(var i=0;i<mantissa;i++)v*=10;return Math.round(n*v)/v;},
	//金额格式化
	formatMoney : function(num,n) {
	    num = String(num.toFixed(n?n:2));
	    var re = /(-?\d+)(\d{3})/;
	    while(re.test(num)) num = num.replace(re,"$1,$2")
	    return n?num:num.replace(/^([0-9,]+\.[1-9])0$/,"$1").replace(/^([0-9,]+)\.00$/,"$1");;
	},
	//字符串替换
	replace:function(s,s1,s2){return s.replace(new RegExp(s1,'g'),s2);},
	//字符串长度(中文算2个)
	strlen:function(s){return s.replace(/[^\x00-\xff]/g,"**").length},
	//字符串是否包含中文
	strch:function(s){return /[^\x00-\xff]+/.test(s)},
	//清除字符串中的'"字符和头尾空格
	clear:function(){var as=arguments,s;if(as.length<1)return '';s=as[0];if(as.length<2)as=[s,"'",'"'];for(var i=1;i<as.length;i++)s=$.replace(s,as[i],'');return $.trim(s);},
	//cookie操作
	getCookie:function(name,dv){var d=document.cookie;var il1=d.indexOf(name+'=');if(il1==-1)return $.no(dv) ? null : dv;il1+=name.length+1;var il2=d.indexOf(';',il1);if(il2==-1)il2=d.length;return decodeURI(d.substring(il1,il2));},
	setCookie:function(name,value,expires,path,domain,secure){var s=new Text()._(name)._('=')._(encodeURI(value));if(!expires || (expires && expires!='temp')){var day=60*60*24*1000;if(expires=='day')expires=$.d($.t()+day);else if(expires=='week')expires=$.d($.t()+day*7);else if(expires=='month')expires=$.d($.t()+day*30);else if(expires=='year')expires=$.d($.t()+day*365);else{expires=$.d($.t()+day*365*100);}s._(';expires=')._(expires.toGMTString());}if(path)s._(';path=')._(path);if(domain)s._(';domain=')._(domain);if(secure)s._(';secure=')._(secure);document.cookie=s;},
	delCookie:function(name,path,domain){var s=new Text()._(name)._('=null;expires=')._($.d($.t()-100000000).toGMTString());if(path)s._(';path=')._(path);if(domain!=null)s._(';domain=')._(domain);document.cookie=s;},
	clrCookie:function(path,domain){var ds=document.cookie.split(';');for(var i=0;i<ds.length;i++)$.delCookie($.trim(ds[i].split('=')[0]),path,domain);},
	//获取Flash对象
	getFlash:function(name){if($.ie())return window[name];else if($.browser.mozilla)return document[name+'-1'];else{var fl=window[name+'-1'];if(!fl)fl=window[name];if(!fl)fl=document[name+'-1'];return fl;}},
	//初始化对象
	init:function(o,dv){if(!o)return dv;for(i in dv)if($.no(o[i]))o[i]=dv[i];return o;},
	stringify  : function stringify(obj) {
        var t = typeof (obj);
        if (t != "object" || obj === null) {
            // simple data type
            if (t == "string") obj = '"' + obj + '"';
            return String(obj);
        } else {
            // recurse array or object
            var n, v, json = [], arr = (obj && obj.constructor == Array);
 
            for (n in obj) {
                v = obj[n];
                t = typeof(v);
                if (obj.hasOwnProperty(n)) {
                    if (t == "string") v = '"' + v + '"'; else if (t == "object" && v !== null) v = jQuery.stringify(v);
                    json.push((arr ? "" : '"' + n + '":') + String(v));
                }
            }
            return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
        }
    }
});
var X={
	//返回当前可用z-index值
	zi:function(){return X._zi++;},_zi:10001,
	//ajax标准请求
	ajax:function(url,data,suc,err){X.loading.show(); var s={url:url,cache:false,success:function(data){ X.loading.hide();suc(data); },error:function(){X.loading.hide();X.dialog.tips('请求发生异常，请重试或稍后再试');}};if(data){s.type='POST';s.data=data;}$.ajax(s);},
	//ajax标准请求
	ajaxjquery:function(url,data,suc,err){var s={url:url,cache:false,success:function(data){suc(data); },error:function(){}};if(data){s.type='POST';s.data=data;}$.ajax(s);},
	//返回页面空间
	pso:null,
	ps:function(){if(X.pso)return X.pso;X.wdb();X.pso={width:X.win.width(),height:X.win.height(),left:X.doc.scrollLeft(),top:X.doc.scrollTop()};return X.pso;},
	//初始化WinDocBody
	win:null,doc:null,body:null,
	wdb:function(){if(!X.win){X.win=$(window);X.doc=$(document);X.body=$(document.body);}}
};

///表单处理
X.form={
		button:{
			disable:function(btn){
				var btn=$("#"+btn);
				btn.prop('disabled',true);
				btn.attr({ "disabled": "disabled" });
			},
			enable:function(btn){
				var btn=$("#"+btn);
				btn.prop('disabled',false);
			},
			
		}
	};
function Text(){this.s;this.b=[];};Text.prototype={
		_:function(s){var t=this;t.b.push(s);t.s=null;return t;},
		clear:function(){this.b=[];this.s=null;},
		length:function(){return this.ts().length;},
		toHtml:function(o){o=$.$(o);if(o.length==0)return;o[0].innerHTML=this.ts();},
		toString:function(){var t=this;if(!t.s)t.s=t.b.join('');return t.s;},
		ts:function(){return this.toString();}
	};
//load框
X.loading={
		show:function(){
			if($('#loading').length<1){
				$("body").append('<div id="loading"><div class="load-wrap"><p></p></div></div>');
			}
			$('#loading').css('zIndex','99999').show();
		},
		hide:function(){
			$('#loading').hide();
		}
	};

//对话框
X.dialog={
	//打开的对话框、提示框、选择框、消息框、加载框索引
	dbs:[],
	//打开对话框 ps:topic(对话框主题名称),width,notify
	//db[索引，宽度，高度，信息层，背景层，回调]
	open:function(content,ps){
		ps=$.init(ps,{topic:'',width:280,notify:null});
		var t=this,w=ps.width,bgi=X.zi(),di=X.zi(),db=[di,w],p=X.ps(),ww=p.width,wh=p.height,dl=$.round((ww-w)/2),dt=-60,s=new Text();
		if(dl<10)dl=10;
		if(content)s._($.replace(content,'#di#',di));
		db[3]=$.ce('div');
		db[3].addClass('db-bg');
		db[3].css('zIndex',bgi);
		db[3].attr('id','dialog-bg-'+di);
		db[4]=$.ce('div');
		db[4].addClass('db-wrap');
		db[4].css('zIndex',di);
		db[4].css('width',w+'px');
		db[4].css('left',dl+'px');
		//db[4].css('display','none');
		db[4].attr('id','dialog-'+di);
		db[4].html(s.ts());
		db[5]=ps.notify;
		X.body.append(db[3],db[4]);
		db[2]=db[4].height();
		dt=$.round((wh-db[2])/2+dt);
		if(dt<10)dt=10;
		db[4].css('top',dt+'px');
		db[4].show();
		t.dbs.push(db);
	},
	//提醒对话框 ps:title,msg,icon,width,btn(按钮名称),notify
	alert:function(msg,ps){
		ps=$.init(ps,{title:'',msg:msg,width:280,btn:'确定'});
		if(ps.width<180)ps.width=180;
		var t=this,s=new Text();
		if(ps.title){
			s._('<div class="db-title">')._(ps.title)._('</div>');
		}
		s._('<div class="db-content">')._(msg)._('</div>');
		s._('<div class="db-foot">');
		if(ps.cfm)
			t.addBtn(s,ps.btn1,2);
		if(ps.ensure){
			t.addBtn(s,ps.btn,1);
		}else{
			t.addBtn(s,ps.btn,0);
		}
		s._('</div>');
		t.open(s.ts(),ps);
	},
	//确认对话框 ps:title,msg,icon,width,btn(按钮名称),btn1(第二个按钮名称),notify
	confirm:function(msg,ps){
		ps=$.init(ps,{ensure:true,msg:msg,btn1:'取消'});
		ps.cfm=true;
		this.alert(msg,ps)
	},
	tips:function(msg){
		if(!($('.db-tip-warp')[0])){
			clearTimeout(window.tipsTimer);
	        var a = $('<div class="db-tip-warp"><div class="db-tip">' + msg + "</div></div>");
        
        	$("body").append(a);
            window.tipsTimer = setTimeout(function() {
                $(".db-tip-warp").remove()
            }, 2000);
        }
	},
	//添加按钮代码(对话框按钮)
	addBtn:function(s,name,nt){
		this.addButton(s,{id:'dialog-btn-#di#',name:name,click:'X.dialog.close(#di#,'+nt+');'});
	},
	//添加按钮代码 ps:id,name,css(按钮CSS),style,click(点击事件),effects(点击效果CSS),type(是按钮或者连接样式)
	addButton:function(s,ps){
		s._('<p');
		if(ps.id)
			s._(' id="')._(ps.id)._('"');
		if(ps.css)
			s._(' class="')._(ps.css)._('"');
		if(ps.style)
			s._(' style="')._(ps.style)._('"');
		if(ps.click)
			s._(' onclick="')._(ps.click)._('"');
		s._('>'+ps.name+'</p>');
	},
	//操作通知
	notify:function(di,nt){
		var db=this.get(di);
		if(db[5])db[5]($.no(nt)?0:nt);
	},
	//关闭对话框或提示框(0不关闭,1关闭,2关闭并关闭上级对话框)
	close:function(di,nt){
		var t=this,b,l,c=1,cn,cv;
		if($.no(nt))nt=0;
		if(!$.no(di)){
			b=t.get(di);
			if(b){
				if($.isN(di)){
					if(b[5]){
						if(nt != 0){
							b[5](nt);
							t.get(di,true);b[4].remove();b[3].remove();	
						}else{
							b[5](nt,function(){	t.get(di,true);b[4].remove();b[3].remove();})
						}
					}else{						
						t.get(di,true);b[4].remove();b[3].remove();
						wx.closeWindow();

					}
				}
			}else{return;}
		}
		//关闭最后打开的对话框
//		l=t.dbs.length;
//		if(l>0)t.close(t.dbs[l-1][0],0);
	},
	//返回对话框
	get:function(di,del){
		var t=this,bs=t.dbs,b;
		for(var i=0;i<bs.length;i++){
			if(bs[i][0]==di){
				b=bs[i];
				if(del)
					bs.splice(i,1);
				break;
			}
		}
		return b;
	},
	//调整大小位置
	resize:function(){
		var t=this,dbs=t.dbs,pbs=t.pbs,mbs=t.mbs;
		if(dbs.length==0 && pbs.length==0 && mbs.length==0)
			return;
		var p=X.ps(),ww=p.width,wh=p.height,dl,dt,top=-30,o,obj;
		if($.ie(6))top+=p.top;
		for(var i=0;i<dbs.length;i++)
		{
			o=dbs[i];
			dl=$.round((ww-o[1])/2);
			if(dl<6)dl=6;
			dt=$.round((wh-o[2])/2+top);
			if(dt<6)dt=6;
			o[3].css('width',ww+'px');
			o[3].css('height',wh+'px');
			o[4].css('top',dt+'px');
			o[4].css('left',dl+'px');
		}
		for(var i=0;i<pbs.length;i++)
		{
			o=pbs[i];
			obj=$.$(o[4]);
			if(obj.length>0)
			{
				p=obj.offset();
				ww=p.left+obj.width()/2;
				wh=p.top+obj.height()+5;
				if(o[2]%2==1)ww-=15;else{ww-=o[1]-15;}
				o[5].css('top',wh+'px');
				o[5].css('left',ww+'px');
			}
		}
		for(var i=0;i<mbs.length;i++)
		{
			o=mbs[i];
			if(o!=null && o.length>4)
			{
				dl=$.round((ww-o[5])/2);
				o[4].css('left',dl+'px');
			}
		}
	}
};
X.wxtip={
	show:function (){
		var isWX = (navigator.userAgent.indexOf("MicroMessenger") > -1);
		if(isWX && !sessionStorage.getItem('wx-tip')){
			$("body").append('<div id="loading"></div><div id="wxtip" class="wx-tip" onclick="X.wxtip.hide();"></div>');
		}
	},
	hide:function (){
		$('#loading').remove();
		$('#wxtip').remove();
		sessionStorage.setItem('wx-tip','WXYIP');
	}
};


/*定时*/
X.timing={
	setInterval:function(func,time){
	var hander= setInterval(function () {
          clearInterval(hander);
          func();
		},time);
	}
}

X.domain = ".ydl.com";

X.code = {
		argument		:	"301",
		loseSecurityCode:	"302",
		unauthorized	:	"405",
		nameUnsettled	:	"407",
		notifyInterval	:	"601",
		schemeError		:	"700",
		balanceShortage	:	"800",
		rebate			:	"801",
		system			:	"900"
};

X.keypress={
		numKeyPress:function(e){
			var k = e.keyCode || e.which;
			if(k>=48&&k<=57||k==8){
				return true;
			}
			return false;
		}
	}

X.location= {
	getQueryString:function(name){
	    var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
	    var r = window.location.search.substr(1).match(reg);
	    
	    if (r != null) {
	        return unescape(r[2]);
	    }
	    return null;
	},
	toUrl:function(url){
		window.location.href=url;
	}
}

X.com={
	loadMore:function(unlock){
		var page=parseInt($("#list").data('page'));
		
		if(!page) page=1;
		
		X.ajax($.winPos(),{page:page,type:'post'},function(rs){
			
			rs=eval("("+rs+")");
			
			if(rs.data.length<20){
				$('#loadMore').attr('onClick','').show().find('p').html('没有更多了');
				$('#list').data('isloaded',1);
			}
			
			$("#list").data('page',page+1);
			
			var tpl=$("#list-tpl").html();
			var html=juicer(tpl,{list:rs.data});
			$("#list").append(html);
			
			unlock();

		});
		
	}
	



}
X.com1={
		loadMore:function(unlock){
			var page=parseInt($("#list").data('page'));
			if(!page) page=1;
			if($('#list').data('isloaded')==1)
			{
				unlock();
				return false;
			}
			X.ajax($.winPos(),{page:page,type:'post'
				},function(rs){
					
				//把返回的字符串数据转换为对象	
				rs=eval("("+rs+")");
				
				if(rs.data.length<20){
					$('#loadMore').show().find('p').html('没有更多了');
					$('#list').data('isloaded',1);
				}
				
				$("#list").data('page',page+1);
				
				
				var tpl=$("#list-tpl").html();
				
				var html=juicer(tpl,{list:rs.data});
				
				$("#list").append(html);
				
				unlock();

			});
			
			
		}



}

X.com2={
		//一次加载多页
		loadMoree:function(unlock,num,id){
			if(num==null ) {num=1;}else{$("#list").data('page',num);num=num;}
			X.ajax('/consult/DoctorListAll',{page:num,type:'post',method:'all',id:id
				},function(rs){
					
				//把返回的字符串数据转换为对象	
				rs=eval("("+rs+")");
				
				if(rs.data.length%20 !== 0){
					$('#loadMore').show().find('p').html('没有更多了');
					$('#list').data('isloaded',1);
				}		
				var tpl=$("#list-tpl").html();
				
				var html=juicer(tpl,{list:rs.data});
				
				$("#list").append(html);
				
				unlock();

			});								
			

			}
}

/* 浏览器检测 发现费手机浏览器则直接跳转到网页地址 */
// try {
// 	var urlhash = window.location.hash;	
// 	if (!urlhash.match("fromapp")) {
// 		if (!(navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i))) {
// 			window.location.href="http://www.yztz.com";
// 		}
// 	}
//  }
//  catch(err) {
//  }