<?php
include_once("lib/session.php");
include_once("lib/function.php");
check_index_page();
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
  <section id="login_section" class="active">
    <header>
      <h1 class="title">登录</h1>
      <nav class="right"> <a data-target="section" data-icon="info" href="#"></a> </nav>
    </header>
    <article id="refresh_article" class="active" data-scroll="true" style="padding: 10px 0;">
      <div  class="indented" >
        <form class="input-group" id="login">
          <div class="input-row">
            <label>电话</label>
            <input type="text" placeholder="电话号码登录" name="user" id="user">
          </div>
          <div class="input-row">
            <label>密码</label>
            <input type="password" placeholder="密码" name="pass" id="pass">
            <input type="hidden" name="key" id="huimingkey" value="123543213">
          </div>
          <a class="button block" data-icon="cogs" onClick="login()">登录</a>
        </form>
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
<!--- app --->
<script type="text/javascript">
function login() {
	if (($('#user').val() =="") || ($('#pass').val() =="")) {
		alert('请填写用户名或密码');
		return;
	}
	var pdata =  $('#login').serialize();
  	$.ajax({
		url : 'ajax.php?action=login',
		dataType: "json", 
        data : pdata,
        type : 'post',
        success : function(data){
			alert(data.msg);
			if (data.success) {
				window.location.href=data.ref; 
			}
        },
        error : function(){
			alert('网络错误');
        }
     })
}
</script>
</html>