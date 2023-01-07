<?php
/*
 module:		个性签名库
 create_time:	2022-11-16 12:28:22
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Autograph;
use base\CommonService;
use think\exception\ValidateException;

class AutographService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = db('autograph')->field($field)->alias('a')->join('typecontrol b', 'a.typecontrol_id=b.typecontrol_id', 'left')->where($where)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\admin\validate\Autograph::class)->scene('add')->check($data);
            $res = Autograph::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->autograph_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            validate(\app\admin\validate\Autograph::class)->scene('update')->check($data);
            $data['usage_time'] = time();
            $res = Autograph::update($data);
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

