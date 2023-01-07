<?php
/*
 module:		用户采集模型
 create_time:	2022-12-13 13:15:50
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use think\Model;

class Externalmember extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'external_member_id';

    protected $name = 'external_member';


}

