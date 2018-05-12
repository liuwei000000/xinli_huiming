<?php
define('PAGE_NUM', 20);
define('LEVEL_ADMIN', 10);
define('LEVEL_MANAGER', 5);
define('ZIXUN_ZIDAI', 1);
define('TYPE_LOGIN', 1);
define('TYPE_LOGOUT', 2);
//缴费类型， 注意和数据库一致
define('TYPE_JIAOFEI_DC', 1);
define('TYPE_JIAOFEI_XL', 2);
define('TYPE_JIAOFEI_XZ', 3);
define('TYPE_JIAOFEI_QY', 4);
define('TYPE_JIAOFEI_XL_BJ', 5);
define('VISITOR_TYPE_QIYE','qy');
define('VISITOR_TYPE_XIAOZU','xz');

$limit = get_count_offset($_GET);
function get_client_ip(){
	global $ip;
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else $ip = "Unknow";
		return $ip;
}

function get_count_offset($v) {
	$offset = 0;
	$count = PAGE_NUM;
	if (detect_key_exists(array("offset"), $v)) {
		$offset = $v['offset'];
	}
	if (detect_key_exists(array("count"), $v)) {
		$count = $v['count'];
	}
	return ' LIMIT '.$offset.','.$count;
}

function get_now() {
	return strtotime('now');
}

function detect_key_exists ($array, $val) {
	foreach($array as $v) {
		if (!array_key_exists($v,$val))
			return 0;
	}
	return 1;
}
function check_login() {
	if(!isset($_SESSION['name'])){
		header("Location: index.php"); 
		exit;
	}
}

function is_admin() {
	return (isset($_SESSION['level']) && $_SESSION['level'] >= LEVEL_ADMIN);
}

function is_manager() {
	return (isset($_SESSION['level']) && $_SESSION['level'] >= LEVEL_MANAGER);
}

function check_index_page() {
	if(is_admin()){
		header("Location: admin.php"); 
		exit;
	}
	if(isset($_SESSION['name'])){
		header("Location: home.php"); 
		exit;
	}
}

function check_admin_login() {
	check_login();
	if(!is_admin()){
		header("Location: index.php"); 
		exit;
	}
}

function get_yuyue($db , $date , $dian, $visi_id, $consl_id) {
	$rt = array();
	$sql = "select c.nick as consultant_nick, u.roomNo, u.shijiand_id from yuyue as u left join consultant as c on u.consul_id=c.id where u.roomNo like '".$dian."%' and u.date='".$date."'";	
	$us = $db->rawQuery($sql);
	$rt["yuyue"][-1][-1] = '';
	foreach ($us as $u) {
		$roomNo = preg_replace('/\D+/', '', $u["roomNo"]);
		$rt["yuyue"][$u["shijiand_id"]][$roomNo] = $u["consultant_nick"];		
	}
	//咨询师和客户的时间
	update_yuyue_visitor_cons($db, $date, $dian, $visi_id, $consl_id, $rt["yuyue"]);
	
	$us = $db->get('shijianduan', null, array("id", "nick"));
	$rt["shijiand"] = array();
	foreach ($us as $u) {
		$rt["shijiand"][] = $u["nick"];
	}
	$us = $db->where("No like '".$dian."%'")
			 ->get('room', null, array("No", "nick"));
	$rt["room"] = array();
	foreach ($us as $u) {
		$roomNo = preg_replace('/\D+/', '', $u["No"]);
		$rt["room"][] = $u["nick"];
	}
	$rt["dian"]	= $dian;
	return $rt;
}

function update_yuyue_visitor_cons($db, $date, $dian, $vid, $cid, &$yuyue) {
	if ($vid == "" || $cid == "") return;
	$us = $db->where("date", $date)
			 ->where("(visitor_id='".$vid."' or consul_id='".$cid."')")
		     ->get("yuyue", null, array("roomNo", "shijiand_id", "visitor_id", "consul_id", "confirm_time"));
	$rooms = $db->where("dian", $dian)
				->get('room', null, array("No"));
	foreach ($us as $u) {
		foreach ($rooms as $m) {
			$roomNo = preg_replace('/\D+/', '', $u["roomNo"]);
			$rn = preg_replace('/\D+/', '', $m["No"]);
			if (array_key_exists($u["shijiand_id"], $yuyue) && array_key_exists($rn, $yuyue[$u["shijiand_id"]])) {
				if ($u["visitor_id"] == $vid && $u["consul_id"] == $cid && $roomNo == $rn) {
					//一旦确认之后就不可以改动了
					if ($u["confirm_time"] == NULL) {
						$yuyue[$u["shijiand_id"]][$roomNo] = "3";
					}
				}
			} else {
				$yuyue[$u["shijiand_id"]][$rn] = "-";
			}
		}
	}			 
}

function get_consultant($db , $date, $cid, &$nick) {
	$nick = '';
	$rt = array();
	$tt = array();
	$sql = "SELECT ct.nick as nick,cw.cons_id FROM `consul_work` as cw join `consul_work_type` as ct on cw.type=ct.name where date='".$date."'";
	$rt_works = $db->rawQuery($sql);
	foreach ($rt_works as $w) {
		$tt[$w["cons_id"]] = " X ".$w["nick"];
	}
	
	$sql = "select u.nick, u.id, a.price from `consultant` as u left join consul_level_price a on u.level = a.level order by class desc,a.price asc";
	$cons = $db->rawQuery($sql);
	foreach ($cons as $c) {
		$price = number_format($c["price"]);
		if (array_key_exists($c["id"], $tt)) {
			$rt[] = array($c["id"], $price."-".$c["nick"].$tt[$c["id"]]);
		} else {
			$rt[] = array($c["id"], $price."-".$c["nick"]);
		}
		if ($cid != '' && $c["id"] == $cid) {
			if (array_key_exists($c["id"], $tt)) {
				$nick = $c["nick"].$tt[$c["id"]];
			} else {
				$nick = $c["nick"];				
			}
		}
	}
	return $rt;
}

function update_visitor($db, $vid){
	$now_time =  date("Y-m-d H:i:s", get_now());
	$db->where('id', $vid)
	   ->update('visitor', array('op_time' => $now_time));
}

function update_logout($db) {
	$now_time =  date("Y-m-d H:i:s", get_now());	
	$ip = get_client_ip(); //获取登录IP
	$v = array('login_nick'=> $_SESSION['nick'],
				'assis_id'=> $_SESSION['id'],
				'login_ip'=>$ip,
				'login_time'=>$now_time,
				'type'=> TYPE_LOGOUT );
	$t = $db->insert('log_assis_login', $v);
}

function update_login($db, $table, $row, &$arr) {	
	$now_time =  date("Y-m-d H:i:s", get_now()); 
	$counts = $row['login_counts'] + 1;
    $_SESSION['nick'] = $row['nick']; 
	$_SESSION['name'] = $row['name']; 
    $_SESSION['login_time'] = $row['login_time']; 
    $_SESSION['level'] = $row['level']; 
    $_SESSION['id'] = $row['id'];
    $_SESSION['login_counts'] = $counts; 
    $ip = get_client_ip(); //获取登录IP
	$data =  array('login_time'=>$now_time, 'login_ip'=>$ip, 'login_counts'=>$counts);
	$rs = $db->where('name', $_SESSION['name'])
			 ->update ($table, $data);
	$v = array('login_nick'=> $_SESSION['nick'],
				'assis_id'=> $_SESSION['id'],
				'login_ip'=>$ip,
				'login_time'=>$now_time,
				'type'=> TYPE_LOGIN );
	$t = $db->insert('log_assis_login', $v);
	if ($rs) { 
		$arr['success'] = 1; 
		$arr['msg'] = '登录成功！'; 
		$arr['nick'] = $_SESSION['nick']; 
		$arr['login_time'] = $now_time; 
		$arr['login_counts'] = $_SESSION['login_counts']; 
		return true;
	} else { 
		$arr['msg'] = '登录失败'; 
		return false;
	}	
}

function jiaofei_ok($db, $date, $vis_id, $cons_id) {
	$v = array("finish" => 1);			
	$r = $db->where('date',  $date)
			->where('visitor_id', $vis_id)
			->where('consul_id', $cons_id)
			->update('yuyue', $v);
}

function update_visitor_consul_count($db, $id, $consul_id) 
{
	$c = $db->where("visitor_id", $id)
			->getValue("log_expenditure", "count(*)");
	$v =  array("consult_count" => $c);
	//系列的讲咨询师填上
	if ($consul_id != "") {
		$v = array_merge($v, array("consul_id"=>$consul_id));
	} 
	$db->where("id", $id)
	   ->update("visitor", $v);
}

function save_dingj($db, $now, $vis_id, $op_id, $dingj) {
	//保存订金
	if ($dingj == 0) return;
	$v = array("op_time" => $now,
				"visitor_id" => $vis_id,
				"assis_op_id" => $op_id,
				"jine" => $dingj);
	$id = $db->insert("log_dingjin", $v);
	if($id) {
		$value = $db->where("visitor_id", $vis_id)
					->getValue("log_dingjin", "SUM(jine)");
		$v =  array("dingjin" => $value);
		$id = $db->where("id", $vis_id)
				 ->update ('visitor', $v);
	}
	return $id;
}

function save_bushi($db, $now, $vis_id, $consul_id, $op_id, $bushi, $cexiao=false) {
	//保存补时
	if ($bushi == 0) return;
	$v = array();
	if ($cexiao) {
		$v = array("op_time" => $now,
					"visitor_id" => $vis_id,
					"assis_op_id" => $op_id,
					"consul_id" => $consul_id,
					"shichang" => $bushi,
					"comment"=> "预约撤销 ");
	} else {
		$v = array("op_time" => $now,
					"visitor_id" => $vis_id,
					"assis_op_id" => $op_id,
					"consul_id" => $consul_id,
					"shichang" => $bushi);
	}
	$id = $db->insert("log_bushi", $v);
	if($id) {
		$bushi = $db->where("visitor_id", $vis_id)
					->getValue("log_bushi", "SUM(shichang)");
		$v =  array("bushi" => $bushi);
		$id = $db->where("id", $vis_id)
				 ->update ('visitor', $v);
	}
	return $id;
}

function save_ding_bu($db, $now_time, $vis_id, $cons_id, $op_id, $dingj,$bushi, $cexiao=false) {
	save_dingj($db, $now_time, $vis_id, $op_id, -$dingj);
	save_bushi($db, $now_time, $vis_id, $cons_id, $op_id, -$bushi, $cexiao);
}

function tz_shichang(&$sc, &$bushi, &$msg) {
	if ($bushi <= 0) return;
	if ($sc == 0) {
		$bushi = 0;
		return;
	}
	if ($sc > $bushi) {
		$msg .= "全用补时！ ";
		$sc = $sc - $bushi;
	} else {
		$bushi = $sc;
		$sc = 0;
		$msg .= "使用补时！ ";
	}
}

function tz_jin_e(&$jine, &$dingjin, &$msg) {
	if ($dingjin <= 0) return;
	if ($jine == 0) {
		$dingjin = 0;
		return;
	}
	if ($dingjin > $jine) {
		$dingjin = $jine;
		$jine = 0;
		$msg .= "使用订金！ ";
	} else {
		$jine = $jine - $dingjin;
		$msg .= "全用订金！ ";
	}
}

function _update_visitor_sc($db, $vis_id) {
	$shengy_sc = $db->where("visitor_id", $vis_id)
				    ->where("residual_shichang", "0", "<>")
		   		    ->getValue("log_income", "SUM(residual_shichang)");	
	//剩余时长				 
	$v = array("shichang" => $shengy_sc);
	$r = $db->where("id", $vis_id)
	   		->update ('visitor', $v);
	if (!$r) {
		$msg .= '数据库错误! ';
		return false;
	}
	return true;
}

function income_log($db, $now, $vis_id, $cons_id, $assis_id, $op_id, $jine, $shichang, $type, &$msg) {
	if ($jine <=0 || $shichang <=0) return 0;
	$v = array("op_time" => $now,
			"visitor_id" => $vis_id,
			"consul_id" => $cons_id,
			"assis_id" => $assis_id,
			"shichang" => $shichang,
			"residual_shichang" => $shichang,			   
			"cash" => $jine,
			"type" => $type,
			"residual_cash" => $jine,
			"assis_op_id" => $op_id);
	$id = $db->insert ('log_income', $v);
	if (!$id) return 0;
	$msg .= '缴费成功! ';
	_update_visitor_sc($db, $vis_id);
	return $id;
}

function get_share_jine_assis($db, $time, $vis_id, $cons_id, $assis_id, $danjia, $jine, $is_dc, &$comment) {
	$s = "助理：";
	$share_je = 0;
	if ($assis_id == NULL) {
		$s.= "自带无 ";
		return $share_je;
	}

	//历史数据处理;
	/*$add_time = $db->where("id", $vis_id)
				   ->get("visitor", "add_time");
	if($add_time != "" && (strtotime($add_time)<= strtotime("2015-12-01"))){  //strtotime() expects parameter 1 to be string
		$s .= "历史数据 ";
	} else {*/
	$count = $db->where("visitor_id", $vis_id)
				->where("op_time", $time , "<=") //本次累计在内
				->getValue("log_expenditure", "count(*)");
	if ($count == 0) {
		$share_je += $danjia * 0.1;	//小时单价的10%
		$s.= "单价10%,".strval(round($danjia * 0.1, 2))." ";
	}
	
	if (!$is_dc) {
		$share_je += $jine * 0.01;	//系列提成1%
		$s.= "提:1%,".strval(round($jine * 0.01, 2))." ";
	} else {
		$s.= "单次 ";
	}
	$s.= "合计,".strval(round($share_je, 2));
	$comment .= $s;
	return $share_je;
}

function get_share_perc_consul($db, $now, $consul_id, $vis_id, $sc, &$comment) {
	$s = "咨询师：";
	$type = "A-0";
	//单次系列均累计	
	$sum_time = $db->where("visitor_id", $vis_id)
				   ->where("op_time", $now , "<=") 
		   		   ->getValue("log_expenditure", "SUM(time_sc)");
	//本次累计在内	
	$sum_time += $sc;
	$sum_h = strval(round($sum_time/60.0, 1));
	if ($sum_time < 10*60) {
		$type = "A-0";
		$s.= $sum_h."h,10档 ";
	} else if ($sum_time < 20*60) {
		$type = "A-10";
		$s.= $sum_h."h,20档 ";
	} else if ($sum_time < 100*60) {
		$type = "A-90";
		$s.= $sum_h."h,90档 ";
	} else if ($sum_time >= 100*60) {
		$type = "A-100+";
		$s.= $sum_h."h,100+档 ";
	}
	$percent = $db->where("type", $type)
		          ->getValue ("profit_share", "consultant");
	//class==0 为兼职咨询师，兼职咨询师-5%
	$class = $db->where("id", $consul_id)
		        ->getValue ("consultant", "class");		
	if (($class == 0) && ($percent > 0.05)) {
		$percent = $percent - 0.05;
		$s.= "兼职-5% ";
	}
	//判断是否是自带客户
	$zidai = $db->where("id", $vis_id)
				->getValue ("visitor", "is_zidai");
	if ($zidai == ZIXUN_ZIDAI) {
		$percent = 0.6;
		$s.= "自带 ";
	}
	$comment .= $s."提".strval(round($percent * 100, 0))."% |";
	return $percent;
}

function _get_income_id($db, $now, $vis_id, $cons_id, $assis_id, $op_id, &$id, &$msg) {	
	$id = $db->where("visitor_id", $vis_id)
				 ->where("residual_shichang", "0", "<>")
				 ->where("op_time", $now , "<=")
				 ->where("type", TYPE_JIAOFEI_XL)
				 ->orderBy("op_time","asc")
				 ->getValue("log_income", "id");
	if ($id && $id != NULL) return true;
	
	$r = false;
	$chaoshi = 0;$bujiao = 0;
	if ($_POST["xilie_chaoshi"] != '')
		$chaoshi = intval($_POST["xilie_chaoshi"]);

	if ($_POST["xilie_bujiao"] != '')
		$bujiao = intval($_POST["xilie_bujiao"]);
	
	if ($chaoshi == 0 || $bujiao == 0) {
		$msg .= "在".$now."之前没有查到缴费记录";
		return $r;
	} else {
		$msg .= "有补交费！ ";
	}
	$v = array("op_time" => $now,
			"visitor_id" => $vis_id,
			"consul_id" => $cons_id,
			"assis_id" => $assis_id,
			"shichang" => $chaoshi,
			"residual_shichang" => $chaoshi,
			"cash" => $bujiao,
			"residual_cash" => $bujiao,
			"type" => TYPE_JIAOFEI_XL_BJ,
			"assis_op_id" => $op_id);
	$id = $db->insert ('log_income', $v);
	if ($id) {
		$r = true;
		$msg .= "补交费用！ ";
	}
	return $r;
}

function _update_log_income ($db, $id, $res_sc, $res_cash, &$msg) {
	$v = array("residual_shichang" => $res_sc,
			   "residual_cash" => $res_cash);
	$r = $db->where("id", $id)
			->update("log_income", $v);
	if (!$r) {
		$msg = "数据库错误！ ";
		return false;
	}
	return true;
}

function _get_expenditure_serialize($db, $id, &$res_cash, &$res_shichang, &$danjia) {
	$cols = array ("residual_shichang", "residual_cash");		
	$income = $db->where("id", $id)
				 ->getOne("log_income", $cols);
	$res_cash = floatval($income["residual_cash"]);
	$res_shichang = intval($income["residual_shichang"]);
	$danjia = ($res_cash/$res_shichang) * 60.0;   //
}

function _update_expenditure($db, $now, $vis_id, $consul_id, $assis_id, $id, $start_t, $end_t, $res_shichang, $res_cash, $share_consl, $share_assis, $op_id, $comment, &$msg) {
	$v = array("op_time" => $now,
			   "visitor_id" => $vis_id,
			   "consul_id" => $consul_id,
			   "assis_id" => $assis_id,
			   "income_id" => $id,
			   "time_start" => $start_t,
			   "time_end" => $end_t,
			   "time_sc" => $res_shichang,
			   "expenditure_total" => $res_cash,
			   "expen_consul" => $share_consl,
			   "expen_assis" => $share_assis,
			   "assis_op_id" => $op_id,
	    	   "comment" => $comment);
	$r = $db->insert("log_expenditure", $v);
	if (!$r) {
		$msg = "数据库错误2！ ";
		return false;
	}
	return true;
}

function expenditure_log($db, $now, $vis_id, $consul_id, $assis_id, $op_id, $start_t, $end_t, $sc, $id=-1, $cexiao=false) {
	$msg = "";
	if ($sc <= 0 || $id == 0) return $msg;
	$is_danci = false;
	if ($id > 0) {
		$is_danci = true;
	} else if ($id == -1) {//系列
		if (!_get_income_id($db, $now, $vis_id, $consul_id, $assis_id, $op_id, $id, $msg))
			return $msg;
	} else {
		return $msg;
	}

	$reg = "/^(\d{1,2}):(\d{1,2})$/";
	if (!preg_match($reg, $start_t)) {
		$start_t = NULL;
	} else {
		$start_t = date('Y-m-d', strtotime($now)).' '.$start_t.":00";
	}
	if (!preg_match($reg, $end_t)) {
		$end_t = NULL;
	} else {
		$end_t = date('Y-m-d', strtotime($now)).' '.$end_t.":00";
	}

	$percent = 0;
	$comment = "";
	$res_cash = 0;
	$res_shichang = 0;
	$share_assis = 0;
	$danjia = 0;

	_get_expenditure_serialize($db, $id, $res_cash, $res_shichang, $danjia);
	
	if ($is_danci) {
		//单次
		if (abs($sc - $res_shichang) > 0.01) {
			$msg = "验证错误！ ";
			return $msg;
		}
		if ($res_cash > 0 && $res_shichang > 0) {
			$percent = get_share_perc_consul($db, $now, $consul_id, $vis_id, $sc, $comment);
			$share_assis = get_share_jine_assis($db, $now, $vis_id, $consul_id, $assis_id, $danjia, $res_cash, true, $comment);
			//添加咨询记录
			if (!_update_expenditure($db, $now, $vis_id, $consul_id, $assis_id, $id, $start_t, $end_t, $res_shichang, $res_cash, ($res_cash * $percent), $share_assis, $op_id, $comment, $msg)) 
				return $msg;
			//更新收入
			if (!_update_log_income($db, $id, 0, 0, $msg)) return $msg;
			//更新咨询次数
			update_visitor_consul_count($db, $vis_id, "");
			$msg .= "咨询成功!";
		}
	} else {
		//系列
		$consul_comment = "";
		$percent = get_share_perc_consul($db, $now, $consul_id, $vis_id, $sc, $consul_comment);
		while ($sc > 0) {
			$comment = $consul_comment;
			$t_sc = 0;
			$sc_cash = 0;
			if ($sc >= $res_shichang) {
				$t_sc = $res_shichang;
				$sc_cash = $res_cash;
				$sc = $sc - $res_shichang;
			} else {
				$t_sc = $sc;
				$sc = 0;
				$sc_cash = ($danjia / 60.0) * $t_sc ;
			}
			$share_assis = get_share_jine_assis($db, $now, $vis_id, $consul_id, $assis_id, $danjia, $sc_cash, false, $comment);
			if ($cexiao) {
				$comment.= "预约撤销 ";
			}
			//添加咨询记录
			if (!_update_expenditure($db, $now, $vis_id, $consul_id, $assis_id, $id, $start_t, $end_t, $t_sc, $sc_cash, ($sc_cash * $percent), $share_assis, $op_id, $comment, $msg)) 
				return $msg;
			//更新收入
			if (!_update_log_income($db, $id, $res_shichang - $t_sc, $res_cash - $sc_cash, $msg))
				return $msg;
				
			if ($sc == 0) break;
			
			if (!_get_income_id($db, $now, $vis_id, $consul_id, $assis_id, $op_id, $id, $msg))
				return $msg;

			_get_expenditure_serialize($db, $id, $res_cash, $res_shichang, $danjia);	
		}
		//更新咨询次数
		update_visitor_consul_count($db, $vis_id, $consul_id);
		$msg .= "咨询成功!";
	}
	_update_visitor_sc($db, $vis_id);
	return $msg;
}

function get_assis_expen($db, $aid) {
	$sql = "SELECT SUM(expen_assis) as s FROM `log_expenditure` where date_format(op_time,'%Y-%m')=date_format(now(),'%Y-%m') and assis_id='".$aid."'";	
	$t =$db->rawQuery($sql);
	$rs['benyue'] = 0;
	if (count($t) > 0 && $t[0]["s"] != NULL) $rs['benyue'] = $t[0]["s"];
	$sql = "SELECT SUM(expen_assis) as s FROM `log_expenditure` where date_format(op_time,'%Y-%m')=date_format(DATE_SUB(curdate(), INTERVAL 1 MONTH),'%Y-%m') and assis_id='".$aid."'";
	$t =$db->rawQuery($sql);
	$rs['shanagyue'] = 0;
	if (count($t) > 0 && $t[0]["s"] != NULL) $rs['shanagyue'] = $t[0]["s"];
	return $rs;
}

function create_like_sql($table, $input, $n1, $n2='') {
	$s = "";
	if ($n1 != '' && detect_key_exists(array($n1), $input) && $input[$n1] != ''){
		$s = "and ".$table.".nick like '%".$input[$n1]."%' ";
	}
	if ($n2 != '' && detect_key_exists(array($n2), $input) && $input[$n2] != '') {
		$s .= "and (".$table.".name like '".$input[$n2]."%' or ".$table.".bakphone like '".$input[$n2]."%')";
	}
	return $s;
}

function create_date_where($p, $column="op_time") {
	$s = "admin_start";
	$d = "admin_end";
	if (detect_key_exists(array($s, $d), $p)) {
		return "date_format(".$column.", '%Y-%m-%d')>=date_format('".$p[$s]."', '%Y-%m-%d') and date_format(".$column.", '%Y-%m-%d')<=date_format('".$p[$d]."', '%Y-%m-%d')";
	} else {
		return "";
	}
}

function admin_set_sql($db, $column, $from, $limit, $col, $sort, $str_title, &$arr) {
	$rs = $db->rawQuery('SELECT  '.$column.$from.$limit);
	if (!$db->count == 0) {
		$arr['have_data'] = 1; 
	} else {
		$arr['have_data'] = 0; 
	}

	$arr["result"]["data"] = $rs;
	$t = $db->rawQuery('SELECT  count(*) as c '.$from);
	if (!$db->count == 0) {
		$arr["result"]['c'] = $t[0]['c'];
	}

	$arr["result"]["col"] = $col;
	$arr["result"]["st"] = $sort;
	$arr["result"]["title"] = $str_title;
}

function admin_get_sql($db, $sql, $cols, &$result) {
	$t = $db->rawQuery($sql); 
	if ($t) {
		$t = $t[0];
		foreach ($cols as $c) {
			if ($t[$c])  $result[$c] = $t[$c];
		}
	}
}

?>