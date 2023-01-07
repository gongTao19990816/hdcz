<?php
/*
 module:		视频评论详情
 create_time:	2022-11-26 13:44:28
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Videocommentdetails;
use base\CommonService;
use think\exception\ValidateException;

class VideocommentdetailsService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = db('videocommentdetails')->field($field)->alias('a')->join('videocomments b', 'a.videocomments_id=b.videocomments_id', 'left')->where($where)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            $data['exe_time'] = strtotime($data['exe_time']);
            $res = Videocommentdetails::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->videocommentdetails_id;
    }


}

