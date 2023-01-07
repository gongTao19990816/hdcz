<?php
/*
 module:		作品点赞表
 create_time:	2022-11-24 16:28:24
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Likeworks;
use base\CommonService;
use think\exception\ValidateException;

class LikeworksService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Likeworks::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $res = Likeworks::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->likeworks_id;
    }


}

