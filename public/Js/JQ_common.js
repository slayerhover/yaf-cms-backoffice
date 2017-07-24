$(document).ready(function () {
    /*下拉菜单*/
    navFun();
    //ScrollBar();
    /*头部导航切换*/
    var s1 = $(".nav_popupl .nav_tit li span");
    s1.hover(function () {
        var index = s1.index(this) + 1;
        $(".nav_list div").hide();
        $("#nav_txt" + index).show();
        s1.removeClass("hover");
        $(this).addClass("hover");
    });

    //LOGO下拉
    $(".h_btn span.btn02").mouseenter(function () {
        $(".h_box").show();
    });
    $(".h_btn").mouseleave(function () {
        $(".h_box").hide();
    });

    /*头部导航切换*/
    //    var s11 = $(".nav_popupll .nav_tit li span");
    //    s11.hover(function () {
    //        s11.removeClass("hover");
    //        $(this).addClass("hover");
    //    });

    /*头部导航切换*/

    /*中部内容切换*/
    var s2 = $(".index_tit2 li");
    $('.index_news div:first').css({ "display": "block" });
    s2.click(function () {
        var index = s2.index(this) + 1;
        $(".index_news .none").css({ "display": "none" });
        $("#index_txt" + index).css({ "display": "block" });
        s2.removeClass("hover");
        $(this).addClass("hover");
    });

    $("#firstpane p.menu_head").click(function () {
        $(this).next("div").slideToggle(300).siblings("div.menu_body:visible").slideUp("slow");
        $(this).toggleClass("hover");
        $(this).siblings(".hover").removeClass("hover");
    });

    /*中部内容切换*/
    //	var s3 = $(".inside_list li span");
    //	$('.inside_tab div:first').css({"display":"block"});
    //	s3.click(function() {
    //		var index = s3.index(this)+1;
    //		$(".inside_tab .none").css({"display":"none"});
    //		$("#inside_"+index).css({"display":"block"});
    //		s3.removeClass("hover");
    //		$(this).addClass("hover");
    //	});

    /*搜索*/
    $("#proField").click(function () {
        var SelectTemp = document.getElementById('txtprokw').value;

        if (SelectTemp == "") {
            alert("请输入关键字！")
            document.getElementById('txtprokw').focus();
            return false;
        }
        else {
            var SPECIAL_STR = "[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]%^";
            for (i = 0; i < SelectTemp.length; i++) {
                if (SPECIAL_STR.indexOf(SelectTemp.charAt(i)) != -1) {
                    alert("关键字不能包含特殊字符(" + SelectTemp.charAt(i) + ")!");
                    document.getElementById('txtprokw').focus();
                    return false;
                }
            }
        }

        window.location.href = 'product.aspx?kw=' + escape(SelectTemp.toString());
        window.event.returnValue = false; //要加这话
    })
    $("#txtprokw").keydown(function () {
        if (event.keyCode == 13) {
            var SelectTemp = document.getElementById('txtprokw').value;

            if (SelectTemp == "") {
                alert("请输入关键字！")
                document.getElementById('txtprokw').focus();
                return false;
            }
            else {
                var SPECIAL_STR = "[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]%^";
                for (i = 0; i < SelectTemp.length; i++) {
                    if (SPECIAL_STR.indexOf(SelectTemp.charAt(i)) != -1) {
                        alert("关键字不能包含特殊字符(" + SelectTemp.charAt(i) + ")!");
                        document.getElementById('txtprokw').focus();
                        return false;
                    }
                }
            }

            window.location.href = 'product.aspx?kw=' + escape(SelectTemp.toString());
            window.event.returnValue = false; //要加这话
        }
    })

    /*左侧搜索*/
    $("#btnSearch").click(function () {
        var SelectTemp = document.getElementById('txtprokey').value;

        var sel = document.getElementsByName("selproname")[0];
        var selvalue = sel.options[sel.options.selectedIndex].value//你要的值

        if ((SelectTemp == "" || SelectTemp == "请输入关键字") && selvalue == "") {
            alert("请输入关键字！")
            document.getElementById('txtprokey').focus();
            return false;
        }
        else {
            var SPECIAL_STR = "[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]%^";
            for (i = 0; i < SelectTemp.length; i++) {
                if (SPECIAL_STR.indexOf(SelectTemp.charAt(i)) != -1) {
                    alert("关键字不能包含特殊字符(" + SelectTemp.charAt(i) + ")!");
                    document.getElementById('txtprokey').focus();
                    return false;
                }
            }
        }
        if (SelectTemp == "请输入关键字") { SelectTemp = ""; }
        var url = 'product.aspx?kw=' + escape(SelectTemp.toString());
        if (selvalue != "") {
            url += "&code=" + selvalue;
        }
        window.location.href = url;
        window.event.returnValue = false; //要加这话
    })
    $("#txtprokey").keydown(function () {
        if (event.keyCode == 13) {
            var SelectTemp = document.getElementById('txtprokey').value;

            if (SelectTemp == "" || SelectTemp == "请输入关键字") {
                alert("请输入关键字！")
                document.getElementById('txtprokey').focus();
                return false;
            }
            else {
                var SPECIAL_STR = "[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]%^";
                for (i = 0; i < SelectTemp.length; i++) {
                    if (SPECIAL_STR.indexOf(SelectTemp.charAt(i)) != -1) {
                        alert("关键字不能包含特殊字符(" + SelectTemp.charAt(i) + ")!");
                        document.getElementById('txtprokey').focus();
                        return false;
                    }
                }
            }

            window.location.href = 'product.aspx?kw=' + escape(SelectTemp.toString());
            window.event.returnValue = false; //要加这话
        }
    })

    /*底部搜索*/
    $("#fproField").click(function () {
        var SelectTemp = document.getElementById('ftxtprokw').value;

        if (SelectTemp == "") {
            alert("请输入关键字！")
            document.getElementById('ftxtprokw').focus();
            return false;
        }
        else {
            var SPECIAL_STR = "[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]%^";
            for (i = 0; i < SelectTemp.length; i++) {
                if (SPECIAL_STR.indexOf(SelectTemp.charAt(i)) != -1) {
                    alert("关键字不能包含特殊字符(" + SelectTemp.charAt(i) + ")!");
                    document.getElementById('ftxtprokw').focus();
                    return false;
                }
            }
        }

        window.location.href = 'product.aspx?kw=' + escape(SelectTemp.toString());
        window.event.returnValue = false; //要加这话
    })
    $("#ftxtprokw").keydown(function () {
        if (event.keyCode == 13) {
            var SelectTemp = document.getElementById('ftxtprokw').value;

            if (SelectTemp == "") {
                alert("请输入关键字！")
                document.getElementById('ftxtprokw').focus();
                return false;
            }
            else {
                var SPECIAL_STR = "[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]%^";
                for (i = 0; i < SelectTemp.length; i++) {
                    if (SPECIAL_STR.indexOf(SelectTemp.charAt(i)) != -1) {
                        alert("关键字不能包含特殊字符(" + SelectTemp.charAt(i) + ")!");
                        document.getElementById('ftxtprokw').focus();
                        return false;
                    }
                }
            }

            window.location.href = 'product.aspx?kw=' + escape(SelectTemp.toString());
            window.event.returnValue = false; //要加这话
        }
    })

    $(document).keydown(function (event) {
        if (event.keyCode == 13) {
            $('form').each(function () {
                event.preventDefault();
            });
        }
    });
});



/*input提示*/
function getAttributeValue(o, key) {
    if (!o.attributes) return null;
    var attr = o.attributes;
    for (var i = 0; i < attr.length; i++) {
        if (key.toLowerCase() == attr[i].name.toLowerCase())
            return attr[i].value;
    }
    return null;
}
function focusInputEle(o) {
    if (o.value == getAttributeValue(o, 'defaultVal')) {
        o.value = '';
        o.style.color = "#3b8dd0";
    }
}
function blurInputEle(o) {
    if (o.value == '') {
        o.value = getAttributeValue(o, 'defaultVal');
        o.style.color = "#3b8dd0";
    }
}
/*input提示*/

/*头部下拉*/
function $$$$$(_sId) {
    return document.getElementById(_sId);
}
function hide(_sId)
{ $$$$$(_sId).style.display = $$$$$(_sId).style.display == "none" ? "" : "none"; }
function pick(v) {
    //document.getElementById('am').value = v;
    hide('HMF-1')
}
function bgcolor(id) {
    document.getElementById(id).style.background = "#F7FFFA";
    document.getElementById(id).style.color = "#000";
}
function nocolor(id) {
    document.getElementById(id).style.background = "";
    document.getElementById(id).style.color = "#788F72";
}

/*选项卡*/
function nTabs(thisObj, Num) {
    if (thisObj.className == "active") return;
    var tabObj = thisObj.parentNode.id;
    var tabList = document.getElementById(tabObj).getElementsByTagName("li");
    for (i = 0; i < tabList.length; i++) {
        if (i == Num) {
            thisObj.className = "active";
            document.getElementById(tabObj + "_Content" + i).style.display = "block";
        } else {
            tabList[i].className = "normal";
            document.getElementById(tabObj + "_Content" + i).style.display = "none";
        }
    }
}
/*选项卡*/


function navFun() {
    var liselected = 0;
    var lis = $("#nav>li:not(.clear)");
    //selectFun(lis, liselected)
    lis.each(function (i) {
        var n = i + 1;
        $(this).hover(function () {
            $(this).find(".nav_popup").slideDown(400);
            $(this).addClass('hover');
        },
		function () {
			$(this).find(".nav_popup").hide("fast");
            if (n != liselected) {
            $(this).removeClass('hover');
            }
        })
    });
}
function selectFun(obj, selectedIndex) {
    obj.eq(selectedIndex - 1).addClass('hover')
}

function total(h) {
    //$(".nav>li:not(.clear)").eq(h).addClass("hover");
}


//滚动条
var ScrollBar = function () {
    $(".about").each(function () {
        var t = $(this);
        if (t.length < 1) return;
        t.mCustomScrollbar({
            scrollButtons: {
                enable: true
            }
        });
    });
}


function showImg(){            
	$(".TabContent img").each(function(index, element) {
			var width = $(element).width();
			console.log(width);
			console.log(element);
			if( width>700 ){
				$(element).css("width", "95%");
			}
	});
	$(".newscontent img").each(function(index, element) {
			var width = $(element).width();
			console.log(width);
			console.log(element);
			if( width>700 ){
				$(element).css("width", "95%");
			}
	});
}