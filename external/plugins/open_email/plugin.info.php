<?php

return array(
    'id' => 'open_email',
    'hook' => 'after_opening',
    'name' => '�����ʼ�֪ͨ',
    'desc' => '����ɹ�����������ʼ�֪ͨ',
    'author' => 'PaiLa Team',
    'version' => '1.0',
    'config' => array(
        'subject' => array(
            'type' => 'text',
            'text' => '�ʼ�����'
        ),
        'content' => array(
            'type' => 'textarea',
            'text' => '�ʼ�����'
        )
    )
);

?>