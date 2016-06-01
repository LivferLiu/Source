<?php

/**
 *
 * 開發PHP的朋友都知道,其實最擔心的就是程序中出現一些異常或錯誤,
 * 這些狀況如果輸出到用戶的螢幕會把用戶給嚇壞,
 * 甚至為此丟了工作,如果不輸出到螢幕就得想辦法記錄到日誌中,
 * 但是似乎不是每個人都有查看錯誤日誌的習慣,
 * 爲了解決這個尷尬的問題,所以我寫了這段代碼,
 * 其用意就是當我們寫的php程式出錯的時候把錯誤內容捕捉出來然後發到我們的email內.
 */


Define('SYS_DEBUG', false);
IF (SYS_DEBUG) {
    ini_set('display_errors', 'on');
    Error_reporting(E_ALL);//上線后使用該設定Error_reporting(E_ERROR | E_WARNING | E_PARSE);
} Else {
    ini_set('display_errors', 'off');
    Error_reporting(0);
}

//錯誤捕捉
Register_shutdown_function('Fun::Error');

Class Fun
{

    /**
     * 通用出錯處理
     * 参数:
     * 要輸出的內容,是否終止執行程序
     * 說明:
     * 有傳值時該函式可以用來輸出自定義的錯誤內容
     * 另外還可以配合Register_shutdown_function實現自動抓取錯誤內容,並將抓取的錯誤內容發送到Email內
     * Register_shutdown_function的機制是程序執行完畢或中途出錯時調用函數
     * 如果是自動抓取錯誤時被調用,則會取得最後一次出錯的內容,如果發現沒有錯誤內容則跳出
     * 返回:
     * 內容會被直接輸出至螢幕或Email內
     * 用法:
     * Fun::Error('錯誤內容');
     * Fun::Error('錯誤內容',False);
     * /**/
    Public Static Function Error($M = '', $E = True)
    {
        $ErrTpl = '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body><table cellspacing="0" cellpadding="0" border="0"><tr><td style="padding:5px;background-color:#F57900;font-size:13px;border:1px solid #444;color:#222;">{$M}</td></tr></table>';

        $M = Trim($M);
        IF ($M != '') {//手工調用
            $M = ' <b>注意:</b> ' . $M;
            Echo Strtr($ErrTpl, Array('{$M}' => $M));
            unSet($ErrTpl);
            IF ($E === True) {
                Die();
            }
            Return;
        } Else {//程式執行完畢自動抓取錯誤時調用
            $M = error_get_last();//取得最後產生的錯誤
            IF (!Is_array($M) Or Count($M) < 4) {
                Unset($M);
                Return;
            }
            IF (!File_Exists($M['file'])) {
                Unset($M);
                Return;
            }

//取得5行出錯關鍵代碼,如果取不到內容,說明出錯檔案不存在
            $E = Array_slice(File($M['file']), ($M['line'] - 4), 5);
            IF (!Is_array($E)) {
                Unset($M, $E);
                Return;
            }

            $E['M'] = '';
            For ($i = 0; $i < 5; $i++) {
                $E[$i] = isSet($E[$i]) ? $E[$i] : '';
                $E['M'] .= '&nbsp;&nbsp;';
                $E['M'] .= ($i == 3) ? '<b>' . (($M['line'] - 3) + ($i + 1)) . '</b>' : (($M['line'] - 3) + ($i + 1));
                $E['M'] .= ': ' . Htmlspecialchars($E[$i], ENT_QUOTES, 'UTF-8') . '<br>';
            }
            $E =& $E['M'];

            $M = '<b>自動捕捉到有錯誤產生!</b><br><br><b>錯誤描述:</b><br>&nbsp;&nbsp;<b>' . $M['file'] . '</b>的第<b>' . $M['line'] . '</b>行出現了類型為<b>' . $M['type'] . '</b>的錯誤:<br>&nbsp;&nbsp;' . $M['message'] . '<br><br><b>關鍵代碼:</b><br>' . $E . '<br>' . self::now('Y-m-d H:i:s', time()) . '<br>';

            $M = Strtr($ErrTpl, Array('{$M}' => $M));
            unSet($ErrTpl);

            $G = seft::getG('SYS', 'config');
            IF (!self::Mail2($G['Spe'], '警告: ' . $G['Tit'] . ' 出現 PHP 程式錯誤!', $M) And SYS_DEBUG === True) {
                throw new Exception('警告: ' . $G['Tit'] . ' 出現 PHP 程式錯誤!<br><br>' . $M);
            }
            IF (SYS_DEBUG) {
                Echo $M;
            }
            unSet($E, $M, $G);
            Die();
        }
    }

    /**
     * 发送電郵
     * 参数:
     * 收件人,郵件標題(不可有換行符),郵件內容(行與行之間必須用\n分隔,每行不可超過70個字符)
     * 說明:
     * 調用PHP內置函式Mail發送電郵
     * 返回:
     * 返回布爾值
     * 用法:
     * $IsSend=Fun::Mail2($email,$tit,$msg);
     * /**/
    Public Static Function Mail2($to, $tit, $msg)
    {
        IF (Filter_var($to, FILTER_VALIDATE_EMAIL) == '') {
            throw new Exception('電郵地址錯誤!');
        }

        $tit = '=?UTF-8?B?' . Base64_Encode($tit) . '?=';
        $msg = str_replace("\n.", "\n..", $msg);     //Windows如果在一行开头发现一个句号则会被删掉,要避免此问题将单个句号替换成两个句号

        Return Mail($to, $tit, $msg, 'From:' . seft::getG('config/SYS/Mal') . "\n" . 'Content-Type:text/html;charset=utf-8');
    }


}