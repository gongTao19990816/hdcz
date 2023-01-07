<?php
/*
 module:		作品点赞表
 create_time:	2022-11-24 16:28:24
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Likeworks as LikeworksModel;
use app\api\service\LikeworksService;
use think\exception\ValidateException;

class Likeworks extends Common
{


    /**
     * @api {post} /Likeworks/index 01、首页数据列表
     * @apiGroup Likeworks
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [nickname] 点赞人的昵称
     * @apiParam (输入参数：) {string}        [add_time_start] 点赞时间开始
     * @apiParam (输入参数：) {string}        [add_time_end] 点赞时间结束
     * @apiParam (输入参数：) {int}            [status] 点赞状态 成功|1|success,失败|0|danger
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
        $where['nickname'] = $this->request->post('nickname', '', 'serach_in');

        $add_time_start = $this->request->post('add_time_start', '', 'serach_in');
        $add_time_end = $this->request->post('add_time_end', '', 'serach_in');

        $where['add_time'] = ['between', [strtotime($add_time_start), strtotime($add_time_end)]];
        $where['status'] = $this->request->post('status', '', 'serach_in');

        $field = '*';
        $orderby = 'likeworks_id desc';

        $res = LikeworksService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Likeworks/add 02、添加
     * @apiGroup Likeworks
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            aweme_id 作品的id
     * @apiParam (输入参数：) {string}            token token
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
        $postField = 'aweme_id,token';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $token_str = str_replace('&quot;', '"', $data['token']);
        $token_str = str_replace('&amp;', '&', $token_str);
        // $arr = json_encode($token_str);
        $user = json_decode($token_str, true);
        $data['uid'] = $user['user']['uid'];
        $data['nickname'] = $user['user']['nickname'];
        $data['token'] = $token_str;
        $data['add_time'] = time();
// 		print_r($user);die;
        $res = LikeworksService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }


    function addloglist()
    {

    }

    /**
     * @api {post} /Likeworks/delete 03、删除
     * @apiGroup Likeworks
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            likeworks_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('likeworks_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['likeworks_id'] = explode(',', $idx);
        try {
            LikeworksModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


    function getlike()
    {
        $likearr = LikeworksModel::where('status', 1)->select()->toArray();
        if ($likearr) {
            foreach ($res as &$row) {


            }
        } else {
            throw new ValidateException('现在没有待执行的点赞任务');
        }
    }


}

