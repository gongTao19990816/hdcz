<?php
/*
 module:		external_member
 create_time:	2022-12-13 13:15:07
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Externalmember;
use base\CommonService;

class ExternalmemberService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Externalmember::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

