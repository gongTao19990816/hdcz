<?php
/*
 module:		api_user_rule
 create_time:	2023-01-02 14:55:13
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Apiuserrule as ApiuserruleModel;
use app\api\service\ApiuserruleService;
use think\exception\ValidateException;

class Apiuserrule extends Common
{


    /**
     * @api {post} /Apiuserrule/index 01、首页数据列表
     * @apiGroup Apiuserrule
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [pid] 父ID
     * @apiParam (输入参数：) {string}        [name] 名称
     * @apiParam (输入参数：) {string}        [title] 标题
     * @apiParam (输入参数：) {string}        [remark] 备注
     * @apiParam (输入参数：) {int}            [ismenu] 是否菜单开启|1,关闭|0 开启|1,关闭|0
     * @apiParam (输入参数：) {string}        [createtime_start] 创建时间开始
     * @apiParam (输入参数：) {string}        [createtime_end] 创建时间结束
     * @apiParam (输入参数：) {string}        [weigh] 权重
     * @apiParam (输入参数：) {int}            [status] 状态正常|1|success,禁用|0|danger 正常|1|success,禁用|0|danger
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
        $where['pid'] = $this->request->post('pid', '', 'serach_in');
        $where['name'] = $this->request->post('name', '', 'serach_in');
        $where['title'] = $this->request->post('title', '', 'serach_in');
        $where['remark'] = $this->request->post('remark', '', 'serach_in');
        $where['ismenu'] = $this->request->post('ismenu', '', 'serach_in');

        $createtime_start = $this->request->post('createtime_start', '', 'serach_in');
        $createtime_end = $this->request->post('createtime_end', '', 'serach_in');

        $where['createtime'] = ['between', [strtotime($createtime_start), strtotime($createtime_end)]];
        $where['weigh'] = $this->request->post('weigh', '', 'serach_in');
        $where['status'] = $this->request->post('status', '', 'serach_in');

        $field = '*';
        $orderby = 'id desc';

        $res = ApiuserruleService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Apiuserrule/add 02、添加
     * @apiGroup Apiuserrule
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {string}            pid 父ID
     * @apiParam (输入参数：) {string}            name 名称
     * @apiParam (输入参数：) {string}            title 标题
     * @apiParam (输入参数：) {string}            remark 备注
     * @apiParam (输入参数：) {int}                ismenu 是否菜单开启|1,关闭|0 开启|1,关闭|0
     * @apiParam (输入参数：) {string}            updatetime 更新时间
     * @apiParam (输入参数：) {string}            weigh 权重
     * @apiParam (输入参数：) {int}                status 状态正常|1|success,禁用|0|danger 正常|1|success,禁用|0|danger
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
        $postField = 'pid,name,title,remark,ismenu,createtime,updatetime,weigh,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = ApiuserruleService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Apiuserrule/update 03、修改
     * @apiGroup Apiuserrule
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            id 主键ID (必填)
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {string}            pid 父ID
     * @apiParam (输入参数：) {string}            name 名称
     * @apiParam (输入参数：) {string}            title 标题
     * @apiParam (输入参数：) {string}            remark 备注
     * @apiParam (输入参数：) {int}                ismenu 是否菜单开启|1,关闭|0 开启|1,关闭|0
     * @apiParam (输入参数：) {string}            createtime 创建时间
     * @apiParam (输入参数：) {string}            updatetime 更新时间
     * @apiParam (输入参数：) {string}            weigh 权重
     * @apiParam (输入参数：) {int}                status 状态正常|1|success,禁用|0|danger 正常|1|success,禁用|0|danger
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
        $postField = 'id,pid,name,title,remark,ismenu,createtime,updatetime,weigh,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['id'])) {
            throw new ValidateException('参数错误');
        }
        $where['id'] = $data['id'];
        $res = ApiuserruleService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Apiuserrule/delete 04、删除
     * @apiGroup Apiuserrule
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {string}            ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['id'] = explode(',', $idx);
        try {
            ApiuserruleModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

