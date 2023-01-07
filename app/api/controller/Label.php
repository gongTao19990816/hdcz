<?php
/*
 module:		标签素材
 create_time:	2022-12-02 15:15:40
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Label as LabelModel;
use app\api\service\LabelService;
use think\exception\ValidateException;

class Label extends Common
{


    /**
     * @api {post} /Label/index 01、首页数据列表
     * @apiGroup Label
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [label] 标签
     * @apiParam (输入参数：) {int}            [status] 状态 未用|1|success,已用|0|danger
     * @apiParam (输入参数：) {int}            [typecontrol_id] 分类
     * @apiParam (输入参数：) {int}            [grouping_id] 分组
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
        $where['label'] = $this->request->post('label', '', 'serach_in');
        $where['status'] = $this->request->post('status', '', 'serach_in');
        $where['typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
        $where['grouping_id'] = $this->request->post('grouping_id', '', 'serach_in');

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
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'label_id desc';

        $res = LabelService::indexList($this->apiFormatWhere($where, LabelModel::class), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['grouping_name'] = db('grouping')->where('grouping_id', $row['grouping_id'])->value('grouping_name');
            $row['type_title'] = db('typecontrol')->where('typecontrol_id', $row['typecontrol_id'])->value('type_title');
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Label/add 02、添加
     * @apiGroup Label
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            label 标签 (必填)
     * @apiParam (输入参数：) {int}                typecontrol_id 分类 (必填)
     * @apiParam (输入参数：) {int}                grouping_id 分组
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
        $postField = 'label,typecontrol_id,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $label = explode("\n", $data['label']);
        unset($data['label']);
        foreach ($label as $item) {
            $data['api_user_id'] = $this->request->uid;
            $data['label'] = $item;
            $res = LabelService::add($data);
        }

        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Label/update 03、修改
     * @apiGroup Label
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            label_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            label 标签 (必填)
     * @apiParam (输入参数：) {int}                typecontrol_id 分类 (必填)
     * @apiParam (输入参数：) {int}                grouping_id 分组
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
        $postField = 'label_id,label,typecontrol_id,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['label_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['label_id'] = $data['label_id'];
        $res = LabelService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Label/delete 04、删除
     * @apiGroup Label
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            label_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('label_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['label_id'] = explode(',', $idx);
        try {
            LabelModel::destroy($data, true);
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
            $yy = db("label")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->where("status", 1)->count();
            $wy = db("label")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->where("status", 0)->count();
            $type_title = getTypeParentNames($typecontrol_id);
            $grouping_name = db('grouping')->where('grouping_id', $grouping_id)->value('grouping_name');
            $arr[] = compact("typecontrol_id", "yy", "wy", "type_title", "grouping_name", "grouping_id");
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

