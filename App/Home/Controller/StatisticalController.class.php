<?php
namespace Home\Controller;
use Think\Controller;

/*
 *注意,没有时区，所以时间有 
 *
 *  
 */

class StatisticalController extends SystemController {
    //年份列表
    public function LineChart(){
        if($year = $_GET['year']) {
            $this->seach($year);
        }else{
            $this->seach();
        }
       $this->display("LineChart");
    }
    //月份列表
    public function LineChartOfMonth(){
        if(!empty($_GET['date'])) {
            $date = $_GET['date'];
            $monthAllData  = $this->monthAllDate($date);//月份总额数据
            $monthZeroData = $this->monthAllDate($date,0); //月份未到数据 
            $monthFirstData = $this->monthAllDate($date,1); //月份来院数据 
            $monthPercentageData = $this->percentage($monthFirstData,$monthAllData);
            $this->assign("monthAllData",$monthAllData);
            $this->assign("monthZeroData",$monthZeroData);
            $this->assign("monthFirstData",$monthFirstData);
            $this->assign("monthPercentageData",$monthPercentageData);
            $this->assign("currentTime",$data);
        }else{
            exit('参数不足');
        }
        $this->display();
    }
    //日期列表
    public function LineChartOfDay() {
        $timeOfStart = strtotime($_GET['date']);
        $timeOfEnd   = $timeOfStart+60*60*24;
        $where['in_time'] = array(array('EGT',$timeOfStart),array('lt',$timeOfEnd));
        $result = M("kefuinfo")->where($where)->field('in_time')->select();
        foreach($result as $k=>$v) {
            $data[] = $result[$k]['in_time'];
        }
        for($i=1;$i<=24;$i++){//计算24小时挂号量数据
            $where['in_time'] = array(array('EGT',$timeOfStart),array('lt',$timeOfStart+=60*60));//总额
            $dayAllData[$i] = M("kefuinfo")->where($where)->count();
            $dayAll +=$dayAllData[$i];
            $where['status'] = 0;//未到
            $dayZeroData[$i] = M("kefuinfo")->where($where)->count();
            $dayZeroAll +=$DayZeroData[$i];
            $where['status'] = 1;//到院
            $dayFirstData[$i] = M("kefuinfo")->where($where)->count();
            $dayFirstAll +=$dayFirstData[$i];
            unset($where['status']);
        }
        $dayAllData['all'] = $dayAll;
        $dayZeroData['all'] = $dayZeroAll;
        $dayFirstData['all'] = $dayFirstAll;
        $dayPercentageData = $this->percentage($dayFirstData,$dayAllData);
        $this->assign("dayAllData",$dayAllData);
        $this->assign("dayZeroData",$dayZeroData);
        $this->assign("dayFirstData",$dayFirstData);
        $this->assign("dayPercentageData",$dayPercentageData);
        $data = array_count_values($data);//算相同时间预约的次数
        $this->assign('regArr',$data);
        $this->display();
    }
    //ajax拿月份编号
    public function returnNumOfMonth() {
        $yearAllData  = $this->yearData($_POST['year']);//月份总额数据0
        foreach($yearAllData as $k=>$v) {
            if($v>0) {
                if($k != 'all') {
                    $data[$k] = $k;
                }
            }
        }
        $this->jsonReturn($data,1);
    }
    //ajax拿日期编号
    public function ajaxNumOfDay(){
        $date = $_POST['year']."-".$_POST['month'];
        $MonthAllData = $this->monthAllDate($date);
        foreach($MonthAllData as $k=>$v) {
            if($v>0) {
                if($k != 'all') {
                    $data[$k] = $k;
                }
            }
        }
        $this->jsonReturn($data,1);
    }
    public function MonthlyReport () {
        $this->display("product-category");
    }
    public function index() {
    }
    //导出xls
    public function ajaxExport() {
        switch(count($_GET)) {
            case 1:
                $this->XlsOfYear($_GET['year']);
                break;
            case 2:
                $this->XlsOfMonth($_GET['year'],$_GET['month']);
                break;
            case 3:
                $this->XlsOfDay($_GET['year'],$_GET['month'],$_GET['day']);
                break;
            default:echo "参数不对，没调用到方法，下载不了文件，请找程序员解决！";
        }
    }
    //导出xls
    function getExcel($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");//A--65
        $key2 = ord("@");//@--64
        foreach($headArr as $v){
            if($key>ord("Z")){
                $key2 += 1;
                $key = ord("A");
                $colum = chr($key2).chr($key);//超过26个字母时才会启用  
            }else{
                if($key2>=ord("A")){
                    $colum = chr($key2).chr($key);//超过26个字母时才会启用  
                }else{
                    $colum = chr($key);
                }
            }
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1',$v);
            $key += 1;
    }
    
    
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        //print_r($data);exit;
        foreach($data as $key => $rows){ //行写入
            $key = ord("A");
            $key2 = ord("@");
            foreach($rows as $keyName=>$value){// 列写入
                if($key>ord("Z")){
                    $key2 += 1;
                    $key = ord("A");
                    $colum = chr($key2).chr($key);//超过26个字母时才会启用  
                }else{
                    if($key2>=ord("A")){
                        $colum = chr($key2).chr($key);//超过26个字母时才会启用  
                    }else{
                        $colum = chr($key);
                    }
                }
                $objActSheet->setCellValue($colum.$column, $value);
                $key++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);

        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
    
    //扫描指定年分区间数据库是有数据，如果有数组返回年分。
    public function getYear($section) {
        $result = explode('-',$section);
        $for = abs($result[0]-$result[1]);
        $first = $result[0]<$result[1]?$result[0]:$result[1];
        for($i=1;$i<=$for;$i++) {
            $momont = $this->getthemonth($first."-1");
            $firstDay = $momont[0];
            $write = $first;
            $first++;
            $momont = $this->getthemonth($first."-1");
            $lastDay = $momont[0];
            $where['in_time'] = array(array('EGT',$firstDay),array('LT',$lastDay));
            $result = M('kefuinfo')->where($where)->find();
            if($result) {
                $data[] = (int)$write;
            }
        }
        return $data;
    }
    //返回月份头一天和最后一天
    function getthemonth($date,$type = 'true'){
        $firstday = date('Y-m-01', strtotime($date));
        $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
        if($type == 'true'){
            $return = array(strtotime($firstday),strtotime($lastday));
        }else{
            $return = array(strtotime($firstday),strtotime($lastday)+24*3600);
        }
        return $return;
    }
    
    //年份和月份总额数据
    public function yearData($year,$status=null) {
        for($i=1;$i<=12;$i++) {
            $result = $this->getthemonth($year."-".$i);
            $where['in_time'] = array(array('EGT',$result[0]),array('LT',$result[1]));
            is_numeric($status)?$where['status']=$status:'';
            $data[$i] = M("kefuinfo")->where($where)->count();
            $num += $data[$i];
        }
        $data['all'] = $num;
        return $data;
    }
    //年份统计数据
    public function seach($year=0) {
       $yearArr = $this->getYear("2015-2020"); //区间年分数据扫描
       $currentYear = $year == 0?$yearArr[0]:$year;
       $yearAllData = $this->yearData($currentYear);//年度数据
       $yearZeroData= $this->yearData($currentYear,0);//失约数据
       $yearFirstData = $this->yearData($currentYear,1);//来院数据
       $yearPercentageData =$this->percentage($yearFirstData,$yearAllData);//来院比率
       $this->assign("yearAllData",$yearAllData);
       $this->assign("yearZeroData",$yearZeroData);
       $this->assign("yearFirstData",$yearFirstData);
       $this->assign("currentYear",$currentYear);
       $this->assign("yearPercentageData",$yearPercentageData);
       $this->assign('yearArr',$yearArr);
    }
    //月份数据
    public function monthAllDate($date,$status=null){
        $result = $this->getthemonth($date);
        $num =1;
        $day=$result[0];
        while($day<=$result[1])
        {   
            $tomorrow =60*60*24+$day; 
            $where['in_time'] = array(array('EGT',$day),array('LT',$tomorrow));
            is_numeric($status)?$where['status'] = $status:'';
            $data[$num] = M('kefuinfo')->where($where)->count();
            $all += (int)$data[$num];
            $day+=60*60*24;
            $num++;
        }
        $data['all'] = $all;
        return $data;
    }
    //比率
    public function percentage($part,$all) {
        foreach($all as $k=>$v) {
            if($v > 0) {
                $data[$k] = round($part[$k]/$v*100,2);
            }else{
                $data[$k] = 0;
            }
        }
        return $data;
    }
    // 导出年度xls
    public function XlsOfYear($year=2017) {
        $yearAllData = $this->yearData($year);//年度数据
        $yearZeroData= $this->yearData($year,0);//失约数据
        $yearFirstData = $this->yearData($year,1);//来院数据
        $yearPercentageData =$this->percentage($yearFirstData,$yearAllData);//来院比率   
        
        array_unshift($yearZeroData,"未到");
        $data[] = $yearZeroData;
        array_unshift($yearFirstData,"来院");
        $data[] = $yearFirstData;
        foreach($yearPercentageData as $k=>$v) {
            $yearPercentageData[$k] = $v."%";
        }
        array_unshift($yearAllData,"总额");
        $data[] = $yearAllData;
        array_unshift($yearPercentageData,"比率");
        $data[] = $yearPercentageData;
        //print_r($goods_list);
        $headArr = array(
            0=>'项目',
            1=>'一月',
            2=>'二月',
            3=>'三月',
            4=>'四月',
            5=>'五月',
            6=>'六月',
            7=>'七月',
            8=>'八月',
            9=>'九月',
            10=>'十月',
            11=>'十一月',
            12=>'十二月',
            13=>'合计'
        );
        $filename=$year."挂号报表";
        $this->getExcel($filename,$headArr,$data);
    }
    // 导出月度xls
    public function XlsOfMonth($year,$month) {
        $date = $year."-".$month;
        $monthAllData  = $this->monthAllDate($date);//月份总额数据
        $monthZeroData = $this->monthAllDate($date,0); //月份未到数据 
        $monthFirstData = $this->monthAllDate($date,1); //月份来院数据 
        $monthPercentageData = $this->percentage($monthFirstData,$monthAllData);
        
        foreach($monthAllData as $k=>$v) {
            if($k != 'all') {
                $headArr[] = $k.'日';
            }else{
                $headArr[] = '合计';
            }
        }
        array_unshift($headArr,"项目");
        array_unshift($monthAllData,"总额");
        $data[] = $monthAllData;
        array_unshift($monthZeroData,"未到");
        $data[] = $monthZeroData;
        array_unshift($monthFirstData,"来院");
        $data[] = $monthFirstData;
        array_unshift($monthPercentageData,"比率");
        $data[] = $monthPercentageData;
        $filename=$date."挂号报表";
        $this->getExcel($filename,$headArr,$data);
    }
    //导出当天
    public function XlsOfDay($year,$month,$day) {
        $timeOfStart = strtotime($year.'-'.$month.'-'.$day);
        $timeOfEnd   = $timeOfStart+60*60*24;
        $where['in_time'] = array(array('EGT',$timeOfStart),array('lt',$timeOfEnd));
        $result = M("kefuinfo")->where($where)->field('in_time')->select();
        for($i=1;$i<=24;$i++){//计算24小时挂号量数据
            $where['in_time'] = array(array('EGT',$timeOfStart),array('lt',$timeOfStart+=60*60));//总额
            $dayAllData[$i] = M("kefuinfo")->where($where)->count();
            $dayAll +=$dayAllData[$i];
            $where['status'] = 0;//未到
            $dayZeroData[$i] = M("kefuinfo")->where($where)->count();
            $dayZeroAll +=$DayZeroData[$i];
            $where['status'] = 1;//到院
            $dayFirstData[$i] = M("kefuinfo")->where($where)->count();
            $dayFirstAll +=$dayFirstData[$i];
            unset($where['status']);
            $headArr[] = $i.":00";// 
        }
        array_unshift($headArr,"项目");
        $headArr[] = '合计';
        $dayAllData['all'] = $dayAll;
        $dayZeroData['all'] = $dayZeroAll;
        $dayFirstData['all'] = $dayFirstAll;
        $dayPercentageData = $this->percentage($dayFirstData,$dayAllData);
        
        array_unshift($dayZeroData,"未到");
        $data[] = $dayZeroData;
        array_unshift($dayFirstData,"来院");
        $data[] = $dayFirstData;
        foreach($dayPercentageData as $k=>$v) {
            $dayPercentageData[$k] = $v."%";
        }
        array_unshift($dayAllData,"总额");
        $data[] = $dayAllData;
        array_unshift($dayPercentageData,"比率");
        $data[] = $dayPercentageData;
        //dump($data);exit;
        $filename=$year.'-'.$month.'-'.$day."挂号报表";
        $this->getExcel($filename,$headArr,$data);
    }
}
