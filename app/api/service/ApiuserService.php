<?php
/*
 module:		api_user
 create_time:	2023-01-02 14:46:28
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Apiuser;
use base\CommonService;
use think\exception\ValidateException;

class ApiuserService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Apiuser::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Apiuser::class)->scene('update')->check($data);
            !is_null($data['createtime']) && $data['createtime'] = strtotime($data['createtime']);
            $data['updatetime'] = time();
            $res = Apiuser::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


    /*
     * @Description  账号密码登录
     */
    public static function login($data, $returnField)
    {
        $where['username'] = $data['username'];
        $where['password'] = md5($data['password'] . config('my.password_secrect'));
        try {
            $res = Apiuser::field($returnField)->where($where)->find();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException('请检查用户名或者密码');
        }
        return checkData($res, false);
    }


    /*
     * @Description  修改密码
     */
    public static function UpPass($where, $data)
    {
        try {
            validate(\app\api\validate\Apiuser::class)->scene('UpPass')->check($data);
            $res = Apiuser::where($where)->update(['password' => md5($data['password'] . config('my.password_secrect'))]);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


    /*
     * @Description  创建数据
     */
    public static function register($data)
    {
        try {
            validate(\app\api\validate\Apiuser::class)->scene('register')->check($data);
            $data['password'] = md5($data['password'] . config('my.password_secrect'));
            $data['createtime'] = time();
            $res = Apiuser::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->id;
    }


}

