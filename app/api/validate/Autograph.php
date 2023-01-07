<?php
/*
 module:		个性签名库验证器
 create_time:	2022-11-16 12:32:30
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class Autograph extends validate
{


    protected $rule = [
        'autograph' => ['require'],
    ];

    protected $message = [
        'autograph.require' => '签名不能为空',
    ];

    protected $scene = [
        'add' => ['autograph'],
        'update' => ['autograph'],
    ];


}

