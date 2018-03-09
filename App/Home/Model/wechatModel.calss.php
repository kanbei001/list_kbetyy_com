<?php
namespace Home\Model;
use Think\Model;
class wechatModel extends Model {
    //接入平台
    protected $tableName = 'wechat_token';
    public function goToWechat() {
        echo 1;exit;
        $token = M('wechat_token')->field('user_defined_token')->find();
        $token = !empty($token['user_defined_token'])?:"shishangdiyishuai";
        $timestamp = $_GET['timestamp'];
        $nonce     = $_GET['nonce'];
        $signature = $_GET['signature'];
        $array     = array($timestamp,$nonce,$token);
        sort($array);
        $tmpstr = implode('',$array);
        $tmpstr = sha1($tmpstr);
        if($tmpstr == $signature) {
            echo $_GET['echostr'];
            exit;
        }
    }

}
?>