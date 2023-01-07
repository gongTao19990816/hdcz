<?php
/*
 module:		api_user
 create_time:	2023-01-02 14:26:58
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Apiuser;
use base\CommonService;
use think\exception\ValidateException;

class ApiuserService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Apiuser::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\admin\validate\Apiuser::class)->scene('add')->check($data);
            $data['password'] = md5($data['password'] . config('my.password_secrect'));
            $data['createtime'] = time();
            $res = Apiuser::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            validate(\app\admin\validate\Apiuser::class)->scene('update')->check($data);
            $data['createtime'] = strtotime($data['createtime']);
            $data['updatetime'] = time();
            $res = Apiuser::update($data);
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

