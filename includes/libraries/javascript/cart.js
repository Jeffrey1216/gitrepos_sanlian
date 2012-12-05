function drop_cart_item(store_id, rec_id){
    var tr = $('#cart_item_' + rec_id);
    var amount_span = $('#cart' + store_id + '_amount');
    var cart_goods_kinds = $('#cart_goods_kinds');
    $.getJSON('index.php?app=cart&act=drop&rec_id=' + rec_id, function(result){
        if(result.done){
          
            if(result.retval.cart.quantity == 0){
                window.location.reload();    
            }
            else{
                tr.remove();        
                amount_span.html(price_format(result.retval.amount));  
                cart_goods_kinds.html(result.retval.cart.kinds);       
            }
        }
    });
}
function move_favorite(store_id, rec_id, goods_id){
    var tr = $('#cart_item_' + rec_id);
    $.getJSON('index.php?app=my_favorite&act=add&type=goods&item_id=' + goods_id, function(result){
    
        if(result.done){
            //drop_cart_item(store_id, rec_id);
            alert(result.msg);
        }
        else{
            alert(result.msg);
        }

    });
}
function change_quantity(store_id, rec_id, spec_id, input, orig){
    var subtotal_span = $('#item' + rec_id + '_subtotal');
    var credit_span = $('#item' + rec_id + '_credit');
    var amount_span = $('#cart' + store_id + '_amount');
    var credit_total_span = $("#cart" + store_id + "_credit_total");
¢˜
    var _v = input.value;
    $.getJSON('index.php?app=cart&act=update&spec_id=' + spec_id + '&quantity=' + _v, function(result){
        if(result.done){
            
            $(input).attr('changed', _v);
            subtotal_span.html(price_format(result.retval.subtotal));
            credit_span.html(result.retval.credit + "PL");
            amount_span.html(price_format(result.retval.amount));
            credit_total_span.html(result.retval.credit_total + "PL");
        }
        else{
           
            alert(result.msg);
            $(input).val($(input).attr('changed'));
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