var fade_speed = 1500;
$(function(){
var iSpeed =2000;   //速度  ms
var times=0;
var i_count = 2;   //图片数量
var i_start =true;   //轮播器启动
var t1 = window.setInterval(function(){
$(".plm_add_m").each(function(i){
	$(this).find(".plm_add_clk").children("div").each(function(j){
		var ss = $(this)		 
		$(this).hover(
			function()
			{	
				i_start =false;
				move(ss,j);
			},function()
			{
				i_start =true;
			})
		if(i_start)
		{
			move(ss,times); 
		}		
	})
})
++times;
if(times>i_count)
{times=0;}	
},iSpeed)
})
function move(ff,k)
{	
	var iNode = ff.parents(".plm_add_m").find(".plm_add_none");
	var iHover = ff.parents(".plm_add_clk").children("div");
	iNode.not(iNode.eq(k)).fadeOut(fade_speed);
	iNode.eq(k).fadeIn(fade_speed);
	iHover.removeClass("clk_img_bg");
	iHover.eq(k).addClass("clk_img_bg");	
}

