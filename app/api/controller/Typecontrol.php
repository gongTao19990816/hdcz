<?php
/*
 module:		类型管理
 create_time:	2022-11-15 14:57:45
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Typecontrol as TypecontrolModel;
use app\api\service\TypecontrolService;
use think\exception\ValidateException;
use utils\Tree;

class Typecontrol extends Common
{


    /**
     * @api {post} /Typecontrol/index 01、首页数据列表
     * @apiGroup Typecontrol
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [type_title] 名称
     * @apiParam (输入参数：) {string}        [pid] 所父级
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
    function index1()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = [];
        $where['type_title'] = $this->request->post('type_title', '', 'serach_in');
        $where['pid'] = $this->request->post('pid', '', 'serach_in');

        $field = '*';
        $orderby = 'typecontrol_id desc';

        $res = TypecontrolService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['value'] = $row['typecontrol_id'];
            $row['label'] = $row['type_title'];
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    function index()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }

        $where = [];
        $where['type_title'] = $this->request->post('type_title', '', 'serach_in');
        $where['grouping_id'] = $this->request->post('grouping_id', '', 'serach_in');
        $field = '*';
        $orderby = 'typecontrol_id desc';

        $list = \app\api\model\Typecontrol::where($this->apiFormatWhere($where))->field($field)->order($orderby)->select()->toArray();
        foreach ($list as &$row) {
            $row['value'] = $row['typecontrol_id'];
            $row['label'] = $row['type_title'];
            $row['grouping_name'] = db('grouping')->where('grouping_id', $row['grouping_id'])->value('grouping_name');
        }
        $tree = Tree::instance()->init($list, "typecontrol_id", "pid");
        $res = $tree->getTreeArray(0);

        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }

    function indexlist()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = [];
        $where['type_title'] = $this->request->post('type_title', '', 'serach_in');
        $where['status'] = $this->request->post('status', '1', 'serach_in');
        $where['pid'] = $this->request->post('pid', '', 'serach_in');

        $field = '*';
        $orderby = 'typecontrol_id desc';

        $res = TypecontrolService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
// 		print_r($res);die;
        $arr = $this->tree($res['list']);
        $arr['count'] = $res['count'];
        // print_r($arr);die;
        // $res['list'] = formartList(['typecontrol_id', 'pid', 'type_title','type_title'],$res['rows']);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($arr));
    }


    function tree($data, $pid = 0, $level = 0)
    {
        static $arr = [];
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pid) {
                $v['type_title'] = str_repeat('|----', $level) . $v['type_title'];
                $arr[] = $v;
                unset($data[$k]);
                $this->tree($data, $v['typecontrol_id'], $level + 1);
            }
        }
        return $arr;
    }

    /**
     * @api {post} /Typecontrol/add 02、添加
     * @apiGroup Typecontrol
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            type_title 名称
     * @apiParam (输入参数：) {string}            pid 所父级  顶级pid=0
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
        $postField = 'type_title,pid,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['type_title'])) {
            throw new ValidateException('参数错误');
        }
        $data['api_user_id'] = $this->request->uid;
        $res = db('typecontrol')->insert($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Typecontrol/update 03、修改
     * @apiGroup Typecontrol
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            typecontrol_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            type_title 名称
     * @apiParam (输入参数：) {string}            pid 所父级
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
        $postField = 'typecontrol_id,type_title,pid,grouping_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['typecontrol_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['typecontrol_id'] = $data['typecontrol_id'];
        $res = TypecontrolService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


    function kylistnum()
    {
        $typecontrol_id = $this->request->post('typecontrol_id');
        $grouping_id = $this->request->post('grouping_id');
        if (empty($typecontrol_id) && empty($grouping_id)) {
            throw new ValidateException('参数错误');
        }
        $where['typecontrol_id'] = $typecontrol_id;
        $where['grouping_id'] = $grouping_id;
        $nick = db('nickname')->where($where)->count();
        $nc = db('autograph')->where($where)->count();
        $tx = db('headimage')->where($where)->count();
        $data['nickname'] = $nick;
        $data['autograph'] = $nc;
        $data['headimage'] = $tx;
        return $this->ajaxReturn($this->successCode, '操作成功', $data);
    }

    /**
     * @api {post} /Typecontrol/delete 04、删除
     * @apiGroup Typecontrol
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            typecontrol_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('typecontrol_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['typecontrol_id'] = explode(',', $idx);
        try {
            TypecontrolModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {get} /Typecontrol/view 05、查看详情
     * @apiGroup Typecontrol
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            typecontrol_id 主键ID
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
        $data['typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
        $field = 'typecontrol_id,type_title,pid';
        $res = checkData(TypecontrolModel::field($field)->where($data)->find());
        $res['pid_title'] = TypecontrolModel::where('typecontrol_id', $res['pid'])->value('type_title');
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }

    /**
     * @api {post} http://192.168.3.30/api/Typecontrol/datalist 06、对外数据
     * @apiGroup Typecontrol
     * @apiVersion 1.0.0
     * @apiDescription  对外数据
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认1）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [type_title] 名称
     * @apiParam (输入参数：) {string}        [pid] 所父级
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
    function datalist()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 1, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = [];
        $where['a.type_title'] = $this->request->post('type_title', '', 'serach_in');
        $where['b.status'] = $this->request->post('status', '1', 'serach_in');
// 		$where['c.status'] = $this->request->post('status', '1', 'serach_in');
        $where['d.status'] = $this->request->post('status', '1', 'serach_in');
        $where['e.status'] = $this->request->post('status', '1', 'serach_in');
        $orderby = 'rand()';
        $sql = 'SELECT a.type_title, b.nickname,b.nickname_id, d.image,d.headimage_id, e.autograph,e.autograph_id FROM tt_typecontrol AS a INNER JOIN tt_nickname AS b  INNER JOIN tt_headimage AS d INNER JOIN tt_autograph AS e ON a.typecontrol_id = b.typecontrol_id  AND a.typecontrol_id = d.typecontrol_id AND a.typecontrol_id = e.typecontrol_id';
        $limit = ($page - 1) * $limit . ',' . $limit;
        $res = \base\CommonService::loadList($sql, $this->apiFormatWhere($where), $limit, $orderby);//筛选出的未使用的数据
// 		var_dump($res);die;
        foreach ($res['rows'] as &$row) {
            $row['image'] = config('my.host_url') . $row['image'];
            TypecontrolService::updatalist($row['nickname_id'], $row['headimage_id'], $row['autograph_id']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }


    // 	接收反馈/Typecontrol/feedback
    function feedback()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $nickname_id = $this->request->post('nickname_id', '', 'serach_in');
        $video_num = $this->request->post('video_num', '', 'serach_in');
        $headimage_id = $this->request->post('headimage_id', '', 'serach_in');
        $autograph_id = $this->request->post('autograph_id', '', 'serach_in');
        if (empty($nickname_id) && empty($video_num) && empty($headimage_id) && empty($autograph_id)) {
            throw new ValidateException('参数错误');
        }
        try {
            // $updata['status'] = 0;
            // $updata['usage_time'] = time();
            if ($nickname_id) {
                db('nickname')->where('nickname_id', $nickname_id)->inc('usage_count')->update();
            }

            if ($headimage_id) {
                db('headimage')->where('headimage_id', $headimage_id)->inc('usage_count')->update();
            }
            if ($autograph_id) {
                db('autograph')->where('autograph_id', $autograph_id)->inc('usage_count')->update();
            }
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

}

