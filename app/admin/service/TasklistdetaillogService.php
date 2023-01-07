<?php
/*
 module:		任务详情日志
 create_time:	2022-12-10 13:41:56
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Tasklistdetaillog;
use base\CommonService;

class TasklistdetaillogService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Tasklistdetaillog::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

