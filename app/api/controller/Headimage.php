<?php
/*
 module:		头像库管理
 create_time:	2022-11-14 20:03:34
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Headimage as HeadimageModel;
use app\api\service\HeadimageService;
use think\exception\ValidateException;

class Headimage extends Common
{


    /**
     * @api {post} /Headimage/index 01、头像首页数据列表
     * @apiGroup Headimage
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [typecontrol_id] 类型
     * @apiParam (输入参数：) {int}            [status] 状态 未用|1|success,已用|0|danger
     * @apiParam (输入参数：) {int}            [grouping_id] 分组
     */
    function index()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = [];
        $where['a.typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
        $where['a.status'] = $this->request->post('status', '', 'serach_in');
        $where['a.grouping_id'] = $this->request->post('grouping_id', '', 'serach_in');


        $field = 'a.*,b.type_title';
        $order = $this->request->post('order', '', 'serach_in');
        $sort = $this->request->post('sort', '', 'serach_in');
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'headimage_id desc';
        // var_dump($where['a.typecontrol_id']);die;
        $res = HeadimageService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        $wy = 0;
        $res['type_title'] = getTypeParentNames($where['a.typecontrol_id']);
        foreach ($res['list'] as &$row) {
            $row['usage_time'] = date("Y-m-d H:i:s", $row['usage_time']);
            $row['image'] = config('my.host_url') . $row['image'];
            if ($row['grouping_id']) {
                $row['grouping_name'] = db('grouping')->where('grouping_id', $row['grouping_id'])->value('grouping_name');
            }
            if ($row['status'] == 0) {
                $wy++;
            }
        }
        $res['yy'] = $wy;
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    function inspectdata()
    {
        $where['status'] = 2;
        $arr = db('headimage')->where($where)->select()->toArray();
        if ($arr) {
            foreach ($arr as &$row) {
                if ($row['usage_time'] + 3600 < time()) {
                    $upda['status'] = 1;
                    $upda['usage_time'] = 0;
                    HeadimageModel::where('headimage_id', $row['headimage_id'])->update($upda);
                }
            }
        }
    }

    /**
     * @api {post} /Headimage/add 02、添加
     * @apiGroup Headimage
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            image 头像 (必填)
     * @apiParam (输入参数：) {string}            typecontrol_id 类型
     * @apiParam (输入参数：) {string}            grouping_id 类型
     */
    function add()
    {
        $postField = 'image,typecontrol_id,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $image = explode(",", $data['image']);
// 		print_r($nickname);die;
        unset($data['image']);
        foreach ($image as $item) {
            $data['api_user_id'] = $this->request->uid;
            $data['image'] = $item;
            $res = HeadimageService::add($data);
        }


        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Headimage/update 03、修改
     * @apiGroup Headimage
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            headimage_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            image 头像 (必填)
     * @apiParam (输入参数：) {string}            typecontrol_id 类型
     * @apiParam (输入参数：) {int}                status 状态 未用|1|success,已用|0|danger
     * @apiParam (输入参数：) {string}            grouping_id 类型
     */
    function update()
    {
        $postField = 'headimage_id,image,typecontrol_id,status,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['headimage_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['headimage_id'] = $data['headimage_id'];
        $res = HeadimageService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Headimage/delete 04、删除
     * @apiGroup Headimage
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            headimage_ids 主键id 注意后面跟了s 多数据删除
     */
    function delete()
    {
        $idx = $this->request->post('headimage_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['headimage_id'] = explode(',', $idx);
        try {
            HeadimageModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {get} /Headimage/view 05、查看详情
     * @apiGroup Headimage
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            headimage_id 主键ID
     */
    function view()
    {
        $data['headimage_id'] = $this->request->get('headimage_id', '', 'serach_in');
        $field = 'headimage_id,image,typecontrol_id,status';
        $res = checkData(HeadimageModel::field($field)->where($data)->find());
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }


}

