<?php
/*
 module:		私信素材库
 create_time:	2022-12-10 15:58:08
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\PrivateLetter;
use base\CommonService;

class PrivateLetterService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = PrivateLetter::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

