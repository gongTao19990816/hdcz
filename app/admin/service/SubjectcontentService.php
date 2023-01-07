<?php
/*
 module:		主题内容素材
 create_time:	2022-12-10 16:41:59
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Subjectcontent;
use base\CommonService;

class SubjectcontentService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Subjectcontent::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


}

