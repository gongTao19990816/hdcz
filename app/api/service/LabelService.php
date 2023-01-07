<?php
/*
 module:		标签素材
 create_time:	2022-12-02 15:15:40
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Label;
use base\CommonService;
use think\exception\ValidateException;

class LabelService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Label::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\Label::class)->scene('add')->check($data);
            $res = Label::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->label_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Label::class)->scene('update')->check($data);
            $res = Label::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

