<?php
/*
 module:		external_member模型
 create_time:	2022-12-13 13:15:07
 author:		大怪兽
 contact:		
*/

namespace app\admin\model;

use think\Model;

class Externalmember extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'external_member_id';

    protected $name = 'external_member';


}

