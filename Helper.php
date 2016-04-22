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
     *切割utf-8格式的字符串(一个汉字或者字符占一个字节)
     * @param $string
     * @param $length
     * @param string $etc
     */
    public static function truncateUtf8String($string, $length, $etc = '...')
    {
        $result = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strlen = strlen($string);
        for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
            $number = strops(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0');
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
        if(preg_match($preg[$default],$str)){
            return $option;
        }
        $str = @iconv($default,$option,$str);
        //不能转成$option 说明原来的不是default
        if(empty($str)){
            return $option;
        }
        return $default;
    }
}