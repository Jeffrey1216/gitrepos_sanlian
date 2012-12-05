<?php
/**
 +----------------------------------------------------------
 * SmsWebservice  ���Žӿ�
 +----------------------------------------------------------
 * @author    lihuoliang
 * @date      2011-07-22
 * @version   1.0
 +----------------------------------------------------------
 */
class SmsWebservice{
	//ʵ�������Žӿڶ���
	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new SmsWebservice();
		}
		return $object;
	}
	/*
	 * ���ŷ���(ȫ��ƽ̨)
	 * 
	 * @param 	
	 * 			$mobiles		���ն��ŵ��ֻ����б�
	 * 			$content		��������
	 * 
	 * @return  int   			���ŷ��͵�״̬
	 */
	function SendSms($mobiles,$content){
		
		/*
		       ���ŷ�����������ò���
		   sname:�ύ�û�
		   spwd:�ύ����
		   scorpid:��ҵ���� (�ɲ���)
		   sprdid:��Ʒ���
		   sdst:���պ��룬�����','�ָ�,���ɳ���10000������
		   smsg:��������
		 */
		$service = 'http://cf.lmobile.cn/submitdata/service.asmx?wsdl'; //Զ�̶��Žӿڵ�ַ
		
		//ʵ����SoapClient��
		$client = new SoapClient($service);
		
		$smscontent = iconv('GBK','UTF-8',$content);
		
		//����webservice�ӿ�����Ҫ�Ĳ���
		$param = array('sname'=>'dljxslrc','spwd'=>'87654321','scorpid'=>'','sprdid'=>'1012818','sdst'=>$mobiles,'smsg'=>$smscontent);
		
		//����Զ�̵ķ�����ȡ����
		$result = $client->__Call('g_Submit',array('paramters'=>$param));
		
		//return $result->g_SubmitResult->State;
		return $result;
	} 
	
	
	
	/*
	 * ���ŷ���(ȫ��ƽ̨)
	 * 
	 * @param 	
	 * 			$mobiles		���ն��ŵ��ֻ����б�
	 * 			$content		��������
	 * 
	 * @return  int   			���ŷ��͵�״̬
	 */
	function SendSms2($mobiles,$content){
		
		$content = urlencode(iconv('GBK','UTF-8',$content)); //���ŵ�����
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
	 * ����Ⱥ��(ȫ��ƽ̨)
	 * 
	 * @param 	
	 * 			$mobiles		���ն��ŵ��ֻ����б�
	 * 			$content		��������
	 * 
	 * @return  int   			���ŷ��͵�״̬
	 */
	function SmsFsend($mobiles,$content) {
		$gateway = "http://dx.lmobile.cn:6003/submitdata/Service.asmx?wsdl";
		
		//ʵ����SoapClient��
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
		//����webservice�ӿ�����Ҫ�Ĳ���
		$param = array('sname'=>'dlyongchuang','spwd'=>'20116688','scorpid'=>'','sprdid'=>'1012812','sdst'=>$mobile_str,'smsg'=>$smscontent);
		//����Զ�̵ķ�����ȡ����
		$result = $client->__Call('g_Submit',array('paramters'=>$param));
		
		return $result;
	}
}
 ?>