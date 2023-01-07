<?php
/*
 module:		昵称库
 create_time:	2022-11-16 14:27:39
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Nickname as NicknameModel;
use app\api\service\NicknameService;
use think\exception\ValidateException;

class Nickname extends Common
{


    /**
     * @api {post} /Nickname/index 01、昵称首页数据列表
     * @apiGroup Nickname
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [nickname] 昵称
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
        $where['a.nickname'] = ['like', $this->request->post('nickname', '', 'serach_in')];
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
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'a.nickname_id desc';


        $res = NicknameService::indexList($this->apiFormatWhere($where, NicknameModel::class), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            if ($row['grouping_id']) {
                $row['grouping_name'] = db('grouping')->where('grouping_id', $row['grouping_id'])->value('grouping_name');
            }
        }
// 		$this->inspectdata();
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

// 	function inspectdata(){
// 	    $where['status'] = 2;
// 	    $arr = db('nickname')->where($where)->select()->toArray();
// 	    if($arr){
//         	foreach ($arr as &$row) {
//                 if($row['usage_time'] + 3600 < time()){
//                     $upda['status'] = 1;
//                     $upda['usage_time'] = 0;
//                     NicknameModel::where('nickname_id',$row['nickname_id'])->update($upda);
//                 }
//             }
// 	    }
// 	}


    function listtable()
    {
        $typecontrol_id = $this->request->post('typecontrol_id', '', 'serach_in');
        $grouping_id = $this->request->post('grouping_id', '', 'serach_in');
        $arrtype_id = $this->pidtype($typecontrol_id);
        $arr = [];
        foreach ($arrtype_id as $typecontrol_id) {
            $wy = db("nickname")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->where("status", 1)->count();
            $yy = db("nickname")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->where("status", 0)->count();
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

    /**
     * @api {post} /Nickname/add 02、添加
     * @apiGroup Nickname
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            nickname 昵称 (必填)  批量换行
     * @apiParam (输入参数：) {string}            typecontrol_id 类型
     * @apiParam (输入参数：) {int}                status 状态 未用|1|success,已用|0|danger
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
        $postField = 'nickname,typecontrol_id,status,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $nickname = explode("\n", $data['nickname']);
// 		print_r($nickname);die;
        unset($data['nickname']);
        foreach ($nickname as $item) {
            $data['nickname'] = $item;
            $res = NicknameService::add($data);
        }

        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Nickname/update 03、修改
     * @apiGroup Nickname
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            nickname_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            nickname 昵称 (必填)
     * @apiParam (输入参数：) {string}            typecontrol_id 类型
     * @apiParam (输入参数：) {int}                status 状态 未用|1|success,已用|0|danger
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
        $postField = 'nickname_id,nickname,typecontrol_id,status,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['nickname_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['nickname_id'] = $data['nickname_id'];
        $res = NicknameService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Nickname/delete 04、删除
     * @apiGroup Nickname
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            nickname_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('nickname_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['nickname_id'] = explode(',', $idx);
        try {
            NicknameModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {get} /Nickname/view 05、查看详情
     * @apiGroup Nickname
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            nickname_id 主键ID
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
        $data['nickname_id'] = $this->request->get('nickname_id', '', 'serach_in');
        $field = 'nickname_id,nickname,typecontrol_id,status';
        $res = checkData(NicknameModel::field($field)->where($data)->find());
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }


}