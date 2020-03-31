
; /* Start:"a:4:{s:4:"full";s:105:"/bitrix/templates/okshop/components/bitrix/catalog.smart.filter/visual_vertical/script.js?158557030413954";s:6:"source";s:89:"/bitrix/templates/okshop/components/bitrix/catalog.smart.filter/visual_vertical/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
function JCSmartFilter(ajaxURL)
{
	this.ajaxURL = ajaxURL;
	this.form = null;
	this.timer = null;
}

JCSmartFilter.prototype.keyup = function(input)
{
	if(this.timer)
		clearTimeout(this.timer);
	this.timer = setTimeout(BX.delegate(function(){
		this.reload(input);
	}, this), 1000);
}

JCSmartFilter.prototype.click = function(checkbox)
{
	if(this.timer)
		clearTimeout(this.timer);
	this.timer = setTimeout(BX.delegate(function(){
		this.reload(checkbox);
	}, this), 1000);
}

JCSmartFilter.prototype.reload = function(input)
{
	this.position = BX.pos(input, true);
	this.form = BX.findParent(input, {'tag':'form'});
	if(this.form)
	{
		var values = new Array;
		values[0] = {name: 'ajax', value: 'y'};
		this.gatherInputsValues(values, BX.findChildren(this.form, {'tag':'input'}, true));
		window.curFilterinput = input;
		BX.ajax.loadJSON(
			this.ajaxURL,
			this.values2post(values),
			BX.delegate(this.postHandler, this)
		);
	}
}

JCSmartFilter.prototype.postHandler = function (result)
{
	if(!!result && !!result.ITEMS)
	{
		for(var PID in result.ITEMS)
		{
			var arItem = result.ITEMS[PID];
			if(arItem.PROPERTY_TYPE == 'N' || arItem.PRICE)
			{
			}
			else if(arItem.VALUES)
			{
				for(var i in arItem.VALUES)
				{
					var ar = arItem.VALUES[i];
					var control = BX(ar.CONTROL_ID);
					if(control)
					{
						control.parentNode.className = ar.DISABLED ? 'disabled': '';
					}
				}
			}
		}
		var modef = BX('modef');
		var modef_num = BX('modef_num');
		if(modef && modef_num)
		{
			modef_num.innerHTML = result.ELEMENT_COUNT;
			var hrefFILTER = BX.findChildren(modef, {tag: 'A'}, true);

			if(result.FILTER_URL && hrefFILTER)
				hrefFILTER[0].href = BX.util.htmlspecialcharsback(result.FILTER_URL);

			if(result.FILTER_AJAX_URL && result.COMPONENT_CONTAINER_ID)
			{
				BX.bind(hrefFILTER[0], 'click', function(e)
				{
					var url = BX.util.htmlspecialcharsback(result.FILTER_AJAX_URL);
					BX.ajax.insertToNode(url, result.COMPONENT_CONTAINER_ID);
					return BX.PreventDefault(e);
				});
			}

			if (result.INSTANT_RELOAD && result.COMPONENT_CONTAINER_ID)
			{
				var url = BX.util.htmlspecialcharsback(result.FILTER_AJAX_URL);
				BX.ajax.insertToNode(url, result.COMPONENT_CONTAINER_ID);
			}
			else
			{
				if(modef.style.display == 'none')
					modef.style.display = 'inline-block';
				var curProp = BX.findChild(BX.findParent(window.curFilterinput, {'class':'bx_filter_container'}), {'class':'bx_filter_container_modef'}, true, false);
				curProp.appendChild(modef);
				//modef.style.top = this.position.top + 'px';
			}
		}
	}
}

JCSmartFilter.prototype.gatherInputsValues = function (values, elements)
{
	if(elements)
	{
		for(var i = 0; i < elements.length; i++)
		{
			var el = elements[i];
			if (el.disabled || !el.type)
				continue;

			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'hidden':
				case 'select-one':
					if(el.value.length)
						values[values.length] = {name : el.name, value : el.value};
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
						values[values.length] = {name : el.name, value : el.value};
					break;
				case 'select-multiple':
					for (var j = 0; j < el.options.length; j++)
					{
						if (el.options[j].selected)
							values[values.length] = {name : el.name, value : el.options[j].value};
					}
					break;
				default:
					break;
			}
		}
	}
}

JCSmartFilter.prototype.values2post = function (values)
{
	var post = new Array;
	var current = post;
	var i = 0;
	while(i < values.length)
	{
		var p = values[i].name.indexOf('[');
		if(p == -1)
		{
			current[values[i].name] = values[i].value;
			current = post;
			i++;
		}
		else
		{
			var name = values[i].name.substring(0, p);
			var rest = values[i].name.substring(p+1);
			if(!current[name])
				current[name] = new Array;

			var pp = rest.indexOf(']');
			if(pp == -1)
			{
				//Error - not balanced brackets
				current = post;
				i++;
			}
			else if(pp == 0)
			{
				//No index specified - so take the next integer
				current = current[name];
				values[i].name = '' + current.length;
			}
			else
			{
				//Now index name becomes and name and we go deeper into the array
				current = current[name];
				values[i].name = rest.substring(0, pp) + rest.substring(pp+1);
			}
		}
	}
	return post;
}

function cDoubleTrackBar(Track, Tracker,LeftDrag, RightDrag, Settings) {
	switch(typeof Track){
		case 'string': this.Track = document.getElementById(Track); break;
		case 'object': this.Track = Track; break;
	}
	switch(typeof Tracker){
		case 'string': this.Tracker = document.getElementById(Tracker); break;
		case 'object': this.Tracker = Tracker; break;
	}
	switch(typeof LeftDrag){
		case 'string': this.LeftDrag = document.getElementById(LeftDrag); break;
		case 'object': this.LeftDrag = LeftDrag; break;
	}
	switch(typeof RightDrag){
		case 'string': this.RightDrag = document.getElementById(RightDrag); break;
		case 'object': this.RightDrag = RightDrag; break;
	}
	if (!Track || !Tracker)
		return false;
	this.OnUpdate = Settings.OnUpdate;
	this.OnComplete = Settings.OnComplete;
	this.FingerOffset = Settings.FingerOffset || 0;
	this.Min = Settings.Min || 0;
	this.Max = Settings.Max || 100;
	this.MinSpace = Settings.MinSpace || 0;
	this.RoundTo = Settings.RoundTo || 1;
	if (this.RoundTo < 1)
	{
		this.Precision = parseInt(Settings.Precision, 10) || 0;
		if (isNaN(this.Precision))
		{
			this.Precision = 0;
		}
	}
	else
	{
		this.Precision = 0;
	}
	this.PrecisionFactor = Math.pow(10,this.Precision);

	this.Disabled = (typeof Settings.Disabled != 'undefined') ? Settings.Disabled : false;

	if (this.Min >= this.Max)
		this.Max = this.Min +1;
	this.MinPos = this.Min;
	this.MaxPos = this.Max;
	if (this.Max - this.Min < this.MinSpace)
		this.MinSpace =  this.Max - this.Min;
	if (this.Max - this.Min < this.RoundTo)
		this.RoundTo =  this.Max - this.Min;
	this.MinSpace = Math.ceil(this.MinSpace/this.RoundTo)*this.RoundTo;

	//this.Track.style.width = (this.Track.clientWidth || this.Track.offsetWidth) + 'px';
	this.OnTrackMouseDown = this.bindAsEventListener(this.TrackMouseDown);
	this.OnDocumentMouseMove = this.bindAsEventListener(this.DocumentMouseMove);
	this.OnDocumentMouseUp = this.bindAsEventListener(this.DocumentMouseUp);

	if ('ontouchstart' in document.documentElement)
	{
		this.bindEvent(this.Track, 'touchstart', this.OnTrackMouseDown);
	}
	else
		this.bindEvent(this.Track, 'mousedown', this.OnTrackMouseDown);

	this.TrackerLeft = 0;
//	this.UpdateTracker(this.Track.offsetWidth + this.FingerOffset);
/*	if (typeof this.OnUpdate == 'function') {
		this.OnUpdate.call(this);
	}*/

	this.MinInputId = Settings.MinInputId || 0;
	this.MaxInputId = Settings.MaxInputId || 1000;

	this.startPosition();
}
cDoubleTrackBar.prototype = {

	TrackMouseDown: function(event) {
		this.TrackerLeft = this.Tracker.offsetLeft;
		this.TrackerRight = this.TrackerLeft + this.Tracker.offsetWidth;

		this.TrackerOffsets = this.getOffsets(this.Track);

		var currentX = ('ontouchmove' in document.documentElement) ? event.targetTouches[0].pageX : event.clientX;
		var X = currentX + document.documentElement.scrollLeft;
		X -= this.TrackerOffsets[0];

		this.Left = Math.abs(this.TrackerLeft-X) <= Math.abs(this.TrackerRight-X);

		if (typeof this.Disabled == 'function') {
			if ( this.Disabled.call(this) )
				return true;
		} else if ( this.Disabled )
			return true;

		this.UpdateTracker(X);

		if ('ontouchmove' in document.documentElement)
		{
			this.bindEvent(document, 'touchmove', this.OnDocumentMouseMove);
			this.bindEvent(document, 'touchend', this.OnDocumentMouseUp);
		}
		else
		{
			this.bindEvent(document, 'mousemove', this.OnDocumentMouseMove);
			this.bindEvent(document, 'mouseup', this.OnDocumentMouseUp);
		}
		return this.stopEvent(event);
	},
	DocumentMouseMove: function(event) {
		var currentX = ('ontouchmove' in document.documentElement) ? event.targetTouches[0].pageX : event.clientX;
		this.UpdateTracker(currentX + document.documentElement.scrollLeft - this.TrackerOffsets[0]);
		return this.stopEvent(event);
	},
	DocumentMouseUp: function(event) {
		if ('ontouchmove' in document.documentElement)
		{
			this.unbindEvent(document, 'touchmove', this.OnDocumentMouseMove);
			this.unbindEvent(document, 'touchend', this.OnDocumentMouseUp);
		}
		else
		{
			this.unbindEvent(document, 'mousemove', this.OnDocumentMouseMove);
			this.unbindEvent(document, 'mouseup', this.OnDocumentMouseUp);
		}

		if (typeof this.OnComplete == 'function') {
			this.OnComplete.call(this);
		}
		return this.stopEvent(event);
	},
	UpdateTracker: function(X){
		var _LogicWidth = this.Track.offsetWidth - 1;
		var _minSpace = Math.floor(_LogicWidth*this.MinSpace/(this.Max-this.Min));
		var _oldMin = this.MinPos;
		var _oldMax = this.MaxPos;

		var koef = this.Track.clientWidth/100;

		if (this.Left) {
			X += this.FingerOffset;
			this.TrackerLeft = Math.max(0, Math.min(this.TrackerRight - _minSpace - 1, X));
			this.MinPos = Math.round((this.Min + this.TrackerLeft*(this.Max-this.Min)/_LogicWidth) / this.RoundTo) * this.RoundTo;
			if (this.MinSpace >= this.MaxPos - this.MinPos) {
				this.MinPos = this.MaxPos - this.MinSpace;
			}
			if (this.Precision > 0)
			{
				this.MinPos = Math.round(this.MinPos*this.PrecisionFactor)/this.PrecisionFactor;
			}

			this.TrackerLeft = Math.round(_LogicWidth*(this.MinPos - this.Min)/(this.Max - this.Min));

			this.LeftDrag.style.left = (this.TrackerLeft)/koef + '%';
			this.Tracker.style.width = (this.TrackerRight - this.TrackerLeft)/koef + '%';
			this.Tracker.style.left = (this.TrackerLeft)/koef + '%';
			this.MinInputId.value = this.MinPos;
			smartFilter.keyup(this.MinInputId);

		} else {
			X -= this.FingerOffset;
			this.TrackerRight = Math.max(this.TrackerLeft + _minSpace + 1 , Math.min(_LogicWidth + 1, X));
			this.MaxPos = Math.round((this.Min + (this.TrackerRight-1)*(this.Max-this.Min)/_LogicWidth) / this.RoundTo) * this.RoundTo;
			if (this.MinSpace >= this.MaxPos - this.MinPos) {
				this.MaxPos = this.MinPos + this.MinSpace;
			}
			if (this.Precision > 0)
			{
				this.MaxPos = Math.round(this.MaxPos*this.PrecisionFactor)/this.PrecisionFactor;
			}

			this.TrackerRight = Math.round(_LogicWidth*(this.MaxPos - this.Min)/(this.Max - this.Min))+1;

			this.Tracker.style.left = (this.TrackerLeft)/koef + '%';
			this.Tracker.style.width = (this.TrackerRight - this.TrackerLeft)/koef + '%';
			this.RightDrag.style.left = ((this.TrackerLeft) + (this.TrackerRight - this.TrackerLeft)-10)/koef + '%';
			this.MaxInputId.value = this.MaxPos;
			smartFilter.keyup(this.MaxInputId);
		}
	},


	getOffsets: function(element) {
		var valueT = 0, valueL = 0;
		do {
			valueT += element.offsetTop  || 0;
			valueL += element.offsetLeft || 0;
			element = element.offsetParent;
		} while (element);
		return [valueL, valueT];
	},
	bindEvent: function(element, event, callBack){
		if (element.addEventListener) {
			element.addEventListener(event, callBack, false);
		} else {
			element.attachEvent('on' + event, callBack);
		}
	},
	unbindEvent: function(element, event, callBack){
		if (element.removeEventListener) {
			element.removeEventListener(event, callBack, false);
		} else if (element.detachEvent) {
			element.detachEvent('on' + event, callBack);
		}
	},
	bindAsEventListener: function (callBack) {
		var _object = this;
		return function(event) {
			return callBack.call(_object, event || window.event);
		}
	},
	stopEvent: function (event){
		if (event.preventDefault) {
			event.preventDefault();
			event.stopPropagation();
		} else {
			event.returnValue = false;
			event.cancelBubble = true;
		}
		return false;
	},
	startPosition: function ()
	{
		var drag_width = this.Tracker.clientWidth;
		var curMinPrice = this.MinInputId.value || 0;
		var curMaxPrice = this.MaxInputId.value || 0;

		if (curMinPrice || curMaxPrice)
		{
			if (!curMinPrice || curMinPrice < this.Min|| curMinPrice > this.Max)
				curMinPrice = this.Min;
			if (!curMaxPrice || curMaxPrice > this.Max || curMaxPrice < this.Min)
				curMaxPrice = this.Max;

			var koef = Math.round(((this.Max - this.Min)*10000/drag_width)/10000);
			var percent_koef = drag_width/100;
			if (curMinPrice)
				var curLeft = Math.round((curMinPrice - this.Min)/koef);
			if (curMaxPrice)
				var curWidth = Math.round((curMaxPrice - this.Min)/koef);


			this.LeftDrag.style.left = curLeft/percent_koef + "%";
			this.Tracker.style.left = curLeft/percent_koef + "%";
			this.Tracker.style.width = (curWidth - curLeft)/percent_koef + "%";
			if (Math.round(curWidth/percent_koef) < 100)
				this.RightDrag.style.left = curWidth/percent_koef  + "%";
		}
	}
}

function hideFilterProps(element)
{
	var obj = element.parentNode;

	var filterBlock = BX.findChild(obj, {className:"bx_filter_block"}, true, false);

	if(BX.hasClass(obj, "active"))
	{
		var easing = new BX.easing({
			duration : 300,
			start : { opacity: 1,  height: filterBlock.offsetHeight },
			finish : { opacity: 0, height:0 },
			transition : BX.easing.transitions.quart,
			step : function(state){
				filterBlock.style.opacity = state.opacity;
				filterBlock.style.height = state.height + "px";
			},
			complete : function() {
				filterBlock.setAttribute("style", "");
				BX.removeClass(obj, "active")
			}
		});
		easing.animate();
	}
	else
	{
		filterBlock.style.display = "block";
		filterBlock.style.opacity = 0;
		filterBlock.style.height = "auto";

		var obj_children_height = filterBlock.offsetHeight;
		filterBlock.style.height = 0;

		var easing = new BX.easing({
			duration : 300,
			start : { opacity: 0,  height: 0 },
			finish : { opacity: 1, height: obj_children_height },
			transition : BX.easing.transitions.quart,
			step : function(state){
				filterBlock.style.opacity = state.opacity;
				filterBlock.style.height = state.height + "px";
			},
			complete : function() {
			}
		});
		easing.animate();
		BX.addClass(obj, "active")
	}
}
/* End */
;
; /* Start:"a:4:{s:4:"full";s:129:"/bitrix/templates/okshop/components/bitrix/catalog/catalog-template/bitrix/catalog.section.list/.default/script.js?15855703043130";s:6:"source";s:114:"/bitrix/templates/okshop/components/bitrix/catalog/catalog-template/bitrix/catalog.section.list/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
function emarket_mSlider_refresh(parent_id, element_wcount) {
	var _window = $('#'+parent_id).children(".mSlider-wrap").children(".mSlider-window"),
		element_current = _window.children("li.current").attr("data-slide"),
		element_first   = _window.children("li:first").attr("data-slide"),
		element_last    = _window.children("li:last").attr("data-slide");

	if(element_current == element_first){
		_window.children("a.mSlider-prev").removeClass("arrow_act");
		_window.children("a.mSlider-next").addClass("arrow_act");
	} else
	if(element_current == element_last-(element_wcount+1)){
		_window.children("a.mSlider-prev").addClass("arrow_act");
		_window.children("a.mSlider-next").removeClass("arrow_act");
	}
	else{
		_window.children("a.mSlider-prev").addClass("arrow_act");  
		_window.children("a.mSlider-next").addClass("arrow_act");        
	}
};

function emarket_mSlider(parent_id, action) {
	var _window = $('#'+parent_id).children(".mSlider-wrap").children(".mSlider-window");
	
	var	element_wcount = 4,
		window_width = _window.parent(".mSlider-wrap").width(),
		element_width = window_width/element_wcount,
		element_Sum = _window.find("li").size(),
		slider_ReelWidth = element_width * element_Sum;
	
	_window.width(slider_ReelWidth);
	_window.find("li").width(element_width);
	
	switch(action) {
		case 'prev':
			var prev_slide = _window.children("li.current").prev();
			if (prev_slide.length > 0) {
				_window.animate({left: "+="+element_width+"px"});
				_window.children("li").removeClass("current");
				prev_slide.addClass("current");
				emarket_mSlider_refresh(parent_id, element_wcount);
			}
		break;
		case 'next':			
			var next_slide = _window.children("li.current").next();
			var naxi = _window.children("li.current").nextAll();
			var element_count = 0;
			naxi.each(function(){element_count++;})
			
			if((next_slide.length > 0) && (element_count >= element_wcount)) {
			
				_window.animate({left: "-="+element_width+"px"});
				_window.children("li").removeClass("current");
				
				next_slide.addClass("current");
				emarket_mSlider_refresh(parent_id, element_wcount);
			}
		break;
		default: break;
	}
}

$(document).on("click", "a.mSlider-prev", function(event){
	event.preventDefault();
	var parent_id = $(this).parent(".emarket-mSlider").attr('id');
	emarket_mSlider(parent_id, 'prev');
});
$(document).on("click", "a.mSlider-next", function(event){
	event.preventDefault();
	var parent_id = $(this).parent(".emarket-mSlider").attr('id');
	emarket_mSlider(parent_id, 'next');
});

$(document).ready(function(){
	$(".emarket-mSlider").each(function(){
		var parent_id = $(this).attr('id');
		var _window = $('#'+parent_id).children(".mSlider-wrap").children(".mSlider-window");
		
		var	element_wcount = 4,
			window_width = _window.parent(".mSlider-wrap").width(),
			element_width = window_width/element_wcount,
			element_Sum   = _window.find("li").size(),
			slider_ReelWidth = element_width * element_Sum;
			
		_window.width(slider_ReelWidth);
		_window.find("li").width(element_width);
	})
})
/* End */
;
; /* Start:"a:4:{s:4:"full";s:125:"/bitrix/templates/okshop/components/bitrix/catalog/catalog-template/bitrix/catalog.section/.default/script.js?158557030433148";s:6:"source";s:109:"/bitrix/templates/okshop/components/bitrix/catalog/catalog-template/bitrix/catalog.section/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window) {

if (!!window.JCCatalogSection)
{
	return;
}

var BasketButton = function(params)
{
	BasketButton.superclass.constructor.apply(this, arguments);
	this.nameNode = BX.create('span', {
		props : { className : 'bx_medium bx_bt_button', id : this.id },
		text: params.text
	});
	this.buttonNode = BX.create('span', {
		attrs: { className: params.ownerClass },
		style: { marginBottom: '0', borderBottom: '0 none transparent' },
		children: [this.nameNode],
		events : this.contextEvents
	});
	if (BX.browser.IsIE())
	{
		this.buttonNode.setAttribute("hideFocus", "hidefocus");
	}
};
BX.extend(BasketButton, BX.PopupWindowButton);

window.JCCatalogSection = function (arParams)
{
	this.productType = 0;
	this.showQuantity = true;
	this.showAbsent = true;
	this.secondPict = false;
	this.showOldPrice = false;
	this.showPercent = false;
	this.showSkuProps = false;
	this.visual = {
		ID: '',
		PICT_ID: '',
		SECOND_PICT_ID: '',
		QUANTITY_ID: '',
		QUANTITY_UP_ID: '',
		QUANTITY_DOWN_ID: '',
		PRICE_ID: '',
		DSC_PERC: '',
		SECOND_DSC_PERC: '',
		DISPLAY_PROP_DIV: '',
		BASKET_PROP_DIV: ''
	};
	this.product = {
		checkQuantity: false,
		maxQuantity: 0,
		stepQuantity: 1,
		isDblQuantity: false,
		canBuy: true,
		canSubscription: true,
		name: '',
		pict: {},
		id: 0,
		addUrl: '',
		buyUrl: ''
	};
	this.basketData = {
		useProps: false,
		emptyProps: false,
		quantity: 'quantity',
		props: 'prop',
		basketUrl: '',
		sku_props: '',
		sku_props_var: 'basket_props'
	};

	this.defaultPict = {
		pict: null,
		secondPict: null
	};

	this.checkQuantity = false;
	this.maxQuantity = 0;
	this.stepQuantity = 1;
	this.isDblQuantity = false;
	this.canBuy = true;
	this.canSubscription = true;
	this.precision = 6;
	this.precisionFactor = Math.pow(10,this.precision);

	this.offers = [];
	this.offerNum = 0;
	this.treeProps = [];
	this.obTreeRows = [];
	this.showCount = [];
	this.showStart = [];
	this.selectedValues = {};

	this.obProduct = null;
	this.obQuantity = null;
	this.obQuantityUp = null;
	this.obQuantityDown = null;
	this.obPict = null;
	this.obSecondPict = null;
	this.obPrice = null;
	this.obTree = null;
	this.obBuyBtn = null;
	this.obDscPerc = null;
	this.obSecondDscPerc = null;
	this.obSkuProps = null;
	this.obMeasure = null;

	this.obPopupWin = null;
	this.basketUrl = '';
	this.basketParams = {};

	this.treeRowShowSize = 10;
	this.treeEnableArrow = { display: '', cursor: 'pointer', opacity: 1 };
	this.treeDisableArrow = { display: '', cursor: 'default', opacity:0.2 };

	this.lastElement = false;
	this.containerHeight = 0;

	this.errorCode = 0;

	if ('object' === typeof arParams)
	{
		this.productType = parseInt(arParams.PRODUCT_TYPE, 10);
		this.showQuantity = arParams.SHOW_QUANTITY;
		this.showAbsent = arParams.SHOW_ABSENT;
		this.secondPict = !!arParams.SECOND_PICT;
		this.showOldPrice = !!arParams.SHOW_OLD_PRICE;
		this.showPercent = !!arParams.SHOW_DISCOUNT_PERCENT;
		this.showSkuProps = !!arParams.SHOW_SKU_PROPS;

		this.visual = arParams.VISUAL;
		switch (this.productType)
		{
			case 1://product
			case 2://set
				if (!!arParams.PRODUCT && 'object' === typeof(arParams.PRODUCT))
				{
					if (this.showQuantity)
					{
						this.product.checkQuantity = arParams.PRODUCT.CHECK_QUANTITY;
						this.product.isDblQuantity = arParams.PRODUCT.QUANTITY_FLOAT;
						if (this.product.checkQuantity)
						{
							this.product.maxQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.MAX_QUANTITY) : parseInt(arParams.PRODUCT.MAX_QUANTITY, 10));
						}
						this.product.stepQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.STEP_QUANTITY) : parseInt(arParams.PRODUCT.STEP_QUANTITY, 10));

						this.checkQuantity = this.product.checkQuantity;
						this.isDblQuantity = this.product.isDblQuantity;
						this.maxQuantity = this.product.maxQuantity;
						this.stepQuantity = this.product.stepQuantity;
						if (this.isDblQuantity)
						{
							this.stepQuantity = Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor;
						}
					}
					this.product.canBuy = arParams.PRODUCT.CAN_BUY;
					this.product.canSubscription = arParams.PRODUCT.SUBSCRIPTION;

					this.canBuy = this.product.canBuy;
					this.canSubscription = this.product.canSubscription;

					this.product.name = arParams.PRODUCT.NAME;
					this.product.pict = arParams.PRODUCT.PICT;
					this.product.id = arParams.PRODUCT.ID;
					if (!!arParams.PRODUCT.ADD_URL)
					{
						this.product.addUrl = arParams.PRODUCT.ADD_URL;
					}
					if (!!arParams.PRODUCT.BUY_URL)
					{
						this.product.buyUrl = arParams.PRODUCT.BUY_URL;
					}
					if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
					{
						this.basketData.useProps = !!arParams.BASKET.ADD_PROPS;
						this.basketData.emptyProps = !!arParams.BASKET.EMPTY_PROPS;
					}
				}
				else
				{
					this.errorCode = -1;
				}
				break;
			case 3://sku
				if (!!arParams.OFFERS && BX.type.isArray(arParams.OFFERS))
				{
					if (!!arParams.PRODUCT && 'object' === typeof(arParams.PRODUCT))
					{
						this.product.name = arParams.PRODUCT.NAME;
						this.product.id = arParams.PRODUCT.ID;
					}
					this.offers = arParams.OFFERS;
					this.offerNum = 0;
					if (!!arParams.OFFER_SELECTED)
					{
						this.offerNum = parseInt(arParams.OFFER_SELECTED, 10);
					}
					if (isNaN(this.offerNum))
					{
						this.offerNum = 0;
					}
					if (!!arParams.TREE_PROPS)
					{
						this.treeProps = arParams.TREE_PROPS;
					}
					if (!!arParams.DEFAULT_PICTURE)
					{
						this.defaultPict.pict = arParams.DEFAULT_PICTURE.PICTURE;
						this.defaultPict.secondPict = arParams.DEFAULT_PICTURE.PICTURE_SECOND;
					}
				}
				else
				{
					this.errorCode = -1;
				}
				break;
			default:
				this.errorCode = -1;
		}
		if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
		{
			if (!!arParams.BASKET.QUANTITY)
			{
				this.basketData.quantity = arParams.BASKET.QUANTITY;
			}
			if (!!arParams.BASKET.PROPS)
			{
				this.basketData.props = arParams.BASKET.PROPS;
			}
			if (!!arParams.BASKET.BASKET_URL)
			{
				this.basketData.basketUrl = arParams.BASKET.BASKET_URL;
			}
			if (3 === this.productType)
			{
				if (!!arParams.BASKET.SKU_PROPS)
				{
					this.basketData.sku_props = arParams.BASKET.SKU_PROPS;
				}
			}
		}
		this.lastElement = (!!arParams.LAST_ELEMENT && 'Y' === arParams.LAST_ELEMENT);
	}
	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
};

window.JCCatalogSection.prototype.Init = function()
{
	var i = 0,
		strPrefix = '',
		TreeItems = null;

	this.obProduct = BX(this.visual.ID);
	if (!this.obProduct)
	{
		this.errorCode = -1;
	}
	this.obPict = BX(this.visual.PICT_ID);
	if (!this.obPict)
	{
		this.errorCode = -2;
	}
	if (this.secondPict && !!this.visual.SECOND_PICT_ID)
	{
		this.obSecondPict = BX(this.visual.SECOND_PICT_ID);
	}
	this.obPrice = BX(this.visual.PRICE_ID);
	if (!this.obPrice)
	{
		this.errorCode = -16;
	}
	if (this.showQuantity && !!this.visual.QUANTITY_ID)
	{
		this.obQuantity = BX(this.visual.QUANTITY_ID);
		if (!!this.visual.QUANTITY_UP_ID)
		{
			this.obQuantityUp = BX(this.visual.QUANTITY_UP_ID);
		}
		if (!!this.visual.QUANTITY_DOWN_ID)
		{
			this.obQuantityDown = BX(this.visual.QUANTITY_DOWN_ID);
		}
	}
	if (3 === this.productType)
	{
		if (!!this.visual.TREE_ID)
		{
			this.obTree = BX(this.visual.TREE_ID);
			if (!this.obTree)
			{
				this.errorCode = -256;
			}
			strPrefix = this.visual.TREE_ITEM_ID;
			for (i = 0; i < this.treeProps.length; i++)
			{
				this.obTreeRows[i] = {
					LEFT: BX(strPrefix+this.treeProps[i].ID+'_left'),
					RIGHT: BX(strPrefix+this.treeProps[i].ID+'_right'),
					LIST: BX(strPrefix+this.treeProps[i].ID+'_list'),
					CONT: BX(strPrefix+this.treeProps[i].ID+'_cont')
				};
				if (!this.obTreeRows[i].LEFT || !this.obTreeRows[i].RIGHT || !this.obTreeRows[i].LIST || !this.obTreeRows[i].CONT)
				{
					this.errorCode = -512;
					break;
				}
			}
		}
		if (!!this.visual.QUANTITY_MEASURE)
		{
			this.obMeasure = BX(this.visual.QUANTITY_MEASURE);
		}
	}
	if (!!this.visual.BUY_ID)
	{
		this.obBuyBtn = BX(this.visual.BUY_ID);
	}

	if (this.showPercent)
	{
		if (!!this.visual.DSC_PERC)
		{
			this.obDscPerc = BX(this.visual.DSC_PERC);
		}
		if (this.secondPict && !!this.visual.SECOND_DSC_PERC)
		{
			this.obSecondDscPerc = BX(this.visual.SECOND_DSC_PERC);
		}
	}

	if (this.showSkuProps)
	{
		if (!!this.visual.DISPLAY_PROP_DIV)
		{
			this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
		}
	}

	if (0 === this.errorCode)
	{
		if (this.showQuantity)
		{
			if (!!this.obQuantityUp)
			{
				BX.bind(this.obQuantityUp, 'click', BX.delegate(this.QuantityUp, this));
			}
			if (!!this.obQuantityDown)
			{
				BX.bind(this.obQuantityDown, 'click', BX.delegate(this.QuantityDown, this));
			}
			if (!!this.obQuantity)
			{
				BX.bind(this.obQuantity, 'change', BX.delegate(this.QuantityChange, this));
			}
		}
		switch (this.productType)
		{
			case 1://product
				break;
			case 3://sku
				TreeItems = BX.findChildren(this.obTree, {tagName: 'li'}, true);
				if (!!TreeItems && 0 < TreeItems.length)
				{
					for (i = 0; i < TreeItems.length; i++)
					{
						BX.bind(TreeItems[i], 'click', BX.delegate(this.SelectOfferProp, this));
					}
				}
				for (i = 0; i < this.obTreeRows.length; i++)
				{
					BX.bind(this.obTreeRows[i].LEFT, 'click', BX.delegate(this.RowLeft, this));
					BX.bind(this.obTreeRows[i].RIGHT, 'click', BX.delegate(this.RowRight, this));
				}
				this.SetCurrent();
				break;
		}
		if (!!this.obBuyBtn)
		{
			BX.bind(this.obBuyBtn, 'click', BX.delegate(this.Basket, this));
		}
		if (this.lastElement)
		{
			this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
			if (isNaN(this.containerHeight))
			{
				this.containerHeight = 0;
			}
			this.setHeight();
			BX.bind(window, 'resize', BX.delegate(this.checkHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseover', BX.delegate(this.setHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseout', BX.delegate(this.clearHeight, this));
		}
	}
};

window.JCCatalogSection.prototype.checkHeight = function()
{
	this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
	if (isNaN(this.containerHeight))
	{
		this.containerHeight = 0;
	}
};

window.JCCatalogSection.prototype.setHeight = function()
{
	if (0 < this.containerHeight)
	{
		BX.adjust(this.obProduct.parentNode, {style: { height: this.containerHeight+'px'}});
	}
};

window.JCCatalogSection.prototype.clearHeight = function()
{
	BX.adjust(this.obProduct.parentNode, {style: { height: 'auto'}});
};

window.JCCatalogSection.prototype.QuantityUp = function()
{
	var curValue = 0,
		boolSet = true;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue += this.stepQuantity;
			if (this.checkQuantity)
			{
				if (curValue > this.maxQuantity)
				{
					boolSet = false;
				}
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
			}
		}
	}
};

window.JCCatalogSection.prototype.QuantityDown = function()
{
	var curValue = 0,
		boolSet = true;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value): parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue -= this.stepQuantity;
			if (curValue < this.stepQuantity)
			{
				boolSet = false;
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
			}
		}
	}
};

window.JCCatalogSection.prototype.QuantityChange = function()
{
	var curValue = 0,
		boolSet = true;

	if (0 === this.errorCode && this.showQuantity)
	{
		if (this.canBuy)
		{
			curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
			if (!isNaN(curValue))
			{
				if (this.checkQuantity)
				{
					if (curValue > this.maxQuantity)
					{
						boolSet = false;
						curValue = this.maxQuantity;
					}
					else if (curValue < this.stepQuantity)
					{
						boolSet = false;
						curValue = this.stepQuantity;
					}
				}
				if (!boolSet)
				{
					this.obQuantity.value = curValue;
				}
			}
			else
			{
				this.obQuantity.value = this.stepQuantity;
			}
		}
		else
		{
			this.obQuantity.value = this.stepQuantity;
		}
	}
};

window.JCCatalogSection.prototype.QuantitySet = function(index)
{
	if (0 === this.errorCode)
	{
		this.canBuy = this.offers[index].CAN_BUY;
		if (this.canBuy)
		{
			BX.addClass(this.obBuyBtn, 'bx_bt_button');
			BX.removeClass(this.obBuyBtn, 'bx_bt_button_type_2');
			this.obBuyBtn.innerHTML = BX.message('MESS_BTN_BUY');
		}
		else
		{
			BX.addClass(this.obBuyBtn, 'bx_bt_button_type_2');
			BX.removeClass(this.obBuyBtn, 'bx_bt_button');
			this.obBuyBtn.innerHTML = BX.message('MESS_NOT_AVAILABLE');
		}
		if (this.showQuantity)
		{
			this.isDblQuantity = this.offers[index].QUANTITY_FLOAT;
			this.checkQuantity = this.offers[index].CHECK_QUANTITY;
			if (this.isDblQuantity)
			{
				this.maxQuantity = parseFloat(this.offers[index].MAX_QUANTITY);
				this.stepQuantity = Math.round(parseFloat(this.offers[index].STEP_QUANTITY)*this.precisionFactor)/this.precisionFactor;
			}
			else
			{
				this.maxQuantity = parseInt(this.offers[index].MAX_QUANTITY, 10);
				this.stepQuantity = parseInt(this.offers[index].STEP_QUANTITY, 10);
			}

			this.obQuantity.value = this.stepQuantity;
			this.obQuantity.disabled = !this.canBuy;
			if (!!this.obMeasure)
			{
				if (!!this.offers[index].MEASURE)
				{
					BX.adjust(this.obMeasure, { html : this.offers[index].MEASURE});
				}
				else
				{
					BX.adjust(this.obMeasure, { html : ''});
				}
			}
		}
	}
};

window.JCCatalogSection.prototype.SelectOfferProp = function()
{
	var i = 0,
		value = '',
		strTreeValue = '',
		arTreeItem = [],
		RowItems = null,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		arTreeItem = strTreeValue.split('_');
		if (this.SearchOfferPropIndex(arTreeItem[0], arTreeItem[1]))
		{
			RowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);
			if (!!RowItems && 0 < RowItems.length)
			{
				for (i = 0; i < RowItems.length; i++)
				{
					value = RowItems[i].getAttribute('data-onevalue');
					if (value === arTreeItem[1])
					{
						BX.addClass(RowItems[i], 'bx_active');
					}
					else
					{
						BX.removeClass(RowItems[i], 'bx_active');
					}
				}
			}
		}
	}
};

window.JCCatalogSection.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
{
	var strName = '',
		arShowValues = false,
		i, j,
		arCanBuyValues = [],
		index = -1,
		arFilter = {},
		tmpFilter = [];

	for (i = 0; i < this.treeProps.length; i++)
	{
		if (this.treeProps[i].ID === strPropID)
		{
			index = i;
			break;
		}
	}

	if (-1 < index)
	{
		for (i = 0; i < index; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arFilter[strName] = this.selectedValues[strName];
		}
		strName = 'PROP_'+this.treeProps[index].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			return false;
		}
		if (!BX.util.in_array(strPropValue, arShowValues))
		{
			return false;
		}
		arFilter[strName] = strPropValue;
		for (i = index+1; i < this.treeProps.length; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arShowValues = this.GetRowValues(arFilter, strName);
			if (!arShowValues)
			{
				return false;
			}
			if (this.showAbsent)
			{
				arCanBuyValues = [];
				tmpFilter = [];
				tmpFilter = BX.clone(arFilter, true);
				for (j = 0; j < arShowValues.length; j++)
				{
					tmpFilter[strName] = arShowValues[j];
					if (this.GetCanBuy(tmpFilter))
					{
						arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
					}
				}
			}
			else
			{
				arCanBuyValues = arShowValues;
			}
			if (!!this.selectedValues[strName] && BX.util.in_array(this.selectedValues[strName], arCanBuyValues))
			{
				arFilter[strName] = this.selectedValues[strName];
			}
			else
			{
				arFilter[strName] = arCanBuyValues[0];
			}
			this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
		}
		this.selectedValues = arFilter;
		this.ChangeInfo();
	}
	return true;
};

window.JCCatalogSection.prototype.RowLeft = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if (0 > this.showStart[index])
			{
				this.showStart[index]++;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeEnableArrow });
			}

			if (0 <= this.showStart[index])
			{
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogSection.prototype.RowRight = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if ((this.treeRowShowSize - this.showStart[index]) < this.showCount[index])
			{
				this.showStart[index]--;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeEnableArrow });
			}

			if ((this.treeRowShowSize - this.showStart[index]) >= this.showCount[index])
			{
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogSection.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
{
	var i = 0,
		showI = 0,
		value = '',
		countShow = 0,
		strNewLen = '',
		obData = {},
		pictMode = false,
		extShowMode = false,
		isCurrent = false,
		selectIndex = 0,
		obLeft = this.treeEnableArrow,
		obRight = this.treeEnableArrow,
		currentShowStart = 0,
		RowItems = null;

	if (-1 < intNumber && intNumber < this.obTreeRows.length)
	{
		RowItems = BX.findChildren(this.obTreeRows[intNumber].LIST, {tagName: 'li'}, false);
		if (!!RowItems && 0 < RowItems.length)
		{
			pictMode = ('PICT' === this.treeProps[intNumber].SHOW_MODE);
			countShow = showID.length;
			extShowMode = this.treeRowShowSize < countShow;
			strNewLen = (extShowMode ? (100/countShow)+'%' : '20%');
			obData = {
				props: { className: '' },
				style: {
					width: strNewLen
				}
			};
			if (pictMode)
			{
				obData.style.paddingTop = strNewLen;
			}
			for (i = 0; i < RowItems.length; i++)
			{
				value = RowItems[i].getAttribute('data-onevalue');
				isCurrent = (value === activeID);
				if (BX.util.in_array(value, canBuyID))
				{
					obData.props.className = (isCurrent ? 'bx_active' : '');
				}
				else
				{
					obData.props.className = (isCurrent ? 'bx_active bx_missing' : 'bx_missing');
				}
				obData.style.display = 'none';
				if (BX.util.in_array(value, showID))
				{
					obData.style.display = '';
					if (isCurrent)
					{
						selectIndex = showI;
					}
					showI++;
				}
				BX.adjust(RowItems[i], obData);
			}

			obData = {
				style: {
					width: (extShowMode ? 20*countShow : 100)+'%',
					marginLeft: '0%'
				}
			};
			if (pictMode)
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_scu full' : 'bx_item_detail_scu')}});
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_size full' : 'bx_item_detail_size')}});
			}
			if (extShowMode)
			{
				if (selectIndex +1 === countShow)
				{
					obRight = this.treeDisableArrow;
				}
				if (this.treeRowShowSize <= selectIndex)
				{
					currentShowStart = this.treeRowShowSize - selectIndex - 1;
					obData.style.marginLeft = currentShowStart*20+'%';
				}
				if (0 === currentShowStart)
				{
					obLeft = this.treeDisableArrow;
				}
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: obLeft });
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: obRight });
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: {display: 'none'}});
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: {display: 'none'}});
			}
			BX.adjust(this.obTreeRows[intNumber].LIST, obData);
			this.showCount[intNumber] = countShow;
			this.showStart[intNumber] = currentShowStart;
		}
	}
};

window.JCCatalogSection.prototype.GetRowValues = function(arFilter, index)
{
	var i = 0,
		j,
		arValues = [],
		boolSearch = false,
		boolOneSearch = true;

	if (0 === arFilter.length)
	{
		for (i = 0; i < this.offers.length; i++)
		{
			if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
			{
				arValues[arValues.length] = this.offers[i].TREE[index];
			}
		}
		boolSearch = true;
	}
	else
	{
		for (i = 0; i < this.offers.length; i++)
		{
			boolOneSearch = true;
			for (j in arFilter)
			{
				if (arFilter[j] !== this.offers[i].TREE[j])
				{
					boolOneSearch = false;
					break;
				}
			}
			if (boolOneSearch)
			{
				if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
				{
					arValues[arValues.length] = this.offers[i].TREE[index];
				}
				boolSearch = true;
			}
		}
	}
	return (boolSearch ? arValues : false);
};

window.JCCatalogSection.prototype.GetCanBuy = function(arFilter)
{
	var i = 0,
		j,
		boolSearch = false,
		boolOneSearch = true;

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in arFilter)
		{
			if (arFilter[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			if (this.offers[i].CAN_BUY)
			{
				boolSearch = true;
				break;
			}
		}
	}
	return boolSearch;
};

window.JCCatalogSection.prototype.SetCurrent = function()
{
	var i = 0,
		j = 0,
		arCanBuyValues = [],
		strName = '',
		arShowValues = false,
		arFilter = {},
		tmpFilter = [],
		current = this.offers[this.offerNum].TREE;

	for (i = 0; i < this.treeProps.length; i++)
	{
		strName = 'PROP_'+this.treeProps[i].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			break;
		}
		if (BX.util.in_array(current[strName], arShowValues))
		{
			arFilter[strName] = current[strName];
		}
		else
		{
			arFilter[strName] = arShowValues[0];
			this.offerNum = 0;
		}
		if (this.showAbsent)
		{
			arCanBuyValues = [];
			tmpFilter = [];
			tmpFilter = BX.clone(arFilter, true);
			for (j = 0; j < arShowValues.length; j++)
			{
				tmpFilter[strName] = arShowValues[j];
				if (this.GetCanBuy(tmpFilter))
				{
					arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
				}
			}
		}
		else
		{
			arCanBuyValues = arShowValues;
		}
		this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
	}
	this.selectedValues = arFilter;
	this.ChangeInfo();
};

window.JCCatalogSection.prototype.ChangeInfo = function()
{
	var i = 0,
		j,
		index = -1,
		obData = {},
		boolOneSearch = true,
		strPrice = '';

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in this.selectedValues)
		{
			if (this.selectedValues[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			index = i;
			break;
		}
	}
	if (-1 < index)
	{
		if (!!this.obPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE)
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.secondPict && !!this.obSecondPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE_SECOND)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE_SECOND.SRC+')'}});
			}
			else if (!!this.offers[index].PREVIEW_PICTURE.SRC)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else if (!!this.defaultPict.secondPict)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.secondPict.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.showSkuProps && !!this.obSkuProps)
		{
			if (0 === this.offers[index].DISPLAY_PROPERTIES.length)
			{
				BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
			}
			else
			{
				BX.adjust(this.obSkuProps, {style: {display: ''}, html: this.offers[index].DISPLAY_PROPERTIES});
			}
		}
		if (!!this.obPrice)
		{
			strPrice = this.offers[index].PRICE.PRINT_DISCOUNT_VALUE;
			if (this.showOldPrice && (this.offers[index].PRICE.DISCOUNT_VALUE !== this.offers[index].PRICE.VALUE))
			{
				strPrice += ' <span>'+this.offers[index].PRICE.PRINT_VALUE+'</span>';
			}
			BX.adjust(this.obPrice, {html: strPrice});
			if (this.showPercent)
			{
				if (this.offers[index].PRICE.DISCOUNT_VALUE !== this.offers[index].PRICE.VALUE)
				{
					obData = {
						style: {
							display: ''
						},
						html: this.offers[index].PRICE.DISCOUNT_DIFF_PERCENT
					};
				}
				else
				{
					obData = {
						style: {
							display: 'none'
						},
						html: ''
					};
				}
				if (!!this.obDscPerc)
				{
					BX.adjust(this.obDscPerc, obData);
				}
				if (!!this.obSecondDscPerc)
				{
					BX.adjust(this.obSecondDscPerc, obData);
				}
			}
		}
		this.offerNum = index;
		this.QuantitySet(this.offerNum);
	}
};

window.JCCatalogSection.prototype.InitBasketUrl = function()
{
	switch (this.productType)
	{
		case 1://product
		case 2://set
			this.basketUrl = this.product.addUrl;
			break;
		case 3://sku
			this.basketUrl = this.offers[this.offerNum].ADD_URL;
			break;
	}
	this.basketParams = {
		'ajax_basket': 'Y'
	};
	if (this.showQuantity)
	{
		this.basketParams[this.basketData.quantity] = this.obQuantity.value;
	}
	if (!!this.basketData.sku_props)
	{
		this.basketParams[this.basketData.sku_props_var] = this.basketData.sku_props;
	}
};

window.JCCatalogSection.prototype.FillBasketProps = function()
{
	if (!this.visual.BASKET_PROP_DIV)
	{
		return;
	}
	var
		i = 0,
		propCollection = null,
		foundValues = false,
		obBasketProps = null;

	if (this.basketData.useProps && !this.basketData.emptyProps)
	{
		if (!!this.obPopupWin && !!this.obPopupWin.contentContainer)
		{
			obBasketProps = this.obPopupWin.contentContainer;
		}
	}
	else
	{
		obBasketProps = BX(this.visual.BASKET_PROP_DIV);
	}
	if (!!obBasketProps)
	{
		propCollection = obBasketProps.getElementsByTagName('select');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'select-one':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						default:
							break;
					}
				}
			}
		}
		propCollection = obBasketProps.getElementsByTagName('input');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'hidden':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						case 'radio':
							if (propCollection[i].checked)
							{
								this.basketParams[propCollection[i].name] = propCollection[i].value;
								foundValues = true;
							}
							break;
						default:
							break;
					}
				}
			}
		}
	}
	if (!foundValues)
	{
		this.basketParams[this.basketData.props] = [];
		this.basketParams[this.basketData.props][0] = 0;
	}
};

window.JCCatalogSection.prototype.SendToBasket = function()
{
	if (!this.canBuy)
	{
		return;
	}
	this.InitBasketUrl();
	this.FillBasketProps();
	BX.ajax.loadJSON(
		this.basketUrl,
		this.basketParams,
		BX.delegate(this.BasketResult, this)
	);
};

window.JCCatalogSection.prototype.Basket = function()
{
	var contentBasketProps = '';
	if (!this.canBuy)
	{
		return;
	}
	switch (this.productType)
	{
	case 1://product
	case 2://set
		if (this.basketData.useProps && !this.basketData.emptyProps)
		{
			this.InitPopupWindow();
			this.obPopupWin.setTitleBar({
				content: BX.create('div', {
					style: { marginRight: '30px', whiteSpace: 'nowrap' },
					text: BX.message('TITLE_BASKET_PROPS')
				})
			});
			if (BX(this.visual.BASKET_PROP_DIV))
			{
				contentBasketProps = BX(this.visual.BASKET_PROP_DIV).innerHTML;
			}
			this.obPopupWin.setContent(contentBasketProps);
			this.obPopupWin.setButtons([
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_SEND_PROPS'),
					events: {
						click: BX.delegate(this.SendToBasket, this)
					}
				})
			]);
			this.obPopupWin.show();
		}
		else
		{
			this.SendToBasket();
		}
		break;
	case 3://sku
		this.SendToBasket();
		break;
	}
};

window.JCCatalogSection.prototype.BasketResult = function(arResult)
{
	var strContent = '',
		strName = '',
		strPict = '',
		successful = true,
		buttons = [];

	if (!!this.obPopupWin)
	{
		this.obPopupWin.close();
	}
	if ('object' !== typeof arResult)
	{
		return false;
	}
	successful = ('OK' === arResult.STATUS);
	if (successful)
	{
		BX.onCustomEvent('OnBasketChange');
		strName = this.product.name;
		switch(this.productType)
		{
		case 1://
		case 2://
			strPict = this.product.pict.SRC;
			break;
		case 3:
			strPict = (!!this.offers[this.offerNum].PREVIEW_PICTURE ?
				this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
				this.defaultPict.pict.SRC
			);
			break;
		}
		strContent = '<div class="product-modal"><img src="'+strPict+'" height="150"><div class="title-prod-modal">'+strName+'</div></div>';		
        ReloadGlobalBasket();
        buttons = [
			new BasketButton({
				ownerClass: this.obProduct.parentNode.parentNode.className,
				text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
				events: {
					click: BX.delegate(function(){
						location.href = (!!this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
					}, this)
				}
			})
		];
	}
	else
	{
	   if(arResult.MESSAGE)
       {
        strContent = arResult.MESSAGE
       }else
       {
        strContent = BX.message('BASKET_UNKNOWN_ERROR');
       }
	
	}
/*	this.InitPopupWindow();
	this.obPopupWin.setTitleBar({
		content: BX.create('div', {
			style: { marginRight: '30px', whiteSpace: 'nowrap' },
			text: (successful ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'))
		})
	});*/
    console.log(strContent);
    $("#BasketEmodal .emodal-data").html(strContent);
    $("#BasketEmodal").eModal();
	//this.obPopupWin.setContent(strContent);
    //	this.obPopupWin.setButtons(buttons);
	//this.obPopupWin.show();
};

window.JCCatalogSection.prototype.InitPopupWindow = function()
{
	if (!!this.obPopupWin)
	{
		return;
	}
	this.obPopupWin = BX.PopupWindowManager.create('CatalogSectionBasket_'+this.visual.ID, null, 
    {
        
		autoHide: false,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		closeByEsc: true,
		titleBar: true,
		closeIcon: {top: '10px', right: '10px'}
	});
};
})(window);

/*jquery-functions*/
var index_xhr;

function get_compare(){
	$.ajax({
		type: "POST",
		url: EmarketSite.SITE_DIR+"ajax/get_compare.php",
		dataType: "html",
		success: function(html){
			$("#emarket-compare-list").html(html);
		}
	});
}

$(document).on("change", ".compare-control-input", function(){
	var this_is = $(this),
		this_list = $("#this_list").val(),
		this_id = parseInt(this_is.attr('data-id'));
			
	if(this_is.prop("checked")) 
		var this_type = 'compare_add';
	else
		var this_type = 'compare_del';
	
	if(index_xhr && index_xhr.readyState != 4)
		index_xhr.abort();

	index_xhr = $.ajax({
		type: "POST",
		url: EmarketSite.SITE_DIR+"ajax/compare_control.php",
		data: {
			id:this_id, 
			type:this_type, 
			list:this_list
		},
		beforeSend: function(){this_is.parent('.compare-control').children('.load').fadeIn(300);},
		success: function(data){
			if(data != 'ERROR')	
				get_compare();
			this_is.parent('.compare-control').children('.load').fadeOut(300);
		}
	});
});
function ReloadGlobalBasket()
{
    $.post(EmarketSite.SITE_DIR+'ajax/get_basket.php',
    {},
    function(data){
        $('#emarket-basket').html(data);
    });
}

/* End */
;; /* /bitrix/templates/okshop/components/bitrix/catalog.smart.filter/visual_vertical/script.js?158557030413954*/
; /* /bitrix/templates/okshop/components/bitrix/catalog/catalog-template/bitrix/catalog.section.list/.default/script.js?15855703043130*/
; /* /bitrix/templates/okshop/components/bitrix/catalog/catalog-template/bitrix/catalog.section/.default/script.js?158557030433148*/
