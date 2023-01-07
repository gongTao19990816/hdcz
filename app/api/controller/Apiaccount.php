<?php
/*
 module:		api账户表
 create_time:	2022-12-27 15:44:17
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Apiaccount as ApiaccountModel;
use app\api\service\ApiaccountService;
use think\exception\ValidateException;

class Apiaccount extends Common
{


    /**
     * @api {post} /Apiaccount/index 01、首页数据列表
     * @apiGroup Apiaccount
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [user] 用户名
     * @apiParam (输入参数：) {string}        [role_id] 所属分组
     * @apiParam (输入参数：) {int}            [status] 状态 正常|1,禁用|0
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
        $where['user'] = $this->request->post('user', '', 'serach_in');
        $where['role_id'] = $this->request->post('role_id', '', 'serach_in');
        $where['status'] = $this->request->post('status', '', 'serach_in');

        $field = '*';
        $orderby = 'user_id desc';

        $res = ApiaccountService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Apiaccount/add 02、添加
     * @apiGroup Apiaccount
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {string}            name 真实姓名
     * @apiParam (输入参数：) {string}            user 用户名 (必填)
     * @apiParam (输入参数：) {string}            pwd 密码
     * @apiParam (输入参数：) {string}            role_id 所属分组
     * @apiParam (输入参数：) {string}            note 备注
     * @apiParam (输入参数：) {int}                status 状态 正常|1,禁用|0
     * @apiParam (输入参数：) {string}            phone 联系电话 (必填)
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
        $postField = 'name,user,pwd,role_id,note,status,create_time,phone';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = ApiaccountService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Apiaccount/update 03、修改
     * @apiGroup Apiaccount
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            user_id 主键ID (必填)
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {string}            name 真实姓名
     * @apiParam (输入参数：) {string}            user 用户名 (必填)
     * @apiParam (输入参数：) {string}            role_id 所属分组
     * @apiParam (输入参数：) {string}            note 备注
     * @apiParam (输入参数：) {int}                status 状态 正常|1,禁用|0
     * @apiParam (输入参数：) {string}            phone 联系电话 (必填)
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
        $postField = 'user_id,name,user,role_id,note,status,phone';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['user_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['user_id'] = $data['user_id'];
        $res = ApiaccountService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Apiaccount/delete 04、删除
     * @apiGroup Apiaccount
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {string}            user_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('user_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['user_id'] = explode(',', $idx);
        try {
            ApiaccountModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Apiaccount/updatePassword 05、修改密码
     * @apiGroup Apiaccount
     * @apiVersion 1.0.0
     * @apiDescription  修改密码
     * @apiParam (输入参数：) {string}            user_id 主键ID
     * @apiParam (输入参数：) {string}            pwd 新密码(必填)
     * @apiParam (输入参数：) {string}            repwd 重复密码(必填)
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
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
    function updatePassword()
    {
        $postField = 'user_id,pwd,repwd';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['user_id'])) {
            throw new ValidateException('参数错误');
        }
        if (empty($data['pwd'])) {
            throw new ValidateException('密码不能为空');
        }
        if ($data['pwd'] <> $data['repwd']) {
            throw new ValidateException('两次密码输入不一致');
        }
        $where['user_id'] = $data['user_id'];
        $res = ApiaccountService::updatePassword($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Apiaccount/login 06、登录
     * @apiGroup Apiaccount
     * @apiVersion 1.0.0
     * @apiDescription  账号密码登录
     * @apiParam (输入参数：) {string}            captcha 图片验证码
     * @apiParam (输入参数：) {string}            user 登录用户名
     * @apiParam (输入参数：) {string}            pwd 登录密码
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
    function login()
    {
        $postField = 'user,pwd';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['user']) || empty($data['pwd'])) throw new ValidateException('账号或密码不能为空');
        $returnField = 'user_id,user,pwd';
        $res = ApiaccountService::login($data, $returnField);
        return $this->ajaxReturn($this->successCode, '登陆成功', $res, $this->setToken($res['user_id']));
    }

    /**
     * @api {post} /Apiaccount/register 07、注册
     * @apiGroup Apiaccount
     * @apiVersion 1.0.0
     * @apiDescription  创建数据
     * @apiParam (输入参数：) {string}            name 真实姓名
     * @apiParam (输入参数：) {string}            user 用户名 (必填)
     * @apiParam (输入参数：) {string}            pwd 密码
     * @apiParam (输入参数：) {string}            phone 联系电话 (必填)
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
    function register()
    {
        $postField = 'name,user,pwd,phone';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = ApiaccountService::register($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }


}

