<?php

/**
 * 助手类
 */
class Helper
{
    /**
     * 判断当前服务器系统
     * @return string
     */
    public static function getOs()
    {
        if (PATH_SEPARATOR == ':') {
            return 'Linux';
        } else {
            return 'Windows';
        }
    }

    /**
     * @return float
     * 当前微秒数
     */
    public static function microTimeFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 切割utf-8格式的字符串(一个汉字或者字符占一个字节)
     * @param $string
     * @param $length
     * @param string $etc
     * @return string
     */
    public static function truncateUtf8String($string, $length, $etc = '...')
    {
        $result = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strlen = strlen($string);
        for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
            $number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0');
            if ($number) {
                if ($length < 1.0) {
                    break;
                }
                $result .= substr($string, $i, $number);
                $length -= 1.0;
                $i += $number - 1;
            } else {
                $result .= substr($string, $i, 1);
                $length -= 0.5;
            }
        }
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        if ($i < $strlen) {
            $result .= $etc;
        }
        return $result;
    }

    /**
     * 遍历文件夹
     * @param string $dir
     * @param bool $all true 表示递归遍历
     * @param array $ret
     */
    public static function scanDir($dir = '', $all = false, &$ret = array())
    {
        if (false !== ($handle = opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
//                '.', '..', '.git', '.gitignore', '.svn', '.htaccess', '.buildpath','.project'
                if (!in_array($file, array('.', '..', '.git', '.gitignore', '.svn', '.hataccess', '.buildpath', '.project'))) {
                    $cur_path = $dir . '/' . $file;
                    if (is_dir($cur_path)) {
                        $ret['dirs'][] = $cur_path;
                        $all && self::scanDir($cur_path, $all, $ret);
                    } else {
                        $ret['files'][] = $cur_path;
                    }
                }
            }
            closedir($handle);
        }
        return $ret;
    }

    /**
     * 判断字符串是utf-8 还是gb2312
     * @param unknown $str
     * @param string $default
     * @return string
     */
    public static function utf8_gb2312($str, $default = 'gb2312')
    {
        $str = preg_replace("/[\x01-\x7F]+/", "", $str);
        $preg = array(
            'gb2312' => "/^([\xA1-\xF7][\xA0-\xFE])+$/",//正则判断是否是gb2312
            'utf-8' => "/^[\x{4E00}-\x{9FA5}]+$/u",//正则判断是否是汉字(utf8编码的条件了)，这个范围实际上已经包含了繁体中文字了
        );
        if ($default) {
            $option = "utf-8";
        } else {
            $option = "gb2312";
        }
        if (preg_match($preg[$default], $str)) {
            return $option;
        }
        $str = @iconv($default, $option, $str);
        //不能转成$option 说明原来的不是default
        if (empty($str)) {
            return $option;
        }
        return $default;
    }

    /**
     * utf-8和gb2312自动转化
     * @param unknown $string
     * @param string $outEncoding
     * @return unknown|string
     */
    public static function safeEncoding($string, $outEncoding = "UTF-8")
    {
        $encoding = "UTF-8";
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string{$i}) < 128) {
                continue;
            }
            if ((ord($string{$i}) & 224) == 224) {
                //第一个字节判断通过
                $char = $string{++$i};
                if ((ord($char) & 128) == 128) {
                    //第二个字节判断通过
                    $char = $string{++$i};
                    if ((ord($char) & 128) == 128) {
                        $encoding = "UTF-8";
                        break;
                    }
                }
            }
            if (ord($string{$i} & 192) == 192) {
                //第一字节判断通过
                $char = $string{++$i};
                if ((ord($char) & 128) == 128) {
                    //第二个字节判断通过
                    $encoding = "GB2312";
                    break;
                }
            }
        }
        if (strtoupper($outEncoding) == $encoding)
            return $string;
        else
            return @iconv($encoding, $outEncoding, $string);
    }

    /**
     * 返回二维数组中某个键名的所有值
     * @param input $array
     * @param string $key
     * @return array
     */
    public static function array_key_values($array = array(), $key = '')
    {
        $ret = array();
        foreach ((array)$array as $k => $value) {
            $ret[$k] = $value[$key];
        }
        return $ret;
    }

    /**
     * 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）
     * @param string $file 文件/目录
     * @return boolean
     */
    public static function isWriteAble($file)
    {
        if (is_dir($file)) {
            $dir = $file;
            if ($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $write_able = 1;
            } else {
                $write_able = 0;
            }
        } else {
            if ($fp = @fopen($file, 'a+')) {
                @fclose($fp);
                $write_able = 1;
            } else {
                $write_able = 0;
            }
        }
        return $write_able;
    }

    /**
     * 格式化单位
     */
    public static function byteFormat($size, $dec = 2)
    {
        $a = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $pos = 0;
        while ($size > 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $a[$pos];
    }

    /**
     * 下拉框，单选按钮 自动选择
     *
     * @param $string 输入字符
     * @param $param  条件
     * @param $type   类型
     * selected checked
     * @return string
     */
    public static function selected($string, $param, $type = "select")
    {
        $true = false;
        if (is_array($param)) {
            $true = in_array($string, $param);
        } elseif ($string == $param) {
            $true = true;
        }
        $return = '';
        if ($true) {
            $return = $type == 'select' ? 'selected = "selected"' : 'checked="checked"';
        }
        echo $return;
    }

    /**
     * 下载远程图片
     * @param string $url 图片的绝对url
     * @param string $filePath 文件的完整路径（例如/www/images/test） ，此函数会自动根据图片url和http头信息确定图片的后缀名
     * @param string $fileName 要保存的文件名(不含扩展名)
     * @return mixed 下载成功返回一个描述图片信息的数组，下载失败则返回false
     */
    public static function downloadImage($url, $filePath, $fileName)
    {
        //服务器返回的头信息
        $responseHeaders = array();
        //原始图片名
        $originalFileName = '';
        //图片的后缀名
        $ext = '';
        $ch = curl_init($url);
        //设置curl_exec返回的值包含Http头
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //设置curl_exec返回的值包含Http内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置抓取跳转(http 301,302)后的页面
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //设置最多的http重定向数量
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

        //服务器返回的数据(包括http的头信息和内容)
        $html = curl_exec($ch);
        //获取此次抓取的相关信息
        $httpInfo = curl_getinfo($ch);
        if ($html !== false) {
            //分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2
            $httpArr = explode("\r\n\r\n", $html, 2 + $httpInfo['redirect_count']);
            //倒数第二段是服务器最后一次response的http头
            $header = $httpArr[count($httpArr) - 2];
            //倒数第一段是服务器最后一次response的内容
            $body = $httpArr[count($httpArr) - 1];
            $header .= "\r\n";

            //获取租后一次response的header的信息
            preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
            if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[2])) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    if (array_key_exists($i, $matches[2])) {
                        $responseHeaders[$matches[1][$i]] = $matches[2][$i];
                    }
                }
            }
            //获取图片后缀名
            if (0 < preg_match('{(?:[^\/\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $url, $matches)) {
                $originalFileName = $matches[0];
                $ext = $matches[1];
            } else {
                if (array_key_exists('Content-Type', $responseHeaders)) {
                    if (0 < preg_match('\'{image/(\w+)}i\'', $responseHeaders['Content-Type'], $extMatches)) {
                        $ext = $extMatches[1];
                    }
                }
            }
            //保存文件
            if (!empty($ext)) {
                //如果目录不存在,则先创建目录
                if (!is_dir($filePath)) {
                    mkdir($filePath, 0777, true);
                }
                $filePath = '/' . $fileName . ".$ext";
                $localFile = fopen($filePath,'w');
                if (false !== $localFile) {
                    if (false !== fwrite($localFile, $body)) {
                        fclose($localFile);
                        $sizeInfo = getimagesize($filePath);
                        return array('filePath' => realpath($filePath), 'width' => $sizeInfo[0], 'height' => $sizeInfo[1], 'originalFileName' => $originalFileName, 'fileName' => pathinfo($filePath, PATHINFO_BASENAME));
                    }
                }
            }
        }
        return false;
    }

    /**
     * 查找ip是否在某个段位里面
     * @param string $ip 要查询的ip
     * @param $arrIP     禁止的ip
     * @return boolean
     */
    public static function ipAccess($ip = '0.0.0.0', $arrIp = array())
    {
        $access = true;
        $arr_cur_ip = array();
        $ip && $arr_cur_ip = explode('.',$ip);
        foreach ((array)$arrIp as $key => $value) {
            if($value == '*.*.*.*'){
                $access = false; //禁止所有
                break;
            }
            $tmp_arr = explode('.',$value);
            if(($arr_cur_ip[0] == $tmp_arr[0]) && ($arr_cur_ip[1] == $tmp_arr[1])){
                //前两段相同
                if(($arr_cur_ip[2] == $tmp_arr[2]) || $tmp_arr[2] == '*'){
                    //第三段为*或者相同
                    if(($arr_cur_ip[3] == $tmp_arr[3]) || $tmp_arr[3] == '*'){
                        //第四段为*或者相同
                        $access = false; //在禁止ip列,则禁止访问
                        break;
                    }
                }
            }
        }
        return $access;
    }
    /**
     * @param string $string 原文或者密文
     * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
     * @param string $key 密钥
     * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
     * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
     *
     * @example
     *
     * $a = authcode('abc', 'ENCODE', 'key');
     * $b = authcode($a, 'DECODE', 'key');  // $b(abc)
     *
     * $a = authcode('abc', 'ENCODE', 'key', 3600);
     * $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
     */
    public static function authCode($string,$operation='DECODE',$key='',$expiry=3600)
    {
        // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        // 当此值为 0 时，则不产生随机密钥
        $ckey_length = 4;
        $key = md5($key ? $key : 'key');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
    /**
     * 取得输入目录所包含的所有目录和文件
     * 以关联数组形式返回
     * 目录路径 $dir
     */
    public static function deepScanDir($dir)
    {
        $fileArr = array();
        $dirArr = array();
        $dir = rtrim($dir.'//');
        if(is_dir($dir)){
            $dirHandle = opendir($dir);
            while(false !== ($fileName = readdir($dirHandle))){
                $subFile = $dir . DIRECTORY_SEPARATOR . $fileName;
                if(is_file($subFile)){
                    $fileArr[] = $subFile;
                }elseif(is_dir($subFile) && str_replace('.','',$fileName) != ''){
                    $dirArr[] = $subFile;
                    $arr = self::deepScanDir($subFile);
                    $dirArr = array_merge($dirArr,$arr['dir']);
                    $fileArr = array_merge($fileArr, $arr['file']);
                }
            }
            closedir($dirHandle);
        }
        return array('dir' => $dirArr, 'file' => $fileArr);
    }

    /**
     * 取得输入目录所包含的所有文件
     * 以数组形式返回
     * @param $dir
     */
    public static function getDirFiles($dir)
    {
        if(is_file($dir)){
            return array($dir);
        }
        $files = array();
        if(is_dir($dir) && ($dir_handle = opendir($dir)) !== false){
            $ds = DIRECTORY_SEPARATOR;
            while(($fileName = readdir($dir_handle))!==false){
                if($fileName == '.' || $fileName == '..'){
                    continue;
                }
                $fileType = filetype($dir.$ds.$fileName);
                if($fileType == 'dir'){
                    $files = array_merge($files,self::getDirFiles($dir.$ds.$fileName));
                }elseif($fileType == 'file'){
                    $files[] = $dir.$ds.$fileName;
                }
            }
            closedir($dir_handle);
        }
        return $files;
    }

    /**
     * 删除文件夹及其文件夹下的所有文件
     * @param $dir
     * @return bool
     */
    public static function delDir($dir)
    {
        if(!is_dir($dir)) return false;
        //先删除目录下的文件
        $handle = opendir($dir);
        while($file = readdir($handle)){
            if($file != '.' && $file != '..'){
                $filePath = $dir.DIRECTORY_SEPARATOR.$file;
                if(!is_dir($filePath)){
                    unlink($filePath);
                }else{
                    self::delDir($filePath);
                }
            }
        }
        closedir($handle);
        if(rmdir($dir)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 页面跳转
     * @param $url
     */
    public static function headerUrl($url)
    {
        echo "<script type='text/javascript'>location.href='{$url}';</script>";
        exit;
    }
    
    /**
     * JS弹窗并且跳转
     * @param $message
     * @param $url
     */
    public static function alertLocation($message,$url)
    {
        echo "<script type='text/javascript'>alert('$message');location.href='$url';</script>";
        exit;
    }

    /**
     * JS弹窗返回
     * @param $message
     */
    public static function alertBack($message)
    {
        echo "<script type='text/javascript'>alert('$message');history.back();</script>";
        exit;
    }

    /**
     * JS弹窗关闭
     * @param $message
     */
    public static function alertClose($message)
    {
        echo "<script type='text/javascript'>alert('$message');close();</script>";
        exit;
    }

    /**
     * JS弹窗
     * @param $message
     */
    public static function alert($message)
    {
        echo "<script type='text/javascript'>alert('$message');</script>";
        exit;
    }

    /**
     * html过滤
     * @param array|object $data
     * @return mixed
     */
    public static function htmlString($data)
    {
        $string = '';
        if(is_array($data)){
            foreach ($data as $key => $value) {
                $string[$key] = self::htmlString($value);
            }
        }elseif(is_object($data)){
            foreach ($data as $key => $value) {
                $string->$key = self::htmlString($value);
            }
        }else{
            $string = htmlspecialchars($data);
        }
        return $string;
    }

    /**
     * 数据库输入过滤
     * @param string $data
     * @return string
     */
    public static function mysqlString($data)
    {
        $data = trim($data);
        return !get_magic_quotes_gpc() ? addslashes($data) : $data;
    }

    /**
     * 清理session
     */
    public static function clearSession()
    {
        if(session_start()){
            session_destroy();
        }
    }

    /**
     *获取真实IP
     * @return null|string
     */
    public static function getRealIp()
    {
        static $realIp = null;
        if($realIp !== null) return $realIp;
        if(isset($_SERVER)){
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $arr = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if($ip != 'unknown'){
                        $realIp = $ip;
                        break;
                    }
                }
            }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
                $realIp = $_SERVER['HTTP_CLIENT_IP'];
            }else{
                $realIp = '0.0.0.0';
            }
        }else{
            if(getenv('HTTP_X_FORWARDED_FOR')){
                $realIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }elseif(getenv('HTTP_CLIENT_IP')){
                $realIp = $_SERVER['HTTP_CLIENT_IP'];
            }else{
                $realIp = getenv('REMOTE_ADDR');
            }
        }
        preg_match('/[\d\.]{7,15}/',$realIp,$onlineIp);
        $realIp = !empty($onlineIp[0]) ? $onlineIp[0] : '0.0.0.0';
        return $realIp;
    }

    /**
     * 图片等比例缩放
     * @param resource $im 图片资源
     * @param int $maxWidth 最大高度
     * @param int $maxHeight 最大宽度
     * @param string $name 生成文件名称
     * @param string $fileType 生成文件类型
     */
    public static function resizeImage($im, $maxWidth, $maxHeight, $name, $fileType)
    {
        //图像宽度
        $pic_width = imagesx($im);
        //图像高度
        $pic_height = imagesy($im);
        $resize_width_tag = $resize_height_tag = false;
        if( ($maxWidth && $pic_width > $maxWidth) || ($maxHeight && $pic_height > $maxHeight)){
            $width_ratio = $height_ratio = $ratio = '';
            if($maxWidth && $pic_width > $maxWidth){
                $width_ratio = $maxWidth / $pic_width;
                $resize_width_tag = true;
            }
            if($maxHeight && $pic_height > $maxHeight){
                $height_ratio = $maxHeight / $pic_height;
                $resize_height_tag = true;
            }
            if($resize_width_tag && $resize_height_tag){
                if($width_ratio < $height_ratio){
                    $ratio = $width_ratio;
                }else{
                    $ratio = $height_ratio;
                }
            }
            if($resize_width_tag && !$resize_height_tag) $ratio = $width_ratio;
            if($resize_height_tag && !$resize_width_tag) $ratio = $height_ratio;
            $new_width = $pic_width * $ratio;
            $new_height = $pic_height * $ratio;
            if(function_exists("imagecopyresampled")){
                $new_im = imagecreatetruecolor($new_width,$new_height);
                imagecopyresampled($new_im,$im,0,0,0,0,$new_width,$new_height,$pic_width,$pic_height);
            }else{
                $new_im = imagecreatetruecolor($new_width,$new_height);
                imagecopyresized($new_im,$im,0,0,0,0,$new_width,$new_height,$pic_width,$pic_height);
            }
            $name = $name.$fileType;
            imagejpeg($new_im,$name);
            imagedestroy($new_im);
        }else{
            $name = $name.$fileType;
            imagejpeg($im,$name);
        }
    }

    /**
     * 下载文件
     * @param $filePath
     */
    public static function downFile($filePath)
    {
        $filePath = iconv('utf-8','gb2312',$filePath);
        if(!file_exists($filePath)){
            exit('文件不存在!');
        }
        $fileName = basename($filePath);
        $fileSize = filesize($filePath);
        $fp = fopen($filePath,'r');
        header("Content-type:application/octet-stream");
        header("Accept-Range:bytes");
        header("Accept-Length:{$fileSize}");
        header("Content-Disposition: attachment;filename={$fileName}");
        $buffer = 1024;
        $fileCount = 0;
        while(!feof($fp) && ($fileSize-$fileCount > 0)){
            $fileData = fread($fp,$buffer);
            $fileCount += $buffer;
            echo $fileData;
        }
        fclose($fp);
    }

    
/**
 * 通过Key排序
 * @param $array
 * @param $field
 * @param bool $desc
 * @return mixed
 */
function sortArrByField(&$array, $field, $desc = true)
{
    $fieldArr = array();
    foreach ($array as $k => $v) {
        $fieldArr[$k] = $v[$field];
    }
    $sort = $desc == false ? SORT_ASC : SORT_DESC;
    array_multisort($fieldArr, $sort, $array);
    return $array;
}
}