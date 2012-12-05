<?php

/**
+------------------------------------------------------------
 *  ����ͼ������
+------------------------------------------------------------
 * @ͼƬ���δ���
 * @author     lhl
 * @time       2010/5/18
+------------------------------------------------------------
**/

class SimpleImage extends Object{ 
	var $image; 
	var $image_type; 
	function load($filename) { 
		$image_info = getimagesize($filename); 
		$this->image_type = $image_info[2]; 
		if ($this->image_type == IMAGETYPE_JPEG) { 
			$this->image = imagecreatefromjpeg($filename); 
		} elseif ($this->image_type == IMAGETYPE_GIF) { 
			$this->image = imagecreatefromgif($filename); 
		} elseif ($this->image_type == IMAGETYPE_PNG) { 
			$this->image = imagecreatefrompng($filename); 
		} 
	} 
	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=90, $permissions=null) { 
		if ($image_type == IMAGETYPE_JPEG) { 
			imagejpeg($this->image,$filename,$compression); 
		} elseif ($image_type == IMAGETYPE_GIF) { 
			imagegif($this->image,$filename); 
		} elseif ($image_type == IMAGETYPE_PNG) { 
			imagepng($this->image,$filename); 
		} 
		if ($permissions != null) { 
			chmod($filename,$permissions); 
		}
		imagedestroy($this->image); //�ͷ��ڴ�
	} 
	function output($image_type=IMAGETYPE_JPEG) { 
		if ($image_type == IMAGETYPE_JPEG) { 
			imagejpeg($this->image); 
		} elseif ($image_type == IMAGETYPE_GIF) { 
			imagegif($this->image); 
		} elseif ($image_type == IMAGETYPE_PNG) { 
			imagepng($this->image); 
		} 
	} 
	function getWidth() { 
		return imagesx($this->image); 
	} 
	function getHeight() { 
		return imagesy($this->image); 
	} 
	function resizeToHeight($height) { 
		$ratio = $height / $this->getHeight(); 
		$width = $this->getWidth() * $ratio; 
		$this->resize($width,$height); 
	}
	function resizeToWidthAndHeight($width,$height) {
		//ͼƬС��750*750����ͼƬ��������1:1,ͼƬ��������
		if ($width==750&&$height==750)
		{
			if ($this->getWidth()<$width||$this->getHeight()<$height)
			{
				return false;
			}else{
				$rz = $this->getWidth()/$this->getHeight();
				if ($rz!=1)
				{
					return false;
				}
			}
		}
		if ($this->getWidth() >= $this->getHeight()) {  //ԭͼ����ڸ�
			if ($this->getWidth() >= $width) {  		 //ԭͼ�����Ҫ���ɵ�ͼƬ�Ŀ�
				$newheight = ($this->getHeight() * $width)/$this->getWidth();
				if ($newheight<$height) {
					$this->resize($width,$newheight); 
				} else {
					$newwidth = ($this->getWidth() * $height)/$this->getHeight();
					$this->resize($newwidth,$height); 
				} 	
			} elseif ($this->getWidth()<$width&&$this->getHeight()>=$height){
				$newwidth = ($this->getWidth() * $height)/$this->getHeight();
				if ($newwidth<$width) {
					$this->resize($newwidth,$height); 
				} else {
					$newheight = ($this->getHeight() * $width)/$this->getWidth();
					$this->resize($width,$newheight); 
				}
			} else {
				$this->resize($this->getWidth(),$this->getHeight()); 
			}
		} else {
			if ($this->getHeight() >= $height) {  		 //ԭͼ�ߴ���Ҫ���ɵ�ͼƬ�ĸ�
				$newwidth = ($this->getWidth() * $height)/$this->getHeight();
				if ($newwidth < $width) {
					$this->resize($newwidth,$height); 
				} else {
					$newheight = ($this->getHeight() * $width)/$this->getWidth();
					$this->resize($width, $newheight); 
				} 	
			} elseif ($this->getHeight() < $height&&$this->getWidth()>=$width){
				if ($newheight < $height) {
					$this->resize($width, $newheight); 
				} else {
					$newwidth = ($this->getWidth() * $height)/$this->getHeight();
					$this->resize($newwidth, $height); 
				}
			} else {
				$this->resize($this->getWidth(), $this->getHeight()); 
			}
		}
		return true; 
	} 
	function resizeToWidth($width) { 
		$ratio = $width / $this->getWidth(); 
		$height = $this->getheight() * $ratio; 
		$this->resize($width,$height); 
	} 
	function scale($scale) { 
		$width = $this->getWidth() * $scale/100; 
		$height = $this->getheight() * $scale/100; 
		$this->resize($width,$height); 
	}
	//ʹ����ͼ������
	function resize($width,$height) {
		//�����ͼ�� ����� ���ڵ��� �߱���
		if (($this->getWidth()/$width) >= ($this->getheight()/$height)){
			$temp_height = $height;
			$temp_width = $this->getWidth()/($this->getheight()/$height);
			$src_X = abs(($width-$temp_width)/2);
			$src_Y = 0;
		} else {
			$temp_width=$width;
			$temp_height=$this->getheight()/($this->getWidth()/$width);
			$src_X=0;
			$src_Y=abs(($height-$temp_height)/2);
		}
		$temp_img = imagecreatetruecolor($temp_width,$temp_height);
		imagecopyresampled($temp_img,$this->image,0,0,0,0,$temp_width,$temp_height,$this->getWidth(),$this->getheight());

		$ni=imagecreatetruecolor($width,$height);
		imagecopyresampled($ni,$temp_img,0,0,$src_X,$src_Y,$width,$height,$width,$height);
		$this->image = $ni; 
        imagedestroy($temp_img); //�ͷ��ڴ�
	}
	/*
	* ���ܣ�PHPͼƬˮӡ (ˮӡ֧��ͼƬ������) 
	* ������
	*      $groundImage     ����ͼƬ������Ҫ��ˮӡ��ͼƬ����ֻ֧��GIF,JPG,PNG��ʽ��
	*      $waterPos        ˮӡλ�ã���10��״̬��0Ϊ���λ�ã�
	*                       1Ϊ���˾���2Ϊ���˾��У�3Ϊ���˾��ң�
	*                       4Ϊ�в�����5Ϊ�в����У�6Ϊ�в����ң�
	*                       7Ϊ�׶˾���8Ϊ�׶˾��У�9Ϊ�׶˾��ң�
	*      $waterImage      ͼƬˮӡ������Ϊˮӡ��ͼƬ����ֻ֧��GIF,JPG,PNG��ʽ��
	*      $waterText       ����ˮӡ������������ΪΪˮӡ��֧��ASCII�룬��֧�����ģ�
	*      $textFont        ���ִ�С��ֵΪ1��2��3��4��5��Ĭ��Ϊ5��
	*      $textColor       ������ɫ��ֵΪʮ��������ɫֵ��Ĭ��Ϊ#FF0000(��ɫ)��
	*
	* ע�⣺Support GD ��Support FreeType��GIF Read��GIF Create��JPG ��PNG
	*      $waterImage �� $waterText ��ò�Ҫͬʱʹ�ã�ѡ����֮һ���ɣ�����ʹ�� $waterImage��
	*      ��$waterImage��Чʱ������$waterString��$stringFont��$stringColor������Ч��
	*      ��ˮӡ���ͼƬ���ļ����� $groundImage һ����
	* ���ߣ�lhl @ 2010-7-22 14:15:13
	*/
	function imageWaterMark($groundImage,$waterPos=0,$waterImage="",$waterText="",$textFont=5,$textColor="#FF0000",$limit=false) {
		$isWaterImage = FALSE;
		$formatMsg = "�ݲ�֧�ָ��ļ���ʽ������ͼƬ���������ͼƬת��ΪGIF��JPG��PNG��ʽ��";
		$errorMsg  = '';
		//��ȡˮӡ�ļ�
		if (!empty($waterImage) && file_exists($waterImage)) {
			$isWaterImage = TRUE;
			$water_info = getimagesize($waterImage);
			$water_w = $water_info[0]; //ȡ��ˮӡͼƬ�Ŀ�
			$water_h = $water_info[1]; //ȡ��ˮӡͼƬ�ĸ�

			switch ($water_info[2]) { //ȡ��ˮӡͼƬ�ĸ�ʽ
				case 1:
					$water_im = imagecreatefromgif($waterImage);
					break;
				case 2:
					$water_im = imagecreatefromjpeg($waterImage);
					break;
				case 3:
					$water_im = imagecreatefrompng($waterImage);
					break;
				default:
					die($formatMsg);
			}
		}

		//��ȡ����ͼƬ
		if (!empty($groundImage) && file_exists($groundImage)) {
			$ground_info = getimagesize($groundImage);
			$ground_w = $ground_info[0]; //ȡ�ñ���ͼƬ�Ŀ�
			$ground_h = $ground_info[1]; //ȡ�ñ���ͼƬ�ĸ�
			
			//�������Ʊ���ͼƬ�ߴ���350�Ŵ�ˮӡ
			if ($limit)
			{
				if($ground_h<350)
				{
					return false;
				}
			}
		
			switch ($ground_info[2]) { //ȡ�ñ���ͼƬ�ĸ�ʽ
				case 1:
					$ground_im = imagecreatefromgif($groundImage);
					break;
				case 2:
					$ground_im = imagecreatefromjpeg($groundImage);
					break;
				case 3:
					$ground_im = imagecreatefrompng($groundImage);
					break;
				default:
					die($formatMsg);
			}
		} else {
			$errorMsg = "��Ҫ��ˮӡ��ͼƬ�����ڣ�";
			return $errorMsg;
		}

		//ˮӡλ��
		if ($isWaterImage) { //ͼƬˮӡ
			$w = $water_w;
			$h = $water_h;
			$label = "ͼƬ��";
		} else { //����ˮӡ
			$temp = imagettfbbox(ceil($textFont*2.5),0,"./cour.ttf",$waterText); //ȡ��ʹ�� TrueType ������ı��ķ�Χ
			$w = $temp[2] - $temp[6];
			$h = $temp[3] - $temp[7];
			unset($temp);
			$label = "��������";
		}
		if (($ground_w<$w) || ($ground_h<$h)) {
			$errorMsg = "��Ҫ��ˮӡ��ͼƬ�ĳ��Ȼ��ȱ�ˮӡ".$label."��С���޷�����ˮӡ��";
			return $errorMsg;
		}
		switch ($waterPos) {
			case 0: //���
				$posX = rand(0,($ground_w - $w));
				$posY = rand(0,($ground_h - $h));
				break;
			case 1: //1Ϊ���˾���
				$posX = 0;
				$posY = 0;
				break;
			case 2: //2Ϊ���˾���
				$posX = ($ground_w - $w) / 2;
				$posY = 0;
				break;
			case 3: //3Ϊ���˾���
				$posX = $ground_w - $w;
				$posY = 0;
				break;
			case 4: //4Ϊ�в�����
				$posX = 0;
				$posY = ($ground_h - $h) / 2;
				break;
			case 5: //5Ϊ�в�����
				$posX = ($ground_w - $w) / 2;
				$posY = ($ground_h - $h) / 2;
				break;
			case 6: //6Ϊ�в�����
				$posX = $ground_w - $w;
				$posY = ($ground_h - $h) / 2;
				break;
			case 7: //7Ϊ�׶˾���
				$posX = 0;
				$posY = $ground_h - $h;
				break;
			case 8: //8Ϊ�׶˾���
				$posX = ($ground_w - $w) / 2;
				$posY = $ground_h - $h;
				break;
			case 9: //9Ϊ�׶˾���
				$posX = $ground_w - $w -5;
				$posY = $ground_h - $h -5;
				break;
			default: //���
				$posX = rand(0,($ground_w - $w));
				$posY = rand(0,($ground_h - $h));
				break;
		}

		//�趨ͼ��Ļ�ɫģʽ
		imagealphablending($ground_im, true);

		if ($isWaterImage) { //ͼƬˮӡ
			imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h); //����ˮӡ��Ŀ���ļ�  
		} else { //����ˮӡ
			if (!empty($textColor) && (strlen($textColor)==7)) {
				$R = hexdec(substr($textColor,1,2));
				$G = hexdec(substr($textColor,3,2));
				$B = hexdec(substr($textColor,5));
			} else {
				$errorMsg = "ˮӡ������ɫ��ʽ����ȷ��";
				return $errorMsg;
			}
			imagestring($ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));     
		}

		//����ˮӡ���ͼƬ
		@unlink($groundImage);
		switch ($ground_info[2]) { //ȡ�ñ���ͼƬ�ĸ�ʽ
			case 1:
				imagegif($ground_im,$groundImage);
				break;
			case 2:
				imagejpeg($ground_im,$groundImage,100);
				break;
			case 3:
				imagepng($ground_im,$groundImage);
				break;
			default:
				die($errorMsg);
		}

		//�ͷ��ڴ�
		if (isset($water_info)) unset($water_info);
		if (isset($water_im)) imagedestroy($water_im);
		unset($ground_info);
		imagedestroy($ground_im);
		return true;
	}
	//����gd����������ͼ
	function make_thumb($name,$filepath,$width,$height){
		$this->load(ROOT_PATH.'/'.$name);
		$info = $this->resizeToWidthAndHeight($width,$height);
		if ($info)
		{
			$this->save($filepath);
			if ($width==750)
			{
				//��������ͼƬˮӡ
				$waterImage = ROOT_PATH . '/themes/mall/default/styles/default/images/pailashuiyin.png';
				$this->imageWaterMark($filepath,9,$waterImage);
			}
		}
	}
}
?>