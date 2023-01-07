<?php
/*
 module:		api_user_rule
 create_time:	2023-01-02 14:55:13
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Apiuserrule;
use base\CommonService;
use think\exception\ValidateException;

class ApiuserruleService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Apiuserrule::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['pid'] = !is_null($data['pid']) ? $data['pid'] : '0';
            $data['ismenu'] = !is_null($data['ismenu']) ? $data['ismenu'] : '1';
            $data['createtime'] = time();
            $data['status'] = !is_null($data['status']) ? $data['status'] : '1';
            $res = Apiuserrule::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            !is_null($data['createtime']) && $data['createtime'] = strtotime($data['createtime']);
            $data['updatetime'] = time();
            $res = Apiuserrule::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

