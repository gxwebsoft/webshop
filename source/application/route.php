<?php
use app\common\model\website\Domain as DomainModel;
// 设限制URL兼容模式
\think\Url::root('/index.php?s=');
\think\Route::domain('m.wsdns.cn','mobile');
/**
 * 域名绑定方法
 * 二级域名绑定 s10001.wsdns.cn
 * 手机版域名绑定 m.s10001.wsdns.cn
 * @throws BaseException
 * @throws \think\exception\DbException
 */
$domain = domain_prefix();
// 免费域名
if(is_numeric($domain)){
    $wxapp_id = $domain;
    if(request()->isMobile()){
        // 手机版
        \think\Route::domain('s' . $wxapp_id . '.wsdns.cn', 'mobile?wxapp_id=' . $wxapp_id);
    }else{
        // PC版
        \think\Route::domain('s' . $wxapp_id . '.wsdns.cn', 'website?wxapp_id=' . $wxapp_id);
    }
}
// 绑定域名
if(!empty($domain)){
    // 查询用户绑定的域名
    $data = DomainModel::get(['domain'=>$domain]);
    if($data){
        if(request()->isMobile()){
            // 手机版
            \think\Route::domain($domain, 'mobile?wxapp_id=' . $data['wxapp_id']);
        }else{
            // PC版
            \think\Route::domain($domain, $data['module'].'?wxapp_id=' . $data['wxapp_id']);
        }
    }
}




return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
];
