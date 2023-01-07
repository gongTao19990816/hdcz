<?php
/*
 module:		素材管理验证器
 create_time:	2022-11-14 20:14:42
 author:		大怪兽
 contact:		
*/

namespace app\api\validate;

use think\validate;

class Material extends validate
{


    protected $rule = [
// 		'pic'=>['require'],
        'typecontrol_id' => ['require'],
        'video_url' => ['require'],
    ];

    protected $message = [
// 		'pic.require'=>'封面图片不能为空',
        'typecontrol_id.require' => '视频类型不能为空',
        'video_url.require' => '视频地址不能为空',
    ];

    protected $scene = [
        'add' => ['typecontrol_id', 'video_url'],
        'update' => ['typecontrol_id', 'video_url'],
    ];


}

