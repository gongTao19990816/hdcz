<?php
/*
 module:		菜单管理验证器
 create_time:	2022-12-27 14:16:30
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class Menulist extends validate
{


    protected $rule = [
        'menu_name' => ['require'],
        'url' => ['require'],
    ];

    protected $message = [
        'menu_name.require' => '菜单名称不能为空',
        'url.require' => '菜单路由不能为空',
    ];

    protected $scene = [
        'add' => ['menu_name', 'url'],
        'update' => ['menu_name', 'url'],
    ];


}

