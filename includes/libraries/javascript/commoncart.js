function drop_cart_item(rec_id){
	var tr = $('#cart_item_' + rec_id);
	var amount_span = $('#item_amount');
    $.getJSON('index.php?app=commoncart&act=drop&rec_id=' + rec_id, function(result){
        if(result.done){
            //删除成功
            if(result.retval.cart.quantity == 0){
                window.location.reload();    //刷新
            }
            else{
                tr.remove();        //移除
                amount_span.html(price_format(result.retval.amount));
            }
        }
    });
}
function move_favorite( rec_id, goods_id){
    var tr = $('#cart_item_' + rec_id);
    $.getJSON('index.php?app=my_favorite&act=add&type=goods&item_id=' + goods_id, function(result){
        //没有做收藏后的处理，比如从购物车移除
        if(result.done){
            alert(result.msg);
        }
        else{
            alert(result.msg);
        }

    });
}
function change_quantity( rec_id){
    var subtotal_span = $('#item' + rec_id + '_subtotal');
    var amount_span = $('#item_amount');
    //暂存为局部变量，否则如果用户输入过快有可能造成前后值不一致的问题
    var item = $('#input_item_' + rec_id);
    var _v = Number(item.val());
    $.getJSON('index.php?app=commoncart&act=update&card_id=' + rec_id + '&quantity=' + _v, function (result) {
        if(result.done){
            //更新成功
            $(this).attr('changed', _v);
            subtotal_span.html(price_format(result.retval.subtotal));
            amount_span.html(price_format(result.retval.amount));
        }
        else{
            //更新失败
            alert(result.msg);
            $(this).val($(input).attr('changed'));
        }
    });
}
function decrease_quantity(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    if(orig > 1){
        item.val(orig - 1);
        item.keyup();
    }
}
function add_quantity(rec_id){
    var item = $('#input_item_' + rec_id);
    var orig = Number(item.val());
    item.val(orig + 1);
    item.keyup();
}