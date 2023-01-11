<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 自定义配置
// +----------------------------------------------------------------------
return [

    'config_module_id' => 41,
    'max_dump_data' => 50000,
    'new_upload_dir'    => './uploads/hdcz',
    'upload_dir' => './uploads/hdcz',
    'upload_subdir' => 'Ym',
    'nocheck' => ['admin/Login/verify', 'admin/Login/index', 'admin/Index/index', 'admin/Index/main', 'admin/Login/out', 'admin/Upload/editorUpload', 'admin/Upload/uploadImages', 'admin/Upload/uploadUeditor', 'admin/Login/captcha', 'admin/Login/aliOssCallBack'],
    'img_show_status' => true,
    'error_log_code' => 500,

    'export_per_num' => 50,
    'import_type' => 'xls',

    'password_secrect' => '373f4b4ac',

    //api基本配置
    'api_input_log' => true,
    'successCode' => '200',                //成功返回码
    'errorCode' => '201',                //错误返回码
    'jwtExpireCode' => '101',                //jwt过期
    'jwtErrorCode' => '102',                //jwt无效

    //聚合短信配置
    'juhe_sms_key' => '3420d7egshdjhshjdsh77776767c373f4b4ac',        //key
    'juhe_sms_tempCode' => '11205725',                                    //短信验证码模板

    //极速短信配置
    'jisu_sms_key' => '892d93ac22b27ee9',                            //key
    'jisu_sms_tempCode' => '20492',                                        //短信验证码模板

    //阿里云短信配置
    'ali_sms_accessKeyId' => 'LTAI4Fjisdd3ALfdXRxLB',                //阿里云短信 keyId
    'ali_sms_accessKeySecret' => 'Wy5isYqtT0eYoePK6m2QjZ8Dc',    //阿里云短信 keysecret
    'ali_sms_signname' => 'daguaishou',                            //签名
    'ali_sms_tempCode' => 'SMS_19762314',                        //短信模板 Code

    //oss开启状态 以及配置指定oss
    'oss_status' => false,            //true启用  false 不启用
    'oss_upload_type' => 'server',        //client 客户端直传  server 服务端传
    'oss_default_type' => 'ali',            //oss使用类别 则使用ali的oss  qiniuyun 则使用七牛云oss

    //阿里云oss配置
    'ali_oss_accessKeyId' => 'LTAI4FjisdtALfdXRxLB',                        //阿里云短信 keyId
    'ali_oss_accessKeySecret' => 'Wy5isYVqtT0g7eZLYoePK6m2QjZ8Dc',        //阿里云短信 keysecret
    'ali_oss_endpoint' => 'http://i.whpjs.vip',    //建议填写自己绑定的域名
    'ali_oss_bucket' => 'daguaishou',                            //阿里bucket

    //七牛云oss配置
    'qny_oss_accessKey' => 'bm1sR9bx0F5KYq2RtAhZMJ8zOxb-HCGYx5pJU',  //access_key
    'qny_oss_secretKey' => 'YrRaySbqu7M1PIzZHOguJMT0ObUdb7GBPRiYa7Lq',     //secret_key
    'qny_oss_bucket' => 'daguaishou',                            //bucket
    'qny_oss_domain' => 'http://images.whpjs.vip',        //

    //jwt鉴权配置
    'jwt_expire_time' => 72000,                //token过期时间 默认2小时
    'jwt_secrect' => 'boTCfOGKwqTNKArT',    //签名秘钥
    'jwt_iss' => 'client.daguaishou',    //发送端
    'jwt_aud' => 'server.daguaishou',    //接收端

    //api上传配置
    'api_upload_domain' => '',                        //如果做本地存储 请解析一个域名到/public/upload目录  也可以不解析
    'api_upload_ext' => 'jpg,jpeg,png,gif,mp4,mov,image',            //api允许上传文件
    'api_upload_max' => 6 * 1024 * 1024,            //默认2M

    //小程序配置
    'mini_program' => [
        'app_id' => 'wxf77d319b',                    //小程序appid
        'secret' => '23d7191f7eb4762c197b835d',        //小程序secret
    ],

    //公众号配置
    'official_accounts' => [
        'app_id' => 'wxa2c835664852',                                                //公众号appid
        'secret' => '2d7ef1db70dccd9e5e744',                                    //公众号secret
        'token' => 'chengdie',
    ],

    'pay_display' => 1,

    //微信支付配置
    'wechart_pay' => [
        'mch_id' => '1346201',                                                            //商户号
        'key' => 'e4a4ab530b3bec6734cdf52',                                        //微信支付32位秘钥
        'cert_path' => app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_cert.pem',    //证书路径
        'key_path' => app()->getRootPath() . 'extend/utils/wechart/zcerts/apiclient_key.pem',    //证书路径
        'rsa_public_key_path' => app()->getRootPath() . 'extend/utils/wechart/zcerts/public.pem',    //rsa公钥
    ],

    'upload_hash_status' => true,
    'filed_name_status' => false,
    'reset_button_status' => false,
    'api_upload_auth' => false,

    //文件注释
    'comment' => [
        'api_comment' => true,
        'file_comment' => true,
        'author' => '大怪兽',
        'contact' => '',
    ],
    'host_url' => 'http://192.168.4.30:8087',

// 	'main_link'   =>'http://165.22.102.235:8004/', 旧的
    'main_link' => 'http://192.74.232.45:8004/',
    'TT_PRO' => 'http://coralip.com/api/v2/getIP?username=pps_ccedlm&password=ssviezjyez&noloop=0&protocol=0&count=1&region=SG&keep_time=2&type=text', //tt socks5代理链接地址
    'TT_PRO_HTTP' => 'http://coralip.com/api/v2/getIP?username=pps_ccedlm&password=ssviezjyez&noloop=0&protocol=1&count=1&region=SG&keep_time=2&type=text', //tt http代理链接地址
    //请求外部接口
    'link_url' => [
        'login' => 'api/v1/login',
        'userinfo' => 'api/v1/ttapi/profile_self',
    ],
    "task_key_prefix" => "task:",
    "task_max_num" => 10000

];
