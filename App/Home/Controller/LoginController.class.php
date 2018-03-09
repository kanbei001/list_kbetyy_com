<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    //分录页面
    public function index(){
        $this->display('login');
    }
    /*
     *  验证码
     */
    public function verify(){
        $obj = new \Think\Verify();
        $obj->fontSize = 14; // 字体大小
        $obj->codeSet = '0123456789';
        $obj->useNoise =false;
        $obj->length = 4; // 验证码长度
        $obj->imageW = 95; // 图片宽度
        $obj->imageH = 40; // 图片高度
        $obj->entry();
    }
    /*
     * 验证管理员账户、密码
     */
    public function checkAdmin(){
        $name = I("post.name");
        $passwd = I("post.passwd");
        $code = I("post.verify");
        if(empty($name)){
            $this->jsonReturn('请输入账户名',400);
        }
        if(empty($passwd)){
            $this->jsonReturn('请输入密码',400);
        }
        $obj = new \Think\Verify();
        if(!$obj->check($code)){
            $this->jsonReturn('验证码不正确',400);
        }
        $info = M('admin')->where(array('name'=>$name))->find();
        if(empty($info)){
            $this->jsonReturn('用户名或密码不正确',400);
        } else if(md5($passwd) != $info['passwd']){
            $this->jsonReturn('用户名或密码不正确',400);
        } else{
            session('admin_id',$info['id']);
            $info['last_ip']      = $info['current_ip'];
            $info['current_ip']   = $_SERVER["REMOTE_ADDR"];
            $info['last_time']    = $info['current_time'];
            $info['current_time'] = time();
            $info['limit']        = $info['limits']++;
            M('admin')->where(array('id'=>$info['id']))->save($info);
            $this->jsonReturn('登录成功',1);
            
        }
    }
     /*
     * 退出登录
     */
    public function logout(){
        session(null);
        $this->redirect('Index/index');
    }
    /*
    *json格式
    */
    public function jsonReturn($str,$error) {
        $data['status'] = $error;
        $data['report'] = $str;
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }
}   

