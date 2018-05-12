<?php
include_once("lib/session.php");
include_once("lib/config.php");
include_once("lib/function.php");
include_once("lib/MysqliDb.php");
header('Content-Type: application/json');
$db = new MysqliDb ($mySQLserver, $mySQLuser, $mySQLpass, $mySQLdb, $mySQLPort);
$now_time =  date("Y-m-d H:i:s", get_now()); 
if( !count($_GET) && !detect_key_exists(array("action"), $_GET)) {	
	$arr['msg'] = '非法请求!';
	goto ret;
}
$action = $_GET['action'];
$arr['success'] = 0;
if (($action != 'login') && (!isset($_SESSION['name']))) {
	$arr['success'] = -1;
	$arr['msg'] = '登陆超时,请重新登陆';
    goto ret;
}

if ($action == 'login') {  //登录 
	if (!detect_key_exists(array("user", "pass", "key"), $_POST)){
		$arr['msg'] = '非法请求';
		goto ret;
	}
    $user = stripslashes(trim($_POST['user'])); 
    $pass = stripslashes(trim($_POST['pass'])); 
    if ($user == "" || !preg_match("/^[0-9]+$/",$user) ) { 
        $arr['msg'] = '用户名不能为空或非法'; 
		goto ret;
    } 

    if ($pass == "") { 
        $arr['msg'] = '密码不能为空'; 
		goto ret;
    }
	
	/*if (($_POST['key'] != 'huiming_key')) {
		$arr['msg'] = '未授权'; 
		echo json_encode($arr); //输出json数据
        exit; 
	} /*  */
	
	$row = $db->where ('name', $user)
			  ->where ('disable', 0)	//激活的
			  ->getOne ('assistant');
			  
    $ps =$row ? strcmp($pass, $row['password']) == 0: FALSE; 
    if ($ps && update_login($db, 'assistant', $row, $arr)) { 
		if (is_admin()) {			
			$arr['ref'] = 'admin.php';
		} else {
			$arr['ref'] = 'home.php';
		}
    } else {
        $arr['msg'] = '用户名或密码错误！'; 
    } 
} //############################################################
elseif ($action == 'logout') {  //退出 
	update_logout($db);
	unset($_SESSION); 
	session_destroy(); 
	$arr['success'] = 1; 
	$arr['msg'] = '退出成功！'; 
}//############################################################
elseif ($action == 'changeps') {	//添加 修改密码
	if (!detect_key_exists(array("password", "password1", "passwold"), $_POST)){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	unset($_POST['password1']);
	$row = $db->where ('id', $_SESSION['id'])
			  ->where ('disable', 0)	//激活的
			  ->getOne ('assistant');
	
	if (strcmp($_POST['passwold'], $row['password']) != 0) {
		$arr['success'] = 1; 
		$arr['msg'] = '原始密码错误！';
		goto ret;
	}
	unset($_POST['passwold']);
	$rs = $db
		->where('id', $_SESSION['id'])
		->update ('assistant', $_POST);
	if ($rs) {		
		$arr['success'] = 1; 
		$arr['msg'] = '修改成功！'; 
	} else { 
        $arr['msg'] = '修改失败'; 
    }		
}//############################################################
elseif ($action == 'add') {  //添加用户 
	$arr['msg'] = '添加失败';
	if (!detect_key_exists(array("name","nick","sex","xuqiu","fang_from","fang_dian","is_zidai"), $_POST)){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$db->where("name", $_POST['name'])
	   ->get('visitor',null,"id");
	if ($db->count != 0) {
		$arr['msg'] = "电话和其他客户重复";
		goto ret;
	}
	if ($_POST['is_zidai'] == 0) {	
		$_POST['assis_id'] = $_SESSION['id'];
	} else {
		$_POST['assis_id'] = NULL;
		$_POST['consul_id'] = $_POST['is_zidai'];
		$_POST['is_zidai'] = 1;
	}
	$_POST['add_time'] = $now_time;
	$_POST['op_time'] = $now_time;
	
	$rs = $db->insert ('visitor', $_POST);
	if ($rs) {
		$arr['msg'] = '添加成功！'; 
	} else {
		$arr['msg'] = '添加失败';
	}
} elseif ($action == 'addb') {
	$arr['msg'] = '添加失败';
	if (!detect_key_exists(array("name","nick","consul_id","xuqiu"), $_POST)){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}	
	
	$db->where("name", $_POST['name'])
	   ->get('visitor',null,"id");
	if ($db->count != 0) {
		$arr['msg'] = "电话和其他客户重复";
		goto ret;
	}

	$_POST['assis_id'] = $_SESSION['id'];
	$_POST['add_time'] = $now_time;
	$_POST['op_time'] = $now_time;
	$_POST['fang_type'] = 'B';

	$rs = $db->insert ('visitor', $_POST);
	if ($rs) {
		$arr['msg'] = '添加成功！'; 
	} else {
		$arr['msg'] = '添加失败';
	}
		
} elseif ($action == 'addc') {
	$arr['msg'] = '添加失败';
	if (!detect_key_exists(array("name","nick", "xuqiu"), $_POST)){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	
	$db->where("name", $_POST['name'])
	   ->get('visitor',null,"id");
	if ($db->count != 0) {
		$arr['msg'] = "电话和其他客户重复";
		goto ret;
	}

	$_POST['assis_id'] = $_SESSION['id'];
	$_POST['add_time'] = $now_time;
	$_POST['op_time'] = $now_time;
	$_POST['fang_type'] = 'C';	
	
	$rs = $db->insert ('visitor', $_POST);
	if ($rs) {
		$arr['msg'] = '添加成功！'; 
	} else {
		$arr['msg'] = '添加失败';
	}
} elseif ($action == 'add_get_consul') {  //添加获得咨询师列表
	$table = 'consultant';
	$arr['result'] = $db->rawQuery('SELECT id, nick FROM '.$table.' WHERE disable = 0');
	$arr['success'] = 1;
}//############################################################
elseif ($action == 'get_visitor') {  //获得客户
	//$arr['msg'] = '查询失败';
	$search = '';
	$search.= create_like_sql("v", $_POST, "nick", "phone");
	$Nomber = 'concat(date_format(v.add_time, "%y%m%d"), v.fang_dian, (select LPAD(count(*),3,"0") from `visitor` as v1 where date_format(v1.add_time, "%Y%m%d")=date_format(v.add_time, "%Y%m%d") and v1.add_time<=v.add_time))';
	if (detect_key_exists(array("bianhao"), $_POST) && $_POST['bianhao'] != '') {
		$search.= " and (".$Nomber." like '%".$_POST['bianhao']."%')";
	}
	$column = 'a.nick as assis_name, v.id, c.nick as consu_name, '.$Nomber.' as No, v.name,v.nick,x.nick as sex,v.fang_type,v.xuqiu,l.nick as fang_from';
	$vis_type = 'A';
	if (detect_key_exists(array("type"), $_GET)) {
		if ($_GET['type'] == VISITOR_TYPE_QIYE) {
			$vis_type = 'C';
		} else if ($_GET['type'] == VISITOR_TYPE_XIAOZU) {
			$vis_type = 'B';
		}
	}
	$from = ' FROM visitor as v join xingbie as x on v.sex=x.name join laiyuan as l on v.fang_from=l.name left join consultant as c on c.id=v.consul_id left join assistant as a on v.assis_id=a.id where v.fang_type=\''.$vis_type.'\' '.$search.' order by v.op_time DESC ';

	$rs = $db->rawQuery('SELECT  '.$column.$from.$limit);	
	if (!$db->count == 0) {
		$arr['have_data'] = 1; 
	}
	$t = $db->rawQuery('SELECT  count(*) as c'.$from);
	$arr['count'] = $t[0]['c'];
	$arr['op_name'] = $_SESSION["nick"];
	$arr['success'] = 1; 
	$arr["result"] = $rs;
} elseif ($action == 'get_qiye_xiaozu') {  //获得客户
	//$arr['msg'] = '查询失败';
	$search = '';
	$search.= create_like_sql("v", $_POST, "nick", "phone");
	$column = 'a.nick as assis_name, v.id, c.nick as consu_name, v.name,v.nick, v.xuqiu';
	$vis_type = 'U';
	if (detect_key_exists(array("type"), $_GET)) {
		if ($_GET['type'] == VISITOR_TYPE_QIYE) {
			$vis_type = 'C';
		} else if ($_GET['type'] == VISITOR_TYPE_XIAOZU) {
			$vis_type = 'B';
		}
	}
	$from = ' FROM visitor as v left join consultant as c on c.id=v.consul_id left join assistant as a on v.assis_id=a.id where v.fang_type=\''.$vis_type.'\' '.$search.' order by v.op_time DESC ';

	$rs = $db->rawQuery('SELECT  '.$column.$from.$limit);	
	if (!$db->count == 0) {
		$arr['have_data'] = 1; 
	}
	$t = $db->rawQuery('SELECT  count(*) as c'.$from);
	$arr['count'] = $t[0]['c'];
	$arr['op_name'] = $_SESSION["nick"];
	$arr['success'] = 1; 
	$arr["result"] = $rs;	
}//############################################################
elseif ($action == 'get_select') {  //获得各种表
	$s = TRUE;
	$rs["dian"] = $db->get('dian');
	if ($db->count == 0) $s = FALSE;
	$rs["laiyuan"] = $db->get('laiyuan');
	if ($db->count == 0) $s = FALSE;
	$rs["leixing"] = $db->get('leixing');
	if ($db->count == 0) $s = FALSE;
	$table = 'consultant';
	$rs[$table] = $db->rawQuery('SELECT id, nick FROM '.$table.' WHERE disable = 0');
	if ($db->count == 0) $s = FALSE;
	if (!$s) {
		$arr['msg'] = "读取数据库错误";
	} else {
		$arr['success'] = 1; 
		$arr["result"] = $rs;
	}
}//############################################################
elseif ($action == 'chakan_ticheng') {  //查看提成	
	if (!detect_key_exists(array("tic_ps"), $_POST)) {
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 		
	}
	$arr["result"]["hgo"] = 0;	
	$pass = $db->where ('id', $_SESSION['id'])
			  ->where ('disable', 0)	//激活的
			  ->getValue ('assistant', 'password');
	if (strcmp($pass , $_POST['tic_ps']) == 0) {
		$arr["result"]["hgo"] = 1;
		$v = array('ticheng_time' => $now_time);
		$db->where ('id', $_SESSION['id'])
		   ->update('assistant', $v);		
		$arr["result"]["href"] = "#zhuliticheng_section";	
		$arr['msg'] = '查看提成';	
	} else {
		$arr['msg'] = '密码错误';
	}
	$arr['success'] = 1; 
}//############################################################
elseif ($action == 'get_yuyue') {  //获得预约数据
	if (!detect_key_exists(array("vid"), $_GET) ||
		!detect_key_exists(array("consultant"), $_POST) ||
		!detect_key_exists(array("mendian"), $_POST) ||
		!detect_key_exists(array("date"), $_POST) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	
	if ($_POST["date"] != "" && $_POST["mendian"] != "") {
		$arr["result"] = get_yuyue($db, $_POST["date"], $_POST["mendian"], $_GET["vid"], $_POST["consultant"]);
	}
	
	if ($_POST["date"] != "") {
		$t = $db->where("id", $_GET["vid"])
				->getValue("visitor", "consul_id");
		$arr["result"]["consultant"] = get_consultant($db, $_POST["date"], $t, $arr["result"]["select_cons_nick"]);
		$t = $db->where("id", $_GET["vid"])
				->getValue("visitor", "consul_id");
				
		$arr["result"]["select_cons_id"] = $t;
	}	
	$arr['success'] = 1;
/*}
elseif ($action == 'get_consultant_date') {  //获得咨询师
	if (!detect_key_exists(array("name"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$s = TRUE;
	$rs = get_counseling($db , $_GET['name']);
	if ($rs == FALSE) $s = FALSE;
	$rs['dian'] = $db->get('dian', null, array("id", "nick") );
	if ($s == FALSE) {
		$arr['msg'] = "读取数据库错误";
	} else {
		$arr['success'] = 1; 
		$arr["result"] = $rs;
	}*/
}elseif ($action == 'get_dian') {  //获得门店
	if (!detect_key_exists(array("vid"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$s = TRUE;
	$rs['dian'] = $db->get('dian', null, array("name", "nick") );
	$cols = array ("name", "nick", "consul_id", "dingjin", "bushi", "shichang");
	$rs['visitor'] = $db->where('id', $_GET["vid"])
			 		    ->getOne('visitor', $cols);	
	$rs['select_cons_id'] = $rs['visitor']['consul_id'];
	if ($rs['select_cons_id'] != '') 
			$rs['select_cons_nick'] = $db->where('id', $rs['select_cons_id'])
			 		    				 ->getValue('consultant', "nick");
	if ($s == FALSE) {
		$arr['msg'] = "读取数据库错误";
	} else {
		$arr['success'] = 1; 
		$arr["result"] = $rs;
	}
}
elseif ($action == 'yuyue') {  //预约  ??//时间检查
	if (!detect_key_exists(array("vid"), $_GET) || 
	    !detect_key_exists(array("consultant"), $_POST) || 
		!detect_key_exists(array("date"), $_POST) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$c = 0; $d_c = 0; $r = '';
	foreach($_POST as  $key => $value) {
		if ($key == 'consultant' || $key == 'date' || $key == 'mendian') continue;
		$tarr = explode('-',$key);
		if (count($tarr) != 2) {
			$arr['msg'] = '非法请求';
			echo json_encode($arr); //输出json数据
       	 	exit; 
		}
		$ds = $db->where('date',  $_POST['date'])
			->where('shijiand_id', $tarr[0])
			->where('(roomNo="'.$tarr[1].'" or visitor_id="'.$_GET['vid'].'" or consul_id="'.$_POST['consultant'].'")')
			->get ('yuyue');
		if ($db->count == 1) {
    		foreach ($ds as $d) {
				if( $d['visitor_id'] == $_GET['vid'] && empty($d['confirm_time'])) {
					//未确认则可取消
					if ($value == '0') { //取消
						$id = $db->where('date',  $_POST['date'])
							->where('shijiand_id', $tarr[0])
							->where('roomNo', $tarr[1])
							->where('consul_id', $_POST['consultant'])
							->where('visitor_id', $_GET['vid'])						
							->delete('yuyue');
						if ($id) {
							$d_c = $d_c + 1;
							update_visitor($db, $_GET['vid']);	
						}
					}
				}
    		}
		} else if ($db->count == 0) {
			if ($value == '3') {
				$data = array (
						"date" => $_POST['date'],
						"shijiand_id" => $tarr[0],
						"roomNo" => $tarr[1],
						"visitor_id" => $_GET['vid'],
						"consul_id" => $_POST['consultant'],
						"assis_op_id" => $_SESSION['id'],												
						"op_time" => $now_time
						);	
				$db->insert ('yuyue', $data);
				update_visitor($db, $_GET['vid']);
				//$db->where('name', $_GET['name'])
				//   ->update ('visitor', array("counseling_name" => $_POST['consultant']));
				$c = $c + 1;
				//获得咨询师信息
				$t = $db->where('id',  $_POST['consultant'])
				   		->getOne('consultant', array('name','nick'));
				$r['consultant_nick'] = $t['nick']; 
				$r['consultant_name'] = $t['name']; 
				//$r['visitor']['counseling_name'] = $_POST['consultant']; 
			}
		} else {
			//$arr['debug'] = 'date:'.$_POST['date'].'|shijiand_id:'.$tarr[0].'|roomNo="'.$tarr[1].'" or visitor_name="'.$_GET['name'].'" or consultant_name="'.$_POST['consultant'].'")';
			//$arr['error'] = '数据有误';
		}
	}
	$arr['success'] = 1; 
	$arr["result"] = $r;
	$arr["op"] = $c;
	$arr['msg'] = '更新成功,新增'.$c.'个数据，删除'.$d_c.'个数据';
	$arr['d_c'] = $d_c;
} //############################################################
elseif ($action == 'get_consultant_worktype') {	//咨询师列表
	$table = 'consultant';
	$rs[$table] = $db->rawQuery('SELECT id, nick FROM '.$table.' WHERE disable = 0');
	$rs["worktype"] = $db->rawQuery('SELECT name, nick FROM consul_work_type');	
	$arr['success'] = 1; 
	$arr["result"] = $rs;
}
elseif ($action == 'get_consultant_paiban') {	//咨询师排班
	if (!detect_key_exists(array("cons_id","year", "month"), $_GET)) {
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	$rs = $db->rawQuery("SELECT consul_work.date,consul_work.type,consul_work_type.nick as type_name FROM `consul_work`,`consul_work_type` where consul_work.type=consul_work_type.name and consul_work.cons_id='".$_GET["cons_id"]."' and date_format(date, '%Y%m ')= '".$_GET["year"].$_GET["month"]."'");
	$arr["result"] = $rs;
	$arr['success'] = 1;
}
elseif ($action == 'paiban_save') {	//咨询师排班保存
	if (!detect_key_exists(array("consultant"), $_POST)) {
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	if (!is_manager()) {	//排班店长检测
		$arr['success'] = -2;
		goto ret;
	}
	
	if ($_POST['consultant'] == "") {
		$arr['success'] = 1; 
		$arr['msg'] = '请选择咨询师';
		goto ret;
	}
	$now_time_date =  date("Y-m-d", get_now());
	$c = 0;$d_c = 0;
	foreach ($_POST as  $key => $value) {
		if ($key == 'consultant' ) continue;
		if ($value == '' || $value == '0') {
			$rs = $db->where('cons_id', $_POST['consultant'])
			   ->where('date', $key)
			   ->delete('consul_work');
			if ($rs) {
				$d_c = $d_c + 1;   
			}
			continue;
		}
		$diff = date_diff(date_create($now_time_date), date_create($key));
		$nd =intval($diff->format('%R%d'));
		if ($nd < 0) continue;
		$nm =intval($diff->format('%R%m'));
		if ($nm > 2) continue;
				
		$v = array ('cons_id' => $_POST['consultant'],
					'date'=> $key,
					'type'=> $value);
		$db->where('cons_id', $_POST['consultant'])
		   ->where('date', $key)
		   ->getOne ("consul_work");
		if ($db->count > 0) {
			$rs = $db->where('cons_id', $_POST['consultant'])
		   		     ->where('date', $key)
				     ->update('consul_work', $v);	
		} else {
			$rs = $db->insert ('consul_work', $v);
		}
		if ($rs) {
			$c = $c + 1;
		}
	}
	$arr['success'] = 1; 
	$arr["op"] = $c;
	$arr['d_c'] = $d_c;
	$arr['msg'] = '更新成功,更新'.$c.'个数据，删除'.$d_c.'个数据';
} //############################################################
elseif ($action == 'get_assistant_dian') {	//咨询师列表店名
	$table = 'assistant';
	$l = LEVEL_ADMIN;
	$rs[$table] = $db->rawQuery('SELECT id, nick FROM '.$table.' WHERE disable = 0 and level<'.$l);
	$rs["dian"] = $db->rawQuery('SELECT name, nick FROM `dian`');
	$arr['success'] = 1;
	$arr["result"] = $rs;
}//############################################################
elseif ($action == 'get_edit') {  //修改用户信息
	if (!detect_key_exists(array("vid"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$s = TRUE;
	$cols = Array ("id", "name", "nick", "xuqiu", "bakphone", "consul_id");
	$rs['visitor'] = $db->where('id', $_GET['vid'])
			 		    ->getOne('visitor', $cols);	
	if ($db->count == 0) $s = FALSE;							
	$rs['consultant'] = $db->where('disable = 0')
							->get('consultant', null, array("id", "nick"));
	if ($db->count == 0) $s = FALSE;							
	if ($s == FALSE) {
		$arr['msg'] = "读取数据库错误";
	} else {
		$arr['success'] = 1; 
		$arr["result"] = $rs;
	}
}//############################################################
elseif ($action == 'get_jiaofei') {  //获得缴费信息
	if (!detect_key_exists(array("vid", "date"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	$rs['start_time'] = '';
	$t_yuyue = $db->where('date', $_GET["date"])
					->where('visitor_id', $_GET["vid"])
					->where("finish", 0)
					->orderBy("op_time", "Desc")
					->getOne('yuyue', array('consul_id','start_time'));	
	$cons_id = $t_yuyue['consul_id'];
	$start_time = $t_yuyue['start_time'];	

	$sql = "select u.nick, u.id, a.price from `consultant` as u left join consul_level_price a on u.level = a.level where u.id='".$cons_id."'";	
	$t = $db->rawQuery($sql);
	$rs['consultant'] = '';
	if ($t) $rs['consultant'] = $t[0];

	$cols = Array ("name", "nick", "dingjin", "shichang", "bushi", "consul_id", "qianfei");
	$rs['visitor'] = $db->where('id', $_GET['vid'])
			 		    ->getOne('visitor', $cols);
	
	if (intval($rs['visitor']["qianfei"]) > 0) {
		$rs['visitor']["shichang"] = intval($rs['visitor']["shichang"] *0.9 - ($rs['visitor']["qianfei"]* 1.2 * 60) / $rs['consultant']["price"]);
	}
	
	if (!empty($start_time)) $rs['start_time'] = date("H:i", strtotime($start_time));
	$rs['end_time'] = date("H:i", get_now());
	$arr['success'] = 1; 
	$arr["result"] = $rs;
}//--------------------------------
elseif ($action == 'get_price') {  //获得缴费信息
	if (!detect_key_exists(array("consul_id", "price"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
		exit;
	}
	$pri = "";
	switch(intval($_GET["price"])) {
		case 10:
			$pri = "price_10";
			break;
		case 20:
			$pri = "price_30";
			break;
		case 30:
			$pri = "price_30";
			break;		
		case 50:
			$pri = "price_50";
			break;		
		case 100:
			$pri = "price_100";
			break;		
		default: 
			break;
	}	
	if ($pri != "") {
		$pri = $db->join("consul_level_price p", "c.level=p.level", "LEFT")
				->where("c.id", $_GET["consul_id"])
				->getValue("consultant c", $pri);
	}
	
	$arr['success'] = 1; 
	$arr["result"] = $pri;
} //=================保存缴费金额
elseif ($action == 'save_jiaofei_dc' || $action == 'save_jiaofei_xl') { 
	if (!detect_key_exists(array("vid","consul_id","date"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	if (date("Y-m-d", get_now()) != $_GET["date"]) {
		$now_time = $_GET["date"];
	}
	$msg = "";
	$dingj = 0; $benci_dingjin = 0;
	$bushi = 0;	$benci_bushi = 0;
	$finish = false;
	//处理订金和补时	
	$cols = Array ("dingjin", "shichang", "bushi");
	$visitor_db = $db->where('id', $_GET['vid'])
			 		 ->getOne('visitor', $cols);
	if ($visitor_db) {
		//取订金金额，保留两位		
		$dingj = floatval($visitor_db["dingjin"]);
		//补时
		$bushi = intval($visitor_db["bushi"]);		
	}
	
	if (detect_key_exists(array("dingjin"), $_GET)) {
		//取订金金额，保留两位		
		$benci_dingjin = floatval($_GET["dingjin"]);
	}
	
	if (detect_key_exists(array("bushi"), $_GET)) {
		//补时，整数		
		$benci_bushi = intval($_GET["bushi"]);
	}	
	
	$vis_id = $_GET["vid"];
	$cons_id  = $_GET["consul_id"];
	$op_id =  $_SESSION['id'];
	$assi_id = $db->where("id", $_GET["vid"])
				->getValue("visitor", "assis_id");

	if (($action == 'save_jiaofei_dc') && $_POST["danci_sc"] != "" && intval($_POST["danci_sc"]) > 0) { 		// 单次缴费
		$dc_sc = intval($_POST["danci_sc"]);
		if ($dc_sc <= 0) goto jiaofei;
		$finish = true;
		tz_shichang($dc_sc, $bushi, $msg);	
		$dc_dj = 0;
		if (detect_key_exists(array("dc_dj"), $_GET) && $_GET["dc_dj"] != "") {
			$dc_dj = floatval($_GET["dc_dj"]);
		}
		$jine = ($dc_sc * $dc_dj ) / 60.0;
		tz_jin_e($jine, $dingj, $msg);
		if ($jine <=0) goto jiaofei;
		$danci_heji = 0;
		if (detect_key_exists(array("danci_heji"), $_POST) && $_POST["danci_heji"] != "") {
			$danci_heji = floatval($_POST["danci_heji"]);
		}		
		if ($benci_dingjin > 0) {
			$danci_heji = $danci_heji - $benci_dingjin;
		}
		//echo $jine;echo "|";echo $danci_heji;exit;
		if (abs($jine - $danci_heji) > 1) {
			$msg = "资金验证错误！ ";
			goto jiaofei;
		}
		if ($dingj > 0) {
			$danci_heji += $dingj;
		}
		//缴费收入
		$id = income_log($db, $now_time, $vis_id, $cons_id ,$assi_id, $op_id, $danci_heji, $dc_sc, TYPE_JIAOFEI_DC, $msg);
		if ($id > 0) {
			//咨询记录
			$msg .=expenditure_log($db, $now_time, $vis_id, $cons_id ,$assi_id, $op_id, $_POST["time_start"], $_POST["time_end"], $dc_sc, $id);
		}
	}
	else if (($action == 'save_jiaofei_xl')) { // 系类缴费
		$xilie_heji = 0;
		if (detect_key_exists(array("xilie_type"), $_POST) &&
		        $_POST["xilie_type"] != "" && intval($_POST["xilie_type"]) > 0 ) {
			$xilie_save_sc = 60.0 * intval($_POST["xilie_type"]);
			if ($xilie_save_sc > 0) {
				$xilie_danjia = 0;
				if (detect_key_exists(array("xl_dj"), $_GET) && $_GET["xl_dj"] != "") {
					$xilie_danjia = floatval($_GET["xl_dj"]);
				}
				$jine = ($xilie_save_sc * $xilie_danjia ) / 60.0;
				tz_jin_e($jine, $dingj, $msg);
				if ($jine <=0) goto jiaofei;
				if ($_POST["xilie_heji"] != "") {
					$xilie_heji = floatval($_POST["xilie_heji"]);
				}
				
				if ($benci_dingjin > 0) {
					$xilie_heji = $xilie_heji - $benci_dingjin;
				}
								
				if (abs($jine - $xilie_heji) > 1) {
					$msg = "资金验证错误！ ";
					goto jiaofei;
				}

				if ($dingj > 0) {
					$xilie_heji += $dingj;
				}
				//缴费收入
				income_log($db, $now_time, $vis_id, $cons_id ,$assi_id, $op_id, $xilie_heji, $xilie_save_sc, TYPE_JIAOFEI_XL, $msg);
			}
		}
		if ($_POST["xilie_fenqi"] != "" && floatval($_POST["xilie_fenqi"]) > 0) {
			if ($xilie_heji > 0) {
				$qianfei = $xilie_heji -  floatval($_POST["xilie_fenqi"]);
				if ($qianfei > 0) {
					$v = array("qianfei" => $qianfei);
					$db->where("id", $vis_id)
					   ->update("visitor", $v);
					$msg.= "分期！ ";
				}
			} else {
				$vis_qianfei = 	$db->where("id", $vis_id)
					   			   ->getValue("visitor", "qianfei");
				$fenqi = floatval($_POST["xilie_fenqi"]);
				if ($fenqi > $vis_qianfei) $fenqi = $vis_qianfei;
				$qianfei = $vis_qianfei - $fenqi;
				$v = array("qianfei" => $qianfei);
				$db->where("id", $vis_id)
				   ->update("visitor", $v);
				$msg.= "分期付款";
			}
		}		
		if ($_POST["xilie_sc"] != "" && intval($_POST["xilie_sc"]) > 0) {
			$xile_zxsc = intval($_POST["xilie_sc"]);
			if ($xile_zxsc <= 0) goto jiaofei;
			$finish = true;
			tz_shichang($xile_zxsc, $bushi, $msg);
			$msg .=expenditure_log($db, $now_time, $vis_id, $cons_id ,$assi_id, $op_id, $_POST["time_start"], $_POST["time_end"], $xile_zxsc);
		}
	} else {
		$dingj = 0;
		$bushi = 0;
	}
		
jiaofei:	
	if ($benci_dingjin > 0) {
		//取订金金额，保留两位		
		save_dingj($db, $now_time, $vis_id, $op_id, $benci_dingjin);		
		$msg .= '保存订金成功！ ';
	}
	
	if ($benci_bushi > 0) {
		save_bushi($db, $now_time, $vis_id, $cons_id, $op_id, $benci_bushi);					
		$msg .= '保存补时成功！ ';
	}
	save_ding_bu($db, $now_time, $vis_id, $cons_id, $op_id, $dingj, $bushi);
	
	if ($finish)
		jiaofei_ok($db, $_GET["date"], $vis_id, $cons_id);
	
	//成功
	//jiaofei_ok($db);
	$arr['msg'] = $msg;	
	$arr['success'] = 1;
} elseif ($action == 'save_jiaofei_cx') {  //撤销预约
	if (!detect_key_exists(array("vid","consul_id","date"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	$msg = "";
	$bushi = 0;	
	//处理订金和补时	
	$cols = Array ("shichang", "bushi");
	$visitor_db = $db->where('id', $_GET['vid'])
			 		 ->getOne('visitor', $cols);
	if ($visitor_db) {
		//补时
		$bushi = intval($visitor_db["bushi"]);
	}
	
	$msg = "";
	$vis_id = $_GET["vid"];
	$cons_id  = $_GET["consul_id"];
	$op_id =  $_SESSION['id'];
	$assi_id = $db->where("id", $_GET["vid"])
				->getValue("visitor", "assis_id");	

	if ($_POST["xilie_sc"] != "" && intval($_POST["xilie_sc"]) > 0) {
		$xile_zxsc = intval($_POST["xilie_sc"]);
			if ($xile_zxsc > 0) {
				tz_shichang($xile_zxsc, $bushi, $msg);  //撤销
				$msg .=expenditure_log($db, $now_time, $vis_id, $cons_id ,$assi_id, $op_id, "", "", $xile_zxsc, -1, true);
			}
	}
	save_ding_bu($db, $now_time,$vis_id,$cons_id,$op_id, 0, $bushi);		
	$arr['msg'] = $msg;	
	$arr['success'] = 1;
} elseif ($action == 'tuihuan_dingjin-失效') {  //--------------退还订金
	/*if (!detect_key_exists(array("name","jine"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	
	//取订退还金额，保留两位		
	$dingj = -floatval(sprintf("%.2f", $_GET["jine"]));	
	if(save_dingj($db, $now_time, $_GET["vid"], $_SESSION['id'], $dingj)) 		
		$arr['msg'] = '退还订金成功';
	else
		$arr['msg'] = '操作失败';
	$arr['success'] = 1;*/
}//############################################################
elseif ($action == 'get_one_visitor') {
	$cols = Array ("name", "nick");
	$sql_where = "";
	if (detect_key_exists(array("vid"), $_GET) && $_GET['vid'] != ''){
		$rs['visitor'] = $db->where('id', $_GET['vid'])
							->getOne('visitor', $cols);	
		if($db->count != 0) {
			$sql_where = "and visitor_id=".$_GET['vid'];
		}
	}

	$now_time2 =  date("Y-m-d", strtotime('-5  week')); //允许5周之前的
	$sql_join = 'select a.nick as anick,v.id as vid,v.name, v.nick, y.id, y.op_time, left(s.nick, 5) as yuyue_time, y.date, y.confirm_time, y.start_time, c.nick as consultant_name, c.id as consultant_id, r.nick as roomName,d.nick as dianName from `yuyue` as y join consultant as c on y.consul_id=c.id join shijianduan as s on y.shijiand_id=s.id join room as r on y.roomNo=r.No join dian as d on r.dian=d.name join visitor as v on v.id=y.visitor_id join assistant as a on a.id=y.assis_op_id';
	$sql = 'SELECT * FROM ('.$sql_join.' where finish=0 '.$sql_where.' and date>="'.$now_time2.'" group by date, shijiand_id order by shijiand_id asc) a group by date,vid order by op_time asc '.$limit;
	$rs['yuyue'] = $db->rawQuery($sql); 
	$arr['success'] = 1; 
	$arr["result"] = $rs;
} elseif ($action == 'confirm_yuyue') {
	if (!detect_key_exists(array("vid"), $_GET) || !detect_key_exists(array("date"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$v = array("confirm_time" => $now_time);				
	$r = $db->where('date',  $_GET["date"])
			->where('visitor_id', $_GET["vid"])
			->where("finish", 0)
			->update('yuyue', $v);
	if (!$r) {
		$arr['msg'] = "读取数据库错误";
	} else {
		update_visitor($db, $_GET["vid"]);
		$arr['success'] = 1;
		$arr['time'] = $now_time;
		$arr['msg'] = "确认成功！";
	}
}elseif ($action == 'delet_yuyue') {
	if (!detect_key_exists(array("vid"), $_GET) || !detect_key_exists(array("date"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$r = $db->where('date',  $_GET["date"])
	   		->where('visitor_id', $_GET["vid"])
			->where("finish", 0)	   
	   		->delete('yuyue');
	if (!$r) {
		$arr['msg'] = "读取数据库错误";
	} else {
		$arr['success'] = 1;
		$arr['msg'] = "删除成功！";
	}
}elseif ($action == 'get_one_visitor_shengyu') {
	if (!detect_key_exists(array("vid"), $_GET)) {
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 		
	}
	$cols = Array ("name", "nick", "bushi", "dingjin", "shichang");
	$rs = $db->where('id', $_GET['vid'])
			->getOne('visitor', $cols);				
	$arr['success'] = 1;
	$arr["result"] = $rs;	
}elseif ($action == 'yuyue_start') {
	if (!detect_key_exists(array("vid"), $_GET) || !detect_key_exists(array("date"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$v = array("start_time" => $now_time);
	$r = $db->where('date',  $_GET["date"])
			->where('visitor_id', $_GET["vid"])
			->where("finish", 0)
			->update('yuyue', $v);
	if (!$r) {
		$arr['msg'] = "读取数据库错误";
	} else {
		$arr['success'] = 1;
		$arr['msg'] = "咨询开始！";
	}
}//############################################################
elseif ($action == 'edit_visitor') {
	if (!detect_key_exists(array("vid"), $_GET) || !detect_key_exists(array("name","nick","xuqiu","consul_id"), $_POST) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$_POST['op_time'] = $now_time;
	if ($_POST['consul_id'] == '') $_POST['consul_id'] = NULL;
	if (!$db->where ('id', $_GET["vid"])
	   		->update ('visitor', $_POST)) {
		$arr['msg'] = "读取数据库错误";
	} else {
		update_visitor($db, $_GET['vid']);	
		$arr['success'] = 1;
		$arr['msg'] = "修改成功！";
	}
}//#############################################################
elseif ($action == 'get_jiaofeilog') {
	if (!detect_key_exists(array("vid"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$sql = "SELECT l.op_time,c.nick as cons_nick, a.nick as assi_nick, l.shichang, l.cash FROM `log_income` as l join `consultant` as c on l.consul_id=c.id join `assistant` as a on l.assis_id=a.id where l.visitor_id='".$_GET["vid"]."' order by l.op_time DESC ".$limit;
	$rs['jiaofei_log'] = $db->rawQuery($sql);
	$rs['visitor_nick'] = $db->where('id', $_GET['vid'])
			 		    ->getValue('visitor', "nick");	
	$arr['success'] = 1;
	$arr["result"] = $rs;
}//#############################################################
elseif ($action == 'get_zixunlog') {
	if (!detect_key_exists(array("vid"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$sql = "SELECT l.op_time,c.nick as cons_nick, a.nick as assi_nick, l.time_sc, substring(l.time_start,11) as time_start, substring(l.time_end, 11) as time_end FROM `log_expenditure` as l join `consultant` as c on l.consul_id=c.id join `assistant` as a on l.assis_id=a.id where l.visitor_id='".$_GET["vid"]."' order by l.op_time DESC ".$limit;
	$rs['zixun_log'] = $db->rawQuery($sql);
	$rs['visitor_nick'] = $db->where('id', $_GET['vid'])
			 		    ->getValue('visitor', "nick");	
	$arr['success'] = 1;
	$arr["result"] = $rs;	
}//#############################################################
elseif ($action == 'get_zhuliticheng') {
	$get_time = $db->where('id', $_SESSION['id'])
				   ->getValue('assistant','ticheng_time');
	if ($get_time == "" ||((strtotime($now_time) - strtotime($get_time)) > 60*5)) {
		$arr['success'] = -2;
		goto ret;
	}
	$rs = get_assis_expen($db, $_SESSION['id']);
	$sql = "SELECT l.op_time,c.nick as cons_nick, v.nick as visi_nick, l.time_sc, l.expen_assis as ticheng, l.expenditure_total as cash FROM `log_expenditure` as l left join `consultant` as c on l.consul_id=c.id left join `visitor` as v on l.visitor_id=v.id where l.assis_id='".$_SESSION["id"]."' order by l.op_time DESC ".$limit;
	$rs['zhuliticheng'] = $db->rawQuery($sql);
	$rs['nick'] = $_SESSION["nick"];
	$arr['success'] = 1;
	$arr["result"] = $rs;
}//############################################################
elseif ($action == 'get_profile') {
	//$rs = get_assis_expen($db, $_SESSION['id']);
	if (is_manager()) 
		$rs['m'] = 1;
	else 
		$rs['m'] = 0;
		
	$arr['success'] = 1;
	$arr["result"] = $rs;	
}//############################################################
elseif ($action == 'huifang') {
	if (!detect_key_exists(array("vid"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	$s = TRUE;
	$cols = array ("nick", "xuqiu");
	$rs['visitor'] = $db->where('id', $_GET['vid'])
			 		    ->getOne('visitor', $cols);	
	if ($db->count == 0) $s = FALSE;
	
	$cols = Array ("id", "op_time", "content");
	$rs['huifang'] = $db->rawQuery('SELECT id, op_time, content FROM huifang WHERE visitor_id='.$_GET['vid'].' order by op_time asc '.$limit);	
								
	if ($s == FALSE) {
		$arr['msg'] = "读取数据库错误";
	} else {
		$arr['success'] = 1; 
		$arr["result"] = $rs;
	}	
} elseif ($action == 'add_huifang') {
	if (!detect_key_exists(array("vid"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit; 
	}
	if ($_POST["content"] != "") {
		$v = array(
			'op_time' => $now_time,
			'assis_op_id' => $_SESSION['id'],
			'visitor_id' =>  $_GET["vid"],
			'content' => $_POST["content"] );
		$db->insert('huifang', $v);
	}
	$arr['success'] = 1;
	$arr['msg'] = "添加记录成功！";
} elseif ($action == 'delet_huifang') {
	if (!detect_key_exists(array("id"), $_GET) ){
		$arr['msg'] = '非法请求';
		echo json_encode($arr); //输出json数据
        exit;
	}
	$db->where('id', $_GET['id']);
	if ($db->delete('huifang')) {
		$arr['success'] = 1;
		$arr['msg'] = "删除成功！";
	}
} else {
	$arr['msg'] = '非法请求';
}

ret:
unset($db);
echo json_encode($arr); //输出json数据
?>