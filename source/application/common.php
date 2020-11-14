<?php

// 应用公共函数库文件

use think\Request;
use think\Log;
use app\common\model\Wxapp as WxappModel;

/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo '<pre>' . print_r($content, true);
    $is_die && die();
}

/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/**
 * 生成密码hash值
 * @param $password
 * @return string
 */
function yoshop_hash($password)
{
    return md5(md5($password) . 'yoshop_salt_SmTRx');
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url()
{
    static $baseUrl = '';
    if (empty($baseUrl)) {
        $request = Request::instance();
        $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
        $baseUrl = $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
    }
    return $baseUrl;
}

/**
 * 写入日志 (废弃)
 * @param string|array $values
 * @param string $dir
 * @return bool|int
 */
//function write_log($values, $dir)
//{
//    if (is_array($values))
//        $values = print_r($values, true);
//    // 日志内容
//    $content = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $values . PHP_EOL . PHP_EOL;
//    try {
//        // 文件路径
//        $filePath = $dir . '/logs/';
//        // 路径不存在则创建
//        !is_dir($filePath) && mkdir($filePath, 0755, true);
//        // 写入文件
//        return file_put_contents($filePath . date('Ymd') . '.log', $content, FILE_APPEND);
//    } catch (\Exception $e) {
//        return false;
//    }
//}

/**
 * 写入日志 (使用tp自带驱动记录到runtime目录中)
 * @param $value
 * @param string $type
 */
function log_write($value, $type = 'yoshop-info')
{
    $msg = is_string($value) ? $value : var_export($value, true);
    Log::record($msg, $type);
}

/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if (!function_exists('array_column')) {
    /**
     * array_column 兼容低版本php
     * (PHP < 5.5.0)
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 多维数组合并
 * @param $array1
 * @param $array2
 * @return array
 */
function array_merge_multiple($array1, $array2)
{
    $merge = $array1 + $array2;
    $data = [];
    foreach ($merge as $key => $val) {
        if (
            isset($array1[$key])
            && is_array($array1[$key])
            && isset($array2[$key])
            && is_array($array2[$key])
        ) {
            $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
        } else {
            $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
        }
    }
    return $data;
}

/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param bool $desc
 * @return mixed
 */
function array_sort($arr, $keys, $desc = false)
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($desc) {
        arsort($key_value);
    } else {
        asort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 数据导出到excel(csv文件)
 * @param $fileName
 * @param array $tileArray
 * @param array $dataArray
 */
function export_excel($fileName, $tileArray = [], $dataArray = [])
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    ob_end_clean();
    ob_start();
    header("Content-Type: text/csv");
    header("Content-Disposition:filename=" . $fileName);
    $fp = fopen('php://output', 'w');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));// 转码 防止乱码(比如微信昵称)
    fputcsv($fp, $tileArray);
    $index = 0;
    foreach ($dataArray as $item) {
        if ($index == 1000) {
            $index = 0;
            ob_flush();
            flush();
        }
        $index++;
        fputcsv($fp, $item);
    }
    ob_flush();
    flush();
    ob_end_clean();
}

/**
 * 隐藏敏感字符
 * @param $value
 * @return string
 */
function substr_cut($value)
{
    $strlen = mb_strlen($value, 'utf-8');
    if ($strlen <= 1) return $value;
    $firstStr = mb_substr($value, 0, 1, 'utf-8');
    $lastStr = mb_substr($value, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', $strlen - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}

/**
 * 获取当前系统版本号
 * @return mixed|null
 * @throws Exception
 */
function get_version()
{
    static $version = null;
    if ($version) {
        return $version;
    }
    $file = dirname(ROOT_PATH) . '/version.json';
    if (!file_exists($file)) {
        throw new Exception('version.json not found');
    }
    $version = json_decode(file_get_contents($file), true);
    if (!is_array($version)) {
        throw new Exception('version cannot be decoded');
    }
    return $version['version'];
}

/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

/**
 * 时间戳转换日期
 * @param $timeStamp
 * @return false|string
 */
function format_time($timeStamp)
{
    return date('Y-m-d H:i:s', $timeStamp);
}

/**
 * 左侧填充0
 * @param $value
 * @param int $padLength
 * @return string
 */
function pad_left($value, $padLength = 2)
{
    return \str_pad($value, $padLength, "0", STR_PAD_LEFT);
}

/**
 * 过滤emoji表情
 * @param $text
 * @return null|string|string[]
 */
function filter_emoji($text)
{
    // 此处的preg_replace用于过滤emoji表情
    // 如需支持emoji表情, 需将mysql的编码改为utf8mb4
    return preg_replace('/[\xf0-\xf7].{3}/', '', $text);
}

/**
 * 根据指定长度截取字符串
 * @param $str
 * @param int $length
 * @return bool|string
 */
function str_substr($str, $length = 30)
{
    if (strlen($str) > $length) {
        $str = mb_substr($str, 0, $length, 'utf-8');
    }
    return $str;
}

/**
 * 个性化日期显示
 * @static
 * @access public
 * @param datetime $times 日期
 * @return string 返回大致日期
 * @example 示例 ueTime('')
 */
function ue_time($times) {
    if ($times == '' || $times == 0) {
        return false;
    }
    //完整时间戳
    $strtotime = is_int($times) ? $times : strtotime($times);
    $times_day = date('Y-m-d', $strtotime);
    $times_day_strtotime = strtotime($times_day);

    //今天
    $nowdate_str = strtotime(date('Y-m-d'));

    //精确的时间间隔(秒)
    $interval = time() - $strtotime;

    //今天的
    if ($times_day_strtotime == $nowdate_str) {

        //小于一分钟
        if ($interval < 60) {
            $pct = sprintf("%d秒前", $interval);
        }
        //小于1小时
        elseif ($interval < 3600) {
            $pct = sprintf("%d分钟前", ceil($interval / 60));
        } else {
            $pct = sprintf("%d小时前", floor($interval / 3600));
        }
    }
    //昨天的
    elseif ($times_day_strtotime == strtotime(date('Y-m-d', strtotime('-1 days')))) {
        $pct = '昨天' . date('H:i', $strtotime);
    }
    //前天的
    elseif ($times_day_strtotime == strtotime(date('Y-m-d', strtotime('-2 days')))) {
        $pct = '前天' . date('H:i', $strtotime);
    }
    //一个月以内
    elseif ($interval < (3600 * 24 * 30)) {
        $pct = date('m月d日', $strtotime);
    }
    //一年以内
    elseif ($interval < (3600 * 24 * 365)) {
        $pct = date('m月d日', $strtotime);
    }
    //一年以上
    else {
        $pct = date('Y年m月d日', $strtotime);
    }
    return $pct;
}

/**
 * 到期时间显示
 * @static
 * @access public
 * @param datetime $times 日期
 * @return string 返回大致日期
 * @example 示例 ueTime('')
 */
function expire_time($times) {
    $time = round(($times - time())/24/60/60 - 1);

    // 已过期
    if( $times < time()) {
        return '<span style="color: #F37B1D;">(已经过期)';

    }
    // 小于一个月开始提醒
    if( $time < 30 ){
        return '<span style="color: #5eb95e;">(' . $time . '天后到期)';
    }
}

/**
 * 获取站内二级域名
 * @static
 * @access public
 * @example 示例 10001
 */
function domain_prefix(){

    $domain = $_SERVER['HTTP_HOST'];
    $domain = explode('.',$domain);
    if( count($domain) == 2 ){
        return $_SERVER['HTTP_HOST'];
    }
    // 顶级域名
    $top_domain = $domain[count($domain)-2] .'.'. $domain[count($domain)-1];
    // 二级域名前缀
    $second_prefix = $domain[count($domain)-3];
    // 从域名拿到wxapp_id
    $wxapp_id = substr($second_prefix,1,strlen($second_prefix));
    // 站内域名绑定
    if($top_domain == 'wsdns.cn' && substr($second_prefix,0,1) == 's' && is_numeric($wxapp_id)){
        return $wxapp_id;
    }
    // 外部域名
    return $_SERVER['HTTP_HOST'];
}

/**
 * 整理字符串参数放在网址上
 * 如：appid/10155/time/1572499627/sign/f18bd02caeadcdea509d45060f03a705
 **/
function str_sign_url($data){
    $paramurl = http_build_query($data);
    $paramurl = str_replace('=','/',$paramurl);
    $paramurl = str_replace('&','/',$paramurl);
    return $paramurl;
}


/**
 * 加密URL地址
 * 加密后：aHR0cDovL3d3dy53c2Rucy5jbi9pbmRleC5waHA%2Fcz0vaXRlbXMvZGV0YWlsL2l0ZW1zX2lkLzEyMQ%3D%3D
 **/
function url_encryption($url){
    $path = base64_encode($url);
    return Urlencode($path);
}


/**
 * 解密URL地址
 * 解码后：http://www.wsdns.cn/index.php?s=/items/detail/items_id/121
 **/
function url_decrypt($url){
    $path = base64_decode(rawurldecode($url));
    return Urldecode($path);
}

