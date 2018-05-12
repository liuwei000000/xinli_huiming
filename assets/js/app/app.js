document.addEventListener('deviceready', onDeviceReady, false);
function onDeviceReady(){
    navigator.splashscreen.hide();
    //注册后退按钮
    document.addEventListener("backbutton", function (e) {
        if(J.hasMenuOpen){
            J.Menu.hide();
        }else if(J.hasPopupOpen){
            J.closePopup();
        }else{
            var sectionId = $('section.active').attr('id');
            if(sectionId == 'index_section'){
                J.confirm('提示','是否退出程序？',function(){
                    navigator.app.exitApp();
                });
            }else{
                window.history.go(-1);
            }
        }
    }, false);
}

var App = (function(){
    var pages = {};
    var run = function(){
        $.each(pages,function(k,v){
            var sectionId = '#'+k+'_section';
            $('body').delegate(sectionId,'pageinit',function(){
                v.init && v.init.call(v);
            });
            $('body').delegate(sectionId,'pageshow',function(e,isBack){
                //页面加载的时候都会执行
                v.show && v.show.call(v);
                //后退时不执行
                if(!isBack && v.load){
                    v.load.call(v);
                }
            });
        });
		J.Transition.add('flip','slideLeftOut','flipOut','slideRightOut','flipIn');
        Jingle.launch();       
    };
    var page = function(id,factory){
        return ((id && factory)?_addPage:_getPage).call(this,id,factory);
    }
    var _addPage = function(id,factory){
        pages[id] = new factory();
    };
    var _getPage = function(id){
        return pages[id];
    }
    //动态计算chart canvas的高度，宽度，以适配终端界面
    var calcChartOffset = function(){
        return {
            height : $(document).height() - 44 - 30 -60,
            width : $(document).width()
        }

    }
    return {
        run : run,
        page : page,
        calcChartOffset : calcChartOffset
    }
}());

App.page('index',function(){	
    this.init = function(){
        /*J.Refresh({
            selector : '#refresh_article',
            type : 'pullDown',
            pullText : '下拉刷新列表',
			refreshText : '努力ing...',
            releaseText : '更新吧',
            callback : function(){
				var scroller = this;
				setTimeout(function () {
					home_refresh();
					scroller.refresh();
				}, 500);
            }
        });*/
//    最简约的调用方式
        J.Refresh('#refresh_article','pullUp', function(){
            var scroller = this;
            setTimeout(function () {
				home_refresh($('div[id="refresh_article"] li[class="grid"]').length , PAGE_NUM);
                scroller.refresh();
                J.showToast('加载成功','success');
            }, 500);
        });
		$('header a[id=logout]').on('click',logout);
    }	
	this.load = home_refresh;
})

App.page('list',function(){	
    this.init = function(){
		J.Refresh('#refresh_list_article','pullUp', function(){
            var scroller = this;
            setTimeout(function () {
				list_refresh($('div[id="refresh_list_article"] li[class="grid"]').length , PAGE_NUM);
                scroller.refresh();
                J.showToast('加载成功','success');
            }, 500);
        });		
	}
	this.load = list_refresh;
})

App.page('add', function() {
	this.load = add_refresh;	
})

App.page('visitor', function() {
	this.load = visitor_refresh;
    this.init =  function(){
			J.Refresh('#refresh_visitor','pullUp', function(){
			var scroller = this;
			setTimeout(function () {
				visitor_refresh($('div[id="refresh_visitor"] li[class="grid"]').length, PAGE_NUM);
				scroller.refresh();
				J.showToast('加载成功','success');
			}, 500);
    	});	
	}
})

App.page('yuyue',function(){
	this.load = yuyue_refresh;
	this.init = function(){
		$('#yuyue_mendian').on('change', get_yuyue);
		$('#yuyue_data').on('click', yuyue_select_date);
	}
})

App.page('fang', function() {
	this.load = fang_refresh;
	this.init = function() {	
		J.Refresh('#refresh_fang','pullUp', function(){
			var scroller = this;
			setTimeout(function () {
				fang_refresh($('div[id="refresh_fang"] li[class="grid"]').length, PAGE_NUM);
				scroller.refresh();
				J.showToast('加载成功','success');
			}, 500);
		});
	}
})

App.page('edit',function(){
	this.load = edit_refresh;
	this.init = function(){
	}
})

App.page('paiban',function() {
	this.load = paiban_refresh;
	this.init = function(){
		$('#paiban_selct_consultant').on('change', paiban_rili_refresh);
		new J.Calendar('#paiban_calendar',{
			onRenderDay : function(day,date){
				if (data_range2(date) != 0 ) {
					return '<div>-</div><div id=paiban_div_'+ date +' style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div>';
				} else {
					return  '<div>'+day+'</div><div id=paiban_div_'+ date +' style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div><input type="hidden" id=paiban_input_'+ date +' name="' + date + '" value="0">';
				}
			},
			onSelect:function(date){
				if (data_range2(date) == 0 ) {
					paiban_open( 'paiban_popup_select','#paiban_tmp_val', date);
				}
			},
			onFinish: function() {
				var a = $('#paiban_selct_consultant').val();
				if (a == "") return;
				paiban_rili_refresh();
			}
		})
		$("#paiban_calendar").resize();		
	}
})

App.page('paibanzhuli', function (){
	this.load = paibanzhuli_refresh;
	this.init = function(){
		$('#panbanzhuli_selct_memdian').on('change', paibanzhuli_rili_refresh);
		new J.Calendar('#paibanzhuli_calendar',{
			onRenderDay : function(day,date){
				if (data_range2(date) != 0 ) {
					return '<div>-</div>' + 
					'<div id=paiban_div_'+ date +'_1 style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div>' +
					'<div id=paiban_div_'+ date +'_2 style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div>' + 
					'<div id=paiban_div_'+ date +'_3 style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div>';
				} else {
					return  '<div>'+day+'</div>' + 
					'<div id=paiban_div_'+ date +' style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div><input type="hidden" id=paiban_input_'+ date +' name="' + date + '" value="0">' +
					'<div id=paiban_div_'+ date +' style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div><input type="hidden" id=paiban_input_'+ date +' name="' + date + '" value="0">' +
					'<div id=paiban_div_'+ date +' style="font-size: .8em;width:50px;text-align:center;color:blue;margin:2px auto auto auto;">&nbsp;&nbsp;</div><input type="hidden" id=paiban_input_'+ date +' name="' + date + '" value="0">';
				}
			},
			onSelect:function(date){
				if (data_range2(date) == 0 ) {
					paiban_open( 'paibanzhuli_popup_select','paibanzhuli_tmp_val',date);
				}
			},
			onFinish: function() {
				var a = $('#panbanzhuli_selct_memdian').val();
				if (a == "") return;
				paibanzhuli_rili_refresh();
			}
		})
		$("#paiban_calendar").resize();		
	}
})

App.page('jiaofei',function() {
	this.load = jiaofei_refresh;
	this.init = function(){
	}
})

App.page('visitorlog',function() {
	this.init = function(){
		var request = get_request();
		$('#vislog_zixun').attr("href", "#zixunlog_section?vid=" + request["vid"]);
		$('#vislog_jiaofei').attr("href", "#jiaofeilog_section?vid=" + request["vid"]);
		$('#vislog_huifang').attr("href", "#fang_section?vid=" + request["vid"]);
		$('#vislog_edit').attr("href", "#edit_section?vid=" + request["vid"]);
		$('#visitorlog_title').html(""); //???????????????????
	}
})

App.page('jiaofeilog',function() {
	this.load = jiaofeilog_refresh;
	this.init =  function(){
			J.Refresh('#refresh_jiaofei_log','pullUp', function(){
			var scroller = this;
			setTimeout(function () {
				jiaofeilog_refresh($('div[id="refresh_jiaofei_log"] li[class="grid"]').length, PAGE_NUM);
				scroller.refresh();
				J.showToast('加载成功','success');
			}, 500);
		});	
	}
})

App.page('zixunlog',function() {
	this.load = zixunlog_refresh;
	this.init =  function(){
			J.Refresh('#refresh_zixun_log','pullUp', function(){
			var scroller = this;
			setTimeout(function () {
				zixunlog_refresh($('div[id="refresh_zixun_log"] li[class="grid"]').length, PAGE_NUM);
				scroller.refresh();
				J.showToast('加载成功','success');
			}, 500);
		});	
	}
})

App.page('zhuliticheng',function() {
	this.load = zhuliticheng_refresh;
	this.init =  function(){
			J.Refresh('#refresh_zhuliticheng_log','pullUp', function(){
			var scroller = this;
			setTimeout(function () {
				zhuliticheng_refresh($('div[id="refresh_zhuliticheng_log"] li[class="grid"]').length, PAGE_NUM);
				scroller.refresh();
				J.showToast('加载成功','success');
			}, 500);
		});	
	}
})

App.page('profile', function() {
	this.load = profile_refresh;
})

App.page('table', function() {
	this.init = function () {
		var st = new SortableTable(document.getElementById("table-1"),
			["Suling", "CaseInsensitiveSuling", "Number", "Date", "None"]);
		st.onsort = function () {
			var rows = st.tBody.children;
			var l = rows.length;
			for (var i = 0; i < l; i++) {
				removeClassName(rows[i], i % 2 ? "odd" : "even");
				addClassName(rows[i], i % 2 ? "even" : "odd");
			}
		};
	};
})

$(function(){
    App.run();
})

//#########################################################################
function profile_refresh() {
	$.ajax({
		url : 'ajax.php?action=get_profile',
		dataType: "json", 
		type : 'post',
		data : '',
		async: false,
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (!data.success) {
				alert(data.msg);
				return;
			}
			if (data.result.m != "") {
				$('#zixunshi_paiban').show();
			}
			$('#profile_xiaozu').attr('href', '#list_section?type=' + VISITOR_TYPE_XIAOZU);
			$('#profile_qiye').attr('href', '#list_section?type=' + VISITOR_TYPE_QIYE);			
		},
		error : function(){
			alert('网络错误');
		}
	});
}


///////////////////////////////////////////////////////////////////////////
function home_refresh(offset, count) {
	var offset = arguments[0] ? arguments[0] : 0;
	var count = arguments[1] ? arguments[1] : PAGE_NUM;

	$.ajax({
		url : 'ajax.php?action=get_visitor&offset=' + offset + '&count=' + count,
		dataType: "json", 
		type : 'post',
		data : $('#search').serialize(),
		async: false,
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (!data.success) {
				alert(data.msg);
				return;
			}
			update_script(data.result, '#home_template', '#home_list', offset > 0 && data.have_data, data.have_data != 1);
			$('#visitor_count').html('['+ $('div[id="refresh_article"] li[class="grid"]').length + '/' + data.count + ']');
			$('#visitor_title').html(data.op_name + '-客户');
		},
		error : function(){
			alert('网络错误');
		}
	});
}

function logout() {
  	$.ajax({
		url : 'ajax.php?action=logout',
		dataType: "json", 
        type : 'post',
		data : "",
        success : function(data){
			alert(data.msg);
			window.location.href="index.php"; 
        },
        error : function(){
			alert(data.msg);
        }
     });
}
//#########################################################################
function list_refresh(offset, count) {
	var r = get_request();
	var offset = arguments[0] ? arguments[0] : 0;
	var count = arguments[1] ? arguments[1] : PAGE_NUM;
	var s = '';s1 = '';
	if (r['type'] != undefined) {
		s = '?type=' + r['type'];
		s1 = '&type=' + r['type'];
	}
	$('#list_add_visitor').attr("href", "#add_section" + s);
	$.ajax({
		url : 'ajax.php?action=get_qiye_xiaozu&offset=' + offset + '&count=' + count + s1,
		dataType: "json",
		type : 'post',
		data : $('#list_search').serialize(),
		async: false,
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (!data.success) {
				alert(data.msg);
				return;
			}
			
			$('#list_count').html('['+ $('div[id="refresh_list_article"] li[class="grid"]').length + '/' + data.count + ']');
			if (r['type'] == VISITOR_TYPE_QIYE) {
				update_script(data.result, '#list_qy_template', '#list_list', offset > 0 && data.have_data, data.have_data != 1);
				$('#list_title').html(data.op_name + '-企业');
				$('#add_footer').html('添加企业');
			} else if (r['type'] == VISITOR_TYPE_XIAOZU) {
				update_script(data.result, '#list_xz_template', '#list_list', offset > 0 && data.have_data, data.have_data != 1);
				$('#list_title').html(data.op_name + '-小组');
				$('#add_footer').html('添加小组');
			}
		},
		error : function(){
			alert('网络错误');
		}
	});		
}

//#########################################################################
function add_refresh() {
	var r = get_request();
	if (r['type'] == VISITOR_TYPE_XIAOZU ) {
		$('#add').hide();
		$('#add_B').show();
		$('#add_C').hide();
		$('#addb_save').attr("onclick", "add_customer(VISITOR_TYPE_XIAOZU);");
		$('#add_list_title').html("添加小组");
		$.ajax({
			url : 'ajax.php?action=add_get_consul',
			dataType: "json", 
			type : 'post',
			data : "",
			success : function(data){
				if (!data.success) {
					alert(data.msg);
					return;
				}
				update_script(data.result, "#add_select_template", "#addb_consul");				
			},
			error : function(){
				alert('网络错误');
			}
		 });		
	} else if (r['type'] == VISITOR_TYPE_QIYE) {
		$('#add').hide();
		$('#add_B').hide();
		$('#add_C').show();
		$('#addc_save').attr("onclick", "add_customer(VISITOR_TYPE_QIYE);");
		$('#add_list_title').html("添加企业");			
	} else {
		$('#add').show();
		$('#add_B').hide();
		$('#add_C').hide();
		$('#add_save').attr("onclick", "add_customer();");
		$('#add_list_title').html("添加客户");
		$.ajax({
			url : 'ajax.php?action=get_select',
			dataType: "json", 
			type : 'post',
			data : "",
			success : function(data){
				if (!data.success) {
					alert(data.msg);
					return;
				}
				cal_html("来源*", data.result.laiyuan, "#div_laiyuan", "fang_from");
				cal_html("首访店*", data.result.dian, "#div_dian", "fang_dian");
				update_script(data.result.consultant, "#add_select_consul_template", "#add_is_zidai", true);			
			},
			error : function(){
				alert('网络错误');
			}
		 });						
	}	
}

function add_customer(type) {	
	if(type == VISITOR_TYPE_XIAOZU) {
		if (($('#addb_phone').val() =="") || 
			($('#addb_name').val() =="")   ||
			($('#addb_consul').val() =="") ||
			($('#addb_xuqiu').val() =="")) {
				alert('请填写打完星号的项目');
				return;
		}
		$.ajax({
			url : 'ajax.php?action=addb',
			dataType: "json", 
			data : $('#add_B').serialize(),
			type : 'post',
			success : function(data){
				alert(data.msg);
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				list_refresh();
				J.Router.back();
			},
			error : function(){
				alert('网络错误');
			}
		})		
	} else if (type == VISITOR_TYPE_QIYE) {
		if (($('#addc_phone').val() =="") || 
			($('#addc_name').val() =="") ||
			($('#addc_xuqiu').val() =="")) {
				alert('请填写打完星号的项目');
				return;
		}
		$.ajax({
			url : 'ajax.php?action=addc',
			dataType: "json", 
			data : $('#add_C').serialize(),
			type : 'post',
			success : function(data){
				alert(data.msg);
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				list_refresh();
				J.Router.back(); 
			},
			error : function(){
				alert('网络错误');
			}
		})		
	} else {
		if (($('#add_name').val() =="") || 
			($('#add_phone').val() =="")   ||
			($('#add_sex').val() =="") ||
			($('#add_fang_from').val() =="") ||
			($('#add_fang_dian').val() =="") ||
			($('#add_xuqiu').val() =="")) {
				alert('请填写打完星号的项目');
				return;
		}		
		$.ajax({
			url : 'ajax.php?action=add',
			dataType: "json", 
			data : $('#add').serialize(),
			type : 'post',
			success : function(data){
				alert(data.msg);
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				home_refresh();
				J.Router.back(); 
			},
			error : function(){
				alert('网络错误');
			}
		})		
	}
}

//#########################################################################
function yuyue_refresh() {
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=get_dian&vid=' + request['vid'],
		dataType: "json", 
		type : 'post',
		data : "",
		//async: false,
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			$("#yuyue_title").html(data.result.visitor.nick + "-预约");
			$("#yuyue_shengyu_zijin").html(data.result.visitor.dingjin + "元");
			$("#yuyue_shengyu_shichang").html((parseInt(data.result.visitor.bushi) + parseInt(data.result.visitor.shichang)) + "分");
			$("#yuyue_edit_visitor").attr("href","#edit_section?vid=" + request['vid']);
			update_script(data.result, '#mendian_sele_template', '#yuyue_mendian');
			if (data.result.select_cons_id != null)
				update_script(data.result, "#yuyue_sele_template", "#yuyue_consultant");
			get_yuyue();
			},
		error : function(){
			alert('网络错误');
		}
	});	
}

function get_yuyue(is_zxs) {
	var is_zxs = arguments[0] == false ? arguments[0] : true;
	var request = get_request();
	var v = $("#selct_consultant").val();
	var d = $("#yuyue_data").val()
	var m = $('#selct_mendian').val();
	if (d == "" || m == "") {
		return;
	} else {
		if (data_range(d) !=0) {
			$('#yuyue_data').val(d);	
			return;
		}
		$.ajax({
			url : 'ajax.php?action=get_yuyue&vid=' +  request['vid'],
			dataType: "json", 
			data : $('#yuyue').serialize(),
			type : 'post',
			async: false,
			success : function(data){
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				update_script(data.result, "#yuyue_yuyue_template", "#yuyue_dian");
				if (is_zxs)
					update_script(data.result, "#yuyue_sele_template", "#yuyue_consultant");
				$("#yuyue_dian_map_title").html($('#selct_mendian').find("option:selected").text());
				$('#yuyue_dian').resize();
			},
			error : function(){
				alert('网络错误');
			}
		})
	}
}

function yuyue_visitor() {
	var request = get_request();
	if ($("#yuyue_data").val() =="" || $("#selct_mendian").val() == "" || $("#selct_consultant").val() == "") {
		alert("请填完带星号的选项");
		return;
	}
	$('#yuyue_save').removeAttr("onclick"); //防止频繁点击
	$.ajax({
		url : 'ajax.php?action=yuyue&vid=' + request['vid'],
		dataType: "json", 
		data : $('#yuyue').serialize(),
		type : 'post',
		async: false,
		success : function(data){
			alert(data.msg);
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (data.op >　0 || data.d_c >　0) {
				get_yuyue(false);
			}
			$('#yuyue_save').attr("onclick","yuyue_visitor();");
		},
		error : function(){
			alert('网络错误');
		}
	})
}

function yuyue_ding(name, text) {
	if ($("input[name='" + name + "']").val() == 0) {
		$("input[name='" + name + "']").val(3);
		$("div[name='" + name + "']").removeClass().addClass("yuyue-block-d");
		$("div[name='" + name + "']").text(text + '││订');
	} else if ($("input[name='" + name + "']").val() == 3) {
		$("input[name='" + name + "']").val(0);
		$("div[name='" + name + "']").removeClass().addClass("yuyue-block-k");
		$("div[name='" + name + "']").text(text + '││空');
	}
}

function yuyue_select_date() {
	J.popup({
		html : '<div id="popup_yuyue_calendar"></div>',
		pos : 'center',
		backgroundOpacity : 0.4,
		showCloseBtn : false,
		onShow : function(){
			new J.Calendar('#popup_yuyue_calendar',{
					date : new Date(),
					onRenderDay : function(day,date){
						if (data_range(date) != 0 ) {
							return '<div>-</div>';
						} else {
							return  '<div>'+day+'</div>';
						}
					},
					onSelect:function(date){
						if (data_range(date) != 0 ) {
							return;
						}
						$('#yuyue_data').val(date);						
						J.closePopup();
						get_yuyue();
					},
					onFinish: function() {
					}
			});
		}
	});
}
//#########################################################################
//缴费记录
function jiaofeilog_refresh(offset, count) {
	var offset = arguments[0] ? arguments[0] : 0;
	var count = arguments[1] ? arguments[1] : PAGE_NUM;	
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=get_jiaofeilog&vid=' + request['vid']+ '&offset='+offset+'&count='+count,
		dataType: "json", 
		type : 'post',
		async: false,
		data : "",
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}			
						//-------------------------------				
			$("#jiaofei_log_title").html('<a href="#" onClick="jiaofeilog_refresh()">' + data.result.visitor_nick + '</a>');
			update_script(data.result.jiaofei_log, '#visitorlog_template', '#jiaofeilog_list', offset > 0);			
			//
			},
		error : function(){
			alert('网络错误');
		}
	});
}

//#########################################################################
//资讯记录
function zixunlog_refresh(offset, count) {
	var offset = arguments[0] ? arguments[0] : 0;
	var count = arguments[1] ? arguments[1] : PAGE_NUM;	
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=get_zixunlog&vid=' + request['vid']+ '&offset='+offset+'&count='+count,
		dataType: "json", 
		type : 'post',
		async: false,
		data : "",
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (data.success != 1) {
				alert(data.msg)
				return;
			}
			//-------------------------------				
			$("#zixun_log_title").html('<a href="#" onClick="zixunlog_refresh()">' + data.result.visitor_nick + '</a>');
			update_script(data.result.zixun_log, '#zixunlog_template', '#zixunlog_list', offset > 0);
			},
		error : function(){
			alert('网络错误');
		}
	});
}

//#########################################################################
//助理提成记录
function zhuliticheng_refresh(offset, count) {
	var offset = arguments[0] ? arguments[0] : 0;
	var count = arguments[1] ? arguments[1] : PAGE_NUM;	
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=get_zhuliticheng&offset='+offset+'&count='+count,
		dataType: "json", 
		type : 'post',
		async: false,
		data : "",
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (data.success == -2) {
				J.Router.goTo('#profile_section'); 
			}		
			//-------------------------------				
			$("#zhuliticheng_title").html('<a href="#" onClick="zhuliticheng_refresh()">' + data.result.nick + '</a>');
			update_script(data.result.zhuliticheng, '#zhuliticheng_template', '#zhuliticheng_list', offset > 0, data.result.zhuliticheng.length == 0);
			$("#tc_benyue_ticheng").html(data.result.benyue + "元");
			$("#tc_shangyue_ticheng").html(data.result.shanagyue + "元");			
			//
			},
		error : function(){
			alert('网络错误');
		}
	});
}

//#########################################################################
//预约条目管理
function visitor_refresh(offset, count) {
	var offset = arguments[0] ? arguments[0] : 0;
  	var count = arguments[1] ? arguments[1] : PAGE_NUM;	
	var sv = '';
	var request = get_request();
	if (request['vid'] != undefined) {
		sv ='&vid=' + request['vid'];
	}
	$.ajax({
		url : 'ajax.php?action=get_one_visitor' + sv + '&offset='+offset+'&count='+count,
		dataType: "json", 
		type : 'post',
		async: false,
		data : "",
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			
			update_script(data.result, '#visitor_template', '#visitor_list', offset > 0);
			//-------------------------------
			if (data.result.visitor != undefined)
				$("#visitor_yuyue_title").html('<a href="#" onClick="visitor_refresh()">'	+ data.result.visitor.nick + '</a>');
			else
				$("#visitor_yuyue_title").html('<a href="#" onClick="visitor_refresh()">预约记录</a>');
			//
			},
        error : function(){
			alert('网络错误');
        }
     });
}

function confirm_yuyue(vid, date, t, consul_id) {
	J.confirm('提示','请确认访客已<font color="#FF0000" face="黑体">确定</font>时间！',
	 	function(){
				$.ajax({
				url : 'ajax.php?action=confirm_yuyue&vid=' + vid + '&date=' + date,
				dataType: "json", 
				type : 'post',
				data : "",
		 		success : function(data){
					alert(data.msg);
					if (data.success == -1) {
						window.location.href="index.php"; 
						return;
					}
					if (data.success == 1) {
						s = '<div class="lab_kaishi" id="kaishi' + t + '"><a href="#" onClick=\"yuyue_start(\''+ vid + '\',\'' + date + '\', \'kaishi' + t + '\')\">开始</a></div><div class="lab_jieshu"><a href="#" onClick="visitor_jiaofei_show(' + vid + ',\'' + date + '\')">结算</a></div><div class="lab_yuyue"><a href="#" onClick="delet_yuyue_show(\'' + vid + '\',\'' + date + '\',\'' + consul_id +'\')">撤销</a></div>';
						$("span[id='yuyue"+ t +"']").html(data.time);
						$("div[id='yuyue"+ t +"']").html(s);
					}
				},
				error : function(){
					alert("网络错误");
		 		}
				});
		},
		function(){
			J.showToast('请和客户沟通确定后在确认');
			return;
		});
}
function delet_yuyue(vid, date, koushi, consul_id) {
 	J.confirm('提示','是否确定删除！',
		function(){
			if (koushi != undefined) {
				var dat = 'xilie_sc=' + koushi + '&xilie_chaoshi=&xilie_bujiao=&xilie_type=0&xilie_dj=&xilie_heji=&xilie_fenqi=&time_start=&time_end='
				$.ajax({
					url : 'ajax.php?action=save_jiaofei_cx&xl_dj=0&vid=' + vid + '&date=' + date + '&consul_id='+ consul_id,
					dataType: "json", 
					type : 'post',
					data : dat,
					async: false,
					success : function(data){////?????????????
							var t = ('扣费 ' + data.msg);
							if (data.success == -1) {
								window.location.href="index.php"; 
								return;
							}
							$.ajax({
								url : 'ajax.php?action=delet_yuyue&vid=' + vid + '&date=' + date,
								dataType: "json", 
								type : 'post',
								data : "",
								success : function(data){
										alert(t + data.msg);
										if (data.success == -1) {
											window.location.href="index.php"; 
											return;
										}
										visitor_refresh();
									},
									error : function(){
										alert("网络错误");
									}
								});
						},
					error : function(){
							alert("网络错误");
						}
				});
			} else {
				$.ajax({
					url : 'ajax.php?action=delet_yuyue&vid=' + vid + '&date=' + date,
					dataType: "json", 
					type : 'post',
					data : "",
					success : function(data){
							alert(data.msg);
							if (data.success == -1) {
								window.location.href="index.php"; 
								return;
							}
							visitor_refresh();
						},
					error : function(){
							alert("网络错误");
						}
					});				
				}			
			},
		function(){
		});
}
function delet_yuyue_show(vid, date, consul_id) {
	J.popup({
		tplId : 'visitor_cexiao_template',
		pos : 'center'
	});	
	$.ajax({
		url : 'ajax.php?action=get_one_visitor_shengyu&vid=' + vid,
		dataType: "json", 
		type : 'post',
		data : "",
		//async: false,		
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
				return;
			}		
			$('#visitor_shengyu_shijian').html(parseInt(data.result.shichang) + parseInt(data.result.bushi));
			$('#visitor_shengyu_shichang').html(data.result.shichang);
			$('#visitor_shengyu_bushi').html(data.result.bushi);	
			$('#visitor_cexiao').attr('onclick', 'yuyue_delet_submit('+vid+',\''+date+'\',\'' + consul_id + '\')');
			},
		error : function(){
			alert("网络错误");
		}
	});	
}
function visitor_jiaofei_show(vid, date) {
	J.popup({
		tplId : 'visitor_leixing_template',
		pos : 'center'
	});	
	$.ajax({
		url : 'ajax.php?action=get_one_visitor_shengyu&vid=' + vid,
		dataType: "json", 
		type : 'post',
		data : "",
		//async: false,		
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
				return;
			}		
			$('#visitor_shengyu_xilie').html(data.result.shichang);
			$('#visitor_jiaofei_dc').attr('onclick', 'visitor_go_jiaofei('+vid+',\''+date+'\', \'dc\')');	
			$('#visitor_jiaofei_xl').attr('onclick', 'visitor_go_jiaofei('+vid+',\''+date+'\', \'xl\')');	
			},
		error : function(){
			alert("网络错误");
		}
	});		
}
function visitor_go_jiaofei(vid, date, t) {
	$('#visitor_leixing_template').hide();
	J.Router.goTo('#jiaofei_section?vid=' + vid + '&date=' + date + '&type=' + t);
}
function yuyue_delet_submit(vid, date, consul_id) {
	if (($('#visitor_cexiao_koushi').val() != '') && (parseInt($('#visitor_cexiao_koushi').val()) > parseInt($('#visitor_shengyu_shijian').html()))) {
		alert('扣时超出拥有的时间')
		return false;
	}
	if ($('#visitor_cexiao_koushi').val() == '' || parseInt($('#visitor_cexiao_koushi').val()) == 0) {
		delet_yuyue(vid, date);
	} else {
		delet_yuyue(vid, date,parseInt($('#visitor_cexiao_koushi').val()), consul_id);
	}
	return false;
	
}
function yuyue_start(vid, date, t) {
	$.ajax({
		url : 'ajax.php?action=yuyue_start&vid=' + vid + '&date=' + date,
		dataType: "json", 
		type : 'post',
		data : "",
		success : function(data){
				alert(data.msg);
				if (data.success == -1) {
					window.location.href="index.php"; 
					return;
				}
				if (data.success == 1) {
					$("div[id=" + t + "]").remove();
				}
			},
				error : function(){
				alert("网络错误");
				}
		});
}
//#########################################################################
//个人信息
function edit_refresh() {
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=get_edit&vid=' + request['vid'],
		dataType: "json", 
		type : 'post',
		//async: false,
		data : "",
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			update_script(data.result.visitor, '#edit_template', '#edit_area');
			update_script(data.result, '#visitor_sele_template', '#visitor_consultant');
			//------------------------------
			$("#edit_title").html(data.result.visitor.nick);
			},
		error : function(){
			alert("网络错误");
		}
		});
	
}

//##############################################################3
function edit_customer() {
	if (($('#edit_name').val() =="") || 
	    ($('#edit_nick').val() =="")
		) {
		alert('请填写打完星号的项目');
		return;
	}
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=edit_visitor&vid=' + request['vid'],
		dataType: "json", 
		data : $('#edit').serialize(),
		type : 'post',
		success : function(data){
			alert(data.msg);
			if (data.success == -1) {
				window.location.href="index.php"; 
				return;
			}
			if ($('#yuyue_article').html() != null) {
				yuyue_refresh();
			}
		},
		error : function(){
			alert('网络错误');
		}
	})
}
////////////////////////////////////////////////////////////////////////////////////
function fang_refresh(offset, count) {
	var offset = arguments[0] ? arguments[0] : 0;
  	var count = arguments[1] ? arguments[1] : PAGE_NUM;	
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=huifang&vid=' + request['vid']+ '&offset='+offset+'&count='+count,
		dataType: "json", 
		type : 'post',
		async: false,
		data : "",
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			
			update_script(data.result.huifang, '#huifang_template', '#huifang_list', offset > 0);
			//-------------------------------				
			$("#fang_title").html('<a href="#" onClick="fang_refresh()">' + data.result.visitor.nick + '</a>');
			$("#huifang_xuqiu").html(data.result.visitor.xuqiu);
			//
			},
		error : function(){
			//alert(data.msg);
		}
	});
}

function fang_save() {
	var request = get_request();
	var v = $("#content").val();
	if (v == "") {
		alert("请填写内容！");
		return;
	}
	$.ajax({
		url : 'ajax.php?action=add_huifang&vid=' + request['vid'],
		dataType: "json", 
		type : 'post',
		//async: false,
		data : $('#huifang').serialize(),
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php";
			}
			if (data.success == 1) {
				alert(data.msg);
			}
			fang_refresh();
			},
		error : function(){
			//alert(data.msg);
		}
	});
}

function fang_delet(id) {
	 J.confirm('提示','您确认要删除么？',
		function () {
			$.ajax({
				url : 'ajax.php?action=delet_huifang&id=' + id,
				dataType: "json", 
				type : 'post',
				//async: false,
				data : $('#huifang').serialize(),
				success : function(data){
					if (data.success == -1) {
						window.location.href="index.php";
					}
					if (data.success == 1) {
						alert(data.msg);
					}
					$("#riqi" + id).remove();
					$("#neirong" + id).remove();
					$("#ge" + id).remove();
					},
				error : function(){
					alert("网络错误");
				}
			});},
			function(){
				return;
			});
}
/////////////////////////////////////////////////////////////////////////////
function paiban_refresh() {
	$.ajax({
		url : 'ajax.php?action=get_consultant_worktype',
		dataType: "json", 
		type : 'post',
		data : "",
		//async: false,
		success : function(data){
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				if(data.success) {
					update_script(data.result.consultant, '#paiban_sele_template', '#paiban_selct_consultant');
					update_script(data.result.worktype, '#paiban_worktype_sele_template', '#paiban_worktype_hidden');
				}
			},
		error : function(){
			alert("网络错误");
		}
	});
}
function paibanzhuli_refresh() {
	$.ajax({
		url : 'ajax.php?action=get_assistant_dian',
		dataType: "json", 
		type : 'post',
		data : "",
		//async: false,
		success : function(data){
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				if(data.success) {
					update_script(data.result.dian,'#paibanzhuli_sele_template' , '#panbanzhuli_selct_memdian');
					update_script(data.result.assistant,'#paibanzhuli_sele_template' , '#paibanzhuli_assistant_hidden');			
				}
			},
		error : function(){
			alert("网络错误");
		}
	});
}
function paiban_rili_refresh() {
	_paiban_rili_refresh('#paiban_calendar', 'paiban_input_', 'paiban_div_', $('#paiban_selct_consultant').val());
}

function paibanzhuli_rili_refresh() {
	//_paiban_rili_refresh('#paiban_calendar', 'paiban_input_', 'paiban_div_', $('#paiban_selct_consultant').val());
}

function _paiban_rili_refresh(calendar_id, input_prefix, div_prefix , consul_id) {
	var d = new Date((new Date()).format("yyyy/MM/dd"));
	var slip_date = $(calendar_id + " td:eq(10)").attr('data-date');
	var slip_d = new Date(slip_date.replace(/-/g,"/"));
	var month = slip_d.getMonth() + 1;
	if (month < 10 ) {
		month = '0' + month.toString();
	}
	var y = slip_d.getFullYear();	
	var sel_s = y.toString() + '/' + month.toString() + '/' + d.getDate().toString();
	var sel_d = new Date(sel_s);
	var days= (sel_d.getTime() - d.getTime())/ (1000 * 60 * 60 * 24);  
	if (days < -10 || days > 100) return;	
 	$.ajax({
		url : 'ajax.php?action=get_consultant_paiban&year='+ y + '&month='+ month + '&cons_id=' + consul_id,
		dataType: "json", 
		type : 'post',
		data : "",
		//async: false,
		success : function(data){
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				$("input[id^='" + input_prefix + "']").val(0);
				$("div[id^='" + div_prefix + "']").html('&nbsp;&nbsp;');
				if (data.success == 1) {
					for (i = 0; i < data.result.length; i++) {
						var str = data.result[i].date;
						if (data_range2(str) == 0 ) {
							$('#'+ div_prefix + str).html(data.result[i].type_name);
							$('#'+ input_prefix + str).val(data.result[i].type);
						}
					}
				}
				if (data.success == 0) {
					alert(data.msg);
				}
				
			},
		error : function(){
			alert("网络错误");
			}
		});		
}
function paiban_open(openid,id, date) {
	$(id).val(date);
	J.popup({
		tplId : openid,
		pos : 'center'
	});
	$('#paiban_leixing').html($('#paiban_worktype_hidden').html());
}
function paiban_change() {
	var date = $('#paiban_tmp_val').val();
	var sel_val = $('#paiban_leixing').val();
	var sel_text = $('#paiban_leixing').find("option:selected").text();
	$('#paiban_div_'+ date).html(sel_text);
	$('#paiban_input_'+ date).val(sel_val);
}
function paiban_save() {
	$.ajax({
		url : 'ajax.php?action=paiban_save',
		dataType: "json", 
		type : 'post',
		data : $('#paiban_from').serialize(),
		async: false,
		success : function(data){
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				if (data.success == -2) {
					J.Router.goTo('#profile_section');  
				}
				if (data.success == 1) {
					alert(data.msg);
				}
			},
		error : function(){
			alert("网络错误");
		}
	});	
}
///////////////////////////////////////////////////////////////////////////
function jiaofei_refresh() {
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=get_jiaofei&vid=' + request['vid'] + '&date=' + request['date'],
		dataType: "json", 
		type : 'post',
		//async: false,
		data : "",
		success : function(data){
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				if (data.success == 1) {
					update_script(data.result.consultant, "#jiaofei_sele_template", "#jiaofei_consultant");
					$('#jiaofei_danjia').html(parseFloat(data.result.consultant.price) + '元/小时');
					$("#jiaofei_title").html(data.result.visitor.nick + "-结算");
					$("#jiaofei_dingjin").html(data.result.visitor.dingjin + "元");
					$("#shengyu_shichang").html(data.result.visitor.shichang + "分");
					$("#shengyu_bushi").html(data.result.visitor.bushi + "分");
					$("#jiaofei_start").val(data.result.start_time);
					$("#jiaofei_end").val(data.result.end_time);
					if (parseFloat(data.result.visitor.qianfei) != 0) {
						$("#jiaofei_qianfei").html(data.result.visitor.qianfei + "元").show();
						$("#jiaofei_qianfei2").show();
						$("#jiaofei_xilie_shijian").val(0).attr("disabled",true);
					} else {
						$("#jiaofei_qianfei").html(data.result.visitor.qianfei + "元").hide();
						$("#jiaofei_qianfei2").hide();
						$("#jiaofei_xilie_shijian").val(0).removeAttr("disabled");				
					}
					jishi_change();
					if (request['type'] == 'xl') {
						$('#danci_from').hide();
						$('#xilie_from').show();
					}
					if (request['type'] == 'dc') {
						$('#xilie_from').hide();
						$('#danci_from').show();
					}					
				}
			},
		error : function(){
			alert('网络错误');
		}
	});	
}
function get_jiaofei_xilie_price() {
	var consul_id = $('#jiaofei_selct_consultant').val();
	if (consul_id == '') return;
	$.ajax({
		url : 'ajax.php?action=get_price&consul_id=' + consul_id + '&price=' + $('#jiaofei_xilie_shijian').val(),
		dataType: "json", 
		type : 'post',
		async: false,
		data : "",
		success : function(data){
				if (data.success == -1) {
					window.location.href="index.php"; 
				}
				if (data.success == 1) {
					if (data.result != "")
						$('#jiaofei_xilie').html(parseFloat(data.result) + '元/小时');
					else
						$('#jiaofei_xilie').html("");
					jiaofei_js();
				}
			},
		error : function(){
			alert('网络错误');
		}
	});	
}

function check_zixun_time() {
	var t_start = $("#jiaofei_start").val().replace(/：/g,":");
	var t_end = $("#jiaofei_end").val().replace(/：/g,":");
	if ((t_start != '' && isNaN(new Date("2015/11/11 " + t_start)))	
	 || (t_end != '' && isNaN(new Date("2015/11/11 " + t_end)))) {
		alert("不是时间，时间的输入格式为 hh:mm");
		$("#danci_zixunsc").val("");
		$("#xilie_zixunsc").val("");
		return false;
	}
	return true;
}

function jishi_change (){
	var t_start = $("#jiaofei_start").val().replace(/：/g,":");
	var t_end = $("#jiaofei_end").val().replace(/：/g,":");
	if (t_start == '' || t_end == '' || !check_zixun_time()) return;
	var d1 = new Date("2015/11/11 " + t_start);
	var d2 = new Date("2015/11/11 " + t_end);
	var fen_cha = d2.getTime() - d1.getTime();
	if (fen_cha < 0) {
		alert("结束时间小于开始时间");
		$("#danci_zixunsc").val("");
		return false;
	}
	//时长计算按5的倍数
	var yu5 = Math.floor((fen_cha/(1000*60))/5) * 5;
	var bushi = $('#jiaofei_benci_bushi').val();
	yu5 = yu5 - bushi;	
	if (yu5 < 0) {
		alert("补时大于咨询时间");
		$("#danci_zixunsc").val("");
		$("#xilie_zixunsc").val("");
		return false;
	}
	$("#danci_zixunsc").val(yu5);
	$("#xilie_zixunsc").val(yu5);
	jiaofei_js();
	return true;
}
function get_dingjin() {
	var id = "#quanju_dingjin";
	var re3 = /^[\d\.]*/;	
	var dingjin = $(id).val();
	if (dingjin == "" || dingjin.match(re3) == null) {
		return 0;
	}
	dingjin = parseFloat(dingjin);
	return dingjin;
}
function get_yu_e() {
	var y = 0;
	var y = parseFloat($('#jiaofei_dingjin').html().replace(/元.*/g,""));
	if (y >=0) return y;
}
function get_danjia(id_dj, id_tzdj) {
	var re = /^\d*$/;
	var re2 = /^\d*/;
	var dj = $(id_dj).html();	
	if (dj != "" && dj.match(re2) != null) {
		dj = parseFloat(dj.replace(/元.*/g,""));
	} else {
		dj = 0;
	}
	var tz_dj = $(id_tzdj).val();
	if (tz_dj != "" && (tz_dj.match(re)) != null) {
		dj =parseFloat(tz_dj);
	}
	return dj;
}
function get_fy_shichang(id_sc) {
	var re = /^\d*$/;
	var sc = $(id_sc).val();
	if (sc != "" && sc.match(re) != null) {
		sc = parseInt(sc);
	} else {
		sc =0;
	}
	var shengyu_bs =  parseInt($('#shengyu_bushi').html().replace(/分.*/g,""));
	if (sc > shengyu_bs) {
		sc = sc - shengyu_bs;
	} else {
		sc = 0;
	}
	return sc;
}
function xilie_zongji() {
	var xl_sc = parseInt($('#jiaofei_xilie_shijian').val());
	var xl_dj = get_danjia("#jiaofei_xilie","#xilie_dj");	
	var yu_e = get_yu_e();
	var xl_sum = (xl_sc * xl_dj) + get_dingjin() - yu_e;
	if (xl_sum > 0 && xl_sc != 0)$("#xilie_heji").val(xl_sum.toFixed(0));
	else $("#xilie_heji").val("");
 	var shiyong_yue = chaoshi_js();
	if (shiyong_yue > 0) return shiyong_yue;
	if (xl_sum >= 0) return yu_e;
	else return ((xl_sc * xl_dj) + get_dingjin());
}
function danci_zongji() {
	var dc_sc = get_fy_shichang("#danci_zixunsc");
	var dc_dj = get_danjia("#jiaofei_danjia","#danci_dj");
	var yu_e = get_yu_e();
	var dc_sum = (dc_sc * (dc_dj/60.0)) + get_dingjin() - yu_e;		
	if (dc_sum > 0) $("#danci_heji").val(dc_sum.toFixed(0));
	else $("#danci_heji").val("");
	if (dc_sum >= 0) return yu_e;
	else return ((dc_sc * (dc_dj/60.0)) + get_dingjin());
}
function formatFloat(t) {
	t.value=t.value.replace(/[^0-9\.]+/,'');
}
function formatNum(t) {
	t.value=t.value.replace(/[^0-9]+/,'');
}
function chaoshi_js() {
	//计算超时
	var shengyu_sc =  parseInt($('#shengyu_shichang').html().replace(/分.*/g,""));
	var shengyu_bs =  parseInt($('#shengyu_bushi').html().replace(/分.*/g,""));
	var zixun_sc = parseInt($('#xilie_zixunsc').val());
	var yu_e = get_yu_e();
	$('#xilie_chaoshi').val("");
	$('#xilie_bujiao').val("");	
	if ($('#jiaofei_xilie_shijian').val() != 0) return;
	if (zixun_sc > (shengyu_sc + shengyu_bs)) {
		var chaoshi = zixun_sc - (shengyu_sc + shengyu_bs);
		$('#xilie_chaoshi').val(chaoshi);
		var danjia = get_danjia("#jiaofei_danjia","#danci_dj");;
		//有超时，且没签系列
		if (danjia >　0 && $('#jiaofei_xilie_shijian').val() == 0) {
			var bujiao = chaoshi * (parseInt(danjia) / 60.0) + get_dingjin() - yu_e;
			if (bujiao>0) {
				$('#xilie_bujiao').val(bujiao.toFixed(0));
				return yu_e;
			} else {
				return (chaoshi * (parseInt(danjia) / 60.0) + get_dingjin());
			}
		}
	}
	return 0;
}

function jiaofei_js() {
	danci_zongji();
	xilie_zongji();
}
function jiaofei_show(id_from, id_show, prefix) {
	var re2 = /^\d*/;
	var t = $(id_from).val();
	if (t != '' && parseFloat(t) != 0) {
		$('#' + prefix + '_popup_' + id_show).html(t);
	} else {
		$('#' + prefix + '_input_'+ id_show).hide();
	}
}
function jiaofei_tijiao(id) {
	//if (!check_zixun_time()) return;
	if (!jishi_change()) return;
	if (parseFloat($('#xilie_fenqi').val()) > 0) {
		$('#quanju_dingjin').val("");
	}
	var dingjin = get_dingjin();
	var dc_yu_e_shiyong = danci_zongji();
	var xl_yu_e_shiyong = xilie_zongji();
	if (id == 'jiaofei_popup_danci' && parseFloat($("#danci_heji").val()) < 0){
		alert("总金额不能为负，请调整订金！");
		return;
	}
		
	if (id == 'jiaofei_popup_xilie' &&
		((parseFloat($("#xilie_heji").val()) < 0) || (parseFloat($("#xilie_bujiao").val()) < 0))){
		alert("金额不能为负，请调整订金！");
		return;
	}
		
	if ((id != "jiaofei_popup_tuihuan") && ($('#jiaofei_selct_consultant').val() == "")) {
		alert("请先选择咨询师");
		return;
	}
	var y = parseFloat($('#jiaofei_dingjin').html().replace(/元.*/g,""));
	if ((id == "jiaofei_popup_tuihuan") && (y == 0)) {
		alert("没有可退还的订金");
		return;
	}

	/*
	var xl_sc = parseInt($("#xilie_zixunsc").val());
	var dc_sc = parseInt($("#danci_zixunsc").val());
	if (xl_sc > 500 || dc_sc > 500) {
		$('#danci_zixunsc').val(500);
		$('#xilie_zixunsc').val(500);
		alert("咨询时长超过500分，请检查！");
		return;		
	}*/
	
	J.popup({
		tplId : id,
		pos : 'center'
	})
	if (id == 'jiaofei_popup_danci') {
		jiaofei_show('#danci_zixunsc', 'shijian', 'danci');
		jiaofei_show('#quanju_dingjin', 'dingjin', 'danci');
		jiaofei_show('#jiaofei_geiyu_bushi', 'bushi', 'danci');
		jiaofei_show('#danci_heji', 'heji', 'danci');
		var bs_ye = parseInt($('#shengyu_bushi').html().replace(/分.*/g,""));
		if (bs_ye != 0)	$('#danci_popup_bushiyue').html("-" + bs_ye.toString() + "(余)").show();
		if (dc_yu_e_shiyong > 0) {
			$('#danci_popup_shiyongyue').html(dc_yu_e_shiyong.toFixed(0));
			$('#danci_input_shiyongyue').show();
		}
	}
	if (id == 'jiaofei_popup_xilie') {
		jiaofei_show('#xilie_zixunsc', 'shijian', 'xilie');
		jiaofei_show('#quanju_dingjin', 'dingjin', 'xilie');
		jiaofei_show('#jiaofei_geiyu_bushi', 'bushi', 'xilie');
		jiaofei_show('#jiaofei_xilie_shijian', 'xiaoshi', 'xilie');
		jiaofei_show('#xilie_chaoshi', 'chaoshi', 'xilie');	
		jiaofei_show('#xilie_bujiao', 'chaoshi_jf', 'xilie');		
		jiaofei_show('#xilie_heji', 'heji', 'xilie');
		jiaofei_show('#xilie_fenqi', 'fenqi', 'xilie');		
		var bs_ye = parseInt($('#shengyu_bushi').html().replace(/分.*/g,""));
		if (bs_ye != 0)	$('#xilie_popup_bushiyue').html("-" + bs_ye.toString() + "(余)").show();	
		if (xl_yu_e_shiyong > 0) {
			$('#xilie_popup_shiyongyue').html(xl_yu_e_shiyong);
			$('#xilie_input_shiyongyue').show();
		}
	}
	if (id == 'jiaofei_popup_tuihuan') $('#tuihuan_popup_dingj').html(y.toFixed(2));

}
function jiaofei_submit(id) {
	var request = get_request();
	var dat = "";
	var url_str = "";
	if (id == "dc") {
		dat = $('#danci_from').serialize() + "&" + $('#jiaofei_time').serialize();
		var dc_dj = get_danjia("#jiaofei_danjia","#danci_dj");
		url_str = 'ajax.php?action=save_jiaofei_dc&vid=' + request['vid'] + '&dc_dj=' + dc_dj;
	}
	if (id == "xl") {
		dat = $('#xilie_from').serialize() + "&" + $('#jiaofei_time').serialize();
		var xl_dj = get_danjia("#jiaofei_xilie","#xilie_dj");
		url_str = 'ajax.php?action=save_jiaofei_xl&vid=' + request['vid'] + '&xl_dj=' + xl_dj;		
	}
	if ($('#jiaofei_selct_consultant').val() == "") {
		alert("请先选择咨询师");
		return;
	}
	url_str = url_str + '&consul_id=' + $('#jiaofei_selct_consultant').val();
	if (Math.abs(parseFloat($('#quanju_dingjin').val())) > 0.01) {
		url_str = url_str + '&dingjin=' + $('#quanju_dingjin').val();
	}
	var bushi = $('#jiaofei_geiyu_bushi').val();
	if (parseInt(bushi) > 0) {
		url_str = url_str + '&bushi=' + bushi;
	}
	var bc_bushi = $('#jiaofei_benci_bushi').val();
	if (parseInt(bc_bushi) > 0) {
		url_str = url_str + '&bc_bushi=' + bc_bushi;
	}
	url_str += ('&date=' + request['date']);
	if (dat != "")
	$.ajax({
		url : url_str,
		dataType: "json", 
		type : 'post',
		//async: false,
		data : dat,
		success : function(data){
			if (data && data.success == -1) {
				window.location.href="index.php"; 
			}
			if (data && data.success == 1) {				
				alert(data.msg);
				$('#jiaofei_geiyu_bushi').val("");
				$('#quanju_dingjin').val("");
				$('#danci_zixunsc').val("");
				$('#xilie_zixunsc').val("");
				$('#xilie_fenqi').val("");
				$('#jiaofei_xilie').html("");
				$('#xilie_heji').val("");
				$('#xilie_chaoshi').val("");
				$('#xilie_bujiao').val("");
				jiaofei_refresh();
				J.Router.goTo('#index_section');
			}
			$('#jingle_popup').css('display','none');			
		},
		error : function(){
			alert('网络错误');
		}
	});
}
function dingjin_tuihuan() {
	var request = get_request();
	$.ajax({
		url : 'ajax.php?action=tuihuan_dingjin&jine=' + $('#dingjin_popup').html() + '&name=' + request['name'],
		dataType: "json", 
		type : 'post',
		//async: false,
		data : "",
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (data.success == 1) {
				$('#jingle_popup').css('display','none');
				alert(data.msg);
				jiaofei_refresh();
			}
		},
		error : function(){
			alert('网络错误');
		}
	});
}

//////////////////////////////////////////////////////////////////////////////////////////
function change_ps() {
	if ($('#password').val() =="" || $('#password1').val() =="" || $('#passwold').val() =="" ) {
		alert('请填写打完星号的项目');
		return;
	}
	
	if (($('#password').val() != $('#password1').val()) ) {
		alert('两次密码不一样');
		return;
	}
	
	$.ajax({
		url : 'ajax.php?action=changeps',
		dataType: "json", 
		data : $('#changeps').serialize(),
		type : 'post',
		success : function(data){
			alert(data.msg);
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
		},
		error : function(){
			alert('网络错误');
		}
	})
}
function show_ticheng_ps(id) {
	J.popup({
		tplId : id,
		pos : 'center'
	})
}
function ticheng_chakan() {
	$.ajax({
		url : 'ajax.php?action=chakan_ticheng',
		dataType: "json", 
		data : $('#chakan_ps').serialize(),
		type : 'post',
		async: false,
		success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			$('#jingle_popup').hide();	
			if (!data.result.hgo) {
				alert(data.msg);
			} else {
				J.Router.goTo(data.result.href);
			}
					
		},
		error : function(){
			alert('网络错误');
		}
	})
}