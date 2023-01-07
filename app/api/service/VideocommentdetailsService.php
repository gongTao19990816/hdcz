<?php
/*
 module:		视频评论详情
 create_time:	2022-11-26 13:44:54
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Videocommentdetails;
use base\CommonService;
use think\exception\ValidateException;

class VideocommentdetailsService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = db('videocommentdetails')->field($field)->alias('a')->join('videocomments b', 'a.videocomments_id=b.videocomments_id', 'left')->where($where)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['mode'] = !is_null($data['mode']) ? $data['mode'] : '1';
            $data['exe_time'] = strtotime($data['exe_time']);
            $res = Videocommentdetails::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->videocommentdetails_id;
    }


}

