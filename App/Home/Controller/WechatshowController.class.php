<?php
namespace Home\Controller;
use Think\Controller;
class WechatshowController extends SystemController
{

    //微信设置  
    public function set()
    {
        $result = M("wechat_token")->field('user_defined_token,appsecret,appid')->find();
        if ($result['user-defind-token'] == null) {
            $result['user_defined_token'] = "shishuangdiyishuai";//史上第一帅
        }
        $this->assign('token', $result);
        $this->display('system-base');
    }

    //保存微信设置
    public function ajax_set_save()
    {
        if (IS_AJAX) {
            $data = I();
            $tmparr = M("wechat_token")->field('id')->find();
            $data['id'] = $tmparr['id'];
            $result = M("wechat_token")->save($data);
            if ($result) {
                $this->jsonReturn(array('error' => 1), 1);
            } else {
                $this->jsonReturn();
            }
        }
    }

    //关键词列表
    public function keyword()
    {
        $User = M('wechat_keyword');//实例化registration对象
        $count = $User->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->order('keyword_sort asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) { //数据类型重格式化输出
            switch ($v['msgtype']) {
                case 'img' :
                    $obj = json_decode($v['return']);
                    foreach ($obj->compress as $objv) {
                        //dump();exit;
                        $tmparr[] = $objv;
                    }
                    $list[$k]['return'] = $tmparr;
                    unset($tmparr);
            }
        }

        //dump( $list);exit;
        $this->assign('list', $list);// 赋值数据集
        //dump($list);exit;
        $this->assign('page', $show);// 赋值分页输出
        $this->assign("count", $count);
        $this->display();
    }

    /*
     * 编辑关键词
     *
     */
    public function keyword_edit()
    {
        $id = I('id');
        $data = M("wechat_keyword")->where(array('id' => $id))->find();
        $this->assign('idData', $data);
        $this->display();
    }

    /*
     *ajax编辑保存
     * return json
     */
    public function editor_ajax()
    {
        if (IS_AJAX) {
            $data = I();
            $result = M('wechat_keyword')->where(array('id' => $data['id']))->save(array('keyword' => $data['keyword'], 'return' => $data['return'], 'keyword_sort' => $data['keyword_sort']));

            if ($result) {
                $this->jsonReturn(array('error' => 1), 1);
            } else {
                $this->jsonReturn();
            }
        }
    }

    /*
     * 删除
     *return json
     */
    public function keyword_delete()
    {
        if (IS_AJAX) {
            //echo 1;exit;
            $tmparr = M('wechat_keyword')->where(array('id' => $_POST['id']))->select();
            $result = M('wechat_keyword')->where(array('id'=>$_POST['id']))->delete();
            switch ($tmparr[0]['msgtype']) {
                case 'text' : //删文本
                    if ($result) {
                        $this->jsonReturn(array('error' => 1, 1));
                    } else {
                        $this->jsonReturn();
                    }
                    break;
                case 'img' : //删图片
                    $result = $this->del_file($tmparr);
                    if ($result) {
                        $this->jsonReturn(array('error' => 1, 1));
                    } else {
                        $this->jsonReturn();
                    }
                    break;


            }

        }
    }

    /*
     * 批量删除
     *return json
     */
    public function batches()
    {
        $idData = I();
        foreach($idData as $v) {
            $arr_int[] = (int)$v;
        }
        $arr_int[] = "OR";
        $where['id'] =$arr_int;
        $data = M('wechat_keyword')->where($where)->select();
        $num = 0;
        $str = '';
        foreach ($data as $v) {
           $result = M('wechat_keyword')->where(array('id' => $v['id']))->delete();
            //$result = true;
            if (!$result) {
                $str .= $v['id'] . "/";
                $num++;
            }
            switch($v['msgtype']) {
                case "img" :
                    $arr_del[] = $v;
                    break;
            }
        }
        //dump($arr_del);exit;
        $del = $arr_del?$this->del_file($arr_del):0;
        if ($num == 0 && !$del) {
            $this->jsonReturn(array('error' => 1), 1);
        } else {
            if($del && !$num) {
                $this->jsonReturn(array('error' => "id" . $str . "数据库和文件删除失败！"), 0);
            }
            if($del) {
                $this->jsonReturn(array('error' => "id" . $str . "文件删除失败！"), 0);
            }
            if(!$num) {
                $this->jsonReturn(array('error' => "id" . $str . "数据库删除失败！"), 0);
            }
        }
    }

    /*
     * 添加关键词
     *
     * return json
     */
    public function add()
    {
        if (IS_AJAX and isset($_POST)) {
            switch ($_POST['msgtype']) { //验证数据

                case "text":
                    if (empty($_POST['keyword'] . $_POST['return'])) {
                        switch (true) {
                            case empty($_POST['keyword'] . $_POST['return']):
                                $str = "请输入关键词和回复内容";
                                break;
                            case empty($_POST['keyword']):
                                $str = "请输入关键词";
                                break;
                            case empty($_POST['return']):
                                $str = "请输入回复内容";
                                break;
                        }
                        $this->jsonReturn(array('error' => 0, 'error_detail' => $str));
                    }
                    break;

                case "img":
                    if (empty($_POST['keyword']) or empty($_SESSION['imgname'])) {
                        switch (true) {
                            case empty($_POST['keyword']) and !$_SESSION['imgname']:
                                $str = "请输入关键词上传图片";
                                break;
                            case empty($_POST['keyword']):
                                $str = "请输入关键词";
                                break;
                            case !$_SESSION['imgname']:
                                $str = "请上传图片";
                                break;
                        }
                        $this->jsonReturn(array('error' => 0, 'error_detail' => $str));
                    }
            }
            $this->is_have_keyword($_POST['keyword']);
            $data['keyword'] = $_POST['keyword'];
            $data['msgtype'] = $_POST['msgtype'];
            $data['keyword_sort'] = $_POST['keyword_sort'];
            switch ($_POST['msgtype']) { //写入
                case "text":
                    $data['return'] = $_POST['return'];
                    break;
                case "img";
                    $data['return'] = json_encode($_SESSION['imgname']);
                    unset($_SESSION['imgname']);
                    break;
            }
            $result = M('wechat_keyword')->add($data);
            if ($result) {
                $this->jsonReturn(array('error' => 1), 1);
            } else {
                $this->jsonReturn();
            }
            exit;
        }
        $this->display();
    }

    /*
     * 查看关键词是否存在
     *return json
     */
    public function is_have_keyword($keword)
    {
        $is_have = M('wechat_keyword')->where(array('keyword' => $keword))->find();
        if ($is_have) {
            $this->jsonReturn(array('error' => 0, 'error_detail' => '该关键词已存在'), 0);
        }
    }

    /*
     * 查找
     *
     */
    public function seach()
    {
        $keyword = empty($_GET['keyword']) ? exit : $_GET['keyword'];
        $User = M('wechat_keyword');//实例化registration对象
        $count = $User->where(array('keyword' => array('like', "%" . $keyword . "%")))->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where(array('keyword' => array('like', "%" . $keyword . "%")))->order('keyword_sort asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);// 赋值数据集
        //dump($list);exit;
        $this->assign('page', $show);// 赋值分页输出
        $this->assign("count", $count);
        $this->display('keyword');
    }

    /*
     *接收图片
     */
    public function upload_img()
    {
        if (!file_exists("Upload")) {
            @mkdir("./Uploads/");
        }
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = ''; // 设置附件上传（子）目录
        // 上传文件
        $info = $upload->upload();
        $src = "http://" . $_SERVER['HTTP_HOST'] . "/Uploads/" . $info['file']['savepath'] . $info['file']['savename'];
        if (!$info) {// 上传错误提示错误信息
            exit('上传失败');
        } else {// 上传成功
            //生成略缩图
            import("Org.Util.compress");
            $image = new \Image($src);
            $image->percent = 0.2;
            $image->openImage();
            $image->thumpImage();
            $image->showImage();
            $tmparr = explode('.', $info['file']['savename']);
            $compres_name = "./Uploads/" . $info['file']['savepath'] . 'compress_' . $tmparr[0];
            $image->saveImage($compres_name);
            $_SESSION['imgname']['master'][] = "/Uploads/" . $info['file']['savepath'] . $info['file']['savename'];
            $_SESSION['imgname']['compress'][] = "/Uploads/" . $info['file']['savepath'] . 'compress_' .$tmparr[0].".jpeg";
        }
    }

    /*
     *删除文件
     * success true true else return false
     */
    public function del_file($data){
        $chknum = 0;
        foreach($data as $v) {
            switch($v['msgtype']) {
                case 'img': //删图
                    $obj_arr = json_decode($v['return']);
                    foreach($obj_arr->master as $inmaster) {
                        $inmaster = ".".$inmaster;
                        unlink($inmaster)?:$chknum++;
                    }
                    foreach ($obj_arr->compress as $incompress) {
                        $incompress =".".$incompress;
                        unlink($incompress)?:$chknum++;
                    }
            }
        }
        //dump($chknum);exit;
        return $chknum;
    }


}