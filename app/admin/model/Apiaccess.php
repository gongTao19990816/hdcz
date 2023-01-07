<?php
/*
 module:		api对应的权限表模型
 create_time:	2022-12-27 14:38:43
 author:		大怪兽
 contact:		
*/

namespace app\admin\model;

use think\Model;

class Apiaccess extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'id';

    protected $name = 'api_access';


}

