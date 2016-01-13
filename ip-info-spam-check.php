<?php
require_once('Core.php');
$smarty = new Core;

/**
 * 如果使用者有輸入要查詢的IP，就使用使用者輸入的IP進行查詢。
 * 如果沒有輸入，就檢查是否有啟用CloudFlare的服務，然後抓取使用者目前的IP。
 */
if(isset($_POST['ip']) && !empty(trim($_POST['ip'])))
{
    $ipAddress = $_POST['ip'];
}
else
{
    $ipAddress = (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : getenv('REMOTE_ADDR');
    if($ipAddress == '::1') $ipAddress = '127.0.0.1';
}

/**
 * 指派要呈現的樣板變數和資料
 */
$smarty->assign(array(
            'title'     => 'HSDN - IP Information and Spam Check',
            'subTitle'  => 'IP Information and Spam Check',
            'ipAddr'    => $ipAddress,
            'ipGeo'     => _ip2geo($ipAddress),
            'spamCheck' => _ipSpamCheck($ipAddress)
        ));


/**
 * 指定要呈現資料的樣板名稱
 */
$smarty->display('main.html');


/**
 * 檢查IP是否被列為Spam來源
 * @param  String $ipAddress IP位址
 * @return Array             檢查結果
 */
function _ipSpamCheck($ipAddress)
{
    $revip = implode(".", array_reverse(explode(".", $ipAddress, 4), false));

    /**
     * 使用的SPAM檢查服務
     * Abuseat: http://www.abuseat.org/faq.html
     * Barracuda: http://www.barracudacentral.org/rbl/how-to-use
     * Sorbs: http://www.sorbs.net/general/using.shtml
     * Spamcop: https://www.spamcop.net/fom-serve/cache/291.html
     * Spamhaus: https://www.spamhaus.org/faq/section/DNSBL%2520Usage#202
     * Surbl: http://www.surbl.org/lists
     * Uribl: http://uribl.com/about.shtml#info
     */

    $blacklists = array(
        array('Abuseat', 'cbl.abuseat.org'),
        array('Barracuda', 'b.barracudacentral.org'),
        array('Sorbs', 'dnsbl.sorbs.net'),
        array('Spamcop', 'bl.spamcop.net'),
        array('Spamhaus', 'zen.spamhaus.org'),
        array('Surbl', 'multi.surbl.org'),
        array('Uribl', 'black.uribl.com')
    );

    $checkSerial=0;
    foreach($blacklists as $key => $val) {
        $checkIP = $revip.'.'.$val[1];

        $spamCheckRes[$checkSerial]['serverName'] = $val[0];
        $records = dns_get_record($checkIP, DNS_A);

        if(count($records) > 0)
        {
            $spamCheckRes[$checkSerial]['result'] = true;
        }
        else
        {
            $spamCheckRes[$checkSerial]['result'] = false;
        }

        $checkSerial++;
    }

    return $spamCheckRes;
}

/**
 * 獲取IP地理資訊
 * @param  String $ipAddress IP位址
 * @return Array             IP地理資訊
 */
function _ip2geo($ipAddress)
{
    $url = "http://freegeoip.net/json/$ipAddress";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    echo curl_error($ch);
    curl_close($ch);

    return json_decode($resp);
}