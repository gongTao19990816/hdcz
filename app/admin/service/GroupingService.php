<?php
/*
 module:		分组管理
 create_time:	2022-12-01 16:06:21
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Grouping;
use base\CommonService;
use think\exception\ValidateException;

class GroupingService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Grouping::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\admin\validate\Grouping::class)->scene('add')->check($data);
            $data['add_time'] = time();
            $res = Grouping::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->grouping_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            validate(\app\admin\validate\Grouping::class)->scene('update')->check($data);
            $data['add_time'] = strtotime($data['add_time']);
            $res = Grouping::update($data);
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

