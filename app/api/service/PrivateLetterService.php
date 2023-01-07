<?php
/*
 module:		私信素材库
 create_time:	2022-12-14 17:24:41
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\PrivateLetter;
use base\CommonService;
use think\exception\ValidateException;

class PrivateLetterService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = PrivateLetter::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\PrivateLetter::class)->scene('add')->check($data);
            $data['type'] = !is_null($data['type']) ? $data['type'] : '0';
            $res = PrivateLetter::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->privateletter_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\PrivateLetter::class)->scene('update')->check($data);
            $res = PrivateLetter::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

