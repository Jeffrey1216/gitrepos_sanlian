$(function(){
	$(".ancmt_ttl").each(function(i){
			$(".ancmt_ttl").eq(i).find("li").each(function(index){
			        $(this).mouseover(function(){
			              $(".ancmt_ttl").eq(i).parents(".ancmt").find(".ancmt_box ul").attr("id","");
			              $(".ancmt_ttl").eq(i).find("li").removeClass("pass2");
			              $(".ancmt_ttl").eq(i).parents(".ancmt").find(".ancmt_box ul").eq(index).attr("id","ancmt_ctt_show");
			              $(".ancmt_ttl").eq(i).find("li").eq(index).addClass("pass2");
		            });
	        });
    });
})