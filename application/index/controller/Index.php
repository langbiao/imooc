<?php
namespace app\index\controller;

use think\Db;
use app\index\model\Course;

class Index extends Base
{
    public function index()
    {

        // 分类
        $category = Db::name('study_category')->order('sc_sort')->select();

        // 分类id
        $sc_id = array_column($category, 'sc_id');

        // 分类下的课程
        $course = Db::name('study_course')->where('sc_c_id', 'in', $sc_id)->order('sc_sort')->select();

        // 格式化课程数据
        $course_data = [];
        foreach ($course as $co) {
            $tmp['sc_id'] = $co['sc_id'];
            $tmp['sc_name'] = $co['sc_name'];
            $course_data[$co['sc_c_id']][] = $tmp;
        }

        $data = [];

        foreach ($category as $key=>$cat) {
            $data[$cat['sc_id']]['id'] = $cat['sc_id'];
            $data[$cat['sc_id']]['name'] = $cat['sc_name'];
            $data[$cat['sc_id']]['child'] = $course_data[$cat['sc_id']];
        }
//        echo "<pre>";
//        print_r($data);die;

        // 首页各种推荐课程
        $res = Course::getIndexCourse();

//        dump($res);die;

        $this->assign('res', $res);
        $this->assign('category', $category);
        $this->assign('data', $data);
        return view();
    }
}
