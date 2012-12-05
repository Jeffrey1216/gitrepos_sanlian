<?php
class ShareArrayfile extends BaseArrayfile
{

    function __construct()
    {
        $this->ShareArrayfile();
    }

    function ShareArrayfile()
    {
        $this->_filename = ROOT_PATH . '/data/share.inc.php';
    }

    function get_default()
    {
        return array (
          1 => array (
            'title' => Lang::get('txwb'),
            'link' => 'http://v.t.qq.com/share/share.php?url={$link}&title={$title}',
            'type' => 'share',
            'sort_order' => 255,

          ),
          2 => array(
          	'title' => Lang::get('xlwb'),
          	'link'  => 'http://service.weibo.com/share/share.php?title={$title}&url={$link}',
            'type' => 'share',
            'sort_order' => 255,

          ),
          3 => array (
            'title' => Lang::get('douban'),
            'link' => 'http://www.douban.com/recommend?title={$title}&url={$link}',
            'type' => 'share',
            'sort_order' => 255,

          ),
          4 => array (
            'title' => Lang::get('qzone'),
            'link' => 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?title={$title}&url={$link}',
            'type' => 'share',
            'sort_order' => 255,

          ),
        );
    }

    function drop($share_id)
    {
        $share = $this->getOne($share_id);
        if ($share['logo'] && strpos($share['logo'], 'data/system/') === false)
        {
            file_exists(ROOT_PATH . '/' . $share['logo']) && @unlink(ROOT_PATH . '/' . $share['logo']);
        }
        parent::drop($share_id);
    }

    function getAll()
    {
        $data = array();
        if (!file_exists($this->_filename))
        {
            $data = $this->get_default();
        }
        else
        {
            $data = $this->_loadfromfile();
        }
        return $data;
    }

}
?>