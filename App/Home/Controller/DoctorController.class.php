<?php
namespace Home\Controller;
use Think\Controller;
class DoctorController extends SystemController
{
    /**
     *医生列表
     * @access public
     */
    public function doctor_list() {
        $doctors = M('doctor')->select();
        $sections = M('section')->select();
        if($sections && $doctors) {
            foreach($sections as $k=>$v) {
                $sections[$v['id']] = $v;
            }
            foreach($doctors as $k=>$v) {
                $doctors[$k]['section_name'] = $sections[$v[ 'keshi_id']]['section_name'];
            }
            $this->assign('doctors',$doctors);
        }
        $this->display();
    }

    /**
     * 添加医生
     * @access public
     */
    public function add_doctor() {
        $sections = M('section')->select();
        if(!$sections) {
            echo "无科室数据";exit;
        }
        $this->assign('sections',$sections);
        $this->display();
    }

    /**
     * ajax增加保存医生
     * @receive ajax
     * @access public
     * @return json
     */
    public function ajax_add_doctor() {
       $data['doctor_name'] = $_POST['doctor_name']?$_POST['doctor_name']:$this->jsonReturn();
       $data['keshi_id']    = $_POST['keshi_id']?$_POST['keshi_id']:$this->jsonReturn();
       $result = M('doctor')->add($data);
       if($result){
           $this->jsonReturn(['error'=>1]);
       }else{
           $this->jsonReturn();
       }
    }

    /**
     * ajax删除医生
     * @receive json
     * @return json
     */
    public function delete() {
        $result = $_POST['id']?$this->delData($_POST['id'],'doctor'):$this->jsonReturn();
        if($result) {
            $this->jsonReturn(['error'=>1]);
        }else{
            $this->jsonReturn(['errors'=>0,'errors_det'=>'删除失败！']);
        }
    }

    /**
     * 编辑医生
     */
    public function edi_doctor() {
        $_GET['id']?:$this->jsonReturn(['error'=>0,'error_det'=>'参数不足']);
        $doctor = M('doctor')->where(['id'=>$_GET['id']])->find();
        $doctor?$this->assign('doctor',$doctor):'';
        $sections = M('section')->select();
        $this->assign('sections',$sections);
        $this->display();
    }

    /**
     * ajax保存编辑
     * @receive json
     * @echo  json
     */
    public  function doctor_save() {
        $data['doctor_name']   = $_POST['doctor_name']?$_POST['doctor_name']:$this->jsonReturn(['error'=>0,'error_det'=>'没有$_POST["doctor_name"]参数'].__LINE__);
        $data['keshi_id']     = $_POST['keshi_id']?$_POST['keshi_id']:$this->jsonReturn(['error'=>0,'error_det'=>'没有$_POST["keshi_id"]参数'.__LINE__]);
        $data['id']           = $_POST['id']?$_POST['id']:$this->jsonReturn(['error'=>0,'error_det'=>'没有$_POST["id"]参数'].__LINE__);
        $intersect_A = M('doctor')->where(['id'=>$data['id']])->find();
        $intersect_result = array_intersect_assoc($intersect_A,$data);

        if($intersect_result == $data) {
            $this->jsonReturn(['error'=>0,'error_det'=>'大哥别玩了，你要根本就没有修改过好吗！第'.__LINE__.'行']);
        }
        $result = M('doctor')->save($data);
        if($result) {
            $this->jsonReturn(['error'=>1]);
        }else{
            $this->jsonReturn(['error'=>0,'error'=>'保存失败第'.__LINE__.'行']);
        }
    }
}