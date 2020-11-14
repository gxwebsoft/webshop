<?php

namespace app\store\model\store;

use think\Session;
use app\common\model\store\User as StoreUserModel;

/**
 * 商家用户模型
 * Class StoreUser
 * @package app\store\model
 */
class User extends StoreUserModel
{
    /**
     * 商家用户登录
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($data)
    {
        // 验证用户名密码是否正确
        if (!$user = $this->getLoginUser($data['user_name'], $data['password'])) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        if (empty($user['wxapp'])) {
            $this->error = '登录失败, 未找到小程序信息';
            return false;
        }
        if ($user['wxapp']['is_recycle']) {
            $this->error = '登录失败, 当前小程序商城已删除';
            return false;
        }
        // 保存登录状态
        $this->loginState($user);
        return true;
    }

    /**
     * 获取登录用户信息
     * @param $user_name
     * @param $password
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getLoginUser($user_name, $password)
    {
        return self::useGlobalScope(false)->with(['wxapp'])->where([
            'user_name' => $user_name,
            'password' => yoshop_hash($password),
            'is_delete' => 0
        ])->find();
    }

    /**
     * 获取用户列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function add($data)
    {
        if (self::checkExist($data['user_name'])) {
            $this->error = '用户名已存在';
            return false;
        }
        if ($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        if (empty($data['role_id'])) {
            $this->error = '请选择所属角色';
            return false;
        }
        $this->startTrans();
        try {
            // 新增管理员记录
            $data['password'] = yoshop_hash($data['password']);
            $data['wxapp_id'] = self::$wxapp_id;
            $data['is_super'] = 0;
            $this->allowField(true)->save($data);
            // 新增角色关系记录
            (new UserRole)->add($this['store_user_id'], $data['role_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新记录
     * @param array $data
     * @return bool
     * @throws \think\exception\DbException
     */
    public function edit($data)
    {
        if ($this['user_name'] !== $data['user_name']
            && self::checkExist($data['user_name'])) {
            $this->error = '用户名已存在';
            return false;
        }
        if (!empty($data['password']) && ($data['password'] !== $data['password_confirm'])) {
            $this->error = '确认密码不正确';
            return false;
        }
        if (empty($data['role_id'])) {
            $this->error = '请选择所属角色';
            return false;
        }
        if (!empty($data['password'])) {
            $data['password'] = yoshop_hash($data['password']);
        } else {
            unset($data['password']);
        }
        $this->startTrans();
        try {
            // 更新管理员记录
            $this->allowField(true)->save($data);
            // 更新角色关系记录
            (new UserRole)->edit($this['store_user_id'], $data['role_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        if ($this['is_super']) {
            $this->error = '超级管理员不允许删除';
            return false;
        }
        // 删除对应的角色关系
        UserRole::deleteAll(['store_user_id' => $this['store_user_id']]);
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 更新当前管理员信息
     * @param $data
     * @return bool
     */
    public function renew($data)
    {
        if ($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        if ($this['user_name'] !== $data['user_name']
            && self::checkExist($data['user_name'])) {
            $this->error = '用户名已存在';
            return false;
        }
        // 更新管理员信息
        if ($this->save([
                'user_name' => $data['user_name'],
                'password' => yoshop_hash($data['password']),
            ]) === false) {
            return false;
        }
        // 更新session
        Session::set('yoshop_store.user', [
            'store_user_id' => $this['store_user_id'],
            'user_name' => $data['user_name'],
        ]);
        return true;
    }

    /**
     * 一键登录登录
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function autologin11($data)
    {
        // 验证用户名密码是否正确
        $user = db('store_user')
            ->where('orderNo',$data['orderNo'])
            ->find();
        $user['wxapp']= db('wxapp')->find($user['wxapp_id']);
        if (!$user) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        if (empty($user['wxapp'])) {
            $this->error = '登录失败, 未找到小程序信息';
            return false;
        }
        if ($user['wxapp']['is_recycle']) {
            $this->error = '登录失败, 当前小程序商城已删除';
            return false;
        }
        // 保存登录状态
        // 保存登录状态
        Session::set('yoshop_store', [
            'user' => [
                'store_user_id' => $user['store_user_id'],
                'user_name' => $user['user_name'],
            ],
            'wxapp' => $user['wxapp'],
            'is_login' => true,
        ]);
        return json_encode(Session::get('yoshop_store'));
    }

    public function autologin($data)
    {

        return json_encode($data);
        //签名验证（AK/SK算法)
        $sign=$this->verifySign(input('param.appid'),input('param.'));
        if($sign['code']!=0){
            return '签名失败';
        }

        $store=db('store_user')->where('wxapp_id',input('param.appid'))->find();
        $wxapp=db('wxapp')->where('wxapp_id',input('param.appid'))->find();
        $store['wxapp']=$wxapp;

        // 验证用户名密码是否正确
        if (!$store) {
            echo '登录失败, 用户名或密码错误';
            return false;
        }
        if (empty($store['wxapp'])) {
            echo  '登录失败, 未找到小程序信息';
            return false;
        }
        if ($store['wxapp']['is_recycle']) {
            echo '登录失败, 当前小程序商城已删除';
            return false;
        }

        // 保存登录状态
        Session::set('yoshop_store', [
            'user' => [
                'store_user_id' => $store['store_user_id'],
                'user_name' => $store['user_name'],
            ],
            'wxapp' => $store['wxapp'],
            'is_login' => true,
        ]);
        return $this->redirect('/index.php?s=/store/index/index/');
    }

    /**
     * 商家用户登录
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function wxlogin($unionid)
    {
        $unionid = input('param.unionid/s');
        $model = new StoreUserModel();
        $user =  $model->with(['wxapp'])->where([
            'unionid' => $unionid,
            'is_delete' => 0
        ])->find();
        /** @var \app\common\model\Wxapp $wxapp */
        $wxapp = $user['wxapp'];


        // 保存登录状态
        Session::set('yoshop_store', [
            'user' => [
                'store_user_id' => $user['store_user_id'],
                'user_name' => $user['user_name'],
            ],
            'wxapp' => $wxapp->toArray(),
            'is_login' => true,
        ]);
        return true;
    }
}
