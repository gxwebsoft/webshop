<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title>管理登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="assets/store/i/favicon2.ico"/>
    <link rel="stylesheet" href="assets/store/css/login/style.css?v=<?= $version ?>"/>
</head>
<body class="page-login-v3">
<div class="container">
    <div id="wrapper" class="login-body">
        <!-- 新增微信扫码登录 S -->
        <style>
            .login-body{ position: relative;}
            .j-wxlogin{position: absolute;right:0px; top: 0px;}
        </style>
        <a  class="j-wxlogin tpl-table-black-operation-default" href="javascript:void(0);" data-id="<?=$version?>">
            <img  class="logincode" src="https://res.wx.qq.com/mpres/zh_CN/htmledition/pages/login/loginpage/images/qr4b3e56.svg">
        </a>

        <div class="login-content" style="display: none">
            <div><?= $wxcode ?></div>
        </div>
        <!-- 新增微信扫码登录 E -->
        <div class="login-content">
            <div class="brand">
                <img alt="logo" class="brand-img" src="assets/store/img/login/logo_3.png?v=<?= $version ?>">
                <h2 class="brand-text">后台管理系统</h2>
            </div>
            <form id="login-form" class="login-form">
                <div class="form-group">
                    <input class="" name="User[user_name]" placeholder="请输入用户名" type="text" required>
                </div>
                <div class="form-group">
                    <input class="" name="User[password]" placeholder="请输入密码" type="password" required>
                </div>
                <div class="form-group">
                    <button id="btn-submit" type="submit">
                        登录
                    </button>
<!--                    <div class="j-reg register am-text-right">还没有账号，<a href="--><?//= url('reg')?><!--" class=""> 立即注册</a></div>-->
                </div>
            </form>
        </div>

        <!-- 模板：微信扫码登录 -->
        <script id="tpl-wxlogin" type="text/template">
            <div class="am-padding-xs am-padding-top" style="display: flex; justify-content: center; padding-top: 15px; ">
                <form class="am-form tpl-form-line-form" method="post" action="">
                    <div class="am-tab-panel am-padding-0 am-active">
                        <div class="am-form-group">
                            <div class="am-u-sm-8 am-u-end">
                                <iframe src="https://developer.wsdns.cn/index.php?s=/api/wxlogin/wxsan" height="420"></iframe>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </script>
    </div>
</div>

</body>
<script src="assets/common/js/jquery.min.js"></script>
<script src="assets/common/plugins/layer/layer.js?v=<?= $version ?>"></script>
<script src="assets/common/js/jquery.form.min.js"></script>
<script src="assets/common/js/amazeui.min.js"></script>
<script src="assets/common/js/art-template.js"></script>
<script src="assets/store/js/app.js?v=<?= $version ?>"></script>
<script>
    $(function () {
        // 表单提交
        var $form = $('#login-form');
        $form.submit(function () {
            var $btn_submit = $('#btn-submit');
            $btn_submit.attr("disabled", true);
            $form.ajaxSubmit({
                type: "post",
                dataType: "json",
                // url: '',
                success: function (result) {
                    $btn_submit.attr('disabled', false);
                    if (result.code === 1) {
                        layer.msg(result.msg, {time: 1500, anim: 1}, function () {
                            window.location = result.url;
                        });
                        return true;
                    }
                    layer.msg(result.msg, {time: 1500, anim: 6});
                }
            });
            return false;
        });

        /**
         * 微信扫码登录
         */
        $('.j-wxlogin').on('click', function () {
            var data = $(this).data();
            console.log(data)
            $.showModal({
                title: '微信扫码登录'
                , area: '460px'
                , content: template('tpl-wxlogin', data)
                , uCheck: true
                , btn:[]
                , success: function ($content) {
                }
            });
        });
    });
</script>
</html>
