<?php
/*
 module:		视频评论详情
 create_time:	2022-11-26 13:44:54
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Videocommentdetails as VideocommentdetailsModel;
use app\api\service\VideocommentdetailsService;
use think\exception\ValidateException;

class Videocommentdetails extends Common
{


    /**
     * @api {post} /Videocommentdetails/index 01、首页数据列表
     * @apiGroup Videocommentdetails
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            [videocomments_id] 所属任务
     * @apiParam (输入参数：) {string}        [uid] uid
     * @apiParam (输入参数：) {string}        [aweme_id] 视频的id
     * @apiParam (输入参数：) {int}            [status] 状态 未执行|1|success,失败|0|danger,成功|2|warning
     * @apiParam (输入参数：) {int}            [mode] 执行方式 立即执行|1|success,定时执行|0|danger
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
        $where['videocomments_id'] = $this->request->post('videocomments_id', '', 'serach_in');
        $where['uid'] = $this->request->post('uid', '', 'serach_in');
        $where['aweme_id'] = $this->request->post('aweme_id', '', 'serach_in');
        $where['status'] = $this->request->post('status', '', 'serach_in');
        $where['mode'] = $this->request->post('mode', '', 'serach_in');

        $field = 'a.*,b.task_name';
        $orderby = 'videocommentdetails_id desc';

        $res = VideocommentdetailsService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Videocommentdetails/add 02、添加
     * @apiGroup Videocommentdetails
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {int}                videocomments_id 所属任务
     * @apiParam (输入参数：) {string}            uid uid
     * @apiParam (输入参数：) {string}            aweme_id 视频的id
     * @apiParam (输入参数：) {string}            text 评论内容
     * @apiParam (输入参数：) {string}            pay_time 评论时间
     * @apiParam (输入参数：) {int}                status 状态 未执行|1|success,失败|0|danger,成功|2|warning
     * @apiParam (输入参数：) {string}            failure_reason 失败原因
     * @apiParam (输入参数：) {int}                mode 执行方式 立即执行|1|success,定时执行|0|danger
     * @apiParam (输入参数：) {string}            exe_time 执行时间
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
        $postField = 'videocomments_id,uid,aweme_id,text,pay_time,status,failure_reason,mode,exe_time';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = VideocommentdetailsService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Videocommentdetails/delete 03、删除
     * @apiGroup Videocommentdetails
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            videocommentdetails_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('videocommentdetails_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['videocommentdetails_id'] = explode(',', $idx);
        try {
            VideocommentdetailsModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

