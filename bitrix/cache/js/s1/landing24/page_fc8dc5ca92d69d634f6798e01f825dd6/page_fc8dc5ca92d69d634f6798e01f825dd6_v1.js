
; /* Start:"a:4:{s:4:"full";s:94:"/bitrix/components/bitrix/landing.landing_view/templates/.default/script.min.js?15853865159457";s:6:"source";s:75:"/bitrix/components/bitrix/landing.landing_view/templates/.default/script.js";s:3:"min";s:79:"/bitrix/components/bitrix/landing.landing_view/templates/.default/script.min.js";s:3:"map";s:79:"/bitrix/components/bitrix/landing.landing_view/templates/.default/script.map.js";}"*/
(function(){"use strict";BX.namespace("BX.Landing.Component.View");BX.Landing.Component.View=function(n){};BX.Landing.Component.View.instance=null;BX.Landing.Component.View.getInstance=function(){return BX.Landing.Component.View.instance};BX.Landing.Component.View.create=function(n,e){n.topInit=e===true;BX.Landing.Component.View.instance=new BX.Landing.Component.View(n);BX.Landing.Component.View.instance.setNewOptions(n);BX.Landing.Component.View.instance.init();return BX.Landing.Component.View.instance};BX.Landing.Component.View.prototype={setNewOptions:function(n){this.type=n.type||"";this.title=n.title||"";this.topInit=n.topInit||false;this.active=n.active||false;this.draftMode=n.draftMode||false;this.id=n.id||0;this.siteId=n.siteId||0;this.pagesCount=n.pagesCount||0;this.siteTitle=n.siteTitle||"";this.storeEnabled=n.storeEnabled||false;this.fullPublication=n.fullPublication||false;this.urls=n.urls||{};this.rights=n.rights||{};this.sliderConditions=n.sliderConditions||[];if(!this.popupMenuIds){this.popupMenuIds=[]}if(!this.placements){this.placements=n.placements||[]}for(var e=0,i=this.popupMenuIds.length;e<i;e++){var t=BX.PopupMenu.getMenuById(this.popupMenuIds[e]);if(t){t.destroy()}}this.popupMenuIds=[]},init:function(){var n=BX.Landing.Component.View.getInstance();if(typeof BX.rest!=="undefined"&&typeof BX.rest.Marketplace!=="undefined"){BX.rest.Marketplace.bindPageAnchors({})}BX.addCustomEvent(window,"Rest:AppLayout:ApplicationInstall",function(n){if(n){}});if(this.topInit){BX.addCustomEvent("SidePanel.Slider:onMessage",function(n){if(n.getEventId()==="landingEditClose"){setTimeout(function(){window.location.reload()},1e3)}})}if(!this.topInit){BX.addCustomEvent("BX.Landing.Block:init",function(e){if(e.data.requiredUserActionIsShown){BX.bind(e.data.button,"click",function(){n.onRequiredLinkClick(this)})}});var e=[].slice.call(document.querySelectorAll(".landing-required-link"));e.forEach(function(e,i){BX.bind(e,"click",function(){n.onRequiredLinkClick(this)})})}if(this.topInit){var i=BX.Landing.PageObject.getEditorWindow();var t=BX.Landing.PageObject.getRootWindow();i.addEventListener("load",function(){BX.Landing.UI.Panel.StylePanel.getInstance();t.BX.Landing.UI.Panel.Top.instance=null;BX.Landing.UI.Panel.Top.getInstance()});i.addEventListener("click",function(){this.closeAllPopupsMenu()}.bind(this));i.addEventListener("resize",BX.debounce(function(){this.closeAllPopupsMenu()}.bind(this),200))}if(this.topInit){this.buildTop();this.initSliders();this.loadEditor();this.hideEditorsPanelHandlers()}},initSliders:function(){if(typeof BX.SidePanel==="undefined"){return}var n=[];for(var e=0,i=this.sliderConditions.length;e<i;e++){n.push(this.sliderConditions[e])}if(n.length<=0){return}var t=top.BX.clone({rules:[{condition:n}]});BX.SidePanel.Instance.bindAnchors(t)},loadEditor:function(){var n=document.querySelector(".landing-editor-loader-container");var e=document.querySelector(".landing-editor-required-user-action");if(n){var i=new BX.Loader({offset:{top:"-70px"}});i.show(n);BX.Landing.PageObject.getInstance().view().then(function(i){BX.bindOnce(i,"load",function(){var t=BX.Landing.Main.getInstance().options.requiredUserAction;if(BX.Landing.Utils.isPlainObject(t)&&!BX.Landing.Utils.isEmpty(t)){if(t.header){e.querySelector("h3").innerText=t.header}if(t.description){e.querySelector("p").innerText=t.description}if(t.href){e.querySelector("a").setAttribute("href",t.href)}if(t.text){e.querySelector("a").innerText=t.text}e.classList.add("landing-ui-user-action-show");document.querySelector(".landing-ui-panel-top-history").classList.add("landing-ui-disabled");document.querySelector(".landing-ui-panel-top-devices").classList.add("landing-ui-disabled");document.querySelector(".landing-ui-panel-top-chain-link.landing-ui-panel-top-menu-link-settings").classList.add("landing-ui-disabled");[].slice.call(document.querySelectorAll(".landing-ui-panel-top-menu-link:not(.landing-ui-panel-top-menu-link-help)")).forEach(function(n){n.classList.add("landing-ui-disabled")})}else{i.classList.add("landing-ui-view-show")}setTimeout(function(){BX.remove(n);BX.remove(e)},200)})})}},hideEditorsPanelHandlers:function(){BX.Landing.PageObject.getInstance().top().then(function(n){n.addEventListener("click",function(){BX.Landing.PageObject.getInstance().view().then(function(n){if(n.contentWindow.BX){if(n.contentWindow.BX.Landing.Block.Node.Text.currentNode){n.contentWindow.BX.Landing.Block.Node.Text.currentNode.disableEdit()}if(n.contentWindow.BX.Landing.UI.Field.BaseField.currentField){n.contentWindow.BX.Landing.UI.Field.BaseField.currentField.disableEdit()}n.contentWindow.BX.Landing.UI.Panel.EditorPanel.getInstance().hide()}})})})},onRequiredLinkClick:function(n){var e=n.getAttribute("href");if(e.substr(0,1)!=="#"){window.open(e,"_top")}var i=e.substr(1);var t={};var a="";if(i.indexOf("@")>0){a=i.split("@")[1];i=i.split("@")[0]}i=i.toUpperCase();if(i==="PAGE_URL_CATALOG_EDIT"){i="PAGE_URL_SITE_EDIT";t.tpl="catalog"}if(typeof landingParams[i]!=="undefined"&&typeof BX.SidePanel!=="undefined"){BX.SidePanel.Instance.open(BX.util.add_url_param(landingParams[i],t)+(a?"#"+a:""),{allowChangeHistory:false})}},buildTop:function(n){n=n||{};for(var e in this.urls){var i=BX("landing-urls-"+e);if(i){i.setAttribute("href",this.urls[e])}}var t=[].slice.call(document.querySelectorAll(".landing-ui-panel-top-menu-link-settings"));t.forEach(function(n,e){n.addEventListener("click",function(){this.onSettingsClick(e,n)}.bind(this))}.bind(this));if(BX("landing-publication")){BX("landing-publication").setAttribute("href",this.fullPublication?this.urls["publicationAll"]:this.urls["publication"]);if(!this.rights.public){BX.addClass(BX("landing-publication").parentNode,"ui-btn-disabled")}else{BX.removeClass(BX("landing-publication").parentNode,"ui-btn-disabled")}if(BX("landing-publication-submenu")){BX("landing-publication-submenu").addEventListener("click",function(){var n=BX("landing-publication-submenu");if(!BX.hasClass(n.parentNode,"ui-btn-disabled")){this.onSubPublicationClick(n)}}.bind(this))}BX("landing-publication").addEventListener("click",function(){if(BX.hasClass(BX("landing-publication").parentNode,"ui-btn-disabled")){BX.PreventDefault()}else if(BX("landing-publication").getAttribute("target")==="_self"){BX.addClass(document.querySelector(".ui-btn-primary.landing-btn-menu"),"ui-btn-wait")}}.bind(this))}if(BX("landing-urls-preview")){BX("landing-urls-preview").addEventListener("click",function(){if(BX("landing-urls-preview").getAttribute("target")==="_self"){BX.SidePanel.Instance.open(BX("landing-urls-preview").getAttribute("href")+"&IFRAME=Y",{allowChangeHistory:false});BX.PreventDefault()}}.bind(this))}},onSubPublicationClick:function(n){if(BX.PopupMenu.getMenuById("landing-menu-publication")){var e=BX.PopupMenu.getMenuById("landing-menu-publication")}else{this.popupMenuIds.push("landing-menu-publication");var e=new BX.Landing.UI.Tool.Menu({id:"landing-menu-publication",bindElement:n,autoHide:true,zIndex:1200,offsetLeft:20,angle:true,closeByEsc:true,items:[{href:this.urls["publication"],text:BX.message("LANDING_TPL_PUBLIC_URL_PAGE"),target:"_blank",dataset:{sliderIgnoreAutobinding:true}},{href:this.urls["publicationAll"],text:BX.message("LANDING_TPL_PUBLIC_URL_ALL"),target:"_blank",dataset:{sliderIgnoreAutobinding:true}}]})}e.show()},onSettingsClick:function(n,e){if(BX.PopupMenu.getMenuById("landing-menu-settings"+n)){var i=BX.PopupMenu.getMenuById("landing-menu-settings"+n)}else{this.popupMenuIds.push("landing-menu-settings"+n);var t=[{href:this.urls["landingEdit"],text:BX.message("LANDING_TPL_SETTINGS_PAGE_URL"),disabled:!this.rights.settings},{href:this.urls["landingSiteEdit"],text:BX.message("LANDING_TPL_SETTINGS_SITE_URL"),disabled:!this.rights.settings},this.storeEnabled?{href:this.urls["landingCatalogEdit"],text:BX.message("LANDING_TPL_SETTINGS_CATALOG_URL"),disabled:!this.rights.settings}:null,!this.draftMode?{href:this.urls["unpublic"],text:BX.message("LANDING_TPL_SETTINGS_UNPUBLIC"),disabled:!this.rights.public||!this.active}:null];var a=this;for(var s=0,o=this.placements.length;s<o;s++){var l=this.placements[s];t.push({text:BX.util.htmlspecialchars(l.TITLE),onclick:function(){BX.rest.AppLayout.openApplication(this.APP_ID,{SITE_ID:a.siteId,LID:a.id},{PLACEMENT:this.PLACEMENT,PLACEMENT_ID:this.ID})}.bind(l,a)})}var i=new BX.Landing.UI.Tool.Menu({id:"landing-menu-settings"+n,bindElement:BX("landing-panel-settings"),autoHide:true,zIndex:1200,offsetLeft:20,angle:true,closeByEsc:true,items:t})}i.show()},closeAllPopupsMenu:function(){this.popupMenuIds.forEach(function(n){var e=BX.PopupMenu.getMenuById(n);if(e){e.close()}})}};BX.Landing.Component.View.changeTop=function(n,e){e=e||{};if(typeof e.changeState==="undefined"){e.changeState=true}BX.ajax({url:BX.util.add_url_param(window.location.href,{action:"changeTop"}),method:"POST",data:{param:n,sessid:BX.message("bitrix_sessid"),actionType:"json"},dataType:"json",onsuccess:function(n){BX.Landing.Component.View.instance.closeAllPopupsMenu();BX.Landing.Component.View.instance.setNewOptions(n);BX.Landing.Component.View.instance.buildTop({changeState:e.changeState})}})}})();var landingAlertMessage=function n(e,i){if(i===true&&typeof BX.Landing.PaymentAlertShow!=="undefined"){BX.Landing.PaymentAlertShow({message:e})}else{var t=BX.Landing.UI.Tool.ActionDialog.getInstance();t.show({content:e,confirm:"OK",contentColor:"grey",type:"alert"})}};
/* End */
;; /* /bitrix/components/bitrix/landing.landing_view/templates/.default/script.min.js?15853865159457*/

//# sourceMappingURL=page_fc8dc5ca92d69d634f6798e01f825dd6.map.js