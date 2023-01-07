<?php
/*
 module:		视频采集模型
 create_time:	2022-12-13 21:41:05
 author:		大怪兽
 contact:		
*/

namespace app\admin\model;

use think\Model;

class Videocapture extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'video_capture_id';

    protected $name = 'video_capture';


}

