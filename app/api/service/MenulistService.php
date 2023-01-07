<?php
/*
 module:		菜单管理
 create_time:	2022-12-27 14:16:30
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Menulist;
use base\CommonService;
use think\exception\ValidateException;

class MenulistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Menulist::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\Menulist::class)->scene('add')->check($data);
            $data['pid'] = !is_null($data['pid']) ? $data['pid'] : '0';
            $res = Menulist::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->menulist_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Menulist::class)->scene('update')->check($data);
            $res = Menulist::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

