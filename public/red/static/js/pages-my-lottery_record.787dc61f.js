(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-my-lottery_record"],{"0d00":function(t,e,n){"use strict";var a;n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return r})),n.d(e,"a",(function(){return a}));var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{staticClass:"page"},[n("v-uni-view",{staticClass:"content"},t._l(t.list,(function(e,a){return n("v-uni-view",{key:a,staticClass:"item flex-center-between"},[n("v-uni-view",{staticClass:"flex-center-start"},[n("v-uni-image",{staticClass:"img",attrs:{src:"../../static/turntable/"+e.grp_id+".png"}}),n("v-uni-view",{staticClass:"text1"},[t._v(t._s(e.grp_name))])],1),n("v-uni-view",{staticClass:"text2"},[t._v(t._s(e.updated_at))])],1)})),1)],1)},r=[]},2359:function(t,e,n){"use strict";n.r(e);var a=n("0d00"),i=n("671f");for(var r in i)"default"!==r&&function(t){n.d(e,t,(function(){return i[t]}))}(r);n("dd73");var o,d=n("f0c5"),s=Object(d["a"])(i["default"],a["b"],a["c"],!1,null,"3f178568",null,!1,a["a"],o);e["default"]=s.exports},"60cf":function(t,e,n){var a=n("7d5a");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var i=n("4f06").default;i("435d0a92",a,!0,{sourceMap:!1,shadowMode:!1})},"671f":function(t,e,n){"use strict";n.r(e);var a=n("bbcb"),i=n.n(a);for(var r in a)"default"!==r&&function(t){n.d(e,t,(function(){return a[t]}))}(r);e["default"]=i.a},"7d5a":function(t,e,n){var a=n("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */.flex-center[data-v-3f178568]{display:flex;align-items:center}.flex-center-between[data-v-3f178568]{display:flex;align-items:center;justify-content:space-between}.flex-center-around[data-v-3f178568]{display:flex;align-items:center;justify-content:space-around}.flex-center-center[data-v-3f178568]{display:flex;align-items:center;justify-content:center}.flex-center-start[data-v-3f178568]{display:flex;align-items:center;justify-content:flex-start}.flex_1[data-v-3f178568]{flex:1}.input_n[data-v-3f178568]{background:none;outline:none;border:none;font-size:%?28?%}.page-column[data-v-3f178568]{width:100%;height:100%;position:absolute;top:0;left:0;display:flex;flex-direction:column}.notice_up[data-v-3f178568]{position:fixed;top:0;right:0;bottom:0;left:0;z-index:999;opacity:0;text-align:center;-webkit-transform:scale(1.5);transform:scale(1.5);background:rgba(0,0,0,.6);transition:all .2s ease-in-out 0s;pointer-events:none;-webkit-perspective:1000px;perspective:1000px;-webkit-backface-visibility:hidden;backface-visibility:hidden;flex-direction:column}.notice_up.show[data-v-3f178568]{opacity:1;-webkit-transform:scale(1);transform:scale(1);pointer-events:auto}.popup .box[data-v-3f178568]{width:%?540?%;height:%?500?%;background:#fff;border-radius:%?10?%;margin:0 auto;margin-top:%?300?%;text-align:center;padding:%?30?%;overflow:hidden}.popup .box .title[data-v-3f178568]{font-size:%?32?%}.popup .box .money[data-v-3f178568]{margin-top:%?40?%;font-size:%?60?%}.popup .box .money[data-v-3f178568]::before{content:"";font-size:%?50?%}.popup .box .money_1[data-v-3f178568]{margin-top:%?40?%;font-size:%?60?%}.popup .box .nav[data-v-3f178568]{margin-top:%?60?%;height:%?70?%;border-radius:%?10?%;border:%?2?% solid #535353}.popup .box .nav .list[data-v-3f178568]{height:%?70?%;border-right:%?2?% solid #989898;width:16.66666%}.popup .box .nav .list[data-v-3f178568]:last-child{border-right:none}.popup .box .nav .list .box-radius[data-v-3f178568]{width:%?20?%;height:%?20?%;border-radius:50%;background:#000}.popup .box .submit[data-v-3f178568]{margin:0 auto;margin-top:%?60?%;width:%?360?%;height:%?70?%;line-height:%?70?%;border-radius:%?10?%;background:linear-gradient(90deg,#df85de,#884ff2);color:#fff;text-align:center;font-size:%?32?%}.popup .number[data-v-3f178568]{flex-wrap:wrap;position:absolute;bottom:0;width:100%;background:#fff}.popup .number .list[data-v-3f178568]{height:%?90?%;line-height:%?90?%;width:33.333%;font-size:%?32?%;box-sizing:border-box;border-right:%?2?% solid hsla(0,0%,80%,.36);border-bottom:%?2?% solid hsla(0,0%,80%,.36)}.arrow[data-v-3f178568]::after{content:"";display:block;-webkit-transform:rotate(45deg);transform:rotate(45deg);width:%?15?%;height:%?15?%;border-top:%?3?% solid #999;border-right:%?3?% solid #999}\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */.page[data-v-3f178568]{min-height:100vh;min-height:calc(100vh - 44px);background:#f51308;padding:0 %?30?%;overflow:hidden}.page .content[data-v-3f178568]{height:%?1115?%;background:#fff;border-radius:%?10?%;margin-top:%?31?%;overflow:auto}.page .content .item[data-v-3f178568]{height:%?133?%;border-bottom:%?2?% solid #ebebeb;padding:0 %?21?%}.page .content .item .img[data-v-3f178568]{width:%?122?%;height:%?78?%;margin-right:%?20?%}.page .content .item .text1[data-v-3f178568]{font-size:%?32?%;font-weight:400;color:#343434}.page .content .item .text2[data-v-3f178568]{font-size:%?26?%;font-weight:400;color:#999}',""]),t.exports=e},bbcb:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={data:function(){return{list:""}},onLoad:function(){var t=this;this.helper.post2("index/grpAct/myRedPacket",{},(function(e){t.list=e.data}))},methods:{}};e.default=a},dd73:function(t,e,n){"use strict";var a=n("60cf"),i=n.n(a);i.a}}]);