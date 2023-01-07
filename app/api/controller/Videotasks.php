<?php
/*
 module:		视频任务发布
 create_time:	2022-11-25 13:42:04
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Videotasks as VideotasksModel;
use app\api\service\VideotaskdetailsService;
use app\api\service\VideotasksService;
use think\exception\ValidateException;

class Videotasks extends Common
{


    /**
     * @api {post} /Videotasks/index 01、首页数据列表
     * @apiGroup Videotasks
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [task_name] 任务名称
     * @apiParam (输入参数：) {string}        [release_time_start] 任务发布时间开始
     * @apiParam (输入参数：) {string}        [release_time_end] 任务发布时间结束
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function index()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = [];
        $where['task_name'] = $this->request->post('task_name', '', 'serach_in');

        $release_time_start = $this->request->post('release_time_start', '', 'serach_in');
        $release_time_end = $this->request->post('release_time_end', '', 'serach_in');

        $where['release_time'] = ['between', [strtotime($release_time_start), strtotime($release_time_end)]];

        $field = '*';
        $orderby = 'videotasks_id desc';

        $res = VideotasksService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['release_time'] = date("Y-m-d H:i:s", $row['release_time']);
            $row['total'] = db('videotaskdetails')->where('videotasks_id', $row['videotasks_id'])->count();
            $sudata['videotasks_id'] = $row['videotasks_id'];
            $sudata['status'] = 2;
            $row['su_total'] = db('videotaskdetails')->where($sudata)->count();
            $ardata['videotasks_id'] = $row['videotasks_id'];
            $ardata['status'] = 0;
            $row['fail_total'] = db('videotaskdetails')->where($ardata)->count();
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Videotasks/add 02、添加
     * @apiGroup Videotasks
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            task_name 任务名称 (必填)
     * @apiParam (输入参数：) {string}            video_description 视频描述
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function add()
    {
        $postField = 'task_name,video_description,video_num,uid,mode,exe_time';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $ress = VideotasksService::add($data);
        $video_num = explode(",", $data['video_num']);
        unset($data['video_num']);
        foreach ($video_num as $item) {
            $data['video_url'] = $item;
            $data['videotasks_id'] = $ress;
            $data['texts'] = $data['video_description'];
            $data['mode'] = $data['mode'];
            $data['exe_time'] = strtotime($data['exe_time']);
            $res = VideotaskdetailsService::add($data);
        }
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Videotasks/update 03、修改
     * @apiGroup Videotasks
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            videotasks_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            task_name 任务名称 (必填)
     * @apiParam (输入参数：) {string}            video_description 视频描述
     * @apiParam (输入参数：) {string}            release_time 任务发布时间
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function update()
    {
        $postField = 'videotasks_id,task_name,video_description,release_time';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['videotasks_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['videotasks_id'] = $data['videotasks_id'];
        $res = VideotasksService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Videotasks/delete 04、删除
     * @apiGroup Videotasks
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            videotasks_ids 主键id 注意后面跟了s 多数据删除
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    function delete()
    {
        $idx = $this->request->post('videotasks_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['videotasks_id'] = explode(',', $idx);
        try {
            VideotasksModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    ///Videotasks/pay_video
    function pay_video()
    {
        set_time_limit(0);
        $arrwhere['status'] = 1;
        $arrwhere['mode'] = 1;
        $arr = db('videotaskdetails')->where($arrwhere)->field('videotaskdetails_id,uid,video_url,texts')->find();
        //视频上传
        $video_url = db('material')->where('video_num', $arr['video_url'])->value('video_url');
        // print_r($video_url);die;
        if ($arr) {
            $url = 'http://47.245.30.4:9999/rest/VideoInfo/upload';
            $file = '/www/wwwroot/192.168.4.30/admin.com/public' . $video_url;
            $uid = $arr['uid'];
            $text = $arr['texts'];
            $arrs = $this->post_files($url, $file, $uid, $text);
            $where['videotaskdetails_id'] = $arr['videotaskdetails_id'];
            if ($arrs['result'] == 0) {
                $success['status'] = 2;
                $success['pay_time'] = time();
                db('videotaskdetails')->where($where)->update($success);
                echo '成功' . $arr['videotaskdetails_id'];
            } else {
                $success['status'] = 0;
                $success['pay_time'] = time();
                $success['failure_reason'] = $arrs['message'];
                db('videotaskdetails')->where($where)->update($success);
                echo '失败' . $arr['videotaskdetails_id'];
            }
        } else {
            echo '没有任务';
        }
        // var_dump($arr);die;

    }

    ///Videotasks/payTimeVideo
    function payTimeVideo()
    {
        // echo '1111';die;
        set_time_limit(0);
        $arrwhere['status'] = 1;
        $arrwhere['mode'] = 0;
        $arr = db('videotaskdetails')->where($arrwhere)->whereTime('exe_time', '<=', time())->field('uid,video_url,texts')->find();
        // print_r($arr);die;
        //视频上传
        $video_url = db('material')->where('video_num', $arr['video_url'])->value('video_url');

        if ($arr) {
            $url = 'http://47.245.30.4:9999/rest/VideoInfo/upload';
            $file = '/www/wwwroot/192.168.4.30/admin.com/public' . $video_url;
            $uid = $arr['uid'];
            $text = $arr['texts'];
            $arrs = $this->post_files($url, $file, $uid, $text);
            // var_dump($arrs);die;
            $where['videotaskdetails_id'] = $arr['videotaskdetails_id'];
            if ($arrs['result'] == 0) {
                $success['status'] = 2;
                $success['pay_time'] = time();
                db('videotaskdetails')->where($where)->update($success);
                echo '成功' . $arr['videotaskdetails_id'];
            } else {
                $success['status'] = 0;
                $success['pay_time'] = time();
                $success['failure_reason'] = $arrs['message'];
                db('videotaskdetails')->where($where)->update($success);
                echo '失败' . $arr['videotaskdetails_id'];
            }
        } else {
            echo '没有任务';
        }
    }


}

