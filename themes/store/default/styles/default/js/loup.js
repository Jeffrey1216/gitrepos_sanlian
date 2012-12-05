
//放大镜效果
	
	var my_glass=document.getElementById("dtl_my_glass");
	var my_mark=document.getElementById("dtl_my_mark");
	var smalls=document.getElementById("dtl_my_small");
	var my_float=document.getElementById("dtl_my_float");
	var my_bigs=document.getElementById("dtl_my_big");
	var my_img=document.getElementById("dtl_my_big_img");
	my_mark.onmouseover=function(){		
		my_float.style.display="block";
		my_bigs.style.display="block";
		};
	my_mark.onmouseout=function(){
		my_float.style.display="none";
		my_bigs.style.display="none";
		};
	my_mark.onmousemove=function(ev){
		var  oevent = ev || event
		var i=oevent.clientX ;
		var d=oevent.clientY ;
      
	    c=i-my_glass.offsetLeft-my_float.offsetWidth/2;
		a=d-my_glass.offsetTop+document.documentElement.scrollTop-my_float.offsetHeight/2;		
		if(c<0){
			c=0} 
		else if(c>my_glass.offsetWidth-my_float.offsetWidth){
			c=my_glass.offsetWidth-my_float.offsetWidth 
			}; 
		if(a<0){
			a=0}
		else if(a>my_glass.offsetHeight-my_float.offsetHeight){
			a=my_glass.offsetHeight-my_float.offsetHeight;
		    };
		my_float.style.left=c+"px"
		my_float.style.top=a+"px"
		pax=a/(my_glass.offsetHeight-my_float.offsetHeight)
		pay=c/(my_glass.offsetWidth-my_float.offsetWidth)
		my_img.style.left=-pay*(my_img.offsetWidth-my_bigs.offsetWidth)+"px"
		my_img.style.top=-pax*(my_img.offsetHeight-my_bigs.offsetHeight)+"px";
		
		};		
		
	//小图切换效果	
		var sml_list = document.getElementById("dtl_small_list").getElementsByTagName("li");	
		var sml_height = sml_list[0].offsetHeight+11;   //小图高度 + margin值					
		var sml_box_height = document.getElementById("dtl_small_box").offsetHeight;	  //最大显示高度	
        var max_count = parseInt(sml_box_height/sml_height);	//最大显示图片数量	
		var img_count = document.getElementById("dtl_small_list").getElementsByTagName("li").length;   //实际图片数量
		var pre_btn = document.getElementById("dtl_sml_previous");
		var next_btn = document.getElementById("dtl_sml_next");
		 //初始化
		document.getElementById("dtl_small_list").style.top = 0; 
		var move_speed =50;   //移动速度
		var move_site = 1;	  //移动距离
		var times;   //定时器
		var i =0;  //速度指针
		var my_i =0;		
		if(img_count > max_count)  //判断数量是否大于显示数量
		{			
			pre_btn.onclick = function(){
				i =0;
				clearInterval(times);				
				move_site = move_site - sml_height;									
				times =  window.setInterval(function(){
				if(parseInt(document.getElementById("dtl_small_list").style.top) > sml_box_height-sml_height*img_count)
				{
					document.getElementById("dtl_sml_next").style.background = "url(../images/ico_03.gif) no-repeat 4px -828px";																											
					if(parseInt(document.getElementById("dtl_small_list").style.top)<=move_site)
					{						
						clearInterval(times);
					}
						my_i = parseInt(document.getElementById("dtl_small_list").style.top)-i;
						
						if(parseInt(document.getElementById("dtl_small_list").style.top)>=move_site+sml_height/2)  
						//加速
						{ ++i; }
						
						else if(parseInt(document.getElementById("dtl_small_list").style.top) < move_site+10)
						{ i=1; }
						
						else  
						//减速
						{ --i; }						
						document.getElementById("dtl_small_list").style.top = my_i +'px';					
				}
				else
				{	
					clearInterval(times);
					document.getElementById("dtl_sml_previous").style.background = "url(../images/ico_03.gif) no-repeat -66px -802px";
					move_site = parseInt(document.getElementById("dtl_small_list").style.top)																			
				};			
				},move_speed);				
			}												
			next_btn.onclick = function(){	
				i =0;
				clearInterval(times);
				move_site = move_site + sml_height;	
				times =  window.setInterval(function(){
				if(parseInt(document.getElementById("dtl_small_list").style.top)<0)
				{
					document.getElementById("dtl_sml_previous").style.background = "url(../images/ico_03.gif) no-repeat 4px -802px";	
					
					if(parseInt(document.getElementById("dtl_small_list").style.top) >= move_site)
					{
						clearInterval(times);
					}				
						my_i = parseInt(document.getElementById("dtl_small_list").style.top)+i;						
						if(parseInt(document.getElementById("dtl_small_list").style.top)<move_site-sml_height/2) 
							//加速
						{ i++; }
						
						else if(parseInt(document.getElementById("dtl_small_list").style.top)> move_site -10) 
						{ i=1; }
						
						else 
							//减速
						{ i--; }
						
						document.getElementById("dtl_small_list").style.top =my_i +'px';											
				}else
				{
					clearInterval(times);
					document.getElementById("dtl_sml_next").style.background = "url(../images/ico_03.gif) no-repeat -66px -828px";
					move_site = parseInt(document.getElementById("dtl_small_list").style.top)
				};
				},move_speed);
			}			
		}	
		//切换显示图片效果
		for(var i =0 ;i < sml_list.length;i++)
		{
			sml_list[i].onmouseover = function()
			{
				document.getElementById("dtl_small_list_hover").id = "";
				this.id = "dtl_small_list_hover";
				document.getElementById("dtl_my_small").getElementsByTagName("img")[0].src = this.getElementsByTagName("img")[0].src;
				document.getElementById("dtl_my_big").getElementsByTagName("img")[0].src = this.getElementsByTagName("img")[0].src;
			}
		}		
	