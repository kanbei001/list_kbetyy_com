<?php
namespace Home\Controller;
use Think\Controller;
class APIController extends Controller {
   
    public function index(){
        $k = (!empty($_POST['k'])) ? $_POST['k'] : 0; 
        switch ($k)
            {
            case 0:
                $this->jsonReturn(array('error'=>"参数错误"));
                break;
            case 1:
                $this->add_ajax(); //挂号
                break;
            case 2:
                $this->keshiList(); //科室列表
                break;
            case 3:
                $this->numbers();//预约名单
                break;
            default:
                $this->jsonReturn(array("error"=>"非法操作"));
        }
        
        
//        $table = M("kefuinfo"); // 实例化User对象
//        //$table->data($data)->add();
//        echo header("Access-Control-Allow-Origin:*");
//        /*定义加密请求
//         * 密钥：ABCDEFGHIJKLMNOPQRSQUVWXYZ
//         * sign=大写MD5(时间戳+密钥);
//         * 请求科室:url="url?k='?'&sign=大写MD5值;
//         * 提交挂号：url="url?";
//         */
//        $API_status = true;//是否启用接口加密
//        $private    = "ABCDEFGHIJKLMNOPQRSQUVWXYZ";
//        if($API_status) {
//            if(IS_POST) {
//                $arr = array('请求方式不对'); 
//                ReturnJson($arr,0);
//            }   
//            if($_GET['salt']=="" || $_GET['token']=="" ) { 
//                $arr = array('请求参数不足');
//                ReturnJson($arr,1);
//            }   
//            $str =  $_GET["salt"].$private;
//            $lower = MD5($str);
//            $token = strtoupper($lower);
//            if($_GET['token'] != $token) {
//                $arr = array('令牌错误');
//                $this->ReturnJson($arr,2);
//            }
//        }
//        if($_GET['k'] != '') {
//            $this->medicine();
//        }
       // }
    
        
    }

    public function medicine() {
        $table = M("section");
        dump($table->select());
    }
    
    //预约科室病种加载
    public function kssection_ajax(){
        if(IS_POST){
            $keshi=I('post.keshi_val');		
            $bzinfo=M('Bingzhong')->where(array('keshi_id'=>$keshi))->select();	
            $bzlist =array('list'=>$bzinfo);
            echo json_encode($bzlist);	
        }else{
            echo "<script type='text/javascript'>alert('非法操作！');history.go(-1);</script>";		
        }
    }
    
    //验证手机号是否重复
    public function yzphone_ajax(){
        if(IS_POST){
            $phone=I('post.phone');	
            $where=array('phone'=>$phone);
            $statesresult=M('Kefuinfo')->where($where)->select();
            if($statesresult){
                $states=1;
            }else{
                $states=0;	
            }
            $results =array('states'=>$states);	
               echo json_encode($results);	
            }else{
                echo "<script type='text/javascript'>alert('非法操作！');history.go(-1);</script>";		
            }
    }
    
    //添加挂号
    public function add_ajax(){
            echo header("Access-Control-Allow-Origin:*");
            $post_data["username"]          = $_POST["username"];                 //姓名
            $post_data["phone"]             = $_POST["phone"];                    //手机
            $post_data["keshi"]             = $_POST["keshi"];                    //科室
            $post_data["bingzheng"]         = $_POST["bingzheng"];                //病症详情
            $post_data["time"]              = $_POST["time"];                     //预约时间
            $post_data["source_web"]        = $_POST["source_web"];               //来源网站
            $post_data["source_url"]        = $_POST["source_url"];               //来源网址 
            $post_data["sex"] = (!empty($_POST['sex'])) ? $_POST['sex'] : "未知";  //性别
            $post_data['laiyuan'] = "否";                                         //是否来院
            $post_data['huifang'] = "否";                                         //是否回访
            $post_data["laiyuan_time"] = "";                                  //来院时间         
            $post_data["in_time"] = time();                                      //录入时间
            $post_data["type_in"] = "数据接口";                                    //录入者
            $lastidinfo= M('Kefuinfo')->order('id desc')->limit(1)->find();

            $lastid=intval($lastidinfo['id']);

            $con= intval(M('Kefuinfo')->count());

            $nums=519948;

            if($lastid<=$nums)
            {
            if($con==0)
            {
            $b="";
            $num=1;
            $tag=floor(($num-1)/9999);
            $part1=65+$tag;
            $part2=$num-9999*$tag;
            $a=strlen($part2);
            for($i=0;$i<(4-$a);$i++)
            {
            $b.=0;
            }
            $str=chr($part1).$b.$part2;

            }else{
            $b="";
            $num=$lastid+1;
            $tag=floor(($num-1)/9999);
            $part1=69+$tag;
            $part2=$num-9999*$tag;
            $a=strlen($part2);
            for($i=0;$i<(4-$a);$i++)
            {
            $b.=0;
            }
            $str=chr($part1).$b.$part2;

            }
            }elseif($nums<$lastid){
            $num=$lastid-$nums+1;
            $tag=floor(($num-1)/9999);
            $part1=97+$tag;
            $part2=$num-9999*$tag;
            $a=strlen($part2);
            for($i=0;$i<(4-$a);$i++)
            {
            $b.=0;
            }
            $str=chr($part1).$b.$part2;

            }
            $post_data['yuyue'] =$str;

            if(M('Kefuinfo')->add($post_data)){
               // $this->message($post_data["username"],$post_data['yuyue'],$post_data["time"],$post_data["phone"]);//发送短信
                $sj['actions']="添加预约信息-预约号：[".$str."]成功！"; 
                $sj['ip_id']=$_POST["ip"];
                $sj['times']=time();
                $sj['action_name']=$_POST["source_web"];
                M('Operation')->add($sj);	
                $state=1;
                $status = 1;
                if(session('m_id')==5){
                    $dumpurl='calls';
                }else{
                    $dumpurl='dengji';
                }

            }else{
                $state=0;
                $status = 0;
            };
        $result = array('state' => $state,'dumpurl'=>$dumpurl,'status'=>$status);  
          echo json_encode($result);
    }
    //科室列表
    public function keshiList() {
        echo header("Access-Control-Allow-Origin:*");
        $data = M("section")->order("sort asc")->select();
        $this->jsonReturn($data);
    }
    //请求30最新预约人
    public function numbers(){
        $data = M("Kefuinfo")->field('username,phone,yuyue,keshi')->order("id desc")->limit(30)->select();
        foreach($data as $k=>$v) {
            if(preg_match("/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|17[0-9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/",$data[$k]["phone"])) {
                $data[$k]["phone"]  = substr_replace($data[$k]["phone"],'****',3,4);
            }
        }
        $this->jsonReturn($data,1);
    }
    
    //挂号短信
    public function message($username,$yuyue,$time,$mobile) {
        @session_start();
        header("Content-type:text/html; charset=UTF-8");
        function Post($curlPost,$url){
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_NOBODY, true);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
                        $return_str = curl_exec($curl);
                        curl_close($curl);
                        return $return_str;
        }

        function xml_to_array($xml){
                $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
                if(preg_match_all($reg, $xml, $matches)){
                        $count = count($matches[0]);
                        for($i = 0; $i < $count; $i++){
                        $subxml= $matches[2][$i];
                        $key = $matches[1][$i];
                                if(preg_match( $reg, $subxml )){
                                        $arr[$key] = xml_to_array( $subxml );
                                }else{
                                        $arr[$key] = $subxml;
                                }
                        }
                }
                return $arr;
        }

        function random($length = 6 , $numeric = 0) {
                PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
                if($numeric) {
                        $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
                } else {
                        $hash = '';
                        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
                        $max = strlen($chars) - 1;
                        for($i = 0; $i < $length; $i++) {
                                $hash .= $chars[mt_rand(0, $max)];
                        }
                }
                return $hash;
        }

        $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";


       // $mobile_code = random(4,1);
        if(empty($mobile)){
                exit('手机号码不能为空');
        }

//        if(empty($_SESSION['send_code']) or $send_code!=$_SESSION['send_code']){
//                exit('请求超时，请刷新页面后重试');
//        }

        $post_data = "account=C59353208&password=4afebf2548d401b95c55118269be2c88&mobile=".$mobile."&content=".rawurlencode("尊敬的".$username."您挂号是：".$yuyue."。请在".$time."到本院就诊。地址：深圳市龙华区工业西路97号;电话:0755-25111120。如非本人操作，可不用理会！");
        $gets =  xml_to_array(Post($post_data, $target));
        if($gets['SubmitResult']['code']==2){
                $_SESSION['mobile'] = $mobile;
                $_SESSION['mobile_code'] = $mobile_code;
        }
        //echo $gets['SubmitResult']['msg'];
    }
    function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
    }
    //json返回 
    public function jsonReturn($arr,$status=0) {
        header('Content-type: application/json');
        $arr['status'] = $status;
        $data = json_encode($arr);
        exit($data);
        
    }
}
?>