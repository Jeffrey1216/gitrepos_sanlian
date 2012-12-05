<?php

/* ���� store */
class StoreModel extends BaseModel
{
    var $table  = 'store';
    var $prikey = 'store_id';
    var $alias  = 's';
    var $_name  = 'store';

    var $_relation = array(
        // һ�������ж��֧����ʽ
        'has_payment' => array(
            'model'         => 'payment',
            'type'          => HAS_MANY,
            'foreign_key'   => 'store_id',
            'dependent'     => true
        ),
        // һ�������ж�����ͷ�ʽ
        'has_shipping' => array(
            'model'         => 'shipping',
            'type'          => HAS_MANY,
            'foreign_key'   => 'store_id',
            'dependent'     => true
        ),
        // һ�������ж����Ʒ����
        'has_gcategory' => array(
            'model'         => 'gcategory',
            'type'          => HAS_MANY,
            'foreign_key' => 'store_id',
            'dependent' => true
        ),
        // һ�������ж����Ʒ
        /* 'has_goods' => array(
            'model'         => 'goods',
            'type'          => HAS_MANY,
            'foreign_key'   => 'store_id',
            'dependent' => true
        ), */
        // һ�������ж������
        'has_order' => array(
            'model'         => 'order',
            'type'          => HAS_MANY,
            'foreign_key'   => 'seller_id',
            'dependent' => true
        ),
        // һ�������ж���Ƽ�����
        'has_recommend' => array(
            'model'         => 'recommend',
            'type'          => HAS_MANY,
            'foreign_key' => 'store_id',
            'dependent' => true
        ),
        // һ�������ж������
        'has_article' => array(
            'model'         => 'article',
            'type'          => HAS_MANY,
            'foreign_key' => 'store_id',
            'dependent' => true
        ),
        // һ�������ж��pageivew
        'has_pageview' => array(
            'model'         => 'pageview',
            'type'          => HAS_MANY,
            'foreign_key'   => 'store_id',
            'dependent'     => true
        ),
        // һ�������ж����������
        'has_partner' => array(
            'model'         => 'partner',
            'type'          => HAS_MANY,
            'foreign_key'   => 'store_id',
            'dependent'     => true
        ),
        'has_cart'    => array(
            'type'          => HAS_MANY,
            'model'         => 'cart',
            'foreign_key'   => 'store_id',
        ),
        'has_quickcart'    => array(
            'type'          => HAS_MANY,
            'model'         => 'quickcart',
            'foreign_key'   => 'store_id',
        ),
        // һ����������һ����Ա
        'belongs_to_user' => array(
            'model'         => 'member',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_store',
        ),
        // һ����������һ���ȼ�
        'belongs_to_sgrade' => array(
            'model'         => 'sgrade',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'store_id',
            'reverse'       => 'has_store',
        ),
        // ���̺ͻ�Ա�Ƕ�Զ�Ĺ�ϵ����Ա�ղص��̣�
        'be_collect' => array(
            'model'         => 'member',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'collect',
            'foreign_key'   => 'item_id',
            'ext_limit'     => array('type' => 'store'),
            'reverse'       => 'collect_store',
        ),
        // ���̺ͷ����Ƕ�Զ�Ĺ�ϵ
        'has_scategory' => array(
            'model'         => 'scategory',
            'type'          => HAS_AND_BELONGS_TO_MANY,
            'middle_table'  => 'category_store',
            'foreign_key'   => 'store_id',
            'reverse'       => 'belongs_to_store',
        ),
        // ���̺ͻ�Ա�Ƕ�Զ�Ĺ�ϵ����Աӵ�е���Ȩ�ޣ�
        'be_manage' => array(
            'model'        => 'member',
            'type'         => HAS_AND_BELONGS_TO_MANY,
            'middle_table' => 'user_priv',
            'foreign_key'  => 'store_id',
            'reverse'      => 'manage_store',
        ),
         //һ�����̶�Ӧ����ϴ��ļ�
        'has_uploadedfile' => array(
            'model'             => 'uploadedfile',
            'type'              => HAS_MANY,
            'foreign_key'       => 'store_id',
            'dependent'         => true
        ),
        //һ�����̶�Ӧ�����Ʒ��ѯ
        'has_question' => array(
            'model'       =>'goodsqa',
            'type'        => HAS_MANY,
            'foreign_key'     => 'store_id',
            'dependent' => true,
        ),
        // һ�����̿����ж���Ż�ȯ
        'has_coupon' => array(
            'model'       =>'coupon',
            'type'        => HAS_MANY,
            'foreign_key' => 'store_id',
            'dependent'   => true,
        ),
        //���̺��Ź����һ�Զ��ϵ
        'has_groupbuy' => array(
            'model' => 'groupbuy',
            'type' => HAS_MANY,
            'foreign_key' => 'store_id',
            'dependent'   => true, // ����
        ),
    );

    var $_autov = array(
        'owner_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'store_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
    );

    /*
     * �ж������Ƿ�Ψһ
     */
    function unique($store_name, $store_id = 0)
    {
        $conditions = "store_name = '" . $store_name . "'";
        $store_id && $conditions .= " AND store_id <> '" . $store_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }

    /**
     * ȡ����Ϣ
     */
    function get_info($store_id)
    {
        $info = $this->get(array(
            'conditions' => $store_id,
            'join'       => 'belongs_to_user',
            'fields'     => 'this.*,member.user_name, member.email',
        ));
        if (!empty($info['certification']))
        {
            $info['certifications'] = explode(',', $info['certification']);
        }
        return $info;
    }

    /* ���� */
    function add($data, $compatible = false)
    {
        $res = parent::add($data, $compatible);
        if ($res === false)
        {
            return false;
        }
        $store_id = $data['store_id'];
        $userpriv_mod =& m('userpriv');
        $userpriv_mod->add(array(
            'store_id' => $store_id,
            'user_id'  => $store_id,
            'privs'    => 'all',
        ));

        return $res;
    }

    /**
     * ��ȡ���������˵�����
     *
     */
    function list_regions()
    {
        $data = array();
        $sql = "SELECT region_id, region_name, count(*) as count FROM {$this->table} WHERE region_id > 0 GROUP BY region_id ORDER BY count DESC LIMIT 50";
        $res = $this->db->query($sql);
        while ($row = $this->db->fetchRow($res))
        {
            $data[$row['region_id']] = $row['region_name'];
        }
        return $data;
    }

    /**
     *    ���¼������ö�
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @return    int
     */
    function recount_credit_value($store_id)
    {
        $credit_value = 0;
        $model_ordergoods =& m('ordergoods');
        /* �ҳ�����is_validΪ1����Ʒ���ۼ�¼���������ǵ�credit_value�ĺ� */
        $info = $model_ordergoods->get(array(
            'join'          => 'belongs_to_order',
            'conditions'    => "seller_id={$store_id} AND evaluation_status=1 AND is_valid = 1",
            'fields'        => 'SUM(credit_value) AS credit_value',
            'index_key'     => false,   /* ����Ҫ���� */
        ));
        $credit_value = $info['credit_value'];

        return $credit_value;
    }

    /**
     *    ���¼��������
     *
     *    @author    Garbin
     *    @param     int $store_id
     *    @return    float
     */
    function recount_praise_rate($store_id)
    {
        $praise_rate = 0.00;
        $model_ordergoods =& m('ordergoods');

        /* �ҳ�����is_validΪ1����Ʒ�е���Ʒ���ۼ�¼���� */
        $info  = $model_ordergoods->get(array(
            'join'          => 'belongs_to_order',
            'conditions'    => "seller_id={$store_id} AND evaluation_status=1 AND is_valid=1",
            'fields'        => 'COUNT(*) as evaluation_count',
            'index_key'     => false,   /* ����Ҫ���� */
        ));
        $evaluation_count = $info['evaluation_count'];
        if (!$evaluation_count)
        {
            return $praise_count;
        }

        /* �ҳ����е�evaluationΪ3�ļ�¼���� */
        $info = $model_ordergoods->get(array(
            'join'          => 'belongs_to_order',
            'conditions'    => "seller_id={$store_id} AND evaluation_status=1 AND is_valid=1 AND evaluation=3",
            'fields'        => 'COUNT(*) as praise_count',
            'index_key'     => false,   /* ����Ҫ���� */
        ));
        $praise_count = $info['praise_count'];
        /* ���������ռ�����İٷֱ� */
        $praise_rate = round(($praise_count / $evaluation_count), 4) * 100;

        return $praise_rate;
    }

    /**
     * ȡ�õ���������Ϣ��������������Ʒ�����ϴ��ռ��С�����̹���ʱ��ȵ�
     */
    function get_settings($store_id)
    {
        return $this->get(array(
            'conditions' => $store_id,
            'fields' => 'sgrade.*',
            'join' => 'belongs_to_sgrade',
        ));
    }

    /**
     * ��������ֵ����ͼ��
     *
     * @param   int     $credit_value   ����ֵ
     * @param   int     $step           ��͵ȼ�������������ֵ
     * @return  string  ͼƬ�ļ���
     */
    function compute_credit($credit_value, $step = 5)
    {
        $level_1 = $step * 5;
        $level_2 = $level_1 * 6;
        $level_3 = $level_2 * 6;
        $level_4 = $level_3 * 6;
        $level_5 = $level_4 * 6;
        if ($credit_value < $level_1)
        {
            return 'heart_' . (floor($credit_value / $step) + 1) . '.gif';
        }
        elseif ($credit_value < $level_2)
        {
            return 'diamond_' . (floor(($credit_value - $level_1) / $level_1) + 1) . '.gif';
        }
        elseif ($credit_value < $level_3)
        {
            return 'crown_' . (floor(($credit_value - $level_2) / $level_2) + 1) . '.gif';
        }
//        elseif ($credit_value < $level_4)
//        {
//            return (floor(($credit_value - $level_3) / $level_3) + 1) . 'level4' . '.gif';
//        }
//        elseif ($credit_value < $level_5)
//        {
//            return (floor(($credit_value - $level_4) / $level_4) + 1) . 'level5' . '.gif';
//        }
        else
        {
            return 'level_end.gif';
        }
    }

    /**
     *    �����������Ƿ����
     *
     *    @author    Garbin
     *    @param     string $subdomain  Ҫע��Ķ�������
     *    @param     string $reserved   ϵͳ����������
     *    @param     string $length     ϵͳ���Ƶ�ע�᳤��
     *    @return    bool
     */
    function check_domain($subdomain, $reserved, $length)
    {
        if (!$subdomain)
        {
            return true;
        }
        if (!preg_match("/^[a-z0-9]+$/i", $subdomain))
        {
            $this->_error('domain_format_error');

            return false;
        }

        /* ����Ƿ��Ǳ������� */
        if ($reserved)
        {
            if (in_array($subdomain, explode(',', $reserved)))
            {
                $this->_error('reserved_domain');

                return false;
            }
        }

        /* ��鳤���Ƿ�Ϸ� */
        if ($length)
        {
            list($min, $max) = explode('-', $length);
            if (strlen($subdomain) < $min || strlen($subdomain) > $max)
            {
                $this->_error('domain_length_error', $length);

                return false;
            }
        }

        /* ���Ψһ�� */
        if ($this->get("domain='{$subdomain}'"))
        {
            $this->_error('domain_exists');

            return false;
        }

        return true;
    }

    function clear_cache($store_id)
    {
        $cache_server =& cache_server();
        $keys = array('function_get_store_data_' . $store_id);
        foreach ($keys as $key)
        {
            $cache_server->delete($key);
        }
    }

    function edit($conditions, $edit_data)
    {
        $store_list = $this->find(array(
            'fields'     => 'store_id',
            'conditions' => $conditions,
        ));
        foreach ($store_list as $store)
        {
            // �������
            $this->clear_cache($store['store_id']);
        }

        return parent::edit($conditions, $edit_data);
    }

    function drop($conditions, $fields = '')
    {
        /* ������� */
        $store_list = $this->find(array(
            'fields'     => 'store_id',
            'conditions' => $conditions,
        ));
        foreach ($store_list as $store)
        {
            $this->clear_cache($store['store_id']);
        }

        return parent::drop($conditions, $fields);
    }

    /* ȡ�ñ���������Ʒ���� */
    function get_sgcategory_options($store_id)
    {
        $mod =& bm('gcategory', array('_store_id' => $store_id));
        $gcategories = $mod->get_list();
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($gcategories, 'cate_id', 'parent_id', 'cate_name');
        return $tree->getOptions();
    }
    /* ȡ�����е��� */
    function getStore()
    {
        $sql = "SELECT store_id,store_name FROM {$this->table} WHERE state = 1";
        return $this->db->getAll($sql);
    }
}

?>
