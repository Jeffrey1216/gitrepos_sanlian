<?php

/**
 *    ��֤��
 *
 *    @author    Garbin
 *    @usage    none
 */
class CaptchaApp extends FrontendApp
{
    function index()
    {
        $this->_captcha(80, 24);
    }

    /* �����֤�� */
    function check_captcha()
    {
        $captcha = empty($_GET['captcha']) ? '' : strtolower(trim($_GET['captcha']));
        if (!$captcha)
        {
            echo ecm_json_encode(false);
            return ;
        }
        if (base64_decode($_SESSION['captcha']) != $captcha)
        {
            echo ecm_json_encode(false);
        }
        else
        {
            echo ecm_json_encode(true);
        }
        return ;
    }
}

?>