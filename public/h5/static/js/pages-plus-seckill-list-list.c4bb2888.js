(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-plus-seckill-list-list"],{"036c":function(t,i,e){"use strict";e.r(i);var a=e("c7ba"),n=e.n(a);for(var s in a)"default"!==s&&function(t){e.d(i,t,(function(){return a[t]}))}(s);i["default"]=n.a},"19d8":function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return s})),e.d(i,"a",(function(){return a}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"countdown"},[null==t.config.type?[0==t.status?e("v-uni-text",[t._v(t._s(t.title))]):t._e(),1==t.status?e("v-uni-text",[t._v("活动具体时间：")]):t._e(),2==t.status?e("v-uni-text",[t._v("活动结束时间：")]):t._e(),e("v-uni-text",{staticClass:"box"},[t._v(t._s(t.day))]),e("v-uni-text",{staticClass:"p-0-10"},[t._v("天")]),e("v-uni-text",{staticClass:"box"},[t._v(t._s(t.hour))]),e("v-uni-text",{staticClass:"p-0-10"},[t._v(":")]),e("v-uni-text",{staticClass:"box"},[t._v(t._s(t.minute))]),e("v-uni-text",{staticClass:"p-0-10"},[t._v(":")]),e("v-uni-text",{staticClass:"box"},[t._v(t._s(t.second))]),e("v-uni-text",{staticClass:"p-0-10"})]:t._e(),"text"===t.config.type?[t._v(t._s(t.title)+t._s(parseInt(24*t.day)+parseInt(t.hour))+":"+t._s(t.minute)+":"+t._s(t.second))]:t._e()],2)},s=[]},"29ab":function(t,i,e){"use strict";e.r(i);var a=e("9574"),n=e("036c");for(var s in n)"default"!==s&&function(t){e.d(i,t,(function(){return n[t]}))}(s);e("4f22");var r,o=e("f0c5"),c=Object(o["a"])(n["default"],a["b"],a["c"],!1,null,"399fde49",null,!1,a["a"],r);i["default"]=c.exports},"4f22":function(t,i,e){"use strict";var a=e("a7a2"),n=e.n(a);n.a},"5a3c":function(t,i,e){"use strict";e("e25e"),e("ac1f"),e("5319"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={data:function(){return{status:0,day:"0",hour:"0",minute:"0",second:"0",timer:null,totalSeconds:0,title:"活动剩余："}},props:{config:{type:Object,default:function(){return{type:"all"}}}},created:function(){},watch:{config:{deep:!0,handler:function(t,i){t!=i&&0!=t.endstamp&&(t.title&&"undefined"!=typeof t.title&&(this.title=t.title),this.setTime())},immediate:!0}},methods:{setTime:function(){var t=this;t.timer=setInterval((function(){t.init()}),1e3)},init:function(){var t=Date.now()/1e3;t<this.config.startstamp?this.status=1:t>this.config.endstamp?this.status=2:(this.totalSeconds=parseInt(this.config.endstamp-t),this.status=0,this.countDown()),this.$emit("returnVal",this.status)},countDown:function(){var t=this.totalSeconds,i=Math.floor(t/86400),e=t%86400,a=Math.floor(e/3600);e%=3600;var n=Math.floor(e/60),s=e%60;this.day=this.convertTwo(i),this.hour=this.convertTwo(a),this.minute=this.convertTwo(n),this.second=this.convertTwo(s),this.totalSeconds--},convertTwo:function(t){var i="";return i=t<10?"0"+t:t,i},getLocalTime:function(t){return new Date(1e3*parseInt(t)).toLocaleString().replace(/:\d{1,2}$/," ")},clear:function(){console.log(1),clearInterval(this.timer),this.timer=null}},destroyed:function(){clearInterval(this.timer)}};i.default=a},8894:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */\r\n/* start--主题色--start */\r\n/* end--主题色--end */uni-page-body[data-v-399fde49]{background-color:#f2f2f2}.top-box[data-v-399fde49]{position:relative}.seckill-product-wrap .tab-item[data-v-399fde49]{flex:0;padding:0 %?30?%;font-size:%?33?%;height:%?86?%;line-height:%?86?%;white-space:nowrap;color:#333}.seckill-product-wrap .tab-item.active[data-v-399fde49]{color:#f6220c;font-size:%?33?%;opacity:1;position:relative}.seckill-product-wrap .tab-item.active[data-v-399fde49]::after{content:"";width:%?120?%;height:%?4?%;background:#f6220c;border-radius:%?2?%;position:absolute;border:%?30?%}.seckill-product-wrap .every-day-time uni-text[data-v-399fde49]{padding:%?8?% %?16?%;font-size:%?26?%;color:#333;opacity:.5}.seckill-product-wrap .ad-activity[data-v-399fde49]{position:relative;overflow:hidden}.seckill-product-wrap .ad-activity uni-image[data-v-399fde49]{width:%?750?%;height:%?367?%}.seckill-list-wrap .list .item[data-v-399fde49]{padding:%?30?%;display:flex;border-radius:%?16?%;margin-bottom:%?20?%;background:#fff}.seckill-list-wrap .product-cover[data-v-399fde49],\r\n.seckill-list-wrap .product-cover uni-image[data-v-399fde49]{width:%?200?%;height:%?200?%}.active-top-tab .ad-datetime[data-v-399fde49] .box{height:%?40?%;padding:%?4?%;line-height:%?40?%;text-align:center;border-radius:%?8?%;font-size:%?28?%;background:#f6220c;color:#fff}.active-top-tab .ad-datetime[data-v-399fde49] uni-text{color:#333;font-size:%?28?%}.seckill-list-wrap .product-title[data-v-399fde49]{display:-webkit-box;overflow:hidden;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:%?32?%}.seckill-list-wrap .already-sale[data-v-399fde49]{padding:%?4?% 0;color:#8228eb;font-size:%?22?%}.seckill-list-wrap .slider-box .slider[data-v-399fde49]{margin-top:%?11?%;height:%?8?%;background:#f2f2f2;border-radius:%?5?%}.seckill-list-wrap .slider-box .slider-inner[data-v-399fde49]{height:%?8?%;background:linear-gradient(-90deg,#cb2bff,#7727e7);border-radius:%?4?%}.seckill-list-wrap .right-btn uni-button[data-v-399fde49]{background:linear-gradient(90deg,#ff6b6b 4%,#f6220c);color:#fff;height:%?60?%;border-radius:%?30?%;line-height:%?60?%}.reg180[data-v-399fde49]{padding-right:%?20?%;text-align:right;-webkit-transform:rotateY(180deg);transform:rotateY(180deg);position:absolute;bottom:0}.icon-jiantou[data-v-399fde49]{color:#fff;font-size:%?30?%}.head_top[data-v-399fde49]{position:absolute;top:0;z-index:20;padding-top:0;height:30px;line-height:30px;color:#fff;font-size:%?28?%}.tab-item.active[data-v-399fde49]::after{content:"";width:60%;height:%?4?%;background:#f2f2f2;border-radius:%?2?%;position:absolute;bottom:%?5?%}body.?%PAGE?%[data-v-399fde49]{background-color:#f2f2f2}',""]),t.exports=i},9574:function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return n})),e.d(i,"c",(function(){return s})),e.d(i,"a",(function(){return a}));var n=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"seckill-product-wrap"},[e("v-uni-view",{staticClass:"top-box"},[e("v-uni-view",{staticClass:"active-top-tab"},[e("v-uni-scroll-view",{staticClass:"scroll-X  mb30",attrs:{"scroll-X":"true","show-scrollbar":"false"}},[e("v-uni-view",{staticClass:"tab-list d-s-c"},t._l(t.activeList,(function(i,a){return e("v-uni-view",{key:a,staticClass:"tab-item",class:{active:t.type_active==a},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.tabTypeFunc(a)}}},[t._v(t._s(i.title))])})),1)],1),t.listData.detail?e("v-uni-view",{staticClass:"ad-activity"},[e("v-uni-image",{attrs:{src:t.listData.detail.file_path,mode:""}})],1):t._e(),t.listData.detail?e("v-uni-view",{staticClass:"ad-datetime ww100 pt40 pb10 d-c-c"},[e("Countdown",{ref:"countdown",attrs:{config:t.countdownConfig},on:{returnVal:function(i){arguments[0]=i=t.$handleEvent(i),t.returnValFunc.apply(void 0,arguments)}}})],1):t._e(),e("v-uni-view",{staticClass:"every-day-time d-c-c mb20"},[e("v-uni-text",[t._v("每日活动时间："+t._s(t.currActive.day_start_time)+"至"+t._s(t.currActive.day_end_time))])],1)],1)],1),t.loading?t._e():e("v-uni-view",{staticClass:"seckill-list-wrap"},[e("v-uni-scroll-view",{staticClass:"scroll-Y",style:"height:"+t.scrollviewHigh+"px;",attrs:{"scroll-y":"true","lower-threshold":"50"}},[e("v-uni-view",{staticClass:"list p-0-20"},t._l(t.listData.list,(function(i,a){return e("v-uni-view",{key:a,staticClass:"item d-stretch"},[e("v-uni-view",{staticClass:"product-cover"},[e("v-uni-image",{attrs:{src:i.product.file_path,mode:"aspectFit"}})],1),e("v-uni-view",{staticClass:"d-b-c d-c flex-1 ml26"},[e("v-uni-view",{staticClass:"product-title ww100"},[t._v(t._s(i.product.product_name))]),e("v-uni-view",{staticClass:"price ww100 red"},[e("v-uni-text",{staticClass:"f24"},[t._v("秒杀价：￥")]),e("v-uni-text",{staticClass:"num f36 fb"},[t._v(t._s(i.seckill_price))]),e("v-uni-text",{staticClass:"ml20 text-d-line gray9 f24"},[t._v("￥"+t._s(i.product_price))])],1),e("v-uni-view",{staticClass:"slider-box ww100 d-b-c"},[e("v-uni-view",{staticClass:"left flex-1"},[e("v-uni-text",{staticClass:"already-sale"},[t._v("已抢购"+t._s(i.product_sales)+"件")]),e("v-uni-view",{staticClass:"slider"},[e("v-uni-view",{staticClass:"slider-inner",style:"width:"+i.product_sales/(i.product_sales+i.stock)*100+"%;"})],1)],1),e("v-uni-view",{staticClass:"right-btn ml30"},[e("v-uni-button",{attrs:{type:"primary"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.gotoDetail(i.seckill_product_id)}}},[t._v("马上抢")])],1)],1)],1)],1)})),1),0!=t.listData.length||t.loading?t._e():e("v-uni-view",{staticClass:"d-c-c p30"},[e("v-uni-text",{staticClass:"iconfont icon-wushuju"}),e("v-uni-text",{staticClass:"cont"},[t._v("亲，暂无相关记录哦")])],1)],1)],1)],1)},s=[]},a7a2:function(t,i,e){var a=e("8894");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("52757f10",a,!0,{sourceMap:!1,shadowMode:!1})},bb9f:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */\r\n/* start--主题色--start */\r\n/* end--主题色--end */.countdown .box[data-v-18ed4ace]{display:inline-block;padding:%?4?%;\r\n  /* width: 34rpx; */border-radius:%?8?%;background:#fff;text-align:center;color:#fff}',""]),t.exports=i},c7ba:function(t,i,e){"use strict";var a=e("4ea4");e("ac1f"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=a(e("ea0f")),s={components:{Countdown:n.default},data:function(){return{phoneHeight:0,scrollviewHigh:0,activeList:[],type_active:0,currActive:{},listData:[],detailData:{},loading:!0,countdownConfig:{startstamp:0,endstamp:0}}},computed:{},onShow:function(){this.getActive()},mounted:function(){this.init()},onPullDownRefresh:function(){},methods:{init:function(){var t=this;uni.getSystemInfo({success:function(i){t.phoneHeight=i.windowHeight;var e=uni.createSelectorQuery().select(".top-box");e.boundingClientRect((function(i){var e=t.phoneHeight-i.height+43;t.scrollviewHigh=e})).exec()}})},tabTypeFunc:function(t){this.type_active=t,this.currActive=this.activeList[t],this.getData()},goback:function(){uni.navigateBack({})},getActive:function(){var t=this,i={};t.loading=!0,t._get("plus.seckill.product/active",{param:i},(function(i){t.activeList=i.data.list,t.activeList&&t.activeList.length>0&&(t.currActive=t.activeList[0],t.getData())}))},getData:function(){var t=this;t.loading=!0,t._get("plus.seckill.product/product",{seckill_activity_id:t.currActive.seckill_activity_id},(function(i){t.listData=i.data,t.countdownConfig.endstamp=i.data.detail.end_time,t.countdownConfig.startstamp=i.data.detail.start_time,uni.hideLoading(),t.loading=!1}))},gotoDetail:function(t){this.$refs.countdown.clear(),this.gotoPage("/pages/plus/seckill/detail/detail?seckill_product_id="+t)},gotoSearch:function(){this.getData()},returnValFunc:function(t){}}};i.default=s},d7a7:function(t,i,e){"use strict";var a=e("f352"),n=e.n(a);n.a},ea0f:function(t,i,e){"use strict";e.r(i);var a=e("19d8"),n=e("f508");for(var s in n)"default"!==s&&function(t){e.d(i,t,(function(){return n[t]}))}(s);e("d7a7");var r,o=e("f0c5"),c=Object(o["a"])(n["default"],a["b"],a["c"],!1,null,"18ed4ace",null,!1,a["a"],r);i["default"]=c.exports},f352:function(t,i,e){var a=e("bb9f");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=e("4f06").default;n("633e3638",a,!0,{sourceMap:!1,shadowMode:!1})},f508:function(t,i,e){"use strict";e.r(i);var a=e("5a3c"),n=e.n(a);for(var s in a)"default"!==s&&function(t){e.d(i,t,(function(){return a[t]}))}(s);i["default"]=n.a}}]);