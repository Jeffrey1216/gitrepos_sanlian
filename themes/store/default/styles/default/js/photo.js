
var Effect = (function() {
	
	var Slider = function(o) {
		this.setting      = typeof o === 'object' ? o : {};
		this.target       = this.setting.target || 'index_slider';
		this.showMarkers  = this.setting.showMarkers || false;
		this.showControls = this.setting.showControls || false;
		this.width_allow  = this.setting.width_allow && true;
		this.trun_text 	  = this.setting.trun_text||'';
		this.autostart    = this.setting.autostart || false;
		this.crosswise    = this.setting.crosswise || false;
		this.timer        = null;
		this.currentTime  = null;
		this.ms           = 35;
		this.autoMs       = 3500;
		this.iTarget      = 0;
		this.nextTarget   = 0;
		this.speed        = 0;		
		this.init();
		this.handleEvent();
	};
	
	Slider.prototype = {   //原型
		init: function() {
			
			this.obj      = document.getElementById(this.target);
			this.oUl      = this.obj.getElementsByTagName('ul')[0];
			this.aUlLis   = this.oUl.getElementsByTagName('li');		
			this.number   = this.aUlLis.length;	
			if(this.crosswise)
			{
				this.width    = this.aUlLis[0].offsetWidth;
				this.oUl.style.width = this.width * this.number + 'px';		
			}
			else
			{
				this.height    = this.aUlLis[0].offsetHeight;
				this.oUl.style.height = this.height * this.number + 'px';	
			}
			if(this.showMarkers) {				
				var oDiv = document.createElement('div');
				var aLis = [];
				
				for(var i = 0; i < this.number; i++) {
					if(this.trun_text[i])
					{
						aLis.push('<li>'+ this.trun_text[i] +'<\/li>');
					}
						else
					{
						aLis.push('<li>'+''+'<\/li>');
					}
				};
				oDiv.innerHTML = '<ol>'+ aLis.join('') +'<\/ol>';
				this.obj.appendChild(oDiv.firstChild);
				this.aLis = this.obj.getElementsByTagName('ol')[0].getElementsByTagName('li');
				this.aLis[0].className = 'active';
				if(this.width_allow)
				{
					for(var i=0;i<aLis.length;i++)
					{
						this.obj.getElementsByTagName('ol')[0].getElementsByTagName('li')[i].style.width = this.obj.clientWidth/aLis.length-1+"px";
						this.obj.getElementsByTagName('ol')[0].getElementsByTagName('li')[aLis.length-1].style.rightborder = "none";
					}
				}
				oDiv = null;
			};			
			if(this.showControls) {
				this.oPrev = document.createElement('p');
				this.oNext = document.createElement('p');
				this.oPrev.className = 'prev';
				this.oPrev.innerHTML = '&lt';
				this.oNext.className = 'next';
				this.oNext.innerHTML = '&gt';
				this.obj.appendChild(this.oPrev);
				this.obj.appendChild(this.oNext);				
			};			
		},
		
		handleEvent: function() {
			var that = this;
			
			this.currentTime = setInterval(function() {
				if(that.autostart)
				{
					that.autoPlay();
				}
			}, this.autoMs);
			
			this.addEvent(this.obj, 'mouseover', function() {
				clearInterval(that.currentTime);
			});
			
			this.addEvent(this.obj, 'mouseout', function() {
				that.currentTime = setInterval(function() {
				if(that.autostart)
				{
					that.autoPlay();
				}
				}, that.autoMs);
			});
			
			if(this.showMarkers) {
				for(var i = 0; i < this.number; i++) {
					var el = this.aLis[i];
					(function(index) {
						that.addEvent(el, 'mouseover', function() {
							that.goTime(index);
						});
					})(i);
				};
			};
			
			if(this.showControls) {
				this.addEvent(this.oPrev, 'click', function() {
					
					that.fnPrev();
				});
				this.addEvent(this.oNext, 'click', function() {
					that.autoPlay();
				});
			};
			
		},
		
		addEvent: function(el, type, fn) {
			if(window.addEventListener) {
				el.addEventListener(type, fn, false);
			}
			else if(window.attachEvent) {
				el.attachEvent('on' + type, fn);
			};
		},
		
		fnPrev: function() {
			this.nextTarget--;
			if(this.nextTarget < 0) {
				this.nextTarget = this.number - 1;
			};
			this.goTime(this.nextTarget);
		},
		
		autoPlay: function() {
			this.nextTarget++;
			if(this.nextTarget >= this.number) {
				this.nextTarget = 0;
			};
			this.goTime(this.nextTarget);
		},
		
		goTime: function(index) {
			var that = this;
			
			if(this.showMarkers) {
				for(var i = 0; i < this.number; i++) {
					i == index ? this.aLis[i].className = 'active' : this.aLis[i].className = '';
				};
			};
			if(that.crosswise)
			{
				this.iTarget = -index * this.width;
			}
			else
			{
				this.iTarget = -index * this.height;
			}
			if(this.timer) {
				clearInterval(this.timer);
			};
			this.timer = setInterval(function() {
				if(that.crosswise)
				{
					that.doMove_x(that.iTarget);
				}
				else
				{
					that.doMove_y(that.iTarget);
				}
			}, this.ms);
		},
		
		doMove_x: function(target) {
			this.oUl.style.left = this.speed + 'px';
			this.speed += (target - this.oUl.offsetLeft) / 3;
			if(Math.abs(target - this.oUl.offsetLeft) === 0) {
				this.oUl.style.left = target + 'px';
				clearInterval(this.timer);
				this.timer = null;
			};
		},
		doMove_y: function(target) {
			this.oUl.style.top = this.speed + 'px';			
			this.speed += (target - this.oUl.offsetTop) / 3;			
			if(Math.abs(target - this.oUl.offsetTop) === 0) {
				this.oUl.style.top = target + 'px';
				clearInterval(this.timer);
				this.timer = null;
			};
		}
	};
	
	return {     //实例化
		slider: function(o) {      
			var kk = new Slider(o);
		},
		slider2: function(o) {
			var tt = new Slider(o);
			
		},
		slider3: function(o) {
			var ss = new Slider(o);			
		},
		slider4: function(o) {
			var ff = new Slider(o);			
		},
		slider5: function(o) {
			var ff_2 = new Slider(o);			
		}
		
	};
})();
$(function(){
	$(".plb_photo").each(function(i){		
			$(this).find(".plb_clk").hover(function(){				
				$(this).parent("li").find(".plb_photo_layer").not($(this).find(".plb_photo_layer")).fadeTo("slow","0.3");
				
			},function(){
				$(".plb_photo").eq(i).find(".plb_photo_layer").hide();
				$(".plb_photo").eq(i).find(".plb_photo_layer").stop();
			})							
		})
	$(".plb_photo").find(".prev").hover(function(){		
		$(this).animate({left:0},100)		
	},function(){
		$(this).stop();
		$(this).animate({left:"-10px"},100)
		
	})
	$(".plb_photo").find(".next").hover(function(){		
		$(this).animate({right:0},100)
	},function(){
		$(this).stop();
		$(this).animate({right:"-10px"},100)		
	})		
})
