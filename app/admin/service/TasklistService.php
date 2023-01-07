<?php
/*
 module:		任务表
 create_time:	2022-12-09 16:25:31
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Tasklist;
use base\CommonService;
use think\exception\ValidateException;

class TasklistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Tasklist::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            $data['create_time'] = time();
            $res = Tasklist::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->tasklist_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            $data['create_time'] = strtotime($data['create_time']);
            $res = Tasklist::update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res;
    }


}

