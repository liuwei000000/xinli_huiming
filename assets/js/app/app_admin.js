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
            var sectionId = '#'+k+'_admin';
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


App.page('admin', function(){	
	this.load = admin_refresh;
	this.init = function () {
		set_time('#admin_start', admin_refresh);
		set_time('#admin_end', admin_refresh);
	}
    /*this.init = function(){
        J.Refresh({
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
        });
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
    }	*/
})

App.page('consultant', function() {
	this.load = consultant_refresh;
    this.init =  function(){
		J.Refresh('#refresh_consulant','pullUp', function(){
		var scroller = this;
		setTimeout(function () {
			consultant_refresh($('div[id="refresh_consulant"] li[class="grid"]').length, PAGE_NUM);
			scroller.refresh();
			J.showToast('加载成功','success');
		}, 500);
    	});	
	}
	$('header a[id=logout]').on('click',logout);
})

App.page('assistant',function(){
	this.load = assistant_refresh;
	this.init = function(){
		J.Refresh('#refresh_assistant','pullUp', function(){
		var scroller = this;
		setTimeout(function () {
			assistant_refresh($('div[id="refresh_assistant"] li[class="grid"]').length, PAGE_NUM);
			scroller.refresh();
			J.showToast('加载成功','success');
		}, 500);
    	});
	}
})

App.page('table', function() {
	this.load = table_refresh;
	this.init = function () {
		J.Refresh('#refresh_table','pullUp', function(){
		var scroller = this;
		setTimeout(function () {
			table_refresh(true, $('div[id="table-1-body"] ul').length, PAGE_NUM);
			scroller.refresh();
			J.showToast('加载成功','success');
		}, 500);
		});
		set_time('#admin_table_start',can_table_refresh);
		set_time('#admin_table_end', can_table_refresh);		
	}
})

$(function(){
    App.run();
})

//#########################################################################
function consultant_refresh(offset, count) {
	var request = get_request();
	var offset = arguments[0] ? arguments[0] : 0;
  	var count = arguments[1] ? arguments[1] : PAGE_NUM;
  	$.ajax({
		url : 'ajax_admin.php?action=get_consultant&offset='+offset+'&count='+count + '&date=' + request['date'],
		dataType: "json", 
        type : 'post',
		data : $('#consultant_search').serialize(),
		async: false,
        success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (!data.success) {
				alert(data.msg);
				return;
			}
			update_script(data.result, '#consultant_template', '#consultant_list', offset > 0 && data.have_data, data.have_data != 1);
        },
        error : function(){
			alert("网络出错！");
        }
     });
}

function assistant_refresh(offset, count) {
	var request = get_request();	
	var offset = arguments[0] ? arguments[0] : 0;
  	var count = arguments[1] ? arguments[1] : PAGE_NUM;
  	$.ajax({
		url : 'ajax_admin.php?action=get_assistant&offset=' + offset + '&count=' + count + '&date=' + request['date'],
		dataType: "json", 
        type : 'post',
		data : $('#assistant_search').serialize(),
		async: false,
        success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (!data.success) {
				alert(data.msg);
				return;
			}
			update_script(data.result, '#assistant_template', '#assistant_list', offset > 0 && data.have_data, data.have_data != 1);			
        },
        error : function(){
			alert("网络出错！");
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
			alert("网络出错！");
        }
     });
}
function create_link(id, filter_time) {
	var filter_time='&admin_start=' + $('#admin_start').val() + '&admin_end=' + $('#admin_end').val();	
	$('#'+id).attr('href', '#table_admin?type=' + id + filter_time);
}

function admin_refresh(not_date) {
	if (not_date != true) {
		//s = '<label>日期*</label>' + get_now_month_option("admin_date");
		//$('#admin_date_div').html(s); 
		$('#admin_start').val(get_now());
		$('#admin_end').val(get_now());
	}
	create_link
	var filter_time='admin_start=' + $('#admin_start').val() + '&admin_end=' + $('#admin_end').val();
	create_link('admin_jiaofei', filter_time);
	create_link('admin_zijinsr', filter_time);
	create_link('admin_dingjinsr', filter_time);
	create_link('admin_conusl', filter_time);
	create_link('admin_assis', filter_time);
	create_link('admin_visitor', filter_time);
	create_link('admin_yuyue', filter_time);
	create_link('admin_huifang', filter_time);
	create_link('admin_bushi', filter_time);
	create_link('admin_zldenglu', filter_time);	

	/*$('#admin_jingru').attr('href',""); //净入
	$('#admin_total').attr('href',""); //总入
		
	$('#admin_visitor').html(data.result.vis_sum);	*/
	///////////////////////////////////////////////////////////////////////
  	$.ajax({
		url : 'ajax_admin.php?action=get_admin_refresh',
		dataType: "json", 
        type : 'post',
		data : $('#admin_form').serialize(),
		async: false,
        success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (!data.success) {
				alert(data.msg);
				return;
			}
			$('#admin_jingru').html(data.result.visitor); //净入
			$('#admin_total').html(data.result.sum_cash); //总入
			
			$('#admin_xs_jiaofei').html(data.result.sum_cash)  //客户缴费
			$('#admin_xs_zijinsr').html(data.result.sum_con_cash);  //咨询			
			$('#admin_xs_dingjinsr').html(data.result.dingjin);	 //订金
			
			$('#admin_xs_consul').html(data.result.consul);			
			$('#admin_xs_assis').html(data.result.assis);
			$('#admin_xs_visitor').html(data.result.vis_sum);
			$('#admin_xs_yuyue').html(data.result.yuyue_sum);
			$('#admin_xs_huifang').html(data.result.huifang_sum);
			$('#admin_xs_bushi').html(data.result.bushi_sum);
			$('#admin_xs_zldenglu').html(data.result.zhulidl_sum);
        },
        error : function(){
			alert("网络出错！");
        }
     });	
}

function table_refresh(not_date, offset, count) {
	var request = get_request();	
	if (not_date != true) {
		//s = '<label>日期*</label>' + get_now_month_option("admin_date");
		//$('#admin_date_div').html(s); 
		$('#admin_table_start').val(request['admin_start']);
		$('#admin_table_end').val(request['admin_end']);
	}
	var offset = arguments[1] ? arguments[1] : 0;
  	var count = arguments[2] ? arguments[2] : PAGE_NUM;
	$.ajax({
		url : 'ajax_admin.php?action=table&offset=' + offset + '&count=' + count+ '&type=' + request['type'],
		dataType: "json",
        type : 'post',
		data : $('#admin_table_from').serialize(),
		async: false,
        success : function(data){
			if (data.success == -1) {
				window.location.href="index.php"; 
			}
			if (!data.success) {
				alert(data.msg);
				return;
			}
			update_script(data.result, '#tablehead_template', '#table-head');
			update_script(data.result, '#tablebody_template', '#table-1-body', offset > 0 && data.have_data, data.have_data != 1);
			$('#table_title').html(data.result.title+ '[' +  $('div[id="table-1-body"] ul').length + '/' + data.result.c + ']');
			var st = new SortableTable(document.getElementById("table-1"),
				data.result.st);
			st.onsort = function () {
				var rows = st.tBody.children;
				var l = rows.length;
				for (var i = 0; i < l; i++) {
					removeClassName(rows[i], i % 2 ? "odd" : "even");
					addClassName(rows[i], i % 2 ? "even" : "odd");
				}
			};
        },
        error : function(){
			alert("网络出错！");
        }
     });
}

function can_table_refresh() {
	$('#table-1-body').html("");
	table_refresh(true);
}