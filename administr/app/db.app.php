<?php

/**
 *    ���ݿ������
 *
 *    @author    Hyber
 *    @usage    none
 */

function dump_escape_string($str)
{
    if ($str === null)
    {
        return null;
    }
    else if($str == '')
    {
        return '';
    }
    else
    {
        return cls_mysql::escape_string($str);
    }
}
function remove_comment($var)
{
    return (substr($var, 0, 2) != '--');
}
class DbApp extends BackendApp
{
    var $db;
    var $backup_dir = 'sql_backup';
    var $backup_path;
    var $backup_name; //��ǰ��������
    var $offset = 300; //ÿ�ζ�ȡ��������
    var $dump_sql  = '';
    var $sql_num   = 0;
    var $min_vol_size = 10; //�־����ٴ�С
    var $limit_backup_name = '1,20'; //��������λ������
    var $ext_insert = false; //�Ƿ���չ����
    function __construct()
    {
        $this->DbApp();
    }

    function DbApp()
    {
        parent::BackendApp();
        /* �����ִ��ʱ��Ϊ5���� */
        _at('set_time_limit', 300);
        _at('ini_set', 'memory_limit' , '64M');
        $this->db = &db();
        $this->backup_path = ROOT_PATH . '/' . 'data/'. $this->backup_dir .'/';
    }

    /**
     *    ���ݿⱸ��
     *
     *    @author    Hyber
     *    @return    void
     */
    function backup()
    {
        /* ��Ҫ���ݵı� */
        $all_tables = $this->db->getAll("show tables LIKE '". $this->_mysql_like_quote(DB_PREFIX) . "%'");
        $tables = array(); //���ݶ���
        foreach ($all_tables as $key => $table)
        {
            $tables[current($table)] = -1;
        }
        if (!IS_POST)
        {
            $allow_max_size = $this->_return_bytes(@ini_get('upload_max_filesize')) / 1024; //��λΪKB;
            $this->assign('vol_size', $allow_max_size);
            $this->assign('backup_name', $this->_make_backup_name());
            $this->assign('tables', $tables);
            $this->display('db.backup.html');
        }
        else
        {
            $exp_tables = array(); //�û�ѡ��Ҫ���ݵĶ���
            isset($_POST['backup_type']) && $_POST['backup_type'] == 'backup_all' && $exp_tables = $tables;
            isset($_POST['backup_type']) && $_POST['backup_type'] == 'backup_custom' && $exp_tables = $_POST['tables'];
            $vol_size = isset($_POST['vol_size']) ? intval($_POST['vol_size']) : 0; //�־��С
            $ext_insert = isset($_POST['ext_insert']) ? intval($_POST['ext_insert']) : 0;
            $backup_name = isset($_POST['backup_name']) ? trim($_POST['backup_name']) : '';
            if ($ext_insert)
            {
                $this->ext_insert = true;
            }
            if(!$exp_tables)
            {
                $this->show_warning('no_table_selected');
                return;
            }
            if($vol_size < $this->min_vol_size)
            {
                $this->show_warning(sprintf(Lang::get('invalid_vol_size'), $this->min_vol_size));
                return;
            }
            if(!$backup_name)
            {
                $this->show_warning('no_backup_name');
                return;
            }
            if (!preg_match("/^[\w]{" . $this->limit_backup_name . "}$/", $backup_name))
            {
                $limit_arr = explode(',', $this->limit_backup_name);
                $this->show_warning(sprintf(Lang::get('invalid_backup_name'), $limit_arr[0], $limit_arr[1]));
                return;
            }
            $this->backup_name = $backup_name;
            if(is_dir($this->backup_path . $this->backup_name))
            {
                $this->show_warning('backup_name_exist');
                return;
            }
            ecm_mkdir(ROOT_PATH . '/data/'. $this->backup_dir .'/' . $this->backup_name);
            if (!file_exists($this->backup_path . $this->backup_name . '/queue.log'))
            {
                /*������Ҫ���������ݱ���У�д���ļ�*/
                $this->_put_queue($exp_tables);
            }
            /*��ʼ����*/
            $tables = $this->_dump_queue(1, $vol_size * 1024);
            if ($tables === false)
            {
                $this->show_warning('invalid_queue_file');
                return;
            }
            $this->_deal_result($tables, 1, $vol_size);
        }
    }

    /**
     *    ���ݱ���
     *
     *    @author    Hyber
     *    @return    void
     */
    function export()
    {
        $backup_name = empty($_GET['backup_name']) ? '' : trim($_GET['backup_name']);
        $vol_size    = empty($_GET['vol_size']) ? 0 : intval($_GET['vol_size']);
        $ext_insert  = empty($_GET['ext_insert']) ? 0 : intval($_GET['ext_insert']);
        if(!$backup_name)
        {
            $this->show_warning('no_backup_name');
            return;
        }
        if (!preg_match("/^[\w]{" . $this->limit_backup_name . "}$/", $backup_name))
        {
            $limit_arr = explode(',', $this->limit_backup_name);
            $this->show_warning(sprintf(Lang::get('invalid_backup_name'), $limit_arr[0], $limit_arr[1]));
            return;
        }
        $this->backup_name = $backup_name;
        if($vol_size < $this->min_vol_size)
        {
            $this->show_warning(sprintf(Lang::get('invalid_vol_size'), $this->min_vol_size));
            return;
        }

        $vol = $this->_get_finish_vol('ex_finish_vol.log');
        if ($vol === false)
        {
            $this->show_warning('vol_log_error');
            return;
        }
        if ($ext_insert)
        {
            $this->ext_insert = true;
        }
        $vol ++;
        /*��ʼ����*/
        $tables = $this->_dump_queue($vol, $vol_size * 1024);
        if ($tables === false)
        {
            $this->show_warning('invalid_queue_file');
            return;
        }
        $this->_deal_result($tables, $vol, $vol_size);
    }

    /**
     *    ���ݿ�ָ�
     *
     *    @author    Hyber
     *    @return    void
     */
    function restore()
    {
        $backups = $this->_list_backup();
        $this->assign('backups', $backups);
        $this->display('db.restore.html');
    }

    function download()
    {
        $file = isset($_GET['file']) ? trim($_GET['file']) : '';
        $backup_name = isset($_GET['backup_name']) ? trim($_GET['backup_name']) : '';
        if (!$file)
        {
            $this->show_warning('no_such_file');
            return;
        }
         if (!$backup_name)
        {
            $this->show_warning('no_backup_name');
            return;
        }
        $sql_file = $this->backup_path . $backup_name . '/' . $file;
        if (file_exists($sql_file))
        {
            header('Content-type: application/unknown');
            header('Content-Disposition: attachment; filename="'. $file. '"');
            header("Content-Length: " . filesize($sql_file) ."; ");
            readfile($sql_file);
        }
        else
        {
            $this->show_warning('no_such_file');
            return;
        }
    }

    function drop()
    {
        $backup_names = isset($_GET['backup_name']) ? trim($_GET['backup_name']) : '';
        if (!$backup_names)
        {
            $this->show_warning('no_backup_name');
            return;
        }
        $backup_names = explode(',', $backup_names);
        foreach ($backup_names as $backup_name)
        {
            is_dir($this->backup_path.$backup_name) && ecm_rmdir($this->backup_path.$backup_name);
        }
        $this->show_message('drop_ok');
    }

    /**
     * ��¼��ɵľ��
     *
     * @param string $log_file
     * @param int $vol
     * @return bool
     */
    function _set_finish_vol($file_name, $vol)
    {
        $log_file = $this->backup_path . $this->backup_name . '/' . $file_name;
        return file_put_contents($log_file, $vol);
    }

    /**
     * ��ȡ�ϴ���ɵľ��
     *
     * @param string $log_file
     * @param int $vol
     * @return int
     */
    function _get_finish_vol($file_name)
    {
        $log_file = $this->backup_path . $this->backup_name . '/' . $file_name;
        if (!file_exists($log_file))
        {
            return 0; //��־�ļ������ڣ���ʱ������Ŵӵ�һ��ʼ
        }
        $content = file_get_contents($log_file);
        return is_numeric($content) ? intval($content) : false;
    }

    function _drop_finish_vol($file_name)
    {
        $log_file = $this->backup_path . $this->backup_name . '/' . $file_name;
        return file_exists($log_file) && @unlink($log_file);
    }

    /**
     *    ���ݱ���
     *
     *    @author    Hyber
     *    @return    void
     */
    function import()
    {
        $backup_name = empty($_GET['backup_name']) ? '' : trim($_GET['backup_name']);
        $new = empty($_GET['new']) ? 0 : intval($_GET['new']);
         if(!$backup_name)
        {
            $this->show_warning('no_backup_name');
            return;
        }
        $this->backup_name = $backup_name;
        if ($new == 1)
        {
            $this->_drop_finish_vol('im_finish_vol.log');
        }
        $vol = $this->_get_finish_vol('im_finish_vol.log');
        if ($vol === false)
        {
            $this->show_warning('vol_log_error');
        }
        $vol++;
        $backups =  $this->_list_vol($this->backup_name);
        /* ���汾 */
        foreach ($backups as $backup)
        {
            if ($backup['ecm_ver'] != VERSION)
            {
                $this->show_warning(sprintf(Lang::get('version_error'), VERSION, $backup['ecm_ver']));
                return;
            }
        }
        /* ����Ƿ���� */
        if (file_exists($this->backup_path . $this->backup_name . '/queue.log'))
        {
            $this->show_warning('backup_not_finished');
            return;
        }
        /* ��������� */
        $end_vol = end($backups);
        $total = isset($end_vol['total']) ? $end_vol['total'] : 0;
        if ($total != count($backups) || !$total){//��������
            $this->show_warning('backup_not_full_or_error');
            return;
        }
        $total_keys = range(1, $total); //����ֵ��1���ܾ���������
        $backups_keys = array_keys($backups); //��ȡ���ķ־����ɵ�����
        if($backups_keys != $total_keys)
        {
            $this->show_warning('backup_not_full_or_error');
            return;
        }

        $backup = $backups[$vol];
        if (!$vol || !$backup)
        {
            $this->show_warning(sprintf(Lang::get('no_such_vol'), $vol));
            return;
        }
        /*��ʼ����sql*/
        if ($this->_import_vol($backup['file']))
        {
            $this->_set_finish_vol('im_finish_vol.log',$vol); //��¼���ε�����
            if ($vol<count($backups))
            {
                $lnk = 'index.php?app=db&act=import&backup_name=' . urlencode($this->backup_name);
                $this->assign('title', sprintf(Lang::get('restore_title'), '#' . $vol));
                $this->assign('auto_redirect', 1);
                $this->assign('auto_link', $lnk);
                $this->display('db.message.html');
            }
            else
            {
                $this->_drop_finish_vol('im_finish_vol.log');
                $this->show_message('restore_success');
            }
        }
        else
        {
            $this->show_warning(sprintf(Lang::get('import_vol_error'), $backup['file']));
            return;
        }
    }


    function _deal_result($tables, $vol, $vol_size)
    {
        if (empty($tables))
        {
            /* ���ݽ��� */
            if (!$this->_savasql($this->dump_sql,$vol))
            {
                $this->show_warning('fail_save_sql');//������Ҫ����
                return;
            }
            $this->_drop_queue();
            $vol != 1 && $this->_drop_finish_vol('ex_finish_vol.log'); //ֻ��һ��ʱ��������־�ļ�������ɾ��
            $list_vol = $this->_list_vol($this->backup_name);
            foreach ($list_vol as $key => $value)
            {
                $list[] = array('name'=>$value['file'], 'href'=>'index.php?app=db&act=download&backup_name=' . $this->backup_name . '&file=' . $value['file']);
            }

            $this->assign('list',  $list);
            $this->assign('title', Lang::get('backup_success'));
            $this->display('db.message.html');
        }
        else
        {
            /* ��һ��ҳ�洦�� */
            if (!$this->_savasql($this->dump_sql, $vol))
            {
                $this->show_warning('fail_save_sql');
                return;
            }
            $this->_set_finish_vol('ex_finish_vol.log', $vol);
            $lnk = 'index.php?app=db&act=export&backup_name=' . $this->backup_name . '&vol_size=' . $vol_size . '&ext_insert=' . ($this->ext_insert ? 1 : 0);
            $this->assign('title', sprintf(Lang::get('backup_title'), '#' . $vol));
            $this->assign('auto_redirect', 1);
            $this->assign('auto_link', $lnk);
            $this->display('db.message.html');
        }
    }

    /**
     * ��������һ�������ı�
     *
     * @param int $vol         ���
     * @param int $vol_size    ÿ������ֽ�
     * @return array           ���ݶ�������
     */
    function _dump_queue($vol, $vol_size)
    {
        $tables = $this->_get_queue();
        if ($tables === false || $tables === 1)
        {
            return false;
        }

        if (empty($tables))
        {
            return $tables;
        }
        $this->dump_sql = $this->_make_head($vol);
        foreach ($tables as $table => $pos)
        {
            if ($pos == -1)
            {
                $table_df = $this->_get_table_df($table, true);
                if (strlen($this->dump_sql) + strlen($table_df) > $vol_size - 32)
                {
                    if ($this->sql_num == 0)
                    {
                        /* ��һ����¼��ǿ��д�� */
                        $this->dump_sql .= $table_df;
                        $this->sql_num += 2;
                        $tables[$table] = 0;
                    }
                    break;
                }
                else
                {
                    $this->dump_sql .= $table_df;
                    $this->sql_num +=2;
                    $pos = 0;
                }
            }
            /* �����ܶ��ȡ���ݱ����� */
            $post_pos = $this->_get_table_data($table, $pos, $vol_size);
            if ($post_pos == -1)
            {
                /* �ñ��Ѿ���ɣ�����ñ� */
                unset($tables[$table]);
            }
            else
            {
                /* �ñ�δ��ɡ�˵����Ҫ��������,��¼��������λ�� */
                $tables[$table] = $post_pos;
                break;
            }
        }
        $this->dump_sql .= "-- END PaiLa 2.0 SQL Dump Program ";
        if (empty($tables))
        {
            $this->dump_sql = "-- TOTAL : ". $vol . "\r\n" . $this->dump_sql;
        }
        $this->_put_queue($tables);
        return $tables;
    }

    function _import_vol($sql_file_name)
    {
        $except_table = array(
            'DROP TABLE IF EXISTS '. DB_PREFIX . 'sessions',
            'CREATE TABLE '. DB_PREFIX . 'sessions',
            'INSERT INTO '. DB_PREFIX . 'sessions',
        ); //���ָ�session��ر�
        $sql_file = $this->backup_path . $this->backup_name . '/' . $sql_file_name;
        $db_ver  = $this->db->version();
        $sql_str = array_filter(file($sql_file), 'remove_comment');//ȥ��ע��
        $sql_str = str_replace("\r", '', implode('', $sql_str));

        $ret = explode(";\n", $sql_str);
        $ret_count = count($ret);

        /* ִ��sql��� */
        if ($db_ver > '4.1')
        {
            for($i = 0; $i < $ret_count; $i++)
            {
                $ret[$i] = trim($ret[$i], " \r\n;"); //�޳�������Ϣ
                if (!empty($ret[$i]))
                {
                    if ((strpos($ret[$i], 'CREATE TABLE') !== false) && (strpos($ret[$i], 'DEFAULT CHARSET='. str_replace('-', '', CHARSET) )=== false))
                    {
                        /* ����ʱȱ DEFAULT CHARSET */
                        $ret[$i] = $ret[$i] . ' DEFAULT CHARSET='. str_replace('-', '', CHARSET);
                    }
                    $tmp_sql = str_replace('`', '', $ret[$i]);
                    if ($this->strposa(trim($tmp_sql), $except_table) === 0)
                    {
                        continue;
                    }
                    $this->db->query($ret[$i]);
                }
            }
        }
        else
        {
            for($i = 0; $i < $ret_count; $i++)
            {
                $ret[$i] = trim($ret[$i], " \r\n;"); //�޳�������Ϣ
                if ((strpos($ret[$i], 'CREATE TABLE') !== false) && (strpos($ret[$i], 'DEFAULT CHARSET='. str_replace('-', '', CHARSET) )!== false))
                {
                    $ret[$i] = str_replace('DEFAULT CHARSET='. str_replace('-', '', CHARSET), '', $ret[$i]);
                }
                if (!empty($ret[$i]))
                {
                    $tmp_sql = str_replace('`', '', $ret[$i]);
                    if ($this->strposa(trim($tmp_sql), $except_table) === 0)
                    {
                        continue;
                    }
                    $this->db->query($ret[$i]);
                }
            }
        }

        return true;
    }

    /**
     *  ��ȡָ����Ķ���
     *
     * @param   string      $table      ���ݱ���
     * @param   boolen      $add_drop   �Ƿ����drop table
     *
     * @return  string      $sql
     */
    function _get_table_df($table, $add_drop = false)
    {
        if ($add_drop)
        {
            $table_df = "DROP TABLE IF EXISTS $table;\r\n";
        }
        else
        {
            $table_df = '';
        }

        $this->db->query('SET SQL_QUOTE_SHOW_CREATE = 0');
        $tmp_arr = $this->db->getRow("SHOW CREATE TABLE $table");
        $tmp_sql = $tmp_arr['Create Table'];
        $tmp_sql = substr($tmp_sql, 0, strrpos($tmp_sql, ")") + 1); //ȥ����β���塣
        $tmp_sql = str_replace("\n", "\r\n", $tmp_sql);
        $table_df .= $tmp_sql . " TYPE=MyISAM;\r\n";
        return $table_df;
    }


    function _get_table_data($table, $pos, $vol_size)
    {
        $post_pos = $pos;

        /* ��ȡ���ݱ��¼���� */
        $total = $this->db->getOne("SELECT COUNT(*) FROM $table");

        if ($total == 0 || $pos >= $total)
        {
            /* ���봦�� */
            return -1;
        }

        /* ȷ��ѭ������ */
        $cycle_time = ceil(($total-$pos) / $this->offset); //ÿ��ȡoffset��������Ҫȡ�Ĵ���

        /* ѭ�������ݱ� */
        for($i = 0; $i<$cycle_time; $i++)
        {
            /* ��ȡ���ݿ����� */
            $data = $this->db->getAll("SELECT * FROM $table LIMIT " . ($this->offset * $i + $pos) . ', ' . $this->offset);
            $data_count = count($data);

            $fields = array_keys($data[0]);
            $start_sql = "INSERT INTO $table ( `" . implode("`, `", $fields) . "` ) VALUES ";

            /* ѭ��������д�� */
            for($j=0; $j< $data_count; $j++)
            {
                $record = array_map("dump_escape_string", $data[$j]);//���˷Ƿ��ַ�

                /* ����Ƿ���д�룬����д�� */
                if ($this->ext_insert)
                {
                    if ($post_pos == $total-1)
                    {
                        $tmp_dump_sql = " (". $this->_implode_insert_values($record) .");\r\n";
                    }
                    else
                    {
                        if ($j == $data_count - 1)
                        {
                            $tmp_dump_sql = " (". $this->_implode_insert_values($record) .");\r\n";
                        }
                        else
                        {
                            $tmp_dump_sql = " (". $this->_implode_insert_values($record) ."),\r\n";
                        }
                    }

                    if ($post_pos == $pos)
                    {
                        /* ��һ�β������� */
                        $tmp_dump_sql = $start_sql . "\r\n" . $tmp_dump_sql;
                    }
                    else
                    {
                        if ($j == 0)
                        {
                            $tmp_dump_sql = $start_sql . "\r\n" . $tmp_dump_sql;
                        }
                    }
                }
                else
                {
                    $tmp_dump_sql = $start_sql . " (". $this->_implode_insert_values($record) .");\r\n";
                }

                if (strlen($this->dump_sql) + strlen($tmp_dump_sql) > $vol_size - 32)
                {
                    if ($this->sql_num == 0)
                    {
                        $this->dump_sql .= $tmp_dump_sql; //���ǵ�һ����¼ʱǿ��д��
                        $this->sql_num++;
                        $post_pos++;
                        if ($post_pos == $total)
                        {
                            /* ���������Ѿ�д�� */
                            return -1;
                        }
                    }

                    return $post_pos;
                }
                else
                {
                    $this->dump_sql .= $tmp_dump_sql;
                    $this->sql_num++; //��¼sql����
                    $post_pos++;
                }
            }
        }
        /* ���������Ѿ�д�� */
        return -1;
    }


    function _make_head($vol)
    {
        /* ϵͳ��Ϣ */
        $sys_info['os']         = PHP_OS;
        $sys_info['web_server'] = $_SERVER['SERVER_SOFTWARE'];
        $sys_info['php_ver']    = PHP_VERSION;
        $sys_info['mysql_ver']  = $this->db->version();
        $sys_info['paila_version']     = VERSION . ' (' . CHARSET . ')';
        $sys_info['date']       = local_date('Y-m-d H:i:s');

        $head = "-- PaiLa 2.0.1 SQL Dump Program\r\n" .
                 "-- " . $sys_info['web_server'] . "\r\n" .
                 "-- \r\n" .
                 "-- DATE : ".$sys_info["date"]. "\r\n" .
                 "-- MYSQL SERVER VERSION : ".$sys_info['mysql_ver']. "\r\n" .
                 "-- PHP VERSION : ".$sys_info['php_ver']. "\r\n" .
                 "-- PaiLa VERSION : ".VERSION. "\r\n" .
                 "-- Vol : " . $vol . "\r\n";
        return $head;
    }

    /**
     * д����Ҫ��������ݱ����
     *
     * @return bool
     */

    function _put_queue($tables)
    {
        return file_put_contents($this->backup_path . $this->backup_name . '/queue.log', "<?php return " . var_export($tables, true). "?>");
    }

    function _drop_queue()
    {
        $queue_file = $this->backup_path . $this->backup_name . '/queue.log';
        return @unlink($queue_file);
    }

    /**
     * ��ȡ��Ҫ��������ݱ����
     *
     * @return array
     */
    function _get_queue()
    {
        $queue_file = $this->backup_path . $this->backup_name . '/queue.log';
        if (!file_exists($queue_file))
        {
            return false;
        }
        else
        {
            return include($queue_file);
        }

    }

    /**
     * ���浼����sql
     *
     * @param string $sql           sql���
     * @param unknown_type $vol     ���
     * @return bool
     */
    function _savasql($sql, $vol)
    {
        return file_put_contents($this->backup_path . $this->backup_name . '/' . $this->backup_name . '_' . $vol . '_' . $this->_make_rand(6)  . '.sql', $sql);
    }

    function _make_rand($leng)
    {
        for ($i = 0; $i < $leng; $i++)
        {
            $type = mt_rand(1,2);
            if ($type == 1)
            {
                $rand .= chr(mt_rand(97, 122));
            }
            else
            {
                $rand .= chr(mt_rand(48, 57));
            }
        }
        return $rand;
    }

    /**
     * ���ɱ�������
     *
     * @return string
     */
    function _make_backup_name()
    {
        $str = local_date('Ymd_'); //����ǰ׺
        $No_have_been = array(); //�����Ѿ��еı������
        if (is_dir($this->backup_path))
        {
            if ($handle = opendir($this->backup_path))
            {
                while (($file = readdir($handle)) !== false)
                {
                    if ($file{0} != '.' && filetype($this->backup_path . $file) == 'dir')
                    {
                        if (strpos($file, $str) === 0)
                        {
                            $No = intval(str_replace($str, '', $file)); //����ı��
                            if ($No)
                            {
                                $No_have_been[] = $No;
                            }
                        }
                    }
                }
            }
        }
        if ($No_have_been)
        {
            $str .= max($No_have_been)+1;
        }
        else
        {//û���ҵ����챸��
            $str .= '1';
        }
        return $str;
    }


    function _list_vol($backup_name)
    {
        $vols = array(); //���еľ�
        $bytes = 0;
        $vol_path = $this->backup_path . $backup_name . '/';
        if (is_dir($vol_path))
        {
            if ($handle = opendir($vol_path))
            {
                while (($file = readdir($handle)) !== false)
                {
                    $file_info = pathinfo($vol_path  . $file);
                    if ($file_info['extension'] == 'sql')
                    {
                        $vol = $this->_get_head($vol_path . $file);
                        $vol['file'] = $file;
                        $bytes += filesize($vol_path . $file);
                        $vol['size'] = ceil(10 * filesize($vol_path . $file) / 1024) / 10;
                        isset($vol['total']) && $vol['total_size'] = ceil(10 * $bytes / 1024) / 10;
                        $vol && $vols[$vol['vol']] = $vol;
                    }
                }
            }
        }
        ksort($vols);
        return $vols;
    }

    function _list_backup()
    {
        $backups = array(); //���еı���
        if (is_dir($this->backup_path))
        {
            if ($handle = opendir($this->backup_path))
            {
                while (($file = readdir($handle)) !== false)
                {
                    if ($file{0} != '.' && filetype($this->backup_path . $file) == 'dir')
                    {
                        $backup['name'] = $file;
                        $backup['date'] = filemtime($this->backup_path . $file);
                        $backup['vols'] = $this->_list_vol($file);
                        $end_vol = end($backup['vols']);
                        $backup['total'] = isset($end_vol['total']) ? intval($end_vol['total']) : 'unknown';
                        $backup['total_size'] = isset($end_vol['total_size']) ? $end_vol['total_size'] : 'unknown';
                        $backups[$backup['date']] = $backup;
                    }
                }
            }
        }
        ksort($backups);
        return $backups;
    }

    /**
     *  ��ȡ�����ļ���Ϣ
     *
     * @param   string      $path       �����ļ�·��
     * @return  array       $arr        ��Ϣ����
     */
    function _get_head($path)
    {
        /* ��ȡsql�ļ�ͷ����Ϣ */
        $sql_info = array('date'=>'', 'mysql_ver'=> '', 'php_ver'=>0, 'ecm_ver'=>'', 'vol'=>0);
        $fp = fopen($path,'rb');
        $str = fread($fp, 270);
        fclose($fp);
        $arr = explode("\n", $str);
        foreach ($arr AS $val)
        {
            $pos = strpos($val, ':');
            if ($pos > 0)
            {
                $type = trim(substr($val, 0, $pos), "-\n\r\t ");
                $value = trim(substr($val, $pos+1), "/\n\r\t ");
                if ($type == 'TOTAL')
                {
                    $sql_info['total'] = $value;
                }
                if ($type == 'DATE')
                {
                    $sql_info['date'] = $value;
                }
                elseif ($type == 'MYSQL SERVER VERSION')
                {
                    $sql_info['mysql_ver'] = $value;
                }
                elseif ($type == 'PHP VERSION')
                {
                    $sql_info['php_ver'] = $value;
                }
                elseif ($type == 'PaiLa VERSION')
                {
                    $sql_info['ecm_ver'] = $value;
                }
                elseif ($type == 'Vol')
                {
                    $sql_info['vol'] = $value;
                }
            }
        }
        return $sql_info;
    }


    /**
     * ��G M Kת��Ϊ�ֽ�
     *
     * @param string $val
     * @return int
     */
    function _return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last)
        {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }


    /**
     * �� MYSQL LIKE �����ݽ���ת��
     *
     * @access      public
     * @param       string      string  ����
     * @return      string
     */
    function _mysql_like_quote($str)
    {
        return strtr($str, array("\\\\" => "\\\\\\\\", '_' => '\_', '%' => '\%'));
    }

    /**
     * �� MYSQL INSERT INTO ����values�������ݽ����ַ�������
     *
     * @param array $values
     * @return string
     */
    function _implode_insert_values($values)
    {
        $str = '';
        $values = array_values($values);
        foreach ($values as $k =>$v)
        {
            $v = ($v === null) ? 'null' : "'" . $v . "'";
            $str = ($k == 0) ? $str . $v : $str . ',' . $v;
        }
        return $str;
    }

    function strposa($haystack ,$needles=array(),$offset=0)
    {
        $chr = array();
        foreach($needles as $needle){
            strpos($haystack,$needle,$offset) !== false && $chr[] = strpos($haystack,$needle,$offset);
        }
        if(empty($chr)) return false;
        return min($chr);
    }
}

?>
