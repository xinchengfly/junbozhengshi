(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-login-openlogin"],{"0436":function(e,t,i){"use strict";i.r(t);var o=i("21a7"),n=i.n(o);for(var a in o)"default"!==a&&function(e){i.d(t,e,(function(){return o[e]}))}(a);t["default"]=n.a},"21a7":function(e,t,i){"use strict";i("b64b"),i("ac1f"),i("5319"),Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var o={data:function(){return{formData:{mobile:"",code:""},loging_password:"",register:{mobile:"",password:"",repassword:"",code:""},resetpassword:{mobile:"",password:"",repassword:"",code:""},is_send:!1,send_btn_txt:"获取验证码",second:60,ip:"",isShow:!0,is_login:1,is_code:!1,phoneHeight:0,isRead:!0,showWeixin:!1,showApple:!1}},onShow:function(){this.init()},onLoad:function(){this.getData()},methods:{init:function(){var e=this;uni.getSystemInfo({success:function(t){e.phoneHeight=t.windowHeight}}),plus.runtime.isApplicationExist({pname:"com.tencent.mm",action:"weixin://"})&&(e.showWeixin=!0),uni.getSystemInfo({success:function(t){var i=t.system.replace(/iOS /,"");"ios"==t.platform&&i>=13&&(e.showApple=!0)}})},getData:function(){var e=this;e._get("user.userapple/policy",{},(function(t){console.log(t),e.service=t.data.service,e.privacy=t.data.privacy}))},formSubmit:function(){var e=this,t={mobile:e.formData.mobile},i="";if(e.isRead){if(!/^1(3|4|5|6|7|8|9)\d{9}$/.test(e.formData.mobile))return console.log(e.formData.mobile),void uni.showToast({title:"手机有误,请重填！",duration:2e3,icon:"none"});if(e.is_code){if(console.log(e.is_code),""==e.formData.code)return void uni.showToast({title:"验证码不能为空！",duration:2e3,icon:"none"});t.code=e.formData.code,i="user.useropen/smslogin"}else{if(""==e.loging_password)return void uni.showToast({title:"密码不能为空！",duration:2e3,icon:"none"});t.password=e.loging_password,i="user.useropen/phonelogin"}uni.showLoading({title:"正在提交"}),e._post(i,t,(function(t){uni.setStorageSync("token",t.data.token),uni.setStorageSync("user_id",t.data.user_id);var i=uni.getStorageSync("currentPage"),o=uni.getStorageSync("currentPageOptions");if(Object.keys(o).length>0){for(var n in i+="?",o)i+=n+"="+o[n]+"&";i=i.substring(0,i.length-1)}e.gotoPage(i)}),!1,(function(){uni.hideLoading()}))}else uni.showToast({title:"请同意并勾选协议内容",duration:2e3,icon:"none"})},registerSub:function(){var e=this;if(!/^1(3|4|5|6|7|8|9)\d{9}$/.test(e.register.mobile))return console.log(e.register.mobile),void uni.showToast({title:"手机有误,请重填！",duration:2e3,icon:"none"});""!=e.register.code?e.register.password.length<6?uni.showToast({title:"密码至少6位数！",duration:2e3,icon:"none"}):e.register.password===e.register.repassword?e.isRead?(e.register.invitation_id=uni.getStorageSync("invitation_id")?uni.getStorageSync("invitation_id"):0,e.register.reg_source="app",e.register.referee_id=uni.getStorageSync("referee_id"),uni.showLoading({title:"正在提交"}),e._post("user.useropen/register",e.register,(function(t){uni.showToast({title:"注册成功",duration:3e3}),e.formData.mobile=e.register.mobile,e.register={mobile:"",password:"",repassword:"",code:""},e.second=0,e.changeMsg(),e.is_login=1}),!1,(function(){uni.hideLoading()}))):uni.showToast({title:"请同意并勾选协议内容",duration:2e3,icon:"none"}):uni.showToast({title:"两次密码输入不一致！",duration:2e3,icon:"none"}):uni.showToast({title:"验证码不能为空！",duration:2e3,icon:"none"})},resetpasswordSub:function(){var e=this;/^1(3|4|5|6|7|8|9)\d{9}$/.test(e.resetpassword.mobile)?""!=e.resetpassword.code?e.resetpassword.password.length<6?uni.showToast({title:"密码至少6位数！",duration:2e3,icon:"none"}):e.resetpassword.password===e.resetpassword.repassword?(uni.showLoading({title:"正在提交"}),e._post("user.useropen/resetpassword",e.resetpassword,(function(t){uni.showToast({title:"重置成功",duration:3e3}),e.formData.mobile=e.resetpassword.mobile,e.resetpassword={mobile:"",password:"",repassword:"",code:""},e.second=0,e.changeMsg(),e.is_login=1}),!1,(function(){uni.hideLoading()}))):uni.showToast({title:"两次密码输入不一致！",duration:2e3,icon:"none"}):uni.showToast({title:"验证码不能为空！",duration:2e3,icon:"none"}):uni.showToast({title:"手机有误,请重填！",duration:2e3,icon:"none"})},isCode:function(){this.is_code?this.$set(this,"is_code",!1):this.$set(this,"is_code",!0),console.log(this.is_code)},sendCode:function(){var e=this;if(1==e.is_login){if(!/^1(3|4|5|6|7|8|9)\d{9}$/.test(e.formData.mobile))return void uni.showToast({title:"手机有误,请重填！",duration:2e3,icon:"none"})}else if(2==e.is_login){if(!/^1(3|4|5|6|7|8|9)\d{9}$/.test(e.register.mobile))return void uni.showToast({title:"手机有误,请重填！",duration:2e3,icon:"none"})}else if(0==e.is_login&&!/^1(3|4|5|6|7|8|9)\d{9}$/.test(e.resetpassword.mobile))return void uni.showToast({title:"手机有误,请重填！",duration:2e3,icon:"none"});var t="register",i=e.register.mobile;1==e.is_login?(t="login",i=e.formData.mobile):0==e.is_login&&(t="login",i=e.resetpassword.mobile),e._post("user.useropen/sendCode",{mobile:i,type:t},(function(t){1==t.code&&(uni.showToast({title:"发送成功"}),e.is_send=!0,e.changeMsg())}))},login:function(){var e=this;plus.oauth.getServices((function(t){var i=t[0];i.authResult?console.log("已经登陆认证"):i.authorize((function(t){uni.showLoading({title:"登录中",mask:!0}),e._post("user.useropen/login",{code:t.code,source:"app"},(function(t){uni.setStorageSync("token",t.data.token),uni.setStorageSync("user_id",t.data.user_id);var i=uni.getStorageSync("currentPage"),o=uni.getStorageSync("currentPageOptions");if(Object.keys(o).length>0){for(var n in i+="?",o)i+=n+"="+o[n]+"&";i=i.substring(0,i.length-1)}e.gotoPage(i)}),!1,(function(){uni.hideLoading()}))}),(function(e){console.log("登陆认证失败!"),uni.showModal({title:"认证失败1",content:JSON.stringify(e)})}))}),(function(e){console.log("获取服务列表失败："+JSON.stringify(e))}))},changeMsg:function(){this.second>0?(this.send_btn_txt=this.second+"秒",this.second--,setTimeout(this.changeMsg,1e3)):(this.send_btn_txt="获取验证码",this.second=60,this.is_send=!1)},xieyi:function(e){var t="";t="service"==e?this.service:this.privacy,uni.navigateTo({url:"/pages/webview/webview?url="+t})},appleLogin:function(){var e=this;uni.login({provider:"apple",success:function(t){uni.getUserInfo({provider:"apple",success:function(t){if("getUserInfo:ok"!==t.errMsg)return!1;uni.showLoading({title:"正在登录",mask:!0}),e._post("user.userapple/login",{invitation_id:e.invitation_id,openId:t.userInfo.openId,nickName:t.userInfo.fullName.giveName?t.userInfo.fullName.giveName:"",referee_id:uni.getStorageSync("referee_id"),source:"apple"},(function(e){uni.setStorageSync("token",e.data.token),uni.setStorageSync("user_id",e.data.user_id),uni.navigateBack()}),!1,(function(){uni.hideLoading()}))}})},fail:function(e){console.log("登录失败"),console.log(e)}})}}};t.default=o},"4b2b":function(e,t,i){"use strict";var o=i("5f81"),n=i.n(o);n.a},"5f81":function(e,t,i){var o=i("d87d");"string"===typeof o&&(o=[[e.i,o,""]]),o.locals&&(e.exports=o.locals);var n=i("4f06").default;n("23589994",o,!0,{sourceMap:!1,shadowMode:!1})},"6c13":function(e,t,i){"use strict";var o;i.d(t,"b",(function(){return n})),i.d(t,"c",(function(){return a})),i.d(t,"a",(function(){return o}));var n=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("v-uni-view",{staticClass:"login-container",class:e.theme()||"",style:"height: "+e.phoneHeight+"px;",attrs:{"data-theme":e.theme()}},[i("v-uni-view",{staticClass:"skip",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.gotoPage("/pages/index/index")}}},[e._v("跳过→")]),2==e.is_login?i("v-uni-view",{staticClass:"p-30-75"},[i("v-uni-view",{staticClass:"login_topbpx"},[i("v-uni-view",{staticClass:"login_tit"},[e._v("注册")]),i("v-uni-view",{staticClass:"login_top"},[e._v("已有账户，"),i("v-uni-text",{staticClass:"dominant",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.is_login=1}}},[e._v("立即登录")])],1)],1),i("v-uni-view",{staticClass:"group-bd"},[i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",placeholder:"请填写手机号",disabled:e.is_send},model:{value:e.register.mobile,callback:function(t){e.$set(e.register,"mobile",t)},expression:"register.mobile"}})],1)],1),i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",password:"true",placeholder:"请输入密码"},model:{value:e.register.password,callback:function(t){e.$set(e.register,"password",t)},expression:"register.password"}})],1)],1),i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",password:"true",placeholder:"请确认密码"},model:{value:e.register.repassword,callback:function(t){e.$set(e.register,"repassword",t)},expression:"register.repassword"}})],1)],1),i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 d-b-c input_botom"},[i("v-uni-input",{staticClass:"flex-1",attrs:{type:"number",placeholder:"请填写验证码"},model:{value:e.register.code,callback:function(t){e.$set(e.register,"code",t)},expression:"register.code"}}),i("v-uni-button",{staticClass:"get-code-btn",attrs:{type:"default",disabled:e.is_send},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.sendCode.apply(void 0,arguments)}}},[e._v(e._s(e.send_btn_txt))])],1)],1)],1)],1):e._e(),1==e.is_login?i("v-uni-view",{staticClass:"p-30-75"},[i("v-uni-view",{staticClass:"login_topbpx"},[i("v-uni-view",{staticClass:"login_tit"},[e._v("登录")]),i("v-uni-view",{staticClass:"login_top"},[e._v("还没有账号，"),i("v-uni-text",{staticClass:"dominant",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.is_login=2}}},[e._v("立即注册")])],1)],1),i("v-uni-view",{staticClass:"group-bd"},[i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",placeholder:"请填写手机号"},model:{value:e.formData.mobile,callback:function(t){e.$set(e.formData,"mobile",t)},expression:"formData.mobile"}})],1)],1),e.is_code?e._e():i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",password:"true",placeholder:"请输入密码"},model:{value:e.loging_password,callback:function(t){e.loging_password=t},expression:"loging_password"}})],1)],1),e.is_code?i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 d-b-c input_botom"},[i("v-uni-input",{staticClass:"flex-1",attrs:{type:"number",placeholder:"请填写验证码"},model:{value:e.formData.code,callback:function(t){e.$set(e.formData,"code",t)},expression:"formData.code"}}),i("v-uni-button",{staticClass:"get-code-btn",attrs:{type:"default",disabled:e.is_send},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.sendCode.apply(void 0,arguments)}}},[e._v(e._s(e.send_btn_txt))])],1)],1):e._e()],1)],1):e._e(),0==e.is_login?i("v-uni-view",{staticClass:"p-30-75"},[i("v-uni-view",{staticClass:"login_topbpx"},[i("v-uni-view",{staticClass:"login_tit"},[e._v("重置密码")]),i("v-uni-view",{staticClass:"login_top"},[i("v-uni-text",{staticClass:"dominant",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.is_login=1}}},[e._v("立即登录")])],1)],1),i("v-uni-view",{staticClass:"group-bd"},[i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",placeholder:"请填写手机号",disabled:e.is_send},model:{value:e.resetpassword.mobile,callback:function(t){e.$set(e.resetpassword,"mobile",t)},expression:"resetpassword.mobile"}})],1)],1),i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",password:"true",placeholder:"请输入密码"},model:{value:e.resetpassword.password,callback:function(t){e.$set(e.resetpassword,"password",t)},expression:"resetpassword.password"}})],1)],1),i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 input_botom"},[i("v-uni-input",{attrs:{type:"text",password:"true",placeholder:"请确认密码"},model:{value:e.resetpassword.repassword,callback:function(t){e.$set(e.resetpassword,"repassword",t)},expression:"resetpassword.repassword"}})],1)],1),i("v-uni-view",{staticClass:"form-level d-s-c"},[i("v-uni-view",{staticClass:"val flex-1 d-b-c input_botom"},[i("v-uni-input",{staticClass:"flex-1",attrs:{type:"number",placeholder:"请填写验证码"},model:{value:e.resetpassword.code,callback:function(t){e.$set(e.resetpassword,"code",t)},expression:"resetpassword.code"}}),i("v-uni-button",{staticClass:"get-code-btn",attrs:{type:"default",disabled:e.is_send},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.sendCode.apply(void 0,arguments)}}},[e._v(e._s(e.send_btn_txt))])],1)],1)],1)],1):e._e(),1==e.is_login?i("v-uni-view",{staticClass:" gray6 p-0-75",class:e.is_code?"d-e-c":"d-b-c"},[e.is_code?e._e():i("v-uni-view",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.is_login=0}}},[e._v("忘记密码?")]),i("v-uni-view",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.isCode()}}},[e._v(e._s(e.is_code?"使用密码登录":"使用验证码登录"))])],1):e._e(),i("v-uni-view",{staticClass:"d-s-c gray6 p-0-75 mt20",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.isRead=!e.isRead}}},[i("v-uni-view",{class:e.isRead?"active agreement":"agreement"}),e._v("我已阅读并接受"),i("v-uni-text",{staticClass:"dominant",on:{click:function(t){t.stopPropagation(),arguments[0]=t=e.$handleEvent(t),e.xieyi("service")}}},[e._v("《用户协议》")]),e._v("和"),i("v-uni-text",{staticClass:"dominant",on:{click:function(t){t.stopPropagation(),arguments[0]=t=e.$handleEvent(t),e.xieyi("privacy")}}},[e._v("《隐私政策》")])],1),2==e.is_login?i("v-uni-view",{staticClass:"btns p-30-75",staticStyle:{"padding-top":"80rpx"}},[i("v-uni-button",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.registerSub.apply(void 0,arguments)}}},[e._v("注册")])],1):e._e(),1==e.is_login?i("v-uni-view",{staticClass:"btns p-30-75",staticStyle:{"padding-top":"80rpx"}},[i("v-uni-button",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.formSubmit.apply(void 0,arguments)}}},[e._v("登录")])],1):e._e(),0==e.is_login?i("v-uni-view",{staticClass:"btns p-30-75",staticStyle:{"padding-top":"80rpx"}},[i("v-uni-button",{on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.resetpasswordSub.apply(void 0,arguments)}}},[e._v("重置密码")])],1):e._e(),1==e.is_login?i("v-uni-view",{staticClass:"bottom_nav"},[i("v-uni-view",{staticClass:"bottom-box"},[i("v-uni-view",{staticClass:"other_tit"},[i("v-uni-text",{staticClass:"bg-white p-0-20"},[e._v("其他方式登录")])],1),e.showWeixin?i("v-uni-view",{staticClass:"pt30 d-c-c"},[i("v-uni-view",{staticClass:"weixin_box",on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.login.apply(void 0,arguments)}}},[i("v-uni-text",{staticClass:"icon iconfont icon-weixin"})],1)],1):e._e(),e.showApple?i("v-uni-view",[i("v-uni-image",{staticClass:"ios-login",attrs:{src:"/static/ios.png",mode:"aspectFill"},on:{click:function(t){arguments[0]=t=e.$handleEvent(t),e.appleLogin.apply(void 0,arguments)}}})],1):e._e()],1)],1):e._e()],1)},a=[]},b035:function(e,t,i){"use strict";i.r(t);var o=i("6c13"),n=i("0436");for(var a in n)"default"!==a&&function(e){i.d(t,e,(function(){return n[e]}))}(a);i("4b2b");var s,r=i("f0c5"),d=Object(r["a"])(n["default"],o["b"],o["c"],!1,null,"922bf3e2",null,!1,o["a"],s);t["default"]=d.exports},d87d:function(e,t,i){var o=i("24fb");t=o(!1),t.push([e.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */\r\n/* start--主题色--start */\r\n/* end--主题色--end */.p-30-75[data-v-922bf3e2]{padding:%?30?% %?75?%}.p-0-75[data-v-922bf3e2]{padding:0 %?75?%}.t-r[data-v-922bf3e2]{text-align:right}.login-container[data-v-922bf3e2]{background:#fff}.login-container uni-input[data-v-922bf3e2]{height:%?88?%;line-height:%?88?%}.wechatapp[data-v-922bf3e2]{padding:%?80?% 0 %?48?%;border-bottom:%?1?% solid #e3e3e3;margin-bottom:%?72?%;text-align:center}.wechatapp .header[data-v-922bf3e2]{width:%?190?%;height:%?190?%;border:2px solid #fff;margin:%?0?% auto 0;border-radius:50%;overflow:hidden;box-shadow:1px 0 5px rgba(50,50,50,.3)}.auth-title[data-v-922bf3e2]{color:#585858;font-size:%?34?%;margin-bottom:%?40?%}.auth-subtitle[data-v-922bf3e2]{color:#888;margin-bottom:%?88?%;font-size:%?28?%}.login-btn[data-v-922bf3e2]{padding:0 %?20?%}.login-btn uni-button[data-v-922bf3e2]{height:%?88?%;line-height:%?88?%;background:#04be01;color:#fff;font-size:%?30?%;border-radius:%?999?%;text-align:center}.no-login-btn[data-v-922bf3e2]{margin-top:%?20?%;padding:0 %?20?%}.no-login-btn uni-button[data-v-922bf3e2]{height:%?88?%;line-height:%?88?%;background:#dfdfdf;color:#fff;font-size:%?30?%;border-radius:%?999?%;text-align:center}.get-code-btn[data-v-922bf3e2]{width:%?200?%;height:%?80?%;line-height:%?76?%;padding:%?0?% %?30?%;border-radius:%?40?%;white-space:nowrap;background-color:#fff;font-size:%?30?%}[data-theme="theme0"] .get-code-btn[data-v-922bf3e2]{color:#ff5704!important}[data-theme="theme1"] .get-code-btn[data-v-922bf3e2]{color:#19ad57!important}[data-theme="theme2"] .get-code-btn[data-v-922bf3e2]{color:#fc0!important}[data-theme="theme3"] .get-code-btn[data-v-922bf3e2]{color:#33a7ff!important}[data-theme="theme4"] .get-code-btn[data-v-922bf3e2]{color:#e4e4e4!important}[data-theme="theme5"] .get-code-btn[data-v-922bf3e2]{color:#c8ba97!important}[data-theme="theme6"] .get-code-btn[data-v-922bf3e2]{color:#623ceb!important}.get-code-btn[disabled="true"][data-v-922bf3e2]{background-color:#fff}[data-theme="theme0"] .get-code-btn[disabled="true"][data-v-922bf3e2]{color:#999!important}[data-theme="theme1"] .get-code-btn[disabled="true"][data-v-922bf3e2]{color:#999!important}[data-theme="theme2"] .get-code-btn[disabled="true"][data-v-922bf3e2]{color:#999!important}[data-theme="theme3"] .get-code-btn[disabled="true"][data-v-922bf3e2]{color:#999!important}[data-theme="theme4"] .get-code-btn[disabled="true"][data-v-922bf3e2]{color:#999!important}[data-theme="theme5"] .get-code-btn[disabled="true"][data-v-922bf3e2]{color:#999!important}[data-theme="theme6"] .get-code-btn[disabled="true"][data-v-922bf3e2]{color:#999!important}.btns uni-button[data-v-922bf3e2]{height:%?90?%;line-height:%?90?%;font-size:%?34?%;border-radius:%?45?%;color:#fff}[data-theme="theme0"] .btns uni-button[data-v-922bf3e2]{background-color:#ff5704!important}[data-theme="theme1"] .btns uni-button[data-v-922bf3e2]{background-color:#19ad57!important}[data-theme="theme2"] .btns uni-button[data-v-922bf3e2]{background-color:#fc0!important}[data-theme="theme3"] .btns uni-button[data-v-922bf3e2]{background-color:#33a7ff!important}[data-theme="theme4"] .btns uni-button[data-v-922bf3e2]{background-color:#e4e4e4!important}[data-theme="theme5"] .btns uni-button[data-v-922bf3e2]{background-color:#c8ba97!important}[data-theme="theme6"] .btns uni-button[data-v-922bf3e2]{background-color:#623ceb!important}.login_topbpx[data-v-922bf3e2]{padding:%?90?% 0;padding-bottom:%?110?%}.login_tit[data-v-922bf3e2]{font-size:%?52?%;font-weight:600;margin-bottom:%?33?%}.login_top[data-v-922bf3e2]{font-size:%?24?%;color:#adafb3}.input_botom[data-v-922bf3e2]{border-bottom:1px solid #f4f4f4}.bottom_nav[data-v-922bf3e2]{width:100%;position:absolute;bottom:%?30?%;padding-bottom:env(safe-area-inset-bottom)}.bottom-box[data-v-922bf3e2]{width:70%;margin:0 auto}.other_tit[data-v-922bf3e2]{height:%?1?%;background-color:#cacaca;width:100%;line-height:%?1?%;text-align:center}.weixin_box[data-v-922bf3e2]{background-color:#04be01;border-radius:50%;width:%?80?%;height:%?80?%;line-height:%?80?%;text-align:center}.weixin_box .icon-weixin[data-v-922bf3e2]{font-size:%?40?%;color:#fff}.agreement[data-v-922bf3e2]{border-radius:50%;width:%?28?%;height:%?28?%;border:%?2?% solid;background:#fff;position:relative;margin-right:%?10?%;box-sizing:border-box}[data-theme="theme0"] .agreement[data-v-922bf3e2]{border-color:#ff5704!important}[data-theme="theme1"] .agreement[data-v-922bf3e2]{border-color:#19ad57!important}[data-theme="theme2"] .agreement[data-v-922bf3e2]{border-color:#fc0!important}[data-theme="theme3"] .agreement[data-v-922bf3e2]{border-color:#33a7ff!important}[data-theme="theme4"] .agreement[data-v-922bf3e2]{border-color:#e4e4e4!important}[data-theme="theme5"] .agreement[data-v-922bf3e2]{border-color:#c8ba97!important}[data-theme="theme6"] .agreement[data-v-922bf3e2]{border-color:#623ceb!important}.agreement.active[data-v-922bf3e2]::after{content:"";width:%?16?%;height:%?16?%;border-radius:50%;position:absolute;left:0;top:0;right:0;bottom:0;margin:auto}[data-theme="theme0"] .agreement.active[data-v-922bf3e2]::after{background-color:#ff5704!important}[data-theme="theme1"] .agreement.active[data-v-922bf3e2]::after{background-color:#19ad57!important}[data-theme="theme2"] .agreement.active[data-v-922bf3e2]::after{background-color:#fc0!important}[data-theme="theme3"] .agreement.active[data-v-922bf3e2]::after{background-color:#33a7ff!important}[data-theme="theme4"] .agreement.active[data-v-922bf3e2]::after{background-color:#e4e4e4!important}[data-theme="theme5"] .agreement.active[data-v-922bf3e2]::after{background-color:#c8ba97!important}[data-theme="theme6"] .agreement.active[data-v-922bf3e2]::after{background-color:#623ceb!important}.ios-login[data-v-922bf3e2]{width:%?420?%;height:%?100?%;margin:%?20?% auto}.skip[data-v-922bf3e2]{position:absolute;top:%?80?%;right:%?30?%;font-size:%?28?%;color:#999}',""]),e.exports=t}}]);