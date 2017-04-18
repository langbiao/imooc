<?php
namespace app\index\controller;

use think\Db;

class User extends Base
{
    private $uid = 0;
    public function _initialize()
    {
        parent::_initialize();

        $this->uid = 0;
    }

   
}
