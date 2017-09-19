<?php
$file = 'a.txt';
// $file = '20170110对账单.txt';
$file = '1.xlsx';
$fp = fopen($file, "r+");
$pos = -1;
$eof = '';
// fseek($fp, -1, SEEK_END);
        while($eof != "\n"){  
            if(fseek($fp, $pos, SEEK_END)==0){    //fseek成功返回0，失败返回-1  
                $eof = fgetc($fp);  
                $pos--;  
                // echo $pos;
            }else{                               //当到达第一行，行首时，设置$pos失败  
                fseek($fp,0,SEEK_SET);  
                $head = true;                   //到达文件头部，开关打开  
                break;  
            }  
              
        }
$buffer = fgets($fp);
var_dump($buffer);
fclose($fp);
exit;
function tail($fp, $n, $base = 5)
{
 $pos = $n+1;
 $lines = array();
 while (count($lines) <= $n)
 {
	 try
	 {
	  fseek($fp, -$pos, SEEK_END);
	 }
	 catch (Exception $e)
	 {
	  fseek(0);
	  break;
	 }
	 $pos *= $base;
	 while (!feof($fp))
	 {
	 	// echo fgets($fp)."<br />";
	  array_unshift($lines, fgets($fp));
	 }
 }
 return array_slice($lines, 0, $n);
 // return $lines;
}


function tails($file,$num){  
    $fp = fopen($file,"r");  
    $pos = -2;  
    $eof = "";  
    $head = false;   //当总行数小于Num时，判断是否到第一行了  
    $lines = array();  
    while($num>0){  
        while($eof != "\n"){  
            if(fseek($fp, $pos, SEEK_END)==0){    //fseek成功返回0，失败返回-1  
                $eof = fgetc($fp);  
                $pos--;  
                // echo $pos;
            }else{                               //当到达第一行，行首时，设置$pos失败  
                fseek($fp,0,SEEK_SET);  
                $head = true;                   //到达文件头部，开关打开  
                break;  
            }  
              
        }  
        array_unshift($lines,fgets($fp));
        if($head){ break; }                 //这一句，只能放上一句后，因为到文件头后，把第一行读取出来再跳出整个循环  
        $eof = "";  
        $num--;  
    }  
    fclose($fp);  
    return $lines;  
} 
//var_dump();
// $content = tail(fopen($file, "r+"), 1000);
$content = tails($file,1000);
print_r($content);
?>
