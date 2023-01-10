<?php
/*
 module:		素材管理
 create_time:	2022-11-14 16:08:59
 author:		
 contact:		
*/

namespace app\api\controller;

use app\api\model\Material as MaterialModel;
use app\api\service\MaterialService;
use think\Exception;
use think\exception\ValidateException;

class Material extends Common
{


    /**
     * @api {post} /Material/index 01、视频首页数据列表
     * @apiGroup Material
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [video_num] 视频编号
     * @apiParam (输入参数：) {string}        [add_time_start] 上传时间开始
     * @apiParam (输入参数：) {string}        [add_time_end] 上传时间结束
     * @apiParam (输入参数：) {string}        [typecontrol_id] 视频类型
     * @apiParam (输入参数：) {string}        [order] 排序的字段
     * @apiParam (输入参数：) {string}        [sort] 排序的方式 desc从大到小 asc从小到大
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
        $where['video_num'] = ['like', $this->request->post('video_num', '', 'serach_in')];
        $add_time_start = $this->request->post('add_time_start', '', 'serach_in');
        $add_time_end = $this->request->post('add_time_end', '', 'serach_in');
        $where['add_time'] = ['between', [strtotime($add_time_start), strtotime($add_time_end)]];
        $where['a.typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
        $where['a.grouping_id'] = $this->request->post('grouping_id', '', 'serach_in');

        $field = 'a.*,b.type_title';
        $order = $this->request->post('order', '', 'serach_in');
        $sort = $this->request->post('sort', '', 'serach_in');
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'material_id desc';
        $res = MaterialService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        $wy = 0;
        $res['type_title'] = getTypeParentNames($where['a.typecontrol_id']);
        foreach ($res['list'] as &$row) {
            $row['add_time'] = date("Y-m-d H:i:s", $row['add_time']);
            $row['video_url'] = config('my.host_url') . $row['video_url'];
            if ($row['usage_time']) {
                $row['usage_time'] = date("Y-m-d H:i:s", $row['usage_time']);
            }
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

    //统计数量
    function countvideo()
    {
        $typecontrol_id = $this->request->post('typecontrol_id');
        $grouping_id = $this->request->post('grouping_id');
        if (empty($typecontrol_id) && empty($grouping_id)) {
            throw new ValidateException('分类分组不能为空');
        }
        $data = MaterialModel::where(['typecontrol_id' => $typecontrol_id, 'grouping_id' => $grouping_id])->count();
        if ($data) {
            return $this->ajaxReturn($this->successCode, '返回成功', $data);
        } else {
            throw new ValidateException('没有视频，请先上传视频到是素材库');
        }

    }

    /**
     * @api {post} /Material/upload 02、上传视频
     * @apiGroup Material
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            typecontrol_id 视频类型
     * @apiParam (输入参数：) {file}              file 视频文件
     * @apiParam (输入参数：) {int}               grouping_id 分组
     */
    function upload()
    {
        $postField = 'add_time,typecontrol_id,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $file = $this->request->file('file');
        try {
            $video_url = $this->common_upload($file);
        } catch (Exception $e) {
            return json(['status' => config('my.errorCode'), 'msg' => $e->getMessage()]);
        }

        if ($video_url) {
            $arr = db('material')->where('video_url', $video_url)->value('video_url');
            if (!$arr) {
                $data['add_time'] = time();
                $data['api_user_id'] = $this->request->uid;
                $data['video_url'] = $video_url;
                $res = MaterialService::add($data);
                $updata['video_num'] = '100' . $res;
                db('material')->where('material_id', $res)->update($updata);
            }
        }

        return $this->ajaxReturn($this->successCode, '新增成功');
    }


    /**
     * @api {post} /Material/add 02、添加
     * @apiGroup Material
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            typecontrol_id 视频类型
     * @apiParam (输入参数：) {string}            video_url 视频地址
     * @apiParam (输入参数：) {int}                grouping_id 分组
     */
    function add()
    {
        $postField = 'add_time,typecontrol_id,video_url,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $video_url = explode(",", $data['video_url']);
// 		print_r($nickname);die;
        $i = 0;
        unset($data['video_url']);
        foreach ($video_url as $item) {
            $data['video_url'] = $item;
            $data['api_user_id'] = $this->request->uid;
            $arr = db('material')->where('video_url', $item)->value('video_url');
            if ($arr) {
                $i++;
                continue;
            }
            $res = MaterialService::add($data);
            $updata['video_num'] = '100' . $res;
            db('material')->where('material_id', $res)->update($updata);
        }
// 		$res = MaterialService::add($data);

        return $this->ajaxReturn($this->successCode, '新增成功，有' . $i . '个重复', $res);
    }

    /**
     * @api {post} /Material/update 03、修改
     * @apiGroup Material
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            material_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            add_time 上传时间
     * @apiParam (输入参数：) {string}            typecontrol_id 视频类型
     * @apiParam (输入参数：) {string}            video_url 视频地址
     * @apiParam (输入参数：) {int}                grouping_id 分组
     */
    function update()
    {
        $postField = 'material_id,add_time,typecontrol_id,video_url,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['material_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['material_id'] = $data['material_id'];
        $res = MaterialService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Material/delete 04、删除
     * @apiGroup Material
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            material_ids 主键id 注意后面跟了s 多数据删除
     */
    function delete()
    {
        $idx = $this->request->post('material_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['material_id'] = explode(',', $idx);
        try {
            MaterialModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


    function view()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $title = $this->request->post('type_title', '', 'serach_in');
        if (empty($title)) {
            throw new ValidateException('参数错误');
        }
        $type_tile = db('typecontrol')->where('type_title', $title)->find();
        $where['typecontrol_id'] = $type_tile['typecontrol_id'];
        $where['status'] = 1;
        if ($type_tile) {
            $res = MaterialModel::where($where)->field('video_url,video_num')->orderRaw('rand()')->limit(1)->select()->toArray();
            if ($res) {
                foreach ($res as &$row) {
                    $row['video_url'] = config('my.host_url') . $row['video_url'];
                    $data['status'] = 2;
                    $data['usage_time'] = time();
                    MaterialModel::where('video_num', $row['video_num'])->update($data);
                }
                return $this->ajaxReturn($this->successCode, '返回成功', $res);
            } else {
                throw new ValidateException('没有可用的视频文件');
            }
        } else {
            throw new ValidateException('没有这个分类');
        }

    }


}

