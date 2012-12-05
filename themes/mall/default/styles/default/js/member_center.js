//用户中心推荐切换JS
$(function(){
	$(".ancmt_ttl").each(function(i){
			$(".ancmt_ttl").eq(i).find("li").each(function(index){
			        $(this).mouseover(function(){
			              $(".ancmt_ttl").eq(i).parents(".ancmt").find(".ancmt_box ul").attr("id","");
			              $(".ancmt_ttl").eq(i).find("li").removeClass("pass2");
			              $(".ancmt_ttl").eq(i).parents(".ancmt").find(".ancmt_box ul").eq(index).attr("id","ancmt_ctt_show");
			              $(".ancmt_ttl").eq(i).find("li").eq(index).addClass("pass2");
						  $(".ancmt_ttl").eq(i).find("li").eq(index).removeClass("pass1");
						  if(index<2){$(".ancmt_ttl").eq(i).find("li").eq(index+1).addClass("pass1");}
						  if(index!=0){
							  $(".ancmt_ttl").eq(i).find("li").eq(0).addClass("pass1");
							 }
						 if(index==0){
							  $(".ancmt_ttl").eq(i).find("li").eq(2).removeClass("pass1");
							 }
						if(index==2){
							  $(".ancmt_ttl").eq(i).find("li").eq(1).removeClass("pass1");
							 }
		            });
					$(this).mouseout(function(index){
						
						});
	        });
    });
})
//用户中显示时间JS
function localtime(){
	var str,colorhead,colorfoot;
	var now=new Date();
	var yy = now.getYear();
	if(yy<1900) yy = yy+1900;
	var MM = now.getMonth()+1;
	if(MM<10) MM = '0' + MM;
	var dd = now.getDate();
	if(dd<10) dd = '0' + dd;
	var hh = now.getHours();
	if(hh<10) hh = '0' + hh;
	var mm = now.getMinutes();
	if(mm<10) mm = '0' + mm;
	var ss = now.getSeconds();
	if(ss<10) ss = '0' + ss;
	var ww = now.getDay();
	if  ( ww==0 )  colorhead="<font color=\"#FF0000\">";
	if  ( ww > 0 && ww < 6 )  colorhead="<font color=\"#373737\">";
	if  ( ww==6 )  colorhead="<font color=\"#008000\">";
	colorfoot="</font>"
	str = colorhead + yy + "&#24180;" + MM + "&#26376;" + dd + "&#26085;" + "&nbsp;&nbsp;"+ hh + ":" + mm + ":" + ss + colorfoot;
	$("#localtime").html(str);
}