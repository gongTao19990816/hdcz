<?php
/*
 module:		分组角色表模型
 create_time:	2022-12-27 14:50:27
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use think\Model;

class Apirole extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'role_id';

    protected $name = 'api_role';


}

