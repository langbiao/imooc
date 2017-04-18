<?php
namespace app\index\model;

use think\Model;
use think\Db;

class Course extends Model
{
    // 首页各种推荐课程
    public static function getIndexCourse()
    {
        $data = [];
        $model = Db::name('study_video');
        // 推荐课程
        $data['recommend'] = $model->where('sv_recommend', '=', '2')->limit('5')->select();

        // 名师推荐

        // 热门课程
        $data['hot'] = $model->where('sv_hot', '=', '2')->limit('5')->select();
        // 最新课程
        $data['new'] = $model->order('sv_addtime', 'desc')->limit('5')->select();

        return $data;
    }
}