<?php
/*
 module:		api_user模型
 create_time:	2023-01-02 14:46:28
 author:		大怪兽
 contact:		
*/

namespace app\api\model;

use app\api\model\Apiusergroup as UserGroup;
use think\Model;

class Apiuser extends Model
{


    protected $connection = 'mysql';

    protected $pk = 'id';

    protected $name = 'api_user';
    protected $append = [
        'group_text',
//        'group',
    ];


    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::where("id", $data['group_id'])->find();
        /*$group_id = self::where("id", $data['id'])->value('group_id');
        $group = UserGroup::where("id", $group_id)->find();
        $group->ruleList = UserRule::where("id", "in", $group->rules)->select();
        return $group;*/
    }

    public function getGroupTextAttr($value, $data)
    {
        return UserGroup::where("id", $data['group_id'])->value("name");
    }
}

