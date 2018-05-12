<?php
include_once("lib/session.php");
include_once("lib/function.php");
check_login();
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
<script id="home_template" type="text/x-dot-template">
	{{ for(var i=0; i< it.length; i++) { }}
	<li class="divider grid">
		<div class="col-5" style="width: 100px;">电话:{{=it[i].name}}&nbsp;&nbsp;||&nbsp;&nbsp;编号:{{=it[i].No}}</div>
	</li>
	<li class="grid">
		<div class="col-1"><strong>{{=it[i].nick}}</strong>-<span style="color:#666;font-size:0.8em;">
		{{? it[i].consu_name != null }}
		{{=it[i].consu_name}}
		{{?? }}
		无
		{{? }}
		,
		{{? it[i].assis_name != null }}
		{{=it[i].assis_name}}
		{{?? }}无
		{{? }}</span>
		{{? it[i].xuqiu.length > 10 }}
		<p>{{=it[i].xuqiu.substring(0,10) + '...'}}</p>
		{{?? }}
		<p>{{=it[i].xuqiu}}</p>
		{{? }}
		</div>
		<div class="lab_yuyue"><a href="#yuyue_section?vid={{=it[i].id}}" data-target="section">预约</a></div>
		<div class="lab_zixun"><a href="#visitor_section?vid={{=it[i].id}}" data-target="section">咨询</a></div>
		{{? it[i].consu_name != null }}		
		<div class="lab_jilu"><a href="#visitorlog_section?vid={{=it[i].id}}" data-target="section">记录</a></div>
		{{?? }}
		<div class="lab_huifang"><a href="#visitorlog_section?vid={{=it[i].id}}" data-target="section">跟进</a></div>
		{{? }}		
	</li>
	<li class="divider"></li>
	{{ } }}	
</script>
<body>
<div id="aside_container"> </div>
<div id="section_container">
  <section id="index_section" class="active">
    <header>
      <nav class="left"> <a data-target="section" href="#profile_section" class="icon menu"></a> </nav>
      <h1 class="title"> <a href="#" onClick="home_refresh()"><span id="visitor_title"></span><span id="visitor_count"></span></a></h1>
      <nav class="right"> <a data-target="section" class="button" id="logout" href="#">退出</a> </nav>
    </header>
    <nav class="header-secondary">
      <form id="search" style="width:100%">
        <div class="grid" style="width:90%">
          <div class="col-2">
            <input type="text" name="nick" placeholder="搜索姓名" style="height:25px;">
          </div>
          <div class="col-2">
            <input type="text" name="phone" placeholder="搜索电话" style="height:25px">
          </div>
          <div class="col-2">
            <input type="text" name="bianhao" placeholder="编号" style="height:25px">
          </div>          
          <div class="col-1">
            <input type="button" style="height:25px; width:50px" value="搜索" onClick="home_refresh()">
          </div>
        </div>
      </form>
    </nav>
    <article class="active" data-scroll="true" style="padding: 10px 0;">
      <div id="refresh_article" style="padding: 0px 5px 5px;">
        <div id="the-scroller-home">
          <ul class="list" id="home_list">
            <li class="divider grid">
              <div class="col-5" style="width: 100px;">电话：加载中... | 加载中...  | 加载中... 2</div>
            </li>
            <li class="grid">
              <div class="col-1"> <a href="#" data-target="section"><strong>加载中... </strong>
                <p>加载中... </p>
                </a> </div>
              <div class="lab_yuyue"><a href="#" data-target="section">预约</a></div>
              <div class="lab_jilu"><a href="#" data-target="section">记录</a></div>
            </li>
            <li class="divider"></li>
          </ul>
        </div>
      </div>
    </article>
    <footer><a href="#add_section" data-target="section" id="admin_add_visitor">
      <div class="add">添加客户</div>
      </a> </footer>
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
<script type="text/javascript" src="assets/js/app/app.js"></script>
</html>