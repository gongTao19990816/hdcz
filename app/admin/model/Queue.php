<?php
/*
 module:		队列任务测试模型
 create_time:	2022-12-07 11:55:34
 author:		大怪兽
 contact:		
*/

namespace app\admin\model;

use think\Model;

class Queue extends Model
{


    protected $pk = 'queue_id';

    protected $name = 'queue';


}

