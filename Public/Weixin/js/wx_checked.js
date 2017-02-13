$(document).ready(function() {


	$("#friend2").bind("click", function() {
		$("#myfriend").addClass("myfriend");
	});

	$("#friend1").bind("click", function() {
		$("#myfriend").removeClass("myfriend");
	});

	$('.label1').bind("click", function(){
   		var radioId = $(this).attr('name');
    	$('.label1').removeClass('check') && $(this).addClass('check');
    	$('input[type="radio"]').removeAttr('checked') && $('#' + radioId).attr('checked', 'checked');
    	$('#gender').attr("value",$(this).attr("for"));
 	});

 	$('.label2').bind("click", function(){
   		var radioId = $(this).attr('name');
    	$('.label2').removeClass('check') && $(this).addClass('check');
    	$('input[type="radio"]').removeAttr('checked') && $('#' + radioId).attr('checked', 'checked');
    	$('#friway').attr("value",$(this).attr("for"));
 	});

 	$('.label3').bind("click", function(){
   		var radioId = $(this).attr('name');
    	$('.label3').removeClass('check') && $(this).addClass('check');
    	$('input[type="radio"]').removeAttr('checked') && $('#' + radioId).attr('checked', 'checked');
    	$('#frigender').attr("value",$(this).attr("for"));
 	});

 	
/*
 	if ($(".state").text() == "对话中") {
 		$(".state").addClass("bg");
 	}


 	if ($(".cost").text() == "免费") {
 		$(".cost").addClass("color");
 	}
*/
 // 信息表单年龄select生成option选项
 	for (var i = 1; i <= 150; i ++) {
 		$("#age").append("<option value='"+i+"'>"+i+"</option>");
 	};

 	for (var i = 1; i <= 150; i ++) {
 		$("#friage").append("<option value='"+i+"'>"+i+"</option>");
 	};
 	



	// 推荐给朋友 黑色透明弹窗
	$(".recommend").bind("click", function() {
		$(".black").addClass("transform");
	});

	$(".black").bind("click", function() {
		$(".black").removeClass("transform");
	});



	
	

});
