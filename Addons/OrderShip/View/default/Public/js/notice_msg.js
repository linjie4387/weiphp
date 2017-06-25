function show_load(str){
	$("#op_loadbox .msg").html(str);
	$("#op_loadbox .msg").show();
}
function hide_load(str){
	$("#op_loadbox .msg").hide();
}


//进度条开始 
function load_start(){
  $("#loading_div").css("display",""); 
  $.AMUI.progress.start();//进度条开始 
}
//进度条结束 
function load_end(){
  $("#loading_div").css("display","none"); 
  $.AMUI.progress.done();//进度条结束 
}

//提示框 非指定位置显示黄色
function comm_alert_y(info) {
    comm_note_info(info,"yellow");
}
//提示框 非指定位置显示 绿色
function comm_alert_g(info) {
    comm_note_info(info,"green");
}

 //提示框 非指定位置显示 yellow green
function comm_note_info(info,color_type) {
    $('#op_result').css('height','auto');
    $('#op_result').css('top','10%');
    $('#op_result').css('left','10%');
    $('#op_result').css('right','10%');
    var color_value = 'box_cue_' + color_type;
    $("#op_result").attr("class", color_value);
    $("#op_result").html(info);
    $("#op_result").animate({opacity: 'show'}, 'slow');
    setTimeout("$('#op_result').animate({opacity:'hide'},'slow');", 3000);
}

//提示框 指定位置显示黄色
function comm_alert_e_y(info,evt) {
    comm_note_info_e(info,"yellow",evt);
}
//提示框 指定位置显示 绿色
function comm_alert_e_g(info,evt) {
    comm_note_info_e(info,"green",evt);
}

//提示框
function comm_note_info_e(info,color_type,evt) { 
	//alert($(document).scrollTop());
    var iHeight = parseInt(document.documentElement.scrollHeight);
    var _event = evt ? evt : window.event; 
    var scrollHeight = $(document).scrollTop() - parseInt('80');
    $('#op_result').css('top', _event.clientY + scrollHeight + 'px');
    $('#op_result').css('left','10%');
    $('#op_result').css('right','10%');
    // $('#op_result').css('left', _event.clientX + 'px');
    var color_value = 'box_cue_' + color_type;
    $("#op_result").attr("class", color_value);
    $("#op_result").html(info);
    $("#op_result").animate({opacity: 'show'}, 'slow');
    setTimeout("$('#op_result').animate({opacity:'hide'},'slow');", 3000);
}
