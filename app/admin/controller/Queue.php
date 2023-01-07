<?php
/*
 module:		队列任务测试
 create_time:	2022-12-06 15:13:04
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\job\Test;
use app\admin\model\Queue as QueueModel;
use app\admin\service\QueueService;
use think\App;
use think\View;

class Queue extends Admin
{

    public function __construct(App $app, View $view)
    {
        parent::__construct($app, $view);
    }

    /*首页数据列表*/
    function index2()
    {
        if (!$this->request->isAjax()) {
            return view('index');
        } else {
            //$response = $this->httpRequest(HttpCrontab::INDEX_PATH . '?' . $this->request->query());
            $response = \Crontab::index($this->request->query());
            if ($response['ok']) {
                $data = [
                    'code' => 0,
                    'msg' => '',
                    'count' => $response['data']['total'],
                    'data' => $response['data']['data'],
                ];
                $data = ['rows' => $response['data']['data'], 'total' => $response['data']['total']];

            } else {
                $data = [
                    'code' => 1,
                    'msg' => $response['msg'],
                    'count' => 0,
                    'data' => [],
                ];

            }

            return json($data);
        }
    }


    /*首页数据列表*/
    function index()
    {
        if (!$this->request->isAjax()) {
            return view('index');
        } else {
            $limit = $this->request->post('limit', 20, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page = floor($offset / $limit) + 1;

            $where = [];
            $where['name'] = $this->request->param('name_s', '', 'serach_in');
            $where['event'] = $this->request->param('event', '', 'serach_in');
            $where['param'] = $this->request->param('param', '', 'serach_in');

            $createtime_start = $this->request->param('createtime_start', '', 'serach_in');
            $createtime_end = $this->request->param('createtime_end', '', 'serach_in');

            $where['createtime'] = ['between', [strtotime($createtime_start), strtotime($createtime_end)]];

            $lasttime_start = $this->request->param('lasttime_start', '', 'serach_in');
            $lasttime_end = $this->request->param('lasttime_end', '', 'serach_in');

            $where['lasttime'] = ['between', [strtotime($lasttime_start), strtotime($lasttime_end)]];

            $filshtime_start = $this->request->param('filshtime_start', '', 'serach_in');
            $filshtime_end = $this->request->param('filshtime_end', '', 'serach_in');

            $where['filshtime'] = ['between', [strtotime($filshtime_start), strtotime($filshtime_end)]];

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'queue_id,name,event,param,rwnum,oknum,createtime,lasttime,filshtime';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'queue_id desc';

            $res = QueueService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            /*$postField = 'name,event,param,rwnum,oknum,createtime,lasttime,filshtime';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            var_dump($this->runCmd('php -r "die(phpinfo());"'));
            //\think\facade\Queue::push(Test::class, ['param' => $this->request->post("param"), 'event' => $this->request->post("event")], $this->request->post("event"));
            //$res = exec("cd " . \app()->getRootPath() . " ; php think queue:listen --queue " . $this->request->post("event"), $callback);
            $res = QueueService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);*/
            /*$post = [
                "status" => 1,
                "type" => 1,
                'title' => 'require',
                'rule' => '/3 * * * * *',
                'target' => "\app\api\controller\Test@getByProfile",//runClassCrontab
                'parameter' => "user_id=7164384802589606918"
            ];*/
            $parameter = json_decode('{"user_id":"7164384802589606918,7146120879167177733"}', true);
            $post = [
                "status" => 1,
                "type" => 3,
                'title' => 'getuserinfo',
                'need_run_times' => 80,
                'rule' => '/10 * * * * *',
                'target' => "http://tt.test/api/Test/getuserinfo",//runUrlCrontab
                'parameter' => json_encode([])
            ];
            //json_encode(Db::name("member")->where(["ifup" => 1])->field("uid as user_id")->limit(10)->select())
            //$response = $this->httpRequest(HttpCrontab::ADD_PATH, 'POST', $post);
            return (new \Crontab())->add($post);
            $response['ok'] ? $this->success('保存成功') : $this->error($response['msg']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $queue_id = $this->request->get('queue_id', '', 'serach_in');
            if (!$queue_id) $this->error('参数错误');
            $this->view->assign('info', checkData(QueueModel::find($queue_id)));
            return view('update');
        } else {
            $postField = 'queue_id,name,event,param,rwnum,oknum,createtime,lasttime,filshtime';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = QueueService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('queue_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            QueueModel::destroy(['queue_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $queue_id = $this->request->get('queue_id', '', 'serach_in');
        if (!$queue_id) $this->error('参数错误');
        $this->view->assign('info', QueueModel::find($queue_id));
        return view('view');
    }


}

