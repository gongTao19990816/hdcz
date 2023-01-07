<?php
/*
 module:		api_user_group模型
 create_time:	2023-01-02 14:52:13
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use think\Model;

class Apiusergroup extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'id';

    protected $name = 'api_user_group';

}

