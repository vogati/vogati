(function(BX, $, window) {

	var Plugin = BX.namespace('YandexMarket.Plugin');
	var Input = BX.namespace('YandexMarket.Ui.Input');

	var constructor = Input.DependField = Plugin.Base.extend({

		defaults: {
			depend: null,
		},

		initialize: function() {
			this.callParent('initialize', constructor);
			this.bind();
		},

		destroy: function() {
			this.unbind();
			this.callParent('destroy', constructor);
		},

		bind: function() {
			this.handleDependChange(true);
		},

		unbind: function() {
			this.handleDependChange(false);
		},

		handleDependChange: function(dir) {
			var fields = this.getDependElements();

			fields[dir ? 'on' : 'off']('change', $.proxy(this.onDependChange, this));
		},

		onDependChange: function() {
			this.update();
		},

		update: function() {
			var isMatch = this.resolveDependRules();

			this.$el.toggleClass('is--hidden', !isMatch);
		},

		resolveDependRules: function() {
			var rules = this.options.depend;
			var rule;
			var fields = this.getDependFields();
			var fieldKey;
			var field;
			var fieldValue;
			var result = true;

			for (fieldKey in fields) {
				if (!fields.hasOwnProperty(fieldKey)) { continue; }

				field = fields[fieldKey];
				fieldValue = field.val();
				rule = rules[fieldKey];

				if (!this.isMatchRule(rule, fieldValue)) {
					result = false;
					break;
				}
			}

			return result;
		},

		isMatchRule: function(rule, value) {
			var result = true;

			switch (rule['RULE']) {
				case 'EMPTY':
					result = (!value === rule['VALUE']);
				break;
			}

			return result;
		},

		getDependElements: function() {
			var fields = this.getDependFields();
			var fieldKey;
			var field;
			var result = $();

			for (fieldKey in fields) {
				if (!fields.hasOwnProperty(fieldKey)) { continue; }

				field = fields[fieldKey];
				result = result.add(field);
			}

			return result;
		},

		getDependFields: function() {
			var keys = Object.keys(this.options.depend);
			var keyIndex;
			var key;
			var field;
			var fields = {};

			for (keyIndex = 0; keyIndex < keys.length; keyIndex++) {
				key = keys[keyIndex];
				field = this.getField(key);

				if (field) {
					fields[key] = field;
				}
			}

			return fields;
		},

		getField: function(selector) {
			var result;

			if (selector.substr(0,1 ) === '#') {
				result = $(selector);
			} else {
				result = this.getFormField(selector);
			}

			return result;
		},

		getFormField: function(name) {
			var form = this.$el.closest('form')[0];

			return form[name] ? $(form[name]) : null;
		},

	}, {
		dataName: 'UiInputDependField'
	});

})(BX, jQuery, window);