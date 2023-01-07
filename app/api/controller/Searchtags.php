<?php 
/*
 module:		被搜索的标签
 create_time:	2023-01-05 18:05:11
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\service\SearchtagsService;
use app\api\model\Searchtags as SearchtagsModel;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Log;

class Searchtags extends Common {


	/**
	* @api {post} /Searchtags/index 01、首页数据列表
	* @apiGroup Searchtags
	* @apiVersion 1.0.0
	* @apiDescription  首页数据列表
	* @apiParam (输入参数：) {int}     		[limit] 每页数据条数（默认20）
	* @apiParam (输入参数：) {int}     		[page] 当前页码
	* @apiParam (输入参数：) {int}			[typecontrol_id] 分类id 
	* @apiParam (输入参数：) {int}			[grouping_id] 分组id 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.data 返回数据
	* @apiParam (成功返回参数：) {string}     	array.data.list 返回数据列表
	* @apiParam (成功返回参数：) {string}     	array.data.count 返回数据总数
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":""}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"查询失败"}
	*/
	function index(){
		if(!$this->request->isPost()){
			throw new ValidateException('请求错误');
		}
		$limit  = $this->request->post('limit', 20, 'intval');
		$page   = $this->request->post('page', 1, 'intval');

		$where = [];
		$where['typecontrol_id'] = $this->request->post('typecontrol_id', '', 'serach_in');
		$where['grouping_id'] = $this->request->post('grouping_id', '', 'serach_in');

		$usage_count = $this->request->post('usage_count', '', 'serach_in');
        $usage_count = html_entity_decode(trim($usage_count));
        if ($usage_count) {
            if (strstr($usage_count, ">")) {
                $where['usage_count'] = ['>', ltrim($usage_count, '>')];
            } else {
                $where['usage_count'] = ['=', html_entity_decode(trim($usage_count))];
            }
        }
        $field = '*';
        $order = $this->request->post('order', '', 'serach_in');
        $sort = $this->request->post('sort', '', 'serach_in');
        $orderby = ($order && $sort) ? $order . ' ' . $sort : 'searchtags_id desc';
		
		

        $model =  \app\api\model\Searchtags::class;
		$res = SearchtagsService::indexList($this->apiFormatWhere($where, $model),$field,$orderby,$limit,$page);
		return $this->ajaxReturn($this->successCode,'返回成功',htmlOutList($res));
	}

	/**
	* @api {post} /Searchtags/add 02、添加
	* @apiGroup Searchtags
	* @apiVersion 1.0.0
	* @apiDescription  添加
	* @apiParam (输入参数：) {int}				typecontrol_id 分类id 
	* @apiParam (输入参数：) {int}				grouping_id 分组id 
	* @apiParam (输入参数：) {string}			label 标签 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","data":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function add(){
		$postField = 'typecontrol_id,grouping_id,label';
		$data = $this->request->only(explode(',',$postField),'post',null);
		$label = explode("\n", $data['label']);
        unset($data['label']);
        foreach ($label as $item) {
            $data['label'] = $item;
            $data['api_user_id'] = $this->request->uid;
            $res = SearchtagsService::add($data);
        }
		return $this->ajaxReturn($this->successCode,'操作成功',$res);
	}
	
	function listtable()
    {
        $typecontrol_id = $this->request->post('typecontrol_id', '', 'serach_in');
        $grouping_id = $this->request->post('grouping_id', '', 'serach_in');
        $arrtype_id = $this->pidtype($typecontrol_id);
        $arr = [];
        foreach ($arrtype_id as $typecontrol_id) {
            $num = db("searchtags")->where(["typecontrol_id" => $typecontrol_id, 'grouping_id' => $grouping_id])->count();
            $grouping_name = $this->grouping_name($typecontrol_id);
            $type_title = getTypeParentNames($typecontrol_id);
            $arr[] = compact("typecontrol_id", "num", "type_title","grouping_name");
        }
        // var_dump($arr);
        return $this->ajaxReturn($this->successCode, '返回成功', $arr);
    }


    function pidtype($myid)
    {
        $types = db("typecontrol")->select();
        // var_dump($types);die;
        $tree = \utils\Tree::instance()->init($types, "typecontrol_id", "pid");
        return $tree->getChildrenIds($myid, true);
    }

	/**
	* @api {post} /Searchtags/update 03、修改
	* @apiGroup Searchtags
	* @apiVersion 1.0.0
	* @apiDescription  修改
	
	* @apiParam (输入参数：) {string}     		searchtags_id 主键ID (必填)
	* @apiParam (输入参数：) {int}				typecontrol_id 分类id 
	* @apiParam (输入参数：) {int}				grouping_id 分组id 
	* @apiParam (输入参数：) {string}			label 标签 

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码  201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":" 201","msg":"操作失败"}
	*/
	function update(){
		$postField = 'searchtags_id,typecontrol_id,grouping_id,label';
		$data = $this->request->only(explode(',',$postField),'post',null);
		if(empty($data['searchtags_id'])){
			throw new ValidateException('参数错误');
		}
		$where['searchtags_id'] = $data['searchtags_id'];
		$res = SearchtagsService::update($where,$data);
		return $this->ajaxReturn($this->successCode,'操作成功');
	}

	/**
	* @api {post} /Searchtags/delete 04、删除
	* @apiGroup Searchtags
	* @apiVersion 1.0.0
	* @apiDescription  删除
	* @apiParam (输入参数：) {string}     		searchtags_ids 主键id 注意后面跟了s 多数据删除

	* @apiParam (失败返回参数：) {object}     	array 返回结果集
	* @apiParam (失败返回参数：) {string}     	array.status 返回错误码 201
	* @apiParam (失败返回参数：) {string}     	array.msg 返回错误消息
	* @apiParam (成功返回参数：) {string}     	array 返回结果集
	* @apiParam (成功返回参数：) {string}     	array.status 返回错误码 200
	* @apiParam (成功返回参数：) {string}     	array.msg 返回成功消息
	* @apiSuccessExample {json} 01 成功示例
	* {"status":"200","msg":"操作成功"}
	* @apiErrorExample {json} 02 失败示例
	* {"status":"201","msg":"操作失败"}
	*/
	function delete(){
		$idx =  $this->request->post('searchtags_ids', '', 'serach_in');
		if(empty($idx)){
			throw new ValidateException('参数错误');
		}
		$data['searchtags_id'] = explode(',',$idx);
		try{
			SearchtagsModel::destroy($data,true);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $this->ajaxReturn($this->successCode,'操作成功');
	}



}

