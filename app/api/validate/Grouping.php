<?php
/*
 module:		分组管理验证器
 create_time:	2022-12-01 16:46:29
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class Grouping extends validate
{


    protected $rule = [
        'grouping_name' => ['require', 'unique:grouping'],
    ];

    protected $message = [
        'grouping_name.require' => '分组名称不能为空',
        'grouping_name.unique' => '分组名称已经存在',
    ];

    protected $scene = [
        'add' => ['grouping_name'],
        'update' => ['grouping_name'],
    ];


}

