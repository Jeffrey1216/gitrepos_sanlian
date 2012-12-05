<?php

/**
 *    �ƻ������ػ�����
 *
 *    @author    Garbin
 *    @usage    none
 */
class Crond extends Object
{
    /* ���� */
    var $_config = array();

    /* �����б� */
    var $_tasks  = null;

    /* ��ǰʱ�� */
    var $_now    = 0;
    var $_lock_fp = null;

    function __construct($setting)
    {
        $this->Crond($setting);
    }
    function Crond($setting)
    {
        $this->_now = time();   //�Է�������ǰʱ��Ϊ��
        $this->_config($setting);
    }

    /**
     *    ����
     *
     *    @author    Garbin
     *    @param     string $key ����������
     *               array  $key ����������
     *    @param     mixed  $value ������ֵ
     *    @return    void
     */
    function _config($key, $value = '')
    {
        if (is_array($key))
        {
            $this->_config = array_merge($this->_config, $key);
        }
        else
        {
            $this->_config[$key] = $value;
        }
    }

    /**
     *    ��ʼ������
     *
     *    @author    Garbin
     *    @return    void
     */
    function _init_tasks()
    {
        if (empty($this->_config['task_list']))
        {
            return;
        }

        $this->_tasks = include($this->_config['task_list']);
        if (empty($this->_tasks))
        {
            return;
        }
        $update = false;
        foreach ($this->_tasks as $task => $config)
        {
            if (empty($config['due_time']))
            {
                $update = true;
                $this->_tasks[$task]['due_time'] = $this->get_due_time($config);
            }
        }
        $update && $this->update();
    }

    /**
     *    ִ��
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function execute()
    {
        //�˴����������ƿ��ܴ��ڳ�ͻ����
        /* ������ */
        if ($this->is_lock())
        {
            return;
        }

        /* �����н���ǰ���� */
        _at('set_time_limit', 1800);      //���Сʱ
        _at('ignore_user_abort', true);   //�����û��˳�
        $this->lock();
        $this->_init_tasks();
        /* ��ȡ���ڵ������б� */
        $due_tasks = $this->get_due_tasks();
        /* û�е��ڵ����� */
        if (empty($due_tasks))
        {
            $this->unlock();
            return;
        }

        /* ִ������ */
        $this->run_task($due_tasks);

        /* ���������б� */
        $this->update_tasks($due_tasks);

        /* ���� */
        $this->unlock();
    }

    /**
     *    ��ȡ��������״̬
     *
     *    @author    Garbin
     *    @return    int
     *               0  δ����״̬
     *               1  ����״̬
     */
    function is_lock()
    {
        if (is_writable($this->_config['lock_file']) && (filemtime($this->_config['lock_file']) + 900) > time())
        {
            return 1;
        }
        else
        {
            return 0;
        }

        /*
        if (!is_file($this->_config['lock_file']))
        {
            return 0;
        }
        $status = intval(file_get_contents($this->_config['lock_file']));

        return $status;
        */
    }

    /**
     *    ��������
     *
     *    @author    Garbin
     *    @return    void
     */
    function lock()
    {
        _at('touch', $this->_config['lock_file']);
        //file_put_contents($this->_config['lock_file'], 1, LOCK_EX);
    }

    /**
     *    ����
     *
     *    @author    Garbin
     *    @return    void
     */
    function unlock()
    {
        _at('unlink', $this->_config['lock_file']);
        //file_put_contents($this->_config['lock_file'], 0, LOCK_EX);
    }

    /**
     *    ��ȡ���ڵ������б�
     *
     *    @author    Garbin
     *    @param    none
     *    @return    array
     */
    function get_due_tasks()
    {
        $tasks = array();
        if (empty($this->_tasks))
        {
            return $tasks;
        }
        foreach ($this->_tasks as $task => $config)
        {
            if ($this->is_due($config))
            {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    /**
     *    ִ�������б�
     *
     *    @author    Garbin
     *    @param     array $tasks
     *    @return    void
     */
    function run_task($tasks)
    {
        if (empty($tasks))
        {
            return;
        }
        foreach ($tasks as $task)
        {
            $this->_run_task($task);
        }
    }

    /**
     *    ���������б�
     *
     *    @author    Garbin
     *    @param     array $tasks
     *    @return    void
     */
    function update_tasks($tasks)
    {
        if (empty($tasks))
        {
            return;
        }
        foreach ($tasks as $task)
        {
            $this->_update_task($task);
        }
        $this->update();
    }

    /**
     *    �жϼƻ��Ƿ���
     *
     *    @author    Garbin
     *    @param     array $task_config
     *    @return    bool
     */
    function is_due($task_config)
    {
        if ($task_config['cycle'] == 'none' && $task_config['last_time'])
        {
            return false;
        }
        $due_time = $task_config['due_time'];

        return ($this->_now >= $due_time);
    }

    /**
     *    ��ȡ�´ε���ʱ��
     *
     *    @author    Garbin
     *    @param     array $config
     *    @return    int
     */
    function get_due_time($config)
    {
        $due_time = 0;
        switch ($config['cycle'])
        {
            /* �Զ�����Ե�ǰʱ��Ϊ�´ε���ʱ�� */
            case 'custom':
                $due_time = $this->_now + $config['interval'];
            break;

            /* ÿ�ն��� */
            case 'daily':
                /* ��ȡ���յ�ʱ��� */
                $today_due_time = strtotime(date('Y-m-d', $this->_now) . " {$config['hour']}:{$config['minute']}");

                if ($this->_now >= $today_due_time)
                {
                    /* ����ѹ����ʱ��㣬���´ε���ʱ��+����1�� */
                    $due_time = $today_due_time + 3600 * 24;
                }
                else
                {
                    /* ������Ե��յĵ���ʱ���Ϊ�´ε���ʱ�� */
                    $due_time = $today_due_time;
                }
            break;

            /* ÿ�ܶ��� */
            case 'weekly':
                $next_week_due_time = strtotime(date('Y-m-d', strtotime("next {$config['day']}")) . " {$config['hour']}:{$config['minute']}");
                $this_week_due_time = $next_week_due_time - 7 * 24 * 3600;
                if ($this->_now >= $this_week_due_time)
                {
                    /* ���ѹ��˱��ܵ�ʱ��㣬���´ε��������ܵ�ʱ��� */
                    $due_time = $next_week_due_time;
                }
                else
                {
                    /* ����Ϊ���ܵ�ʱ��� */
                    $due_time = $this_week_due_time;
                }
            break;

            /* ÿ�¶��� */
            case 'monthly':
                $this_month_time = date('Y-m', $this->_now) . "-{$config['day']} {$config['hour']}:{$config['minute']}";
                $this_month_due_time = strtotime($this_month_time);                 //���µ���ʱ��
                $next_month_due_time = strtotime($this_month_time . ' +1 month');   //���µ���ʱ��
                if ($this->_now >= $this_month_due_time)
                {
                    /* �ѹ�����ʱ��� */
                    $due_time = $next_month_due_time;
                }
                else
                {
                    /* δ������ʱ��� */
                    $due_time = $this_month_due_time;
                }
            break;

            default:
                return false;
            break;
        }

        return $due_time;
    }

    /**
     *    ����ָ������
     *
     *    @author    Garbin
     *    @param     string $task_name
     *    @return    bool
     */
    function _run_task($task_name)
    {
        $task_file = $this->_config['task_path'] . '/' . $task_name . '.task.php';
        include_once($task_file);
        $task_config = empty($this->_tasks[$task_name]['config']) ? array() : $this->_tasks[$task_name]['config'];
        $task_class_name = ucfirst($task_name) . 'Task';
        $task  = new $task_class_name($task_config);
        $task->run();
    }

    /**
     *    ���������б�
     *
     *    @author    Garbin
     *    @return    void
     */
    function update()
    {
        file_put_contents($this->_config['task_list'], "<?php\r\n\r\nreturn " . var_export($this->_tasks, true) . ";\r\n\r\n?>");
    }

    /**
     *    �����ϴ�ִ��ʱ��
     *
     *    @author    Garbin
     *    @param     string $task_name
     *    @return    void
     */
    function _update_task($task)
    {
        if (!isset($this->_tasks[$task]))
        {
            return;
        }

        /* �����ϴ�ִ��ʱ�� */
        $this->_tasks[$task]['last_time'] = $this->_now;

        /* �����´ε���ʱ�� */
        $this->_tasks[$task]['due_time']  = $this->get_due_time($this->_tasks[$task]);
    }
}

/**
 *    ���������
 *
 *    @author    Garbin
 *    @usage    none
 */
class BaseTask extends Object
{
    var $_config = null;

    function __construct($config)
    {
        $this->BaseTask($config);
    }

    function BaseTask($config)
    {
        $this->_config = $config;
    }

    /**
     *    ��������
     *
     *    @author    Garbin
     */
    function run() {}
}

?>
