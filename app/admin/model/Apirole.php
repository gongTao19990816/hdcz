<?php
/*
 module:		分组表模型
 create_time:	2022-12-27 14:39:21
 author:		大怪兽
 contact:		
*/

namespace app\admin\model;

use think\Model;

class Apirole extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'role_id';

    protected $name = 'api_role';


}

