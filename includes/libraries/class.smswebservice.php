<?php
/**
 +----------------------------------------------------------
 * SmsWebservice  短信接口
 +----------------------------------------------------------
 * @author    lihuoliang
 * @date      2011-07-22
 * @version   1.0
 +----------------------------------------------------------
 */
class SmsWebservice{
	//实例化短信接口对象
	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new SmsWebservice();
		}
		return $object;
	}
	/*
	 * 短信发送(全网平台)
	 * 
	 * @param 	
	 * 			$mobiles		接收短信的手机号列表
	 * 			$content		短信内容
	 * 
	 * @return  int   			短信发送的状态
	 */
	function SendSms($mobiles,$content){
		
		/*
		       短信发送所需的配置参数
		   sname:提交用户
		   spwd:提交密码
		   scorpid:企业代码 (可不填)
		   sprdid:产品编号
		   sdst:接收号码，多个以','分割,不可超过10000个号码
		   smsg:短信内容
		 */
		$service = 'http://cf.lmobile.cn/submitdata/service.asmx?wsdl'; //远程短信接口地址
		
		//实例化SoapClient类
		$client = new SoapClient($service);
		
		$smscontent = iconv('GBK','UTF-8',$content);
		
		//设置webservice接口所需要的参数
		$param = array('sname'=>'dljxslrc','spwd'=>'87654321','scorpid'=>'','sprdid'=>'1012818','sdst'=>$mobiles,'smsg'=>$smscontent);
		
		//调用远程的方法获取数据
		$result = $client->__Call('g_Submit',array('paramters'=>$param));
		
		//return $result->g_SubmitResult->State;
		return $result;
	} 
	
	
	
	/*
	 * 短信发送(全网平台)
	 * 
	 * @param 	
	 * 			$mobiles		接收短信的手机号列表
	 * 			$content		短信内容
	 * 
	 * @return  int   			短信发送的状态
	 */
	function SendSms2($mobiles,$content){
		
		$content = urlencode(iconv('GBK','UTF-8',$content)); //短信的内容
		$gateway = "http://cf.lmobile.cn/submitdata/service.asmx/g_Submit?sname=dljxslrc&spwd=87654321&scorpid=&sprdid=1012818&sdst={$mobiles}&smsg={$content}";
		$result = file_get_contents($gateway);
		
		if (preg_match("/<State>0<\/State>+/",$result)){
			return 0;
		}else{
			return 1;
		}
	}
	
	public function log($param) {
		$sms_log_mod = & m("smslog");
		$sms_log_mod->add($param);
	}
	
	/*
	 * 短信群发(全网平台)
	 * 
	 * @param 	
	 * 			$mobiles		接收短信的手机号列表
	 * 			$content		短信内容
	 * 
	 * @return  int   			短信发送的状态
	 */
	function SmsFsend($mobiles,$content) {
		$gateway = "http://dx.lmobile.cn:6003/submitdata/Service.asmx?wsdl";
		
		//实例化SoapClient类
		$client = new SoapClient($gateway);
		
		$smscontent = iconv('GBK','UTF-8',$content);
		$mobile_str = '';
		if(is_array($mobiles)) {
			foreach($mobiles as $mobile) {
				$mobile_str .= $mobile . ",";
			}
			$mobile_str = substr($mobile_str, 0, -1);
		} else {
			$mobile_str = $mobiles;
		}
		//设置webservice接口所需要的参数
		$param = array('sname'=>'dlyongchuang','spwd'=>'20116688','scorpid'=>'','sprdid'=>'1012812','sdst'=>$mobile_str,'smsg'=>$smscontent);
		//调用远程的方法获取数据
		$result = $client->__Call('g_Submit',array('paramters'=>$param));
		
		return $result;
	}
}
 ?>