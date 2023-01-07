<?php
/*
 module:		头像库管理验证器
 create_time:	2022-11-29 19:23:55
 author:		大怪兽
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Headimage extends validate
{


    protected $rule = [
        'image' => ['require'],
    ];

    protected $message = [
        'image.require' => '头像不能为空',
    ];

    protected $scene = [
        'add' => ['image'],
        'update' => ['image'],
    ];


}

