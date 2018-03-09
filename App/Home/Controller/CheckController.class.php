<?php
namespace Home\Controller;
use Think\Controller;
class CheckController extends SystemController
{
    /******************************************[ 题卷列表 ]*************************************************************/
    /**
     * 题卷列表
     */
    public function questionnaire_list() {
        $User = M('questionnaire_list'); // 实例化registration对象
        $count      = $User->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,3);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k=>$v) {
            $tmparr = json_decode($v['content']);
            if(is_array($tmparr)) {
                $list[$k]['questions'] = count($tmparr);
            }else{
                $list[$k]['questions'] =0;
            }
        }
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign("count",$count);
        $this->display();
    }
    /**
     *添加题卷
     */
    public function add_questionnaire(){
        $this->display();
    }
    /**
     *保存题卷
     */
    public function save_questionnaire() {
        $data['name']      = $_POST['name']?$_POST['name']:$this->jsonReturn();
        $data['remark']    = $_POST['remark']?$_POST['remark']:'';
        $data['time']      = time();
        $data['is_quest']  = 1;
        if(M('questionnaire_list')->add($data)) {
            $this->jsonReturn(array('error'=>1),1);
        }else{
            $this->redirect(array('error'=>0,'error_det'=>'添加数据库失败'));
        }
    }

    /**
     *禁止访问
     */
    public function quest_stop() {
        if($_POST['id']) {
            if(M('questionnaire_list')->where(array('id'=>$_POST['id']))->save(['is_quest'=>0])) {
                $this->jsonReturn(['error=>1'],1);
            }else{
                $this->jsonReturn();
            }
        }else{
            $this->jsonReturn();
        }
    }
    /**
     *允许访问
     */
    public function quest_start() {
        if($_POST['id']) {
            if(M('questionnaire_list')->where(array('id'=>$_POST['id']))->save(['is_quest'=>1])) {
                $this->jsonReturn(['error=>1'],1);
            }else{
                $this->jsonReturn();
            }
        }else{
            $this->jsonReturn();
        }
    }

    /**
     *删除题卷
     */
    public function del_questionnaire() {
        if($_POST['id']) {
            if(M('questionnaire_list')->where(array('id'=>$_POST['id']))->delete()) {
                $this->jsonReturn(['error=>1'],1);
            }else{
                $this->jsonReturn();
            }
        }else{
            $this->jsonReturn();
        }
    }

    /**
     *批量删除
     */
    public function batches(){
        $idData = $_POST?$_POST:$this->jsonReturn();
        $num = 0;
        $str = '';
        foreach($idData as $v){
            $result = M('questionnaire_list')->where(array('id'=>$v))->delete();
            if(empty($result)) {
                $num++;
                $str.=$v."/";
            }
        }
        if($num == 0) {
            $this->jsonReturn(array('error'=>1),1);
        }else{
            $this->jsonReturn(array('error'=>"id".$str."删除失败！"),0);
        }
    }

    /******************************************[ 题卷列表 ----> 题卷题目操作 ]*************************************************************/
    /**
     *题目列表
     */
    public function question_list() {
        if(!$_GET['id']){
            echo "没有传id过来";
            exit;
        }
        $questionnaire = M('questionnaire_list')->where(['id'=>$_GET['id']])->find();
        if($questionnaire['content']) {
            $tmparr = json_decode($questionnaire['content'],true);
            $num = 0;
            foreach($tmparr as $k=>$v) {
                $tmparr[$num] = (int)$v;
                $num++;
            }
            $where['id'] = $tmparr;
            $where['id'][] = 'OR';
            $result = M('question_bank')->where($where)->select();
            foreach($result as $k=>$v) {
                if($v['type'] == 'choose') {
                    $tmparr = json_decode($v['content'],true);
                }
                $bingzhong = M('bingzhong')->select();
                foreach($bingzhong as $key=>$value) {
                    $bingzhong[$value['id']] = $value;
                }
                unset($choose);
                foreach($tmparr as $key2=>$value2) {
                    foreach($value2 as $ink=>$inv) {
                        $choose[] = $inv.'|'.$bingzhong[$ink]['bingzhong_name'];
                    }
                }
                $result[$k]['content'] = $choose;
            }
            $this->assign('questions',$result);
        }
        $this->assign('questionnaire',$questionnaire);
        $this->display();
    }

    /**
     *添加题目
     */
    public function add_question() {
        if(!$questionnaire_id = $_GET['questionnaire_id']) {
            echo "没有题卷id传过来！";
        }
        $keshi = M('section')->select();
        $this->assign('questionnaire_id',$questionnaire_id);
        $this->assign('keshi',$keshi);
        $this->display();
    }

    /**
     * ajax题库分页
     *
     */
    public function ajax_question_bank() {
        $User = M('question_bank'); // 实例化registration对象
        $count      = $User->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('last','尾页');
        $Page->setConfig('first','首页');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->order('id asc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k=>$v) {
            if($v['type'] == 'choose') {
                $tmparr = json_decode($v['content'],true);
            }
            $bingzhong = M('bingzhong')->select();
            foreach($bingzhong as $key=>$value) {
                $bingzhong[$value['id']] = $value;
            }
            unset($choose);
            foreach($tmparr as $key2=>$value2) {
                foreach($value2 as $ink=>$inv) {
                    $choose[] = $inv.'|'.$bingzhong[$ink]['bingzhong_name'];
                }
            }
            $list[$k]['content'] = $choose;
        }
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign("count",$count);
        $this->display();
    }

    /**
     *ajax保存添加的题目
     */
    public function ajax_add_question() {
        $_POST?$_POST:$this->jsonReturn(_);
        $data['type']     = $_POST['type']?$_POST['type']:$this->jsonReturn(['error'=>0,'error_det'=>'没有类型参数']);
        $data['time']     = time();
        $data['description']    = $_POST['remark'];
        $questionnaire_id     = $_POST['questionnaire']?$_POST['questionnaire']:$this->jsonReturn(['error'=>0,'error_det'=>'没有题目卷参数']); //题卷id
        if($data['type'] == 'choose') {
            foreach($_POST as $k=>$v) {
                if(strchr($k,'_')) {
                    $tmparr[$k] = $v;
                }
            }
            foreach($tmparr as $v) {
                $arr = explode('|',$v);
                $content[] = [$arr[1]=>$arr[0]];
            }
            $data['content']        = json_encode($content);
        }
        $success_id = M('question_bank')->add($data);
        if($success_id){
            $result = $this->questionnaire_add_question($questionnaire_id,$success_id);//将题库id存进题卷
            if($result) {
                $this->jsonReturn(['error'=>1],1);
            }else{
                $this->jsonReturn(['error'=>0,'error_det'=>'数据存不进题卷'],1);
            }
        }
    }
    /**
     * 从题库批量导入题目
     * @return json
     */
    public function lay_in() {
        $questionnaire_id = $_POST['questionnaire']?$_POST['questionnaire']:$this->jsonReturn();
        unset($_POST['questionnaire']);
        $result = $this->questionnaire_add_question($questionnaire_id,$_POST);
        if($result) {
            $this->jsonReturn(["error"=>1],1);
        }else{
            $this->jsonReturn();
        }
    }
    /**
     *删除题目
     *@return json
     */
    public function ajax_question_del(){
        $questionnaire_id = $_POST['questionnaire_id']?$_POST['questionnaire_id']:$this->jsonReturn();
        unset($_POST['questionnaire_id']);
        $content = M('questionnaire_list')->where(['id'=>$questionnaire_id])->field('content')->find();
        $content = json_decode($content['content'],true);
        foreach($_POST as $v) {
            $content_key = array_search($v,$content);
            unset($content[$content_key]);
        }
        $content = json_encode($content);
        $result = M('questionnaire_list')->where(['id'=>$questionnaire_id])->save(['content'=>$content]);
        if($result) {
            $this->jsonReturn(['error'=>1],1);
        }else{
            $this->jsonReturn();
        }
    }
    /**
     * 请求对应科室病种
     */
    public function ajax_bingzhong() {
        $_POST['keshi']?$_POST['keshi']:$this->jsonReturn();
        $result = M("bingzhong")->where(['keshi_id'=>$_POST['keshi']])->select();
        if($result) {
            $this->jsonReturn($result,1);
        }else{
            $keshi = M('section')->where(['id'=>$_POST['keshi']])->field('section_name')->find();
            $this->jsonReturn(['error'=>0,'error_detail'=>'没有相关的'.$keshi["section_name"].'的病种，请前去添加！']);
        }
    }

    /**
     * 将题目添加进题卷
     * @access private
     */
    private function questionnaire_add_question($questionnaire_id,$id) {
        $tmparr = M('questionnaire_list')->where(['id'=>$questionnaire_id])->field('content')->find();
        if($tmparr['content']) {
            $tmp = json_decode($tmparr['content'],true);
            if(is_array($id)) {
                foreach($id as $v) {
                    $tmp[] = $v;
                }
            }else{
                $tmp[] =$id;
            }
            $tmparr['content'] = json_encode($tmp);
        }else{
            $tmparr['content'] =json_encode([0=>$id]);
        }
        $result = M('questionnaire_list')->where(['id'=>$questionnaire_id])->save($tmparr);
        return $result;
    }
    /****************************** [调查卷收件箱] ****************************************/
    public function question_receipt() {
        if(M('question_receipt')->find()){
            $User = M('question_receipt'); // 实例化registration对象
            $count      = $User->count();// 查询满足要求的总记录数
            $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            $Page->setConfig('last','尾页');
            $Page->setConfig('first','首页');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $show       = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $question_receipt = $User->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

            $questionnaire_data = M('questionnaire_list')->select();//加入题卷名称
            foreach($questionnaire_data as $id=>$question) {
                $questionnaire_data[$question['id']] = $question;
            }
            foreach($question_receipt as $k=>$cheet) {
                $question_receipt[$k]['questionnaire_name'] = $questionnaire_data[$cheet['questionnaire_id']]['name'];
                $question_receipt[$k]['system_grade'] = json_decode($question_receipt[$k]['system_grade']);
            }

            $this->assign('list',$question_receipt);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
            $this->assign("count",$count);
        }
        $this->display();
    }

    /**
     * 查看用户提交的内容
     */
    public function client_questionnaire() {
        $_GET['id']?:$this->jsonReturn();
        $question_receipt = M('question_receipt')->where(['id'=>$_GET['id']])->find();
        if(!$question_receipt) {
            $this->jsonReturn();
        }
        $content = json_decode($question_receipt['content'],true);
        foreach($content as $k=>$v) { //如果该选择项被选中就在当前加入['choose'=>1]
            $question = M('question_bank')->where(['id'=>$k])->find();
            if($v['type'] == 'choose') {
                unset($v['type']);
                $question['content'] =  json_decode($question['content'],true);
                foreach($v as $choose_key) {
                    $question['content'][$choose_key]['choose'] = 1;
                }
            }
            if($v['type'] == 'write') {
                $question['content'] = $v[0];
            }

            $data[] = $question;
        }
        M('question_receipt')->save(['id'=>$_GET['id'],'is_read'=>1]); //读取状态改为已读
        $questionnaire = M('questionnaire_list')->where(['id'=>$question_receipt['questionnaire_id']])->find();
        $this->assign('questionnaire',$questionnaire);
        $this->assign('questins',$data);
        $this->display();
    }

    /**
     * 邮件回复模板
     *
     */
    public function send_email() {
        $_GET['id']?:$this->jsonReturn();
        $doctors = M('doctor')->select();
        $this->assign('doctors',$doctors);
        $this->display();
    }

    /**
     * ajax发送邮件
     *
     */
    public function ajax_send_email() {
        $id            = $_POST['question_receipt_id']?$_POST['question_receipt_id']:$this->jsonReturn();
        $email_content = $_POST['email_content']?$_POST['email_content']:$this->jsonReturn();
        $email_title   = $_POST['title']?$_POST['title']:$this->jsonReturn();
        $doctor_id     = $_POST['doctor_id']?$_POST['doctor_id']:$this->jsonReturn();
        $result = M('question_receipt')->where(['id'=>$id])->find();
        $status = send_mail($result['email'],$email_title,$email_content);
        if($status) {
            $data['id']             = $id;
            $data['is_sentreport']  = 1;
            $data['manpower_grade'] = json_encode(['email_title'=>$email_title,'email_content'=>$email_content]);
            $data['doctor_id']      = $doctor_id;
            M('question_receipt')->save($data);
            $this->jsonReturn(['error'=>1],1);
        }else{
            $this->jsonReturn();
        }
    }

    /**
     *邮件回复内容简
     */
    public function email_content() {
        $id = $_GET['id']?$_GET['id']:$this->jsonReturn();
        $result = M('question_receipt')->where(['id'=>$id])->find();
        if(!$result) {
            $this->jsonReturn(['error'=>0,'error_det'=>__LINE__]);
        }else{
            $result['manpower_grade'] = json_decode($result['manpower_grade'],true);
            $doctor_name = M('doctor')->where(['id'=>$result['doctor_id']])->field('doctor_name')->find();
            $this->assign('doctor_name',$doctor_name['doctor_name']);
        }
        $this->assign('manpower_grade',$result['manpower_grade']);
        $this->display();
    }
    /**
     * 删除题卷收件箱
     *
     */
    public function question_receipt_delet() {
        $result = $_POST['id']?$this->delData($_POST['id'],'question_receipt'):$this->jsonReturn();
        if($result) {
            $this->jsonReturn(['error'=>1]);
        }else{
            $this->jsonReturn(['errors'=>0,'errors_det'=>'删除失败！']);
        }
    }
    /**
     * 查找
     *
     */
    public function seach() {
        if(is_numeric($_GET['is_read'])) {
            $where['is_read'] = $_GET['is_read'];
        }
        !empty($_GET['startTime'])?$where['time'] = array(array('EGT',strtotime($_GET['startTime']))):'';
        if(!empty($where["time"])) {
            !empty($_GET['endTime'])?$where['time'][1] = array('ELT',strtotime($_GET['endTime'])):'';//大于等于
        }else{
            !empty($_GET['endTime'])?$where['time'] = array(array('ELT',strtotime($_GET['endTime']))):'';//小于等于
        }
        !empty($_GET['client'])?$where['client'] = array('like','%'.$_GET['client'].'%'):'';
        if(M('question_receipt')->find()){
            $User = M('question_receipt'); // 实例化registration对象
            $count      = $User->where($where)->count();// 查询满足要求的总记录数
            $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
            $Page->setConfig('last','尾页');
            $Page->setConfig('first','首页');
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $show       = $Page->show();// 分页显示输出
            // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
            $question_receipt = $User->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();

            $questionnaire_data = M('questionnaire_list')->select();//加入题卷名称
            foreach($questionnaire_data as $id=>$question) {
                $questionnaire_data[$question['id']] = $question;
            }
            foreach($question_receipt as $k=>$cheet) {
                $question_receipt[$k]['questionnaire_name'] = $questionnaire_data[$cheet['questionnaire_id']]['name'];
                $question_receipt[$k]['system_grade'] = json_decode($question_receipt[$k]['system_grade']);
            }
            $this->assign('list',$question_receipt);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出
            $this->assign("count",$count);
        }
        $this->display('question_receipt');
    }

}