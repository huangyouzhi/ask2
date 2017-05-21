/**
 * Created by Administrator on 2016/11/4.
 */

window.alert=function(msg){
	$.tips({
        content:msg,
        stayTime:1000,
        type:"info"
    });
}
  function goTop(acceleration, time) {
acceleration = acceleration || 0.1;
time = time || 16;
var x1 = 0;
var y1 = 0;
var x2 = 0;
var y2 = 0;
var x3 = 0;
var y3 = 0;
if (document.documentElement) {
x1 = document.documentElement.scrollLeft || 0;
y1 = document.documentElement.scrollTop || 0;
}
if (document.body) {
x2 = document.body.scrollLeft || 0;
y2 = document.body.scrollTop || 0;
}
var x3 = window.scrollX || 0;
var y3 = window.scrollY || 0;
// 滚动条到页面顶部的水平距离
var x = Math.max(x1, Math.max(x2, x3));
// 滚动条到页面顶部的垂直距离
var y = Math.max(y1, Math.max(y2, y3));
// 滚动距离 = 目前距离 / 速度, 因为距离原来越小, 速度是大于 1 的数, 所以滚动距离会越来越小
var speed = 1 + acceleration;
window.scrollTo(Math.floor(x / speed), Math.floor(y / speed));
// 如果距离不为零, 继续调用迭代本函数
if (x > 0 || y > 0) {
var invokeFunction = "goTop(" + acceleration + ", " + time + ")";
window.setTimeout(invokeFunction, time);
}
}
$(function (){
    $(".ui-icon-close").bind("click",function(event){

        $(this).parent().find("input").val("");
        event.stopPropagation();    //  阻止事件冒泡
    });

    $(".fixed-bar .ui-icon-refresh").bind("click",function(event){
        window.location.reload();
        event.stopPropagation();
    });
    $(".fixed-bar .ui-icon-home").bind("click",function(event){
        window.location.href=g_site_url;
        event.stopPropagation();
    });

    $(".fixed-bar .ui-icon-gototop").bind("click",function(event){
    	 goTop();
        event.stopPropagation();
    });
    var tab = new fz.Scroll('.ui-tab', {
        role: 'tab',
        autoplay: false,
        interval: 3000
    });

  
  
  
  

});
$("input").focus(function(){
	
	     var target=$(".xuanfu");
            target.addClass("ui-hide");




}).blur(function(){
	var target=$(".xuanfu");
  target.removeClass("ui-hide");
	});
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
function attentto_question(qid) {
    if (g_uid == 0) {
        window.location.href=g_site_url + "index.php" + query + "user/login/";
    }
    var ael='';
   
    $.ajax({
        type: "POST",
      
        data:{qid: qid},
        url:g_site_url + "index.php?question/attentto",
      //在请求之前调用的函数
        beforeSend:function(){
        	ael=$.loading({
     	        content:'加载中...',
     	    })
        },
        success: function(msg) {
        	ael.loading("hide");
        	 if (msg == 'ok') {
             	
                 if ($("#attentquestion").hasClass("button_attention")) {
                 	$("#attentquestion").removeClass("button_attention");
                 
                   var html_element=' <div class="q-unfollower "> <i class="ui-icon-add"></i>  <span class="q-follower-txt">    关注';
             	  
                   
                   

                   
                   
             	  $("#attentquestion").html(html_element);
                 } else {
                 	$("#attentquestion").addClass("button_attention");
                 	var html_element='<div class="q-follower "><i class="ui-icon-success-block"></i>             <span class="q-follower-txt">                   已关注             </span>          </div>   </span> </div>';
                     $("#attentquestion").html(html_element);
                 }
               
             }
        },
        //调用执行后调用的函数
        complete: function(XMLHttpRequest, textStatus){
        	ael.loading("hide");
        }
    });
    
   
}
/*关注用户*/
function attentto_user(uid) {
    if (g_uid == 0) {
        window.location.href=g_site_url + "index.php?user/login";
        return false;
    }
    $.post(g_site_url + "index.php?user/attentto", {uid: uid}, function(msg) {
        if (msg == 'ok') {
            if ($("#attenttouser_"+uid).hasClass("button_attention")) {
                $("#attenttouser_"+uid).removeClass("button_attention");
                $("#attenttouser_"+uid).addClass("button_followed");
                $("#attenttouser_"+uid).html("取消关注");
              
            } else {
                $("#attenttouser_"+uid).removeClass("button_followed");
                $("#attenttouser_"+uid).addClass("button_attention");
                $("#attenttouser_"+uid).html("+关注");
                
            }
          
        }else{
             if(msg == 'self'){
            	 alert('不能关注自己');
             }
        }
    });
}
//验证码
function updatecode() {
	
  var img = g_site_url + "index.php" + query + "user/code/" + Math.random();
  $('#verifycode').attr("src", img);
}

//验证码检测
function check_code() {
  var code = $.trim($('#code').val());
  if ($.trim(code) == '') {
     
      el2=$.tips({
          content:'验证码错误',
          stayTime:1000,
          type:"info"
      });
      return false;
  }
  $.ajax({
      type: "GET",
      async: false,
      cache: false,
      url: g_site_url + "index.php" + query + "user/ajaxcode/" + code,
      success: function(flag) {
          if (1 == flag) {
              
              el2=$.tips({
                  content:'验证码正确',
                  stayTime:2000,
                  type:"success"
              });
              return true;
          } else {
            
              el2=$.tips({
                  content:'验证码错误',
                  stayTime:1000,
                  type:"info"
              });
              return false;
          }

      }
  });
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


