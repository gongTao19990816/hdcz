<?php
/*
 module:		视频任务详情
 create_time:	2022-11-25 13:50:01
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Videotaskdetails as VideotaskdetailsModel;
use app\api\service\VideotaskdetailsService;
use think\exception\ValidateException;

class Videotaskdetails extends Common
{


    /**
     * @api {post} /Videotaskdetails/index 01、首页数据列表
     * @apiGroup Videotaskdetails
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            [videotasks_id] 所属任务
     * @apiParam (输入参数：) {string}        [uid] 上传视频的uid
     * @apiParam (输入参数：) {string}        [video_url] 视频地址
     * @apiParam (输入参数：) {int}            [status] 状态 未开始|1|success,失败|0|danger,成功|2|warning
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
        $where['a.videotasks_id'] = $this->request->post('videotasks_id', '', 'serach_in');
        $where['a.uid'] = $this->request->post('uid', '', 'serach_in');
// 		$where['video_url'] = $this->request->post('video_url', '', 'serach_in');
        $where['a.status'] = $this->request->post('status', '', 'serach_in');

        $field = 'a.*,b.task_name';
        $orderby = 'a.videotaskdetails_id desc';

        $res = VideotaskdetailsService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            if ($row['exe_time'] != 0) {
                $row['exe_time'] = date("Y-m-d H:i:s", $row['exe_time']);
            }

            $row['pay_time'] = date("Y-m-d H:i:s", $row['pay_time']);
            $row['video_num'] = config('my.host_url') . db('material')->where('video_num', $row['video_url'])->value('video_url');

        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Videotaskdetails/add 02、添加
     * @apiGroup Videotaskdetails
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {int}                videotasks_id 所属任务
     * @apiParam (输入参数：) {string}            uid 上传视频的uid
     * @apiParam (输入参数：) {string}            video_url 视频地址
     * @apiParam (输入参数：) {string}            pay_time 上传时间
     * @apiParam (输入参数：) {int}                status 状态 未开始|1|success,失败|0|danger,成功|2|warning
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
        $postField = 'videotasks_id,uid,video_url,pay_time,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = VideotaskdetailsService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Videotaskdetails/update 03、修改
     * @apiGroup Videotaskdetails
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            videotaskdetails_id 主键ID (必填)
     * @apiParam (输入参数：) {int}                videotasks_id 所属任务
     * @apiParam (输入参数：) {string}            uid 上传视频的uid
     * @apiParam (输入参数：) {string}            video_url 视频地址
     * @apiParam (输入参数：) {string}            pay_time 上传时间
     * @apiParam (输入参数：) {int}                status 状态 未开始|1|success,失败|0|danger,成功|2|warning
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
        $postField = 'videotaskdetails_id,videotasks_id,uid,video_url,pay_time,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['videotaskdetails_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['videotaskdetails_id'] = $data['videotaskdetails_id'];
        $res = VideotaskdetailsService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Videotaskdetails/delete 04、删除
     * @apiGroup Videotaskdetails
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            videotaskdetails_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('videotaskdetails_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['videotaskdetails_id'] = explode(',', $idx);
        try {
            VideotaskdetailsModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

