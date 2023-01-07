<?php
/*
 module:		api_user
 create_time:	2023-01-02 14:46:28
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Apiuser as ApiuserModel;
use app\api\model\Apiuserrule as UserRule;
use app\api\service\ApiuserService;
use think\exception\ValidateException;
use utils\Tree;

class Apiuser extends Common
{
    protected $noNeedLogin = ["Apiuser/login", "Apiuser/register","Apiuser/ImLogin"];
    protected $noNeedRight = ['apiuser/get_rule_list', 'apiuser/get_rule_tree', 'apiuser/get_auth_rule_list', 'apiuser/get_auth_rule_tree', 'apiuser/getCode'];

    /**
     * @api {get} /Apiuser/index 01、首页数据列表
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function index()
    {
        $limit = $this->request->get('limit', 20, 'intval');
        $page = $this->request->get('page', 1, 'intval');

        $where = [];
        $where['group_id'] = $this->request->get('group_id', '', 'serach_in');
        $where['username'] = $this->request->get('username', '', 'serach_in');
        $where['nickname'] = $this->request->get('nickname', '', 'serach_in');
        
        $createtime_start = $this->request->get('createtime_start', '', 'serach_in');
        $createtime_end = $this->request->get('createtime_end', '', 'serach_in');

        $where['createtime'] = ['between', [strtotime($createtime_start), strtotime($createtime_end)]];
        $where['status'] = $this->request->get('status', '', 'serach_in');
        $where['mobile'] = $this->request->get('mobile', '', 'serach_in');

        $field = '*';
        $orderby = 'id desc';

        $res = ApiuserService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row){
            // $row['group_name'] = db('api_user_group')->where('id',$row['group_id'])->value('name');
            $row['createtime'] =date("Y-m-d H:i:s", $row['createtime']);
            $row['updatetime'] = date("Y-m-d H:i:s", $row['updatetime']);
            if(!$row['group_id']){
                $row['group_id'] = "";
            }
            
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }


    /**
     * @api {get} /Apiuser/get_rule_list 01、获取路由规则列表
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  获取管理人员的路由规则列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     */
    function get_rule_list()
    {
        $rules = UserRule::where('status', 'normal')->field('id,pid,name,title,ismenu')->select();
        return $this->ajaxReturn($this->successCode, '返回成功', $rules);
    }


    /**
     * @api {get} /Apiuser/get_rule_tree 01、获取路由规则树形列表
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  获取路由规则树形列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     */
    function get_rule_tree()
    {
        $rules = UserRule::where('status', 'normal')->field('id,pid,name,title,ismenu')->select();
        $tree = Tree::instance()->init($rules)->getTreeArray(0);
        return $this->ajaxReturn($this->successCode, '返回成功', $tree);
    }

    /**
     * @api {get} /Apiuser/get_auth_rule_list 01、获取当前登录的账户的路由规则列表
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  获取管理人员的路由规则列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     */
    function get_auth_rule_list()
    {
        return $this->ajaxReturn($this->successCode, '返回成功', $this->auth->getRuleList());
    }


    /**
     * @api {get} /Apiuser/get_auth_rule_tree 01、获取当前登录的账户的路由规则树形列表
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  获取路由规则树形列表
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     */
    function get_auth_rule_tree()
    {
        $tree = Tree::instance()->init($this->auth->getRuleList())->getTreeArray(0);
        return $this->ajaxReturn($this->successCode, '返回成功', $tree);
    }

    /**
     * @api {post} /Apiuser/update 02、修改
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            id 主键ID (必填)
     * @apiHeader {String} Authorization 用户授权token
     * @apiHeaderExample {json} Header-示例:
     * "Authorization: eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"
     * @apiParam (输入参数：) {string}            group_id 组别ID
     * @apiParam (输入参数：) {string}            username 用户名
     * @apiParam (输入参数：) {string}            email 电子邮箱
     * @apiParam (输入参数：) {string}            createtime 创建时间
     * @apiParam (输入参数：) {string}            updatetime 更新时间
     * @apiParam (输入参数：) {int}                status 状态正常|0|success,禁用|1|danger 正常|0|success,禁用|1|danger
     * @apiParam (输入参数：) {string}            phone 手机号
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
        $postField = 'id,group_id,username,email,createtime,updatetime,status,mobile,nickname';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['id'])) {
            throw new ValidateException('参数错误');
        }
        $where['id'] = $data['id'];
        $res = ApiuserService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Apiuser/delete 03、删除
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  删除
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
            ApiuserModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Apiuser/login 04、登录
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  账号密码登录
     * @apiParam (输入参数：) {string}            captcha 图片验证码
     * @apiParam (输入参数：) {string}            username 登录用户名
     * @apiParam (输入参数：) {string}            password 登录密码
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
        $postField = 'username,password';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['username']) || empty($data['password'])) throw new ValidateException('账号或密码不能为空');
        $returnField = 'id,username,group_id';
        $res = ApiuserService::login($data, $returnField);
        return $this->ajaxReturn($this->successCode, '登陆成功', $res, $this->setToken($res['id']));
    }


    /**
     * @api {post} /Apiuser/ImLogin 04、im登录
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  账号密码登录
     * @apiParam (输入参数：) {string}            captcha 图片验证码
     * @apiParam (输入参数：) {string}            username 登录用户名
     * @apiParam (输入参数：) {string}            password 登录密码
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
    function ImLogin(){
        $postField = 'username,password';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['username']) || empty($data['password'])) throw new ValidateException('账号或密码不能为空');
        $returnField = 'id,username,group_id';
        $res = ApiuserService::login($data, $returnField);
        return $this->ajaxReturn($this->successCode, '登陆成功', $res);
    }

    /**
     * @api {post} /Apiuser/UpPass 05、修改密码
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  修改密码
     * @apiParam (输入参数：) {string}            id 主键ID
     * @apiParam (输入参数：) {string}            password 新密码(必填)
     * @apiParam (输入参数：) {string}            repassword 重复密码(必填)
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
    function UpPass()
    {
        $postField = 'id,password,repassword';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['id'])) {
            throw new ValidateException('参数错误');
        }
        if (empty($data['password'])) {
            throw new ValidateException('密码不能为空');
        }
        if ($data['password'] <> $data['repassword']) {
            throw new ValidateException('两次密码输入不一致');
        }
        $where['id'] = $data['id'];
        $res = ApiuserService::UpPass($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Apiuser/register 06、注册
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  创建数据
     * @apiParam (输入参数：) {string}            username 用户名
     * @apiParam (输入参数：) {string}            password 密码
     * @apiParam (输入参数：) {string}            email 电子邮箱
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功","data":[],"token":"eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function register()
    {
        $postField = 'username,password,mobile,createtime';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = ApiuserService::register($data);
        return $this->ajaxReturn($this->successCode, '操作成功', ApiuserModel::where("id", $res)->field('id,username,group_id')->find()->toArray(), $this->setToken($res));
    }
    
     /**
     * @api {post} /Apiuser/add 06、注册
     * @apiGroup Apiuser
     * @apiVersion 1.0.0
     * @apiDescription  创建数据
     * @apiParam (输入参数：) {string}            username 用户名
     * @apiParam (输入参数：) {string}            password 密码
     * @apiParam (输入参数：) {string}            mobile 电子邮箱
     * @apiParam (输入参数：) {string}            mobile 手机号
     * @apiParam (输入参数：) {string}            expire_date 到期时间（2023-1-5）永久到期不传
     * apiParam (输入参数：)  {string}            remark 备注
     * @apiParam (输入参数：) {string}            group_id 分组id
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功","data":[],"token":"eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOjM2NzgsImF1ZGllbmNlIjoid2ViIiwib3BlbkFJZCI6MTM2NywiY3JlYXRlZCI6MTUzMzg3OTM2ODA0Nywicm9sZXMiOiJVU0VSIiwiZXhwIjoxNTM0NDg0MTY4fQ.Gl5L-NpuwhjuPXFuhPax8ak5c64skjDTCBC64N_QdKQ2VT-zZeceuzXB9TqaYJuhkwNYEhrV3pUx1zhMWG7Org"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function add(){
        // $arr = strtotime('2023-01-05');
        // var_dump($arr);die;
       $postField = 'username,password,nickname,mobile,createtime,expire_date,group_id,remark';
       $data = $this->request->only(explode(',', $postField), 'post', null);
       if($data['expire_date']){
           $data['expire_date'] = strtotime($data['expire_date']);
       }
       $res = ApiuserService::register($data);
       return $this->ajaxReturn($this->successCode, '操作成功',$res);
    }


}

