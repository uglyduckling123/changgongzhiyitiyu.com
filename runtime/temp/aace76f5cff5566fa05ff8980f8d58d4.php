<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:94:"/www/wwwroot/changgongzhiyitiyu.com/public/../application/admin/view/venue/place_room/add.html";i:1681261604;s:78:"/www/wwwroot/changgongzhiyitiyu.com/application/admin/view/layout/default.html";i:1642752126;s:75:"/www/wwwroot/changgongzhiyitiyu.com/application/admin/view/common/meta.html";i:1642752126;s:77:"/www/wwwroot/changgongzhiyitiyu.com/application/admin/view/common/script.html";i:1642752126;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">
<meta name="robots" content="noindex, nofollow">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('所属场馆'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-place_id" data-rule="required" data-source="venue/place/index" class="form-control selectpage" name="row[place_id]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('Name'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" class="form-control" name="row[name]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('公益时间段'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-slof_time" class="form-control" name="row[slof_time]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('第一时间段'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-gold_one_time" class="form-control" name="row[gold_one_time]" type="text">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('第二时间段'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-gold_two_time" class="form-control" name="row[gold_two_time]" type="text">
        </div>
    </div>
     <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('第一时间段会员价格'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-one_first_price" class="form-control" name="row[one_first_price]" type="number">
        </div>
    </div>
     <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('第一时间段非会员价格'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-one_second_price" class="form-control" name="row[one_second_price]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('第二时间段会员价格'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-weekday_one_price" class="form-control" name="row[weekday_one_price]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('第二时间段非会员价格'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-weekday_two_price" class="form-control" name="row[weekday_two_price]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('周六节假日会员价格'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-saturday_one_price" class="form-control" name="row[saturday_one_price]" type="number">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('周六节假日非会员价格'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-saturday_two_price" class="form-control" name="row[saturday_two_price]" type="number">
        </div>
    </div>

    <!--<div class="form-group">-->
    <!--    <label class="control-label col-xs-12 col-sm-2"><?php echo __('节假日会员价格'); ?>:</label>-->
    <!--    <div class="col-xs-12 col-sm-8">-->
    <!--        <input id="c-wrong_one_price" class="form-control" name="row[wrong_one_price]" type="number">-->
    <!--    </div>-->
    <!--</div>-->
    <!--<div class="form-group">-->
    <!--    <label class="control-label col-xs-12 col-sm-2"><?php echo __('节假日非会员价格'); ?>:</label>-->
    <!--    <div class="col-xs-12 col-sm-8">-->
    <!--        <input id="c-wrong_two_price" class="form-control" name="row[wrong_two_price]" type="number">-->
    <!--    </div>-->
    <!--</div>-->
    <div class="form-group">
        <label class="control-label col-xs-12 col-sm-2"><?php echo __('收费时间段可约天数'); ?>:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-make_day" class="form-control" name="row[make_day]" type="text">
        </div>
    </div>
    <div class="form-group layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-primary btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>
</form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
