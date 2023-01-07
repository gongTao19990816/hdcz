<?php
/*
 module:		作品点赞表
 create_time:	2022-11-24 15:47:44
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Likeworks;
use base\CommonService;

class LikeworksService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Likeworks::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

