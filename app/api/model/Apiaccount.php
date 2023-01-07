<?php
/*
 module:		api账户表模型
 create_time:	2022-12-27 15:44:18
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use think\Model;

class Apiaccount extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'user_id';

    protected $name = 'api_account';


}

