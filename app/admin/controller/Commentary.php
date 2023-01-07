<?php 
/*
 module:		评论话术
 create_time:	2023-01-05 16:32:34
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\CommentaryService;
use app\admin\model\Commentary as CommentaryModel;
use think\facade\Db;

class Commentary extends Admin {


	/*首页数据列表*/
	function index(){
		if (!$this->request->isAjax()){
			return view('index');
		}else{
			$limit  = $this->request->post('limit', 20, 'intval');
			$offset = $this->request->post('offset', 0, 'intval');
			$page   = floor($offset / $limit) +1 ;

			$where = [];
			$where['typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');
			$where['grouping_id'] = $this->request->param('grouping_id', '', 'serach_in');
			$where['usage_count'] = $this->request->param('usage_count', '', 'serach_in');

			$order  = $this->request->post('order', '', 'serach_in');	//排序字段 bootstrap-table 传入
			$sort  = $this->request->post('sort', '', 'serach_in');		//排序方式 desc 或 asc

			$field = 'commentary_id,typecontrol_id,grouping_id,text,usage_count';
			$orderby = ($sort && $order) ? $sort.' '.$order : 'commentary_id desc';

			$res = CommentaryService::indexList(formatWhere($where, $model),$field,$orderby,$limit,$page);
			return json($res);
		}
	}

	/*添加*/
	function add(){
		if (!$this->request->isPost()){
			return view('add');
		}else{
			$postField = 'typecontrol_id,grouping_id,text,usage_count';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = CommentaryService::add($data);
			return json(['status'=>'00','msg'=>'添加成功']);
		}
	}

	/*修改*/
	function update(){
		if (!$this->request->isPost()){
			$commentary_id = $this->request->get('commentary_id','','serach_in');
			if(!$commentary_id) $this->error('参数错误');
			$this->view->assign('info',checkData(CommentaryModel::find($commentary_id)));
			return view('update');
		}else{
			$postField = 'commentary_id,typecontrol_id,grouping_id,text,usage_count';
			$data = $this->request->only(explode(',',$postField),'post',null);
			$res = CommentaryService::update($data);
			return json(['status'=>'00','msg'=>'修改成功']);
		}
	}

	/*删除*/
	function delete(){
		$idx =  $this->request->post('commentary_id', '', 'serach_in');
		if(!$idx) $this->error('参数错误');
		try{
			CommentaryModel::destroy(['commentary_id'=>explode(',',$idx)],true);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return json(['status'=>'00','msg'=>'操作成功']);
	}



}

