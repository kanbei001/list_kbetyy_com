<?php
namespace Home\Controller;
use Think\Controller;
class WechatController extends Controller {

    public function index(){
        //header("Content-type:text/html;charset=utf-8");
        if(!empty($_GET['timestamp']) && !empty($_GET['nonce']) && !empty($_GET['signature']) && !empty($_GET['echostr'])) {
            $this->goToWechat();//服务器接入，默认口令"史上第一帅"
        }
        //ob_clean();

       if(!empty($GLOBALS["HTTP_RAW_POST_DATA"])){
            $arr = $this->xmlToArr();//数组化数据
            switch($arr['msgtype']['value']){
                case "text"        : $this->recieveText($arr);//接收文本
                                     break;
                case "image"       : $this->recieveImage($arr);//接收图片
                                    break;
                case "voice"       : $this->recieveVoice($arr);//接收语音
                                    break;
                case "video"       : $this->recieveVideo($arr);//接收视频
                                    break;
                case "shortvideo"  : $this->recieveShortvideo($arr);//接收小视频
                                    break;
                case "location"    : $this->recieLocation($arr);//接收位置
                                    break;
                case "link"        : $this->recieveLink($arr);//接收链接
                                    break;
                case "event"       : $this->recieveEvent($arr);//接收事件
                                    break;
                case "event"       : $this->recieveEvent($arr);//接收事件,且有小程序事件
                                    break;
                default            : echo 0;exit;
            }
        }
    }



    //微信令牌
    public function getToken() {
        $result = M("wechat_token")->find();
        $time = time()+7200;
        if($time >= $result['receive_token_time']) {
            $getToken = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$result['appid']."&secret=".$result['appsecret']);
            if($getToken) {
                $arr = json_decode($getToken);
            }
            $data['token'] = $arr->access_token;
            $data['receive_token_time'] = time();
            $insert = M('wechat_token')->where(array('id'=>$result['id']))->save($data);
            if($insert) {
                return $arr->access_token;
            }
        }else{
            return $result['token'];
        }
    }

    //接收文本
    public function recieveText($arr) {
        $data = $this->getXmlValue(); //接收消息数组
        /*$str =  json_encode($data);
        M("web")->add(array('web_name'=>$str));
        exit('success');*/
        $textid    = M('wechat_receive')->add($data);
        if($textid) {
            $this->send_msg($data);
        }
    }

    //接收图片
    public function recieveImage($arr){
        $data = $this->getXmlValue(); //接收消息数组
        $textid    = M('wechat_receive')->add($data);
        $result = M('wechat_session')->where(array('msgid'=>$data['msgid']))->find();
        if(empty($result)) {
             M('wechat_session')->add(array('msgid'=>$data['msgid']));
        }
        if($textid) {
            $this->send_msg($data);
        }
    }

    //接收语音
    public function recieveVoice($arr){
        echo "Volice";
    }

    //接收视频
    public function recieveVideo($arr) {
        echo "Video";
    }

    //接收小视频
    public function recieveShortvideo($arr) {
        echo "Shortvideo";
    }

    //接收位置
    public function recieLocation ($arr) {
        echo "Location";
    }

    //接收链接
    public function recieveLink ($arr) {
        echo "Link";
    }

    public function recieveEvent ($arr) {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }

   /*
    *回复消息
    */
   public function send_msg($data) {
       $result  = M("wechat_keyword")->where(array('keyword'=>$data['content']))->find();
       switch($result['msgtype']) {
           case "text" :
               $this->send_text($result['return'],$data);
               break;
           case "img" :
               $this->send_img($result['return'],$data);
               break;
           default:
               $return = "未定义关键字！/::'(  /::'(  /::'(  /::'(  /::'(  /::'( /::'(/::'(/::'(/::'(/::'(/::'(/::'(/::'(/::'(/::'(";
               $xml = "<xml>
                <ToUserName>%s</ToUserName>
                <FromUserName>%s</FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType>%s</MsgType>
                <Content>%s</Content>
            </xml>";
               echo sprintf($xml,$data['fromusername'], $data['tousername'], time(), "text",$return);
               $tmpstr = $data['tousername'];
               $data['tousername'] = $data['fromusername'];
               $data['fromusername'] = $tmpstr;
               $data['content']      = $return;
               M('wechat_receive')->add($data);
       }
   }

   /*
    * 回复文本
    * return ture
    */
   public function send_text($return,$data) {
       $xml = "<xml>
                <ToUserName>%s</ToUserName>
                <FromUserName>%s</FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType>%s</MsgType>
                <Content>%s</Content>
            </xml>";
       echo sprintf($xml,$data['fromusername'], $data['tousername'], time(), "text",$return);
       $tmpstr = $data['tousername'];
       $data['tousername'] = $data['fromusername'];
       $data['fromusername'] = $tmpstr;
       $data['content']      = $return;
       M('wechat_receive')->add($data);
   }

   /*
    *回复图片
    */
    public function send_img($return,$data) {
       // print_r($data);exit;
        $obj_arr = json_decode($return);
        foreach($obj_arr->compress as $v) {
            $xml = "<xml> 
                    <ToUserName>%s</ToUserName> 
                    <FromUserName>%s</FromUserName> 
                    <CreateTime>%s</CreateTime>
                    <MsgType>%s</MsgType> 
                    <PicUrl>http://mmbiz.qpic.cn/mmbiz_jpg/KMibcrkREunB87RMibIsbeKKicrRYq6faqsBlTVCHpbGVBldwT14MlIxbPSAvXpIANLDyFQzEvf5mJn2j5Nk70ETA/0</PicUrl> 
                    <MediaId>aWHlbMgyUBjW1Q830KeALFuU8OgyIG4RrDP3RrRiumleOPpDmxraot7C9PCeo0rD</MediaId> 
                    <MsgId>%s</MsgId> 
                </xml>";
            echo sprintf($xml,$data['fromusername'], $data['tousername'], time(), "image",$data['msgid']);
            $tmpstr = $data['tousername'];
            $data['tousername'] = $data['fromusername'];
            $data['fromusername'] = $tmpstr;
            $data['content']      = $v;
            M('wechat_receive')->add($data);
            exit;
        }

    }

    //xml转arr
   public function xmlToArr(){
        $str = $GLOBALS["HTTP_RAW_POST_DATA"];
        $xmlparser = xml_parser_create();
        $xmldata = $str;
        xml_parse_into_struct($xmlparser,$xmldata,$values);
        xml_parser_free($xmlparser);
        foreach($values as $k=>$v) {
            if($v['tag'] == "XML" && $v['type'] == "close") {
                $values['xml-close'] = $v;
            }else{
                $lower = strtolower($v['tag']);
                $values[$lower] = $v;
            }
            unset($values[$k]);
        }
        return $values;
    }

    //xmll转去掉tag和levao数据
    public function  getXmlValue() {
        $arr = $this->xmlToArr();
        foreach($arr as $k=>$v) {
            if(!empty($v['value'])) {
                $data[$k] = $v['value'];
            }
        }
        if(!empty($arr['xml-close'])) {
            $data['status'] = 1;
        }

//        $result = M('wechat_receive')->where(array('createtime'=>$data['createtime']))->find();
//        if($result) {
            //exit('success');
//        }
       /* if($_SESSION['createtime'] == $data['creatime']) {
           // unset( $_SESSION['createtime']);
            exit('success');
        }
        $_SESSION['createtime'] = $data['creatime'];*/
        return $data;
    }
    /*
     *将接收的消息保存到总览表
     *return bool
     */
    public function savereceive($data,$tableid) {
        //$data = $this->getXmlValue();
        $synopsis['savetable']     = "wechat_receive";
        $synopsis['savetableid']  = $tableid;
        $synopsis['createtime']   = time();
        $synopsis['isread']       = 0;
        $synopsis['fromusername'] = $data['fromusername'];
        $synopsis['tousername']   = $data['tousername'];
        $result = M('wechat_receive')->add($synopsis);
        return $result;
    }


}

?>
