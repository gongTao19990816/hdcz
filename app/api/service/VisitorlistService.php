<?php
/*
 module:		来访列表
 create_time:	2022-12-12 12:50:28
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Visitorlist;
use base\CommonService;

class VisitorlistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Visitorlist::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


}

