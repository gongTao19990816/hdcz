<?php 
/*
 module:		评论话术
 create_time:	2023-01-05 16:33:37
 author:		大怪兽
 contact:		
*/

namespace app\api\service;
use app\api\model\Commentary;
use think\facade\Log;
use think\exception\ValidateException;
use base\CommonService;

class CommentaryService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$orderby,$limit,$page){
		try{
			$res = Commentary::where($where)->field($field)->order($orderby)->paginate(['list_rows'=>$limit,'page'=>$page])->toArray();
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['list'=>$res['data'],'count'=>$res['total']];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$res = Commentary::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res->commentary_id;
	}


	/*
 	* @Description  修改
 	*/
	public static function update($where,$data){
		try{
			$res = Commentary::where($where)->update($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return $res;
	}




}

