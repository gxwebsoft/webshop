<?php

namespace app\store\controller;

use app\store\model\store\User as StoreUser;
use think\Session;
use app\store\model\store\PostData as PostData;

/**
 * 商户认证
 * Class Passport
 * @package app\store\controller
 */
class Passport extends Controller
{

    /**
     * 商户后台登录
     * @return array|bool|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {

        // 一键登录（来自gxwebsoft.com）
        if (input('param.sign')) {
            $store = Session::get('yoshop_store');
            if(isset($store) && $store['user']['store_user_id'] != input('param.wxapp_id')){
                Session::clear('yoshop_store');
                return $this->redirect(url('passport/login',input('param.')));
            }
            $model = new PostData;
            $model->autologin(input('param.'));
            return $this->redirect('index/index');
        }

        // 微信扫码登录
        $unionid = input('param.unionid');
        if($unionid){
            $model = new StoreUser;
            if ($model->wxlogin($unionid)) {
                return $this->redirect('index/index');
            }
        }

        // 表单登录提交
        if ($this->request->isAjax()) {

            $model = new StoreUser;
            if ($model->login($this->postData('User'))) {
                return $this->renderSuccess('登录成功', url('index/index'));
            }
            return $this->renderError($model->getError() ?: '登录失败');
        }
        $this->view->engine->layout(false);

        return $this->fetch('login', [
            // 系统版本号
            'version' => get_version(),
            'wxcode'  => '',
        ]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Session::clear('yoshop_store');
        $this->redirect('passport/login');
    }
    /** * 字符串替换 * @param string $str 要替换的字符串 * @param string $repStr 即将被替换的字符串 * @param int $start 要替换的起始位置,从0开始 * @param string $splilt 遇到这个指定的字符串就停止替换 */
    function StrReplace($str, $repStr, $start, $splilt = '')
    {
        $newStr = substr($str, 0, $start);
        $breakNum = -1;
        for ($i = $start; $i < strlen($str); $i++) {
            $char = substr($str, $i, 1);
            if ($char == $splilt) {
                $breakNum = $i;
                break;
            }
            $newStr .= $repStr;
        }
        if ($splilt != '' && $breakNum > -1) {
            for ($i = $breakNum; $i < strlen($str); $i++) {
                $char = substr($str, $i, 1);
                $newStr .= $char;
            }
        }
        return $newStr;
    }


}
