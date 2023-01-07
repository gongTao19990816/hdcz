<?php
/*
 module:		api账户表
 create_time:	2022-12-27 15:44:18
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Apiaccount;
use base\CommonService;
use think\exception\ValidateException;

class ApiaccountService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Apiaccount::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\Apiaccount::class)->scene('add')->check($data);
            $data['pwd'] = md5($data['pwd'] . config('my.password_secrect'));
            $data['status'] = !is_null($data['status']) ? $data['status'] : '1';
            $data['create_time'] = time();
            $res = Apiaccount::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->user_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Apiaccount::class)->scene('update')->check($data);
            $res = Apiaccount::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


    /*
     * @Description  修改密码
     */
    public static function updatePassword($where, $data)
    {
        try {
            validate(\app\api\validate\Apiaccount::class)->scene('updatePassword')->check($data);
            $res = Apiaccount::where($where)->update(['pwd' => md5($data['pwd'] . config('my.password_secrect'))]);
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
        $where['user'] = $data['user'];
        $where['pwd'] = md5($data['pwd'] . config('my.password_secrect'));
        try {
            $res = Apiaccount::field($returnField)->where($where)->find();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException('请检查用户名或者密码');
        }
        return checkData($res, false);
    }


    /*
     * @Description  创建数据
     */
    public static function register($data)
    {
        try {
            validate(\app\api\validate\Apiaccount::class)->scene('register')->check($data);
            $data['pwd'] = md5($data['pwd'] . config('my.password_secrect'));
            $res = Apiaccount::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->user_id;
    }


}

