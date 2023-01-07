<?php
/*
 module:		用户作品
 create_time:	2022-11-23 16:29:39
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Membervideo;
use base\CommonService;
use think\exception\ValidateException;

class MembervideoService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = db('membervideo')->field($field)->alias('a')->join('member b', 'a.member_id=b.member_id', 'left')->where($where)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['addtime'] = time();
            $res = Membervideo::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->membervideo_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            $data['addtime'] = strtotime($data['addtime']);
            $res = Membervideo::update($data);
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

