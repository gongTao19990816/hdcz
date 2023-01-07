<?php

//接口路由文件

use think\facade\Route;

Route::rule('User/index', 'User/index')->middleware(['JwtAuth']);	//用户管理首页数据列表;
Route::rule('User/login', 'User/login')->middleware(['CaptchaAuth']);	//用户管理登录;
Route::rule('Demo/index', 'Demo/index')->middleware(['JwtAuth']);	//doem首页数据列表;
Route::rule('Demo/add', 'Demo/add')->middleware(['JwtAuth']);	//doem添加;
Route::rule('Apiuser/update', 'Apiuser/update')->middleware(['JwtAuth']);	//api_user修改;
Route::rule('Apiuser/index', 'Apiuser/index')->middleware(['JwtAuth']);	//api_user首页数据列表;
Route::rule('Apiusergroup/add', 'Apiusergroup/add')->middleware(['JwtAuth']);	//api_user_group添加;
Route::rule('Apiuser/login', 'Apiuser/login')->middleware(['CaptchaAuth']);	//api_user登录;
Route::rule('Apiuser/UpPass', 'Apiuser/UpPass')->middleware(['JwtAuth']);	//api_user修改密码;
Route::rule('Apiusergroup/update', 'Apiusergroup/update')->middleware(['JwtAuth']);	//api_user_group修改;
Route::rule('Apiusergroup/delete', 'Apiusergroup/delete')->middleware(['JwtAuth']);	//api_user_group删除;
Route::rule('Apiusergroup/index', 'Apiusergroup/index')->middleware(['JwtAuth']);	//api_user_group首页数据列表;
Route::rule('Apiuserrule/index', 'Apiuserrule/index')->middleware(['JwtAuth']);	//api_user_rule首页数据列表;
Route::rule('Apiuserrule/add', 'Apiuserrule/add')->middleware(['JwtAuth']);	//api_user_rule添加;
Route::rule('Apiuserrule/update', 'Apiuserrule/update')->middleware(['JwtAuth']);	//api_user_rule修改;
Route::rule('Apiuserrule/delete', 'Apiuserrule/delete')->middleware(['JwtAuth']);	//api_user_rule删除;


