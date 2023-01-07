<?php
/*
 module:		账户管理
 create_time:	2022-11-23 16:27:00
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Member;
use base\CommonService;
use think\exception\ValidateException;

class MemberService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Member::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $res = Member::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->member_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            $res = Member::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

