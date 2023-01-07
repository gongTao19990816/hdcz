<?php
/*
 module:		私信素材库验证器
 create_time:	2022-12-10 15:58:08
 author:		大怪兽
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class PrivateLetter extends validate
{


    protected $rule = [
        'content' => ['require'],
    ];

    protected $message = [
        'content.require' => '内容不能为空',
    ];

    protected $scene = [
    ];


}

