<?php
/*
 module:		关注用户表
 create_time:	2022-12-05 15:53:49
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Followuser;
use base\CommonService;

class FollowuserService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Followuser::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

