<?php

namespace app\api\job;

use app\api\model\TaskListDetail;
use think\queue\Job;

class TaskExpire
{
    /**
     * fire是消息队列默认调用的方法
     * @param Job $job 当前的任务对象
     * @param array|mixed $taskdetail_id 发布任务时自定义的数据
     */
    public function fire(Job $job, $taskdetail_id)
    {
        //执行业务处理
        if ($this->doJob($taskdetail_id)) {
            $job->delete();//任务执行成功后删除
        } else {
            //检查任务重试次数
            if ($job->attempts() > 3) $job->delete();
        }
    }

    function doJob($taskdetail_id)
    {
        TaskListDetail::where('taskdetail_id', $taskdetail_id)->save(['status' => TaskListDetail::$expireCode]);
        return true;
    }
}