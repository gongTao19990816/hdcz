<?php
/*
 module:		用户管理验证器
 create_time:	2023-01-01 21:49:18
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class User extends validate
{


    protected $rule = [
        'user' => ['require'],
        'pwd' => ['require', 'regex' => '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,20}$/'],
    ];

    protected $message = [
        'user.require' => '用户名不能为空',
        'pwd.require' => '密码不能为空',
        'pwd.regex' => '6-21位数字字母组合',
    ];

    protected $scene = [
        'add' => ['user', 'pwd'],
        'update' => ['user'],
        'updatePassword' => ['pwd'],
        'register' => ['user', 'pwd'],
    ];


}

