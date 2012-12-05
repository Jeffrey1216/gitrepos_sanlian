<?php
/**
 *    ��Ʒ���������
 */
class GoodsApp extends BackendApp
{
	var $_goods_mod;
    var $_goodsattr_mod;
    var $_spec_mod;
    var $_image_mod;
    var $_uploadedfile_mod;
    var $_supply_mod;
    var $_supply_goods_mod;
    var $_gcategory_mod;
    
    function __construct()
    {
        $this->GoodsApp();
    }
    function GoodsApp()
    {
        parent::BackendApp();
		import('chineseSpell.class');
        $this->_goods_mod =& bm('goods');
        $this->_goodsattr_mod = & m('goodsattr');
        $this->_spec_mod  =& m('goodsspec');
        $this->_image_mod =& m('goodsimage');
        $this->_uploadedfile_mod =& m('uploadedfile');
        $this->_supply_mod = & m('supply');
        $this->_supply_goods_mod = & m('supplygoods');
        $this->_gcategory_mod =& bm('gcategory');
        $this->_get_brand_mod = &m('brand');
    }

    /* ��Ʒ�б� */
    function index()
    {
    	 $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'goods_name',
                'equal' => 'like',
            ),
            array(
                'field' => 'brand',
                'equal' => 'like',
            ),
            array(
                'field' => 'closed',
                'type'  => 'int',
            ),
            array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',        	
            ),
        ));
        // ����
        $cate_id = empty($_GET['cate_id']) ? 0 : intval($_GET['cate_id']);
        if ($cate_id > 0)
        {
            
            $cate_ids = $this->_gcategory_mod->get_descendant_ids($cate_id);
            $conditions .= " AND g.cate_id" . db_create_in($cate_ids);
        }
		
        //��������
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'goods_id';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'goods_id';
            $order = 'desc';
        }
        $page = $this->_get_page();
        $goods_list = $this->_goods_mod->get_goods_list(array(
            'conditions' => "1 = 1" . $conditions,
            'count' => true,
            'order' => "$sort $order",
            'limit' => $page['limit'],
        ));
        foreach ($goods_list as $key => $goods)
        {
            $goods_list[$key]['cate_name'] = $this->_goods_mod->format_cate_name($goods['cate_name']);
        }
        $this->assign('goods_list', $goods_list);
        $page['item_count'] = $this->_goods_mod->getCount();
        $this->_format_page($page);
        $this->assign('status',array(
        	0 => 'δ���',
        	1 => '��ͨ��',
        	2 => 'δͨ��',
        ));
        $this->assign('page_info', $page);
        // ��һ������
        $this->assign('gcategories', $this->_gcategory_mod->get_all_options(0));
        $this->import_resource(array('script' => 'mlselection.js,inline_edit.js'));
        $this->assign('imgurl', IMAGE_URL);
        $this->display('goods.index.html');
    }

	//������Ʒ
    function add()
    {
		if(!empty($_GET['type']) && $_GET['type'] == 'addSuccess'){
			$this->show_message('add_ok',
				'back_list', 'index.php?app=goods',
				'continue_add', 'index.php?app=goods&amp;act=add'
			);
		return ;
		}
    	if (!IS_POST)
    	{       
    		/* ��Ӵ���iframe�յ�id,belong*/
             $this->assign("id", 0);
             $this->assign("belong", BELONG_GOODS);
             $this->assign('user_id',$this->visitor->get('user_id'));
             $this->assign('goods', $this->_get_goods_info(0));
             $this->assign('supply',$this->get_supply());
             /* ȡ������״��ͼƬ */
             $goods_images =array();
             $desc_images =array();
             $uploadfiles = $this->_uploadedfile_mod->find(array(
                 'join' => 'belongs_to_goodsimage',
                 'conditions' => "belong=".BELONG_GOODS." AND item_id=0 AND user_id=".$this->visitor->get('user_id'),
                 'order' => 'add_time ASC'
             ));
             foreach ($uploadfiles as $key => $uploadfile)
             {
                 if ($uploadfile['goods_id'] == null)
                 {
                     $desc_images[$key] = $uploadfile;
                 }
                 else
                 {
                     $goods_images[$key] = $uploadfile;
                 }
             }
             $data = require('../includes/libraries/unit_config.php');
             $this->assign('data',$data);
             $this->assign('goods_images', $goods_images);
             $this->assign('desc_images', $desc_images);
             /* ȡ����Ʒ���� */
             $this->assign('mgcategories', $this->_gcategory_mod->get_all_options(0)); // �̳Ƿ����һ��

             /* ��ƷͼƬ�����ϴ��� */
             $this->assign('images_upload', $this->_build_upload(array(
                 'obj' => 'GOODS_SWFU',
                 'belong' => BELONG_GOODS,
                 'item_id' => 0,
                 'button_text' => Lang::get('bat_upload'),
                 'progress_id' => 'goods_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=goods_image&user_id='.$this->visitor->get('user_id'),
                 'if_multirow' => 1,
             )));

             /* �༭��ͼƬ�����ϴ��� */
             $this->assign('editor_upload', $this->_build_upload(array(
                 'obj' => 'EDITOR_SWFU',
                 'belong' => BELONG_GOODS,
                 'item_id' => 0,
                 'button_text' => Lang::get('bat_upload'),
                 'button_id' => 'editor_upload_button',
                 'progress_id' => 'editor_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=desc_image&user_id='.$this->visitor->get('user_id'),
                 'if_multirow' => 1,
                 'ext_js' => false,
                 'ext_css' => false,
             )));
             $this->import_resource(array(
             	'script' => array(
                     array(
                         'path' => 'mlselection.js',
                         'attr' => 'charset="gbk"',
                     ),
                     array(
                         'path' => 'jquery.plugins/jquery.validate.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'mlselection.js',
	                     'attr' => 'charset="utf-8"',
                     ), 
                     array(
	                     'path' => 'ui/jquery.ui.core.js',
	                     'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'ui/jquery.ui.widget.js',
	                     'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'ui/jquery.ui.position.js',
	                     'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'ui/jquery.ui.autocomplete.js',
	                     'attr' => 'charset="utf-8"',
                     ), 
                 ),
             ));
            $region_mod = & m('region');
            $brand_mod  = & m('brand');
    		$brand = $brand_mod->findAll();
            foreach ($brand as $k=>$v){
            	$brands[$v['brand_id']] = $v['brand_name'];
            }
            $region = $region_mod->get_list(0);
            foreach ($region as $k=>$v){
            	$regions[$v['region_id']] = $v['region_name'];
            }
            $this->assign('region', $regions); 
            $this->assign('brand', $brands); 
			$this->display("goods_add.form.html");
    	}
    	else
    	{   
    		/* ȡ������ */
            $data = $this->_get_post_data(0);

    	    /* ������� */
            if (!$this->_check_post_data($data, 0)) {
                $this->_showErrorAlert();
                return;
            }
            /* �������� */
            if (!$this->_save_post_data($data, 0)) {
                $this->_showErrorAlert();
                return;
            }
            //ȡ���ϴ���Ʒ��Ϣ
            $goods_info = $this->_get_goods_info($this->_last_update_id);
            if ($goods_info['if_show'])
            {
                $goods_url = SITE_URL . '/' . url('app=goods&id=' . $goods_info['goods_id']);
                $feed_images = array();
                $feed_images[] = array(
                    'url'   => SITE_URL . '/' . $goods_info['default_image'],
                    'link'  => $goods_url,
                );
                $this->send_feed('goods_created', array(
                    'user_id' => $this->visitor->get('user_id'),
                    'user_name' => $this->visitor->get('user_name'),
                    'goods_url' => $goods_url,
                    'goods_name' => $goods_info['goods_name'],
                	'old_name'	=> $goods_info['old_name'],
                    'images' => $feed_images
                ));
            }
            $this->show_message('add_ok',
                'back_list', 'index.php?app=goods',
                'continue_add', 'index.php?app=goods&amp;act=add'
            );
            $this->_showErrorAlert('refresh');
    	}
    }
   
    /**
     * ȡ����Ʒ��Ϣ
     */
    function _get_goods_info($id = 0)
    {
        $default_goods_image = Conf::get('default_goods_image'); // �̳�Ĭ����ƷͼƬ
        if ($id > 0)
        {
            $goods_info = $this->_goods_mod->get_goods_info($id);

            if ($goods_info === false)
            {
                return false;
            }
            $goods_info['default_goods_image'] = $default_goods_image;
            if (empty($goods_info['default_image']))
            {
                   $goods_info['default_image'] = $default_goods_image;
            }
        }
        else
        {
            $goods_info = array(
                'cate_id' => 0,
                'if_show' => 1,
                'spec_qty' => 0,
                'spec_name_1' => '',
                'spec_name_2' => '',
                'default_goods_image' => $default_goods_image,
            );
        }
        $goods_info['spec_json'] = ecm_json_encode(array(
            'spec_qty' => $goods_info['spec_qty'],
            'spec_name_1' => isset($goods_info['spec_name_1']) ? $goods_info['spec_name_1'] : '',
            'spec_name_2' => isset($goods_info['spec_name_2']) ? $goods_info['spec_name_2'] : '',
            'specs' => $goods_info['_specs'],
        ));
        return $goods_info;
    }
    function _showErrorAlert($type = warning, $msg = NULL) {
        if ($type == 'refresh') {
            
            echo "<script>parent.showSuccessMsg();</script>";
        } else {
            $errorArray = $this->get_error();
            $error_msg = Lang::get($errorArray[0]['msg']);
            echo "<script>alert('" . $error_msg . "');</script>";
        }
    }
    
    /* �Ƽ���Ʒ�� */
    function recommend()
    {
        if (!IS_POST)
        {
            /* ȡ���Ƽ����� */
            $recommend_mod =& bm('recommend', array('_store_id' => 0));
            $recommends = $recommend_mod->get_options();
            if (!$recommends)
            {
                $this->show_warning('no_recommends', 'go_back', 'javascript:history.go(-1);', 'set_recommend', 'index.php?app=recommend');
                return;
            }
            $this->assign('recommends', $recommends);
            $this->display('goods.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }

            $recom_id = empty($_POST['recom_id']) ? 0 : intval($_POST['recom_id']);
            if (!$recom_id)
            {
                $this->show_warning('recommend_required');
                return;
            }

            $ids = explode(',', $id);
            $recom_mod =& bm('recommend', array('_store_id' => 0));
            $recom_mod->createRelation('recommend_goods', $recom_id, $ids);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('recommend_ok',
                'back_list', 'index.php?app=goods&page=' . $ret_page,
                'view_recommended_goods', 'index.php?app=recommend&amp;act=view_goods&amp;id=' . $recom_id);
        }
    }

    /* �༭��Ʒ */
    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* ����iframe id */
            $this->assign('id', $id);
            $this->assign('belong', BELONG_GOODS);
            $this->assign('user_id',$this->visitor->get('user_id'));
            
            if(!$id || !($goods = $this->_get_goods_info($id)))
            {
                $this->show_warning('no_such_goods');
                return;
            }
            $goods['tags'] = trim($goods['tags'], ',');
            
            $this->assign('goods', $goods);
            /* ȡ����Ʒ������ͼƬ */
            $uploadedfiles = $this->_uploadedfile_mod->find(array(
                'fields' => "f.*,goods_image.*",
                'conditions' => "belong=".BELONG_GOODS." AND item_id=".$id,
                'join'       => 'belongs_to_goodsimage',
                'order' => 'add_time ASC'
            ));
            $default_goods_images = array(); // Ĭ����ƷͼƬ
            $other_goods_images = array(); // ������ƷͼƬ
            $desc_images = array(); // ����ͼƬ
           
            foreach ($uploadedfiles as $key => $uploadedfile)
            {
                if ($uploadedfile['goods_id'] == null)
                {
                    $desc_images[$key] = $uploadedfile;
                }
                else
                {
                    if (!empty($goods['default_image']) && ($uploadedfile['thumbnail'] == $goods['default_image']))
                    {
                        $default_goods_images[$key] = $uploadedfile;
                    }
                    else
                    {
                        $other_goods_images[$key] = $uploadedfile;
                    }
                }
            }
            $this->assign('goods_images', array_merge($default_goods_images, $other_goods_images));
            $this->assign('desc_images', $desc_images);
        	/* ȡ����Ʒ���� */
            $this->assign('mgcategories', $this->_gcategory_mod->get_all_options(0)); // �̳Ƿ����һ������
			$this->import_resource(array(
             	'script' => array(
                     array(
                         'path' => 'mlselection.js',
                         'attr' => 'charset="gbk"',
                     ),
                     array(
                         'path' => 'jquery.plugins/jquery.validate.js',
                         'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'mlselection.js',
	                     'attr' => 'charset="utf-8"',
                     ), 
                     array(
	                     'path' => 'ui/jquery.ui.core.js',
	                     'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'ui/jquery.ui.widget.js',
	                     'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'ui/jquery.ui.position.js',
	                     'attr' => 'charset="utf-8"',
                     ),
                     array(
	                     'path' => 'ui/jquery.ui.autocomplete.js',
	                     'attr' => 'charset="utf-8"',
                     ), 
                 ),
             ));
            /* ��ƷͼƬ�����ϴ��� */
             $this->assign('images_upload', $this->_build_upload(array(
                 'obj' => 'GOODS_SWFU',
                 'belong' => BELONG_GOODS,
                 'item_id' => $id,
                 'button_text' => Lang::get('bat_upload'),
                 'progress_id' => 'goods_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=goods_image&user_id='.$this->visitor->get('user_id'),
                 'if_multirow' => 1,
             )));

             /* �༭��ͼƬ�����ϴ��� */
             $this->assign('editor_upload', $this->_build_upload(array(
                 'obj' => 'EDITOR_SWFU',
                 'belong' => BELONG_GOODS,
                 'item_id' => $id,
                 'button_text' => Lang::get('bat_upload'),
                 'button_id' => 'editor_upload_button',
                 'progress_id' => 'editor_upload_progress',
                 'upload_url' => 'index.php?app=swfupload&instance=desc_image&user_id='.$this->visitor->get('user_id'),
                 'if_multirow' => 1,
                 'ext_js' => false,
                 'ext_css' => false,
             )));
            $cod_supplys = $this->_supply_goods_mod->getAll('SELECT s.supply_name,sd.supply_id from pa_supply_goods sd left join pa_supply s on sd.supply_id=s.supply_id where s.supply_name is not null and sd.goods_id='.$id);
            $this->assign('supply',$this->get_supply());
            $region_mod = & m('region');
            $region = $region_mod->get_list(0);
            foreach ($region as $k=>$v){
            	$regions[$v['region_id']] = $v['region_name'];
            }
            $this->assign('region', $regions); 
            
            $brand_mod  = & m('brand');
            $brand = $brand_mod->findAll();
            foreach ($brand as $k=>$v){
            	$brands[$v['brand_id']] = $v['brand_name'];
            }
            $this->assign('region', $regions); 
            $this->assign('brand', $brands); 
            $data = require('../includes/libraries/unit_config.php');
            $this->assign('data',$data);
            $this->assign('goods',$this->_get_goods_info($id));	
            $spec_mod=&m('goodsspec');
            $spec_data=$spec_mod->find('goods_id='.$id);
            $this->assign('specs',$spec_data);
            $this->assign('cod_supplys',$cod_supplys);
            $this->display('goods.form.html');
        }
        else
        {	
            /* ȡ������ */
            $data = $this->_get_post_data($id);
            
            /* ������� */
            if (!$this->_check_post_data($data, $id))
            {
                $this->show_warning($this->get_error());
                return;
            }
            /* ������Ʒ */
            if (!$this->_save_post_data($data, $id))
            {
                $this->show_warning($this->get_error());
                return;
            }

            $this->show_message('edit_ok',
                'back_list', 'index.php?app=goods',
                'edit_again', 'index.php?app=goods&amp;act=edit&amp;id=' . $id);
        }
    }

    //�첽�޸�����
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('goods_name', 'brand', 'closed')))
       {
           $data[$column] = $value;
           $this->_goods_mod->edit($id, $data);
           if(!$this->_goods_mod->has_error())
           {
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    /* ɾ����Ʒ */
/* ��ʱֹͣ�����
   function drop()
    {
        if (!IS_POST)
        {
            $this->display('goods.batch.html');
        }
        else
        {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if (!$id)
            {
                $this->show_warning('Hacking Attempt');
                return;
            }
            $ids = explode(',', $id);

            // notify store owner
            $ms =& ms();
            $goods_list = $this->_goods_mod->find(array(
                "conditions" => $ids,
                "fields" => "goods_name, store_id",
            ));
            foreach ($goods_list as $goods)
            {
                //$content = sprintf(LANG::get('toseller_goods_droped_notify'), );
                $content = get_msg('toseller_goods_droped_notify', array('reason' => trim($_POST['drop_reason']),
                    'goods_name' => addslashes($goods['goods_name'])));
                $ms->pm->send(MSG_SYSTEM, $goods['store_id'], '', $content);
            }

            // drop
            $this->_goods_mod->drop_data($ids);
            $this->_goods_mod->drop($ids);
            $ret_page = isset($_GET['ret_page']) ? intval($_GET['ret_page']) : 1;
            $this->show_message('drop_ok',
                'back_list', 'index.php?app=goods&page=' . $ret_page);
        }
    }
 */   
	public function getCategory()
    {
    	$mall_type = empty($_GET['mall_type']) ? 0 : intval($_GET['mall_type']);
    	
    	if (!$mall_type)
    	{
    		$this->json_error("has_not_mall_type");
    		return;
    	}
    	$_gcategory_mod = & m('gcategory');
    	$gcategory_list = $_gcategory_mod->getAll("select * from pa_gcategory where 
    	store_id = 0 and mall_type = '" . $mall_type . "' and parent_id = 0");
    	
    	if (!$gcategory_list)
    	{
    		$this->json_error("error");
    		return;
    	}
    	
    	$this->json_result($gcategory_list);
    }
    
    /****
     * ȡ�ù�Ӧ����Ϣ
     * ****/
    function get_supply()
    {
    	$supply_info = $this->_supply_mod->getAll('select su.supply_id,su.supply_name from pa_supply su');
    	return $supply_info;
    }

	function drop_image()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        $uploadedfile = $this->_uploadedfile_mod->get(array(
                  'conditions' => "f.file_id = '$id'",
                  'join' => 'belongs_to_goodsimage',
                  'fields' => 'goods_image.image_url, goods_image.thumbnail, goods_image.yimage_url,
                  			   goods_image.mimage_url,goods_image.smimage_url,goods_image.dimage_url,
                               goods_image.simage_url,goods_image.image_id, f.file_id',
        ));
        if ($uploadedfile)
        {
            $this->_uploadedfile_mod->drop($id);
            if ($this->_image_mod->drop($uploadedfile['image_id']))
            {
                // ɾ���ļ�
                if (file_exists(ROOT_PATH . '/' . $uploadedfile['image_url']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['image_url']);
                }
                if (file_exists(ROOT_PATH . '/' . $uploadedfile['thumbnail']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['thumbnail']);
                }
                if ($uploadedfile['yimage_url'])
                {
	                if (file_exists(ROOT_PATH . '/' . $uploadedfile['yimage_url']))
	                {
	                       @unlink(ROOT_PATH . '/' . $uploadedfile['yimage_url']);
	                }
                }
            	if (file_exists(ROOT_PATH . '/' . $uploadedfile['mimage_url']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['mimage_url']);
                }
            	if (file_exists(ROOT_PATH . '/' . $uploadedfile['smimage_url']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['smimage_url']);
                }
            	if (file_exists(ROOT_PATH . '/' . $uploadedfile['dimage_url']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['dimage_url']);
                }
            	if (file_exists(ROOT_PATH . '/' . $uploadedfile['simage_url']))
                {
                       @unlink(ROOT_PATH . '/' . $uploadedfile['simage_url']);
                }
                $this->json_result($id);
                return;
            }
            $this->json_result($id);
            return;
        }
        $this->json_error(Lang::get('no_image_droped'));
    }
    
	/* �����Ʒ���ࣺ��ӡ��༭��Ʒ����֤ʱ�õ� */
    function check_mgcate()
    {
        $cate_id = isset($_GET['cate_id']) ? intval($_GET['cate_id']) : 0;

        echo ecm_json_encode($this->_check_mgcate($cate_id));
    }
    
    /**
     * �����Ʒ���ࣨ��ѡ������Ҷ�ӽ�㣩
     *
     * @param   int     $cate_id    ��Ʒ����id
     * @return  bool
     */
    function _check_mgcate($cate_id)
    {
        if ($cate_id > 0)
        {
            $info = $this->_gcategory_mod->get_info($cate_id);
            if ($info && $info['if_show'] && $this->_gcategory_mod->is_leaf($cate_id))
            {
                return true;
            }
        }

        return false;
    }
    
    /**
     * �ύ������
     */
    function _get_post_data($id = 0)
    {
    	$cs = new ChineseSpell();
        $goods = array(
            'goods_name'    => $_POST['goods_name'],
            'old_name'      => $_POST['old_name'],
        	'description'   => $_POST['description'],
            'cate_id'       => $_POST['cate_id'],
            'cate_name'     => $_POST['cate_name'],
            'brand_id'      => intval($_POST['brand']),
            'brand'      	=> $_POST['brand_name'],
            'if_show'       => $_POST['if_show'],
        	'buy_name'		=> $_POST['buy_name'],
            'last_update'   => gmtime(),
        	'status'		=> '0',
            'is_best'      	=> $_POST['best'],
            'tags'          => trim($_POST['tags']),
        	'is_hot'      		=> $_POST['hot'],
        	'is_new'      		=> $_POST['new'],
        	'autotrophy'		=> $_POST['autotrophy'],
        	'unit'      		=> $_POST['unit'],
            'region_id'   => intval($_POST['region']),
        	'region_name' => $_POST['region_name'],
        	'first_letter' => $cs->getFirstLetter(trim($_POST['goods_name'])),
        	'full_spell' => $cs->getFullSpell(trim($_POST['goods_name'])),
        );
        if ($goods['brand_id'])
        {
            $brand_mod  = & m('brand');
            $brand = $brand_mod->get($goods['brand_id']);
            $goods['brand'] = $brand['brand_name'];
        }
        if ($goods['region_id'])
        {
        	$region_mod = & m('region');
        	$region = $region_mod->get($goods['region_id']);
            $goods['region_name'] = $region['region_name'];
        }
        //ȡ����Ʒѡ��Ĺ�Ӧ��
        $supply_info = array(); //��ʼ����Ϣ
        if ($_POST['cod_supplys'])
        {
	        foreach ($_POST['cod_supplys'] as $supply_id => $supply_name)
	        {
	        	$supply_info[$supply_id]['supply_id'] = $supply_id;
	        	$supply_info[$supply_id]['supply_name'] = $supply_name;
	        }
        }
        
    	$spec_name_1 = !empty($_POST['spec_name_1']) ? $_POST['spec_name_1'] : '';
        $spec_name_2 = !empty($_POST['spec_name_2']) ? $_POST['spec_name_2'] : '';
        if ($spec_name_1 && $spec_name_2)
        {
            $goods['spec_qty'] = 2;
        }
        elseif ($spec_name_1 || $spec_name_2)
        {
            $goods['spec_qty'] = 1;
        }
        else
        {
            $goods['spec_qty'] = 0;
        }
        $goods_file_id = array();
        $desc_file_id =array();
        if (isset($_POST['goods_file_id']))
        {
            $goods_file_id = $_POST['goods_file_id'];
        }
        if (isset($_POST['desc_file_id']))
        {
            $desc_file_id = $_POST['desc_file_id'];
        }
        if ($id <= 0)
        {
            $goods['type'] = 'material';
            $goods['closed'] = 0;
            $goods['add_time'] = gmtime();
        }

    	$specs = array(); // ԭʼ���
        switch ($goods['spec_qty'])
        {
            case 0: // û�й��
            	$is_start = empty($_POST['is_start'][0]) ? 0 : $this->_filter_price($_POST['is_start'][0]);
            	$credits = (floatval($_POST['price'][0]) - floatval($_POST['profit'][0]) - floatval($_POST['gprice'][0]))*0.1;
	            $credit  = floor($credits*100)/100; //ȡ������PL,ȡС�����2λ���������������С����
	            $rebate  = floor($credit*0.5*100)/100; //�Ź�������
	            $zprices = floatval($_POST['gprice'][0]) + floatval($_POST['profit'][0]) + $credit + $rebate;
	            $zprice  = floor($zprices*100)/100; //ȡ��ר�������,ȡС�����2λ���������������С����
				$logistics_num = empty($_POST['logistics'][0]) ? 1 :intval($_POST['logistics'][0]);
	            if($credit < 0)
	           	{
	           		$this->_error('������-���ҽ�����-������С��0');
	           		return;
	           		break;
	           	}
        	    if($zprice > $_POST['price'][0])
	           	{
	           		$this->_error('ר������۲��ܴ���������');
	           		return;
	           		break;
	           	}
	           	if($specs[intval($_POST['spec_id'])])
	           	{
	                $specs[intval($_POST['spec_id'])] = array(
	                     'price'     => $this->_filter_price($_POST['price'][0]),
		                 'gprice'    => $this->_filter_price($_POST['gprice'][0]),
		               	 'profit'    => $this->_filter_price($_POST['profit'][0]),
		                 'weight'     	=> floatval($_POST['weight'][0]),						
		                 'commodity_code'       => trim($_POST['barcode'][0]),
		                 'zprice'	=> $zprice,
		               	 'credit'  	=> $credit,
	                	 'is_start' => $is_start,
	                	 'logistics_num' => $logistics_num,
	                );
	           	}else {
	           		$specs[0] = array(
	           		     'spec_id' => intval($_POST['spec_id'][0]),
                         'price'     => $this->_filter_price($_POST['price'][0]),
		                 'gprice'    => $this->_filter_price($_POST['gprice'][0]),
		               	 'profit'    => $this->_filter_price($_POST['profit'][0]),
		                 'weight'     	=> floatval($_POST['weight'][0]),						
		                 'commodity_code'       => trim($_POST['barcode'][0]),
		                 'zprice'	=> $zprice,
		               	 'credit'  	=> $credit,
	                	 'is_start' => $is_start,
	           			 'logistics_num' => $logistics_num,
                     );
	           	}
                
                break;
            case 1: // һ�����
                $goods['spec_name_1'] = $spec_name_1 ? $spec_name_1 : $spec_name_2;
                $goods['spec_name_2'] = '';
                $spec_data = $spec_name_1 ? $_POST['spec_1'] : $_POST['spec_2'];
                $spec_ids = array_unique($_POST['spec_id']);
                foreach ($spec_data as $key => $spec_1)
                {
                	$is_start = empty($_POST['is_start'][$key]) ? 0 : $this->_filter_price($_POST['is_start'][$key]);
                	$credits = (floatval($_POST['price'][$key]) - floatval($_POST['profit'][$key]) - floatval($_POST['gprice'][$key]))*0.1;
		            $credit  = floor($credits*100)/100; //ȡ������PL,ȡС�����2λ���������������С����
		            $rebate  = floor($credit*0.5*100)/100; //�Ź�������
		            $zprices = floatval($_POST['gprice'][$key]) + floatval($_POST['profit'][$key]) + $credit + $rebate;
		            $zprice  = floor($zprices*100)/100; //ȡ��ר�������,ȡС�����2λ���������������С����
                    $spec_1 = trim($spec_1);
                    $logistics_num = empty($_POST['logistics'][$key]) ? 1 :intval($_POST['logistics'][$key]);
	                if($credit < 0)
		           	{
		           		$this->_error('������-���ҽ�����-������С��0');
		           		return ;
		           		break;
		           	}
	        	    if($zprice > $_POST['price'][$key])
		           	{
		           		$this->_error('ר������۲��ܴ���������');
		           		return ;
		           		break;
		           	}
                    if ($spec_1)
                    {
                        if (($spec_id = intval($spec_ids[$key]))) // ���й��ID��
                        {
                            $specs[$key] = array(
                                 'spec_id' => $spec_id,
                                 'spec_1' => $spec_1,
				                 'price'     => $this->_filter_price($_POST['price'][$key]),
				                 'gprice'    => $this->_filter_price($_POST['gprice'][$key]),
				               	 'profit'    => $this->_filter_price($_POST['profit'][$key]),
				                 'weight'     	=> floatval($_POST['weight'][$key]),						
				                 'commodity_code'       => trim($_POST['barcode'][$key]),
				                 'zprice'	=> $zprice,
				               	 'credit'  	=> $credit,
                            	 'is_start' => $is_start,
                            	 'logistics_num' => $logistics_num,
                            );
                        }
                        else  // �����Ĺ��
                        {
                            $specs[$key] = array(
                                 'spec_1' => $spec_1,
				                 'price'     => $this->_filter_price($_POST['price'][$key]),
				                 'gprice'    => $this->_filter_price($_POST['gprice'][$key]),
				               	 'profit'    => $this->_filter_price($_POST['profit'][$key]),
				                 'weight'     	=> floatval($_POST['weight'][$key]),						
				                 'commodity_code'       => trim($_POST['barcode'][$key]),
				                 'zprice'	=> $zprice,
				               	 'credit'  	=> $credit,
                            	 'is_start' => $is_start,
                            	 'logistics_num' => $logistics_num,
                            );
                        }

                    }
                }
                break;
            case 2: // �������
                $goods['spec_name_1'] = $spec_name_1;
                $goods['spec_name_2'] = $spec_name_2;
                $spec_ids = array_unique($_POST['spec_id']);
                foreach ($_POST['spec_1'] as $key => $spec_1)
                {
                	$is_start = empty($_POST['is_start'][key]) ? 0 : $this->_filter_price($_POST['is_start'][key]);
                	$credits = (floatval($_POST['price'][$key]) - floatval($_POST['profit'][$key]) - floatval($_POST['gprice'][$key]))*0.1;
		            $credit  = floor($credits*100)/100; //ȡ������PL,ȡС�����2λ���������������С����
		            $rebate  = floor($credit*0.5*100)/100; //�Ź�������
		            $zprices = floatval($_POST['gprice'][$key]) + floatval($_POST['profit'][$key]) + $credit + $rebate;
		            $zprice  = floor($zprices*100)/100; //ȡ��ר�������,ȡС�����2λ���������������С����
                    $spec_1 = trim($spec_1);
                    $spec_2 = trim($_POST['spec_2'][$key]);
                    $logistics_num = empty($_POST['logistics'][$key]) ? 1 :intval($_POST['logistics'][$key]);
                	if($credit < 0)
		           	{
		           		$this->_error('������-���ҽ�����-������С��0');
		           		return false;
		           		break;
		           	}
	        	    if($zprice > $_POST['price'][$key])
		           	{
		           		$this->_error('ר������۲��ܴ���������');
		           		return ;
		           		break;
		           	}
                    if ($spec_1 && $spec_2)
                    {
                    	$is_start = empty($_POST['is_start'][$key]) ? 0 : $this->_filter_price($_POST['is_start'][$key]);
                        if (($spec_id = intval($spec_ids[$key]))) // ���й��ID��
                        {
                            $specs[$key] = array(
                                 'spec_id'   => $spec_id,
                                 'spec_1'    => $spec_1,
                                 'spec_2'    => $spec_2,
				                 'price'     => $this->_filter_price($_POST['price'][$key]),
				                 'gprice'    => $this->_filter_price($_POST['gprice'][$key]),
				               	 'profit'    => $this->_filter_price($_POST['profit'][$key]),
				                 'weight'     	=> floatval($_POST['weight'][$key]),						
				                 'commodity_code'       => trim($_POST['barcode'][$key]),
				                 'zprice'	=> $zprice,
				               	 'credit'  	=> $credit,
                            	 'is_start'	=> $is_start,
                            	 'logistics_num' => $logistics_num,
                            );
                        }
                        else // �����Ĺ��
                        {
                            $specs[$key] = array(
                                 'spec_1'    => $spec_1,
                                 'spec_2'    => $spec_2,
				                 'price'     => $this->_filter_price($_POST['price'][$key]),
				                 'gprice'    => $this->_filter_price($_POST['gprice'][$key]),
				               	 'profit'    => $this->_filter_price($_POST['profit'][$key]),
				                 'weight'     	=> floatval($_POST['weight'][$key]),						
				                 'commodity_code' => trim($_POST['barcode'][$key]),
				                 'zprice'	=> $zprice,
				               	 'credit'  	=> $credit,
                            	 'is_start' => $is_start,
                            	 'logistics_num' => $logistics_num,
                            );
                        }
                    }
                }
                break;
            default:
                break;
        }
        return array('goods' => $goods, 'specs' => $specs, 'goods_file_id' => $goods_file_id, 'desc_file_id' => $desc_file_id ,'supply' => $supply_info);
    }
    /**
     * ����ύ������
     */
    function _check_post_data($data, $id = 0)
    {
    	
    	if (!$this->_check_mgcate($data['goods']['cate_id']))
        {
            $this->_error('select_leaf_category');
            return;
        }
        if (!$this->_goods_mod->unique(trim($data['goods']['goods_name']), $id))
        {
            $this->_error('name_exist');
            return false;
        }
    
    	if(empty($_POST['goods_name']))
    	{
    		$this->_error(lang::get('plase_goods_name'));
   			return false;	
   		}
        if(empty($_POST['buy_name']))
    	{
    		$this->_error(lang::get('plase_buy_name'));
   			return false;	
   		}

        if ($data['goods']['spec_qty'] == 1 && empty($data['goods']['spec_name_1'])
                  || $data['goods']['spec_qty'] == 2 && (empty($data['goods']['spec_name_1']) || empty($data['goods']['spec_name_2'])))
        {
            $this->_error('fill_spec_name');
            return false;
        }
        if (empty($data['specs']))
        {
            $this->_error('fill_spec');
            return false;
        }
        
    	foreach ($data['specs'] as $k=>$v)
        {
        	$spec_price = $this->_filter_price($v['price']);
	        if (!is_numeric($spec_price)||!$spec_price)
	        {
	            $this->_error('price_error');
	            return false;
	        }
        	if (empty($v['commodity_code']))
	        {
	            $this->_error('commodity_code_error');
	            return false;
	        }
	        $info = $this->_spec_mod->find("commodity_code='".$v['commodity_code']."'");
	        if ($v['spec_id'])
	        {
	        	$sp = $this->_spec_mod->get($v['spec_id']);
	        	if ($sp['commodity_code']!=$v['commodity_code'])
	        	{
			        if (count($info)>0)
			        {
			        	$this->_error('commodity_code_exist');
			            return false;
			        }
	        	}
	        }else
	        {
	        	if (count($info)>0)
		        {
		        	$this->_error('commodity_code_exist');
		            return false;
		        }
	        }
        }
        return true;
    }
    function _save_post_data($data, $id = 0)
    {
        if ($data['goods']['tags'])
        {
            $data['goods']['tags'] = $this->_format_goods_tags($data['goods']['tags']);
        }  
        /* ������Ʒ */
        if ($id > 0)
        {
            // edit
            if (!$this->_goods_mod->edit($id, $data['goods']))
            {
                $this->_error($this->_goods_mod->get_error());
                return false;
            }
            $goods_id = $id;
        }
        else
        {
            // add
            $goods_id = $this->_goods_mod->add($data['goods']);
            if (!$goods_id)
            {
                $this->_error($this->_goods_mod->get_error());
                return false;
            }
            if (($data['goods_file_id'] || $data['desc_file_id'] ))
            {
                $uploadfiles = array_merge($data['goods_file_id'], $data['desc_file_id']);
                $this->_uploadedfile_mod->edit(db_create_in($uploadfiles, 'file_id'), array('item_id' => $goods_id));
            }
            if (!empty($data['goods_file_id']))
            {
                $this->_image_mod->edit(db_create_in($data['goods_file_id'], 'file_id'), array('goods_id' => $goods_id));
            }
        }
        /* ������ */
        /*
        if ($id > 0)
        {
           	//ɾ���Ĺ�� 
            $goods_specs = $this->_spec_mod->find(array(
                'conditions' => "goods_id = '{$id}'",
                'fields' => 'spec_id'
            ));
            $drop_spec_ids = array_diff(array_keys($goods_specs), array_keys($data['specs']));
            if (!empty($drop_spec_ids))
            {
                $this->_spec_mod->drop($drop_spec_ids);
            }
        }
		*/
        $goods_specs = $this->_spec_mod->find(array(
             'conditions' => "goods_id = '{$id}'",
             'fields' => 'spec_id'
            ));
        $spec_ids = array_diff(array_keys($goods_specs), array_keys($data['specs']));
        $default_spec = array(); // ��ʼ��Ĭ�Ϲ��
        
        if (count($data['specs']) == 1 )
        {
        	
        	$spec = $data['specs'][0];
            if (empty($spec_ids[0]))
            {
            	$spec['goods_id'] = $goods_id;
                $spec_id = $this->_spec_mod->add($spec);
            }else{	
	            $this->_spec_mod->edit($spec_ids[0],$spec);
	            $spec_id = $spec['spec_id'];
            }
            $default_spec = array('default_spec' => $spec_id,'price' => $spec['price'] ,'zprice' => $spec['zprice'], 'gprice' => $spec['gprice'] ,'credit' => $spec['credit']);
        }else 
        {
	        foreach ($data['specs'] as $key => $spec)
	        {
	            if ($spec_ids[$key] = $spec['spec_id']) // �������й��ID
	            {
	                $this->_spec_mod->edit($spec_ids[$key],$spec);
	                $spid = $spec_ids[$key];
	            }
	            else // �¼ӹ��ID
	            {
	                $spec['goods_id'] = $goods_id;
	                $spid = $this->_spec_mod->add($spec);
	            }
	            if (empty($default_spec))
	            {
	                $default_spec = array('default_spec' => $spid,'price' => $spec['price'] ,'zprice' => $spec['zprice'], 'gprice' => $spec['gprice'] ,'credit' => $spec['credit']);
	            }
	        }
        }
        /* ����Ĭ�Ϲ�� */
        $this->_goods_mod->edit($goods_id, $default_spec);
        if ($this->_goods_mod->has_error())
        {
            $this->_error($this->_goods_mod->get_error());
            return false;
        }

        /* ���湩Ӧ����Ʒ���� */
        if ($data['supply'])
        {
        	//ɾ���Ĺ�Ӧ����Ʒ
            $goods_supply = $this->_supply_goods_mod->find(array(
                'conditions' => "goods_id = '{$goods_id}'",
                'fields' => 'id'
            ));
			foreach ($goods_supply as $k=>$v)
			{
				$this->_supply_goods_mod->drop($v['id']);
			}
        	foreach ($data['supply'] as $k=>$v)
        	{
        		$sudata = array(
        			'supply_id' => $v['supply_id'],
        			'goods_id' 	=> $goods_id
        		);
        		$this->_supply_goods_mod->add($sudata);	
        	}	
        }
        

        /* ����Ĭ��ͼƬ */
        if (isset($data['goods_file_id'][0]))
        {
            $default_image = $this->_image_mod->get(array(
                'fields' => 'thumbnail,yimage_url,mimage_url,smimage_url,dimage_url,simage_url',
                'conditions' => "goods_id = '$goods_id' AND file_id = '{$data[goods_file_id][0]}'",
            ));
            $this->_image_mod->edit("goods_id = $goods_id", array('sort_order' => 255));
            $this->_image_mod->edit("goods_id = $goods_id AND file_id = '{$data[goods_file_id][0]}'", array('sort_order' => 1));
        }

        $this->_goods_mod->edit($goods_id, array(
            'default_image' => $default_image ? $default_image['thumbnail'] : '',
        	'yimage_url'    => $default_image ? $default_image['yimage_url'] : '',
            'mimage_url'    => $default_image ? $default_image['mimage_url'] : '',
        	'smimage_url'   => $default_image ? $default_image['smimage_url'] : '',
        	'dimage_url'    => $default_image ? $default_image['dimage_url'] : '',
            'simage_url'    => $default_image ? $default_image['simage_url'] : '',
        ));

        $this->_last_update_id = $goods_id;

        return true;
    }
    function _format_goods_tags($tags)
    {
        if (!$tags)
        {
            return '';
        }
        $tags = explode(',', str_replace(Lang::get('comma'), ',', $tags));
        array_walk($tags, create_function('&$item, $key', '$item=trim($item);'));
        $tags = array_filter($tags);
        $tmp = implode(',', $tags);
        if (strlen($tmp) > 100)
        {
            $tmp = sub_str($tmp, 100, false);
        }

        return ',' . $tmp . ',';
    }

	/* �۸���ˣ����طǸ������� */
    function _filter_price($price)
    {
        return abs(floatval($price));
    }
    /*****
     * ͨ��JSON�����Ƿ��ܹرո���Ʒ���
     * 
     *****/
    function is_start()
    {
        $spec_id = empty($_GET['spec_id']) ? '0' : trim($_GET['spec_id']);

        $store_goods_mod =& m('storegoods');
        $sg_info = $store_goods_mod->getAll("select * from pa_store_goods where spec_id=".$spec_id);
        if (!$sg_info)
        {
        	$this->json_result('true');
        }else {
        	$this->json_error('�˹����Ʒ���ѽ������ܹر�');
        }
    }
    //�������������
	function create_commodity_code() {
		/* ѡ��һ������ķ��� */
	    mt_srand((double) microtime() * 1000000);
	    return  '9'.time(). str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
	}
	/****
	 *	ͨ��JSON���������� 
	 * 
	 ****/
	function commodity_code()
	{
		$commoditycode = $this->create_commodity_code();
		$msg = $this->_spec_mod->getRow('select * from pa_goods_spec where commodity_code='.$commoditycode);
		if($msg)
		{
			$this->json_result($commoditycode);
		}else {
			$commoditycode = $this->create_commodity_code();
			$this->json_result($commoditycode);
		}		
	}
	//ɾ����Ʒ����
	function drop()
    {
    	exit;
    	$user_id = $this->visitor->get('user_id');
    	if ($user_id!=1 && $user_id!=641)
    	{
    		$this->show_warning('�Ƿ�������');
            return;
    	}
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_goods_to_drop');
            return;
        }

        $ids = explode(',', $id);
        $this->_goods_mod->drop_data($ids);
        $rows = $this->_goods_mod->drop($ids);
        if ($this->_goods_mod->has_error())
        {
            $this->show_warning($this->_goods_mod->get_error());
            return;
        }

        $this->show_message('drop_ok');
    }
    function searchsupply()
    {
    	$q = iconv("UTF-8","GBK",strtolower($_GET["term"]));
		$query = $this->_supply_mod->getAll("select supply_id,supply_name from pa_supply where supply_name like '%$q%' limit 0,10");
		if($query)
		{
			foreach ($query as $key => $val){
				$result[] = array(
				    'id' => $val['supply_id'],
				    'label' => iconv("GBK","UTF-8",$val['supply_name'])
				);		
			}
		}
		echo json_encode($result);
    }
    /**
     *���������ʾ
      @author wscsky 
     */
    function import_help(){
        $this->display("goods.import_help.html"); 
    }
     
     
    /**
     * ��Ʒ��������
      @author wscsky  
    */
    function import(){
        if(!IS_POST){
            $this->display("goods.import.html");
            return;
        }
        $file = $_FILES['csv'];
        if ($file['error'] != UPLOAD_ERR_OK)
        {
            $this->show_warning('����ѡ��Ҫ�����excel�ļ�!');
            return;
        }
        if (strtolower($file['name']) == basename(strtolower($file['name']),'.xls'))
            {
                $this->show_warning('�������ʽΪ<b>xls</b>��excel�ļ�!');
                return;
            }
            
        import(PHPExcel);
        $PHPExcel = new PHPExcel();
        $PHPReader = new PHPExcel_Reader_Excel2007();
                
        if(!$PHPReader->canRead($file['tmp_name'])){
	        $PHPReader = new PHPExcel_Reader_Excel5();
	        if(!$PHPReader->canRead($file['tmp_name'])){
	            $this->show_warning('���ϴ��Ĳ���excel��ʽ�ļ�!');
	            return ;
	        }
	    }
	    $PHPExcel = $PHPReader->load($file['tmp_name']);
	    $currentSheet = $PHPExcel->getSheet(0);       
	    $allColumn = $currentSheet->getHighestColumn();
	    $allRow = $currentSheet->getHighestRow();         
        $data = array();
        $cdata = array(
            "err"   => 0,
            "msg"   => "",
            "A"     => "",
            "B"     => "",
            "C"     => "",
            "D"     => "",
            "E"     => "",
            "F"     => "",
            "G"     => "",
            "H"     => "",
            "I"     => "",
            "J"     => "",            
            "K"     => "",
            "L"     => "",
            "M"     => "",
            "N"     => "",
            "O"     => "",
            "P"     => "",
            "Q"     => "",
            "R"     => "",
            "S"     => ""
          );
	    for($currentRow = 2;$currentRow<=$allRow;$currentRow++){
	        for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
	            $address = $currentColumn.$currentRow;
                $v= $currentSheet->getCell($address)->getValue();
                $cdata[$currentColumn] = iconv($_POST['charset'],"gbk", $v);
	        }
            $data[]=$cdata;
	     }
         $okdata=array();
         $erdata=array();
         //��������
         foreach($data as $k=>&$v){
                //��������֤
                $v["K"] = trim($v["K"]);
                if(empty($v["K"])){
                    //$v["K"] = $this->create_commodity_code();                    
                }
                else{
                    if(strlen($v["K"])!=13)
                        {
                            $v["err"]=1;
                            $v["msg"].="�����볤������";
                        }
                    else{
                          if(ctype_digit($v["K"])==false)
                            {
                            $v["err"]=1;
                            $v["msg"].="�����������ȫ������";                            
                            }     
                        } 
                       
                    }
                    
                //��������֤
                
                if(empty($v['E'])){
                    $v["err"]=1;
                    $v["msg"].="��Ʒ����Ϊ��;";                        
                }
                empty($v['F']) && $v['F']=$v['E'];
                empty($v['G']) && $v['G']=$v['E'];
                empty($v['H']) && $v['H']="ǧ��";

                //��������
                $cateid = $this->_get_cateid($v);
                $v["cate_id"] = $cateid[0];
                $v["cate_id_1"] = $cateid[1];
                $v["cate_id_2"] = $cateid[2];
                $v["cate_id_3"] = $cateid[3];
                $v["cate_id_4"] = $cateid[4];
                $v["cate_name"] = $v['A'];
                $v["cate_id_2"]>0 && $v["cate_name"] .= chr(9).$v['B'];
                $v["cate_id_3"]>0 && $v["cate_name"] .= chr(9).$v['C'];
                $v["cate_id_4"]>0 && $v["cate_name"] .= chr(9).$v['D'];
                
                //�������
                $v['spec'] = $this->_get_goods_spec($v);
                //��Ӧ��&Ʒ�ƴ���&����
                $v['supply_id']= intval($this->_get_supply_id($v['P']));
                $v['supply_id']==0 && $v['P']="";
                $v['brand_id'] = intval($this->_get_brand_id($v['Q']));
                $v['brand_id']==0 && $v['Q']="";
                $v['region_id']=intval($this->_get_region_id($v['R']));
                $v['region_id']==0 && $v['R']="";
                //���ִ���
                $v["J"]=floatval($v["J"]);
                $v["L"]=floatval($v["L"]);
                $v["M"]=floatval($v["M"]);
                $v["N"]=floatval($v["N"]);
                $v["O"]=intval($v["O"]);
                if($v["O"]==0){$v["O"]=1;}
                
                $credits = ($v['M'] - $v['N'] -$v['L'])*0.1;
	            $credit  = floor($credits*100)/100; //ȡ������PL,ȡС�����2λ���������������С����
	            $rebate  = floor($credit*0.5*100)/100; //�Ź�������
	            $zprices = $v['L'] + $v['N'] + $credit + $rebate;
	            $zprice  = floor($zprices*100)/100; //ȡ��ר�������,ȡС�����2λ���������������С����
                
	            if($credit < 0)
	           	{
	           	   $v["err"]=1;
                   $v["msg"].="������-���ҽ�����-������С��0;"; 
	           	}
        	    if($zprice > $v['M'])
	           	{
	           	   $v["err"]=1;
                   $v["msg"].="ר������۲��ܴ���������;";
        
	           	}
                $v["credit"]=$credit;
                $v["zprice"]=$zprice;
                
                //�������ݿ�
               $this->_import_goods_pro($v,$okdata,$erdata);
                
            }
            $this->assign('oknum',count($okdata));
            $this->assign('errnum',count($erdata));
            $this->assign('okgoods',$okdata);
            $this->assign('errgoods',$erdata);
            $this->display('goods.import_list.html');       
    }
    /**
     * �������ݴ�������
     * @author wscsky
     */
        function _import_goods_pro($arr,&$okdata,&$erdata){
            if($arr["err"]==1){
                   $erdata[]=$arr;             
            }else{                
                //�����봦��
                if(strlen($arr["K"])==13){
                        $num = $this->_get_commodity_num($arr["K"]);
                        if($num>0){
                            $arr["err"]=1;
                            $arr["msg"].="�������Ѵ���;";
                            $erdata[]=$arr;
                            return;               
                            }
                     }
                  else{
                     for($i=0;$i<1000;$i++){
                         $arr["K"] = $this->create_commodity_code();
                         $num = $this->_get_commodity_num($arr["K"]);
                         if($num==0){break;}
                        }                                       
                    }
                
                //�����봦��
                $cs = new ChineseSpell();
                $unitdata = require('../includes/libraries/unit_config.php');
                $unit = 0;
                for($i=0;$i<count($unitdata);$i++){
                    if($unitdata[$i]==$arr['H']){
                        $unit = $i;break;                        
                    }
                    
                }
                $arr['H'] = $unit;
                $goods_spec = array();
                $goods = array(
                        'goods_name'    => $arr['E'],
                        'old_name'      => $arr['F'],
                        'buy_name'      => $arr['G'],
                        'cate_id'       => $arr['cate_id'],
                        'cate_name'     => $arr['cate_name'],
                        'cate_id_1'     => $arr['cate_id_1'],
                        'cate_id_2'     => $arr['cate_id_2'],
                        'cate_id_3'     => $arr['cate_id_3'],
                        'cate_id_4'     => $arr['cate_id_4'],
                        'price'         => $arr['M'],
                        'gprice'        => $arr['L'],
                        'credit'        => $arr['credit'],
                        'zprice'        => $arr['zprice'],                       
                        'brand_id'      => $arr['brand_id'],
                        'brand'      	=> $arr['Q'],
                        'if_show'       => 1,
                        'last_update'   => gmtime(),
                        'add_time'      => gmtime(),
                    	'status'		=> 0,
                        'is_best'      	=> 0,
                        'tags'          => '',
                    	'is_hot'      	=> 0,
                    	'is_new'      	=> 0,
                    	'autotrophy'	=> 0,
                    	'unit'      	=> $arr['H'],
                        'region_id'     => $arr['region_id'],
                    	'region_name'   => $arr['R'],
                    	'first_letter'  => $cs->getFirstLetter(trim($arr['E'])),
                    	'full_spell'    => $cs->getFullSpell(trim($arr['E'])),
                        'spec_qty'      => 0,
                        'description'   => addslashes($arr['S'])
                    );
                  //���
                  if(is_array($arr["spec"])){
                     if(is_array($arr["spec"][0])){
                        if(count($arr["spec"][0])==2){
                            $goods['spec_name_1'] = $arr["spec"][0][0];
                            $goods_spec['spec_1'] = $arr["spec"][0][1];
                            $goods['spec_qty'] = 1;
                        }
                     }
                     if(is_array($arr["spec"][1])){
                       if(count($arr["spec"][1])==2){
                            $goods['spec_name_2'] = $arr["spec"][1][0];
                            $goods_spec['spec_2'] = $arr["spec"][1][1];
                            $goods['spec_qty'] = 2;
                        }
                     }       
                  }
                  $goods_id = $this->_goods_mod->add($goods);

                  if($goods_id){
                        $goods_spec['goods_id'] = $goods_id;
                        $goods_spec['price'] = $arr['M'];
                        $goods_spec['gprice'] = $arr['L'];
                        $goods_spec['profit'] = $arr['N'];
                        $goods_spec['credit'] = $arr['credit'];
                        $goods_spec['zprice'] = $arr['zprice'];                        
                        $goods_spec['commodity_code'] = $arr['K'];
                        $goods_spec['weight'] = $arr['J'];
                        $goods_spec['logistics_num'] = $arr['O'];                        
                        $arr['goods_id'] = $goods_id;
                        
                        $spec_id = $this->_spec_mod->add($goods_spec);
                        $this->_goods_mod->edit($goods_id,array("default_spec" => $spec_id));

                        $arr['supply_id']>0 && $this->_supply_goods_mod->add(array("goods_id"=>$goods_id,"supply_id"=>$arr['supply_id']));
                           
                        $okdata[] = $arr;
                    }
                    else{
                        $arr['err'] = 1;
                        $arr['msg'] .='���������ݱ�ʧ��;';
                        $erdata[]=$arr;                    
                    }
            }        
        }
     /**
      * ͨ����������ȡ����ID
      * @author wscksy
     */
      function _get_cateid($arr){        
          $cate = array(0,0,0,0,0);
          if($arr["err"]==1){
            return $cate;
          }
          $cate[1] = $this->_get_cateid_pro($arr["A"],0);
          if($cate[1]==0){return $cate;}
          $cate[2] = $this->_get_cateid_pro($arr["B"],$cate[1]);
          if($cate[2]==0){$cate[1]=0;return $cate;}
          $cate[3] = $this->_get_cateid_pro($arr["C"],$cate[2]);
          if($cate[3]==0){$cate[1]=0;$cate[2]=0;return $cate;}
          $cate[4] = $this->_get_cateid_pro($arr["D"],$cate[3]);
          if(!empty($arr["D"])&&$cate[4]==0){$cate[1]=0;$cate[2]=0;$cate[3]=0;return $cate;}        
          $cate[0]=$cate[3];
          $cate[4]>0 && $cate[0]=$cate[4]; 
          return $cate;
      }
      
      function _get_cateid_pro($cate_name,$parentid){
        $sql = "select cate_id from pa_gcategory where cate_name like '{$cate_name}' and parent_id={$parentid}";
        $cateid =$this->_gcategory_mod->getOne($sql);
        if(!$cateid){$cateid=0;}
        return $cateid;       
      }
      //�ù�Ӧ�����ֲ�ID
      function _get_supply_id($str){
        $num = 0;
        if($str){
            $sql = "select supply_id from pa_supply where supply_name like '{$str}'";
            $num = $this->_spec_mod->getOne($sql);
            if(!$num){$num=0;}
        }
        return $num;        
      }
      //��Ʒ�����ֲ�Ʒ��
      function _get_brand_id($str){
        $num = 0;
        if($str){
            $sql = "select brand_id from pa_brand where brand_name like '{$str}'";
            $num = $this->_get_brand_mod->getOne($sql);
            if(!$num){$num=0;}
        }
        return $num;          
      }
      //��ȡʡ��ID
      function _get_region_id($str){
        $num = 0;
        if($str){
            $region_mod = & m('region');
            $sql = "SELECT region_id from pa_region where parent_id=0 and region_name like '{$str}'";
            $num = $region_mod->getOne($sql);
            if(!$num){$num=0;}
        }
        return $num;     
      }
      /**
       * ����������
       * @author wscsky
       * return $num ��¼����
       */
      function _get_commodity_num($str){
        $num = 0;
        if($str){
            $sql = "select count(0) from pa_goods_spec where commodity_code like '{$str}'";
            $num = $this->_spec_mod->getOne($sql);
        }        
        return $num;
      }
      /**
       *�����������й��
       * @author wscsky
      */
      function _get_goods_spec($arr){
        if($arr["err"]==1){
            return(0);
          }
        $da = explode("$",$arr["I"]);
         foreach($da as $k=>&$v){
            $v=explode("&",$v);
        }
        return $da;                
      }
    
}

?>
