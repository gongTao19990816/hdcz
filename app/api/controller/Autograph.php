<?php
/*
 module:		个性签名库
 create_time:	2022-11-16 12:32:30
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Autograph as AutographModel;
use app\api\service\AutographService;
use think\exception\ValidateException;

class Autograph extends Common
{


    /**
     * @api {post} /Autograph/index 01、个性签名数据列表
     * @apiGroup Autograph
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [typecontrol_id] 类型
     * @apiParam (输入参数：) {int}            [status] 状态 未用|1|success,已用|0|danger
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
        $where['b.typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
        $where['a.status'] = $this->request->post('status', '', 'serach_in');
        $where['a.grouping_id'] = $this->request->post('grouping_id', '', 'serach_in');
        $usage_count = $this->request->post('usage_count', '', 'serach_in');
        $usage_count = html_entity_decode(trim($usage_count));
        if ($usage_count) {
            if (strstr($usage_count, ">")) {
                $where['a.usage_count'] = ['>', ltrim($usage_count, '>')];
            } else {
                $where['a.usage_count'] = ['=', html_entity_decode(trim($usage_count))];
            }
        }
        $field = 'a.*,b.type_title';
        $order = $this->request->post('order', '', 'serach_in');
        $sort = $this->request->post('sort', '', 'serach_in');
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'autograph_id desc';

        $res = AutographService::indexList($this->apiFormatWhere($where, AutographModel::class), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            if ($row['grouping_id']) {
                $row['grouping_name'] = db('grouping')->where('grouping_id', $row['grouping_id'])->value('grouping_name');
            }
        }
// 		$this->inspectdata();
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }


    function listtable()
    {
        $typecontrol_id = $this->request->post('typecontrol_id', '', 'serach_in');
        $grouping_id = $this->request->post('grouping_id', '', 'serach_in');
        $arrtype_id = $this->pidtype($typecontrol_id);
        $arr = [];
        foreach ($arrtype_id as $typecontrol_id) {
            $wy = db("autograph")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->where("status", 1)->count();
            $yy = db("autograph")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->where("status", 0)->count();
            $type_title = getTypeParentNames($typecontrol_id);
            $arr[] = compact("typecontrol_id", "yy", "wy", "type_title");
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($arr));
    }


    function pidtype($myid)
    {
        $types = db("typecontrol")->select();
        $tree = \utils\Tree::instance()->init($types, "typecontrol_id", "pid");
        return $tree->getChildrenIds($myid, true);
    }

// 	function inspectdata(){
// 	    $where['status'] = 2;
// 	    $arr = db('autograph')->where($where)->select()->toArray();
// 	    if($arr){
//         	foreach ($arr as &$row) {
//                 if($row['usage_time'] + 3600 < time()){
//                     $upda['status'] = 1;
//                     $upda['usage_time'] = 0;
//                     AutographModel::where('autograph_id',$row['autograph_id'])->update($upda);
//                 }
//             }
// 	    }
// 	}
    function RandomData()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $typecontrol_id = $this->request->post('typecontrol_id');
        $type = $this->request->post('type');
        if (empty($typecontrol_id) || empty($type)) {

            throw new ValidateException('参数错误');
        }
        $where['typecontrol_id'] = $typecontrol_id;
        $where['status'] = 1;
        if ($type == 1) { //昵称
            $res = db('nickname')->where($where)->orderRaw('rand()')->limit(1)->select()->toArray();
        } else if ($type == 2) { //签名
            $res = db('autograph')->where($where)->orderRaw('rand()')->limit(1)->select()->toArray();
        } else {
            $res = db('headimage')->where($where)->orderRaw('rand()')->limit(1)->select()->toArray();
            foreach ($res as &$row) {
                $row['image'] = config('my.host_url') . $row['image'];
            }
        }
        if (empty($res)) {
            throw new ValidateException('当前分类没有素材');
        }
        $res['type_title'] = getTypeParentNames($typecontrol_id);
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }

    /**
     * @api {post} /Autograph/add 02、添加
     * @apiGroup Autograph
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            autograph 签名 (必填)   批量换行
     * @apiParam (输入参数：) {int}                grouping_id 分组id   (必填)
     * @apiParam (输入参数：) {string}            typecontrol_id 类型
     * @apiParam (输入参数：) {int}                status 状态 未用|1|success,已用|0|danger
     * @apiParam (输入参数：) {string}            usage_time 使用时间
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
        $postField = 'autograph,typecontrol_id,status,usage_time,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $autograph = explode("\n", $data['autograph']);
        unset($data['autograph']);
        foreach ($autograph as $item) {
            $data['autograph'] = $item;
            $data['api_user_id'] = $this->request->uid;
            $res = AutographService::add($data);
        }
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Autograph/update 03、修改
     * @apiGroup Autograph
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            autograph_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            autograph 签名 (必填)
     * @apiParam (输入参数：) {string}            typecontrol_id 类型
     * @apiParam (输入参数：) {int}                status 状态 未用|1|success,已用|0|danger
     * @apiParam (输入参数：) {string}            usage_time 使用时间
     * @apiParam (输入参数：) {int}                grouping_id 分组id
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
        $postField = 'autograph_id,autograph,typecontrol_id,status,usage_time,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['autograph_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['autograph_id'] = $data['autograph_id'];
        $res = AutographService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Autograph/delete 04、删除
     * @apiGroup Autograph
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            autograph_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('autograph_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['autograph_id'] = explode(',', $idx);
        try {
            AutographModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {get} /Autograph/view 05、查看详情
     * @apiGroup Autograph
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            autograph_id 主键ID
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    function view()
    {
        $data['autograph_id'] = $this->request->get('autograph_id', '', 'serach_in');
        $field = 'autograph_id,autograph,typecontrol_id,status,usage_time';
        $res = checkData(AutographModel::field($field)->where($data)->find());
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }


}

