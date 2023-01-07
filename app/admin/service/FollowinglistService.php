<?php
/*
 module:		关注
 create_time:	2022-11-24 14:10:43
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Followinglist;
use base\CommonService;

class FollowinglistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Followinglist::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

