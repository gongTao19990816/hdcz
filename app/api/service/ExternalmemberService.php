<?php
/*
 module:		用户采集
 create_time:	2022-12-13 13:15:50
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Externalmember;
use base\CommonService;

class ExternalmemberService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Externalmember::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


}

