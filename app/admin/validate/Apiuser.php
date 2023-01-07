<?php
/*
 module:		api_user验证器
 create_time:	2023-01-02 14:26:58
 author:		大怪兽
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Apiuser extends validate
{


    protected $rule = [
        'email' => ['regex' => '/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/'],
        'phone' => ['regex' => '/^1[3456789]\d{9}$/'],
    ];

    protected $message = [
        'email.regex' => '电子邮箱格式错误',
        'phone.regex' => '手机号格式错误',
    ];

    protected $scene = [
        'add' => ['email', 'phone'],
        'update' => ['email', 'phone'],
    ];


}

