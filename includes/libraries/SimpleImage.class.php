<?php

/**
+------------------------------------------------------------
 *  缩略图制作类
+------------------------------------------------------------
 * @图片二次处理
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
		imagedestroy($this->image); //释放内存
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
		//图片小于750*750或者图片比例不是1:1,图片不做处理
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
		if ($this->getWidth() >= $this->getHeight()) {  //原图宽大于高
			if ($this->getWidth() >= $width) {  		 //原图宽大于要生成的图片的宽
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
			if ($this->getHeight() >= $height) {  		 //原图高大于要生成的图片的高
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
	//使缩略图不变形
	function resize($width,$height) {
		//如果新图的 宽比例 大于等于 高比例
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
        imagedestroy($temp_img); //释放内存
	}
	/*
	* 功能：PHP图片水印 (水印支持图片或文字) 
	* 参数：
	*      $groundImage     背景图片，即需要加水印的图片，暂只支持GIF,JPG,PNG格式；
	*      $waterPos        水印位置，有10种状态，0为随机位置；
	*                       1为顶端居左，2为顶端居中，3为顶端居右；
	*                       4为中部居左，5为中部居中，6为中部居右；
	*                       7为底端居左，8为底端居中，9为底端居右；
	*      $waterImage      图片水印，即作为水印的图片，暂只支持GIF,JPG,PNG格式；
	*      $waterText       文字水印，即把文字作为为水印，支持ASCII码，不支持中文；
	*      $textFont        文字大小，值为1、2、3、4或5，默认为5；
	*      $textColor       文字颜色，值为十六进制颜色值，默认为#FF0000(红色)；
	*
	* 注意：Support GD ，Support FreeType、GIF Read、GIF Create、JPG 、PNG
	*      $waterImage 和 $waterText 最好不要同时使用，选其中之一即可，优先使用 $waterImage。
	*      当$waterImage有效时，参数$waterString、$stringFont、$stringColor均不生效。
	*      加水印后的图片的文件名和 $groundImage 一样。
	* 作者：lhl @ 2010-7-22 14:15:13
	*/
	function imageWaterMark($groundImage,$waterPos=0,$waterImage="",$waterText="",$textFont=5,$textColor="#FF0000",$limit=false) {
		$isWaterImage = FALSE;
		$formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。";
		$errorMsg  = '';
		//读取水印文件
		if (!empty($waterImage) && file_exists($waterImage)) {
			$isWaterImage = TRUE;
			$water_info = getimagesize($waterImage);
			$water_w = $water_info[0]; //取得水印图片的宽
			$water_h = $water_info[1]; //取得水印图片的高

			switch ($water_info[2]) { //取得水印图片的格式
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

		//读取背景图片
		if (!empty($groundImage) && file_exists($groundImage)) {
			$ground_info = getimagesize($groundImage);
			$ground_w = $ground_info[0]; //取得背景图片的宽
			$ground_h = $ground_info[1]; //取得背景图片的高
			
			//新增限制背景图片高大于350才打水印
			if ($limit)
			{
				if($ground_h<350)
				{
					return false;
				}
			}
		
			switch ($ground_info[2]) { //取得背景图片的格式
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
			$errorMsg = "需要加水印的图片不存在！";
			return $errorMsg;
		}

		//水印位置
		if ($isWaterImage) { //图片水印
			$w = $water_w;
			$h = $water_h;
			$label = "图片的";
		} else { //文字水印
			$temp = imagettfbbox(ceil($textFont*2.5),0,"./cour.ttf",$waterText); //取得使用 TrueType 字体的文本的范围
			$w = $temp[2] - $temp[6];
			$h = $temp[3] - $temp[7];
			unset($temp);
			$label = "文字区域";
		}
		if (($ground_w<$w) || ($ground_h<$h)) {
			$errorMsg = "需要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！";
			return $errorMsg;
		}
		switch ($waterPos) {
			case 0: //随机
				$posX = rand(0,($ground_w - $w));
				$posY = rand(0,($ground_h - $h));
				break;
			case 1: //1为顶端居左
				$posX = 0;
				$posY = 0;
				break;
			case 2: //2为顶端居中
				$posX = ($ground_w - $w) / 2;
				$posY = 0;
				break;
			case 3: //3为顶端居右
				$posX = $ground_w - $w;
				$posY = 0;
				break;
			case 4: //4为中部居左
				$posX = 0;
				$posY = ($ground_h - $h) / 2;
				break;
			case 5: //5为中部居中
				$posX = ($ground_w - $w) / 2;
				$posY = ($ground_h - $h) / 2;
				break;
			case 6: //6为中部居右
				$posX = $ground_w - $w;
				$posY = ($ground_h - $h) / 2;
				break;
			case 7: //7为底端居左
				$posX = 0;
				$posY = $ground_h - $h;
				break;
			case 8: //8为底端居中
				$posX = ($ground_w - $w) / 2;
				$posY = $ground_h - $h;
				break;
			case 9: //9为底端居右
				$posX = $ground_w - $w -5;
				$posY = $ground_h - $h -5;
				break;
			default: //随机
				$posX = rand(0,($ground_w - $w));
				$posY = rand(0,($ground_h - $h));
				break;
		}

		//设定图像的混色模式
		imagealphablending($ground_im, true);

		if ($isWaterImage) { //图片水印
			imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h); //拷贝水印到目标文件  
		} else { //文字水印
			if (!empty($textColor) && (strlen($textColor)==7)) {
				$R = hexdec(substr($textColor,1,2));
				$G = hexdec(substr($textColor,3,2));
				$B = hexdec(substr($textColor,5));
			} else {
				$errorMsg = "水印文字颜色格式不正确！";
				return $errorMsg;
			}
			imagestring($ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));     
		}

		//生成水印后的图片
		@unlink($groundImage);
		switch ($ground_info[2]) { //取得背景图片的格式
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

		//释放内存
		if (isset($water_info)) unset($water_info);
		if (isset($water_im)) imagedestroy($water_im);
		unset($ground_info);
		imagedestroy($ground_im);
		return true;
	}
	//利用gd库生成缩略图
	function make_thumb($name,$filepath,$width,$height){
		$this->load(ROOT_PATH.'/'.$name);
		$info = $this->resizeToWidthAndHeight($width,$height);
		if ($info)
		{
			$this->save($filepath);
			if ($width==750)
			{
				//打上派啦图片水印
				$waterImage = ROOT_PATH . '/themes/mall/default/styles/default/images/pailashuiyin.png';
				$this->imageWaterMark($filepath,9,$waterImage);
			}
		}
	}
}
?>