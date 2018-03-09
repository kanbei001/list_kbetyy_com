<?php
namespace Home\Controller;
use Think\Controller;
class ArticleController extends SystemController {
    //来院列表
    public function index(){
        $User = M('kefuinfo'); // 实例化registration对象
        $count      = $User->where(array('status'=>1))->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('last','尾页');
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where('status=1')->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign("count",$count);
        $this->display('ready-article-list');
    }
    //查看
    public function article_detail(){
       $table = M('kefuinfo');
       $id = $_GET['id'];
       $content = $table->where(array('id'=>$id))->field('bingzheng')->find();
       $this->assign('content',$content['bingzheng']);
       $this->display('article-detail');
    }
    //回收站列表
    public function recycle(){
        $User = M('kefuinfo'); // 实例化registration对象
        $count      = $User->where(array('status'=>2))->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('last','尾页');
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where('status=2')->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign("count",$count);
        $this->display('ready-article-list');
    }
    //添加回收站和删除
    public function article_delete(){
       $table = M('kefuinfo');
       $id = $_POST['id'];
       $status = $table->where(array('id'=>$id))->field('status')->find();
       if($status['status'] == 2) {
           $result = $table->where(array('id'=>$id))->delete();
           if($result) {
               echo 1;
           }else{
               echo 0;
           }
           exit;
       }
       $del_result = $table->where(array('id'=>$id))->save(array('status'=>'2'));
       if($del_result) {
           echo 1;
       }else{
           echo 0;
       }
    }
    //删除
    public function delete() {
        //dump($_POST['id']);exit;
        $table = M('kefuinfo');
        $id = $_POST['id'];
        $del_result = $table->where(array('id'=>$id))->delete();
        if($del_result) {
            echo 1;
        }else{
            echo 0;
        }
    }
    //批量删除
    public function batches(){
        $idData = I();
        $num = 0;
        $str = '';
        foreach($idData as $v){
            $result = M('kefuinfo')->where(array('id'=>$v))->field('status')->find(); //status不等于2，回收。等于2删除
            if($result['status'] == 2){
                $result = M('kefuinfo')->where(array('id'=>$v))->delete();
                if(empty($result)) {
                    $num++;
                    $str.=$v."/";
                }
            }else{
                $result = M('kefuinfo')->where(array('id'=>$v))->save(array('status'=>2));
                if(empty($result)) {
                    $num++;
                    $str.=$v."/";
                }
            }
            
        }
        if($num == 0) {
            $this->jsonReturn(array('error'=>1),1);
        }else{
            $this->jsonReturn(array('error'=>"id".$str."删除失败！"),0);
        }
        
    }
    //未处理->已处理
    public function stop(){
       $table = M('kefuinfo');
       $id = $_GET['id'];
       $del_result = $table->where(array('id'=>$id))->save(array('status'=>1));
       if($del_result) {
           echo 1;
       }else{
           echo 0;
       }
    }
    //已处理->未处理
    public function start(){
       $table = M('kefuinfo');
       $id = $_GET['id'];
       $del_result = $table->where(array('id'=>$id))->save(array('status'=>0));
       if($del_result) {
           echo 1;
       }else{
           echo 0;
       }
    }
    //未到挂号列表
    public function ready(){
        //$this->seach();
        $User = M('kefuinfo'); // 实例化registration对象
        $count      = $User->where(array('status'=>0))->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('last','尾页');
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where('status=0')->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign("count",$count);
        $this->display('ready-article-list');
    }
    //编辑挂号
    public function article_edit() {
        $id = I('id');
        $data = M("kefuinfo")->where(array('id'=>$id))->find();
        $section = M("section")->select();
        foreach($section as $v) {
            if($v['section_name'] == $data["keshi"]) {
                $data['sectionId'] = $v['id'];
            }
        }
        switch($data['huifang']) {
            case "否":
                $data['huifangId'] = 0;
                break;
            case "是":
                $data['huifangId'] = 1;
                break;
            default:
                $data['huifangId'] = 0;
        }
        $data['visitReport'] = $data['visitreport'];
        unset($data['visitreport']);
        
        $this->assign("section",$section);
        $this->assign("idData",$data);
        $this->display("article-add");
    }
    //编辑修改
    public function editor_ajax() {
        $idSection = I("post.keshi");
        $data = I();
        $idResult = M("section")->where(array('id'=>$idSection))->field("section_name")->find();
        if(empty($idResult)) {
            $this->jsonReturn();
        }
        $data['keshi']   = $idResult['section_name'];
        $data['huifang'] = empty(I("post.huifang"))?"否":"是"; //回访转换
        $id = I('post.id');
        $data['in_time'] = I("post.in_time")?I("post.in_time"):null;//时间转时间截
        $data['in_time'] = strtotime($data['in_time']);
        $result = M("kefuinfo")->where(array('id'=>$id))->save($data);
        if($result) {
            $this->jsonReturn(array('error'=>1),1);
        }else{
            $this->jsonReturn();
        }
    }
    //挂号添加
    public function add_form() {
        if(IS_AJAX){
            $idSection = I("post.keshi");
            $data = I();
            $idResult = M("section")->where(array('id'=>$idSection))->field("section_name")->find();
            if(empty($idResult)) {
                $this->jsonReturn();
            }
            $data['keshi']   = $idResult['section_name'];
            $data['huifang'] = empty(I("post.huifang"))?"否":"是"; //回访转换
            $id = I('post.id');
            $data['in_time'] = I("post.in_time")?I("post.in_time"):null;//时间转时间截
            $data['in_time'] = strtotime($data['in_time']);
            $result = M("kefuinfo")->add($data);
            if($result) {
                $this->jsonReturn(array('error'=>1),1);
            }else{
                $this->jsonReturn();
            }
        }
        $section = M("section")->select();
        $this->assign("section",$section);
        $this->display();
    }
    //搜索调用
    public function seach() {
        if(is_numeric($_GET['catecory'])) { //是否数字
           $catecory = is_numeric($_GET['catecory'])?$_GET['catecory']:'';
           if($catecory != '') {
               $catecory == 0?:$data['status'] = $catecory-1;
               !empty($_GET['startTime'])?$data['in_time'] = array(array('EGT',strtotime($_GET['startTime']))):'';
               if(!empty($data["in_time"])) {
                   !empty($_GET['endTime'])?$data['in_time'][1] = array('ELT',strtotime($_GET['endTime'])):'';//大于等于
               }else{
                   !empty($_GET['endTime'])?$data['in_time'] = array(array('ELT',strtotime($_GET['endTime']))):'';//小于等于
               }
               !empty($_GET['username'])?$data['username'] = array('like','%'.$_GET['username'].'%'):'';
               session('where',$data);
           }
            $User = M('kefuinfo'); // 实例化registration对象
            $count      = $User->where(session('where'))->count();// 查询满足要求的总记录数
            $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            $show       = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $list = $User->where(session('where'))->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
            $this->assign('list',$list);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
            $this->assign("count",$count);
            $this->display('ready-article-list');
        }
    }
    
}
