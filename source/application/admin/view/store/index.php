<div class="row">
    <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
        <div class="widget am-cf">
            <div class="widget-head am-cf">
                <div class="widget-title am-cf">应用列表</div>
            </div>
            <div class="widget-body am-fr">
                <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                    <div class="am-form-group">
                        <div class="am-btn-toolbar">
                            <div class="am-btn-group am-btn-group-xs">
                                <a href="http://www.wsdns.cn/index.php?s=/user.wxapp/add" class="am-btn am-btn-default am-btn-success am-radius">请移步到会员中心创建</a>
<!--                                <a class="am-btn am-btn-default am-btn-success am-radius"-->
<!--                                   href="--><?//= url('store/add') ?><!--">-->
<!--                                    <span class="am-icon-plus"></span> 新增-->
<!--                                </a>-->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="am-u-sm-12">
                    <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                        <thead>
                        <tr>
                            <th>应用ID</th>
                            <th>应用名称</th>
                            <th>用户</th>
                            <th>状态</th>
                            <th>添加时间</th>
                            <th>到期时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                            <tr>
                                <td class="am-text-middle">
                                    <p class="item-title"><?= $item['wxapp_id'] ?></p>
                                </td>
                                <td class="am-text-middle">
                                    <p class="item-title"><?= $names[$item['wxapp_id']] ?></p>
                                </td>
                                <td class="am-text-middle">
                                    <p class="item-title"><?= $item['user_id'] ?></p>
                                    <p class="item-title"><?= $item['user']['nickName'] ?></p>
                                    <p class="item-title"><?= $item['user']['mobile'] ?></p>
                                </td>
                                <td>
                                    <?php if($item['status'] == 10): ?>
                                        <span class="am-badge am-badge-warning">试用</span>
                                    <?php endif;?>
                                    <?php if($item['status'] == 20): ?>
                                        <span class="am-badge am-badge-success">正常</span>
                                    <?php endif;?>
                                    <?php if($item['status'] == 30): ?>
                                        <span class="am-badge am-badge-danger">过期</span>
                                    <?php endif;?>
                                    <?php if($item['status'] == 40): ?>
                                        <span class="am-badge am-badge-defult">禁用</span>
                                    <?php endif;?>
                                </td>
                                <td class="am-text-middle"><?= date('Y-m-d',strtotime($item['create_time'])) ?></td>
                                <td class="am-text-middle">
                                    <div><?= date('Y-m-d',$item['expire_time']) ?></div>
                                    <div><?= expire_time($item['expire_time']) ?></span></div>
                                </td>
                                <td class="am-text-middle">
                                    <div class="tpl-table-black-operation">
                                        <a href="<?= url('store/enter', ['wxapp_id' => $item['wxapp_id']]) ?>"
                                           class="j-move tpl-table-black-operation-green" data-id="<?= $item['wxapp_id'] ?>" target="_blank">
                                            <i class="am-icon-arrow-right"></i> 进入应用
                                        </a>
                                        <a href="<?= url('store/transfer', ['wxapp_id' => $item['wxapp_id']])?>" class="tpl-table-black-operation"
                                           data-id="<?= $item['wxapp_id'] ?>">
                                            <i class="am-icon-pencil"></i> 编辑
                                        </a>
                                        <a href="javascript:void(0);" class="j-delete tpl-table-black-operation-del"
                                           data-id="<?= $item['wxapp_id'] ?>">
                                            <i class="am-icon-trash"></i> 删除
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="4" class="am-text-center">暂无记录</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="am-u-lg-12 am-cf">
                    <div class="am-fr"><?= $list->render() ?> </div>
                    <div class="am-fr pagination-total am-margin-right">
                        <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 模板：修改运费 -->
<script id="tpl-update-price" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label"> 输入运费 </label>
                    <div class="am-u-sm-8 am-u-end">
                        <input type="number" min="0" class="tpl-form-input" placeholder="请输入要变更的金额" name="update_price" placeholder="请输入管理员备注"  class="am-field-valid" value="" required >
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>


<script>
    $(function () {

        // 删除元素
        var url = "<?= url('store/recovery') ?>";
        $('.j-delete').delete('wxapp_id', url, '确定要删除吗？可在回收站中恢复');


    });
</script>

