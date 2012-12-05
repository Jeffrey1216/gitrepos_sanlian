/* �༶ѡ����غ����������ѡ�񣬷���ѡ��
 * multi-level selection
 */

/* ����ѡ���� */
function regionInit(divId)
{
    $("#" + divId + " > select").change(regionChange); // select��onchange�¼�
   // alert('fuck');
    $("#" + divId + " > input:button[class='edit_region']").click(regionEdit); // �༭��ť��onclick�¼�
}

function priInit(divId)
{
	$("#" + divId + " .edit_region").click(regionEdit);
}

function regionChange()
{
    // ɾ�������select
    $(this).nextAll("select").remove();

    // ���㵱ǰѡ�е�id��ƴ������name
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

    // ajax�����¼�����
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

/* ��Ʒ����ѡ���� */
function gcategoryInit(divId)
{
    $("#" + divId + " > select").get(0).onchange = gcategoryChange; // select��onchange�¼�
    //window.onerror = function(){return true;}; //����jquery����
    $("#" + divId + " .edit_gcategory").click(gcategoryEdit); // �༭��ť��onclick�¼�
}

function gcategoryChange()
{
    // ɾ�������select
    $(this).nextAll("select").remove();
    // ���㵱ǰѡ�е�id��ƴ������name
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

    // ajax�����¼�����
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
/* ��Ʒ����ѡ���� */
function _all_gcategoryInit(divId)
{
    $("#" + divId + " > select").get(0).onchange = function () {
    	// ɾ�������select
    $(this).nextAll("select").remove();
    // ���㵱ǰѡ�е�id��ƴ������name
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

    // ajax�����¼�����
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
    //window.onerror = function(){return true;}; //����jquery����
    $("#" + divId + " .edit_gcategory").click(gcategoryEdit); // �༭��ť��onclick�¼�
}

function all_gcategoryChange(divId, obj)
{
    // ɾ�������select
    $(obj).nextAll("select").remove();
    // ���㵱ǰѡ�е�id��ƴ������name
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
		
    // ajax�����¼�����
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