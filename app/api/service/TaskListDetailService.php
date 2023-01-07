<?php
/*
 module:		任务明细表
 create_time:	2022-12-09 17:25:05
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\TaskListDetail;
use base\CommonService;
use think\exception\ValidateException;

class TaskListDetailService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = TaskListDetail::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            $data['status'] = !is_null($data['status']) ? $data['status'] : '1';
            $data['create_time'] = time();
            $res = TaskListDetail::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->tasklistdetail_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            !is_null($data['create_time']) && $data['create_time'] = strtotime($data['create_time']);
            $res = TaskListDetail::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

