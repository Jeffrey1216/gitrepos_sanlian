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

class ComuploadApp extends BackendApp
{
    var $id = 0;
    var $belong = 0;
    var $store_id = 0;
    var $user_id = 0;
    var $instance = null; //ͬһ��ģ�Ϳ������ö����ͬʵ����goodsģ�Ϳ�������Ʒ������Ʒ��������ʵ����
    function __construct()
    {
        $this->ComuploadApp();
    }

    function ComuploadApp()
    {
        parent::__construct();
        if (isset($_REQUEST['id']))
        {
             $this->id = intval($_REQUEST['id']);
        }
        if (isset($_REQUEST['belong']))
        {
            $this->belong = intval($_REQUEST['belong']);
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

    }

    function view_iframe()
    {
        $this->assign("act", 'uploadedfile');
        $this->assign("id", $this->id);
        $this->assign("instance", $this->instance);
        $this->assign("belong", $this->belong);
        $this->assign("user_id", $this->user_id);
        $this->display("image.goods.html");
    }

    function uploadedfile()
    {
            import('SimpleImage.class');
            import('uploader.lib');
            $uploader = new Uploader();
            $simpleimage = new SimpleImage();
            $uploader->allowed_type(IMAGE_FILE_TYPE);
            $uploader->allowed_size(SIZE_GOODS_IMAGE); // 2M
            $upload_mod =& m('uploadedfile');
            /* ȡ��ʣ��ռ䣨��λ���ֽڣ���false��ʾ������ */
//            $store_mod  =& m('store');
//            $settings   = $store_mod->get_settings($this->store_id);
//
//            $remain     = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $upload_mod->get_file_size($this->store_id) : false;
			$remain = false;
            $files = $_FILES['file'];
            if ($files['error'] === UPLOAD_ERR_OK)
            {
                /* �����ļ��ϴ� */
                $file = array(
                    'name'      => $files['name'],
                    'type'      => $files['type'],
                    'tmp_name'  => $files['tmp_name'],
                    'size'      => $files['size'],
                    'error'     => $files['error']
                );
                $uploader->addFile($file);
                if (!$uploader->file_info())
                {
                    $data = current($uploader->get_error());
                    $res = Lang::get($data['msg']);
                    $this->view_iframe();
                    echo "<script type='text/javascript'>alert('{$res}');</script>";
                    return false;
                }
                /* �ж��ܷ��ϴ� */
                if ($remain !== false)
                {
                    if ($remain < $file['size'])
                    {
                        $res = Lang::get('space_limit_arrived');
                        $this->view_iframe();
                        echo "<script type='text/javascript'>alert('{$res}');</script>";
                        return false;
                    }
                }

                $uploader->root_dir(ROOT_PATH);
                $dirname = '';
                if ($this->belong == BELONG_GOODS)
                {
                    $dirname = 'data/files/goods/'.date('Y').'/'.date('m').'/goods_' . (time() % 200);
                }
                elseif ($this->belong == BELONG_STORE)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store') . '/other';
                }
                elseif ($this->belong == BELONG_ARTICLE)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store').'/article';
                }

                $filename  = $uploader->random_filename();
                $file_path = $uploader->save($dirname, $filename);
                //�����ϴ���Ʒ����ͼƬʱ����ˮӡ����
                if ($this->instance == 'desc_image')
                {
                	//��������ͼƬˮӡ
					$waterImage = ROOT_PATH . '/themes/mall/default/styles/default/images/pailashuiyin.png';
					$simpleimage->imageWaterMark(ROOT_PATH . '/' .$file_path,9,$waterImage,$waterText="",$textFont=5,$textColor="#FF0000",true);
                }
                /* �����ļ���� */
                $data = array(
                    'store_id'  => $this->store_id,
                    'file_type' => $file['type'],
                    'file_size' => $file['size'],
                    'file_name' => $file['name'],
                    'file_path' => $file_path,
                    'belong'    => $this->belong,
                    'item_id'   => $this->id,
                    'add_time'  => gmtime(),
                    'user_id'   => $this->user_id,
                );
                $file_id = $upload_mod->add($data);
                if (!$file_id)
                {
                    $this->_error($uf_mod->get_error());
                    return false;
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
					$newname = reimgname($file_path,$dirname);
					
                    /* ������Ʒ��� */
                    $mod_goods_image = &m('goodsimage');
                    $goods_image = array(
                        'goods_id'   => $this->id,
                        'image_url'  => $newname,
                    	'yimage_url' => $yimg,
                        'thumbnail'  => $big,
                    	'mimage_url' => $middle,
	                    'smimage_url' => $smiddle,
	                    'dimage_url'  => $default,
	                    'simage_url'  => $small,
                        'sort_order'  => 255,
                        'file_id'     => $file_id,
                    );
                    $upload_mod->edit($file_id,"file_path='".$newname."'"); //����ԭͼ������
                    if (!$mod_goods_image->add($goods_image))
                    {
                        $this->_error($this->mod_goods_imaged->get_error());
                        return false;
                    }
                    $data['thumbnail'] = $big;

                }

                $data['instance'] = $this->instance;
                $data['file_id'] = $file_id;
                $res = "{";
                foreach ($data as $key => $val)
                {
                    $res .= "\"$key\":\"$val\",";
                }
                $res = substr($res, 0, strrpos($res, ','));
                $res .= '}';
                $this->view_iframe();
                echo "<script type='text/javascript'>window.parent.add_uploadedfile($res);</script>";
            }
            elseif ($files['error'] == UPLOAD_ERR_NO_FILE)
            {
                $res = Lang::get('file_empty');
                $this->view_iframe();
                echo "<script type='text/javascript'>alert('{$res}');</script>";
                return false;
            }
            else
            {
                $res = Lang::get('sys_error');
                $this->view_iframe();
                echo "<script type='text/javascript'>alert('{$res}');</script>";
                return false;
            }

    }

    function view_remote()
    {
        $this->assign("act", 'remote_image');
        $this->assign("instance", $this->instance);
        $this->assign("id", $this->id);
        $this->assign("belong", $this->belong);
        $this->display("image.goods.html");
    }

    function remote_image()
    {
        import('SimpleImage.class');
        import('uploader.lib');
        $uploader = new Uploader();
        $simpleimage = new SimpleImage();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->allowed_size(SIZE_GOODS_IMAGE); // 2M
        $upload_mod =& m('uploadedfile');
        /* ȡ��ʣ��ռ䣨��λ���ֽڣ���false��ʾ������ */
        $store_mod  =& m('store');
        $settings   = $store_mod->get_settings($this->store_id);
        $remain     = $settings['space_limit'] > 0 ? $settings['space_limit'] * 1024 * 1024 - $upload_mod->get_file_size($this->store_id) : false;
        $uploader->root_dir(ROOT_PATH);
        $dirname = '';
        $remote_url = trim($_POST['remote_url']);
        if (!empty($remote_url))
        {
            if(preg_match("/^(http:\/\/){1,1}.+(gif|png|jpeg|jpg){1,1}$/i", $remote_url))
            {
                $result = $this->url_exist($remote_url, 2097152, $remain);
                if ($result === 1)
                {
                    $this->view_remote();
                    $res = Lang::get("url_invalid");
                    echo "<script type='text/javascript'>alert('{$res}');</script>";
                    return false;
                }
                elseif ($result === 2)
                {
                    $this->view_remote();
                    $res = Lang::get("not_allowed_size");
                    echo "<script type='text/javascript'>alert('{$res}');</script>";
                    return false;
                }
                elseif ($result === 3)
                {
                    $this->view_remote();
                    $res = Lang::get("space_limit_arrived");
                    echo "<script type='text/javascript'>alert('{$res}');</script>";
                    return false;
                }
                $img_url = _at('file_get_contents', $remote_url);
                $dirname = '';
                if ($this->belong == BELONG_GOODS)
                {
                    $dirname = 'data/files/goods/'.date('Y').'/'.date('m').'/goods_' . (time() % 200);
                }
                elseif ($this->belong == BELONG_STORE)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store') . '/other';
                }
                elseif ($this->belong == BELONG_ARTICLE)
                {
                    $dirname = 'data/files/store_' . $this->visitor->get('manage_store').'/article';
                }
                $filename  = $uploader->random_filename();
                $new_url = $dirname . '/' . $filename . '.' . substr($remote_url, strrpos($remote_url, '.')+1);
                ecm_mkdir(ROOT_PATH . '/' . $dirname);
                $fp = _at('fopen', ROOT_PATH . '/' . $new_url, "w");
                _at('fwrite', $fp, $img_url);
                _at('fclose', $fp);
                if(!file_exists(ROOT_PATH . '/' . $new_url))
                {
                    $this->view_remote();
                    $res = Lang::get("system_error");
                    echo "<script type='text/javascript'>alert('{$res}');</script>";
                    return false;
                }
            	//�����ϴ���Ʒ����ͼƬʱ����ˮӡ����
                if ($this->instance == 'desc_image')
                {
                	//��������ͼƬˮӡ
					$waterImage = ROOT_PATH . '/themes/mall/default/styles/default/images/pailashuiyin.png';
					$simpleimage->imageWaterMark(ROOT_PATH . '/' .$new_url,9,$waterImage,$waterText="",$textFont=5,$textColor="#FF0000",true);
                }
                /* �����ļ���� */
                $data = array(
                    'store_id'  => $this->store_id,
                    'file_type' => $this->_return_mimetype(ROOT_PATH . '/' . $new_url),
                    'file_size' => filesize(ROOT_PATH . '/' . $new_url),
                    'file_name' => substr($remote_url, strrpos($remote_url, '/')+1),
                    'file_path' => $new_url,
                    'belong'    => $this->belong,
                    'item_id'   => $this->id,
                    'add_time'  => gmtime(),
                    'user_id'   => $this->user_id,
                );
                $file_id = $upload_mod->add($data);
                if (!$file_id)
                {
                    $this->_error($uf_mod->get_error());
                    return false;
                }

                if ($this->instance == 'goods_image') // ������ϴ���Ʒ���ͼƬ
                {
                     /* ��������ͼ */
                	$file_path = $new_url;
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
					$newname = reimgname($file_path,$dirname);
					
                    /* ������Ʒ��� */
                    $mod_goods_image = &m('goodsimage');
                    $goods_image = array(
                        'goods_id'   => $this->id,
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
                    $upload_mod->edit($file_id,"file_path='".$newname."'"); //����ԭͼ������
                    if (!$mod_goods_image->add($goods_image))
                    {
                        $this->_error($this->mod_goods_imaged->get_error());
                        return false;
                    }
                    $data['thumbnail'] = $big;

                }

                $data['instance'] = $this->instance;
                $data['file_id'] = $file_id;
                $res = "{";
                foreach ($data as $key => $val)
                {
                    $res .= "\"$key\":\"$val\",";
                }
                $res = substr($res, 0, strrpos($res, ','));
                $res .= '}';
                $this->view_remote();
                echo "<script type='text/javascript'>window.parent.add_uploadedfile($res);</script>";
            }
            else
            {
               $res = Lang::get('url_invalid');
               $this->view_remote();

               echo "<script type='text/javascript'>alert('{$res}');</script>";
               return false;
            }
        }
        else
        {
            $res = Lang::get('remote_empty');
            $this->view_remote();
            echo "<script type='text/javascript'>alert('{$res}');</script>";
            return false;
        }
    }

    /**
     * ���Զ�̵�ַ�Ƿ���Ч���ļ��Ƿ񳬹��������ֵ��ʣ��ռ��Ƿ���
     * @author cheng
     * @param string $url | Զ�̵�ַ
     * @param int $allow_size | �����ϴ��ļ������ֵ
     * @param int $remain | �û�ʣ��Ŀռ�
     *            0 | ���ü�飬���޴�
     * @return int 1 | ��Ч��Զ�̵�ַ
     *         int 2 | �ļ���С��������ֵ
     *         int 3 | �ļ���С����ʣ��ռ�Ĵ�С
     *         boolen true | ͨ�����
     */
    function url_exist($url, $allow_size , $remain)
    {
        if(!function_exists('get_headers'))
        {
            function get_headers($url,$format=0)
            {
                $url=parse_url($url);
                $end = "\r\n\r\n";
                $fp = fsockopen($url['host'], (empty($url['port'])?80:$url['port']), $errno, $errstr, 30);
                if ($fp)
                {
                    $out  = "GET ".$url['path']." HTTP/1.1\r\n";
                    $out .= "Host: ".$url['host']."\r\n";
                    $out .= "Connection: Close\r\n\r\n";
                    $var  = '';
                    fwrite($fp, $out);
                    while (!feof($fp))
                    {
                        $var.=fgets($fp, 1280);
                        if(strpos($var,$end))
                            break;
                    }
                    fclose($fp);

                    $var=preg_replace("/\r\n\r\n.*\$/",'',$var);
                    $var=explode("\r\n",$var);
                    if($format)
                    {
                        foreach($var as $i)
                        {
                            if(preg_match('/^([a-zA-Z -]+): +(.*)$/',$i,$parts))
                                $v[$parts[1]]=$parts[2];
                        }
                        return $v;
                    }
                    else
                        return $var;
                }
            }
        }
        $head = get_headers($url);
        if(is_array($head) && !empty($head))
        {
            foreach ($head as $key => $val)
            {
                //$val = str_replace("\r\n", '', $val);
                $pos = strpos($val, 'Content-Length');
                if($key == 0)
                {
                    $hhttp = explode(' ', $val);
                    $hsize = count($hhttp) - 1;
                    $res = strcmp($hhttp[$hsize], "OK");
                    if ($res != 0)
                    {
                        return 1;
                    }
                }
                elseif ( $pos === false)
                {
                    continue;
                }
                elseif ($pos >= 0)
                {
                    $size = explode(' ', $val);
                    $count = count($size);
                    $count = $count - 1;
                    $res = intval($size[$count]);
                    if ($res > $allow_size)
                    {
                        return 2;
                    }
                    if (!empty($remain) && $res > $remain)
                    {
                        return 3;
                    }
                }
            }
        }
        else
        {
           return 1;
        }
           return true;
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