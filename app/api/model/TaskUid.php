<?php

namespace app\api\model;

use think\Model;

class TaskUid extends Model
{
    protected $name = "task_uid";


    function member()
    {
        return $this->belongsTo(Member::class, "uid", "uid")->field('avatar_thumb,uid,nickname,signature,phone_number,backups_name,typecontrol_id')->setFieldType([0]);
    }
}