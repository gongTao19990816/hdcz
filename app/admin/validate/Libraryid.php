<?php
/*
 module:		需要关注的id库验证器
 create_time:	2022-12-13 15:10:03
 author:		大怪兽
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Libraryid extends validate
{


    protected $rule = [
        'name' => ['require'],
    ];

    protected $message = [
        'name.require' => '名称不能为空',
    ];

    protected $scene = [
        'add' => ['name'],
        'update' => ['name'],
    ];


}

