<?php

define('SMALL_WIDTH', 80);
define('SMALL_HEIGHT', 80);
define('DEFAULT_WIDTH', 120);
define('DEFAULT_HEIGHT', 120);
define('SMIDDLE_WIDTH', 160);
define('SMIDDLE_HEIGHT', 160);
define('MIDDLE_WIDTH', 220);
define('MIDDLE_HEIGHT', 220);
define('BIG_WIDTH', 350);
define('BIG_HEIGHT', 350);
define('YWIDTH', 750);
define('YHEIGHT', 750);
define('THUMB_QUALITY', 85);

/**
 *    swfupload�����ϴ�������
 *
 *    @author    Hyber
 *    @usage    none
 */

class SwfuploadApp extends BackendApp
{
    var $belong; // �ϴ����ļ�������ģ��
    var $mod_uploadedfile; //�ϴ��ļ�ģ��
    var $mod_goods; //��Ʒģ��
    var $mod_goods_image; //��Ʒ���ģ��
    var $item_id = 0; // ����ģ�͵�ID
    var $save_path; // ����·��
    var $store_id; // ����ID
    var $instance = null; //ʵ������ͬʱ��Ʒ����������ʵ��:����������
    var $user_id = 0;
    function __construct()
    {
        $this->SwfuploadApp();
    }
    function SwfuploadApp()
    {
        /* �����Ự */
        if (isset($_POST["PL_ID"]) && isset($_POST['HTTP_USER_AGENT']))
        {
            $_COOKIE['PL_ID'] = $_POST["PL_ID"];
            $_SERVER['HTTP_USER_AGENT'] = $_POST['HTTP_USER_AGENT'];
        }
        else
        {
            $this->json_error('no_post_params_authorize');
            exit();
        }

        parent::__construct();

        /* ��ʼ�� */

        /* ���� */
        if (isset($_POST['belong']))
        {
            $this->belong = $_POST['belong'];
        }
        else
        {
            $this->json_error('no_post_param_belong');
            exit();
        }

        /* ��λ */
        if (isset($_POST['item_id']))
        {
            $this->item_id = $_POST['item_id'];
        }
        else
        {
            $this->json_error('no_post_param_item_id');
            exit();
        }

        /* ʵ�� */
        if (isset($_GET['instance']))
        {
            $this->instance = $_GET['instance'];
        }
        
    	if (isset($_REQUEST['user_id']))
        {
            $this->user_id = $_REQUEST['user_id'];
        }

        $this->store_id = 0;
        switch ($this->belong)
        {
            case BELONG_ARTICLE :   $this->save_path = 'data/files/store_' . $this->store_id . '/article';
            break;
            case BELONG_STORE :     $this->save_path = 'data/files/store_' . $this->store_id . '/other';
            break;
            case BELONG_GOODS :     $this->save_path = 'data/files/goods/'.date('Y').'/'.date('m').'/goods_' . (time() % 200);
            break;
        }

        $this->mod_uploadedfile = &m('uploadedfile');
        $this->mod_goods = &m('goods');
        $this->mod_goods_image = &m('goodsimage');
    }
    function index()
    {
        $this->_upload_file();
    }
    function taobao_image()
    {
        $this->_upload_taobao_image();
    }
    function _upload_taobao_image()
    {
        $ret_info = array(); // ���ص��ͻ��˵���Ϣ
        $file = $_FILES['Filedata'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // û���ļ����ϴ�
        {
            $this->json_error('no_upload_file');
            exit();
        }

        $file['filename'] = substr($file['name'], 0, strpos($file['name'], '.'));

        /* ȡ����Ʒ��Ϣ����Ƿ��и�ͼƬ�ļ�¼ */

        //$find_goods = $this->mod_goods->get("default_image='{$file['filename']}'");
        $find_goods = $this->mod_goods->find(array(
            'conditions' => "default_image LIKE '%" . $file['filename'] . ";%' AND store_id=" . $this->store_id,
        ));

        if (!$find_goods)
        {
            $this->json_error(array(
                array('msg'=>'db_no_such_image', 'obj'=>$file['name']),
            ));
            exit();
        }

        /* ����mime���ͻ�ԭ��ʵͼƬ�� */
        $file['imagesize'] = @getimagesize($file['tmp_name']); // ���ڷ�ͼƬ�ļ����ܻ�������
        $file['mime'] = $file['imagesize']['mime'];
        $file['extension'] = substr($file['mime'], strpos($file['mime'], '/')+1); // ��ʵ��׺
        $file['name'] = $file['filename'] . '.' . $file['extension'];

        import('SimpleImage.class'); //����ͼƬ������
        import('uploader.lib'); //�����ϴ���
        $uploader = new Uploader();
        $simpleimage = new SimpleImage();
        $uploader->allowed_type(IMAGE_FILE_TYPE); // �����ļ�����
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // ���Ƶ����ļ���С2M
        $uploader->addFile($file);
        if (!$uploader->file_info())
        {
            $this->json_error($uploader->get_error());
            exit();
        }
        foreach ($find_goods as $key =>$goods)
        {
            static $uploaded_file = NULL;
            /* ȡ��ʣ��ռ䣨��λ���ֽڣ���false��ʾ������ */
//            $store_mod  =& m('store');
//            $settings   = $store_mod->get_settings($this->store_id);
//            $remain     = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $this->mod_uploadedfile->get_file_size($this->store_id) : false;
			$remain = false;
            /* �ж��ܷ��ϴ� */
            if ($remain !== false)
            {
                if ($remain < $file['size'])
                {
                    $this->json_error('space_limit_arrived');
                    exit();
                }
            }

             /* ָ������λ�õĸ�Ŀ¼ */
            $uploader->root_dir(ROOT_PATH);
            $filename  = $uploader->random_filename();

            if ($uploaded_file ===NULL)
            {
                /* �ϴ� */
                $uploaded_file = $file_path = $uploader->save($this->save_path, $filename); // ���浽ָ��Ŀ¼
                if (!$file_path)
                {
                    $this->json_error('file_save_error');
                    exit();
                }
            }
            else
            {
                $this->save_path = 'data/files/goods/'.date('Y').'/'.date('m').'/goods_' . (time() % 200);
                $file_content = file_get_contents(ROOT_PATH. '/' . $uploaded_file);
                $file_path = $this->save_path . '/' . $filename . '.' . $file['extension'];
                file_put_contents($file_path, $file_content);
            }

            /* ������� */
            $data = array(
                'store_id'  => $this->store_id,
                'file_type' => $file['mime'],
                'file_size' => $file['size'],
                'file_name' => $file['name'],
                'file_path' => $file_path,
                'belong'    => $this->belong,
                'item_id'   => $goods['goods_id'],
                'add_time'  => gmtime(),
                'user_id'   => $this->user_id,
            );
            $file_id = $this->mod_uploadedfile->add($data);
            if (!$file_id)
            {
                $this->json_error('file_add_error');
                exit();
            }

            /* ��������ͼ */
            $small = dirname($file_path) . '/80x80_' . basename($file_path);
            $default = dirname($file_path) . '/120x120_' . basename($file_path);
            $smiddle = dirname($file_path) . '/160x160_' . basename($file_path);
            $middle = dirname($file_path) . '/220x220_' . basename($file_path);
            $big = dirname($file_path) . '/350x350_' . basename($file_path);
            $yimg = dirname($file_path) . '/750x750_' . basename($file_path);
	                
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $small, SMALL_WIDTH, SMALL_HEIGHT);      //80*80
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $default, DEFAULT_WIDTH, DEFAULT_HEIGHT);//120*120
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $smiddle, SMIDDLE_WIDTH, SMIDDLE_HEIGHT); //160*160
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $middle, MIDDLE_WIDTH, MIDDLE_HEIGHT);   //220*220
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $big, BIG_WIDTH, BIG_HEIGHT);  //350*350           
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $yimg, YWIDTH, YHEIGHT); //750*750
			
			//�ж��Ƿ�������750*750��ͼ
			if (!file_exists(ROOT_PATH . '/' . $yimg))
			{
				$yimg = '';
			}
			//�޸�ԭͼ������
			$newname = reimgname($file_path,$this->save_path);

            /* ������Ʒ��� */
            $data = array(
                'goods_id'   => $goods['goods_id'],
                'image_url'  => $newname,
                'yimage_url' => $yimg,
                'thumbnail'  => $big,
                'mimage_url' => $middle,
                'smimage_url' => $smiddle,
                'dimage_url'  => $default,
                'simage_url'  => $small,
                'sort_order' => 255,
                'file_id'    => $file_id,
            );
            $this->mod_uploadedfile->edit($file_id,"file_path='".$newname."'"); //����ԭͼ������
            if (!$this->mod_goods_image->add($data))
            {
                $this->json_error($this->mod_goods_imaged->get_error());
                return false;
            }

            /* ������ƷĬ��ͼƬ */
            $remain_image = str_replace($file['filename'] . ';', '', $goods['default_image']);
            if ($remain_image) // default_image�ֶ����г���һ��ͼƬ
            {
                $this->mod_goods->edit($goods['goods_id'], array('default_image' => $remain_image));
            }
            else
            {
                $this->mod_goods->edit($goods['goods_id'], array('default_image' => $big));
            }
        }
        /* ���ؿͻ��� */
        $ret_info =array(
            'file_id'   => $file_id,
            'file_path' => $file_path
        );
        $this->json_result($ret_info);
    }
    function _upload_file()
    {
        $ret_info = array(); // ���ص��ͻ��˵���Ϣ
        $file = $_FILES['Filedata'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // û���ļ����ϴ�
        {
            $this->json_error('no_upload_file');
            exit();
        }
        import('SimpleImage.class'); //����ͼƬ������
        import('uploader.lib'); //�����ϴ���
        $uploader = new Uploader();
        $simpleimage = new SimpleImage();
        $uploader->allowed_type(IMAGE_FILE_TYPE); // �����ļ�����
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // ���Ƶ����ļ���С2M
        $uploader->addFile($file);
        if (!$uploader->file_info())
        {
            $this->json_error($uploader->get_error());
            exit();
        }

        /* ȡ��ʣ��ռ䣨��λ���ֽڣ���false��ʾ������ */
//        $store_mod  =& m('store');
//        $settings   = $store_mod->get_settings($this->store_id);
//        $remain     = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $this->mod_uploadedfile->get_file_size($this->store_id) : false;
		$remain = false;
        /* �ж��ܷ��ϴ� */
        if ($remain !== false)
        {
            if ($remain < $file['size'])
            {
                $this->json_error('space_limit_arrived');
                exit();
            }
        }

        /* ָ������λ�õĸ�Ŀ¼ */
        $uploader->root_dir(ROOT_PATH);
        $filename  = $uploader->random_filename();
        /* �ϴ� */
        $file_path = $uploader->save($this->save_path, $filename); // ���浽ָ��Ŀ¼
        if (!$file_path)
        {
            $this->json_error('file_save_error');
            exit();
        }
        
    	//�����ϴ���Ʒ����ͼƬʱ����ˮӡ����
        if ($this->instance == 'desc_image')
        {
            //��������ͼƬˮӡ
			$waterImage = ROOT_PATH . '/themes/mall/default/styles/default/images/pailashuiyin.png';
			$simpleimage->imageWaterMark(ROOT_PATH . '/' .$file_path,9,$waterImage,$waterText="",$textFont=5,$textColor="#FF0000",true);
        }
        
        $file_type = $this->_return_mimetype($file_path);
        /* �ļ���� */
        $data = array(
            'store_id'  => $this->store_id,
            'file_type' => $file_type,
            'file_size' => $file['size'],
            'file_name' => $file['name'],
            'file_path' => $file_path,
            'belong'    => $this->belong,
            'item_id'   => $this->item_id,
            'add_time'  => gmtime(),
            'user_id'   => $this->user_id,
        );
        $file_id = $this->mod_uploadedfile->add($data);
        if (!$file_id)
        {
            $this->json_error('file_add_error');
            exit();
        }

        if ($this->instance == 'goods_image') // ������ϴ���Ʒ���ͼƬ
        {
            /* ��������ͼ */
            $small = dirname($file_path) . '/80x80_' . basename($file_path);
            $default = dirname($file_path) . '/120x120_' . basename($file_path);
            $smiddle = dirname($file_path) . '/160x160_' . basename($file_path);
            $middle = dirname($file_path) . '/220x220_' . basename($file_path);
            $big = dirname($file_path) . '/350x350_' . basename($file_path);
            $yimg = dirname($file_path) . '/750x750_' . basename($file_path);
	                
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $small, SMALL_WIDTH, SMALL_HEIGHT);      //80*80
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $default, DEFAULT_WIDTH, DEFAULT_HEIGHT);//120*120
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $smiddle, SMIDDLE_WIDTH, SMIDDLE_HEIGHT); //160*160
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $middle, MIDDLE_WIDTH, MIDDLE_HEIGHT);   //220*220
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $big, BIG_WIDTH, BIG_HEIGHT);  //350*350           
			$simpleimage->make_thumb($file_path, ROOT_PATH . '/' . $yimg, YWIDTH, YHEIGHT); //750*750

			//�ж��Ƿ�������750*750��ͼ
			if (!file_exists(ROOT_PATH . '/' . $yimg))
			{
				$yimg = '';
			}
			//�޸�ԭͼ������
			$newname = reimgname($file_path,$this->save_path);
			
            /* ������Ʒ��� */
            $data = array(
                'goods_id'   => $this->item_id,
                'image_url'  => $newname,
                'yimage_url' => $yimg,
                'thumbnail'  => $big,
                'mimage_url' => $middle,
                'smimage_url' => $smiddle,
                'dimage_url'  => $default,
                'simage_url'  => $small,
                'sort_order' => 255,
                'file_id'    => $file_id,
            );
            $this->mod_uploadedfile->edit($file_id,"file_path='".$newname."'"); //����ԭͼ������
            if (!$this->mod_goods_image->add($data))
            {
                $this->json_error($this->mod_goods_imaged->get_error());
                return false;
            }
            $ret_info = array_merge($ret_info, array('thumbnail' => $big));
        }

        /* ���ؿͻ��� */
        $ret_info = array_merge($ret_info, array(
            'file_id'   => $file_id,
            'file_path' => $file_path,
            'instance'  => $this->instance,
        ));
        $this->json_result($ret_info);
    }

    function _return_mimetype($filename)
    {
        preg_match("|\.([a-z0-9]{2,4})$|i", $filename, $fileSuffix);
        switch(strtolower($fileSuffix[1]))
        {
            case "js" :
                return "application/x-javascript";

            case "json" :
                return "application/json";

            case "jpg" :
            case "jpeg" :
            case "jpe" :
                return "image/jpeg";

            case "png" :
            case "gif" :
            case "bmp" :
            case "tiff" :
                return "image/".strtolower($fileSuffix[1]);

            case "css" :
                return "text/css";

            case "xml" :
                return "application/xml";

            case "doc" :
            case "docx" :
                return "application/msword";

            case "xls" :
            case "xlt" :
            case "xlm" :
            case "xld" :
            case "xla" :
            case "xlc" :
            case "xlw" :
            case "xll" :
                return "application/vnd.ms-excel";

            case "ppt" :
            case "pps" :
                return "application/vnd.ms-powerpoint";

            case "rtf" :
                return "application/rtf";

            case "pdf" :
                return "application/pdf";

            case "html" :
            case "htm" :
            case "php" :
                return "text/html";

            case "txt" :
                return "text/plain";

            case "mpeg" :
            case "mpg" :
            case "mpe" :
                return "video/mpeg";

            case "mp3" :
                return "audio/mpeg3";

            case "wav" :
                return "audio/wav";

            case "aiff" :
            case "aif" :
                return "audio/aiff";

            case "avi" :
                return "video/msvideo";

            case "wmv" :
                return "video/x-ms-wmv";

            case "mov" :
                return "video/quicktime";

            case "rar" :
                return "application/x-rar-compressed";

            case "zip" :
            return "application/zip";

            case "tar" :
                return "application/x-tar";

            case "swf" :
                return "application/x-shockwave-flash";

            default :
            if(function_exists("mime_content_type"))
            {
                $fileSuffix = mime_content_type($filename);
            }
            return "unknown/" . trim($fileSuffix[0], ".");
        }
    }
}

?>