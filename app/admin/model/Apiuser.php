<?php
/*
 module:		api_user模型
 create_time:	2023-01-02 14:26:58
 author:		大怪兽
 contact:		
*/

namespace app\admin\model;

use think\Model;

class Apiuser extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'id';

    protected $name = 'api_user';


}

