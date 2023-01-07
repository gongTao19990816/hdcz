<?php
/*
 module:		视频评论任务验证器
 create_time:	2022-11-26 13:46:15
 author:		大怪兽
 contact:		
*/

namespace app\admin\validate;

use think\validate;

class Videocomments extends validate
{


    protected $rule = [
        'comments' => ['require'],
    ];

    protected $message = [
        'comments.require' => '评论内容不能为空',
    ];

    protected $scene = [
        'add' => ['comments'],
        'update' => ['comments'],
    ];


}

