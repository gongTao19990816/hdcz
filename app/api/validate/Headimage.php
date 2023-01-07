<?php
/*
 module:		头像库管理验证器
 create_time:	2022-11-14 20:03:34
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

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

