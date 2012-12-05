// JavaScript Document
   //document.oncontextmenu=new Function("event.returnValue=false;"); //禁止右键功能,单击右键将无任何反应
   //document.onselectstart=new Function("event.returnValue=false;"); //禁止先择,也就是无法复制
var root_url = '/shopcpweb/index.php';
var yes      = "<img src=/shopcpweb/Tpl/default/Public/images/yes.png >";
var error    = "<img src=/shopcpweb/Tpl/default/Public/images/error.png >";
function sizeHeight(){
	var vheight = document.documentElement.clientHeight;
	var vWidth = document.documentElement.clientWidth-210+"px";
	if ($.browser.version == "6.0"){
			$(".leftBox").css("height",vheight);
			$(".rightBox").css("width",vWidth);
			$("#iframeHeight").height(vheight);
	}
	if ($.browser.version == "7.0"){
		$("#iframeHeight").height(vheight);
	}
}

function changeMenu(id){
	var vParent =  $("#"+id).parent().siblings();
	var liA =  vParent.find("a");
	var ul =  vParent.find("ul");
	$("#"+id).addClass("menuOn");
	$(liA).removeClass("menuOn");
	$("#"+id+"_show").slideToggle("normal");
	$(ul).slideUp("normal");
	$("#"+id+"_show").find("a").removeClass("sideOn");
	$("#"+id+"_show").find("a:first").addClass("sideOn");
}
function sideMenu(obj){
	var vParent = $(obj).parent().siblings();
	var liA =  vParent.find("a");
	$(obj).addClass("sideOn");
	$(liA).removeClass("sideOn");
}
function titHover(id){
    $("#"+id).stop().animate({paddingRight: '30px'}, 200);
}
function titOut(id){
	$("#"+id).stop().animate({paddingRight: '15px'});
}

//列表隔行换色
function addElementCss(){
    $(".trBg tr:even").addClass("even"); 
    $(".trBg tr").mouseover(function() {$(this).addClass("over");}).mouseout(function() {$(this).removeClass("over");});
	
	$(".tongjiTab tr:even").addClass("even"); 
    $(".tongjiTab tr").mouseover(function() {$(this).addClass("over");}).mouseout(function() {$(this).removeClass("over");});
	
	
	 //文本框统一样式
    $("body :text, body :password").addClass("input");
	$("body: textarea").addClass("textarea");
	if($("body: textarea").attr("readonly")==true){
		$("body: textarea").addClass("readonly");
	}
    $("body :text, ,body :password, body: textarea").each(function(){
        $(this).focus(function(){$(this).addClass("inputHover")}).blur(function(){$(this).removeClass("inputHover");})
    });
	$(".btnShort").each(function(){
		 $(this).hover(function(){
			$(this).addClass("btnShortHover");
		},function(){
			 $(this).removeClass("btnShortHover");
		});								
	});
	$(".btnShort2").each(function(){
		 $(this).hover(function(){
			$(this).addClass("btnShort2Hover");
		},function(){
			 $(this).removeClass("btnShort2Hover");
		});								
	});
	$(".btnClose").each(function(){
		 $(this).hover(function(){
			$(this).addClass("btnCloseHover");
		},function(){
			 $(this).removeClass("btnCloseHover");
		});								
	});
	$(".btnClose2").each(function(){
		 $(this).hover(function(){
			$(this).addClass("btnClose2Hover");
		},function(){
			 $(this).removeClass("btnClose2Hover");
		});
	});
	
}

function showTab(id){
	$("#"+id).addClass("hover").siblings().removeClass("hover");
	$("#"+id+"_show").show().siblings().hide();
}

//提示信息
function showMsg(msg,type, time, fn){
    $("<div id='divMsg'></div>").appendTo($("body"));
	$("#divMsg").css("background-color",getColor(type));
    $("#divMsg").text(msg);
    if(time == null || time == undefined){
        time = 2000;
    }
    $("#divMsg").fadeIn("fast", function(){
        window.setTimeout(function(){ 
           $("#divMsg").fadeOut("fast", function(){
               $("#divMsg").remove();
               if (typeof(fn) == 'function') fn();
           }) 
        }, time);
    }); 
}

function getColor(type){
    var strColor = "#d6ed9c";
    switch (type){
        case "alert":
            strColor = "#f3e9a9";
            break;
        case "success":
            strColor = "#d6ed9c";
            break;
        case "failed":
            strColor = "#f3e9a9";
            break;            
    }
    return strColor;
}

function textCounter(obj){
	var len = strLen(obj.value);
	var msgLen = parseInt(len / 65) + (len % 65 > 0 ? 1 : 0);
	$("#txtLen").text(len);
	$("#msgLen").text(msgLen);
}

function strLen(str) {
	var charset = document.charset;
	var len = 0;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == "utf-8" ? 1 : 1) : 1;
	}
	return len;
}

//捕捉回车事件
function keyenter(evt) 
{ 
	evt = (evt) ? evt : ((window.event) ? window.event : "") 
	keyCode = evt.keyCode ? evt.keyCode : (evt.which ? evt.which : evt.charCode); 
	if (keyCode == 13) { 
		$("#saveButton").click();
	} 
}

// 设置页眉页脚为空	
var hkey_root,hkey_path,hkey_key
hkey_root="HKEY_CURRENT_USER"
hkey_path="\\Software\\Microsoft\\Internet Explorer\\PageSetup\\"

function PageSetup_Null()
{
 try{
  var RegWsh = new ActiveXObject("WScript.Shell") ;
  hkey_key="header" ;
  RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"") ;
  hkey_key="footer" ;
  RegWsh.RegWrite(hkey_root+hkey_path+hkey_key,"") ;
  }
 catch(e){}
}
// 打印
function PrintPage()
{
 PageSetup_Null();
 window.print();
}
//函数名：fucCheckNUM()
//功能介绍：检查是否为数字
//参数说明：要检查的数字
function fucCheckNUM(NUM)
{
	var i,j,strTemp;
	strTemp="0123456789";
	if ( NUM.length == 0) return false;
	for (i=0;i<NUM.length;i++)
	{
		j = strTemp.indexOf(NUM.charAt(i));
		if (j==-1)
		{
			//说明有字符不是数字
			return false;
		}
	}
	//说明是数字
	return true;
}