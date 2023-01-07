<?php
/*
 module:		头像库管理
 create_time:	2022-11-29 19:23:55
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Headimage;
use base\CommonService;
use think\exception\ValidateException;

class HeadimageService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = db('headimage')->field($field)->alias('a')->join('typecontrol b', 'a.typecontrol_id=b.typecontrol_id', 'left')->where($where)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\admin\validate\Headimage::class)->scene('add')->check($data);
            $res = Headimage::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->headimage_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            validate(\app\admin\validate\Headimage::class)->scene('update')->check($data);
            $data['usage_time'] = time();
            $res = Headimage::update($data);
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

