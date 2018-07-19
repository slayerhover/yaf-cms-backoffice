$(function(){
	InitLeftMenu();
	tabClose();
	tabCloseEven();
	clockon();
})

//初始化左侧
function InitLeftMenu() {
	$('.navMenu li a').click(function(){
		var tabTitle = $(this).text();
		var url = $(this).attr("href");
		addTab(tabTitle,url);
		$('.navMenu li').removeClass("active");
		$(this).parent().addClass("active");
		return false;
	}).hover(function(){
		$(this).parent().addClass("hover");
	},function(){
		$(this).parent().removeClass("hover");
	});
}

function addTab(subtitle,url){
	if(!$('#rightTabs').tabs('exists',subtitle)){
		$('#rightTabs').tabs('add',{
			title:subtitle,
			href :url,
			closable:true,
			width:$('#rightTabs').width()-10,
			height:$('#rightTabs').height()-26
		});
	}else{
		$('#rightTabs').tabs('select',subtitle);		
	}
	tabClose();
}
function tabClose()
{
	/*双击关闭TAB选项卡*/
	$(".tabs-inner").dblclick(function(){
		var subtitle = $(this).children("span").text();
		if(subtitle !='首页')$('#rightTabs').tabs('close',subtitle);
	})

	$(".tabs-inner").bind('contextmenu',function(e){
		$('#mm').menu('show', {
			left: e.pageX,
			top: e.pageY,
		});
		
		var subtitle =$(this).children("span").text();
		$('#mm').data("currtab",subtitle);
		
		return false;
	});
}
//绑定右键菜单事件
function tabCloseEven()
{
	//关闭当前
	$('#mm-tabclose').click(function(){
		var currtab_title = $('#mm').data("currtab");
		if(currtab_title!='首页')$('#rightTabs').tabs('close',currtab_title);
	})
	//全部关闭
	$('#mm-tabcloseall').click(function(){
		$('.tabs-inner span').each(function(i,n){
			var t = $(n).text();
			if(t!='首页')$('#rightTabs').tabs('close',t);
		});	
	});
	//关闭除当前之外的TAB
	$('#mm-tabcloseother').click(function(){
		var currtab_title = $('#mm').data("currtab");
		$('.tabs-inner span').each(function(i,n){
			var t = $(n).text();
			if(t!=currtab_title)
				$('#rightTabs').tabs('close',t);
		});	
	});
	//关闭当前右侧的TAB
	$('#mm-tabcloseright').click(function(){
		var nextall = $('.tabs-selected').nextAll();
		if(nextall.length==0){
			msgShow('系统提示','后边没有啦~~','warning');
			//alert('后边没有啦~~');
			return false;
		}
		nextall.each(function(i,n){
			var t=$('a:eq(0) span',$(n)).text();
			$('#rightTabs').tabs('close',t);
		});
		return false;
	});
	//关闭当前左侧的TAB
	$('#mm-tabcloseleft').click(function(){
		var prevall = $('.tabs-selected').prevAll();
		if(prevall.length==0){
			msgShow('系统提示','到头了，前边没有啦~~','warning');
			return false;
		}
		prevall.each(function(i,n){
			var t=$('a:eq(0) span',$(n)).text();
			$('#rightTabs').tabs('close',t);
		});
		return false;
	});

	//退出
	$("#mm-exit").click(function(){
		$('#mm').menu('hide');
	})
}

//弹出信息窗口 title:标题 msgString:提示信息 msgType:信息类型 [error,info,question,warning]
function msgShow(title, msgString, msgType) {
	$.messager.alert(title, msgString, msgType);
}
function dump_obj(obj) {  
	var s = "";  
	for (var property in obj) {  
		s = s + "\r\n" + property +": " + obj[property];  
	}  
 	alert(s);
}  
function clockon() {
    var now = new Date();
    var year = now.getFullYear(); //getFullYear getYear
    var month = now.getMonth();
    var date = now.getDate();
    var day = now.getDay();
    var hour = now.getHours();
    var minu = now.getMinutes();
    var sec = now.getSeconds();
    var week;
    month = month + 1;
    if (month < 10) month = "0" + month;
    if (date < 10) date = "0" + date;
    if (hour < 10) hour = "0" + hour;
    if (minu < 10) minu = "0" + minu;
    if (sec < 10) sec = "0" + sec;
    var arr_week = new Array("星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
    week = arr_week[day];
    var time = "";
	if  ( day==0 )  time="<font class=\"up-font-over\">";  
    if  ( day > 0 && day < 6 )  time="<font class=\"up-fonts\">";  
    if  ( day==6 )  time="<font class=\"up-font-over\">";  
    time+= year + "年" + month + "月" + date + "日" + " " + hour + ":" + minu + ":" + sec + " " + week;
	time+="</font>"  

    $("#localtime").html(time);

    var timer = setTimeout("clockon()", 200);
}
function toRepwd(){
	 $(function(){
		var idd = 1;
		$("#repwd").dialog({
			title:'修改密码',
			resizable:true,
			width:400,
			height:230,
			href:'/EasyWork/index.php?s=/User/repwd/id/'+idd,
			onOpen:function(){
				cancel['Repwd'] = $(this);
			},
		});	 
	 });
 }
 function toShowSms(){
	 $(function(){
		$("#setpwd").dialog({
			title:'我的信息',
			resizable:true,
			width:580,
			height:353,
			href:'/EasyWork/index.php?s=/index/showsms/act/1',
			onOpen:function(){
				//cancel['Sendsms'] = $(this);
			},
			onClose:function(){
				//cancel['SmsDetail'].dialog('destroy');
				//cancel['SmsDetail'].dialog('close');
				//cancel['SmsDetail'] = null;
				//cancel['Sendmail'] = null;
			}
		});	 
	 });
 }
 
 function toSetpwd(){
	 $(function(){
		var idd = 1;
		$("#setpwd").dialog({
			title:'邮箱设置',
			resizable:true,
			width:450,
			height:255,
			href:'/EasyWork/index.php?s=/User/setpwd/id/'+idd,
			onOpen:function(){
				cancel['Setpwd'] = $(this);
			},
		});	 
	 });
 }
 
 function toSendMail(){
	var idd = 1;
	$("<div />").dialog({
		title:'发邮件',
		resizable:true,
		width:900,
		height:435,
		href:'/EasyWork/index.php?s=/Public/Mail/index/mode/1/id/'+idd,
		onOpen:function(){
			cancel['Sendmail'] = $(this);
		},
		onClose:function(){
			cancel['Sendmail'].dialog('destroy');
			cancel['Sendmail'] = null;
		}
	});
 }
 
$(function(){
	$.get('index.php?s=/index/getsms',function(data){
		if(data>0){
			$("#smsid").html(data);
			$("#smsid").attr("title","您有"+data+"条未读通知");
		}else{
			$("#smsid").html("0");
			$("#smsid").attr("title","您没有未读通知");
		}
	});
	//tick();
});