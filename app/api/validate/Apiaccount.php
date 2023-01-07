<?php
/*
 module:		api账户表验证器
 create_time:	2022-12-27 15:44:18
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class Apiaccount extends validate
{


    protected $rule = [
        'user' => ['require'],
        'pwd' => ['regex' => '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/'],
        'phone' => ['require', 'regex' => '/^1[3456789]\d{9}$/'],
    ];

    protected $message = [
        'user.require' => '用户名不能为空',
        'pwd.regex' => '6-21位数字字母组合',
        'phone.require' => '联系电话不能为空',
        'phone.regex' => '联系电话格式错误',
    ];

    protected $scene = [
        'add' => ['user', 'pwd', 'phone'],
        'update' => ['user', 'phone'],
        'updatePassword' => ['pwd'],
        'register' => ['user', 'pwd', 'phone'],
    ];


}

