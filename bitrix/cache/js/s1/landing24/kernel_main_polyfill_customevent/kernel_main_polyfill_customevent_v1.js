; /* /bitrix/js/main/polyfill/customevent/main.polyfill.customevent.min.js?1583532006556*/

; /* Start:"a:4:{s:4:"full";s:83:"/bitrix/js/main/polyfill/customevent/main.polyfill.customevent.min.js?1583532006556";s:6:"source";s:65:"/bitrix/js/main/polyfill/customevent/main.polyfill.customevent.js";s:3:"min";s:69:"/bitrix/js/main/polyfill/customevent/main.polyfill.customevent.min.js";s:3:"map";s:69:"/bitrix/js/main/polyfill/customevent/main.polyfill.customevent.map.js";}"*/
(function(){"use strict";try{new window.CustomEvent("bx-test-custom-event",{bubbles:true,cancelable:true})}catch(t){var e=function(e,t){t=t||{};t.bubbles=!!t.bubbles;t.cancelable=!!t.cancelable;var n=document.createEvent("CustomEvent");n.initCustomEvent(e,t.bubbles,t.cancelable,t.detail);var u=n.preventDefault;n.preventDefault=function(){Object.defineProperty(this,"defaultPrevented",{get:function(){return true}});u.call(this)};return n};e.prototype=window.Event.prototype;window.CustomEvent=e}})();
/* End */
;
//# sourceMappingURL=kernel_main_polyfill_customevent.map.js