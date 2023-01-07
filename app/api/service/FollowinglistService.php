<?php
/*
 module:		关注
 create_time:	2022-11-24 14:11:11
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Followinglist;
use base\CommonService;

class FollowinglistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Followinglist::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


}

