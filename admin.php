<?php
include_once("lib/session.php");
include_once("lib/function.php");
check_admin_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>系统</title>
<meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="stylesheet" href="assets/css/Jingle.css">
</head>
<body>
<div id="aside_container"> </div>
<div id="section_container">
  <section id="admin_admin" class="active">
    <header>
      <nav class="left"> <a data-target="section" href="#profile_admin" class="icon menu"></a> </nav>
      <h1 class="title"> <a href="#" onClick="admin_refresh(true)" ><?php echo $_SESSION["nick"]?>-系统管理</a></h1>
      <nav class="right"> <a data-target="section" class="button" id="logout" href="#">退出</a> </nav>
    </header>
    <article class="active" data-scroll="true" style="padding: 10px 0;">
      <div class="indented">
        <form class="input-group" id="admin_form">
          <div class="input-row" id="admin_date_div">
            <label style="width:70px">开始*</label>
            <input style="min-width:100px;" type="text" name="admin_start" id="admin_start" readonly>
            <label style="width:70px">结束*</label>
            <input style="min-width:100px;" type="text" name="admin_end" id="admin_end" readonly>
          </div>
          <div class="input-row">
            <label style="width:70px">净入：</font></label>
            <label style="min-width:100px;color:#F00" id="admin_jingru">加载中...</label>
            <label style="width:70px">收费：</label>
            <label style="min-width:120px;color:#F00" id="admin_total">加载中...</label>
          </div>
          <ul class="list" id="admin_list">
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_jiaofei"><strong>客户缴费</strong>
                <p>点击显示明细</p>
                </a> </div>
              <div class="lab_zixun" style="font-weight:bold" id="admin_xs_jiaofei">0</div>
            </li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_zijinsr"><strong>咨询收入</strong>
                <p>点击显示明细</p>
                </a> </div>
              <div class="lab_zixun" style="font-weight:bold" id="admin_xs_zijinsr">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_dingjinsr"><strong>订金收入</strong>
                <p>点击显示明细</p>
                </a></div>
              <div class="lab_jilu"  style="font-weight:bold" id="admin_xs_dingjinsr">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_conusl"><strong>咨询师提成</strong>
                <p>咨询师详情</p>
                </a> </div>
              <div class="lab_yuyue"  style="font-weight:bold" id="admin_xs_consul">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_assis"><strong>助理提成</strong>
                <p>助理详情</p>
                </a></div>
              <div class="lab_yuyue"  style="font-weight:bold" id="admin_xs_assis">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_visitor"><strong>新增客户</strong>
                <p>客户详情</p>
                </a></div>
              <div class="lab_yuyue"  style="font-weight:bold" id="admin_xs_visitor">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_yuyue"><strong>预约记录</strong>
                <p>预约详情</p>
                </a></div>
              <div class="lab_yuyue"  style="font-weight:bold" id="admin_xs_yuyue">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_huifang"><strong>回访记录</strong>
                <p>回访详情</p>
                </a></div>
              <div class="lab_yuyue"  style="font-weight:bold" id="admin_xs_huifang">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_bushi"><strong>补时记录</strong>
                <p>补时详情</p>
                </a></div>
              <div class="lab_yuyue"  style="font-weight:bold" id="admin_xs_bushi">0</div>
            </li>
            <li class="divider"></li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section" id="admin_zldenglu"><strong>助理登陆记录</strong>
                <p>助理登陆详情</p>
                </a></div>
              <div class="lab_yuyue"  style="font-weight:bold" id="admin_xs_zldenglu">0</div>
            </li>
            <li class="divider"></li>                                             
          </ul>
        </form>
        <div class="input-group" style="height:10px"></div>
      </div>
    </article>
  </section>
</div>
</body>
<!-- lib -->
<script type="text/javascript" src="assets/js/lib/zepto.js"></script>
<script type="text/javascript" src="assets/js/lib/iscroll.js"></script>
<script type="text/javascript" src="assets/js/lib/template.min.js"></script>
<script type="text/javascript" src="assets/js/lib/Jingle.debug.js"></script>
<script type="text/javascript" src="assets/js/lib/zepto.touch2mouse.js"></script>
<script type="text/javascript" src="assets/js/lib/JChart.debug.js"></script>
<script type="text/javascript" src="assets/js/lib/doT.min.js"></script>
<script type="text/javascript" src="assets/js/lib/fun.js"></script>
<script type="text/javascript" src="assets/js/lib/sortTable.js"></script>
<!--- app --->
<script type="text/javascript" src="assets/js/app/app_admin.js"></script>
</html>