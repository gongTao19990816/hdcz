<?php
/*
 module:		设备管理验证器
 create_time:	2022-11-01 09:52:44
 author:		
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Equipment extends validate
{


    protected $rule = [
        'deviceip' => ['require'],
    ];

    protected $message = [
        'deviceip.require' => '设备ip不能为空',
    ];

    protected $scene = [
        'add' => ['deviceip'],
        'update' => ['deviceip'],
    ];


}

