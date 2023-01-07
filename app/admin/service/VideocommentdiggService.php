<?php
/*
 module:		作品评论点赞
 create_time:	2022-12-05 18:11:42
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Videocommentdigg;
use base\CommonService;

class VideocommentdiggService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Videocommentdigg::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

