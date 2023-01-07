<?php
/*
 module:		uid库
 create_time:	2022-12-13 15:38:52
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Uidlibrary as UidlibraryModel;
use app\api\service\UidlibraryService;
use think\exception\ValidateException;

class Uidlibrary extends Common
{


    /**
     * @api {post} /Uidlibrary/index 01、首页数据列表
     * @apiGroup Uidlibrary
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {int}            [libraryid_id] libraryid_id
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
        $where['a.libraryid_id'] = $this->request->post('libraryid_id', '', 'serach_in');

        $usage_count = $this->request->post('usage_count', '', 'serach_in');
        $usage_count = html_entity_decode(trim($usage_count));
        if ($usage_count) {
            if (strstr($usage_count, ">")) {
                $where['usage_count'] = ['>', ltrim($usage_count, '>')];
            } else {
                $where['usage_count'] = ['=', html_entity_decode(trim($usage_count))];
            }
        }
        $field = 'a.*,b.name';
        $order = $this->request->post('order', '', 'serach_in');
        $sort = $this->request->post('sort', '', 'serach_in');
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'uidlibrary_id desc';

        $res = UidlibraryService::indexList($this->apiFormatWhere($where, UidlibraryModel::class), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Uidlibrary/add 02、添加
     * @apiGroup Uidlibrary
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {int}                libraryid_id libraryid_id
     * @apiParam (输入参数：) {string}            url 主页链接
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
        $postField = 'libraryid_id,url,add_time';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $url = explode("\n", $data['url']);
        $i = 0;
        unset($data['url']);
        foreach ($url as $item) {
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {//主页链接

            } else {//自己库里面有的在自己库里面拿
                $arr = db('member')->where('uid', $item)->find();
                if ($arr) {
                    $data['uid'] = $arr['uid'];
                    $data['sec_uid'] = $arr['sec_uid'];
                } else {
                    $i++;
                    continue;
                }
            }
            $data['url'] = $item;
            $res = UidlibraryService::add($data);
        }


        return $this->ajaxReturn($this->successCode, '操作成功有' . $i . '失败');
    }


    function jsonlist()
    {
        $data = $this->request->post();
        if (empty($data)) {
            throw new ValidateException('参数错误');
        }
        $userlist = $data['data'];
        if (empty($userlist)) {
            throw new ValidateException('参数错误');
        }
        // print_r($userlist);die;
        foreach ($userlist as $k => $v) {
            $adddata['libraryid_id'] = $v['libraryid_id'];
            $adddata['uid'] = $v['uid'];
            $adddata['sec_uid'] = $v['sec_uid'];
            $adddata['url'] = $v['url'];
            $adddata['add_time'] = time();
            $res = UidlibraryService::add($adddata);
        }
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Uidlibrary/update 03、修改
     * @apiGroup Uidlibrary
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            uidlibrary_id 主键ID (必填)
     * @apiParam (输入参数：) {int}                libraryid_id libraryid_id
     * @apiParam (输入参数：) {string}            uid uid
     * @apiParam (输入参数：) {string}            sec_uid sec_uid
     * @apiParam (输入参数：) {string}            url 主页链接
     * @apiParam (输入参数：) {string}            add_time 添加时间
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
        $postField = 'uidlibrary_id,libraryid_id,uid,sec_uid,url,add_time';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['uidlibrary_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['uidlibrary_id'] = $data['uidlibrary_id'];
        $res = UidlibraryService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Uidlibrary/delete 04、删除
     * @apiGroup Uidlibrary
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            uidlibrary_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('uidlibrary_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['uidlibrary_id'] = explode(',', $idx);
        try {
            UidlibraryModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

