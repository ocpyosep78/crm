SNIPPET_IMAGES = 'core/private/lib/Snippet/output/images';



J(function(){
	J('body').on('snippets', function(){
		J('snippet[initialized!="true"]').each(function(){
			this.attr('initialized', 'true');
			Snippets.add(new Snippet(this));
		}, true);
	});
});



/**
 * Central store of loaded Snippet objects
 */
var Snippets = {
	members: {},

	add: function(Snippet){
		var groupId = Snippet.getGroupId();

		if (groupId)
		{
			this.members[groupId] || (this.members[groupId] = {});
			this.members[groupId][Snippet.getType()] = Snippet;
		}
	},

	get: function(groupId, type) {
		return (this.members[groupId]||{})[type];
	}
}



/**
 * Snippet Constructor
 */
function Snippet(el){
	var params = J.parseJSON(el.attr('params')),
	    code = params.code,
	    type = params.type,
	    groupId = params.groupId;

	/**
	 * Public methods (getters)
	 */
	this.getType    = function(){ return type;    };
	this.getCode    = function(){ return code;    };
	this.getGroupId = function(){ return groupId; };

	/**
	 * Private "methods"
	 */
	function my(sel) {
		return el.find(sel);
	}

	function linked(type) {
		return Snippets.get(groupId, type);
	}

	function resetBigToolsState(btns) {
		linked('bigTools') && linked('bigTools').disable().enable(btns||true);
	}

	/**
	 * Load a new snippet
	 */
	function addSnippet(snippet, opts) {
		xajax_addSnippet(snippet, code, J.extend({}, params, opts));
	}

	/**
	 * Request a new Snippet from the server, or perform an action
	 */
	function request(snippet, filters) {
		var ask = {deleteItem: '¿Realmente desea eliminar este elemento?',
		           blockItem: '¿Realmente desea bloquear este elemento?'};
		if (!ask[snippet] || confirm(ask[snippet])) {
			addSnippet(snippet, {filters: filters, writeTo: ''});
		}
	}

	/**
	 * Snippet especialized initializers
	 */
	var methods = {
		comboList: function() {
			my('.comboList').change(function(){
				J(this).val() && request('viewItem', J(this).val());
			});
		},

		bigTools: function() {
			var btns = J.extend(my('[btn][class!="btOff"]'), {
				// Empty code: all btns; string or array: listed btn(s)
				get: function(code) {
					var set = code ? btns.filter(':not([btn])') : this;
					code && J.each(J.isArray(code) ? code : [code], function(){
						set = set.add(btns.filter('[btn="'+this+'"]'));
					});
					return set;
				}
			}).click(function(){
				var axn = J(this).attr('btn'),
				    uid = my('[btn]').attr('uid') || '';
				J(this).hasClass('btOn') && request(axn + 'Item', uid);
			});

			this.disable = function(code) {
				btns.get(code).removeClass('btOn');
				return this;
			};

			this.enable = function(code) {
				var btn = (code === true) ? this.enable.ss : btns.get(code);
				this.enable.ss = btn.addClass('btOn');
				return this;
			};

			this.id = function(uid) {
				return uid ? (this.uid = uid) && this : this.uid;
			};

			this.enable.ss = J();
		},

		commonList: function() {
			resetBigToolsState(['create']);

			my('.listWrapper').on('fill', function(){
				// Store horizontal position and width of each cell...
				my('tbody tr:first td').each(function(i){
					my('.listTitles div:eq('+i+')')
						.width(J(this).width())
						.css('left', J(this).position().left);
				});
			});

			// Append set filters, and tgt ID to parent's atts
			addSnippet('innerCommonList', {writeTo: 'lw_'+groupId, page: 1});
		},

		innerCommonList: function() {
			el.parents('.listWrapper').trigger('fill');

			my('.innerListRow').click(function(){
				my('.selectedListRow').removeClass('selectedListRow');
				J(this).addClass('selectedListRow');
				linked('bigTools').enable().id(J(this)._for());
			});

			my('.innerListRow').dblclick(function(){
				// Create new embeddedView if there was none in current row
				if (!J(this).next().find('.embeddedView').remove().length) {
					my('.embeddedView').parents('tr').remove();

					J('<td />', {
						'id': 'embed_' + groupId,
						'class': 'embeddedView',
						'colspan': J(this).find('td').length
					}).on('embed', function(){
						this.scrollIntoView();
//						this.scrollIntoView && this.scrollIntoView(true);
					}).appendTo(J('<tr />').insertAfter(this));

					// Request the embeddedView content
					addSnippet('snp_viewItem', {filters: J(this)._for(),
					                            writeTo: 'embed_' + groupId});
				}
			});

			my('.innerListTools').on('click', '[btn]', function(){
				var uid = J(this).parents('.innerListRow')._for();
				request(J(this).attr('btn') + 'Item', uid);
			});
		},

		simpleList: function() {

		},

		viewItem: function() {
			resetBigToolsState(['list', 'create', 'edit', 'delete']);
			J('#embed_'+groupId).trigger('embed');
		},

		createItem: function(editing){
			// BigTools buttons
			editing && resetBigToolsState(['create', 'view']);
			linked('bigTools').enable('list'); // Enabled either way

			// Form submitting
			my('.snippet_createForm').submit(function(){
				var filters = J(this).serializeJSON();
				return request(editing ? 'edit' : 'create', filters) & false;
			});

			this.tooltip = function(field, msg) {
				var tgt = my('.snippet_createForm [name="'+field+'"]');
				showTip(tgt, msg, 'bottom left', '.snippet_createForm');
			};
		},

		editItem: function() {
			this.createItem(true);
		}
	}

	// Call the handler method of this Snippet
	methods[type] && methods[type].call(this);
}