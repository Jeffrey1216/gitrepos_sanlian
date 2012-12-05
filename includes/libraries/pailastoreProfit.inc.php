<?php
return array(
	0 => array(
		0	=>	0.36,
		'min_discount' => 0.36, //最小折扣
		1	=> 0.40,
		'max_discount'	=> 0.40, // 最大折扣
		2 	=> 	0.3,
		'profit' 	=> 0.3, //对应利润率 (暂时不用了。）  之前通过这个计算赠送积分。公式：（$credit = ($price - $gprice - ($gprice * $profix) - (ZINS * $price)) / (CREDIT_TRADE_PROPORTION + TOTAL_CHANNEL_PROFIT);）
		3	=>  0.6,
		'credit' => 0.6, //赠送积分数
	),
	1 => array(
		0	=>	0.41,
		'min_discount' => 0.41,
		1	=> 0.5,
		'max_discount'	=> 0.5,
		2 	=> 	0.23,
		'profit' 	=> 0.23,
		3	=>  0.4,
		'credit' => 0.4,
	),
	2 => array(
		0	=>	0.51,
		'min_discount' => 0.51,
		1	=> 0.6,
		'max_discount'	=> 0.6,
		2 	=> 	0.16,
		'profit' 	=> 0.16,
		3	=> 0.3,
		'credit' => 0.3,
	),
	3 => array(
		0	=>	0.61,
		'min_discount' => 0.61,
		1	=> 0.7,
		'max_discount'	=> 0.7,
		2 	=> 	0.08,
		'profit' 	=> 0.08,
		3	=> 	0.12,
		'credit'	=> 0.12,
	),
	4 => array(
		0	=>	0.71,
		'min_discount' => 0.71,
		1	=> 0.8,
		'max_discount'	=> 0.8,
		2 	=> 	0.08,
		'profit' 	=> 0.08,
		3	=> 	0.05,
		'credit'	=> 0.05,
	),
	5 => array(
		0	=>	0.81,
		'min_discount' => 0.81,
		1	=> 0.9,
		'max_discount'	=> 0.9,
		2 	=> 	0.02,
		'profit' 	=> 0.02,
		3	=> 	0.02,
		'credit'	=> 0.02,
	),
	6 => array(
		0	=>	0.91,
		'min_discount' => 0.91,
		1	=> 0.95,
		'max_discount'	=> 0.95,
		2 	=> 	0.01,
		'profit' 	=> 0.01,
		3	=> 	0.01,
		'credit'	=> 0.01,
	),
);

?>