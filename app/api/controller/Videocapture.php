<?php
/*
 module:		视频采集
 create_time:	2022-12-13 21:31:44
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Videocapture as VideocaptureModel;
use app\api\service\VideocaptureService;
use think\exception\ValidateException;

class Videocapture extends Common
{


    /**
     * @api {post} /Videocapture/index 01、首页数据列表
     * @apiGroup Videocapture
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [uid] 用户id
     * @apiParam (输入参数：) {string}        [country] 国家
     * @apiParam (输入参数：) {string}        [aweme_id] 视频id
     * @apiParam (输入参数：) {int}            [ifvideo] 1是未下载，0已下载 未下载|1|success,已下载|0|danger
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
        $where['uid'] = $this->request->post('uid', '', 'serach_in');
        $where['country'] = $this->request->post('country', '', 'serach_in');
        $where['aweme_id'] = $this->request->post('aweme_id', '', 'serach_in');
        $where['ifvideo'] = $this->request->post('ifvideo', '', 'serach_in');

        $field = '*';
        $orderby = 'video_capture_id desc';

        $res = VideocaptureService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Videocapture/Download 02、是否下载
     * @apiGroup Videocapture
     * @apiVersion 1.0.0
     * @apiDescription  修改状态
     * @apiParam (输入参数：) {string}            video_capture_id 主键ID
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
    function Download()
    {
        $data['video_capture_id'] = $this->request->post('video_capture_id', '', 'serach_in');
        if (empty($data['video_capture_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['video_capture_id'] = explode(',', $data['video_capture_id']);
        try {
            $res = VideocaptureModel::where($where)->update(['ifvideo' => '0']);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

