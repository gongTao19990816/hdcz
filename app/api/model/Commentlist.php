<?php
/*
 module:		评论模型
 create_time:	2022-11-23 19:43:17
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use think\Model;

class Commentlist extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'comment_list_id';

    protected $name = 'comment_list';


}

