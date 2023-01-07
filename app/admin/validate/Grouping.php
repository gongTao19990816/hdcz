<?php
/*
 module:		分组管理验证器
 create_time:	2022-12-01 16:06:21
 author:		大怪兽
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Grouping extends validate
{


    protected $rule = [
        'grouping' => ['require'],
    ];

    protected $message = [
        'grouping.require' => '分组名称不能为空',
    ];

    protected $scene = [
        'add' => ['grouping'],
        'update' => ['grouping'],
    ];


}

