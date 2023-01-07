<?php
/*
 module:		菜单管理
 create_time:	2022-12-27 14:14:56
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Menulist;
use base\CommonService;
use think\exception\ValidateException;

class MenulistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Menulist::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\admin\validate\Menulist::class)->scene('add')->check($data);
            $res = Menulist::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->menulist_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            validate(\app\admin\validate\Menulist::class)->scene('update')->check($data);
            $res = Menulist::update($data);
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

