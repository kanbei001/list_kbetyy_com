<?php
namespace Home\Controller;
use Think\Controller;
class SectionController extends SystemController {
    //列表
    public function sectionList(){
        $sectionList = M('section')->order('sort asc')->select();
        $count       = count($sectionList);
        $this->assign("count",$count);
        $this->assign('sectionList',$sectionList);
        $this->display();
    }
    //编辑
    public function editor(){
        $id = $_GET['id']?$_GET['id']:null;
        if($id != null) {
            $sectionInfo = M('section')->where(array('id'=>$id))->find();
            $this->assign("sectionInfo",$sectionInfo);
        }
        $this->display();
    }
    //保存
    public function save(){
        $sectionInfo = I();
        if($sectionInfo) {
            $result = M("section")->save($sectionInfo);
            if($result) {
                $this->jsonReturn(array('error'=>1),1);
            }else{
                $this->jsonReturn();
            }
        }
        
    }
    //单删除
    public function delete() {
        $id = $_POST['id'];
        if($id) {
            $result = M('section')->where(array('id'=>$id))->delete();
        }
        if($result) {
            $this->jsonReturn(array('error'=>1),1);
        }else{
            $this->jsonReturn();
        }
    }
    //批量删除
    public function batches() {
        $idArr =  $_POST;
        $str = '';
        if($idArr) {
            foreach($idArr as $id) {
                $result = M('section')->where(array('id'=>$id))->delete();
                if(empty($result)) {
                    $str = $str."/".$id;
                }
            }
            if(empty($str)) {
                $this->jsonReturn(array('error'=>1),1);
            }else{
                $this->jsonReturn(array('error'=>$str));
            }
        }
    }
    //添加
    public function add (){
        if(IS_POST) {
            if($_POST['section_name']){
                $section_name = $_POST["section_name"];
                $sort         = $_POST["sort"];
                $result = M('section')->add(array('section_name'=>$section_name,'sort'=>$sort));
            }
            if($result) {
                $this->jsonReturn(array('error'=>1),1);
            }else{
                $this->jsonReturn();
            }
            exit;
        }
        
        $this->display();
    }

    //病种列表
    public function category () {
        $section_result = M("section")->select();
        $bz_result  = M("bingzhong")->select();

        foreach($bz_result as $k=>$v) {
            foreach($section_result as $inSecionKey=>$inSectionVal) {
                if(array_search($v['keshi_id'],$inSectionVal)) {
                    $bz_result[$k]['keshi'] =  $section_result[$inSecionKey]['section_name'];
                    break;
                }
            }
        }
        $this->assign("bingzhong",$bz_result);
        $this->display();
    }
    //批量删除病种
    public function bingzhong_batches() {
        $idArr =  $_POST;
        $str = '';
        if($idArr) {
            foreach($idArr as $id) {
                $result = M('bingzhong')->where(array('id'=>$id))->delete();
                if(empty($result)) {
                    $str = $str."/".$id;
                }
            }
            if(empty($str)) {
                $this->jsonReturn(array('error'=>1),1);
            }else{
                $this->jsonReturn(array('error'=>$str));
            }
        }
    }
    //删除病种
    public function bingzhong_delete() {
        $id = $_POST['id'];
        if($id) {
            $result = M('bingzhong')->where(array('id'=>$id))->delete();
        }
        if($result) {
            $this->jsonReturn(array('error'=>1),1);
        }else{
            $this->jsonReturn();
        }
    }
    //病种编辑
    public function bingzhong_editor(){
        $id = $_GET['id']?$_GET['id']:null;
        if($id != null) {
            $sectionInfo = M('bingzhong')->where(array('id'=>$id))->find();
            $keshi = M("section")->select();
           // dump($keshi);exit;
            $this->assign("keshi",$keshi);
            $this->assign("bingzhong",$sectionInfo);
        }
        $this->display();
    }

    //病种保存
    public function bingzhong_save(){
        $sectionInfo = I();
        $data['bingzhong_name'] = $sectionInfo['bingzhong']?$sectionInfo['bingzhong']:$this->jsonReturn();
        $data['id'] = $sectionInfo['id']?$sectionInfo['id']:$this->jsonReturn();
        $data['keshi_id'] = $sectionInfo['keshi']?$sectionInfo['keshi']:$this->jsonReturn();
        if($sectionInfo) {
            $result = M("bingzhong")->save($data);
            if($result) {
                $this->jsonReturn(array('error'=>1),1);
            }else{
                $this->jsonReturn();
            }
        }
    }

    //添加病种
    public function bingzhong_add () {
        $keshi = M("section")->select();
        $this->assign('keshi',$keshi);
        $this->display();
    }
    //添加保存病种
    public function ajax_bingzhong_add() {
        $sectionInfo = I();
        $data['bingzhong_name'] = $sectionInfo['bingzhong']?$sectionInfo['bingzhong']:$this->jsonReturn();
        $data['keshi_id'] = $sectionInfo['keshi']?$sectionInfo['keshi']:$this->jsonReturn();
        if($sectionInfo) {
            $result = M("bingzhong")->add($data);
            if($result) {
                $this->jsonReturn(array('error'=>1),1);
            }else{
                $this->jsonReturn();
            }
        }
    }

}
