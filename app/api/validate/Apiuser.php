<?php
/*
 module:		api_user验证器
 create_time:	2023-01-02 14:46:28
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class Apiuser extends validate
{


    protected $rule = [
        'password' => ['regex' => '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/'],
        'email' => ['regex' => '/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/'],
        'phone' => ['regex' => '/^1[3456789]\d{9}$/'],
    ];

    protected $message = [
        'password.regex' => '密码格式错误',
        'email.regex' => '电子邮箱格式错误',
        'phone.regex' => '手机号格式错误',
    ];

    protected $scene = [
        'update' => ['email', 'phone'],
        'UpPass' => ['password'],
        'register' => ['password', 'email'],
    ];


}

