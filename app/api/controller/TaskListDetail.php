<?php
/*
 module:		任务明细表
 create_time:	2022-12-09 17:25:05
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\TaskListDetail as TaskListDetailModel;
use app\api\service\TaskListDetailService;
use RedisException;
use think\Exception;
use think\exception\ValidateException;

class TaskListDetail extends Common
{


    /**
     * @api {post} /TaskListDetail/index 01、首页数据列表
     * @apiGroup TaskListDetail
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [status] 状态 未开始|1|primary,成功|0|success,失败|2|danger
     * @apiParam (输入参数：) {int}            [tasklist_id] 所属任务
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
//        $where['status'] = $this->request->post('status', '', 'serach_in');
        $where['tasklist_id'] = $this->request->post('tasklist_id', '', 'serach_in');
        $where['crux'] = $this->request->post("uid");
        $field = '*';
        $orderby = 'tasklistdetail_id desc';

        $res = TaskListDetailService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['create_times'] = date("Y-m-d H:i:s", $row['create_time']);
            if ($row['receive_time'] == 0) {
                $row['receive_times'] = '';
            } else {
                $row['receive_times'] = date("Y-m-d H:i:s", $row['receive_time']);
            }
            if ($row['complete_time']) {
                $row['complete_times'] = date("Y-m-d H:i:s", $row['complete_time']);
            } else {
                $row['complete_times'] = '';
            }
            $row['parameter'] = json_decode($row['parameter'], true);
//            $row['userinfo'] = db('member')->where('uid',$row['crux'])->field('nickname,signature,avatar_thumb,backups_name,phone_number,typecontrol_id')->find();
//            $row['type_title'] = getTypeParentNames($row['userinfo']['typecontrol_id']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /TaskListDetail/retry 02、单个任务重试
     * @apiGroup TaskListDetail
     * @apiVersion 1.0.0
     * @apiDescription  单个任务重试
     * @apiParam (输入参数：) {int}              tasklistdetail_id 任务详情id
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
    function retry()
    {
        $taskdetail_id = $this->request->post("tasklistdetail_id/n");
        try {
            $taskdetail = \app\api\model\TaskListDetail::where("tasklistdetail_id", $taskdetail_id)->find()->toArray();
            $task = \app\api\model\Tasklist::where("tasklist_id", $taskdetail['tasklist_id'])->field('redis_key,tasklist_id')->find();
            if ($taskdetail) {
                if ($taskdetail['status'] != 2) {
                    throw new \think\Exception("任务未失败");
                }
                $redis_key = $task->redis_key;
                if ($redis_key) {
                    $task->inc('task_num')->update();
                    $taskdetail['parameter'] = json_decode($taskdetail['parameter'], true);
                    try {
                        connectRedis()->lPush($redis_key, json_encode($taskdetail));
                    } catch (RedisException $e) {
                        throw new Exception($e->getMessage());
                    }
                } else {
                    throw new \think\Exception('未从任务表中获取到redis_key');
                }
            } else {
                throw new \think\Exception('未查询到任务详情');
            }
        } catch (Exception $e) {
            throw new ValidateException($e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '重试成功');
    }

    /**
     * @api {post} /TaskListDetail/add 02、添加
     * @apiGroup TaskListDetail
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            parameter 任务参数
     * @apiParam (输入参数：) {string}            status 状态 未开始|1|primary,成功|0|success,失败|2|danger
     * @apiParam (输入参数：) {string}            reason 失败原因
     * @apiParam (输入参数：) {int}                tasklist_id 所属任务
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
        $postField = 'parameter,status,create_time,reason,tasklist_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = TaskListDetailService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /TaskListDetail/update 03、修改
     * @apiGroup TaskListDetail
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            tasklistdetail_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            parameter 任务参数
     * @apiParam (输入参数：) {string}            status 状态 未开始|1|primary,成功|0|success,失败|2|danger
     * @apiParam (输入参数：) {string}            create_time 完成时间
     * @apiParam (输入参数：) {string}            reason 失败原因
     * @apiParam (输入参数：) {int}                tasklist_id 所属任务
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
        $postField = 'tasklistdetail_id,parameter,status,create_time,reason,tasklist_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['tasklistdetail_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['tasklistdetail_id'] = $data['tasklistdetail_id'];
        $res = TaskListDetailService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /TaskListDetail/delete 04、删除
     * @apiGroup TaskListDetail
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            tasklistdetail_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('tasklistdetail_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['tasklistdetail_id'] = explode(',', $idx);
        try {
            TaskListDetailModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {get} /TaskListDetail/view 05、查看详情
     * @apiGroup TaskListDetail
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            tasklistdetail_id 主键ID
     * @apiParam (输入参数：) {string}            800px 主键ID
     * @apiParam (输入参数：) {string}            450px 主键ID
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
        $data['800px'] = $this->request->get('800px', '', 'serach_in');
        $data['450px'] = $this->request->get('450px', '', 'serach_in');
        $field = 'tasklistdetail_id,parameter,status,create_time,reason,tasklist_id';
        $res = checkData(TaskListDetailModel::field($field)->where($data)->find());
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }


}

