<?php

/**
 *    Ftp�ͻ���
 *
 *    @author    Garbin
 *    @usage    none
 */
class FtpServer extends Object
{
    /* FTP����Flag */
    var $_connection = null;


    function __construct($server, $port = 21, $timeout = 90, $ssl = false)
    {
        $this->FtpServer($server, $port = 21, $timeout = 90, $ssl);
    }
    function FtpServer($server, $port = 21, $timeout = 90, $ssl = false)
    {
        $func = $ssl ? 'ftp_ssl_connect' : 'ftp_connect';
        $this->_connection = @$func($server, $port, $timeout);
    }

    /**
     *    ��ȡFTPѡ��
     *
     *    @author    Garbin
     *    @param     string $option
     *    @return    mixed
     */
    function get_option($option)
    {
        return ftp_get_option($this->_connection, $option);
    }

    /**
     *    ����FTPѡ��
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function set_option($option, $value)
    {
        return ftp_set_option($this->_connection, $option, $value);
    }

    /**
     *    ��ָ�����û��������¼FTP������
     *
     *    @author    Garbin
     *    @param     string $username
     *    @param     string $password
     *    @return    bool
     */
    function login($username, $password)
    {
        if (!@ftp_login($this->_connection, $username, $password))
        {
            $this->_error('ftp_login_failed');

            return false;
        }

        return true;
    }

    /**
     *    �Ա���ģʽ����
     *
     *    @author    Garbin
     *    @param     bool $turn_on
     *    @return    bool
     */
    function pasv($turn_on = false)
    {
        return ftp_pasv($this->_connection, $turn_on);
    }

    /**
     *    Sends an arbitrary command to an FTP server
     *
     *    @author    Garbin
     *    @param     string $cmd
     *    @return    array
     */
    function raw($cmd)
    {
        return ftp_raw($this->_connection, $cmd);
    }

    /**
     *    ִ��һ��FTP����
     *
     *    @author    Garbin
     *    @param     string $cmd
     *    @return    bool
     */
    function exec($cmd)
    {
        return ftp_exec($this->_connection, $cmd);
    }

    /**
     *    Sends a SITE command to the server
     *
     *    @author    Garbin
     *    @param     string $cmd
     *    @return    bool
     */
    function site($cmd)
    {
        return ftp_site($this->_connection, $cmd);
    }

    /**
     *    �رյ�ǰFTP����
     *
     *    @author    Garbin
     *    @return    bool
     */
    function close()
    {
        return ftp_close($this->_connection);
    }

    /*-------------Ŀ¼�������-----------*/
    /**
     *    ��ȡ��ǰĿ¼
     *
     *    @author    Garbin
     *    @return    bool
     */
    function pwd()
    {
        return ftp_pwd($this->_connection);
    }

    /**
     *    �л���ָ��Ŀ¼
     *
     *    @author    Garbin
     *    @param     string $dir
     *    @return    bool
     */
    function chdir($dir, $force = false)
    {
        return ftp_chdir($this->_connection, $dir);
    }

    /**
     *    �л����ϼ�Ŀ¼
     *
     *    @author    Garbin
     *    @return    void
     */
    function cdup()
    {
        return ftp_cdup($this->_connection);
    }

    /**
     *    ����Ŀ¼
     *
     *    @author    Garbin
     *    @param     string $dir
     *    @return    bool
     */
    function mkdir($dir)
    {
        return ftp_mkdir($this->_connection, $dir);
    }

    /**
     *    ɾ��ָ��Ŀ¼
     *
     *    @author    Garbin
     *    @param     string $dir
     *    @return    bool
     */
    function rmdir($dir)
    {
        return ftp_rmdir($this->_connection, $dir);
    }

    /**
     *    �б�ָ��Ŀ¼����ϸ��Ϣ
     *
     *    @author    Garbin
     *    @param     string $dir
     *    @param     bool   $recursive
     *    @return    array
     */
    function rawlist($dir, $recursive = false)
    {
        return ftp_rawlist($this->_connection, $dir, $recursive);
    }

    /*------------�ļ�������ط���-----------*/

    function alloc($size, &$msg)
    {
        return ftp_alloc($this->_connection, $size, $msg);
    }
    /**
     *    ��ָ��ģʽ��ָ��·�����ļ��ϴ���������
     *
     *    @author    Garbin
     *    @param     string   $src
     *    @param     string   $target
     *    @param     int      $mode
     *    @return    bool
     */
    function put($src, $target, $mode = FTP_BINARY)
    {
        return ftp_put($this->_connection, $target, $src, $mode);
    }

    /**
     *    ��ָ��ģʽ���������ļ���Դ�ϴ���������
     *
     *    @author    Garbin
     *    @param     resource $fp
     *    @param     string   $target
     *    @param     int      $mode
     *    @return    bool
     */
    function fput($fp, $target, $mode = FTP_BINARY)
    {
        return ftp_fput($this->_connection, $target, $fp, $mode);
    }

    /**
     *    ��FTP�����ļ���ָ���ı���·��
     *
     *    @author    Garbin
     *    @param     string   $local
     *    @param     string   $target
     *    @param     int      $mode
     *    @return    bool
     */
    function get($local, $target, $mode = FTP_BINARY)
    {
        return ftp_get($this->_connection, $local, $target, $mode);
    }

    /**
     *    ��FTP�����ļ���ָ�����ļ���Դ��
     *
     *    @author    Garbin
     *    @param     resource $fp
     *    @param     string   $target
     *    @param     int      $mode
     *    @return    bool
     */
    function fget($fp, $target, $mode = FTP_BINARY)
    {
        return ftp_fget($this->_connection, $fp, $target, $mode);
    }

    /**
     *    ��ȡָ���ļ�������޸�ʱ��
     *
     *    @author    Garbin
     *    @param     string $path
     *    @return    int
     */
    function mdtm($path)
    {
        return ftp_mdtm($this->_connection, $path);
    }

    /**
     *    ��FTP��ɾ��һ���ļ�
     *
     *    @author    Garbin
     *    @param     string $file_path
     *    @return    void
     */
    function delete($file_path)
    {
        return ftp_delete($this->_connection, $file_pat);
    }

    /**
     *    ��ȡ�����ļ��Ĵ�С
     *
     *    @author    Garbin
     *    @param     string $file_path
     *    @return    int
     */
    function size($file_path)
    {
        return ftp_size($this->_connection, $file_path);
    }

    /*--------�ļ���Ŀ¼���в���--------*/
    /**
     *    �޸��ļ���Ŀ¼��
     *
     *    @author    Garbin
     *    @param     string $old_name
     *    @param     string $new_name
     *    @return    bool
     */
    function rename($old_name, $new_name)
    {
        return ftp_rename($this->_connection, $old_name, $new_name);
    }

    /**
     *    �����ļ���Ŀ¼Ȩ��
     *
     *    @author    Garbin
     *    @param     string $path
     *    @param     int $mode
     *    @return    bool
     */
    function chmod($path, $mode = 0777)
    {
        if (!function_exists('ftp_chmod') || !ftp_chmod($this->_connection, $mode, $path))
        {
            return $this->site("CHMOD {$mode} {$path}");
        }

        return true;
    }
}

?>