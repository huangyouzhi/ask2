/**
 * Created by Administrator on 2017/4/2.
 */
var popusertimer = null;
var query = '?';
var has_submit=false;
var _el;//加载进度条元素
window.alert=function(msg){
    new $.zui.Messager(msg, {
        icon: 'bell',
        time:1000,
        placement: 'center' // 定义显示位置
    }).show();
}
function bytes(str) {
    var len = 0;
    for (var i = 0; i < str.length; i++) {
        if (str.charCodeAt(i) > 127) {
            len++;
        }
        len++;
    }
    return len;
}

//登录弹窗
function login(){


var url=g_site_url+"index.php?user/ajaxpoplogin";
var myModalTrigger = new $.zui.ModalTrigger({url:url});
myModalTrigger.show();
}
function check_phone(_phone){
	
	 if(!(/^1(3|4|5|7|8)\d{9}$/.test(_phone))){ 
	       
	        return false; 
	    }else{
	    	return true;
	    }
}
function gosms(){
	 var _phone = $("#userphone").val();
	  var _rs=check_phone(_phone);
	if(!_rs){
		 alert("手机号码有误");  
		 return false;
	}
  $.post(g_site_url+"index.php?user/getsmscode", {phone: _phone}, function(flag) {
	   flag=$.trim(flag);
  if(flag==1){
  	var _timecount=60;
  	var _listener= setInterval(function(){
  		--_timecount;
  		$("#testbtn").html(_timecount+"s后获取");
  		$("#testbtn").addClass("disabled");
  		if(_timecount<=0){
  			clearInterval(_listener);
  		$("#testbtn").removeClass("disabled").html("发送短信");;
  		}
  	},1000);
  }else{
	   if(flag==2){
		   alert("手机号已存在");
	   }else if(flag==2){
		   alert("手机号不正确");
	   }else{
		   alert("稍后在获取验证码");
	   }
	  
  }
  });
}
/*关注用户*/
function attentto_user(uid) {
    if (g_uid == 0) {
        login();
    }
    if(g_uid==uid){
    	alert("不能关注自己");
    	return false;
    }
    $.post(g_site_url + "index.php?user/attentto", {uid: uid}, function(msg) {
        if (msg == 'ok') {
            if ($("#attenttouser_"+uid).hasClass("following")) {
                $("#attenttouser_"+uid).removeClass("btn-default following");
                $("#attenttouser_"+uid).addClass("btn-success follow");
                
                $("#attenttouser_"+uid).html('<i class="fa fa-plus"></i><span>关注</span>');
            } else {
                $("#attenttouser_"+uid).removeClass("btn-success follow");
                $("#attenttouser_"+uid).addClass("btn-default following");
               
                $("#attenttouser_"+uid).html('<i class="fa fa-check"></i><span >已关注</span>');
            }
            $("#attenttouser_"+uid).hover(function(){
            	
            	 if ($("#attenttouser_"+uid).hasClass("following")){
            		 $("#attenttouser_"+uid).html('<i class="fa fa-times"></i><span >取消关注</span>');
            	 }
            	
            },function(){
            	 if ($("#attenttouser_"+uid).hasClass("following")){
            		 $("#attenttouser_"+uid).html('<i class="fa fa-check"></i><span >已关注</span>');
            	 }
            })
        }
    });
}

/*关注用户*/
function attentto_user_index(uid) {
    if (g_uid == 0) {
        login();
    }
    if(g_uid==uid){
    	alert("不能关注自己");
    	return false;
    }
    $.post(g_site_url + "index.php?user/attentto", {uid: uid}, function(msg) {
        if (msg == 'ok') {
            if ($("#attenttouser_"+uid).hasClass("following")) {
                $("#attenttouser_"+uid).removeClass("following");
                $("#attenttouser_"+uid).addClass(" follow");
                
                $("#attenttouser_"+uid).html('<i class="fa fa-plus"></i><span>关注</span>');
            } else {
                $("#attenttouser_"+uid).removeClass("follow");
                $("#attenttouser_"+uid).addClass("following");
               
                $("#attenttouser_"+uid).html('<i class="fa fa-check"></i><span >已关注</span>');
            }
            $("#attenttouser_"+uid).hover(function(){
            	
            	 if ($("#attenttouser_"+uid).hasClass("following")){
            		 $("#attenttouser_"+uid).html('<i class="fa fa-times"></i><span >取消关注</span>');
            	 }
            	
            },function(){
            	 if ($("#attenttouser_"+uid).hasClass("following")){
            		 $("#attenttouser_"+uid).html('<i class="fa fa-check"></i><span >已关注</span>');
            	 }
            })
        }
    });
}
/*关注分类*/
function attentto_cat(cid) {
    if (g_uid == 0) {
        login();
    }
 
    $.post(g_site_url + "index.php?category/attentto", {cid: cid}, function(msg) {
        if (msg == 'ok') {
            if ($("#attenttouser_"+cid).hasClass("following")) {
                $("#attenttouser_"+cid).removeClass("btn-default following");
                $("#attenttouser_"+cid).addClass("btn-success follow");
                
                $("#attenttouser_"+cid).html('<i class="fa fa-plus"></i><span>关注</span>');
            } else {
                $("#attenttouser_"+cid).removeClass("btn-success follow");
                $("#attenttouser_"+cid).addClass("btn-default following");
               
                $("#attenttouser_"+cid).html('<i class="fa fa-check"></i><span >已关注</span>');
            }
            $("#attenttouser_"+cid).hover(function(){
            	
            	 if ($("#attenttouser_"+cid).hasClass("following")){
            		 $("#attenttouser_"+cid).html('<i class="fa fa-times"></i><span >取消关注</span>');
            	 }
            	
            },function(){
            	 if ($("#attenttouser_"+cid).hasClass("following")){
            		 $("#attenttouser_"+cid).html('<i class="fa fa-check"></i><span >已关注</span>');
            	 }
            })
        }else{
        	if(msg == '-1'){
        		alert("先登录在关注");
        	}else{
        		alert(msg);
        	}
        }
    });
}
function checkall(checkname) {
    var chkall = $("#chkall:checked").val();
    if (chkall && (chkall === 'chkall')) {
        $("input[name^='" + checkname + "']").each(function() {
            $(this).prop("checked", "checked");
        });
    } else {
        $("input[name^='" + checkname + "']").each(function() {
            $(this).removeProp("checked");
        });
    }
}

function ajaxloading(text){
     _el=document.createElement("div");
    _el.id="ajax_loading";
    _el.innerHTML="<div style='border-radius: 5px;;font-size: 14px;;color: #fff;background: #000;opacity: 0.8;width: 80px;height: 80px;line-height:80px;text-align:center;position: fixed;top:50%;left: 48%;z-index:999999999;'>"+text+"</div>";
    document.body.appendChild(_el);
    return _el;
}
function removeajaxloading(){
    document.body.removeChild(_el);
}

function load_message_sowenda() {
	
    if (g_uid == 0) {
        return false;
    }
    $.ajax({
        type: "POST",
        async: false,
        cache: false,
        url:g_site_url + "index.php?user/ajaxloadmessage",
        dataType:"json",
        success: function(msg) {
        	
        	
        
        	var msg_count=parseInt(msg.msg_personal)+parseInt(msg.msg_system);
        	if(msg.msg_personal!=0){
        		$(".msg-count").html(msg_count).removeClass("hide").show();
        		$(".p-msg-count").html(msg.msg_personal).show();
        	}else{
        		$(".p-msg-count").hide();
        	}
        	if(msg.msg_system!=0){
        		$(".msg-count").html(msg_count).removeClass("hide").show();
        		$(".s-msg-count").html(msg.msg_system).show();
        	}else{
        		$(".s-msg-count").hide();
        	}
        	if(msg_count==0){
        		$(".msg-count").hide();
        		$(".p-msg-count").hide();
        		$(".s-msg-count").hide();
        	}else{
        		$(".msg-count").html(msg_count).removeClass("hide").show();
        	}
        	
	    	
	
        }
    });
   
}

function refresh_code() {
    var img = g_site_url + "index.php" + query + "user/code/" + Math.random();
    $('#verifylogincode').attr("src", img);
};

//验证码
function updatecode() {
  var img = g_site_url + "index.php" + query + "user/code/" + Math.random();
  $('#verifycode').attr("src", img);
}

//验证码检测
function check_code() {
  var code = $.trim($('#code').val());
  if ($.trim(code) == '') {
      $('#codetip').html("<i class='fa fa-exclamation mar-lr-1'></i>验证码错误");
      $('#codetip').attr('class', 'alert alert-warning input_error');
      return false;
  }
  $.ajax({
      type: "GET",
      async: false,
      cache: false,
      url: g_site_url + "index.php" + query + "user/ajaxcode/" + code,
      success: function(flag) {
          if (1 == flag) {
              $('#codetip').html("<i class='fa fa-check mar-lr-1'></i>验证码正确");
              $('#codetip').attr('class', 'alert alert-info input_ok');
              return true;
          } else {
              $('#codetip').html("<i class='fa fa-exclamation mar-lr-1'></i>验证码错误");
              $('#codetip').attr('class', 'alert alert-warning input_error');
              return false;
          }

      }
  });
}
//问题分类选择函数
function initcategory(category1) {
    var selectedcid1 = $("#selectcid1").val();
    $("#category1").html('');
    for (var i = 0; i < category1.length; i++) {
        var selected = '';
        if (selectedcid1 === category1[i][0]) {
            selected = ' selected';
        }
        $("#category1").append("<option value='" + category1[i][0] + "' " + selected + ">" + category1[i][1] + "</option>");
    }

}
var ctrdown=false;
var returndown=false;
function keydownlistener(){
	 var e = e||event;
	   var currKey = e.keyCode||e.which||e.charCode;
	   console.log(currKey);
	   if (currKey==17)
	   {
		   ctrdown=true;
	   }
	   if (currKey==13)
	   {
		   returndown=true;
	   }
	   if(returndown&&ctrdown){
		   postask();
	   }
}
function wxpay(type,typevalue,touser){
	
	
	var url=g_site_url+"index.php?user/ajaxpopwxpay/"+type+"/"+typevalue+"/"+touser;
	var myModalTrigger = new $.zui.ModalTrigger({url:url,size:'sm',title:'微信打赏-打开微信扫一扫'});
	myModalTrigger.show();
}
function viewanswer(answerid){
	
	
	var url=g_site_url+"index.php?question/ajaxviewanswer/"+answerid;
	var myModalTrigger = new $.zui.ModalTrigger({url:url,size:'sm',title:'付费偷看'});
	myModalTrigger.show();
}
function topickeydownlistener(e){
	 var e = e||event;
	   var currKey = e.keyCode||e.which||e.charCode;
	   console.log(currKey);
	   if (currKey==17)
	   {
		   ctrdown=true;
	   }
	   if (currKey==13)
	   {
		   returndown=true;
	   }
	   if(returndown&&ctrdown){
		   postarticle();
	   }
}
function postask(){
	 ctrdown=false;
	 returndown=false;
	var eidtor_content= $.trim($("#editor").val());
	if(eidtor_content==''){
		 eidtor_content= editor.getContent();
	}
	 var _chakanjine=0;
	 if(_chakanjine!=0){
		 if(_chakanjine>10||_chakanjine<0.1 ){
   		 alert("查看金额在0.1-10元之间");
   		 return false;
   	 }
	 }
	 var data=null;
	 if(needcode){
		  data={
		 			content:eidtor_content,
		 			chakanjine:_chakanjine,
		 			qid:$("#ans_qid").val(),
		 			title:$("#ans_title").val(),
		 			code:$("#code").val()
		 	}
	 }else{
		  data={
		   			content:eidtor_content,
		   			chakanjine:_chakanjine,
		   			qid:$("#ans_qid").val(),
		     			title:$("#ans_title").val(),
		   			
		   	}
	 }
	 
	   
		
	   if(has_submit){
		   alert("提交中,稍后操作....");
		   return false;
	   }
	  
	 var url="/?question/ajaxanswer";
	$.ajax({
       //提交数据的类型 POST GET
       type:"POST",
       //提交的网址
       url:url,
       //提交的数据
       data:data,
       async: false,
       //返回数据的格式
       datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
       //在请求之前调用的函数
       beforeSend:function(){has_submit=true; ajaxloading("提交中...");},
       //成功返回之后调用的函数             
       success:function(data){
       	var data=eval("("+data+")");
          if(data.message=='ok'||data.message.indexOf('成功')>=0){
       	   new $.zui.Messager('回答成功!', {
       		   type: 'success',
       		   close: true,
          	    placement: 'center' // 定义显示位置
          	}).show();
       	   setTimeout(function(){
                  window.location.reload();
              },1500);
          }else{
       	   new $.zui.Messager(data.message, {
           	   close: true,
           	    placement: 'center' // 定义显示位置
           	}).show();
          }
         
        
       }   ,
       //调用执行后调用的函数
       complete: function(XMLHttpRequest, textStatus){
    	   removeajaxloading();
    	   has_submit=false;
       },
       //调用出错执行的函数
       error: function(){
           //请求出错处理
       }         
    });
}
function postarticle(){
	 ctrdown=false;
	 returndown=false;
	//var artcomment=$.trim($("#editor").val());
	 var artcomment=$.trim($(".comment-area").val());
    var _tid=$("#artid").val();
    var _artitle=$("#artitle").val();
    
	var url="/?topic/ajaxpostcomment";
	if(artcomment==''){
	    new $.zui.Messager("评论不能为空", {
    	    icon: 'bell',
    	    placement: 'center' // 定义显示位置
    	}).show();
		return false;
	}
    $.ajax({
        //提交数据的类型 POST GET
        type:"POST",
        //提交的网址
        url:url,
        //提交的数据
       data:{title:_artitle,tid:_tid, content:artcomment},
        //返回数据的格式
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
        beforeSend: function () {
    
           ajaxloading("提交中...");
        },
        //成功返回之后调用的函数
        success:function(data){
          
             
            var  jsondata=eval('(' + data+ ')');
           
           
           new $.zui.Messager(jsondata.msg, {
        	    icon: 'bell',
        	    placement: 'center' // 定义显示位置
        	}).show();
           
           if(jsondata.state==1){
        	   window.location.reload();
           }
           if(jsondata.state==-1){
        	   login();
           }
           
        }   ,
        complete: function () {
           removeajaxloading();
        },
       
        //调用出错执行的函数
        error: function(){
            //请求出错处理
        }
    });
}
function fillcategory(category2, value1, cateid) {
    var optionhtml = '<option value="0">不选择</option>';
    var selectedcid = 0;
    if (cateid === "category2") {
        selectedcid = $("#selectcid2").val();
    } else if (cateid === "category3") {
        selectedcid = $("#selectcid3").val();
    }
    $("#" + cateid).html("");
    for (var i = 0; i < category2.length; i++) {
        if (value1 === category2[i][0]) {
            var selected = '';
            if (selectedcid === category2[i][1]) {
                selected = ' selected';
                $("#" + cateid).show();
            }
            optionhtml += "<option value='" + category2[i][1] + "' " + selected + ">" + category2[i][2] + "</option>";
        }
    }
    $("#" + cateid).html(optionhtml);
}
setTimeout(function(){
	$(".fixedbottom").removeClass("hide").addClass("slideInUp animated ");
	
},3000);
setTimeout(function(){
	$(".btn-buy").removeClass("hide").addClass("lightSpeedIn animated ");
	$(".btn-down").removeClass("hide").addClass("lightSpeedIn animated ");
},3600);

$(function(){

	load_message_sowenda();
	$('[data-toggle="tooltip"]').tooltip('hide');
    $(".user,.notification ").hover(function(){

        $(this).find(".dropdown-menu").show();
        $(this).find(".dropdown-menu").hover(function(){
        	$(this).find(".dropdown-menu").show();

        },function(){
        	$(this).find(".dropdown-menu").hide();

        })
    },function(){
    	$(this).find(".dropdown-menu").hide();

    });
    $(".index .list-container .note-list li ").hover(function(){
    	$(this).addClass("lightSpeedIn animated ");
   
    },function(){
    	$(this).removeClass("lightSpeedIn animated ");

    });
    $.fn.smartFloat = function() { 
        var position = function(element) { 
            var top = element.position().top; //当前元素对象element距离浏览器上边缘的距离 
            var left = element.position().left; //当前元素对象element距离浏览器上边缘的距离 
            var pos = element.css("position"); //当前元素距离页面document顶部的距离 
            $(window).scroll(function() { //侦听滚动时 
                var scrolls = $(this).scrollTop(); 
                if (scrolls > top) { //如果滚动到页面超出了当前元素element的相对页面顶部的高度 
                    if (window.XMLHttpRequest) { //如果不是ie6 
                        element.css({ //设置css 
                            position: "fixed", //固定定位,即不再跟随滚动 
                            top: 60 //距离页面顶部为20 
                            
                        }).addClass("shadow"); //加上阴影样式.shadow 
                    } else { //如果是ie6 
                        element.css({ 
                            top: scrolls  //与页面顶部距离 
                        });     
                    } 
                }else { 
                    element.css({ //如果当前元素element未滚动到浏览器上边缘，则使用默认样式 
                        position: pos, 
                        top: top
                       
                    }).removeClass("shadow");//移除阴影样式.shadow 
                } 
            }); 
        }; 
        return $(this).each(function() { 
            position($(this));                          
        }); 
    }; 
  //分类选择
    $("#category1").change(function() {
        fillcategory(category2, $("#category1 option:selected").val(), "category2");
        $("#jiantou1").show();
        $("#category2").show();
    });
    $("#category2").change(function() {
        fillcategory(category3, $("#category2 option:selected").val(), "category3");
        $("#jiantou2").show();
        $("#category3").show();
    });
	$("#comfirm_pay").click(function(){
		 var _chakanjine=$("#chakanjine").val();
		 if(_chakanjine!=0){
    		 if(_chakanjine>10||_chakanjine<0.1 ){
        		 alert("查看金额在0.1-10元之间");
        		 return false;
        	 }
    		 $(".emoji-modal-wrap .fa-paypal").html(_chakanjine+"元");
    		
    	 }
		 $(".pay-money").hide();
		
		 $("#anscontent").focus();
	})
	   //显示回答偷看金额弹窗
          $(".emoji-modal-wrap .fa-paypal").click(function(){
        	 
    	 $(".pay-money").show();
    	 $(".pay-money .modal-body #chakanjine").focus();
     });
    //关闭问题
    $("#close_question").click(function() {
if (confirm('确定关闭该问题?') === true) {
	var url=g_site_url+"/?question/close/"+qid;
document.location.href = url;
}
});
	//删除问题
    $("#delete_question").click(function() {
if (confirm('确定删除问题？该操作不可返回！') === true) {
	var url=g_site_url+"/?question/delete/"+qid;
document.location.href = url;
}
});
    //发布文章评论
    $(".btn-cm-submit").click(function(){
    	postarticle();
    
        
    });
    $("#ajaxsubmitasnwer").click(function(){
    	postask();
    
        
    });

    
});