<?php
/*
 module:		分组管理
 create_time:	2022-12-01 16:46:29
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Grouping as GroupingModel;
use app\api\service\GroupingService;
use think\exception\ValidateException;

class Grouping extends Common
{


    /**
     * @api {post} /Grouping/index 01、首页数据列表
     * @apiGroup Grouping
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [grouping_name] 分组名称
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
        $where['grouping_name'] = $this->request->post('grouping_name', '', 'serach_in');

        $field = '*';
        $orderby = 'grouping_id desc';

        $res = GroupingService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['add_time'] = date("Y-m-d H:i:s", $row['add_time']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Grouping/add 02、添加
     * @apiGroup Grouping
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            grouping_name 分组名称 (必填)
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
        $postField = 'grouping_name';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $data['add_time'] = time();
        $data['api_user_id'] = $this->request->uid;
        $res = GroupingService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Grouping/update 03、修改
     * @apiGroup Grouping
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            grouping_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            grouping_name 分组名称 (必填)
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
        $postField = 'grouping_id,grouping_name';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['grouping_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['grouping_id'] = $data['grouping_id'];
        $res = GroupingService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Grouping/delete 04、删除
     * @apiGroup Grouping
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            grouping_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('grouping_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        if(strpos($idx,'3') !== false){
            throw new ValidateException('系统默认分组，不让删除');
        }
        $data['grouping_id'] = explode(',', $idx);
        try {
            GroupingModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

