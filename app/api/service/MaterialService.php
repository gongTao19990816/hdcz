<?php
/*
 module:		素材管理
 create_time:	2022-11-14 20:14:42
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Material;
use base\CommonService;
use think\exception\ValidateException;

class MaterialService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = db('material')->field($field)->alias('a')->join('typecontrol b', 'a.typecontrol_id=b.typecontrol_id', 'left')->where($where)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\Material::class)->scene('add')->check($data);
            $data['add_time'] = time();
            $res = Material::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->material_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Material::class)->scene('update')->check($data);
            !is_null($data['add_time']) && $data['add_time'] = strtotime($data['add_time']);
            $res = Material::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

