(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-product-list-list"],{"2a7f":function(t,i,e){"use strict";e.r(i);var a=e("57aa"),o=e.n(a);for(var n in a)"default"!==n&&function(t){e.d(i,t,(function(){return a[t]}))}(n);i["default"]=o.a},4027:function(t,i,e){"use strict";e("a9e3"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={name:"load-more",props:{loadingType:{type:Number,default:0},showImage:{type:Boolean,default:!0},color:{type:String,default:"#999999"},contentText:{type:Object,default:function(){return{contentdown:"上拉显示更多",contentrefresh:"正在加载...",contentnomore:"已经到底了"}}}},data:function(){return{}}};i.default=a},"431c":function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */\r\n/* start--主题色--start */\r\n/* end--主题色--end */.inner-tab[data-v-55d30086]{position:relative;height:%?100?%;display:flex;justify-content:space-around;align-items:center;background:#fff;z-index:9}.inner-tab .item[data-v-55d30086]{flex:1;height:100%;line-height:%?90?%;position:relative;color:#999;font-size:%?32?%}.inner-tab .item.active[data-v-55d30086],\r\n.inner-tab .item .arrow.active .iconfont[data-v-55d30086]{color:#333;font-weight:700}.item.active[data-v-55d30086]::after{content:"";width:%?72?%;height:%?4?%;background:#ee1414;border-radius:%?2?%;position:absolute;bottom:%?14?%;left:0;right:0;margin:auto}.inner-tab .item .box[data-v-55d30086]{display:flex;justify-content:center;align-items:center;flex-direction:row}.inner-tab .item .arrows[data-v-55d30086]{margin-left:%?10?%;line-height:0}.inner-tab .item .iconfont[data-v-55d30086]{line-height:%?24?%;font-size:%?24?%}.inner-tab .item .arrow[data-v-55d30086],\r\n.inner-tab .item .svg-icon[data-v-55d30086]{width:%?20?%;height:%?20?%}.prodcut-list-wrap[data-v-55d30086]{padding-top:%?20?%}.prodcut-list-wrap .list[data-v-55d30086]{background:#fff}.prodcut-list-wrap .list .item[data-v-55d30086]{padding:%?20?%;display:flex;border-bottom:%?1?% solid #f6f6f6}.prodcut-list-wrap .product-cover[data-v-55d30086],\r\n.prodcut-list-wrap .product-cover uni-image[data-v-55d30086]{width:%?150?%;height:%?150?%}.prodcut-list-wrap .product-info[data-v-55d30086]{flex:1;margin-left:%?30?%;display:flex;flex-direction:column;justify-content:space-around}.prodcut-list-wrap .product-title[data-v-55d30086]{display:-webkit-box;line-height:%?36?%;overflow:hidden;-webkit-line-clamp:2;-webkit-box-orient:vertical;font-size:%?26?%}.prodcut-list-wrap .already-sale[data-v-55d30086]{color:#999;font-size:%?22?%}.prodcut-list-wrap .already-sale > uni-text[data-v-55d30086]{padding:%?6?% %?10?%}.prodcut-list-wrap .price[data-v-55d30086]{color:#f6220c;font-size:%?24?%}.prodcut-list-wrap .price .num[data-v-55d30086]{margin-left:%?6?%;padding:0 %?4?%;font-size:%?32?%;font-weight:700}.inner-tab .item .box[data-v-55d30086]{display:flex;justify-content:center;align-items:center;flex-direction:row}.shop_body[data-v-55d30086]{width:100%;background-color:#fff;padding:%?0?% %?20?%;box-sizing:border-box}.shop_body_l_item[data-v-55d30086]{margin:0 auto;background-color:#fff;display:flex;padding:%?40?% 0;box-sizing:border-box;border-bottom:%?1?% solid #d9d9d9}.shop_body_l_item uni-image[data-v-55d30086]{width:%?150?%;height:%?150?%;border-radius:%?12?%;background-color:rgba(0,0,0,.1)}.shop_body_l_item_info[data-v-55d30086]{flex:1;display:flex;justify-content:space-between;flex-direction:column;padding-left:%?20?%;box-sizing:border-box}.shop_body_l_item_info_title[data-v-55d30086]{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:2;text-overflow:ellipsis;-webkit-box-orient:vertical;word-wrap:break-word;word-break:break-all;overflow:hidden}.shop_body_l_item_info_price[data-v-55d30086]{display:flex;align-items:flex-end}.shop_body_l_item_info_price uni-view[data-v-55d30086]{margin-right:%?15?%}.shop_body_l_item_info_others[data-v-55d30086]{height:%?30?%;display:flex;justify-content:space-between}.shop_body_l_item_info_others_activity[data-v-55d30086]{width:%?150?%;height:%?30?%;line-height:%?30?%;border:%?1?% #e22319 solid;border-radius:%?30?%;\r\n  /* font-size: 16rpx; */color:#e22319;text-align:center;box-sizing:border-box}.shop_body_l_item_info_others_sales[data-v-55d30086]{color:#333}.shop_body2[data-v-55d30086]{width:100%;display:flex;justify-content:flex-start;flex-wrap:wrap;background-color:#f2f2f2}.shop_body_t_item[data-v-55d30086]{width:%?345?%;margin-bottom:%?20?%;height:%?520?%;overflow:hidden;background-color:#fff;border-radius:%?12?%}.collect uni-text[data-v-55d30086]{color:#fff}.shop_body_t_item uni-image[data-v-55d30086]{width:100%;height:%?337.5?%;background-color:rgba(0,0,0,.1)}.shop_body_t_item_info[data-v-55d30086]{display:flex;flex-direction:column;justify-content:flex-start;padding:%?20?%;box-sizing:border-box}.shop_body_t_item_info_title[data-v-55d30086]{display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:1;text-overflow:ellipsis;-webkit-box-orient:vertical;word-wrap:break-word;word-break:break-all;overflow:hidden;margin-bottom:%?30?%}.shop_body_t_item_info_price[data-v-55d30086]{display:flex;align-items:flex-end}.shop_body_t_item_info_others[data-v-55d30086]{display:flex;justify-content:space-between;margin-bottom:%?8?%}.shop_body_t_item_info_others_sales[data-v-55d30086]{color:#999}.huaxianjia[data-v-55d30086]{text-decoration:line-through;color:#999;margin-left:%?5?%}.shop_red[data-v-55d30086]{color:#f6220c}.inner-tab .item .icon-sanjiao2[data-v-55d30086]{font-size:%?13?%}.inner-tab .item .icon-sanjiao1[data-v-55d30086]{font-size:%?13?%}.noborder[data-v-55d30086]{border:none}',""]),t.exports=i},5129:function(t,i,e){var a=e("431c");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=e("4f06").default;o("03dd7dc8",a,!0,{sourceMap:!1,shadowMode:!1})},"56f2":function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,".load-more[data-v-61e8bd6a]{display:flex;flex-direction:row;height:%?80?%;align-items:center;justify-content:center}.loading-img[data-v-61e8bd6a]{height:24px;width:24px;margin-right:10px}.loading-text[data-v-61e8bd6a]{font-size:%?24?%;color:#999}.loading-img>uni-view[data-v-61e8bd6a]{position:absolute}.load1[data-v-61e8bd6a],\n.load2[data-v-61e8bd6a],\n.load3[data-v-61e8bd6a]{height:24px;width:24px}.load2[data-v-61e8bd6a]{-webkit-transform:rotate(30deg);transform:rotate(30deg)}.load3[data-v-61e8bd6a]{-webkit-transform:rotate(60deg);transform:rotate(60deg)}.loading-img>uni-view uni-view[data-v-61e8bd6a]{width:6px;height:2px;border-top-left-radius:1px;border-bottom-left-radius:1px;background:#777;position:absolute;opacity:.2;-webkit-transform-origin:50%;transform-origin:50%;-webkit-animation:load-data-v-61e8bd6a 1.56s ease infinite}.loading-img>uni-view uni-view[data-v-61e8bd6a]:nth-child(1){-webkit-transform:rotate(90deg);transform:rotate(90deg);top:2px;left:9px}.loading-img>uni-view uni-view[data-v-61e8bd6a]:nth-child(2){-webkit-transform:rotate(180deg);top:11px;right:0}.loading-img>uni-view uni-view[data-v-61e8bd6a]:nth-child(3){-webkit-transform:rotate(270deg);transform:rotate(270deg);bottom:2px;left:9px}.loading-img>uni-view uni-view[data-v-61e8bd6a]:nth-child(4){top:11px;left:0}.load1 uni-view[data-v-61e8bd6a]:nth-child(1){-webkit-animation-delay:0s;animation-delay:0s}.load2 uni-view[data-v-61e8bd6a]:nth-child(1){-webkit-animation-delay:.13s;animation-delay:.13s}.load3 uni-view[data-v-61e8bd6a]:nth-child(1){-webkit-animation-delay:.26s;animation-delay:.26s}.load1 uni-view[data-v-61e8bd6a]:nth-child(2){-webkit-animation-delay:.39s;animation-delay:.39s}.load2 uni-view[data-v-61e8bd6a]:nth-child(2){-webkit-animation-delay:.52s;animation-delay:.52s}.load3 uni-view[data-v-61e8bd6a]:nth-child(2){-webkit-animation-delay:.65s;animation-delay:.65s}.load1 uni-view[data-v-61e8bd6a]:nth-child(3){-webkit-animation-delay:.78s;animation-delay:.78s}.load2 uni-view[data-v-61e8bd6a]:nth-child(3){-webkit-animation-delay:.91s;animation-delay:.91s}.load3 uni-view[data-v-61e8bd6a]:nth-child(3){-webkit-animation-delay:1.04s;animation-delay:1.04s}.load1 uni-view[data-v-61e8bd6a]:nth-child(4){-webkit-animation-delay:1.17s;animation-delay:1.17s}.load2 uni-view[data-v-61e8bd6a]:nth-child(4){-webkit-animation-delay:1.3s;animation-delay:1.3s}.load3 uni-view[data-v-61e8bd6a]:nth-child(4){-webkit-animation-delay:1.43s;animation-delay:1.43s}@-webkit-keyframes load-data-v-61e8bd6a{0%{opacity:1}100%{opacity:.2}}",""]),t.exports=i},"57aa":function(t,i,e){"use strict";var a=e("4ea4");e("99af"),e("ac1f"),e("841c"),Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var o=a(e("77c4")),n={components:{uniLoadMore:o.default},data:function(){return{isLieBiao:!0,phoneHeight:0,scrollviewHigh:0,topRefresh:!1,loading:!0,no_more:!1,type_active:0,price_top:!1,listData:[],page:1,category_id:0,search:"",sortType:"",sortPrice:0,list_rows:10,last_page:0}},computed:{loadingType:function(){return this.loading?1:0!=this.listData.length&&this.no_more?2:0}},onLoad:function(t){this.category_id=t.category_id,t.search&&(this.search=t.search),t.sortType&&(this.sortType=t.sortType),t.sortPrice&&(this.sortPrice=t.sortPrice)},mounted:function(){this.init(),this.getData()},onPullDownRefresh:function(){this.restoreData(),this.getData()},methods:{init:function(){var t=this;uni.getSystemInfo({success:function(i){t.phoneHeight=i.windowHeight;var e=uni.createSelectorQuery().select(".top-box");e.boundingClientRect((function(i){var e=t.phoneHeight-i.height;t.scrollviewHigh=e})).exec()}})},restoreData:function(){this.listData=[],this.category_id=0,this.search="",this.sortType="",this.sortPrice=0},tabTypeFunc:function(t){var i=this;i.listData=[],i.page=1,i.no_more=!1,i.loading=!0,2==t?(i.price_top=!this.price_top,1==i.price_top?i.sortPrice=0:i.sortPrice=1,i.sortType="price"):1==t&&(i.price_top=!this.price_top,i.sortType="sales"),i.type_active=t,console.log(i.type_active),i.getData()},getData:function(){var t=this,i=t.page,e=t.list_rows,a=t.category_id,o=t.search,n=t.sortType,s=t.sortPrice;t.loading=!0,t._get("product.product/lists",{page:i||1,category_id:a,search:o,sortType:n,sortPrice:s,list_rows:e},(function(i){t.loading=!1,t.listData=t.listData.concat(i.data.list.data),t.last_page=i.data.list.last_page,i.data.list.last_page<=1&&(t.no_more=!0)}))},gotoList:function(t){var i="pages/product/detail/detail?product_id="+t;this.gotoPage(i)},gotoSearch:function(){self.gotoPage("/pages/product/search/search")},scrolltolowerFunc:function(){var t=this;if(t.bottomRefresh=!0,t.page++,t.loading=!0,t.page>t.last_page)return t.loading=!1,void(t.no_more=!0);t.getData()},select_type:function(){var t=this;t.isLieBiao=!t.isLieBiao},onShareAppMessage:function(){return{title:"全部分类",path:"/pages/product/category?"+this.getShareUrlParams()}}}};i.default=n},"641f":function(t,i,e){"use strict";var a=e("5129"),o=e.n(a);o.a},"77c4":function(t,i,e){"use strict";e.r(i);var a=e("9893"),o=e("a9d9");for(var n in o)"default"!==n&&function(t){e.d(i,t,(function(){return o[t]}))}(n);e("d54b");var s,r=e("f0c5"),d=Object(r["a"])(o["default"],a["b"],a["c"],!1,null,"61e8bd6a",null,!1,a["a"],s);i["default"]=d.exports},9893:function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return o})),e.d(i,"c",(function(){return n})),e.d(i,"a",(function(){return a}));var o=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",{staticClass:"load-more"},[e("v-uni-view",{directives:[{name:"show",rawName:"v-show",value:1===t.loadingType&&t.showImage,expression:"loadingType === 1 && showImage"}],staticClass:"loading-img"},[e("v-uni-view",{staticClass:"load1"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1),e("v-uni-view",{staticClass:"load2"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1),e("v-uni-view",{staticClass:"load3"},[e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}}),e("v-uni-view",{style:{background:t.color}})],1)],1),e("v-uni-text",{staticClass:"loading-text",style:{color:t.color}},[t._v(t._s(0===t.loadingType?t.contentText.contentdown:1===t.loadingType?t.contentText.contentrefresh:t.contentText.contentnomore))])],1)},n=[]},"9add":function(t,i,e){var a=e("56f2");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=e("4f06").default;o("b6c3d57c",a,!0,{sourceMap:!1,shadowMode:!1})},a046:function(t,i,e){"use strict";var a;e.d(i,"b",(function(){return o})),e.d(i,"c",(function(){return n})),e.d(i,"a",(function(){return a}));var o=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",[e("v-uni-view",{staticClass:"top-box"},[e("v-uni-view",{staticClass:"index-search-box index-search-box_re d-b-c",attrs:{id:"searchBox"}},[e("v-uni-view",{staticClass:"index-search index-search_re t-c flex-1"},[e("span",{staticClass:"icon iconfont icon-sousuo"}),e("v-uni-input",{staticClass:"flex-1 ml10 f26 gray3",attrs:{type:"text",value:"","placeholder-class":"f26 gray9",placeholder:"搜索商品","confirm-type":"search"},on:{confirm:function(i){arguments[0]=i=t.$handleEvent(i),t.search()}},model:{value:t.search,callback:function(i){t.search=i},expression:"search"}})],1)],1),e("v-uni-view",{staticClass:"inner-tab"},[e("v-uni-view",{class:0==t.type_active?"item active":"item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.tabTypeFunc(0)}}},[e("v-uni-view",{staticClass:"box"},[t._v("综合")])],1),e("v-uni-view",{class:1==t.type_active?"item active":"item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.tabTypeFunc(1)}}},[e("v-uni-view",{staticClass:"box"},[t._v("销量")])],1),e("v-uni-view",{class:2==t.type_active?"item active":"item",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.tabTypeFunc(2)}}},[e("v-uni-view",{staticClass:"box"},[e("v-uni-text",[t._v("价格")]),e("v-uni-view",{staticClass:"arrows"},[e("v-uni-view",{class:t.price_top&&2==t.type_active?"arrow active":"arrow"},[e("span",{staticClass:"icon iconfont icon-sanjiao2"})]),e("v-uni-view",{class:t.price_top||2!=t.type_active?"arrow":"arrow active"},[e("span",{staticClass:"icon iconfont icon-sanjiao1"})])],1)],1)],1),e("v-uni-view",{staticClass:"item"},[e("v-uni-view",{staticClass:"box",staticStyle:{height:"100%"}},[e("v-uni-image",{staticStyle:{width:"36rpx",height:"36rpx"},attrs:{src:1==t.isLieBiao?"/static/shop/liebiao.png":"/static/shop/tubiao.png"},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.select_type()}}})],1)],1)],1)],1),e("v-uni-view",{staticClass:"prodcut-list-wrap"},[e("v-uni-scroll-view",{staticClass:"scroll-Y",style:"height:"+t.scrollviewHigh+"px;",attrs:{"scroll-y":"true","lower-threshold":"50"},on:{scrolltolower:function(i){arguments[0]=i=t.$handleEvent(i),t.scrolltolowerFunc.apply(void 0,arguments)}}},[e("v-uni-view",{class:t.topRefresh?"top-refresh open":"top-refresh"},t._l(3,(function(t,i){return e("v-uni-view",{key:i,staticClass:"circle"})})),1),1==t.isLieBiao?e("v-uni-view",{staticClass:"shop_body"},t._l(t.listData,(function(i,a){return e("v-uni-view",{key:a,staticClass:"shop_body_l_item",class:a==t.listData.length-1?"noborder":"",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.gotoList(i.product_id)}}},[e("v-uni-view",[e("v-uni-image",{attrs:{src:i.product_image,mode:""}})],1),e("v-uni-view",{staticClass:"shop_body_l_item_info"},[e("v-uni-view",{staticClass:"shop_body_l_item_info_title gray3 f32"},[t._v(t._s(i.product_name))]),e("v-uni-view",{staticClass:"d-b-c pb10"},[e("v-uni-view",{staticClass:"shop_body_l_item_info_price"},[e("v-uni-view",{staticClass:"f24 shop_red"},[t._v("¥"),e("v-uni-text",{staticClass:"f32 fb"},[t._v(t._s(i.product_price))])],1)],1),e("v-uni-view",{staticClass:"shop_body_l_item_info_others f22"},[e("v-uni-view",{staticClass:"shop_body_l_item_info_others_sales"},[t._v("累计成交："+t._s(i.product_sales)+"笔")])],1)],1)],1)],1)})),1):t._e(),0==t.isLieBiao?e("v-uni-view",{staticClass:"shop_body2"},t._l(t.listData,(function(i,a){return e("v-uni-view",{key:a,staticClass:"shop_body_t_item",class:a%2==0?"ml20 mr20":" mr20",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.gotoList(i.product_id)}}},[e("v-uni-image",{attrs:{src:i.product_image,mode:""}}),e("v-uni-view",{staticClass:"shop_body_t_item_info"},[e("v-uni-view",{staticClass:"shop_body_t_item_info_title f26"},[t._v(t._s(i.product_name))]),e("v-uni-view",{staticClass:"shop_body_t_item_info_others f24 gray9 mt"},[e("v-uni-view",{staticClass:"shop_body_t_item_info_others_sales"},[t._v("累计成交："+t._s(i.product_sales)+"笔")])],1),e("v-uni-view",{staticClass:"shop_body_t_item_info_price"},[e("v-uni-view",{staticClass:"f20 redF6"},[t._v("¥"),e("v-uni-text",{staticClass:"f32"},[t._v(t._s(i.product_price))])],1),e("v-uni-view",{staticClass:"f20 huaxianjia"},[t._v("¥"),e("v-uni-text",{staticClass:"24"},[t._v(t._s(i.line_price))])],1)],1)],1)],1)})),1):t._e(),0!=t.listData.length||t.loading?e("uni-load-more",{attrs:{loadingType:t.loadingType}}):e("v-uni-view",{staticClass:"d-c-c p30"},[e("v-uni-text",{staticClass:"iconfont icon-wushuju"}),e("v-uni-text",{staticClass:"cont"},[t._v("亲，暂无相关记录哦")])],1)],1)],1)],1)},n=[]},a9d9:function(t,i,e){"use strict";e.r(i);var a=e("4027"),o=e.n(a);for(var n in a)"default"!==n&&function(t){e.d(i,t,(function(){return a[t]}))}(n);i["default"]=o.a},bcb6:function(t,i,e){"use strict";e.r(i);var a=e("a046"),o=e("2a7f");for(var n in o)"default"!==n&&function(t){e.d(i,t,(function(){return o[t]}))}(n);e("641f");var s,r=e("f0c5"),d=Object(r["a"])(o["default"],a["b"],a["c"],!1,null,"55d30086",null,!1,a["a"],s);i["default"]=d.exports},d54b:function(t,i,e){"use strict";var a=e("9add"),o=e.n(a);o.a}}]);