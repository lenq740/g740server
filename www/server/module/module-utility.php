<?php
/**
Утилиты для пересчета вычисляемых полей, и перегенерации файлов
@package module
@subpackage module-utility
*/

function getRisFileNameFromId($klsris) {
	$name=str_pad(ltrim($klsris),2,'0',STR_PAD_LEFT);
	$dir=str_pad(substr($name,0,strlen($name)-2),3,'0',STR_PAD_LEFT);
	return $dir.'/'.ltrim($klsris);
}

function doRisResize($sourceFileName, $resultFileName, $resWidth, $resHeight, $isCover) {
	$imgQuality=85;
	$result=true;
	try {
		$sourceImgSize=getimagesize($sourceFileName);
		if (!$sourceImgSize) throw new Exception('Ошибка при открытии файла иллюстрации!!!');
		$sourceWidth=$sourceImgSize[0];
		$sourceHeight=$sourceImgSize[1];
		if (($sourceWidth<=0) || ($sourceHeight<=0)) throw new Exception('Не удалось определить размеры изображения!');
		$img=@imagecreatefromjpeg($sourceFileName);
		if (!$img) throw new Exception('Ошибка при открытии файла!');
		
		if ($isCover) {
			$srcWidth=$sourceWidth;
			$srcHeight=$sourceHeight;
			if (floor($sourceHeight*($resWidth/$resHeight))<$srcWidth) $srcWidth=floor($sourceHeight*($resWidth/$resHeight));
			if (floor($sourceWidth*($resHeight/$resWidth))<$srcHeight) $srcHeight=floor($sourceWidth*($resHeight/$resWidth));
			$resImg=imagecreatetruecolor($resWidth,$resHeight);
			imagecopyresampled(
				$resImg,
				$img,
				0,0,floor(($sourceWidth-$srcWidth)/2),floor(($sourceHeight-$srcHeight)/2),
				$resWidth,$resHeight,$srcWidth,$srcHeight
			);
			imagejpeg($resImg, $resultFileName, $imgQuality);
			imagedestroy($resImg);
		} 
		else {
			$kX=$resWidth/$sourceWidth;
			$kY=$resHeight/$sourceHeight;
			$k=$kX;
			if ($k>$kY) $k=$kY;
			if ($k>1) {
				copy($sourceFileName, $resultFileName);
			}
			else {
				$resWidth=floor($sourceWidth*$k);
				$resHeight=floor($sourceHeight*$k);
				$resImg=imagecreatetruecolor($resWidth,$resHeight);
				imagecopyresampled(
					$resImg,
					$img,
					0,0,
					0,0,
					$resWidth,$resHeight,
					$sourceWidth,$sourceHeight
				);
				imagejpeg($resImg, $resultFileName, $imgQuality);
				imagedestroy($resImg);
			}
		}
		imagedestroy($img);
	}
	catch (Exception $e) {
		$result=false;
	}
	return $result;
}
?>