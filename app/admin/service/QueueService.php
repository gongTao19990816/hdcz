<?php
/*
 module:		队列任务测试
 create_time:	2022-12-07 11:55:34
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Queue;
use base\CommonService;
use think\exception\ValidateException;

class QueueService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Queue::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['createtime'] = time();
            $data['lasttime'] = strtotime($data['lasttime']);
            $data['filshtime'] = strtotime($data['filshtime']);
            $res = Queue::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->queue_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            $data['createtime'] = strtotime($data['createtime']);
            $data['lasttime'] = strtotime($data['lasttime']);
            $data['filshtime'] = strtotime($data['filshtime']);
            $res = Queue::update($data);
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

