<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | Copyright (c) http://thinkphp.cn All rights reserved. 
// +----------------------------------------------------------------------
// | 官方网站: http://www.thinkphp.cn/
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | Author: bb
// +----------------------------------------------------------------------

namespace service;

/**
 * 系统工具服务
 * Class ToolsService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/10/25 14:49
 */
class ToolsService {
    /**
     * Cors Options 授权处理
     */
    public static function corsOptionsHandler() {
        if (request()->isOptions()) {
            header('Access-Control-Allow-Origin:*');
            header('Access-Control-Allow-Headers:Accept,Referer,Host,Keep-Alive,User-Agent,X-Requested-With,Cache-Control,Content-Type,token');
            header('Access-Control-Allow-Credentials:true');
            header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
            header('Access-Control-Max-Age:1728000');
            header('Content-Type:text/plain charset=UTF-8');
            header('Content-Length: 0', true);
            header('status: 204');
            header('HTTP/1.0 204 No Content');
            exit;
        }
    }

    /**
     * Cors Request Header信息
     * @return array
     */
    public static function corsRequestHander() {
        return [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Allow-Methods'     => 'GET,POST,OPTIONS',
            'X-Support'                        => 'service@cuci.cc',
            'X-Servers'                        => 'Guangzhou Cuci Technology Co. Ltd',
        ];
    }

    /**
     * 一维数据数组生成数据树
     * @param array $list 数据列表
     * @param string $id 父ID Key
     * @param string $pid ID Key
     * @param string $son 定义子数据Key
     * @return array
     */
    public static function arr2tree($list, $id = 'id', $pid = 'pid', $son = 'sub') {
        $tree = $map = array();
        foreach ($list as $item) {
            $map[$item[$id]] = $item;
        }
        foreach ($list as $item) {
            if (isset($item[$pid]) && isset($map[$item[$pid]])) {
                $map[$item[$pid]][$son][] = &$map[$item[$id]];
            } else {
                $tree[] = &$map[$item[$id]];
            }
        }
        unset($map);
        return $tree;
    }

    /**
     * 一维数据数组生成数据树
     * @param array $list 数据列表
     * @param string $id ID Key
     * @param string $pid 父ID Key
     * @param string $path
     * @return array
     */
    public static function arr2table($list, $id = 'id', $pid = 'pid', $path = 'path', $ppath = '') {
        $_array_tree = self::arr2tree($list, $id, $pid);
        $tree = array();
        foreach ($_array_tree as $_tree) {
            $_tree[$path] = $ppath . '-' . $_tree[$id];
            $_tree['spl'] = str_repeat("&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;", substr_count($ppath, '-'));
            if (!isset($_tree['sub'])) {
                $_tree['sub'] = array();
            }
            $sub = $_tree['sub'];
            unset($_tree['sub']);
            $tree[] = $_tree;
            if (!empty($sub)) {
                $sub_array = self::arr2table($sub, $id, $pid, $path, $_tree[$path]);
                $tree = array_merge($tree, (Array)$sub_array);
            }
        }
        return $tree;
    }

    /**
     * 获取数据树子ID
     * @param array $list 数据列表
     * @param int $id 起始ID
     * @param string $key 子Key
     * @param string $pkey 父Key
     * @return array
     */
    public static function getArrSubIds($list, $id = 0, $key = 'id', $pkey = 'pid') {
        $ids = array(intval($id));
        foreach ($list as $vo) {
            if (intval($vo[$pkey]) > 0 && intval($vo[$pkey]) == intval($id)) {
                $ids = array_merge($ids, self::getArrSubIds($list, intval($vo[$key]), $key, $pkey));
            }
        }
        return $ids;
    }

}
