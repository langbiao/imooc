<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | Copyright (c) http://thinkphp.cn All rights reserved. 
// +----------------------------------------------------------------------
// | 官方网站: http://www.thinkphp.cn/
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: bb
// +----------------------------------------------------------------------

namespace service;

use think\Db;

/**
 * 基础数据服务
 * Class DataService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/22 15:32
 */
class DataService {

    /**
     * 删除指定序号
     * @param string $sequence
     * @param string $type
     * @return bool
     */
    public static function deleteSequence($sequence, $type = 'SYSTEM') {
        $data = ['sequence' => $sequence, 'type' => strtoupper($type)];
        return Db::name('SystemSequence')->where($data)->delete();
    }

    /**
     * 生成唯一序号 (失败返回 NULL )
     * @param int $length 序号长度
     * @param string $type 序号顾类型
     * @return string
     */
    public static function createSequence($length = 10, $type = 'SYSTEM') {
        $times = 0;
        while ($times++ < 10) {
            $sequence = '';
            $i = 0;
            while ($i++ < $length) {
                $sequence .= ($i <= 1 ? rand(1, 9) : rand(0, 9));
            }
            $data = ['sequence' => $sequence, 'type' => strtoupper($type)];
            if (Db::name('SystemSequence')->where($data)->count() < 1 && Db::name('SystemSequence')->insert($data)) {
                return $sequence;
            }
        }
        return null;
    }

    /**
     * 数据增量保存
     * @param \think\db\Query|string $db 数据查询对象
     * @param array $data 需要保存或更新的数据
     * @param string $upkey 条件主键限制
     * @param array $where 其它的where条件
     * @return bool
     */
    public static function save($db, $data, $upkey = 'id', $where = []) {
        if (is_string($db)) {
            $db = Db::name($db);
        }
        $fields = $db->getTableFields(['table' => $db->getTable()]);
        $_data = [];
        foreach ($data as $k => $v) {
            in_array($k, $fields) && ($_data[$k] = $v);
        }
        if (self::_apply_save_where($db, $data, $upkey, $where)->count() > 0) {
            return self::_apply_save_where($db, $data, $upkey, $where)->update($_data) !== FALSE;
        }
        return self::_apply_save_where($db, $data, $upkey, $where)->insert($_data) !== FALSE;
    }

    /**
     * 应用 where 条件
     * @param \think\db\Query|string $db 数据查询对象
     * @param array $data 需要保存或更新的数据
     * @param string $upkey 条件主键限制
     * @param array $where 其它的where条件
     * @return \think\db\Query
     */
    protected static function _apply_save_where(&$db, $data, $upkey, $where) {
        foreach (is_string($upkey) ? explode(',', $upkey) : $upkey as $v) {
            if (is_string($v) && array_key_exists($v, $data)) {
                $db->where($v, $data[$v]);
            } elseif (is_string($v)) {
                $db->where("{$v} IS NULL");
            }
        }
        return $db->where($where);
    }

    /**
     * 更新数据表内容
     * @param \think\db\Query|string $db 数据查询对象
     * @param array $where 额外查询条件
     * @return bool|null
     */
    public static function update(&$db, $where = []) {
        if (is_string($db)) {
            $db = Db::name($db);
        }
        $ids = explode(',', input("post.id", ''));
        $field = input('post.field', '');
        $value = input('post.value', '');
        $pk = $db->getPk(['table' => $db->getTable()]);
        $db->where(empty($pk) ? 'id' : $pk, 'in', $ids);
        !empty($where) && $db->where($where);
        // 删除模式
        if ($field === 'delete') {
            $fields = $db->getTableFields(['table' => $db->getTable()]);
            if (in_array('is_deleted', $fields)) {
                return false !== $db->update(['is_deleted' => 1]);
            }
            return false !== $db->delete();
        }
        // 更新模式
        return false !== $db->update([$field => $value]);
    }

}
