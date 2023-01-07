<?php
/*
 module:		任务明细表模型
 create_time:	2022-12-09 17:25:05
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use think\Model;

class TaskListDetail extends Model
{


    protected $pk = 'tasklistdetail_id';

    protected $name = 'tasklistdetail';


    public static $expireCode = 4; //过期
    public static $successCode = 0; //成功
    public static $failCode = 2;//失败
    public static $receiveCode = 3;//已领取
    public static $waitCode = 5;//等执行
    public static $noStartCode = 1;//未开始

    static function add($taskdetaildata)
    {
        $taskdetail = new self();
        $taskdetail->save($taskdetaildata);
        queue(\app\api\job\TaskExpire::class, $taskdetail->tasklistadetail_id, 60);
        return $taskdetail->tasklistadetail_id;
    }
}

