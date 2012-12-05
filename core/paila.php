<?php

/**
 *    PaiLa��ܺ����ļ�����������������뺯��
 *    Streamlining comes from Sparrow PHP @ Garbin
 *
 *    @author    Garbin
 */

/*---------------------������ϵͳ����-----------------------*/
/* ��¼��������ʱ�� */
define('START_TIME', pl_microtime());

/* �ж�����ʽ */
define('IS_POST', (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'));

/* �ж�����ʽ */
define('IN_PL', true);

/* ����PHP_SELF���� */
define('PHP_SELF',  htmlentities(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));

/* ��ǰPaiLa����汾 */
define('VERSION', '2.0.1');

/* ��ǰPaiLa����Release */
define('RELEASE', '20120811');

/* ͼƬ��������ַ */
//define('IMAGE_URL','http://img.paila100.com/');
//define('IMAGE_URL','http://192.168.0.66/paila/');



/*---------------------������PHP�ڲ�ͬ�汾����ͬ�������ϵļ��ݴ���-----------------------*/

/* �ڲ���IIS�ϻ�û��REQUEST_URI���� */
$query_string = isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : $_SERVER['QUERY_STRING'];
if (!isset($_SERVER['REQUEST_URI']))
{
    $_SERVER['REQUEST_URI'] = PHP_SELF . '?' . $query_string;
}
else
{
    if (strpos($_SERVER['REQUEST_URI'], '?') === false && $query_string)
    {
        $_SERVER['REQUEST_URI'] .= '?' . $query_string;
    }
}

/*---------------------������ϵͳ�ײ�����༰����-----------------------*/
class PaiLa
{
    /* ���� */
   static function startup($config = array())
    {
    	date_default_timezone_set('Asia/Shanghai');
        /* ���س�ʼ���ļ� */
        require(ROOT_PATH . '/core/controller/app.base.php');     //������������
        require(ROOT_PATH . '/core/model/model.base.php');   //ģ�ͻ�����
	
        if (!empty($config['external_libs']))
        {
            foreach ($config['external_libs'] as $lib)
            {
                require($lib);
            }
	}

	$cron = new Cron();

        /* ���ݹ��� */
        if (!get_magic_quotes_gpc())
        {
            $_GET   = addslashes_deep($_GET); //addslashes_deep()��������������еݹ��'\'ת��
            $_POST  = addslashes_deep($_POST);
            $_COOKIE= addslashes_deep($_COOKIE);
        }
		
        /* ����ת�� */
        $default_app = $config['default_app'] ? $config['default_app'] : 'default';
        $default_act = $config['default_act'] ? $config['default_act'] : 'index';
		
        $app    = isset($_REQUEST['app']) ? trim($_REQUEST['app']) : $default_app;
        $act    = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : $default_act;
        $app_file = $config['app_root'] . "/{$app}.app.php";
        
        if (!is_file($app_file))
        {
            exit('Missing controller');
        }
        require($app_file);
        
        define('APP', $app);
        define('ACT', $act);
        $app_class_name = ucfirst($app) . 'App';
		
        /* ʵ���������� */
        $app     = new $app_class_name();
        c($app);
        $app->do_action($act);        //ת������Ӧ��Action 
        $app->destruct();
    }
}

/**
 * 	  �ƻ����������
 * 
 **/
class Cron {
	private $cron_list;
	private $cron_row;
    private $manager_arr;
    
	public function __construct() {
		$this->Cron();
	}	
	public function Cron() {
		$this->manager_arr = array();
		date_default_timezone_set("Asia/Shanghai");
		$this->cron_mod = & m('cron');
		$this->init();
	}

	public function init() {
		$now = intval(date('G',time()));
		$this->cron_list = $this->cron_mod->getAll('select * from pa_cron where is_enable = 1');	
		//var_dump($this->cron_list);
		foreach($this->cron_list as $k => $v) {
			if(intval($v['update_start']) <= $now && intval($v['update_end'] > $now)) {
				$func_name = $v['func_name'];
				$this->cron_row = $v;
				$this->$func_name();
			}
		}
	}
	//��ɶ���
	private function finish_order() {
		$model_name = $this->getModelName();	
		$model = & m($model_name);
		$conditions = ' status = 30 AND ship_time <= ' . (time() - intval($this->cron_row['time_cycle'])) . '';
		//��ȡ����Ҫ��Ķ���
		$order_list = $model->find(array('conditions' => $conditions));
		foreach($order_list as $k => $v) {
			$this->confirm_order($v['order_id']);
		}
	}
	
	private function getModelName() {
		return strtr($this->cron_row['data_sheet'],array(DB_PREFIX => ''));
	}
	/**
	*    ȷ�϶���
	*
	*    @author    Garbin
	*    @return    void
	*/
	function confirm_order($order_id)
	{
		$order_id = isset($order_id) ? intval($order_id) : 0;
		
		if (!$order_id)
		{
			echo Lang::get('no_such_order');
			
			return;
		}
		$model_order    =&  m('order');
		/* ֻ���ѷ����Ķ�������ȷ�� */
		$order_info     = $model_order->get("order_id={$order_id} AND status=" . ORDER_SHIPPED);
		if (empty($order_info))
		{
			echo Lang::get('no_such_order');
			
			return;
		}
		$model_order->edit($order_id, array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));
		if ($model_order->has_error())
		{
			$this->pop_warning($model_order->get_error());
			
			return;
		}
		
		/* ��¼����������־ */
		$order_log =& m('orderlog');
		$order_log->add(array(
			'order_id'  => $order_id,
			'operator'  => 'system',
			'order_status' => order_status(ORDER_SHIPPED),
			'changed_status' => order_status(ORDER_FINISHED),
			'remark'    => '����ȷ���ջ�ʱ��,ϵͳ�Զ�����!',
			'log_time'  => gmtime(),
		));
		
		$model_ordergoods =& m('ordergoods');
		$order_goods = $model_ordergoods->find("order_id={$order_id}");
		/**
		* ������һ��� 
		**/
		$user_id    = $order_info['buyer_id']; 
		$get_credit = $order_info['get_credit'];//�������͵Ļ���
		if ($get_credit>0)
		{
			//���ﶨ�����ɹ�,ֱ��ȡorder���е�get_credit
			changeMemberCreditOrMoney($user_id,$get_credit,ADD_CREDIT);
			
			//��ӻ�Ա�˻���¼
	    	$param = array(
	    		'user_id' => $user_id,
	    		'credit'  => $get_credit,
	    		'change_time' => gmtime(),
	    		'change_desc' => "ϵͳ�Զ�������ɶ����������û����֣�{$get_credit}PL",
	    		'change_type' => 1,
	    	    'order_id' => $order_id,
	    	);
	    	add_account_log($param);
		}
    	
		/* �������, ���㷵�� */
    	$member = & m('member');
    	$_customer_manager_mod = & m('customermanager');
		$member_info = $member->get($user_id);
		$manager_info = $_customer_manager_mod->get($user_id);
		
		$autotrophy_money = $autotrophy_credit = $credit = 0 ;
		foreach ($order_goods as $goods)
		{
			if ($goods['autotrophy'] == 1)
			{
				$autotrophy_money += $goods['price'] * $goods['quantity']; //�����д������Ʒ���ܼ�
				if ($goods['is_usecredit'] == 0) //ȡ��δʹ�û���֧���Ĵ����
				{
					$autotrophy_credit += $goods['credit'] * $goods['quantity']; //������δʹ�û���֧���������Ʒ�������ͻ���
				}
			}
		}
		//�����ʣ����뷵���Ļ���ֵ
		if ($order_info['get_credit']>$autotrophy_credit)
		{
			$credit = $get_credit - $autotrophy_credit;
		}
			
		//�������û��������Ź�Աʱ
		if (!$manager_info)
		{
			$type = getfanli($user_id); //����Ƽ��˵�����
			if ($type == 'tuan')
			{
				$this->ad_manager_rebate($member_info['invite_id'], $autotrophy_money ,1,$order_id);
			
				$this->mb_manager_rebate($member_info['invite_id'],$credit,$order_id,$order_info['goods_amount']);
			}elseif ($type == 'store')
			{
				$this->mb_store_rebate($member_info['invite_id'],$credit,$order_id);
			}else
			{
				//�����û�û���κ����Ƽ�����������
				$this->mb_channel_rebate(CHANNEL_ID,$credit,$order_id);
			}
		}else
		{
			//�û������Ѿ����Ź�Ա
			//��������ְ���Ӧ�ȼ���ɸ��践��
			$this->ad_manager_rebate($member_info['user_id'], $autotrophy_money ,1,$order_id);

			$type = getfanli($user_id); //����Ƽ��˵�����
			if ($type == 'tuan')
			{
				$this->mb_manager_rebate($member_info['invite_id'],$credit,$order_id,$order_info['goods_amount']);
			}elseif ($type == 'store')
			{
				//�������ְ����ָ��践������
				$this->mb_store_rebate($member_info['invite_id'],$credit,$order_id);
			}else
			{
				//�����û�û���κ����Ƽ�����������
				$this->mb_channel_rebate(CHANNEL_ID,$credit,$order_id);
			}
		}
		
		/* �����ۼ����ۼ��� */
		$model_goodsstatistics =& m('goodsstatistics');
		
		foreach ($order_goods as $goods)
		{
			$model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
		}
	}
	
	//���ɷѻ��Ա�������� �Ź�Ա����
    function ad_manager_rebate($manager_id, $amount ,$type=0 ,$order_id = 0) //manager_id ֱ���Ƽ����Ź�Աid
    {
    	if ($amount>0)
    	{
	    	$_customer_manager_mod = & m('customermanager');
	    	$_manager_arr = $this->_get_customer_manager($manager_id);
	
	    	foreach ($_manager_arr['ret_grant'] as $k => $v)
	    	{
	    		if ($v['ratio']>0)
	    		{
		    		$grant = format_fanli_money($amount * $v['ratio']);
		    		if ($grant)
		    		{
			    		//����Ӧ�û���, ����Ӧ���ֽ�
			    		$cash = $grant['cash'];
			    		$credit = $grant['credit'];
			    		
			    		changeMemberCreditOrMoney($v['manager'] , $credit , ADD_CREDIT);
			    		changeMemberCreditOrMoney($v['manager'] , $cash , ADD_MONEY);
			    		
			    		$_customer_manager_mod->db->query("update pa_customer_manager set 
			    				  gains_total = gains_total + "
			    				 . $grant['money'] ." where user_id = " . $v['manager'] );
			    	   //��д�����¼
			    		$param = array(
			    			'user_id' => $v['manager'],
			    			'user_money' => $cash,
			    			'user_credit' => $credit,
			    			'change_time' => gmtime(),
			    		    'order_id' => $order_id,
			    		);
			    		if ($type==0)
			    		{
			    			$param['change_desc'] = "�Ź�Ա({$v['manager_name']})��ȡ���ѣ�{$amount}, ��÷ֳɱ���".($v['ratio']*100)."%,��{$grant['money']}";
			    			$param['change_type'] = 50;
			    		}else
			    		{
			    			$param['change_desc'] = "�Ź�Ա({$v['manager_name']})�����Ź��������{$amount}, ��÷ֳɱ���".($v['ratio']*100)."%,��{$grant['money']}";
			    			$param['change_type'] = 52;
			    		}
			    		
			    		add_account_log($param);
		    		}
	    		}
	    	}
	    	
	    	foreach ($_manager_arr['customer'] as $_k => $_v)
	    	{
	    		$_customer_manager_mod->db->query("update pa_customer_manager  set 
	    			outstanding_achievement_total = outstanding_achievement_total + "
	    				 . $amount . " where user_id = " . $_v);
	    	}
	    	//������ȡ����
	    	$channel_money = format_fanli_money($amount * 0.05) ;
	    	if ($channel_money)
	    	{
		    	//����Ӧ�û���, ����Ӧ���ֽ�
		    	$cash = $channel_money['cash'];
		    	$credit = $channel_money['credit'];
		    	changeMemberCreditOrMoney(CHANNEL_ID , $credit , ADD_CREDIT);
		    	changeMemberCreditOrMoney(CHANNEL_ID , $cash , ADD_MONEY);
		    	
		    	//��д�����¼
		    	$data = array(
		    		'user_id' => CHANNEL_ID,
		    		'user_money' => $cash,
		    		'user_credit' => $credit,
		    		'change_time' => gmtime(),
		    	    'order_id' => $order_id,
		    	);
		    	if ($type==0)
		    	{
		    		$data['change_desc'] = "�Ź�Ա({$v['manager_name']})��ȡ���ѣ�{$amount}, ������÷ֳɱ���5%,��{$channel_money['money']}";
		    		$data['change_type'] = 54;
		    	}else
		    	{
		    		$data['change_desc'] = "�Ź�Ա({$v['manager_name']})�����Ź��������{$amount}, ������÷ֳɱ���5%,��{$channel_money['money']}";
		    		$data['change_type'] = 55;
		    	}
		    	
		    	add_account_log($data);
	    	}
    	}
    }
    
	//��Ա����---�����Ź�Ա��÷���
    function mb_manager_rebate($manager_id, $credit ,$order_id = 0,$amount ) //manager_id ֱ���Ƽ����Ź�Աid
    {
    	if ($credit>0)
    	{
	    	$grant = format_fanli_money($credit * 0.5); //�û��������û��ֵ�50%
	    	if ($grant)
	    	{
	    	//����Ӧ�û���, ����Ӧ���ֽ�
	    	$cash = $grant['cash'];
			$credit = $grant['credit'];
	    	
	    	changeMemberCreditOrMoney($manager_id , $credit , ADD_CREDIT);
	    	changeMemberCreditOrMoney($manager_id , $cash , ADD_MONEY);
	    	
	       //��д�����¼
	    	$param = array(
	    		'user_id' => $manager_id,
	    		'user_money' => $cash,
	    		'user_credit' => $credit,
	    		'change_time' => gmtime(),
	    		'change_desc' => "��Ա����Ź�Ա����õķ�������{$grant['money']}",
	    		'change_type' => 53,
	    		'order_id' => $order_id,
	    	);
	    	add_account_log($param);
	    	//�����Ź�Ա����ҵ���Լ�������
	    	$_customer_manager_mod = & m('customermanager');
	    	$_customer_manager_mod->db->query("update pa_customer_manager set 
		    				  gains_total = gains_total + "
		    				 . $grant['money'] ." ,outstanding_achievement_total = outstanding_achievement_total + "
	    				 . $amount." where user_id = " . $manager_id );
	    	}
    	}
    }
    
	//��Ա����---������÷���
    function mb_channel_rebate($channel_id, $credit ,$order_id = 0 )
    {
    	if ($credit>0)
    	{
	    	$channel_money =  format_fanli_money($credit * 0.5); //�û��������û��ֵ�50%
	    	if ($channel_money)
	    	{
		    	//����Ӧ�û���, ����Ӧ���ֽ�
		    	$cash = $channel_money['cash'];
		    	$credit = $channel_money['credit'];
		    	
		    	changeMemberCreditOrMoney($channel_id , $credit , ADD_CREDIT);
		    	changeMemberCreditOrMoney($channel_id , $cash , ADD_MONEY);
		    	
		       //��д�����¼
		    	$param = array(
		    		'user_id' => $channel_id,
		    		'user_money' => $cash,
		    		'user_credit' => $credit,
		    		'change_time' => gmtime(),
		    		'change_desc' => "��Ա�����������õķ�������{$channel_money['money']}",
		    		'change_type' => 57,
		    		'order_id' => $order_id,
		    	);
		    	add_account_log($param);
	    	}
    	}
    }
    
	//��Ա����---���̻�÷���
    function mb_store_rebate($store_id, $credit,$order_id )
    {
    	if ($credit>0)
    	{
	    	$balance =  format_fanli_money($credit * 0.5); //�û��������û��ֵ�50%
	    	if ($balance)
	    	{
		    	//����Ӧ�û���, ����Ӧ���ֽ�
		    	$cash   = $balance['cash'];
		    	$credit = $balance['credit'];
		    	
		    	changeMemberCreditOrMoney($store_id , $credit , ADD_CREDIT);
		    	changeMemberCreditOrMoney($store_id , $cash , ADD_MONEY);
		    	//��д�����¼
		    	$param = array(
		    		'user_id' => $store_id,
		    		'user_money' => $cash,
		    		'user_credit' => $credit,
		    		'change_time' => gmtime(),
		    		'change_desc' => "��Ա������̻�õķ�������{$balance['money']}",
		    		'change_type' => 58,
		    		'order_id' => $order_id,
		    	);
		    	add_account_log($param);
	    	}
    	}
    }
    
    //��ȡ��Ҫ�������Ź�Ա
    function _get_customer_manager($manager_id, $level_id = -1, $ratio = 0)
    {
    	$_customer_manager_mod = & m('customermanager');
    	$sql = "select * from pa_customer_manager cm left join 
    		pa_customer_level cl on cm.customer_level = cl.level_id 
    		where cm.user_id = " . $manager_id;
    	
    	$info = $_customer_manager_mod->getRow($sql);
    	
    	if ($level_id == -1) define('MANAGE_NAME',$info['user_name']); 
   		$r = $info['benefit_ratio'] - $ratio;
    	$level_id = $info['parent_level_id'];
    	if ($r>0)
    	{
			$this->manager_arr['customer'][] = $info['user_id'];
	    	$this->manager_arr['ret_grant'][] = array(
	    		'manager' => $info['user_id'],
	    	    'manager_name' => MANAGE_NAME,
				'ratio' => $r);
	    	$ratio = $info['benefit_ratio'];
    	}

	    if ($info['parent_level_id'] != 0 && $info['parent_id'] != 0)
    	{
	    	$this->_get_customer_manager($info['parent_id'], $level_id, $ratio);
    	}
    	return $this->manager_arr;
    }
}

/**
 *    ������Ļ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class Object
{
    var $_errors = array();
    var $_errnum = 0;
    function __construct()
    {
        $this->Object();
    }
    function Object()
    {
        #TODO
    }
    /**
     *    ��������
     *
     *    @author    Garbin
     *    @param     string $errmsg
     *    @return    void
     */
    function _error($msg, $obj = '')
    {
        if(is_array($msg))
        {
            $this->_errors = array_merge($this->_errors, $msg);
            $this->_errnum += count($msg);
        }
        else
        {
            $this->_errors[] = compact('msg', 'obj');
            $this->_errnum++;
        }
    }

    /**
     *    ����Ƿ���ڴ���
     *
     *    @author    Garbin
     *    @return    int
     */
    function has_error()
    {
        return $this->_errnum;
    }

    /**
     *    ��ȡ�����б�
     *
     *    @author    Garbin
     *    @return    array
     */
    function get_error()
    {
        return $this->_errors;
    }
}

/**
 *    ���������
 *
 *    @author    Garbin
 *    @param    none
 *    @return    void
 */
class Lang
{
    /**
     *    ��ȡָ������������
     *
     *    @author    Garbin
     *    @param     none
     *    @return    mixed
     */
    function &get($key = '')
    {
        if (Lang::_valid_key($key) == false)
        {
            return $key;
        }
        $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'__ECLANG__\']') : '$GLOBALS[\'__ECLANG__\']';
        $tmp = eval('if(isset(' . $vkey . '))return ' . $vkey . ';else{ return $key; }');

        return $tmp;
    }

    /**
     * ��֤key����Ч��
     *
     * @author Hyber
     * @param string $key
     * @return bool
     */
    function _valid_key($key)
    {
        if (strpos($key, ' ') !== false)
        {
            return false;
        }
        #todo ��ʱֻ�ж��Ƿ��пո�
        return true;
    }


    /**
     *    ����ָ������������ȫ������������
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function load($lang_file)
    {
        static $loaded = array();
        $old_lang = $new_lang = array();
        $file_md5 = md5($lang_file);
        if (!isset($loaded[$file_md5]))
        {
            $new_lang = Lang::fetch($lang_file);
            $loaded[$file_md5] = $lang_file;
        }
        else
        {
            return;
        }
        $old_lang =& $GLOBALS['__ECLANG__'];
        if (is_array($old_lang))
        {
            $new_lang = array_merge($old_lang, $new_lang);
        }

        $GLOBALS['__ECLANG__'] = $new_lang;
    }

    /**
     *    ��ȡһ�������ļ�������
     *
     *    @author    Garbin
     *    @param     string $lang_file
     *    @return    array
     */
    function fetch($lang_file)
    {
        return include($lang_file);
    }
}
function lang_file($file)
{
    return ROOT_PATH . '/languages/' . LANG . '/' . $file . '.lang.php';
}

/**
 *    ���ù�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class Conf
{
    /**
     *    ����������
     *
     *    @author    Garbin
     *    @param     mixed $conf
     *    @return    bool
     */
    function load($conf)
    {
        $old_conf = isset($GLOBALS['ECMALL_CONFIG']) ? $GLOBALS['ECMALL_CONFIG'] : array();
        if (is_string($conf))
        {
            $conf = include($conf);
        }
        if (is_array($old_conf))
        {
            $GLOBALS['ECMALL_CONFIG'] = array_merge($old_conf, $conf);
        }
        else
        {
            $GLOBALS['ECMALL_CONFIG'] = $conf;
        }
    }
    /**
     *    ��ȡ������
     *
     *    @author    Garbin
     *    @param     string $k
     *    @return    mixed
     */
    function get($key = '')
    {
        $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'ECMALL_CONFIG\']') : '$GLOBALS[\'ECMALL_CONFIG\']';

        return eval('if(isset(' . $vkey . '))return ' . $vkey . ';else{ return null; }');
    }
}

/**
 *    ��ȡ��ͼ����
 *
 *    @author    Garbin
 *    @param     string $engine
 *    @return    object
 */
function &v($is_new = false, $engine = 'default')
{
    include_once(ROOT_PATH . '/core/view/template.php');
    if ($is_new)
    {
        return new ecsTemplate();
    }
    else
    {
        static $v = null;
        if ($v === null)
        {
            switch ($engine)
            {
                case 'default':
                    $v = new ecsTemplate();
                break;
            }
        }

        return $v;
    }
}

/**
 *  ��ȡһ��ģ��
 *
 *  @author Garbin
 *  @param  string $model_name
 *  @param  array  $params
 *  @param  book   $is_new
 *  @return object
 */
function &m($model_name, $params = array(), $is_new = false)
{
    static $models = array();
    $model_hash = md5($model_name . var_export($params, true));
    if ($is_new || !isset($models[$model_hash]))
    {
        $model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
        if (!is_file($model_file))
        {
            /* �����ڸ��ļ������޷���ȡģ�� */
            return false;
        }
        include_once($model_file);
        $model_name = ucfirst($model_name) . 'Model';
        if ($is_new)
        {
            return new $model_name($params, db());
        }
        $models[$model_hash] = new $model_name($params, db());
    }
    return $models[$model_hash];
}

/**
 * ��ȡһ��ҵ��ģ��
 *
 * @param string $model_name
 * @param array $params
 * @param bool $is_new
 * @return object
 */
function &bm($model_name, $params = array(), $is_new = false)
{
    static $models = array();
    $model_hash = md5($model_name . var_export($params, true));
    if ($is_new || !isset($models[$model_hash]))
    {
        $model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
        if (!is_file($model_file))
        {
            /* �����ڸ��ļ������޷���ȡģ�� */
            return false;
        }
        include_once($model_file);
        $model_name = ucfirst($model_name) . 'BModel';
        if ($is_new)
        {
            return new $model_name($params, db());
        }
        $models[$model_hash] = new $model_name($params, db());
    }
    return $models[$model_hash];
}

/**
 *    ��ȡ��ǰ������ʵ��
 *
 *    @author    Garbin
 *    @return    void
 */
function c(&$app)
{
    $GLOBALS['ECMALL_APP'] =& $app;
}

/**
 *    ��ȡ��ǰ������
 *
 *    @author    Garbin
 *    @return    Object
 */
function &cc()
{
    return $GLOBALS['ECMALL_APP'];
}

/**
 *    ����һ����
 *
 *    @author    Garbin
 *    @return    void
 */
function import()
{
    $c = func_get_args();
    if (empty($c))
    {
        return;
    }
    array_walk($c, create_function('$item, $key', 'include_once(ROOT_PATH . \'/includes/libraries/\' . $item . \'.php\');'));
}

/**
 *    ��default.abc����ַ���תΪ$default['abc']
 *
 *    @author    Garbin
 *    @param     string $str
 *    @return    string
 */
function strtokey($str, $owner = '')
{
    if (!$str)
    {
        return '';
    }
    if ($owner)
    {
        return $owner . '[\'' . str_replace('.', '\'][\'', $str) . '\']';
    }
    else
    {
        $parts = explode('.', $str);
        $owner = '$' . $parts[0];
        unset($parts[0]);
        return strtokey(implode('.', $parts), $owner);
    }
}
/**
 *    ���ٵ���
 *
 *    @author    Garbin
 *    @param     mixed $var
 *    @return    void
 */
function trace($var)
{
    static $i = 0;
    echo $i, '.', var_dump($var), '<br />';
    $i++;
}

/**
 *  rdump�ı���
 *
 *  @author Garbin
 *  @param  any
 *  @return void
 */
function dump($arr)
{
    $args = func_get_args();
    call_user_func_array('rdump', $args);
}

/**
 *  ��ʽ����ʾ������
 *
 *  @author Garbin
 *  @param  any
 *  @return void
 */
function rdump($arr)
{
    echo '<pre>';
    array_walk(func_get_args(), create_function('&$item, $key', 'print_r($item);'));
    echo '</pre>';
    exit();
}

/**
 *  ��ʽ������ʾ����������
 *
 *  @author Garbin
 *  @param  any
 *  @return void
 */
function vdump($arr)
{
    echo '<pre>';
    array_walk(func_get_args(), create_function('&$item, $key', 'var_dump($item);'));
    echo '</pre>';
    exit();
}

/**
 * ����MySQL���ݿ����ʵ��
 *
 * @author  wj
 * @return  object
 */
function &db()
{
    include_once(ROOT_PATH . '/core/model/mysql.php');
    static $db = null;
    if ($db === null)
    {
        $cfg = parse_url(DB_CONFIG);

        if ($cfg['scheme'] == 'mysql')
        {
            if (empty($cfg['pass']))
            {
                $cfg['pass'] = '';
            }
            else
            {
                $cfg['pass'] = urldecode($cfg['pass']);
            }
            $cfg ['user'] = urldecode($cfg['user']);

            if (empty($cfg['path']))
            {
                trigger_error('Invalid database name.', E_USER_ERROR);
            }
            else
            {
                $cfg['path'] = str_replace('/', '', $cfg['path']);
            }

            $charset = (CHARSET == 'utf-8') ? 'utf8' : CHARSET;
            $db = new cls_mysql();
            $db->cache_dir = ROOT_PATH. '/temp/query_caches/';
            $db->connect($cfg['host']. ':' .$cfg['port'], $cfg['user'],
                $cfg['pass'], $cfg['path'], $charset);
        }
        else
        {
            trigger_error('Unkown database type.', E_USER_ERROR);
        }
    }

    return $db;
}

/**
 * ��õ�ǰ������
 *
 * @return  string
 */
function get_domain()
{
    /* Э�� */
    $protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

    /* ������IP��ַ */
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
    {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
    elseif (isset($_SERVER['HTTP_HOST']))
    {
        $host = $_SERVER['HTTP_HOST'];
    }
    else
    {
        /* �˿� */
        if (isset($_SERVER['SERVER_PORT']))
        {
            $port = ':' . $_SERVER['SERVER_PORT'];

            if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
            {
                $port = '';
            }
        }
        else
        {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME']))
        {
            $host = $_SERVER['SERVER_NAME'] . $port;
        }
        elseif (isset($_SERVER['SERVER_ADDR']))
        {
            $host = $_SERVER['SERVER_ADDR'] . $port;
        }
    }

    return $protocol . $host;
}

/**
 * �����վ��URL��ַ
 *
 * @return  string
 */
function site_url()
{
    return get_domain() . substr(PHP_SELF, 0, strrpos(PHP_SELF, '/'));
}


/**
 * ��ȡUTF-8�������ַ����ĺ���
 *
 * @param   string      $str        ����ȡ���ַ���
 * @param   int         $length     ��ȡ�ĳ���
 * @param   bool        $append     �Ƿ񸽼�ʡ�Ժ�
 *
 * @return  string
 */
function sub_str($string, $length = 0, $append = true)
{

    if(strlen($string) <= $length) {
        return $string;
    }

    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

    $strcut = '';

    if(strtolower(CHARSET) == 'utf-8') {
        $n = $tn = $noc = 0;
        while($n < strlen($string)) {

            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t < 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }

            if($noc >= $length) {
                break;
            }

        }
        if($noc > $length) {
            $n -= $tn;
        }

        $strcut = substr($string, 0, $n);

    } else {
        for($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
        }
    }

    $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

    if ($append && $string != $strcut)
    {
        $strcut .= '...';
    }

    return $strcut;

}

/**
 * ����û�����ʵIP��ַ
 *
 * @return  string
 */
//function real_ip()
//{
//    static $realip = NULL;
//
//    if ($realip !== NULL)
//    {
//        return $realip;
//    }
//
//    if (isset($_SERVER))
//    {
//        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
//        {
//            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
//
//            /* ȡX-Forwarded-For�е�һ����unknown����ЧIP�ַ��� */
//            foreach ($arr AS $ip)
//            {
//                $ip = trim($ip);
//
//                if ($ip != 'unknown')
//                {
//                    $realip = $ip;
//
//                    break;
//                }
//            }
//        }
//        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
//        {
//            $realip = $_SERVER['HTTP_CLIENT_IP'];
//        }
//        else
//        {
//            if (isset($_SERVER['REMOTE_ADDR']))
//            {
//                $realip = $_SERVER['REMOTE_ADDR'];
//            }
//            else
//            {
//                $realip = '0.0.0.0';
//            }
//        }
//    }
//    else
//    {
//        if (getenv('HTTP_X_FORWARDED_FOR'))
//        {
//            $realip = getenv('HTTP_X_FORWARDED_FOR');
//        }
//        elseif (getenv('HTTP_CLIENT_IP'))
//        {
//            $realip = getenv('HTTP_CLIENT_IP');
//        }
//        else
//        {
//            $realip = getenv('REMOTE_ADDR');
//        }
//    }
//
//    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
//    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
//
//    return $realip;
//}

function real_ip()
{
	$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
}
/**
 * ��֤������ʼ���ַ�Ƿ�Ϸ�
 *
 * @param   string      $email      ��Ҫ��֤���ʼ���ַ
 *
 * @return bool
 */
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,5}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false)
    {
        if (preg_match($chars, $user_email))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

/**
 * ����Ƿ�Ϊһ���Ϸ���ʱ���ʽ
 *
 * @param   string  $time
 * @return  void
 */
function is_time($time)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

    return preg_match($pattern, $time);
}

/**
 * ��÷������ϵ� GD �汾
 *
 * @return      int         ���ܵ�ֵΪ0��1��2
 */
function gd_version()
{
    import('image.lib');

    return imageProcessor::gd_version();
}

/**
 * �ݹ鷽ʽ�ĶԱ����е������ַ�����ת��
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}

/**
 * �������Ա������������������ַ�����ת��
 *
 * @access   public
 * @param    mix        $obj      �����������
 * @author   Xuan Yan
 *
 * @return   mix                  �����������
 */
function addslashes_deep_obj($obj)
{
    if (is_object($obj) == true)
    {
        foreach ($obj AS $key => $val)
        {
            if ( ($val) == true)
            {
                $obj->$key = addslashes_deep_obj($val);
            }
            else
            {
                $obj->$key = addslashes_deep($val);
            }
        }
    }
    else
    {
        $obj = addslashes_deep($obj);
    }

    return $obj;
}

/**
 * �ݹ鷽ʽ�ĶԱ����е������ַ�ȥ��ת��
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function stripslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    }
}
/**
 *  ��һ���ִ��к���ȫ�ǵ������ַ�����ĸ���ո��'%+-()'�ַ�ת��Ϊ��Ӧ����ַ�
 *
 * @access  public
 * @param   string       $str         ��ת���ִ�
 *
 * @return  string       $str         ������ִ�
 */
function make_semiangle($str)
{
    $arr = array('��' => '0', '��' => '1', '��' => '2', '��' => '3', '��' => '4',
                 '��' => '5', '��' => '6', '��' => '7', '��' => '8', '��' => '9',
                 '��' => 'A', '��' => 'B', '��' => 'C', '��' => 'D', '��' => 'E',
                 '��' => 'F', '��' => 'G', '��' => 'H', '��' => 'I', '��' => 'J',
                 '��' => 'K', '��' => 'L', '��' => 'M', '��' => 'N', '��' => 'O',
                 '��' => 'P', '��' => 'Q', '��' => 'R', '��' => 'S', '��' => 'T',
                 '��' => 'U', '��' => 'V', '��' => 'W', '��' => 'X', '��' => 'Y',
                 '��' => 'Z', '��' => 'a', '��' => 'b', '��' => 'c', '��' => 'd',
                 '��' => 'e', '��' => 'f', '��' => 'g', '��' => 'h', '��' => 'i',
                 '��' => 'j', '��' => 'k', '��' => 'l', '��' => 'm', '��' => 'n',
                 '��' => 'o', '��' => 'p', '��' => 'q', '��' => 'r', '��' => 's',
                 '��' => 't', '��' => 'u', '��' => 'v', '��' => 'w', '��' => 'x',
                 '��' => 'y', '��' => 'z',
                 '��' => '(', '��' => ')', '��' => '[', '��' => ']', '��' => '[',
                 '��' => ']', '��' => '[', '��' => ']', '��' => '[', '��' => ']',
                 '��' => '[', '��' => ']', '��' => '{', '��' => '}', '��' => '<',
                 '��' => '>',
                 '��' => '%', '��' => '+', '��' => '-', '��' => '-', '��' => '-',
                 '��' => ':', '��' => '.', '��' => ',', '��' => '.', '��' => '.',
                 '��' => ',', '��' => '?', '��' => '!', '��' => '-', '��' => '|',
                 '��' => '"', '��' => '`', '��' => '`', '��' => '|', '��' => '"',
                 '��' => ' ');

    return strtr($str, $arr);
}

/**
 * ��ʽ�����ã������������ֻ�ٷֱȵĵط�
 *
 * @param   string      $fee    ����ķ���
 */
function format_fee($fee)
{
    $fee = make_semiangle($fee);
    if (strpos($fee, '%') === false)
    {
        return floatval($fee);
    }
    else
    {
        return floatval($fee) . '%';
    }
}

/**
 * �����ܽ��ͷ��ʼ������
 *
 * @param     float    $amount    �ܽ��
 * @param     string    $rate    ���ʣ������ǹ̶����ʣ�Ҳ�����ǰٷֱȣ�
 * @param     string    $type    ���ͣ�s ���۷� p ֧�������� i ��Ʊ˰��
 * @return     float    ����
 */
function compute_fee($amount, $rate, $type)
{
    $amount = floatval($amount);
    if (strpos($rate, '%') === false)
    {
        return round(floatval($rate), 2);
    }
    else
    {
        $rate = floatval($rate) / 100;
        if ($type == 's')
        {
            return round($amount * $rate, 2);
        }
        elseif($type == 'p')
        {
            return round($amount * $rate / (1 - $rate), 2);
        }
        else
        {
            return round($amount * $rate, 2);
        }
    }
}

/**
 * ��ȡ��������ip
 *
 * @access      public
 *
 * @return string
 **/
function real_server_ip()
{
    static $serverip = NULL;

    if ($serverip !== NULL)
    {
        return $serverip;
    }

    if (isset($_SERVER))
    {
        if (isset($_SERVER['SERVER_ADDR']))
        {
            $serverip = $_SERVER['SERVER_ADDR'];
        }
        else
        {
            $serverip = '0.0.0.0';
        }
    }
    else
    {
        $serverip = getenv('SERVER_ADDR');
    }

    return $serverip;
}
/**
 * ����û�����ϵͳ�Ļ��з�
 *
 * @access  public
 * @return  string
 */
function get_crlf()
{
/* LF (Line Feed, 0x0A, \N) �� CR(Carriage Return, 0x0D, \R) */
    if (stristr($_SERVER['HTTP_USER_AGENT'], 'Win'))
    {
        $the_crlf = "\r\n";
    }
    elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'Mac'))
    {
        $the_crlf = "\r"; // for old MAC OS
    }
    else
    {
        $the_crlf = "\n";
    }

    return $the_crlf;
}

/**
 * ����ת������
 *
 * @author  wj
 * @param string $source_lang       ��ת������
 * @param string $target_lang         ת�������
 * @param string $source_string      ��Ҫת��������ִ�
 * @return string
 */
function ecm_iconv($source_lang, $target_lang, $source_string = '')
{
    static $chs = NULL;

    /* ����ַ���Ϊ�ջ����ַ�������Ҫת����ֱ�ӷ��� */
    if ($source_lang == $target_lang || $source_string == '' || preg_match("/[\x80-\xFF]+/", $source_string) == 0)
    {
        return $source_string;
    }

    if ($chs === NULL)
    {
        import('iconv.lib');
        $chs = new Chinese(ROOT_PATH . '/');
    }

    return strtolower($target_lang) == 'utf-8' ? addslashes(stripslashes($chs->Convert($source_lang, $target_lang, $source_string))) : $chs->Convert($source_lang, $target_lang, $source_string);
}

function ecm_geoip($ip)
{
    static $fp = NULL, $offset = array(), $index = NULL;

    $ip    = gethostbyname($ip);
    $ipdot = explode('.', $ip);
    $ip    = pack('N', ip2long($ip));

    $ipdot[0] = (int)$ipdot[0];
    $ipdot[1] = (int)$ipdot[1];
    if ($ipdot[0] == 10 || $ipdot[0] == 127 || ($ipdot[0] == 192 && $ipdot[1] == 168) || ($ipdot[0] == 172 && ($ipdot[1] >= 16 && $ipdot[1] <= 31)))
    {
        return 'LAN';
    }

    if ($fp === NULL)
    {
        $fp     = fopen(ROOT_PATH . 'includes/codetable/ipdata.dat', 'rb');
        if ($fp === false)
        {
            return 'Invalid IP data file';
        }
        $offset = unpack('Nlen', fread($fp, 4));
        if ($offset['len'] < 4)
        {
            return 'Invalid IP data file';
        }
        $index  = fread($fp, $offset['len'] - 4);
    }

    $length = $offset['len'] - 1028;
    $start  = unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);
    for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8)
    {
        if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip)
        {
            $index_offset = unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
            $index_length = unpack('Clen', $index{$start + 7});
            break;
        }
    }

    fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
    $area = fread($fp, $index_length['len']);

    fclose($fp);
    $fp = NULL;

    return $area;
}

function ecm_json_encode($value)
{
    if (CHARSET == 'utf-8' && function_exists('json_encode'))
    {
        return json_encode($value);
    }

    $props = '';
    if (is_object($value))
    {
        foreach (get_object_vars($value) as $name => $propValue)
        {
            if (isset($propValue))
            {
                $props .= $props ? ','.ecm_json_encode($name)  : ecm_json_encode($name);
                $props .= ':' . ecm_json_encode($propValue);
            }
        }
        return '{' . $props . '}';
    }
    elseif (is_array($value))
    {
        $keys = array_keys($value);
        if (!empty($value) && !empty($value) && ($keys[0] != '0' || $keys != range(0, count($value)-1)))
        {
            foreach ($value as $key => $val)
            {
                $key = (string) $key;
                $props .= $props ? ','.ecm_json_encode($key)  : ecm_json_encode($key);
                $props .= ':' . ecm_json_encode($val);
            }
            return '{' . $props . '}';
        }
        else
        {
            $length = count($value);
            for ($i = 0; $i < $length; $i++)
            {
                $props .= ($props != '') ? ','.ecm_json_encode($value[$i])  : ecm_json_encode($value[$i]);
            }
            return '[' . $props . ']';
        }
    }
    elseif (is_string($value))
    {
        //$value = stripslashes($value);
        $replace  = array('\\' => '\\\\', "\n" => '\n', "\t" => '\t', '/' => '\/',
                        "\r" => '\r', "\b" => '\b', "\f" => '\f',
                        '"' => '\"', chr(0x08) => '\b', chr(0x0C) => '\f'
                        );
        $value  = strtr($value, $replace);
        if (CHARSET == 'big5' && $value{strlen($value)-1} == '\\')
        {
            $value  = substr($value,0,strlen($value)-1);
        }
        return '"' . $value . '"';
    }
    elseif (is_numeric($value))
    {
        return $value;
    }
    elseif (is_bool($value))
    {
        return $value ? 'true' : 'false';
    }
    elseif (empty($value))
    {
        return '""';
    }
    else
    {
        return $value;
    }
}

function ecm_json_decode($value, $type = 0)
{
    if (CHARSET == 'utf-8' && function_exists('json_decode'))
    {
        return empty($type) ? json_decode($value) : get_object_vars_deep(json_decode($value));
    }

    if (!class_exists('JSON'))
    {
        import('json.lib');
    }
    $json = new JSON();
    return $json->decode($value, $type);
}

/**
 * �����ɶ���������ɵĹ�������
 *
 * @access   pubilc
 * @param    obj    $obj
 *
 * @return   array
 */
function get_object_vars_deep($obj)
{
    if(is_object($obj))
    {
        $obj = get_object_vars($obj);
    }
    if(is_array($obj))
    {
        foreach ($obj as $key => $value)
        {
            $obj[$key] = get_object_vars_deep($value);
        }
    }
    return $obj;
}

function file_ext($filename)
{
    return trim(substr(strrchr($filename, '.'), 1, 10));
}

/**
 * �����������Ĳ�ѯ: "IN('a','b')";
 *
 * @access   public
 * @param    mix      $item_list      �б�������ַ���,���Ϊ�ַ���ʱ,�ַ���ֻ�������ִ�
 * @param    string   $field_name     �ֶ�����
 * @author   wj
 *
 * @return   void
 */
function db_create_in($item_list, $field_name = '')
{
    if (empty($item_list))
    {
        return $field_name . " IN ('') ";
    }
    else
    {
        if (!is_array($item_list))
        {
            $item_list = explode(',', $item_list);
            foreach ($item_list as $k=>$v)
            {
                $item_list[$k] = intval($v);
            }
        }

        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item)
        {
            if ($item !== '')
            {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp))
        {
            return $field_name . " IN ('') ";
        }
        else
        {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * ����Ŀ¼�������Ŀ¼���ϼ�Ŀ¼�����ڣ����ȴ����ϼ�Ŀ¼��
 * ������ ROOT_PATH ��������ֻ�ܴ��� ROOT_PATH Ŀ¼�µ�Ŀ¼
 * Ŀ¼�ָ��������� / ������ \
 *
 * @param   string  $absolute_path  ����·��
 * @param   int     $mode           Ŀ¼Ȩ��
 * @return  bool
 */
function ecm_mkdir($absolute_path, $mode = 0777)
{
    if (is_dir($absolute_path))
    {
        return true;
    }

    $root_path      = ROOT_PATH;
    $relative_path  = str_replace($root_path, '', $absolute_path);
    $each_path      = explode('/', $relative_path);
    $cur_path       = $root_path; // ��ǰѭ�������·��
    foreach ($each_path as $path)
    {
        if ($path)
        {
            $cur_path = $cur_path . '/' . $path;
            if (!is_dir($cur_path))
            {
                if (@mkdir($cur_path, $mode))
                {
                    fclose(fopen($cur_path . '/index.htm', 'w'));
                }
                else
                {
                    return false;
                }
            }
        }
    }

    return true;
}

/**
 * ɾ��Ŀ¼,��֧��Ŀ¼�д� ..
 *
 * @param string $dir
 *
 * @return boolen
 */
function ecm_rmdir($dir)
{
    $dir = str_replace(array('..', "\n", "\r"), array('', '', ''), $dir);
    $ret_val = false;
    if (is_dir($dir))
    {
        $d = @dir($dir);
        if($d)
        {
            while (false !== ($entry = $d->read()))
            {
               if($entry!='.' && $entry!='..')
               {
                   $entry = $dir.'/'.$entry;
                   if(is_dir($entry))
                   {
                       ecm_rmdir($entry);
                   }
                   else
                   {
                       @unlink($entry);
                   }
               }
            }
            $d->close();
            $ret_val = rmdir($dir);
         }
    }
    else
    {
        $ret_val = unlink($dir);
    }

    return $ret_val;
}

function price_format($price, $price_format = NULL)
{
    if (empty($price)) $price = '0.00';
    $price = number_format($price, 2);

    if ($price_format === NULL)
    {
        $price_format = Conf::get('price_format');
    }

    return sprintf($price_format, $price);
}

/**
 *  ����COOKIE
 *
 *  @access public
 *  @param  string $key     Ҫ���õ�COOKIE����
 *  @param  string $value   ������Ӧ��ֵ
 *  @param  int    $expire  ����ʱ��
 *  @return void
 */
function ecm_setcookie($key, $value, $expire = 0, $cookie_path=COOKIE_PATH, $cookie_domain=COOKIE_DOMAIN)
{
    setcookie($key, $value, $expire, $cookie_path, $cookie_domain);
}

/**
 *  ��ȡCOOKIE��ֵ
 *
 *  @access public
 *  @param  string $key    Ϊ��ʱ����������COOKIE
 *  @return mixed
 */
function ecm_getcookie($key = '')
{
    return isset($_COOKIE[$key]) ? $_COOKIE[$key] : 0;
}


/**
 * ������ת��
 *
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function ecm_iconv_deep($source_lang, $target_lang, $value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        if (is_array($value))
        {
            foreach ($value as $k=>$v)
            {
                $value[$k] = ecm_iconv_deep($source_lang, $target_lang, $v);
            }
            return $value;
        }
        elseif (is_string($value))
        {
            return ecm_iconv($source_lang, $target_lang, $value);
        }
        else
        {
            return $value;
        }
    }
}

/**
 *  fopen��װ����
 *
 *  @author wj
 *  @param string $url
 *  @param int    $limit
 *  @param string $post
 *  @param string $cookie
 *  @param boolen $bysocket
 *  @param string $ip
 *  @param int    $timeout
 *  @param boolen $block
 *  @return responseText
 */
function ecm_fopen($url, $limit = 500000, $post = '', $cookie = '', $bysocket = false, $ip = '', $timeout = 15, $block = true)
{
    $return = '';
    $matches = parse_url($url);
    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;

    if($post)
    {
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: '.strlen($post)."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
        $out .= $post;
    }
    else
    {
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
    }
    $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    if(!$fp)
    {
        return '';
    }
    else
    {
        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);
        $status = stream_get_meta_data($fp);
        if(!$status['timed_out'])
        {
            while (!feof($fp))
            {
                if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n"))
                {
                    break;
                }
            }

            $stop = false;
            while(!feof($fp) && !$stop)
            {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if($limit)
                {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        @fclose($fp);
        return $return;
    }
}

/**
 * Σ�� HTML���������
 *
 * @param   string  $html   ��Ҫ���˵�html����
 *
 * @return  string
 */
function html_filter($html)
{
    $filter = array(
        "/\s/",
        "/<(\/?)(script|i?frame|style|html|body|title|link|\?|\%)([^>]*?)>/isU",//object|meta|
        "/(<[^>]*)on[a-zA-Z]\s*=([^>]*>)/isU",
        );

    $replace = array(
        " ",
        "&lt;\\1\\2\\3&gt;",
        "\\1\\2",
        );

    $str = preg_replace($filter,$replace,$html);
    return $str;
}

/**
 * ����ϵͳ���б����ļ��������ļ���ģ��ṹ����
 *
 * @author  wj
 * @param   void
 *
 * @return  void
 */
function clean_cache()
{
    /*������*/
    $cache_dirs = array(
        ROOT_PATH . '/temp/caches',
        ROOT_PATH . '/temp/compiled/mall/admin',
        ROOT_PATH . '/temp/compiled/mall/',
        ROOT_PATH . '/temp/compiled/store/admin',
        ROOT_PATH . '/temp/compiled/store',
        ROOT_PATH . '/temp/js',
        ROOT_PATH . '/temp/query_caches',
        ROOT_PATH . '/temp/tag_caches',
        ROOT_PATH . '/temp/style',
    );

    foreach ($cache_dirs as $dir)
    {
        $d = dir($dir);
        if ($d)
        {
            while (false !== ($entry = $d->read()))
            {
                if($entry!='.' && $entry!='..' && $entry != '.svn' && $entry != 'admin' && $entry != 'index.html')
                {
                   ecm_rmdir($dir . '/'. $entry);
                }
            }
            $d->close();
        }
    }

    /*�����໺������*/
    if (is_file(ROOT_PATH . '/temp/query_caches/cache_category.php'))
    {
        unlink(ROOT_PATH . '/temp/query_caches/cache_category.php');
    }

    /*���һ����ǰͼƬ���沢���ն���Ŀ¼*/
    $expiry_time = strtotime('-1 week');
    $path = ROOT_PATH . '/temp/thumb';
    $d = dir($path);
    if ($d)
    {
        while(false !== ($entry = $d->read()))
        {
            if ($entry!='.' && $entry!= '..' && $entry != '.svn' && is_dir(($dir = ($path . '/' . $entry))))
            {
                $sd = dir($dir);
                if ($sd)
                {
                    $left_dir_count = 0;
                    while(false !== ($entry = $sd->read()))
                    {
                        if ($entry!='.' && $entry!= '..' && is_dir(($subdir = ($dir . '/' . $entry))))
                        {
                            $fsd = dir($subdir);
                            $left_file_count = 0;
                            while (false !== ($entry= $fsd->read()))
                            {
                                if ($entry!='.' && $entry!='..' && $entry != 'index.htm' && is_file(($file =$subdir . '/' . $entry)))
                                {
                                    if (filemtime($file) < $expiry_time)
                                    {
                                        unlink($file);
                                    }
                                    else
                                    {
                                        $left_file_count ++;
                                    }
                                }
                            }
                            $fsd->close();
                            if ($left_file_count == 0)
                            {
                                //�����Ŀ¼
                                ecm_rmdir($subdir);
                            }
                            else
                            {
                                $left_dir_count ++;
                            }
                        }
                    }
                    $sd->close();
                    if ($left_dir_count == 0) ecm_rmdir($dir);
                }
            }
        }
        $d->close();
    }

}

/**
 * ���ϵͳ������file_put_contents�����������ú���
 *
 * @author  wj
 * @param   string  $file
 * @param   mix     $data
 * @return  int
 */
if (!function_exists('file_put_contents'))
{
    define('FILE_APPEND', 'FILE_APPEND');
    if (!defined('LOCK_EX'))
    {
        define('LOCK_EX', 'LOCK_EX');
    }

    function file_put_contents($file, $data, $flags = '')
    {
        $contents = (is_array($data)) ? implode('', $data) : $data;

        $mode = ($flags == 'FILE_APPEND') ? 'ab+' : 'wb';

        if (($fp = @fopen($file, $mode)) === false)
        {
            return false;
        }
        else
        {
            $bytes = fwrite($fp, $contents);
            fclose($fp);

            return $bytes;
        }
    }
}

/**
 * ȥ���ַ����Ҳ���ܳ��ֵ�����
 *
 * @author  wj
 * @param   string      $str        �ַ���
 *
 *
 * @return  string
 */
function trim_right($str)
{
    $len = strlen($str);
    /* Ϊ�ջ򵥸��ַ�ֱ�ӷ��� */
    if ($len == 0 || ord($str{$len-1}) < 127)
    {
        return $str;
    }
    /* ��ǰ���ַ���ֱ�Ӱ�ǰ���ַ�ȥ�� */
    if (ord($str{$len-1}) >= 192)
    {
       return substr($str, 0, $len-1);
    }
    /* �зǶ������ַ����ȰѷǶ����ַ�ȥ��������֤�Ƕ������ַ��ǲ���һ���������֣�������ԭ��ǰ���ַ�Ҳ��ȡ�� */
    $r_len = strlen(rtrim($str, "\x80..\xBF"));
    if ($r_len == 0 || ord($str{$r_len-1}) < 127)
    {
        return sub_str($str, 0, $r_len);
    }

    $as_num = ord(~$str{$r_len -1});
    if ($as_num > (1<<(6 + $r_len - $len)))
    {
        return $str;
    }
    else
    {
        return substr($str, 0, $r_len-1);
    }
}

/**
 * ͨ���ú������к����������ƴ���
 *
 * @author  weberliu
 * @param   string      $fun        Ҫ���δ���ĺ�����
 * @return  mix         ����ִ�н��
 */
function _at($fun)
{
    $arg = func_get_args();
    unset($arg[0]);
    restore_error_handler();
    $ret_val = @call_user_func_array($fun, $arg);
    reset_error_handler();

    return $ret_val;
}

/**
 * �����ⲿ����
 *
 * @author  weberliu
 * @param   string  $func
 * @param   array   $params
 *
 * @return  mixed
 */
function outer_call($func, $params=null)
{
    restore_error_handler();

    $res = call_user_func_array($func, $params);

    set_error_handler('exception_handler');

    return $res;
}

function reset_error_handler()
{
    set_error_handler('exception_handler');
}

/**
 * �����Ƿ���ͨ����������ʵ�ҳ��
 *
 * @author wj
 * @param  void
 * @return boolen
 */
function is_from_browser()
{
    static $ret_val = null;
    if ($ret_val === null)
    {
        $ret_val = false;
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
        if ($ua)
        {
            if ((strpos($ua, 'mozilla') !== false) && ((strpos($ua, 'msie') !== false) || (strpos($ua, 'gecko') !== false)))
            {
                $ret_val = true;
            }
            elseif (strpos($ua, 'opera'))
            {
                $ret_val = true;
            }
        }
    }
    return $ret_val;
}

/**
 *    ���ļ��������ж��峣��
 *
 *    @author    Garbin
 *    @param     mixed $source
 *    @return    void
 */
function pl_define($source)
{
    if (is_string($source))
    {
        /* �������� */
        $source = include($source);
    }
    if (!is_array($source))
    {
        /* �������飬�޷����� */
        return false;
    }
    foreach ($source as $key => $value)
    {
        if (is_string($value) || is_numeric($value) || is_bool($value) || is_null($value))
        {
            /* ����ǿɱ�����ģ����� */
            define(strtoupper($key), $value);
        }
    }
}

/**
 *    ��ȡ��ǰʱ���΢����
 *
 *    @author    Garbin
 *    @return    float
 */
function pl_microtime()
{
    if (PHP_VERSION >= 5.0)
    {
        return microtime(true);
    }
    else
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }
}
/**
 *    ��֤����
 *
 *    @author    lihuoliang
 *    @parama	 string $email
 *    @return    boolen
 */
function isemail($email) 
{
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 *    ��֤�ֻ��źϷ���
 *
 *    @author    lihuoliang
 *    @parama	 string $mobile
 *    @return    boolen
 */
function is_mobile($mobile) 
{
	//�ж��Ƿ���13��14��15��18��ͷ
	if (preg_match("/^(13|14|15|18)\d{9}$/", $mobile))
	{
		return true;
	}else 
	{
		return false;
	}
	return ;
}
/**
 *    ��֤֧������ĺϷ���
 *
 *    @author    lihuoliang
 *    @parama	 string $tpwd
 *    @return    boolen
 */
function is_tpwd($tpwd) 
{
	//�ж��Ƿ���6λ��Ч����
	if (preg_match("/^\d{6}$/", $tpwd))
	{
		return true;
	}else 
	{
		return false;
	}
	return ;
}
/**
 *    �޸�ͼƬ������
 *
 *    @author    lihuoliang
 *    @parama	 string $file_path  �ļ����൱·��
 *               string $dirname    �ļ���Ŀ¼·��
 *    @return    boolen
 */
function reimgname($file_path,$dirname)
{
	$basename = basename($file_path); 
	$suarr   = explode('.',$basename);
	$suffix  = $suarr[1]; //��ȡ�ļ���׺��
	$newname = $dirname.'/'.md5($suarr[0]).'.'.$suffix;
	$flag = rename(ROOT_PATH . '/' . $file_path,ROOT_PATH .'/'.$newname);
	return $newname;
}
/**
 * �ݹ鴴��Ŀ¼
 * @param string $path
 */
function createFolder($path) { 
	if (!file_exists($path)) { 
		createFolder(dirname($path)); 
		mkdir($path, 0777); 
	} 
}
?>
