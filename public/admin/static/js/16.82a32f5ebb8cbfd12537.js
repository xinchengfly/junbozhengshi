webpackJsonp([16],{RMgi:function(e,t){},sayk:function(e,t,l){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=l("07y0"),o={data:function(){return{loading:!1,id:0,form:{province_id:"",city_id:"",level:1},areaList:[]}},created:function(){this.id=this.$route.query.id,this.getData()},methods:{getData:function(){var e=this;a.a.regionDetail({id:e.id},!0).then(function(t){e.form=t.data.model,e.areaList=t.data.regionData}).catch(function(e){})},onSubmit:function(){var e=this,t=this.form;e.$refs.form.validate(function(l){l&&(e.loading=!0,a.a.editRegion(t,!0).then(function(t){e.loading=!1,e.$message({message:"恭喜你，修改成功",type:"success"}),e.$router.push("/region/Index")}).catch(function(t){e.loading=!1}))})},initCity:function(){this.form.city_id=""}}},r={render:function(){var e=this,t=e.$createElement,l=e._self._c||t;return l("div",{staticClass:"product-add"},[l("el-form",{ref:"form",attrs:{size:"small",model:e.form,"label-width":"200px"}},[l("div",{staticClass:"common-form"},[e._v("新增物流公司")]),e._v(" "),l("el-form-item",{attrs:{label:"地区类型"}},[l("div",[l("el-radio",{attrs:{label:1},model:{value:e.form.level,callback:function(t){e.$set(e.form,"level",t)},expression:"form.level"}},[e._v("省份")]),e._v(" "),l("el-radio",{attrs:{label:2},model:{value:e.form.level,callback:function(t){e.$set(e.form,"level",t)},expression:"form.level"}},[e._v("城市")]),e._v(" "),l("el-radio",{attrs:{label:3},model:{value:e.form.level,callback:function(t){e.$set(e.form,"level",t)},expression:"form.level"}},[e._v("地区")])],1)]),e._v(" "),e.form.level>1?l("el-form-item",{attrs:{label:"选择上级"}},[e.form.level>1?l("el-select",{attrs:{placeholder:"省"},on:{change:e.initCity},model:{value:e.form.province_id,callback:function(t){e.$set(e.form,"province_id",t)},expression:"form.province_id"}},e._l(e.areaList,function(e,t){return l("el-option",{key:t,attrs:{label:e.name,value:e.id}})}),1):e._e(),e._v(" "),""!=e.form.province_id&&e.form.level>2?l("el-select",{attrs:{placeholder:"市"},model:{value:e.form.city_id,callback:function(t){e.$set(e.form,"city_id",t)},expression:"form.city_id"}},e._l(e.areaList[e.form.province_id].city,function(e,t){return l("el-option",{key:t,attrs:{label:e.name,value:e.id}})}),1):e._e()],1):e._e(),e._v(" "),l("el-form-item",{attrs:{label:"地区名称 ",prop:"name",rules:[{required:!0,message:" "}]}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"简称",prop:"shortname"}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.shortname,callback:function(t){e.$set(e.form,"shortname",t)},expression:"form.shortname"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"全称",prop:"merger_name"}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.merger_name,callback:function(t){e.$set(e.form,"merger_name",t)},expression:"form.merger_name"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"拼音",prop:"pinyin"}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.pinyin,callback:function(t){e.$set(e.form,"pinyin",t)},expression:"form.pinyin"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"邮编",prop:"zip_code"}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.zip_code,callback:function(t){e.$set(e.form,"zip_code",t)},expression:"form.zip_code"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"首字母",prop:"first"}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.first,callback:function(t){e.$set(e.form,"first",t)},expression:"form.first"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"经度",prop:"lng"}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.lng,callback:function(t){e.$set(e.form,"lng",t)},expression:"form.lng"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"纬度",prop:"lat"}},[l("el-input",{staticClass:"max-w460",model:{value:e.form.lat,callback:function(t){e.$set(e.form,"lat",t)},expression:"form.lat"}})],1),e._v(" "),l("el-form-item",{attrs:{label:"排序"}},[l("el-input",{staticClass:"max-w460",attrs:{type:"number"},model:{value:e.form.sort,callback:function(t){e.$set(e.form,"sort",t)},expression:"form.sort"}}),e._v(" "),l("div",{staticClass:"tips"},[e._v("数字越小越靠前")])],1),e._v(" "),l("el-form-item",[l("el-button",{attrs:{type:"primary",loading:e.loading},on:{click:e.onSubmit}},[e._v("提交")])],1)],1)],1)},staticRenderFns:[]};var i=l("VU/8")(o,r,!1,function(e){l("RMgi")},null,null);t.default=i.exports}});
//# sourceMappingURL=16.82a32f5ebb8cbfd12537.js.map