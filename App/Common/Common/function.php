<?php
function send_mail($tomail,$subject,$body){
    require('ThinkPHP/Library/Vendor/PHPMailer/class.phpmailer.php');
    if(!$result = M('system_email')->find()){
        return 0;
    }
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPDebug = 3;						//显示调试信息
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "ssl";				//启用ssl加密
    $mail->Host = $result['host'];				//邮箱服务器名称
    $mail->Port = $result['port'];							//邮箱服务端口
    $mail->Username = $result['user_name'];			//发件人邮箱地址
    $mail->Password = $result['password'];					//发件人邮箱密码
    $mail->CharSet = "UTF-8";
    $mail->SetFrom($result['user_name'],$result['name']);		//发件人信息 邮箱地址，姓名
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail, "");
    if (!$mail->Send())
    {
        $stat = 0;
    }
    else
    {
        $stat = 1;
    }
    return $stat;
}
/*
QQ邮箱 POP3 和 SMTP 服务器地址设置如下：
邮箱 	POP3服务器（端口110） 	SMTP服务器（端口25）
qq.com 	pop.qq.com 	smtp.qq.com
SMTP服务器需要身份验证。

如果是设置POP3和SMTP的SSL加密方式，则端口如下：
POP3服务器（端口995）
SMTP服务器（端口465或587）。
*/
    //邮箱找回密码路径配置

function isMobile()
{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
} 
