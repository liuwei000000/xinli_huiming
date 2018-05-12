var now_time = get_now();
var tomorrow_time = get_tomorrow();
var week_time = get_week();
var month_time = get_month();
var PAGE_NUM = 20;
var VISITOR_TYPE_QIYE = 'qy';
var VISITOR_TYPE_XIAOZU = 'xz';
function get_request() {  
   var url = window.location.href; //获取url中"?"符后的字串
   var theRequest = new Object();
   if (url.indexOf("?") != -1) {
      var str = url.substr(url.indexOf("?") + 1);
      strs = str.split("&");
      for(var i = 0; i < strs.length; i ++) {
         theRequest[strs[i].split("=")[0]]=(strs[i].split("=")[1]);
      }
   }
   return theRequest;
}
function get_url_not() {
   var url = window.location.href; //获取url中"?"符后的字串
   if (url.indexOf("#") != -1) {
      url = url.substr(0, url.indexOf("#"));
   } else if (url.indexOf("?") != -1) {
	  url = url.substr(0, url.indexOf("?"));
   }
   return url;
}
function get_now() {
	var myDate = new Date();
	var t= myDate.getTime();
	myDate = new Date(t);
	var m =  myDate.getMonth()+1;
	var d = myDate.getDate();
	if (m < 10 ){ m = "0" + m; }
	if (d < 10 ){ d = "0" + d; }
	var r = myDate.getFullYear() + '-' + m + '-' + d;
	return r;
}
function get_tomorrow() {
	var myDate = new Date();
	var t= myDate.getTime()+1000*60*60*24;
	myDate = new Date(t);
	var m =  myDate.getMonth()+1;
	var d = myDate.getDate();
	if (m < 10 ){ m = "0" + m; }
	if (d < 10 ){ d = "0" + d; }
	var r = myDate.getFullYear() + '-' + m + '-' + d;
	return r;
}

function get_week() {
	var myDate = new Date();
	var t= myDate.getTime()+1000*60*60*24*7;
	myDate = new Date(t);
	var m =  myDate.getMonth()+1;
	var d = myDate.getDate();
	if (m < 10 ){ m = "0" + m; }
	if (d < 10 ){ d = "0" + d; }
	var r = myDate.getFullYear() + '-' + m + '-' + d;
	return r;
}

function get_month(mn) {
	var mn = arguments[0] ? arguments[0] : 1;
	var myDate = new Date();
	var t= myDate.getTime()+1000*60*60*24*31*mn;
	myDate = new Date(t);
	var m =  myDate.getMonth()+1;
	var d = myDate.getDate();
	if (m < 10 ){ m = "0" + m; }
	if (d < 10 ){ d = "0" + d; }
	var r = myDate.getFullYear() + '-' + m + '-' + d;
	return r;
}

function get_pre_month(mn) {
	var mn = arguments[0] ? arguments[0] : 1;
	var myDate = new Date();
	var t= myDate.getTime() - 1000*60*60*24*31*mn;
	myDate = new Date(t);
	var m =  myDate.getMonth()+1;
	var d = myDate.getDate();
	if (m < 10 ){ m = "0" + m; }
	if (d < 10 ){ d = "0" + d; }
	var r = myDate.getFullYear() + '-' + m + '-' + d;
	return r;
}

function data_range(d) {
	var n = get_pre_month();
	var m = get_month();
	if (d.replace(/-/g,"") < n.replace(/-/g,"")) {
		 return n;
	}
	if (d.replace(/-/g,"") > m.replace(/-/g,"")) {
		return m;
	}
	return 0;
}
function data_range2(d) {
	var n = get_now();
	var m = get_month(2);
	if (d.replace(/-/g,"") < n.replace(/-/g,"")) {
		 return n;
	}
	if (d.replace(/-/g,"") > m.replace(/-/g,"")) {
		return m;
	}
	return 0;
}

function update_script(data, script_id, element_id, is_append, is_return) 
{
	if (is_return == true) return;
	var is_append = (arguments[3] == true);	
	var tpl = $(script_id).text();
	var tempFn = doT.template(tpl);
	var c = tempFn(data);
	if (!is_append) {
		$(element_id).html(c).resize();
	} else {
		$(element_id).append(c).resize();
	}
}

function cal_html(name, object, id, select_id) {
	var result = new Object();
	result.lab_name = name;
	result.items = object;
	result.select_id = select_id;
	update_script(result, '#select_template', id);
}

function get_now_month_option(name) {
	var nowDate = new Date();
	var year = nowDate.getFullYear();
	var month = nowDate.getMonth();
	s = '<select name="' + name + '" id="' + name + '" onchange=admin_refresh(true)>';
	for (i = month+1; i >= month -1; i--) {
		if (i < 10) i = '0' + i;
		t = year + '-' + i;
		s += '<option value="'+ t +'-01">';
		s += t;
		s += '</option>';
	}
	s += '</select>';
	return s;
}

function addClassName(el, sClassName) {
	var s = el.className;
	var p = s.split(" ");
	var l = p.length;
	for (var i = 0; i < l; i++) {
		if (p[i] == sClassName)
			return;
	}
	p[p.length] = sClassName;
	el.className = p.join(" ").replace( /(^\s+)|(\s+$)/g, "" );
}

function removeClassName(el, sClassName) {
	var s = el.className;
	var p = s.split(" ");
	var np = [];
	var l = p.length;
	var j = 0;
	for (var i = 0; i < l; i++) {
		if (p[i] != sClassName)
			np[j++] = p[i];
	}
	el.className = np.join(" ").replace( /(^\s+)|(\s+$)/g, "" );
}

function set_time(id, f) {	
	$(id).tap(function(){
		J.popup({
			html : '<div id="popup_calendar"></div>',
			pos : 'center',
			backgroundOpacity : 0.4,
			showCloseBtn : false,
			onShow : function(){
				new J.Calendar('#popup_calendar',{
					date : new Date(),
					onSelect:function(date){
						J.closePopup();
						$(id).val(date);
						setTimeout(f(true), 1000);
					}
				});
			}
		});
	});
}