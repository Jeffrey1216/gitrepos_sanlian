/* 多级选择相关函数，如地区选择，分类选择
 * multi-level selection
 */

/* 地区选择函数 */
function regionInit(divId)
{
    $("#" + divId + " > select").change(regionChange); // select的onchange事件
   // alert('fuck');
    $("#" + divId + " > input:button[class='edit_region']").click(regionEdit); // 编辑按钮的onclick事件
}

function priInit(divId)
{
	$("#" + divId + " .edit_region").click(regionEdit);
}

function regionChange()
{
    // 删除后面的select
    $(this).nextAll("select").remove();

    // 计算当前选中到id和拼起来的name
    var selects = $(this).siblings("select").andSelf();
    var id = 0;
    var names = new Array();
    for (i = 0; i < selects.length; i++)
    {
        sel = selects[i];
        if (sel.value > 0)
        {
            id = sel.value;
            name = sel.options[sel.selectedIndex].text;
            names.push(name);
        }
    }
    $(".mls_id").val(id);
    $(".mls_name").val(name);
    $(".mls_names").val(names.join("\t"));

    // ajax请求下级地区
    if (this.value > 0)
    {
        var _self = this;
        var url = SITE_URL + '/index.php?app=mlselection&type=region';
        $.getJSON(url, {'pid':this.value}, function(data){
            if (data.done)
            {
                if (data.retval.length > 0)
                {
                    $("<select><option>" + lang.select_pls + "</option></select>").change(regionChange).insertAfter(_self);
                    var data  = data.retval;
                    for (i = 0; i < data.length; i++)
                    {
                        $(_self).next("select").append("<option value='" + data[i].region_id + "'>" + data[i].region_name + "</option>");
                    }
                }
            }
            else
            {
                alert(data.msg);
            }
        });
    }
}

function regionEdit()
{
    $(this).siblings("select").show();
    $(this).siblings("span").andSelf().hide();
}

/* 商品分类选择函数 */
function gcategoryInit(divId)
{
    $("#" + divId + " > select").get(0).onchange = gcategoryChange; // select的onchange事件
    //window.onerror = function(){return true;}; //屏蔽jquery报错
    $("#" + divId + " .edit_gcategory").click(gcategoryEdit); // 编辑按钮的onclick事件
}

function gcategoryChange()
{
    // 删除后面的select
    $(this).nextAll("select").remove();
    // 计算当前选中到id和拼起来的name
    var selects = $(this).siblings("select").andSelf();
    var id = 0;
    var names = new Array();
    for (i = 0; i < selects.length; i++)
    {
        sel = selects[i];
        if (sel.value > 0)
        {
            id = sel.value;
            name = sel.options[sel.selectedIndex].text;
            names.push(name);
        }
    }
    
    $(".mls_id").val(id);
    $(".mls_name").val(name);
    $(".mls_names").val(names.join("\t"));

    // ajax请求下级分类
    if (this.value > 0)
    {
    	var obj = {
    		'pid' : this.value
    	}
    	
    	if (window.store_id != undefined)
    	{
    		obj = {
    			'pid' : this.value,
    			'store_id' : store_id
    		}
    	}
    	
        var _self = this;
        var url = SITE_URL + '/index.php?app=mlselection&type=gcategory';
        
        $.get(url, obj, function(data){
        	data = eval('(' + data + ')');
        	if (data.done)
            {
                if (data.retval.length > 0)
                {
                    $("<select><option>" + lang.select_pls + "</option></select>").change(gcategoryChange).insertAfter(_self);
                    var data  = data.retval;
                    for (i = 0; i < data.length; i++)
                    {
                        $(_self).next("select").append("<option value='" + data[i].cate_id + "'>" + data[i].cate_name + "</option>");
                    }
                }
            }
            else
            {
                alert(data.msg);
            }
        });
    }
}

function gcategoryEdit()
{
    $(this).siblings("select").show();
    $(this).siblings("span").andSelf().remove();
}
/* 商品分类选择函数 */
function _all_gcategoryInit(divId)
{
    $("#" + divId + " > select").get(0).onchange = function () {
    	// 删除后面的select
    $(this).nextAll("select").remove();
    // 计算当前选中到id和拼起来的name
    var selects = $(this).siblings("select").andSelf();
    var id = 0;
    var names = new Array();
    for (i = 0; i < selects.length; i++)
    {
        sel = selects[i];
        if (sel.value > 0)
        {
            id = sel.value;
            name = sel.options[sel.selectedIndex].text;
            names.push(name);
        }
    }
    $("#" + divId + " > .mls_id").val(id);
    $("#" + divId + " > .mls_name").val(name);
    $("#" + divId + " > .mls_names").val(names.join("\t"));

    // ajax请求下级分类
    if (this.value > 0)
    {
    	var obj = {
    		'pid' : this.value
    	}
    	
    	if (window.store_id != undefined)
    	{
    		obj = {
    			'pid' : this.value,
    			'store_id' : store_id
    		}
    	}
    	
        var _self = this;
        var url = SITE_URL + '/index.php?app=mlselection&act=getCategory&type=gcategory';
        
        $.get(url, obj, function(data){
        	data = eval('(' + data + ')');
        	if (data.done)
            {
                if (data.retval.length > 0)
                {
                		var str = "all_gcategoryChange(\'" + divId + "\',";
                    $("<select onchange=" + str + "this)><option>" + lang.select_pls + "</option></select>").insertAfter(_self);
                    var data  = data.retval;
                    for (i = 0; i < data.length; i++)
                    {
                        $(_self).next("select").append("<option value='" + data[i].cate_id + "'>" + data[i].cate_name + "</option>");
                    }
                }
            }
            else
            {
                alert(data.msg);
            }
        });
    }
    };
    //window.onerror = function(){return true;}; //屏蔽jquery报错
    $("#" + divId + " .edit_gcategory").click(gcategoryEdit); // 编辑按钮的onclick事件
}

function all_gcategoryChange(divId, obj)
{
    // 删除后面的select
    $(obj).nextAll("select").remove();
    // 计算当前选中到id和拼起来的name
    var selects = $(obj).siblings("select").andSelf();
    var id = 0;
    var names = new Array();
    for (i = 0; i < selects.length; i++)
    {
        sel = selects[i];
        if (sel.value > 0)
        {
            id = sel.value;
            name = sel.options[sel.selectedIndex].text;
            names.push(name);
        }
    }
    $("#" + divId + " > .mls_id").val(id);
    $("#" + divId + " > .mls_name").val(name);
    $("#" + divId + " > .mls_names").val(names.join("\t"));
		
    // ajax请求下级分类
    if (obj.value > 0)
    {
        var url = SITE_URL + '/index.php?app=mlselection&act=getCategory&type=gcategory';
        var _self = obj;

        $.get(url, {"pid":obj.value}, function(data){
        	data = eval('(' + data + ')');
        	if (data.done)
            {
                if (data.retval.length > 0)
                {
                    var str = "all_gcategoryChange(\'" + divId + "\',";
                    $("<select onchange=" + str + "this)><option>" + lang.select_pls + "</option></select>").insertAfter(obj);
                    var data  = data.retval;
                    for (i = 0; i < data.length; i++)
                    {
                        $(_self).next("select").append("<option value='" + data[i].cate_id + "'>" + data[i].cate_name + "</option>");
                    }
                }
            }
            else
            {
                alert(data.msg);
            }
        });
    }
}