
; /* Start:"a:4:{s:4:"full";s:85:"/bitrix/components/bitrix/landing.pub/templates/.default/script.min.js?15853865151139";s:6:"source";s:66:"/bitrix/components/bitrix/landing.pub/templates/.default/script.js";s:3:"min";s:70:"/bitrix/components/bitrix/landing.pub/templates/.default/script.min.js";s:3:"map";s:70:"/bitrix/components/bitrix/landing.pub/templates/.default/script.map.js";}"*/
this.BX=this.BX||{};this.BX.Landing=this.BX.Landing||{};(function(e,t,n){"use strict";var i=Symbol("onEditButtonClick");var a=function(){function e(){babelHelpers.classCallCheck(this,e);this.cache=new t.Cache.MemoryCache;this[i]=this[i].bind(this);t.Event.bind(this.getEditButton(),"click",this[i])}babelHelpers.createClass(e,[{key:"getLayout",value:function e(){return this.cache.remember("layout",function(){return document.querySelector(".landing-pub-top-panel")})}},{key:"getEditButton",value:function e(){var t=this;return this.cache.remember("editButton",function(){return t.getLayout().querySelector(".landing-pub-top-panel-edit-button")})}},{key:i,value:function e(n){n.preventDefault();var i=t.Dom.attr(n.currentTarget,"href");if(t.Type.isString(i)&&i!==""){this.openSlider(i)}}},{key:"openSlider",value:function e(t){BX.SidePanel.Instance.open(t,{cacheable:false,customLeftBoundary:240,allowChangeHistory:false,events:{onClose:function e(){void n.SliderHacks.reloadSlider(window.location.toString())}}})}}]);return e}();e.TopPanel=a})(this.BX.Landing.Pub=this.BX.Landing.Pub||{},BX,BX.Landing);
/* End */
;; /* /bitrix/components/bitrix/landing.pub/templates/.default/script.min.js?15853865151139*/

//# sourceMappingURL=page_c1cd436c5f99764b595d6dcc1ac617e6.map.js