<?php
/*
 module:		粉丝模型
 create_time:	2022-11-24 15:16:12
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use think\Model;

class Fanslist extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'fanslist_id';

    protected $name = 'fanslist';


}

