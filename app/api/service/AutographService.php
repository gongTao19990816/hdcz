<?php
/*
 module:		个性签名库
 create_time:	2022-11-16 12:32:30
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Autograph;
use base\CommonService;
use think\exception\ValidateException;

class AutographService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = db('autograph')->field($field)->alias('a')->join('typecontrol b', 'a.typecontrol_id=b.typecontrol_id', 'left')->where($where)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\Autograph::class)->scene('add')->check($data);
            $data['status'] = !is_null($data['status']) ? $data['status'] : '1';
            $res = Autograph::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->autograph_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Autograph::class)->scene('update')->check($data);
            $data['usage_time'] = time();
            $res = Autograph::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

