<?php
/*
 module:		主题内容素材
 create_time:	2022-12-15 20:06:56
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Subjectcontent as SubjectcontentModel;
use app\api\service\SubjectcontentService;
use think\exception\ValidateException;

class Subjectcontent extends Common
{


    /**
     * @api {post} /Subjectcontent/index 01、首页数据列表
     * @apiGroup Subjectcontent
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            [typecontrol_id] 分类
     * @apiParam (输入参数：) {int}            [grouping_id] 分组
     * @apiParam (输入参数：) {int}            [status] 状态 正常|1|success,禁用|0|danger
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
        $where['status'] = $this->request->post('status', '', 'serach_in');


        $usage_count = $this->request->post('usage_count', '', 'serach_in');
        $usage_count = html_entity_decode(trim($usage_count));
        if ($usage_count) {
            if (strstr($usage_count, ">")) {
                $where['usage_count'] = ['>', ltrim($usage_count, '>')];
            } else {
                $where['usage_count'] = ['=', html_entity_decode(trim($usage_count))];
            }
        }
        $field = '*';
        $order = $this->request->post('order', '', 'serach_in');
        $sort = $this->request->post('sort', '', 'serach_in');
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'subjectcontent_id desc';

        $res = SubjectcontentService::indexList($this->apiFormatWhere($where, SubjectcontentModel::class), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }


    function listtable()
    {
        $typecontrol_id = $this->request->post('typecontrol_id', '', 'serach_in');
        $grouping_id = $this->request->post('grouping_id', '', 'serach_in');
        $arrtype_id = $this->pidtype($typecontrol_id);
        $arr = [];
        foreach ($arrtype_id as $typecontrol_id) {
            $zs = db("subjectcontent")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->count();
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

    /**
     * @api {post} /Subjectcontent/add 02、添加
     * @apiGroup Subjectcontent
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            content 内容
     * @apiParam (输入参数：) {string}            use_num 使用数
     * @apiParam (输入参数：) {int}                typecontrol_id 分类
     * @apiParam (输入参数：) {int}                grouping_id 分组
     * @apiParam (输入参数：) {int}                status 状态 正常|1|success,禁用|0|danger
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
        $postField = 'content,use_num,typecontrol_id,grouping_id,add_time,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $content = explode("\n", $data['content']);
        unset($data['content']);
        foreach ($content as $item) {
            $data['api_user_id'] = $this->request->uid;
            $data['content'] = $item;
            $res = SubjectcontentService::add($data);
        }
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Subjectcontent/update 03、修改
     * @apiGroup Subjectcontent
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            subjectcontent_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            content 内容
     * @apiParam (输入参数：) {string}            use_num 使用数
     * @apiParam (输入参数：) {int}                typecontrol_id 分类
     * @apiParam (输入参数：) {int}                grouping_id 分组
     * @apiParam (输入参数：) {string}            add_time 添加时间
     * @apiParam (输入参数：) {int}                status 状态 正常|1|success,禁用|0|danger
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
        $postField = 'subjectcontent_id,content,use_num,typecontrol_id,grouping_id,add_time,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['subjectcontent_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['subjectcontent_id'] = $data['subjectcontent_id'];
        $res = SubjectcontentService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Subjectcontent/delete 04、删除
     * @apiGroup Subjectcontent
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            subjectcontent_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('subjectcontent_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['subjectcontent_id'] = explode(',', $idx);
        try {
            SubjectcontentModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

