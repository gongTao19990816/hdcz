<?php
/*
 module:		用户作品
 create_time:	2022-11-23 16:28:26
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Membervideo;
use base\CommonService;
use think\exception\ValidateException;

class MembervideoService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = db('membervideo')->field($field)->alias('a')->join('member b', 'a.member_id=b.member_id', 'left')->where($where)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['addtime'] = time();
            $res = Membervideo::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->membervideo_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            !is_null($data['addtime']) && $data['addtime'] = strtotime($data['addtime']);
            $res = Membervideo::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

