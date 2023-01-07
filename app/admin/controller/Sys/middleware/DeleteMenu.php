<?php

namespace app\admin\controller\Sys\middleware;

use app\admin\controller\Admin;
use app\admin\controller\Sys\model\Application;
use app\admin\controller\Sys\model\Menu;
use think\facade\Db;

class DeleteMenu extends Admin
{

    public function handle($request, \Closure $next)
    {
        $data = $request->param();
        if (in_array($data['menu_id'], [18, 19, 41])) {
            return json(['status' => '01', 'msg' => '系统模块禁止卸载']);
        };
        $menuInfo = Menu::find($data['menu_id']);
        $applicationInfo = Application::find($menuInfo['app_id']);

        $where['menu_id'] = $data['menu_id'];

        //开始删除系统的字段 操作方法
        db("field")->where($where)->delete();
        db("action")->where($where)->delete();

        $connect = $menuInfo['connect'] ? $menuInfo['connect'] : config('database.default');

        //开始删除数据表
        if (!empty($menuInfo['table_name']) && in_array($applicationInfo['app_type'], [1, 3]) && $menuInfo['table_status']) {
            $sql = Db::connect($connect)->execute('DROP TABLE if exists ' . config('database.connections.' . $connect . '.prefix') . $menuInfo['table_name']);
        }

        //开始删除相关文件
        if (!$menuInfo['url'] && !empty($menuInfo['controller_name'])) {
            $rootPath = app()->getRootPath();
            deldir($rootPath . '/app/' . $applicationInfo['app_dir'] . '/view/' . getViewName($menuInfo['controller_name']));  //删除视图
            if ($this->getSubFiles($rootPath . '/app/' . $applicationInfo['app_dir'] . '/view/' . explode('/', $menuInfo['controller_name'])[0])) {
                deldir($rootPath . '/app/' . $applicationInfo['app_dir'] . '/view/' . explode('/', $menuInfo['controller_name'])[0]);
            }


            @unlink($rootPath . '/app/' . $applicationInfo['app_dir'] . '/controller/' . $menuInfo['controller_name'] . '.php');  //删除控制器文件
            if ($this->getSubFiles($rootPath . '/app/' . $applicationInfo['app_dir'] . '/controller/' . explode('/', $menuInfo['controller_name'])[0])) {
                deldir($rootPath . '/app/' . $applicationInfo['app_dir'] . '/controller/' . explode('/', $menuInfo['controller_name'])[0]);
            }

            @unlink($rootPath . '/app/' . $applicationInfo['app_dir'] . '/model/' . $menuInfo['controller_name'] . '.php');  //删除模型
            if ($this->getSubFiles($rootPath . '/app/' . $applicationInfo['app_dir'] . '/model/' . explode('/', $menuInfo['controller_name'])[0])) {
                deldir($rootPath . '/app/' . $applicationInfo['app_dir'] . '/model/' . explode('/', $menuInfo['controller_name'])[0]);
            }

            @unlink($rootPath . '/app/' . $applicationInfo['app_dir'] . '/service/' . $menuInfo['controller_name'] . 'Service.php');  //删除服务层
            if ($this->getSubFiles($rootPath . '/app/' . $applicationInfo['app_dir'] . '/service/' . explode('/', $menuInfo['controller_name'])[0])) {
                deldir($rootPath . '/app/' . $applicationInfo['app_dir'] . '/service/' . explode('/', $menuInfo['controller_name'])[0]);
            }


            @unlink($rootPath . '/app/' . $applicationInfo['app_dir'] . '/validate/' . $menuInfo['controller_name'] . '.php');  //删除验证器
            if ($this->getSubFiles($rootPath . '/app/' . $applicationInfo['app_dir'] . '/validate/' . explode('/', $menuInfo['controller_name'])[0])) {
                deldir($rootPath . '/app/' . $applicationInfo['app_dir'] . '/validate/' . explode('/', $menuInfo['controller_name'])[0]);
            }
        }

        return $next($request);
    }

    //判断当前目录有没有其他文件
    private function getSubFiles($filepath)
    {
        $res = scandir($filepath);
        if (count($res) == 2) {
            return true;
        }
    }
}