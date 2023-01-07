<?php
/*
 module:		视频任务发布验证器
 create_time:	2022-11-25 13:39:22
 author:		大怪兽
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Videotasks extends validate
{


    protected $rule = [
        'task_name' => ['require'],
    ];

    protected $message = [
        'task_name.require' => '任务名称不能为空',
    ];

    protected $scene = [
        'add' => ['task_name'],
        'update' => ['task_name'],
    ];


}

