<?php
include_once("lib/session.php");
include_once("lib/config.php");
include_once("lib/function.php");
include_once("lib/MysqliDb.php");
header('Content-Type: application/json');
$db = new MysqliDb ($mySQLserver, $mySQLuser, $mySQLpass, $mySQLdb, $mySQLPort);
$PAGE_NUM = 20;

$now_time =  date("Y-m-d H:i:s", get_now()); 
if( !count($_GET) && !detect_key_exists(array("action"), $_GET)) {	
	$arr['msg'] = '非法请求!';
	goto ret;
}
$action = $_GET['action'];
$arr['success'] = 0; 
if (!is_admin()) {
	$arr['success'] = -1;
	$arr['msg'] = '登陆超时,请重新登陆';
    goto ret;
}

elseif ($action == 'logout') {  //退出 
    unset($_SESSION); 
    session_destroy(); 
	$arr['success'] = 1; 
	$arr['msg'] = '退出成功！'; 
}//############################################################
elseif ($action == 'changeps') {	//添加 修改密码
	if (!detect_key_exists(array("password", "password1"), $_POST)){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	unset($_POST['password1']);
	$rs = $db
		->where('name', $_SESSION['name'])
		->update ('assistant', $_POST);
	if ($rs) {		
		$arr['success'] = 1; 
		$arr['msg'] = '修改成功！'; 
	} else { 
        $arr['msg'] = '修改失败'; 
    }		
}//############################################################
elseif ($action == 'get_consultant') {	//咨询师列表???????????
	/*if (!detect_key_exists(array("date"), $_GET)){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}

	$offset = 0;
	$count = 0;
	get_count_offset($_GET, $count, $offset);	
	$table = "consultant";
	$search = create_like_sql($table, $_POST, "nick", "phone");
	//$where = create_date_where($_GET["date"]);
	$t = "(SELECT consul_id,sum(expen_consul) as s FROM `log_expenditure` where ".$where." group by consul_id)";
	$sql = 'select id, name, nick, j.s FROM '.$table.' as c left join '.$t.' j on j.consul_id =c.id where disable = 0 and level<10 '.$search.' LIMIT '.$offset.','.$count;
	$rs = $db->rawQuery($sql);	
	if (!$db->count == 0) {
		$arr['have_data'] = 1; 
	}
	$arr['success'] = 1; 
	$arr["result"] = $rs;*/
} ///////////////////////////////////////////////////////////////////////////////////////////
elseif ($action == 'get_assistant') {	//助理师列表 
	/*$offset = 0;
	$count = 0;
	get_count_offset($_GET, $count, $offset);	
	$table = 'assistant';
	$search = create_like_sql($table, $_POST, "nick", "phone");
	$where = create_month_where($_GET["date"]);
	$t = "(SELECT assis_id,sum(expen_assis) as s FROM `log_expenditure` where ".$where." group by assis_id)";
	$sql = 'select id, name,nick,j.s FROM '.$table.' as c left join '.$t.' j on j.assis_id=c.id where disable = 0 and level<10 '.$search.' LIMIT '.$offset.','.$count;
	$rs = $db->rawQuery($sql);
	if (!$db->count == 0) {
		$arr['have_data'] = 1;
	}
	$arr['success'] = 1; 
	$arr["result"] = $rs;*/
} ////////////////////////////////////////////////////////////////////////////////////////////
elseif ($action == 'get_admin_refresh') {	//本月合计 
	if (!detect_key_exists(array("admin_start", "admin_end"), $_POST) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	$where = create_date_where($_POST);
	$rs["total"] = "0.00";		//净入
	
	$rs["sum_cash"] = "0.00";	
	$rs["sum_res_cash"] = "0.00";	
	$rs["sum_con_cash"] = "0.00"; //消费
	
	$rs["dingjin"] = "0.00";
	
	$rs["visitor"] = "0.00";
	$rs["assis"] = "0.00";
	$rs["consul"] = "0.00";
	
	$rs["vis_sum"] = "0";
	$rs["yuyue_sum"] = "0";
	$rs["huifang_sum"] = "0";
	$rs["bushi_sum"] = "0";
	
	$sql = "select sum(expen_assis) as assis, sum(expen_consul) as consul, sum(expenditure_total) as total from log_expenditure where ".$where;
	admin_get_sql($db, $sql, array("total", "assis", "consul"), $rs);
	$rs["visitor"] = $rs["total"] - $rs["assis"] - $rs["consul"];
	$rs["sum_con_cash"] = $rs["total"];

	$sql = "select sum(cash) as sum_cash , sum(residual_cash) as sum_res_cash from log_income where ".$where;
	admin_get_sql($db, $sql, array("sum_cash", "sum_res_cash"), $rs);
	
	$sql = "select sum(jine) as dingjin from log_dingjin where ".$where;
	admin_get_sql($db, $sql, array("dingjin"), $rs);
	
	$sql = "select count(*) as vis_sum from visitor where ".create_date_where($_POST, "add_time");
	admin_get_sql($db, $sql, array("vis_sum"), $rs);
	
	$sql = "select count(*) as yuyue_sum from visitor where ".create_date_where($_POST, "add_time");
	admin_get_sql($db, $sql, array("yuyue_sum"), $rs);
	
	$sql = "(select * from (select y.date,v.id as vid from yuyue as y left join visitor as v on v.id=y.visitor_id where ".create_date_where($_POST, "y.op_time")." group by date, shijiand_id order by shijiand_id asc)  a group by date,vid) b";
	$sql = "select count(*) as yuyue_sum from ".$sql;
	admin_get_sql($db, $sql, array("yuyue_sum"), $rs);

	$sql = "select count(*) as huifang_sum from huifang where ".create_date_where($_POST);
	admin_get_sql($db, $sql, array("huifang_sum"), $rs);
	
	$sql = "select count(*) as bushi_sum from log_bushi where ".create_date_where($_POST);
	admin_get_sql($db, $sql, array("bushi_sum"), $rs);
	
	$m = LEVEL_ADMIN;
	$sql = "select count(*) as zhulidl_sum from log_assis_login as l join assistant as a on a.id=l.assis_id where a.level <".$m." and ".create_date_where($_POST, "l.login_time");
	admin_get_sql($db, $sql, array("zhulidl_sum"), $rs);
	
	$arr["result"] = $rs;
	$arr['success'] = 1; 
} ////////////////////////////////////////////////////////////////////////////////////////////
elseif ($action == 'table') {	//本月合计 table
	if (!detect_key_exists(array("type"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	$t = $_GET["type"];
	$where = create_date_where($_POST, "log.op_time");
	if ($where != "") {
		$where = "where ".$where;
	}
	$Nomber = 'concat(date_format(v.add_time, "%y%m%d"), v.fang_dian, (select LPAD(count(*),3,"0") from `visitor` as v1 where date_format(v1.add_time, "%Y%m%d")=date_format(v.add_time, "%Y%m%d") and v1.add_time<=v.add_time))';
	
	if ($t == "admin_jiaofei") {//////////////////////////////////	
		$column = 't.nick as type_nick, a.nick as assis_name, c.nick as consu_name, '.$Nomber.' as No, v.nick, date_format(log.op_time, "%Y%m%d") as op_time, cash, a1.nick as op_name';	
		$from = ' FROM log_income as log join visitor as v on v.id=log.visitor_id left join consultant as c on c.id=log.consul_id left join assistant as a on log.assis_id=a.id join income_type as t on t.name=log.type join assistant as a1 on log.assis_op_id = a1.id '.$where.' order by v.op_time DESC ';
		$col = array(array("op_time","时间"),
					 array("No","编号"),
					 array("nick","客户名"),
					 array("consu_name","咨询师"),
					 array("assis_name","首邀助理"),
					 array("cash","缴费"),
					 array("type_nick","类型"),
 					 array("op_name","操作"));
		$sort = array("Date", "String", "String",  "String", "String","Number", "String", "String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "缴费明细", $arr);		
	} else if ($t == "admin_zijinsr") {//////////////////////////////////
		$column = 'log.time_sc as t_sc, a.nick as assis_name, c.nick as consu_name, '.$Nomber.' as No, v.nick, date_format(log.op_time, "%Y%m%d") as op_time, expenditure_total as t_cash, expen_consul as consul_cash, expen_assis as ass_cash, log.comment as _title_, a1.nick as op_name';	
		$from = ' FROM log_expenditure as log join visitor as v on v.id=log.visitor_id left join consultant as c on c.id=log.consul_id left join assistant as a on log.assis_id=a.id left join assistant as a1 on a1.id=log.assis_op_id '.$where.' order by log.op_time DESC ';
		$col = array(array("op_time","时间"),
					 array("No","编号"),
					 array("nick","客户名"),
					 array("consu_name","咨询师"),
					 array("assis_name","助理"),
					 array("t_sc","时长-分"),
					 array("t_cash","消费"),
					 array("consul_cash","咨询师提"),
					 array("ass_cash","助理提"),
					 array("op_name","操作") );
		$sort = array("Date", "String", "String",  "String", "String","Number", "Number", "Number", "Number", "String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "咨询明细", $arr);
	} else if ($t == "admin_dingjinsr") {//////////////////////////////////
		$column = $Nomber.' as No, v.nick, date_format(log.op_time, "%Y%m%d") as op_time, log.jine, a.nick as op_name, log.jine ';
		$from = ' FROM log_dingjin as log join visitor as v on v.id=log.visitor_id left join assistant as a on a.id=log.assis_op_id '.$where.' order by log.op_time DESC ';
		$col = array(array("op_time","时间"),
					 array("No","编号"),
					 array("nick","客户名"),
					 array("jine","金额"),
					 array("op_name","操作") );
		$sort = array("Date", "String", "String",  "Number", "String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "订金明细", $arr);
	} else if ($t == "admin_conusl") {//////////////////////////////////
		$t = "(SELECT log.comment as _title_, consul_id, sum(expen_consul) as expen_consul, COUNT(*) as number FROM `log_expenditure` as log ".$where." group by consul_id)";
		$column = 'c.nick, t.*, c.name';
		$from = ' FROM consultant as c left join '.$t.' as t on t.consul_id =c.id where disable = 0 ';
		$col = array(array("nick","名称"),
					 array("name","电话"),
					 array("number","咨询次数"),
					 array("expen_consul","提成"));
		$sort = array("String", "Number", "Number",  "Number");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "咨询师详情", $arr);
	} else if ($t == "admin_assis") {//////////////////////////////////
		$t = "(SELECT log.comment as _title_, assis_id, sum(expen_assis) as expen_assis, COUNT(*) as number FROM `log_expenditure` as log ".$where." group by assis_id)";
		$column = 'a.nick, t.*, a.name';
		$from = ' FROM assistant as a left join '.$t.' as t on t.assis_id =a.id where disable = 0 ';
		$col = array(array("nick","名称"),
 					 array("name","账号/电话"),	
					 array("number","咨询次数"),
					 array("expen_assis","提成"));
		$sort = array("String", "Number", "Number", "Number");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "助理详情", $arr);
	} else if ($t == "admin_visitor") {//////////////////////////////////
		$column = $Nomber.' as No, v.name, date_format(v.add_time, "%Y%m%d") as add_time, v.nick as vnick, s.nick as sex_nick, f.nick as from_nick, d.nick as dian_nick, t.nick as type_nick, a.nick as anick, c.nick as cnick ';
		$from = ' FROM visitor as v left join xingbie as s on s.name=v.sex left join laiyuan as f on f.name=v.fang_from left join dian as d on v.fang_dian=d.name left join leixing as t on t.name=v.fang_type left join assistant as a on a.id=v.assis_id left join consultant as c on c.id=v.consul_id where '.create_date_where($_POST, "v.add_time");
		$col = array(array("No","编号"),
 					 array("name","电话"),
					 array("add_time","添加时间"),
					 array("vnick","名称"),
 					 array("sex_nick","性别"),
					 array("from_nick","来源"),
					 array("dian_nick","门店"),	
 					 array("type_nick","类型"),
					 array("anick","首问助理"),
					 array("cnick","系列咨询师"));
		$sort = array("Number", "Number", "Date", "String", "String", "String", "String", "String", "String", "String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "客户详情", $arr);
	} else if ($t == "admin_huifang") {//////////////////////////////////
		$column = $Nomber.' as No, v.name, v.xuqiu as _title_  ,v.nick as vnick, a.nick as anick, date_format(log.op_time, "%Y%m%d") as op_time, log.content  ';
		$from = ' FROM huifang as log left join visitor as v on v.id=log.visitor_id left join assistant as a on a.id=log.assis_op_id where '.create_date_where($_POST, "log.op_time");
		$col = array(array("op_time","操作时间"),
					 array("No","编号"),
 					 array("name","电话"),
					 array("vnick","名称"),
 					 array("content","回访记录"),
					 array("anick","操作"));
		$sort = array("Date", "Number", "Number", "String", "String", "String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "回访详情", $arr);		
	} else if ($t == "admin_bushi") {//////////////////////////////////
		$column = $Nomber.' as No, log.comment as _title_, v.name, v.nick as vnick, a.nick as anick, c.nick as cnick, date_format(log.op_time, "%Y%m%d") as op_time, log.shichang';	
		$from = ' FROM log_bushi as log left join visitor as v on v.id=log.visitor_id left join assistant as a on a.id=log.assis_op_id left join consultant as c on c.id=log.consul_id where '.create_date_where($_POST, "log.op_time");
		$col = array(array("op_time","操作时间"),
					 array("No","编号"),
 					 array("name","电话"),
					 array("vnick","名称"),
 					 array("shichang","时长"),
 					 array("cnick","咨询师"),			 
					 array("anick","操作"));
		$sort = array("Date", "Number", "Number", "String", "Number", "String", "String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "补时详情", $arr);
	} else if ($t == "admin_yuyue") {//////////////////////////////////	
		$column = " * ";
		$sql_join = 'select '.$Nomber.' as No, v.id as vid, y.finish, a.nick as anick, v.name as vname, v.nick as vnick, date_format(y.op_time, "%Y%m%d") as op_time, left(s.nick, 5) as yuyue_time, y.date, c.nick as cnick, r.nick as roomName,d.nick as dianName from `yuyue` as y join consultant as c on y.consul_id=c.id join shijianduan as s on y.shijiand_id=s.id join room as r on y.roomNo=r.No join dian as d on r.dian=d.name join visitor as v on v.id=y.visitor_id join assistant as a on a.id=y.assis_op_id';
		$from = ' FROM ('.$sql_join.' where '.create_date_where($_POST, "y.op_time").' group by date, shijiand_id order by shijiand_id asc) a group by date,vid order by op_time asc ';
		$col = array(array("op_time","操作时间"),
					 array("No","编号"),
 					 array("vname","电话"),
					 array("vnick","名称"),
 					 array("date","预约日期"),
					 array("yuyue_time","开始时间"),
					 array("dianName","门店"),
					 array("roomName","房间"),	
 					 array("cnick","咨询师"),			 
					 array("anick","操作"));
		$sort = array("Date", "Number", "Number", "String", "Date",  "String", "String", "String","String","String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "预约详情", $arr);
		$t = $db->rawQuery('SELECT  count(*) as c from (select * '.$from.') b');
		if (!$db->count == 0) {
			$arr["result"]['c'] = $t[0]['c'];
		}	
	} else if ($t == "admin_zldenglu") {//////////////////////////////////				
		$column = 'l.login_nick, l.login_time as login_time, l.login_ip as content, t.nick as denglu';
		$m = LEVEL_ADMIN;
		$from = ' FROM log_assis_login as l left join assistant as a on a.id=l.assis_id left join login_type as t on t.name=l.type where '.create_date_where($_POST, "l.login_time").' and a.level <'.$m.' order by l.login_time DESC ';
		$col = array(array("login_time","时间"),
					 array("login_nick","助理"),
					 array("content","IP"),
					 array("denglu","类型"));
		$sort = array("Date", "String", "String", "String");
		admin_set_sql($db, $column, $from, $limit, $col, $sort, "登陆明细", $arr);
	} else {		
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;		
	}
	$arr['success'] = 1;
}
elseif ($action == 'zijinsr') {	//本月合计 
	if (!detect_key_exists(array("date"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	$Nomber = '(select LPAD(count(*),3,"0") from `visitor` as v1 where date_format(v1.add_time, "%Y%m%d")=date_format(v.add_time, "%Y%m%d") and v1.add_time<=v.add_time) as num';
	$sql = 'select '.$Nomber.',date_format(v.add_time, "%y%m%d") as add_time, v.fang_dian, optime, cash, a.nick from log_income as l join visitor as v on l.visitor_name=v.name join assistant as a on l.op_name=a.name';
	
	$arr['success'] = 1;
	
} ////////////////////////////////////////////////////////////////////////////////////////////
else {
	$arr['msg'] = '非法请求';
}

ret:
unset($db);
echo json_encode($arr); //输出json数据
?>