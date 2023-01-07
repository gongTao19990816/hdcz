<?php
/*
 module:		主题内容素材
 create_time:	2022-12-15 20:06:56
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Subjectcontent;
use base\CommonService;
use think\exception\ValidateException;

class SubjectcontentService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Subjectcontent::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['use_num'] = !is_null($data['use_num']) ? $data['use_num'] : '0';
            $data['add_time'] = time();
            $data['status'] = !is_null($data['status']) ? $data['status'] : '1';
            $res = Subjectcontent::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->subjectcontent_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            !is_null($data['add_time']) && $data['add_time'] = strtotime($data['add_time']);
            $res = Subjectcontent::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

