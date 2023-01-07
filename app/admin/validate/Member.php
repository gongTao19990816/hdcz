<?php
/*
 module:		账户管理验证器
 create_time:	2022-11-03 15:03:02
 author:		
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Member extends validate
{


    protected $rule = [
        'username' => ['require'],
        'pass' => ['require'],
        'fans' => ['regex' => '/^[0-9]*$/'],
        'dianzan' => ['regex' => '/^[0-9]*$/'],
        'play' => ['regex' => '/^[0-9]*$/'],
    ];

    protected $message = [
        'username.require' => '账号不能为空',
        'pass.require' => '密码不能为空',
        'fans.regex' => '粉丝格式错误',
        'dianzan.regex' => '点赞格式错误',
        'play.regex' => '播放格式错误',
    ];

    protected $scene = [
        'update' => ['username', 'pass'],
        'add' => ['username', 'pass'],
    ];


}

