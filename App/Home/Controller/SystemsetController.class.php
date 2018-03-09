<?php
namespace Home\Controller;
use Think\Controller;
class SystemsetController extends SystemController {

    /**
     * 邮件设置
     *
     */
    public function email() {
        if($result = M('system_email')->find()) {
            $this->assign('email_conf',$result);
        }
        $this->display();
    }

    /**
     *保存邮件配置
     * @access public
     * @return json
     */
    public function email_conf() {
        $data['host'] = $_POST['host']?$_POST['host']:$this->jsonReturn();
        $data['port'] = $_POST['port']?$_POST['port']:$this->jsonReturn();
        $data['user_name'] = $_POST['user_name']?$_POST['user_name']:$this->jsonReturn();
        $data['name'] = $_POST['name']?$_POST['name']:$this->jsonReturn();
        $data['password'] = $_POST['password']?$_POST['password']:$this->jsonReturn();
        if(!$result = M('system_email')->find()) {
            $result = M('system_email')->add($data);
        }else{
            $result = M('system_email')->where(['id'=>$result['id']])->save($data);
        }
        if($result) {
            $this->jsonReturn(['error'=>1],1);
        }else{
            $this->jsonReturn();
        }
    }

    /**
     * 发送测试邮件
     * @access public
     * @return json
     */
    public function test_email() {
       $result = $_POST['test_address']?send_mail($_POST['test_address'],'这是一封测试邮件','测试邮件的内容,当你见到这内容，说明发送成功！'):0;
       if($result) {
           $this->jsonReturn(['error'=>1],1);
       }else{
           $this->jsonReturn();
       }
    }

}
?>