<?php
/*
 module:		任务明细表
 create_time:	2022-12-09 16:34:00
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\TaskListDetail;
use base\CommonService;

class TaskListDetailService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = TaskListDetail::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

