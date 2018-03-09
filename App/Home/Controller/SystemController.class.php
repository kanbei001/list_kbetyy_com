<?php
namespace Home\Controller;
use Think\Controller;
class SystemController extends Controller {
    /*
     * 构造函数
     */
    public function _initialize(){
        set_time_limit(15); //设置脚本最大运行时间
        if (!session('admin_id')) {
            $this->redirect('Login/index');
        }
        ini_set('date.timezone','Asia/Shanghai');//北京时区
    }
    //json格式
    public function jsonReturn($arr=array('error'=>0),$status=0) {
        header('Content-type: application/json');
        $arr['status'] = $status;
        if($arr['error'] == 0) {
            $tmparr   = debug_backtrace();
            if($tmparr[0]['line']) {
                $tmparr['error_line'] =  $tmparr[0]['line'];
            }
            if($tmparr[0]['file']) {
                $arr['error_file'] = $tmparr[0]['file'];
            }
        }
        $data = json_encode($arr);
        exit($data);
    }

    /**
     * 删除数据表数据
     *@return boole
     */
    public function delData ($id,$table){
        $tmparr   = debug_backtrace();
        if($tmparr[0]['line']) {
            $arr['error_line'] =  $tmparr[0]['line'];
        }else{
            $arr['error_line'] = '';
        }

        if($tmparr[0]['file']) {
            $arr['error_file'] = $tmparr[0]['file'];
        }else{
            $arr['error_file'] = '';
        }

        if(!$id or !$table) {
           $error = "表名或数据id没有传过来。时间:".date("Y-m-d H:s:i",time());
           $error .= "\n 位置:".__FILE__."第".$arr['error_line']."行";
           $this->controller_log($error);
            return false;
        }
        $count = is_array($id)?count($id):1;
        if(is_array($id)) {
            foreach($id as $k=>$v) {
                $where['id'][] = (int)$v;
            }
            $where['id'][] = 'OR';
        }else{
            $where['id'] = (int)$id;
        }
        $result = M($table)->where($where)->delete();
        $count_result = $count - $result;
        if($count_result == 0) {
            return true;
        }else{
            $error = "\n数据库删除失败！时间".date("Y-m-d H:i:s",time());
            $error .="\n 位置：".$arr['error_file']."第".$arr['error_line']."行";
            $error .="\n 详情：".$count_result."条数据删除失败。共".$count."条数据";
            $this->controller_log($error);
            return false;
        }
    }

    /**
     *控制器错误日志
     *@return boool
     */
    public function controller_log($detail) {
        if(!is_file("error")) {
            mkdir('error/','0777');
        }
        $file = fopen("error/contrller.log", "a");
        $detail .= "\n";
        $detail  = "\n".$detail;
        $result = fwrite($file,$detail);
        fclose($file);
        if($result) {
            return true;
        }else{
            return false;
        }
    }
}

