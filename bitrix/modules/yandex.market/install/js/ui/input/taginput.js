(function(BX, $, window) {

	var Plugin = BX.namespace('YandexMarket.Plugin');
	var Input = BX.namespace('YandexMarket.Ui.Input');

	var constructor = Input.TagInput = Plugin.Base.extend({

		defaults: {
			width: 200,
			tags: true,

			lang: {},
			langPrefix: 'CHOSEN_'
		},

		initVars: function() {
			this.callParent('initVars', constructor);
			this._isPluginReady = false;
		},

		initialize: function() {
			this.clearClone();
			this.callParent('initialize', constructor);
			this.createPlugin();
		},

		destroy: function() {
			this.destroyPlugin();
			this.callParent('destroy', constructor);
		},

		clearClone: function() {
			var pluginContainer = this.$el.next();

			this.$el.removeClass('select2-hidden-accessible').removeAttr('aria-hidden').removeAttr('tabindex');

			if (pluginContainer.hasClass('select2')) {
				pluginContainer.remove();
			}
		},

		createPlugin: function() {
			if (this._isPluginReady) { return; }

			var options = this.createPluginOptions();

			this._isPluginReady = true;

			this.$el.select2(options);
		},

		createPluginOptions: function() {
			var result = $.extend(true, {
				width: this.options.width,
				tags: this.options.tags,
			}, this.getLanguageOptions());

			if (this.options.autocomplete) {
				result = $.extend(true, result, this.getAjaxOptions());
			}

			return result;
		},

		getLanguageOptions: function() {
			var _this = this;

			return {
				placeholder: _this.getLang('PLACEHOLDER'),
				language: {
					errorLoading: function () {
						return _this.getLang('LOAD_ERROR');
					},
					inputTooLong: function (t) {
						return _this.getLang('TOO_LONG', {
							'LIMIT': t.maximum
						});
					},
					inputTooShort: function (t) {
						return _this.getLang('TOO_SHORT', {
							'LIMIT': t.minimum
						});
					},
					loadingMore: function () {
						return _this.getLang('LOAD_PROGRESS');
					},
					maximumSelected: function (t) {
						return _this.getLang('MAX_SELECT', {
							'LIMIT': t.maximum
						});
					},
					noResults: function () {
						return _this.getLang('NO_RESULTS');
					},
					searching: function () {
						return _this.getLang('SEARCHING');
					}
				}
			};
		},

		getAjaxOptions: function() {
			var element = this.$el;

			return {
				minimumInputLength: 1,
				tags: false,
				ajax: {
					delay: 300,
					url: 'yamarket_filter_autocomplete.php',
					type: 'post',
					data: function (params) {
						return {
							QUERY: params.term,
							SOURCE_FIELD: element.data('sourceField'),
							IBLOCK_ID: element.data('iblockId')
						}
					},
					dataType: 'json',
					processResults: function (data, params) {
						var i;
						var hasTerm = false;
						var result = {
							results: []
						};

						if (!$.isPlainObject(data)) {
							// not valid data
						} else if ('suggest' in data) {
							for (i = 0; i < data.suggest.length; i++) {
								if (data.suggest[i]['VALUE'] == params.term) {
									hasTerm = true;
								}

								result['results'].push({
									id: data.suggest[i]['ID'],
									text: data.suggest[i]['VALUE']
								});
							}
						}

						if (!hasTerm) {
							result['results'].push({
								id: params.term,
								text: params.term,
							});
						}

						return result;
					}
				}
			};
		},

		destroyPlugin: function() {
			this._isPluginReady = false;
			this.$el.select2('destroy');
		}

	}, {
		dataName: 'uiTagInput'
	});

})(BX, jQuery, window);