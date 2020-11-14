<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">修改密码</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 用户名 </label>
                                <!--                                    <div class="am-u-sm-9 am-padding-top-xs">-->
                                <!--                                        <span class="am-form--static">--><?//= $model['user_name'] ?><!--</span>-->
                                <!--                                    </div>-->
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="user[user_name]"
                                           value="<?= $model['user_name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 登录密码 </label>
                                <div class="am-u-sm-9">
                                    <input type="password" class="tpl-form-input" name="user[password]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 确认密码 </label>
                                <div class="am-u-sm-9">
                                    <input type="password" class="tpl-form-input" name="user[password_confirm]"
                                           value="" required>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <label class="am-u-sm-3 am-form-label form-require"> 绑定微信 </label>
                            <div class="am-u-sm-9 am-padding-top-xs">
                                <?php if(isset($model['unionid']) && $model['unionid']):?>
                                    <div class="help-block">
                                        <span class="am-badge x-cur-p am-badge-success"><i class="am-icon-weixin"></i> 已绑定</span>
                                    </div>
                                <?php else: ?>
                                    <div class="help-block">
                                        <span class="j-unionid am-badge x-cur-p am-badge-warning"><i class="am-icon-weixin"></i> 立即绑定</span>
                                    </div>
                                <?php endif; ?>
                                <small>微信扫码登录，绑定后可以使用微信扫码快捷登录</small>
                            </div>
<!--                            <div class="am-form-group">-->
<!--                                <label class="am-u-sm-3 am-form-label form-require"> 手机绑定 </label>-->
<!--                                <div class="am-u-sm-9 am-padding-top-xs">-->
<!--                                    --><?php //if($model['mobile'] == ''):?>
<!--                                        <span class="j-mobile am-badge x-cur-p am-badge-warning"><i class="am-icon-mobile"></i> 立即绑定</span>-->
<!--                                    --><?php //else:?>
<!--                                        <span class="am-form--static">--><?//=$model['mobile']?><!--</span>-->
<!--                                        <span class="am-badge x-cur-p am-badge-success"><i class="am-icon-mobile"></i> 已绑定</span>-->
<!--                                    --><?php //endif;?>
<!--                                </div>-->
<!--                            </div>-->
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>

                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        /**
         * 绑定微信
         */
        $('.j-unionid').on('click', function () {
            window.open('/api/wxlogin/getUnionid/unionid/' + '<?=$model['store_user_id']?>');
        });



    });
</script>
