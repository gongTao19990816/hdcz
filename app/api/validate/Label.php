<?php
/*
 module:		标签素材验证器
 create_time:	2022-12-02 15:15:40
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class Label extends validate
{


    protected $rule = [
        'label' => ['require', 'unique:label'],
        'typecontrol_id' => ['require'],
    ];

    protected $message = [
        'label.require' => '标签不能为空',
        'label.unique' => '标签已经存在',
        'typecontrol_id.require' => '分类不能为空',
    ];

    protected $scene = [
        'add' => ['label', 'typecontrol_id'],
        'update' => ['label', 'typecontrol_id'],
    ];


}

