<?php
/*
 module:		标签素材
 create_time:	2022-12-02 15:14:38
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Label;
use base\CommonService;
use think\exception\ValidateException;

class LabelService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Label::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\admin\validate\Label::class)->scene('add')->check($data);
            $data['add_time'] = time();
            $res = Label::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->label_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            validate(\app\admin\validate\Label::class)->scene('update')->check($data);
            $data['add_time'] = strtotime($data['add_time']);
            $data['usage_time'] = time();
            $res = Label::update($data);
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

