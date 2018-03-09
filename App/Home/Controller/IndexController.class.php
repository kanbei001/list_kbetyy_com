<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends SystemController {
    public function index(){
        $userData = M('admin')->where(array('id'=>session('admin_id')))->find();
        $this->assign('userData',$userData);
        $this->display();
    }
    public function welcome() {
        //dump($_SERVER['HTTP_ACCEPT_LANGUAGE']);exit;
        $userData = M('admin')->where(array('id'=>session('admin_id')))->find();
        $this->assign('userData',$userData);
        $this->display();
    }
}
