<?php

// 应用公共函数库文件
error_reporting(E_ERROR | E_WARNING | E_PARSE);
use app\store\service\Auth;

/**
 * 验证指定url是否有访问权限
 * @param string|array $url
 * @param bool $strict 严格模式
 * @return bool
 */
function checkPrivilege($url, $strict = true)
{
    try {
        return Auth::getInstance()->checkPrivilege($url, $strict);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * 截取文章摘要
 * @return bool
 */
function get_article_desc($post_excerpt){
    $post_excerpt = strip_tags(htmlspecialchars_decode($post_excerpt));
    $post_excerpt = trim($post_excerpt);
    $patternArr = array('/\s/','/ /');
    $replaceArr = array('','');
    $post_excerpt = preg_replace($patternArr,$replaceArr,$post_excerpt);
    $value = mb_strcut($post_excerpt,0,240,'utf-8');
    return $value;
}
/**
 * 验证域名是否解析
 * @return bool
 */
function httpcode($url){

    $ch = curl_init();

    $timeout = 3;

    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);

    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

    curl_setopt($ch, CURLOPT_HEADER, 1);

    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    curl_setopt($ch,CURLOPT_URL,$url);

    curl_exec($ch);

    return $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

    curl_close($ch);

}

function getUser($user_id){
    return db('user')->find($user_id);
}