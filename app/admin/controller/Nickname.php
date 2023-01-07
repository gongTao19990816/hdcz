<?php
/*
 module:		昵称库
 create_time:	2022-11-16 12:41:24
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Nickname as NicknameModel;
use app\admin\service\NicknameService;

class Nickname extends Admin
{


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
            $where['a.nickname'] = $this->request->param('nickname', '', 'serach_in');
            $where['a.typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');
            $where['a.status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'nickname_id,nickname,typecontrol_id,status,usage_time';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'nickname_id desc';

            $res = NicknameService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'nickname,typecontrol_id,status,usage_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = NicknameService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $nickname_id = $this->request->get('nickname_id', '', 'serach_in');
            if (!$nickname_id) $this->error('参数错误');
            $this->view->assign('info', checkData(NicknameModel::find($nickname_id)));
            return view('update');
        } else {
            $postField = 'nickname_id,nickname,typecontrol_id,status,usage_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = NicknameService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('nickname_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            NicknameModel::destroy(['nickname_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $nickname_id = $this->request->get('nickname_id', '', 'serach_in');
        if (!$nickname_id) $this->error('参数错误');
        $this->view->assign('info', NicknameModel::find($nickname_id));
        return view('view');
    }

    /*导入*/
    function Imports()
    {
        if ($this->request->isPost()) {
            try {
                $key = 'Nickname';
                $result = \base\CommonService::importData($key);
                if (count($result) > 0) {
                    cache($key, $result, 3600);
                    return redirect('startImport');
                } else {
                    $this->error('内容格式有误！');
                }
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            return view('base/importData');
        }
    }

    //开始导入
    function startImport()
    {
        if (!$this->request->isPost()) {
            return view('base/startImport');
        } else {
            $p = $this->request->post('p', '', 'intval');
            $data = cache('Nickname');
            $export_per_num = config('my.export_per_num') ? config('my.export_per_num') : 50;
            $num = ceil((count($data) - 1) / $export_per_num);
            $export_fields = 'nickname,typecontrol_id';    //支持导入的字段
            if ($data) {
                $start = $p == 1 ? 2 : ($p - 1) * $export_per_num + 1;
                if ($data[$start]) {
                    $dt['percent'] = ceil(($p) / $num * 100);
                    try {
                        for ($i = 1; $i <= $export_per_num; $i++) {
                            //根据中文名称来读取字段名称
                            if ($data[$i + ($p - 1) * $export_per_num]) {
                                foreach ($data[1] as $key => $val) {
                                    $fieldInfo = db('field')->where(['name' => $val, 'menu_id' => 739])->find();
                                    if ($val && $fieldInfo && in_array($fieldInfo['field'], explode(',', $export_fields))) {
                                        $d[$fieldInfo['field']] = $data[$i + ($p - 1) * $export_per_num][$key];
                                        if ($fieldInfo['type'] == 17) {
                                            unset($d[$fieldInfo['field']]);
                                        }
                                        if (in_array($fieldInfo['type'], [7, 12])) {    //时间字段
                                            if (strlen($data[$i + ($p - 1) * $export_per_num][$key]) == 5) {
                                                $d[$fieldInfo['field']] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($data[$i + ($p - 1) * $export_per_num][$key]);
                                            } else {
                                                $d[$fieldInfo['field']] = strtotime($data[$i + ($p - 1) * $export_per_num][$key]);
                                            }
                                        }
                                        if ($fieldInfo['type'] == 5) {    //密码字段
                                            $d[$fieldInfo['field']] = md5($data[$i + ($p - 1) * $export_per_num][$key] . config('my.password_secrect'));
                                        }
                                        if ($fieldInfo['type'] == 17) {    //三级联动字段
                                            $arrTitle = explode('|', $fieldInfo['field']);
                                            $arrValue = explode('-', $data[$i + ($p - 1) * $export_per_num][$key]);
                                            if ($arrTitle && $arrValue) {
                                                foreach ($arrTitle as $k => $v) {
                                                    $d[$v] = $arrValue[$k];
                                                }
                                            }
                                        }
                                        if (in_array($fieldInfo['type'], [2, 3, 23, 29]) && empty($fieldInfo['sql'])) {    //下拉，单选，开关按钮
                                            $d[$fieldInfo['field']] = getFieldName($data[$i + ($p - 1) * $export_per_num][$key], $fieldInfo['config']);
                                        }
                                    }
                                }
                                if (($i + ($p - 1) * $export_per_num) > 1) {
                                    NicknameModel::create($d);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        abort(config('my.error_log_code'), $e->getMessage());
                    }
                    return json(['error' => '00', 'data' => $dt]);
                } else {
                    cache('Nickname', null);
                    return json(['error' => '10']);
                }
            } else {
                $this->error('当前没有数据');
            }
        }
    }

    /*导出*/
    function exports()
    {
        $where = [];
        $where['nickname'] = $this->request->param('nickname', '', 'serach_in');
        $where['typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');
        $where['status'] = $this->request->param('status', '', 'serach_in');
        $where['nickname_id'] = ['in', $this->request->param('nickname_id', '', 'serach_in')];

        try {
            //此处读取前端传过来的 表格勾选的显示字段
            $fieldInfo = [];
            for ($j = 0; $j < 100; $j++) {
                $fieldInfo[] = $this->request->param($j);
            }
            $list = NicknameModel::where(formatWhere($where))->order('nickname_id desc')->select();
            if (empty($list)) throw new Exception('没有数据');
            NicknameService::exports(htmlOutList($list), filterEmptyArray(array_unique($fieldInfo)));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }


}

