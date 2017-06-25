<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <title><?php echo empty($page_title) ? C('WEB_SITE_TITLE') : $page_title; ?></title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<meta content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <meta content="application/xhtml+xml;charset=UTF-8" http-equiv="Content-Type">
    <meta content="no-cache,must-revalidate" http-equiv="Cache-Control">
    <meta content="no-cache" http-equiv="pragma">
    <meta content="0" http-equiv="expires">
    <meta content="telephone=no, address=no" name="format-detection">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="stylesheet" type="text/css" href="/Public/Home/css/mobile_module.css?v=<?php echo SITE_VERSION;?>" media="all">
    <script type="text/javascript">
		//静态变量
		var IMG_PATH = "/Public/Home/images";
		var STATIC_PATH = "/Public/static";
		var SITE_URL = "<?php echo SITE_URL;?>";
		var WX_APPID = "<?php echo ($jsapiParams["appId"]); ?>";
		var	WXJS_TIMESTAMP='<?php echo ($jsapiParams["timestamp"]); ?>'; 
		var NONCESTR= '<?php echo ($jsapiParams["nonceStr"]); ?>'; 
		var SIGNATURE= '<?php echo ($jsapiParams["signature"]); ?>';
	</script>
    <script type="text/javascript" src="/Public/static/jquery-2.0.3.min.js"></script>
	<script type="text/javascript" src="http://yaotv.qq.com/shake_tv/include/js/lib/zepto.1.1.4.min.js"></script>
	<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
	<script type="text/javascript" src="minify.php?f=/Public/Home/js/prefixfree.min.js,/Public/Home/js/m/dialog.js,/Public/Home/js/m/flipsnap.min.js,/Public/Home/js/m/mobile_module.js&v=<?php echo SITE_VERSION;?>"></script>
</head>
<link href="<?php echo ADDON_PUBLIC_PATH;?>/mobile/common.css?v=<?php echo SITE_VERSION;?>" rel="stylesheet" type="text/css">
<body class="withFoot">
    <div class="container">
    	<div class="center_header">
    		<?php if(!empty($follow["headimgurl"])): ?><img src="<?php echo ($follow["headimgurl"]); ?>"/>
    		<?php else: ?>
        	<img src="<?php echo ADDON_PUBLIC_PATH;?>/mobile/center.png"/><?php endif; ?>
           <?php if(!empty($follow["nickname"])): echo ($follow["nickname"]); ?>
    		<?php else: ?>
        	本地用户<?php endif; ?>
        </div>		
        <div class="center_nav">
        	<a href="<?php echo ($ordersUrl); ?>">全部订单</a>
            <a href="<?php echo ($unPayUrl); ?>">待付款</a>
            <a href="<?php echo U('shippingOrder');?>">配送中</a>
        </div>
        <div class="block">
        	<a class="block_a" href="<?php echo ($cartUrl); ?>">我的购物车<em class="arrow_right">&nbsp;</em></a>
            <a class="block_a" href="<?php echo ($collectUrl); ?>">我的收藏<em class="arrow_right">&nbsp;</em></a>
            <!--<a class="block_a" href="#">我的浏览器记录<em class="arrow_right">&nbsp;</em></a>-->
            <a class="block_a block_last" href="<?php echo ($addressUrl); ?>">我的收货地址<em class="arrow_right">&nbsp;</em></a>
        </div>
    </div>	
    <!-- 底部导航 -->
    <div class="bottom_menu"> 
<a class="home" href="<?php echo U('index', array('shop_id'=>$shop_id));?>">首页</a> 
<a class="cart" href="<?php echo U('cart', array('shop_id'=>$shop_id));?>">购物车<span class="count"><?php echo ($cart_count); ?></span></a> 
<a class="center" href="<?php echo U('user_center', array('shop_id'=>$shop_id));?>">个人中心</a> 
</div>
<p class="copyright"><?php echo ($system_copy_right); echo ($tongji_code); ?></p>

</body>
</html>