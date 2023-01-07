<?php
/*
 module:		api_user_group
 create_time:	2023-01-02 14:52:13
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Apiusergroup;
use base\CommonService;
use think\exception\ValidateException;

class ApiusergroupService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Apiusergroup::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['status'] = !is_null($data['status']) ? $data['status'] : '1';
            $data['createtime'] = time();
            $res = Apiusergroup::create($data);
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
            $data['updatetime'] = time();
            $res = Apiusergroup::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

