{"version":3,"sources":["script.js"],"names":["BX","namespace","Rest","Configuration","Install","prototype","init","params","this","id","signedParameters","next","section","progressDescriptionContainer","findChildByClassName","clearAll","errors","startBtn","bind","delegate","btnConfirm","UI","Button","color","Color","PRIMARY","state","State","DISABLED","text","message","onclick","btn","checked","context","close","start","create","children","attrs","type","name","value","events","change","event","setState","ACTIVE","for","Dialogs","MessageBox","show","modal","bindElement","buttons","setDescription","code","mess","html","finish","barContainer","infoContainer","removeClass","length","addClass","cleanNode","appendChild","className","data-slider-ignore-autobinding","href","click","openPopupErrors","insertAfter","self","sendAjax","response","data","result","errorsBlock","i","addErrors","push","errorText","forEach","item","errorTextArea","props","placeholder","restConfigWindowContent","restConfigWindow","PopupWindowManager","titleBar","content","contentBackground","contentPadding","minWidth","maxWidth","autoHide","closeIcon","animation","LINK","select","document","execCommand","onPopupClose","destroy","style","loadManifest","save","step","showFatalError","clear","import","action","callback","ajax","runComponentAction","mode","then","console","log","catch","window"],"mappings":"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","file":"script.map.js"}