<?php
namespace Home\Controller;
use Think\Controller;
class CheckapiController extends Controller {
    /**
     * 数据接口
     *
     */
    public function api_questionnaire(){
        //print_r($_POST);exit;
        $form = $_POST['form']?$_POST['form']:$this->jsonReturn(['error'=>0,'error_det'=>'请输入题卷名']);
        $questionnaire = M("questionnaire_list")->where(['name'=>$form])->find();
        if(!$questionnaire) {
            $this->jsonReturn(['error'=>0,'error_det'=>"没有题卷名为:{$form}。请核对！"]);
        }
        if($questionnaire['is_quest'] != 1) {
            $this->jsonReturn(['error'=>0,'error_det'=>'禁止访问'.$form.'，请与管理员联系']);
        }
        if(!$questionnaire['content']) {
            $this->jsonReturn(['error'=>0,'error_det'=>$form.'还没有题目']);
        }
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
            $question_id = $v['id'];
            $tmparr = json_decode($v['content'],true);
            $bingzhong = M('bingzhong')->select();
            foreach($bingzhong as $key=>$value) {
                $bingzhong[$value['id']] = $value;
            }
            unset($choose);
            if($v['type'] == 'choose') { //给选择题命名和赋值。选择题的input name 命名为 问卷id+问题id+选择题的数组key值
                foreach($tmparr as $choos_arr_key=>$value2) {
                    foreach($value2 as $ink=>$inv) {
                        $choose[] = [$questionnaire['id'].'|'.$question_id.'|'.$choos_arr_key,$inv];
                    }
                }
            }
            if($v['type'] == 'write') { //给自答题命名。自答题的input name 命名为 问卷id+问题id+题目的类型
                $choose[] = [$questionnaire['id'].'|'.$question_id.'|'.$v['type']];
            }
            $result[$k]['content'] = $choose;
        }
        $questionnaire['quest']++;
        M('questionnaire_list')->save($questionnaire);

        $this->jsonReturn(['questions'=>$result,'questionnaire'=>$questionnaire,'error'=>1],1);
    }


    /**
     * 预览
     *
     */
    public function index(){
        $form = $_GET['form']?$_GET['form']:$this->jsonReturn();
        //$form = '开发测试卷1';
        $questionnaire = M("questionnaire_list")->where(['name'=>$form])->find();
        if(!$questionnaire) {
            $this->jsonReturn(['error'=>0,'error_det'=>'没有'.$form]);
        }
        if($questionnaire['is_quest'] != 1) {
            $this->jsonReturn(['error'=>0,'error_det'=>'禁止访问'.$form.'，请与管理员联系']);
        }
        if(!$questionnaire['content']) {
            $this->jsonReturn(['error'=>0,'error_det'=>$form.'还没有题目']);
        }
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
                $question_id = $v['id'];
                    $tmparr = json_decode($v['content'],true);
                    $bingzhong = M('bingzhong')->select();
                    foreach($bingzhong as $key=>$value) {
                        $bingzhong[$value['id']] = $value;
                    }
                    unset($choose);
                    if($v['type'] == 'choose') { //给选择题命名和赋值。选择题的input name 命名为 问卷id+问题id+选择题的数组key值
                        foreach($tmparr as $choos_arr_key=>$value2) {
                            foreach($value2 as $ink=>$inv) {
                                $choose[] = [$questionnaire['id'].'|'.$question_id.'|'.$choos_arr_key,$inv];
                            }
                        }
                    }
                    if($v['type'] == 'write') { //给自答题命名。自答题的input name 命名为 问卷id+问题id+题目的类型
                        $choose[] = [$questionnaire['id'].'|'.$question_id.'|'.$v['type']];
                    }
                    $result[$k]['content'] = $choose;
            }
        $questionnaire['quest']++;
        M('questionnaire_list')->save($questionnaire);

        $this->assign('questions',$result);
        $this->assign('questionnaire',$questionnaire);
        $this->display();
    }

    /**
     * 接收
     * @return json
     * @!每一道题只能在一份答卷出现一次，如果多次这里会merge掉
     * @!答题的内容会以键名是题id的数组json格式保存，如果是选择题就保存被选择项在题库的选择键名，格式为一维数组排列。
     */
    public function receive() {
       // print_r($_POST);exit;
        $_POST?$_POST:$this->jsonReturn();
        $data['client']             = $_POST['client']?$_POST['client']:null;
        $data['email']             = $_POST['email']?$_POST['email']:null;
        $data['phone']             = $_POST['phone']?$_POST['phone']:null;
        list($json_content,$question_choose,$question_write)=$this->formatting_content();
        $data['content']           = $json_content;
        $data['questionnaire_id']  = $this->get_questionnaire_id();
        $data['is_read']           = 0;
        $data['ip']                = $_SERVER['REMOTE_ADDR'];
        $data['is_sentreport']     = 0;
        $data['time']              = time();
        $data['download']          = 0;

        list($bingzhong_id,$bingzhong_id_count) = $this->bingzhong_id();
        $report['report_status'] = count(json_decode($data['content'],true)) > 0?1:0;
        if(!$report['report_status']) {
            $report['error_detail'] = '您还没有填写问卷，请填写！';
            $this->jsonReturn($report);
        }
        $report['report_all_questions'] = count(json_decode($data['content'],true));
        $report['report_choose_questions'] = count($question_choose);
        $report['report_write_questions']  = count($question_write);
        $report['report_choose_sub'] = $bingzhong_id_count;
        $mysql_bingzhong = M('bingzhong')->select();//做出各种病种的比率
        foreach($mysql_bingzhong as $k=>$v) {
            $mysql_bingzhong[$v['id']] = $v;
        }
        foreach($bingzhong_id as $num=>$bingzhong_id_arr) {
            $num+=1;
            foreach($bingzhong_id_arr as $id) {
                $persents = round($num/$bingzhong_id_count,2)*100;
                $persents .="%";
                $report['report_choose_del'][] = $mysql_bingzhong[$id]['bingzhong_name'].$persents;
            }
        }
        $keshi = M('section')->select();//做出可能性最高人的病种建议
        foreach($keshi as $k=>$v){
            $keshi[$v['id']] = $v;
        }
       // print_r($bingzhong_id);exit;
        if(count($bingzhong_id) == 1) {
            $report['advice'][] = '参数过于均衡无法给出近似的建议，请多选择符合情况的选项！';
        }else{
            foreach(end($bingzhong_id) as $num=>$id) {
                $report['advice'][] = $keshi[$mysql_bingzhong[$id]['keshi_id']]['section_name']. '-'.$mysql_bingzhong[$id]['bingzhong_name'];
            }
        }
        $data['system_grade']      = json_encode([$report['report_choose_del'],$report['advice']]);
        if($report['report_write_questions']) {//如果用户有填写自答题就检查用户的联系方式，如果不足就提醒增加。
            $report['parameter_detail'] = $data['client']?'':'姓名';
            $report['parameter_detail'] = $data['phone']?'':'手机号码';
            $report['parameter_detail'] .= $data['email']?'':'邮箱';
                if($report['parameter_detail']) {
                    $report['parameter_detail'] = '请输入'.$report['parameter_detail'];
                    $report['parameter_status'] = 1;
                }else{
                    $report['parameter_status'] = 0;
                }
        }
       // print_r($data);exit;
        $id = M('question_receipt')->add($data);
        if($id) {
            $report['url'] = 'http://'.$_SERVER['HTTP_HOST']."/Home/checkapi/download_pdf/id/".$id;
            $this->jsonReturn($report);
        }
    }

    /**
     * 提交内容数据格式化
     * @access private
     * @return jason
     */
    private function formatting_content() {
        unset($_POST['client'],$_POST['phone'],$_POST['email']);
        foreach($_POST as $k=>$v) {
            $tmparr_explode           = explode('|',$k);
            if(is_numeric($tmparr_explode[2])) { //选择题选中项的题库key值
                if(!$tmparr_1[$tmparr_explode[1]]['type']) {
                    $tmparr_1[$tmparr_explode[1]]['type'] = 'choose';
                    $choose_questions[] = $tmparr_explode[1];
               }
                $tmparr_1[$tmparr_explode[1]][] = $tmparr_explode[2];
            }
            if($tmparr_explode[2] == 'write' && $v!='') { //自答题
                $tmparr_1[$tmparr_explode[1]]['type'] = 'write';
                $tmparr_1[$tmparr_explode[1]][] = $v;
                $write_questions[] = $tmparr_explode[1];
            }
        }
        $write_questions = $write_questions?:[];
        return [json_encode($tmparr_1),$choose_questions,$write_questions];
    }

    /**
     * 返回题卷id
     * @access private
     * @return num
     */
    private function get_questionnaire_id() {
        unset($_POST['client'],$_POST['phone'],$_POST['email']);
        foreach($_POST as $k=>$v) {
            $tmparr_explode           = explode('|',$k);
            if($tmparr_explode[0]) {
                return $tmparr_explode[0];
            }
        }
    }

    /**
     * 返回是否有自答题
     * @access private
     * @return boolean
     */
    private function is_write(){
        unset($_POST['client'],$_POST['phone'],$_POST['email']);
        foreach($_POST as $k=>$v) {
            $tmparr_explode           = explode('|',$k);
            if($tmparr_explode[2] == 'write') {
                if($v) {
                    return true;
                }
            }
        }
    }

    /**
     * 返回所有选择题病种id
     * @access private
     * @return array
     */
    private function bingzhong_id() {
        unset($_POST['client'],$_POST['phone'],$_POST['email']);
        foreach($_POST as $k=>$v) {
            $tmparr_explode           = explode('|',$k);
            if(is_numeric($tmparr_explode[2])) { //选择题选中项的题库key值
                $queston = M('question_bank')->where(array('id'=>$tmparr_explode[1]))->field('content')->find();
                $content_arr = json_decode($queston['content'],true);
                foreach($content_arr[$tmparr_explode[2]] as $bingzhong_id=>$bingzhong) {
                    $result[] = $bingzhong_id;
                }
            }
        }
         return [$this->recursion_arr($result),count($result)];//返回根据次数排序数组

    }

    /**
     * 递归一维重复数组值
     * @以键名为数量值
     * @acces private
     * @return array
     */
    private function recursion_arr($arr,$data=[]) {
        $unique_arr = array_unique ( $arr );
        $repeat_arr = array_diff_assoc ( $arr, $unique_arr );
        foreach(array_unique($repeat_arr) as $repeat_arr_val) {
            $repeat_arr_key   = array_search($repeat_arr_val,$unique_arr);
            unset($unique_arr[$repeat_arr_key]);
        }
        $start_count = count($repeat_arr);
        $tmp = array_unique($repeat_arr);
        $executed_count = count($tmp);
        $data[] = $unique_arr;
        if($start_count > $executed_count) { //如果有相同的值,继续回调
            $result = $this->recursion_arr($repeat_arr,$data);
            return $result; //一层一层传上来
        }else{
            if($repeat_arr) {
                $data[] = $repeat_arr;
            }
            return $data; //直到底层开始返回结果
        }
    }

    /**
     * 返回是几维数组
     * @acess private
     * @return number
     */
    private function getmaxdim($vDim)
    {
        if(!is_array($vDim)) return 0;
        else
        {
            $max1 = 0;
            foreach($vDim as $item1)
            {
                $t1 = $this->getmaxdim($item1);
                if( $t1 > $max1) $max1 = $t1;
            }
            return $max1 + 1;
        }
    }
    /**
     * 渲染pdf模板
     * @access private
     */
    public function load_pdf() {
       $id = $_GET['id'];
       $id?:$this->jsonReturn();
       $result = M('question_receipt')->where(['id'=>$id])->field('system_grade,email')->find();
       $system_grade = json_decode($result['system_grade'],true);
       if($result['email']) {
            $this->assign('email_address',$result['email']);
       }
       $this->assign('system_grade',$system_grade);
       $this->display('load_pdf');
    }


    /**
     * 下载pdf报告
     *
     */
    public function download_pdf() {
        //引入类库
        Vendor('mpdf.mpdf');
        //设置中文编码
        $mpdf=new \mPDF('zh-cn','A4', 0, '宋体', 0, 0);
        //html内容
        $url =  "http://".$_SERVER['HTTP_HOST'].__CONTROLLER__."/load_pdf/id/".$_GET['id'];
        $html=file_get_contents($url);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        exit;
    }
    //json格式
    public function jsonReturn($arr=array('error'=>0),$status=0) {
        header('Content-type: application/json');
        $arr['status'] = $status;
        $data = json_encode($arr);
        exit($data);
    }
}
?>