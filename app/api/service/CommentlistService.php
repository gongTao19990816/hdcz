<?php
/*
 module:		评论
 create_time:	2022-11-23 19:43:17
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Commentlist;
use base\CommonService;
use think\exception\ValidateException;

class CommentlistService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Commentlist::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            $res = Commentlist::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->comment_list_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            $res = Commentlist::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

