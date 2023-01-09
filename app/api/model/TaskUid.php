<?php

namespace app\api\model;

use think\Model;

class TaskUid extends Model
{
    protected $name = "task_uid";


    function member()
    {
        return $this->belongsTo(Member::class, "uid", "uid")->setFieldType([0]);
    }
}