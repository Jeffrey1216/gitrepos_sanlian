<?php
/* 商铺进货价百分比 (商铺进货价 = 商品零售价 * 商铺进货价百分比) */
define("STOCK_PERCENT" , 0.50);

$profitArr = require_once dirname(__FILE__).'/./libraries/profit.inc.php';
$storeProfitArr = require_once dirname(__FILE__).'/./libraries/pailastoreProfit.inc.php';
$db = 'mysql';
$uname = 'webdbuser';
$pwd = 'PaiLaDBK002';
$host = '10.0.10.6:3306';
$dbName = 'paila';
$conn = mysql_connect($host,$uname,$pwd);
mysql_select_db($dbName);
mysql_query('SET NAMES GBK');

$result = mysql_query("select g.goods_id,g.sprice as 'd_sprice',gs.sprice, gs.spec_id, gs.goods_id, g.price as 'd_price',g.gprice as 'd_gprice',g.rate as 'd_rate',g.zprice as 'd_zprice',gs.price,gs.gprice,gs.rate
 from pa_goods g left join pa_goods_spec gs on g.goods_id = gs.goods_id left join pa_store s on g.store_id = s.store_id where g.store_id in (2,3,4) and g.if_show = 1 and g.closed = 0");

while(false !== ($row = mysql_fetch_array($result,MYSQL_ASSOC))) {
	$gprice = empty($row['gprice']) ? floatval($row['d_gprice']) : floatval($row['gprice']);
	$price = empty($row['price']) ? floatval($row['d_price']) : floatval($row['price']);
	$stock_discount_profit = floatval(number_format(floatval($gprice/$price),2)); //进货折扣率
	$offline_shoppe_profit = 0;
	$transition_discount = 0;
	foreach($profitArr as $k => $v) {
        	if($stock_discount_profit > $v['original_discount_min'] && $stock_discount_profit <= $v['original_discount_max']) {
					$offline_shoppe_profit = $v['offline_shoppe_profit'];
					$transition_discount = $v['transition_discount'];
	        	break;
		}
	}
	echo $stock_discount_profit."<br/>";
	//更新数据(pa_goods_spec);
	mysql_query("update pa_goods_spec set rate=" . $offline_shoppe_profit . " where spec_id= " . $row['spec_id']);
	//更新sprice
	echo "更新前" . $stock_discount_profit . "<br/>";
	echo "更新前 gprice = " . $gprice . "<br/>"; 
	echo '更新后' . $transition_discount . "<br/>";

	$sprice = ($transition_discount * $price);
	if($gprice > $sprice) {
		$sprice = $sprice + 1;
	}
	echo "更新后 sprice= " . $sprice . "<br/><hr/>";
	mysql_query("update pa_goods_spec set sprice=" . $sprice . " where spec_id = " . $row['spec_id']);
	//更新 zprice和积分
	if($stock_discount_profit > 0.35) {
		mysql_query("update pa_goods_spec set zprice = " . ceil($transition_discount * $price) . " where spec_id = " . $row['spec_id']);	
		foreach ($storeProfitArr as $key => $val) 
		{
			if($stock_discount_profit >= $val['min_discount'] && $stock_discount_profit <= $val['max_discount']) { //取得折扣89 
				echo "<br/>goods_id: " . $row['goods_id'] . " price: " . $price . " gprice: " . $gprice . " stock_discount: " . $stock_discount_profit . " credit_profit:" . $val['credit'] . " credit: " . number_format($price * $val['credit'], 2) .  " <br/>";
				mysql_query("update pa_goods_spec set credit = " . number_format($price * $val['credit'], 2) . " where spec_id = " . $row['spec_id']);
				$a = mysql_query("update pa_goods set credit = " . number_format($price * $val['credit'], 2) . " where goods_id = " . $row['goods_id']);
				echo "<span style='color:red'>update pa_goods set credit = " . number_format($price * $val['credit'], 2) . " where goods_id = " . $row['goods_id'] . "</span><br/>";
				break;
			}	
		}
	} else {
		mysql_query("update pa_goods_spec set zprice = " . ceil($price * STOCK_PERCENT) . " where spec_id = " . $row['spec_id']);
		mysql_query("update pa_goods_spec set credit = price where spec_id = " . $row['spec_id']);
		mysql_query("update pa_goods set credit = price where goods_id = " . $row['goods_id']);
		echo "update pa_goods set credit = price where goods_id = " . $row['goods_id'] . "<br/>";
	}
	//更新数据(pa_goods);
	mysql_query("update pa_goods set rate=" . $offline_shoppe_profit . " where goods_id = " . $row['goods_id']);

	$d_sprice =  ($transition_discount * $row['d_price']);
	if($gprice > $d_sprice) {
		$d_sprice = $d_sprice + 1;
	}
	mysql_query("update pa_goods set sprice=" . $d_sprice . " where goods_id = " .$row['goods_id']);
	if($row['d_sprice'] = 0) {
		mysql_query("update pa_goods set sprice=" . $d_sprice . " where goods_id = " .$row['goods_id']);
		if($stock_discount_profit >= 0.36) {
			mysql_query("update pa_goods set zprice= " . ($transition_discount * $price) . " where spec_id = " . $row['goods_id']);
		} else {
			mysql_query("update pa_goods set zprice= " . (STOCK_PERCENT * $price) . " where spec_id = " . $row['goods_id']);
		}
	}
	if($stock_discount_profit > 0.35) {
		mysql_query("update pa_goods set zprice= " . ceil($transition_discount * $row['d_price']) . " where goods_id = " . $row['goods_id']);
	} else {
		mysql_query("update pa_goods set zprice= " . ceil(STOCK_PERCENT * $row['d_price']) . " where goods_id = " . $row['goods_id']);
	}	
	
}
?>

