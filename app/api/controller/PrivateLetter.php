<?php
/*
 module:		私信素材库
 create_time:	2022-12-14 17:24:41
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\PrivateLetter as PrivateLetterModel;
use app\api\service\PrivateLetterService;
use think\exception\ValidateException;

class PrivateLetter extends Common
{


    /**
     * @api {post} /PrivateLetter/index 01、私信首页数据列表
     * @apiGroup PrivateLetter
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            [typecontrol_id] 所属分类
     * @apiParam (输入参数：) {int}            [grouping_id] 分组信息
     * @apiParam (输入参数：) {int}            [type] 私信类型 文本话术|0|primary,短链接|1|success,好友名片|2|info,作品转发|3|warning
     * @apiParam (输入参数：) {string}        [content] 内容
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
        $where['typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
        $where['grouping_id'] = $this->request->post('grouping_id', '', 'serach_in');
        $where['type'] = $this->request->post('type', '', 'serach_in');
        $where['content'] = $this->request->post('content', '', 'serach_in');

        $field = '*';
        $orderby = 'privateletter_id desc';

        $res = PrivateLetterService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /PrivateLetter/add 02、添加
     * @apiGroup PrivateLetter
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {int}                typecontrol_id 所属分类
     * @apiParam (输入参数：) {int}                grouping_id 分组信息
     * @apiParam (输入参数：) {int}                type 私信类型 文本话术|0|primary,短链接|1|success,好友名片|2|info,作品转发|3|warning
     * @apiParam (输入参数：) {string}            content 内容 (必填)
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
        $postField = 'typecontrol_id,grouping_id,type,content';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $content = explode("\n", $data['content']);
        unset($data['content']);
        foreach ($content as $item) {
            $data['content'] = $item;
            $data['api_user_id'] = $this->request->uid;
            $res = PrivateLetterService::add($data);
        }
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /PrivateLetter/update 03、修改
     * @apiGroup PrivateLetter
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            privateletter_id 主键ID (必填)
     * @apiParam (输入参数：) {int}                typecontrol_id 所属分类
     * @apiParam (输入参数：) {int}                grouping_id 分组信息
     * @apiParam (输入参数：) {int}                type 私信类型 文本话术|0|primary,短链接|1|success,好友名片|2|info,作品转发|3|warning
     * @apiParam (输入参数：) {string}            content 内容 (必填)
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
        $postField = 'privateletter_id,typecontrol_id,grouping_id,type,content';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['privateletter_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['privateletter_id'] = $data['privateletter_id'];
        $res = PrivateLetterService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /PrivateLetter/delete 04、删除
     * @apiGroup PrivateLetter
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            privateletter_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('privateletter_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['privateletter_id'] = explode(',', $idx);
        try {
            PrivateLetterModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


    function listtable()
    {
        $typecontrol_id = $this->request->post('typecontrol_id', '', 'serach_in');
        $grouping_id = $this->request->post('grouping_id', '', 'serach_in');
        $arrtype_id = $this->pidtype($typecontrol_id);
        $arr = [];
        foreach ($arrtype_id as $typecontrol_id) {
            $zs = db("privateletter")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->count();
            // $yy = db("privateletter")->where(["typecontrol_id"=> $typecontrol_id,'grouping_id'=>$grouping_id])->count();
            $type_title = getTypeParentNames($typecontrol_id);
            $grouping_name = db('grouping')->where('grouping_id', $grouping_id)->value('grouping_name');
            $arr[] = compact("typecontrol_id", "zs", "type_title", "grouping_name", "grouping_id");
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($arr));
    }


    function pidtype($myid)
    {
        $types = db("typecontrol")->select();
        $tree = \utils\Tree::instance()->init($types, "typecontrol_id", "pid");
        return $tree->getChildrenIds($myid, true);
    }


}

