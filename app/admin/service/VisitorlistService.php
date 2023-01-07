<?php
/*
 module:		来访列表
 create_time:	2022-12-05 20:33:15
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Visitorlist;
use base\CommonService;

class VisitorlistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Visitorlist::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

