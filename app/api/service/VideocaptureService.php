<?php
/*
 module:		视频采集
 create_time:	2022-12-13 21:31:44
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Videocapture;
use base\CommonService;

class VideocaptureService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Videocapture::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


}

