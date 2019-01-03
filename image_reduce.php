<?php
/**
 * @param $imgSrc string 压缩图片路径
 * @param $imgDst string 压缩后图片路径
 */
function image_png_size_add($imgSrc,$imgDst)
{
	list($width,$height,$type) = getimagesize($imgSrc);
	$newWidth = ($width > 600 ? 600 : $width)* 0.9;
	$newHeight = ($height > 600 ? 600 : $height)*0.9;
	switch ($type){
        case 1:
            $gifType = checkGif($imgSrc);
            if ($gifType){
                header('Content-Type:image/gif');
                $imageWp = imagecreatetruecolor($newWidth,$newHeight);
                $image = imagecreatefromgif($imgSrc);
                imagecopyresampled($imageWp,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);
                imagejpeg($imageWp,$imgDst,75);
                imagedestroy($imageWp);
            }
            break;
        case 2:
            header('Content-Type:image/jpeg');
            $imageWp = imagecreatetruecolor($newWidth,$newHeight);
            $image = imagecreatefromjpeg($imgSrc);
            imagecopyresampled($imageWp,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);
            imagejpeg($imageWp,$imgDst,75);
            imagedestroy($imageWp);
            break;
        case 3:
            header('Content-type:image/png');
            $imageWp = imagecreatetruecolor($newWidth,$newHeight);
            $image = imagecreatefrompng($imgSrc);
            imagecopyresampled($imageWp,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);
            imagejpeg($imageWp,75);
            imagedestroy($imageWp);
    }
}


/**
 * 判断是否gif动画
 * @param $image_file
 * @return bool
 */
function checkGif($image_file){
  $fp = fopen($image_file,'rb');   
  $image_head = fread($fp,1024);   
  fclose($fp);   
  return preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)?false:true;   
}   