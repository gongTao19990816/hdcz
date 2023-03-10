<?php

namespace app\ApplicationName\service;

use app\ApplicationName\model\Content;

class BaseService
{

    /*
     * 页面Meda信息组合
     * @return array 页面信息
     */
    public static function getMedia($title = '', $keywords = '', $description = '')
    {
        if (empty($title)) {
            $title = config('base.site_title');
        } else {
            $title = $title . ' - ' . config('base.site_title');
        }
        if (empty($keywords)) {
            $keywords = config('base.keyword');
        }
        if (empty($description)) {
            $description = config('base.description');
        }
        return checkData(['title' => $title, 'keyword' => $keywords, 'description' => $description], false);
    }

    /*
     * 上一页下一页链接信息
     * @return array 链接信息
     */
    public static function showNext($id, $classid)
    {
        $arr = [];
        $map['content_id'] = ['<', $id];
        $map['class_id'] = $classid;
        $pre = Content::where(formatWhere($map))->field('content_id,title,jumpurl')->order('sortid desc,content_id desc')->find();
        $pre = checkData($pre, false);
        if ($pre) {
            $url = getListUrl($pre);
            $str_a = '<a href="' . $url . '">' . $pre['title'] . '</a>';
        } else {
            $str_a = '没有了';
        }

        $con['content_id'] = ['>', $id];
        $con['class_id'] = $classid;
        $next = Content::where(formatWhere($con))->field('content_id,title,jumpurl')->order('sortid desc,content_id desc')->find();
        $pre = checkData($next, false);
        if ($next) {
            $url = getListUrl($next);
            $str_b = '<a href="' . $url . '">' . $next['title'] . '</a>';
        } else {
            $str_b = '没有了';
        }

        array_push($arr, $str_a, $str_b);
        return $arr;
    }


}
