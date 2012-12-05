<?php
date_default_timezone_set('Asia/Shanghai');
define("PAGE_NUM",20);
/*团购后台控制器*/
class Group_projectApp extends MallbaseApp
{
	var $group_project_mod;
	var $group_gcategory_mod;
	
	function __construct(){
		$this->GroupcategoryApp();
	}
	function GroupcategoryApp(){
		parent::__construct();
		$this->_group_gcategory_mod=&m('groupgcategory'); //商品分类 
		$this->_group_project_mod=&m('groupproject');
		$this->_group_category_mod = & m('groupcategory'); 
		$this->_group_specname_mod = & m('groupspecname'); //规格名
		$this->_group_spec_mod = & m('groupspec'); //规格值
		$this->_store_mod = & m('store');//商户
		$this->_group_project_specname_mod = & m('groupprojectspecname');
		$this->_group_project_spec_mod = & m('groupprojectspec');
	}
	
	/*管理*/
	function index(){
		$t1 = true;
		$t2 = true;
		$t3 = true;
		$t4 = true;
		$t5 = true;
		$t6 = true;
		$time = time();
		$sql = "select gp.*,gc.category from pa_group_project gp left join pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on gp.gcategory_id = gg.id where 1 = 1 ";
		$sql .= " and gc.id=1 and gp.start_time < " . $time . " AND gp.finish_time > " . $time;
		$appliance =$this->_group_project_mod->getAll($sql . " and gg.id=24 limit 3");
		if(!$appliance) {
			$t1 = false;
		}

	foreach($appliance as $k=>$v){
			$a=$v['cprice'] - $v['price'];
			$arr=number_format("$a",2);
			$appliance[$k]['rebate']=number_format($v['price']/$v['cprice'],2)*10;
			$appliance[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
			$appliance[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
			$appliance[$k]['image_other'] = GROUP_IMAGE_URL . $v['image_other'];
			$appliance[$k]['save']=$arr;
			$appliance[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
			//var_dump($arr);
		}
		$this->assign('appliance',$appliance);
		$personal=$this->_group_project_mod->getAll($sql . " and gg.id=8 limit 3");
		if(!$personal) {
			$t2 = false;
		}
		foreach($personal as $k=>$v){
			$a=$v['cprice'] - $v['price'];
			$arr=number_format("$a",2);
			$personal[$k]['rebate']=number_format($v['price']/$v['cprice'],2)*10;
			$personal[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
			$personal[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
			$personal[$k]['image_other'] = GROUP_IMAGE_URL . $v['image_other'];
			//var_dump($arr);
			$personal[$k]['save']=$arr;
			$personal[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
		}
		$this->assign('personal',$personal);
		
		$food=$this->_group_project_mod->getAll($sql . " and gg.id=9 limit 3");
		if(!$food) {
			$t3 = false;
		}
	foreach($food as $k=>$v){
			$a=$v['cprice'] - $v['price']; 
			$arr=number_format("$a",2);
			$food[$k]['rebate']=number_format($v['price']/$v['cprice'],2)*10;
			$food[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
			$food[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
			$food[$k]['image_other'] = GROUP_IMAGE_URL . $v['image_other'];
			//var_dump($arr);
			$food[$k]['save']=$arr;
			$food[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
		}
		//var_dump($food);
		$this->assign('food',$food);
		$jewelry=$this->_group_project_mod->getAll($sql . " and gg.id=10 limit 3");
		if(!$jewelry) {
			$t4 = false;
		}
	foreach($jewelry as $k=>$v){
			$a=$v['cprice'] - $v['price'];
			$arr=number_format("$a",2);
			$jewelry[$k]['rebate']=number_format($v['price']/$v['cprice'],2)*10;
			$jewelry[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
			$jewelry[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
			$jewelry[$k]['image_other'] = GROUP_IMAGE_URL . $v['image_other'];
			//var_dump($arr);
			$jewelry[$k]['save']=$arr;
			$jewelry[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
		}
		//var_dump($jewelry);
		$this->assign('jewelry',$jewelry);
		$gear=$this->_group_project_mod->getAll($sql . " and gg.id=14 limit 3");
		if(!$gear) {
			$t5 = false;
		}
		foreach($gear as $k=>$v){
			$a=$v['cprice'] - $v['price'];
			$arr=number_format("$a",2);
			$gear[$k]['rebate']=number_format($v['price']/$v['cprice'],2)*10;
			$gear[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
			$gear[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
			$gear[$k]['image_other'] = GROUP_IMAGE_URL . $v['image_other'];
			//var_dump($arr);
			$gear[$k]['save']=$arr;
			$gear[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
		}
		//var_dump($gear);
		$this->assign('gear',$gear);
		$other=$this->_group_project_mod->getAll($sql . " and gg.id=23 limit 3");
		if(!$other) {
			$t6 = false;
		}
		foreach($other as $k=>$v){
			$a=$v['cprice'] - $v['price'];
			$arr=number_format("$a",2);
			$other[$k]['rebate']=number_format($v['price']/$v['cprice'],2)*10;
			$other[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
			$other[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
			$other[$k]['image_other'] = GROUP_IMAGE_URL . $v['image_other'];
			//var_dump($arr);
			$other[$k]['save']=$arr;
			$other[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
		}
		//var_dump($other);
		$this->assign('index',1);
		$this->assign('other',$other);
//		if($t1 && $t2 && $t3 && $t4 && $t5 && $t6) { //都有
//			$this->display("group_index.html");
//		} else {
//			$this->display("group_start.html");
//		}
		//原有页面
		//$this->display("group_index.html"); 
		//即将开始 
		//$this->display("group_start.html");
		$this->display("mm_group.html");
		//$this->display("wy_group.html");
	}
	/*商品显示页*/
	function goodsshow(){
		$now = time();
		if(!IS_POST){
		$id=isset($_GET['id']) ? intval($_GET['id']) : 0 ;
		}
		if(!$id){
			$this->show_warning('商品不存在');
			return ;
		}
		$goodsshow=$this->_group_project_mod->find($id);
		foreach($goodsshow as $k=>$v){
			$goodsshow[$k]['rebate']=number_format($v['price']/$v['cprice'],2)*10;
			$goodsshow[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
		}
		foreach($goodsshow as $k => $v){
			if($now > intval($v['finish_time'])) {
				$this->assign("over",1);
			}
			$surplus_time=intval($v['finish_time']) - intval($now);
			//var_dump($surplus_time);
			$days=floor($surplus_time/(60 * 60 * 24));
			//var_dump($days);
			$hour=floor(($surplus_time%86400)/3600);
			//var_dump($hour);
			$minute=floor(($surplus_time%3600)/60);
			$second=$surplus_time -($days * 86400) - ($hour * 3600) -($minute * 60);
			//echo $days . "天" . $hour . "时" . $minute . '分' . $second . '秒' . "<br/>";
			$goodsshow[$k]['days'] =$days;
			$goodsshow[$k]['hour'] = $hour;
			$goodsshow[$k]['minute'] = $minute;
			$goodsshow[$k]['second'] = $second;
			$goodsshow[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
			$goodsshow[$k]['save'] = $arr;
		}
		
		$time = time();
		$sql = "select gp.*,gp.id as pid,gc.category from pa_group_project gp left join pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on gp.gcategory_id = gg.id where 1 = 1 ";
		$sql .= " and gc.id=1 and gp.start_time < " . $time . " AND gp.finish_time > " . $time;
		$other =$this->_group_project_mod->getAll($sql ." limit 4");
		foreach($other as $k=>$v){
			$other[$k]['image_other'] = GROUP_IMAGE_URL . $v['image_other'];
			$other[$k]['image_show'] = GROUP_IMAGE_URL . $v['image_show'];
			$other[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
			$other[$k]['save']=$arr;
		}	
		$this->assign('other',$other);
		$this->assign('goodsshow',$goodsshow);
		$this->display('group_detail.html');
	}
		
	/* 往期团购 */
	public function pastGroupBuy() {
		$time = time();
		$page = $this->_get_page(PAGE_NUM);
		
		$sql = "select gp.*,gp.id as pid,gc.category from pa_group_project gp left join pa_group_category gc on 
		gp.category_id = gc.id left join pa_group_gcategory gg on gp.gcategory_id = gg.id where 1 = 1 and gc.id=1 ";
		//过期团购
		$sql .= " AND (gp.finish_time < " . $time . " OR gp.max_quantity <= 0)";
		$count = $this->_group_project_mod->getOne("select count(*) from pa_group_project gp left join pa_group_category gc on 
		gp.category_id = gc.id left join pa_group_gcategory gg on gp.gcategory_id = gg.id where 1 = 1 and gc.id=1 
		AND gp.finish_time < " . $time . "");
		
		$page['item_count'] = $count;
		$sql .= " AND gp.finish_time < " . $time . " limit " . $page['limit'];
		$pastGroup_list = $this->_group_project_mod->getAll($sql);
		if(is_array($pastGroup_list)) {
			foreach($pastGroup_list as $k => $v) {
				if($v['max_quantity'] <= 0 && $v['finish_time'] > $time) {
					$pastGroup_list[$k]['is_old'] = 0; 
				} else {
					$pastGroup_list[$k]['is_old'] = 1;	
				}
				$pastGroup_list[$k]['buynum'] = intval($v['sale']) + intval($v['virtual_buy']);
				$pastGroup_list[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
				$pastGroup_list[$k]['discount'] = number_format(($v['price']/$v['cprice']),2);
				$pastGroup_list[$k]['save'] = number_format(($v['cprice'] - $v['price']),2);
			}
		}
		$this->_format_page($page);
		$this->assign('index',2);
		$this->assign('page_info',$page);
		$this->assign('pastGroup_list',$pastGroup_list);
		$this->display("group.groupoldshow.html");
	}
	
	
	/* 秒杀列表页 */
	public function seckillList() {
		//当前时间
		$time = time();
		
		$herald_time = $time + (6 * 60 * 60);//预告时间
		/*var_dump(date("Y-m-d H:i",$time));
		var_dump(date("Y-m-d H:i",$herald_time));*/
		$page = $this->_get_page(PAGE_NUM);
		$gcategory_id = empty($_GET['gcategory_id']) ? 0 : intval($_GET['gcategory_id']);
		$conditions = ' 1 = 1 ';
		if($gcategory_id != 0) {
			$conditions .= " AND gg.id = " . $gcategory_id;
		}
		$conditions .= " AND gp.start_time < " . $herald_time . " AND gp.finish_time > " . $time ;
		$gcategory_list = $this->_group_gcategory_mod->find(array('conditions' => ' parent_id = 0'));
		//一元秒杀
		$oneSeckill_list = $this->_group_project_mod->getAll("select gp.id,gp.title,gc.category,gp.cprice,
		gp.price,gp.min_quantity,gp.start_time,gp.image_show,gp.image_list,gp.virtual_buy,gp.finish_time,gp.goods_name,gp.max_quantity,gp.sale,gg.category as gcategory from pa_group_project gp left join 
		pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on 
		gp.gcategory_id = gg.id where " . $conditions . " AND gc.id = 3");
		foreach($oneSeckill_list as $k => $v) {
			if($v['start_time'] < $time) { //已经开始的秒杀
				//剩余时间
				$surplus_time = intval($v['finish_time']) - intval($time);
				$hour = floor($surplus_time/3600);
				$minute = floor(($surplus_time%3600)/60);
				$second = $surplus_time - ($hour * 3600) - ($minute * 60);
				//echo $hour . "时" . $minute . '分' . $second . '秒' . "<br/>";
				$oneSeckill_list[$k]['hour'] = $hour;
				$oneSeckill_list[$k]['minute'] = $minute;
				$oneSeckill_list[$k]['second'] = $second;
				$oneSeckill_list[$k]['is_start'] = 1;
			} else { //预告的秒杀
				$surplus_start_time = intval($v['start_time']) - intval($time);
				$hour = floor($surplus_start_time/3600);
				$minute = floor(($surplus_start_time%3600)/60);
				$second = $surplus_start_time - ($hour * 3600) - ($minute * 60);
				$oneSeckill_list[$k]['hour'] = $hour;
				$oneSeckill_list[$k]['minute'] = $minute;
				$oneSeckill_list[$k]['second'] = $second;
				$oneSeckill_list[$k]['is_start'] = 0;
			}
			$oneSeckill_list[$k]['surplus_quantity'] = $v['max_quantity'] - $v['sale'];
			$oneSeckill_list[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
		}
		$this->assign("oneSeckill_list",$oneSeckill_list);
		//其它秒杀
		$seckill_count = "select count(*) from pa_group_project gp left join 
		pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on 
		gp.gcategory_id = gg.id where " . $conditions . " AND gc.id = 2";
		$page['item_count'] = $seckill_count;
		
		$seckill_list = $this->_group_project_mod->getAll("select  gp.id,gp.title,gc.category,gp.cprice,
		gp.price,gp.min_quantity,gp.start_time,gp.image_show,gp.goods_desc,gp.image_list,gp.virtual_buy,gp.finish_time,gp.goods_name,
		gp.max_quantity,gp.sale,gg.category as gcategory from pa_group_project gp left join 
		pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on 
		gp.gcategory_id = gg.id where " . $conditions . " AND gc.id = 2 limit " . $page['limit']);
		
		foreach($seckill_list as $k => $v) {
			if($v['start_time'] < $time) { //已经开始的秒杀
				//剩余时间
				$surplus_time = intval($v['finish_time']) - intval($time);
				$hour = floor($surplus_time/3600);
				$minute = floor(($surplus_time%3600)/60);
				$second = $surplus_time - ($hour * 3600) - ($minute * 60);
				//echo $hour . "时" . $minute . '分' . $second . '秒' . "<br/>";
				$seckill_list[$k]['hour'] = $hour;
				$seckill_list[$k]['minute'] = $minute;
				$seckill_list[$k]['second'] = $second;
				$seckill_list[$k]['is_start'] = 1;
			} else { //预告的秒杀
				$surplus_start_time = intval($v['start_time']) - intval($time);
				$hour = floor($surplus_start_time/3600);
				$minute = floor(($surplus_start_time%3600)/60);
				$second = $surplus_start_time - ($hour * 3600) - ($minute * 60);
				$seckill_list[$k]['hour'] = $hour;
				$seckill_list[$k]['minute'] = $minute;
				$seckill_list[$k]['second'] = $second;
				$seckill_list[$k]['is_start'] = 0;
			}
			$seckill_list[$k]['surplus_quantity'] = $v['max_quantity'] - $v['sale'];
			$seckill_list[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
		}
		//未开始秒杀
		
		$this->_format_page($page);
		$this->assign('page_info',$page);
		$this->assign('seckill_list',$seckill_list);
		if(!$oneSeckill_list && !$seckill_list) {
			$this->display("seckill.yugao.html");
		} else {
			$this->display("seckill.list.html");
		}
	}
	
	/* 秒杀详细页 */
	public function seckillShow() {
		$time = time();
		$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
		if($id == 0) {
			$this->show_warning("商品不存在!");
			return;
		}
		$conditions = " 1 = 1 ";
		$now = time();
		//当前秒杀商品
		$seckill = $this->_group_project_mod->getRow("select  gp.id,gp.introduction,gp.warm_prompt,gp.title,gc.category,gp.cprice,
		gp.price,gp.min_quantity,gp.start_time,gp.goods_desc,gp.image_show,gp.image_list,gp.virtual_buy,gp.finish_time,gp.goods_name,
		gp.max_quantity,gp.sale,gg.category as gcategory from pa_group_project gp left join 
		pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on 
		gp.gcategory_id = gg.id where gp.id = " . $id);
		
		if($seckill['finish_time'] < $now) {
			$this->show_warning("当前秒杀已结束! 请选择其它秒杀!");
			return;
		}
		
		if($seckill['start_time'] > $now) { //未开始的
			//距离开始时间
			$surplus_time = intval($seckill['start_time']) - intval($time);
			$hour = floor($surplus_time/3600);
			$minute = floor(($surplus_time%3600)/60);
			$second = $surplus_time - ($hour * 3600) - ($minute * 60);
			//echo $hour . "时" . $minute . '分' . $second . '秒' . "<br/>";
			$seckill['hour'] = $hour;
			$seckill['minute'] = $minute;
			$seckill['second'] = $second;
			$seckill['surplus_quantity'] = $seckill['max_quantity'] - $seckill['sale'];
			$seckill['image_show'] = GROUP_IMAGE_URL . $seckill['image_show'];
			$seckill['is_start'] = 0;
		} else {
			//剩余时间
			$surplus_time = intval($seckill['finish_time']) - intval($time);
			$hour = floor($surplus_time/3600);
			$minute = floor(($surplus_time%3600)/60);
			$second = $surplus_time - ($hour * 3600) - ($minute * 60);
			//echo $hour . "时" . $minute . '分' . $second . '秒' . "<br/>";
			$seckill['hour'] = $hour;
			$seckill['minute'] = $minute;
			$seckill['second'] = $second;
			$seckill['surplus_quantity'] = $seckill['max_quantity'] - $seckill['sale'];
			$seckill['image_show'] = GROUP_IMAGE_URL . $seckill['image_show'];
			$seckill['is_start'] = 1;
		}
		
		
		//秒杀预告
		$next_time = $now + (60 * 60 * 24);
		$conditions .= " AND gp.start_time < " . $next_time . " AND gp.finish_time > " . $next_time . " AND gp.start_time > " . $now;
		$seckill_list = $this->_group_project_mod->getAll("select * from pa_group_project gp left join 
		pa_group_category gc on gp.category_id = gc.id left join pa_group_gcategory gg on 
		gp.gcategory_id = gg.id where " . $conditions . " AND gc.id in(2,3)");
		
		
		
		foreach($seckill_list as $k => $v) {
			//剩余时间
			$surplus_time = intval($v['start_time']) - intval($time);
			$hour = floor($surplus_time/3600);
			$minute = floor(($surplus_time%3600)/60);
			$second = $surplus_time - ($hour * 3600) - ($minute * 60);
			//echo $hour . "时" . $minute . '分' . $second . '秒' . "<br/>";
			$seckill_list[$k]['hour'] = $hour;
			$seckill_list[$k]['minute'] = $minute;
			$seckill_list[$k]['second'] = $second;
			$seckill_list[$k]['surplus_quantity'] = $v['max_quantity'] - $v['sale'];
			$seckill_list[$k]['image_list'] = GROUP_IMAGE_URL . $v['image_list'];
		}

		$this->assign("seckill",$seckill);
		$this->assign("seckill_list",$seckill_list);
		$this->display("seckill.show.html");
	}
}

?>