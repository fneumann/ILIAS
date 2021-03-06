il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function($, maincontrols) {
	maincontrols.mainbar = (function($) {
		var mappings = {},
			external_commands = {
				/**
				 * Engage a certain tool
				 */
				engageTool: function(mapping_id) {
					var tool_id = mappings[mapping_id];
					this.model.actions.engageTool(tool_id);
					this.renderer.render(this.model.getState());
				},
				/**
				 * Just open the tools, activate last one
				 */
				disengageAll: function() {
					this.model.actions.disengageAll();
					this.renderer.render(this.model.getState());
				},
			},
			construction = {
				/**
				 * Add an entry to the model representing a tool.
				 * A tool, other than a regular entry, may be removeable by a user
				 * or may be invisible at first.
				 * This only adds to the model, the html-parts still need to be registered.
				 */
				addToolEntry: function (position_id, removeable = true, hidden = false) {
					this.model.actions.addTool(position_id, removeable, hidden);
				},
				/**
				 * An entry consists of several visible parts: the button, the slate and
				 * the close-button (remover).
				 * All these parts are summed up in the model via the position_id;
				 * however, when it comes to rendering, the individual parts are needed.
				 * The function also adds an entry to the model if there is none already.
				 */
				addPartIdAndEntry: function (position_id, part, html_id, is_tool = false) {
					this.renderer.addEntry(position_id, part, html_id);
					if( !is_tool
						&& (position_id in this.model.getState().tools == false)
					) {
						this.model.actions.addEntry(position_id);
					}
				},
				/**
				 * Toplevel entries and tools are being given to the mainbar with an
				 * id; this mapps the id to the position_id calculated during rendering of
				 * the mainbar.
				 */
				addMapping: function(mapping_id, position_id) {
					if(! (mapping_id in Object.keys(mappings))) {
						mappings[mapping_id] = position_id;
					}
				},
				/**
				 * Register signals. Signals will have an id (=position_id) and an action
				 * in their options,
				 */
				addTriggerSignal: function(signal) {
					$(document).on(signal, function(event, signalData) {
						var id = signalData.options.entry_id,
							action = signalData.options.action,
							mb = il.UI.maincontrols.mainbar,
							state;

						switch(action) {
							case 'trigger_mapped':
								id = mappings[id]; //no break afterwards!
							case 'trigger':
								state = mb.model.getState();
								if(id in state.tools) {
									mb.model.actions.engageTool(id);
								}
								if(id in state.entries) { //toggle
									if(state.entries[id].engaged) {
										mb.model.actions.disengageEntry(id);
									} else {
										mb.model.actions.engageEntry(id);
									}
								}
								break;
							case 'remove':
								mb.model.actions.removeTool(id);
								break;
							case 'disengage_all':
								mb.model.actions.disengageAll();
								break;
							case 'toggle_tools':
								mb.model.actions.toggleTools();
								break;
						}

						mb.renderer.render(mb.model.getState());
						mb.persistence.store(mb.model.getState());
					});
				}
			},
			helper = {
				getMappingIdForPosId: function (position_id) {
					for(var idx in mappings) {
						if(mappings[idx] === position_id) {
							return idx;
						}
					}
					return null;
				},
				getFirstEngagedToolId: function(tools) {
					var keys = Object.keys(tools);
					for(var idx in keys) {
						if(tools[keys[idx]].engaged) {
							return keys[idx];
						}
					}
					return false;
				}
			},
			adjustToScreenSize = function() {
				var mb = il.UI.maincontrols.mainbar,
					amount = mb.renderer.calcAmountOfButtons();
				if(il.UI.page.isSmallScreen()) {
					mb.model.actions.disengageAll();
				}
				mb.model.actions.initMoreButton(amount);
				mb.renderer.render(mb.model.getState());
			},
			init_desktop = function(initially_active) {
				var mb = il.UI.maincontrols.mainbar,
					cookie_state = mb.persistence.read(),
					init_state = mb.model.getState();
				/**
				 * apply cookie-state;
				 * tools appear and disappear by context and
				 * global screen modifications - take them from there,
				 * but apply engaged states
				 */
				if(Object.keys(cookie_state).length > 0) {
					//re-apply engaged
					for(var idx in init_state.tools) {
						id = init_state.tools[idx].id;
						gs_id = helper.getMappingIdForPosId(id);

						if(cookie_state.known_tools.indexOf(gs_id) === -1) {
							cookie_state.known_tools.push(gs_id);
							init_state.tools[idx].engaged = true //new tool is active
						} else {
							if(cookie_state.tools[idx]) {
								init_state.tools[idx].engaged = cookie_state.tools[idx].engaged;
							}
						}
					}

					cookie_state.tools = init_state.tools;
					mb.model.setState(cookie_state);
				}

				init_state = mb.model.getState();
				first_tool_id = helper.getFirstEngagedToolId(init_state.tools);
				/**
				 * initially active (from mainbar-component) will override everything (but tools)
				 */
				if(initially_active) {
					if(initially_active === '_none') {
						mb.model.actions.disengageAll();
					} else if(init_state.entries[mappings[initially_active]]) {
						mb.model.actions.engageEntry(mappings[initially_active]);
					} else if(init_state.tools[mappings[initially_active]]) {
						mb.model.actions.engageTool(mappings[initially_active]);
					}
				}

				/**
				 * Override potentially active entry, if there are is an active tool.
				 */
				if(first_tool_id) {
					mb.model.actions.engageTool(first_tool_id);
				} else {
					//tools engaged, but none active: take the first one:
					if(mb.model.getState().tools_engaged) {
						//are there any tools?
						if(Object.keys(mb.model.getState().tools).length === 0) {
							mb.model.actions.disengageTools();
							mb.model.actions.disengageAll();
						} else {
							mb.model.actions.engageTool(Object.keys(init_state.tools).shift());
						}
					}
				}

				mb.model.actions.initMoreButton(mb.renderer.calcAmountOfButtons());
				mb.renderer.render(mb.model.getState());
			},
			init_mobile = function() {
				var mb = il.UI.maincontrols.mainbar;
				mb.model.actions.disengageAll();
				mb.model.actions.initMoreButton(mb.renderer.calcAmountOfButtons());
				mb.renderer.render(mb.model.getState());
			},
			init = function(initially_active) {
				if(il.UI.page.isSmallScreen()) {
					init_mobile();
				} else {
					init_desktop(initially_active);
				}
			},

			public_interface = {
				addToolEntry: construction.addToolEntry,
				addPartIdAndEntry: construction.addPartIdAndEntry,
				addMapping: construction.addMapping,
				addTriggerSignal: construction.addTriggerSignal,
				adjustToScreenSize: adjustToScreenSize,
				init: init,
				engageTool: external_commands.engageTool,
				disengageAll: external_commands.disengageAll
			};

		return public_interface;
	})($);
})($, il.UI.maincontrols);


/**
 * The Mainbar holds a collection of entries that each consist of some triggerer
 * and an according slate; in case of Tools, these entries might be hidden at first
 * or may be removed by the users.
 * There is a redux-like model of the moving parts of the mainbar: All entries and tools
 * are stored in a state.
 * Whenever something changes, i.e. the engagement and thus visibility of elements
 * should change, these changes are applied to the model first, so that calculations
 * of dependencies can be done _before_ rendering.
  */
(function($, mainbar) {
	mainbar.model = (function($) {
		var state,
			classes = {
				bar: {
					any_entry_engaged : false,
					tools_engaged: false,
					more_available: false,
					any_tools_visible: function() {
						for(idx in this.tools) {
							if(!this.tools[idx].hidden) {
								return true;
							}
						}
						return false;
					},
					any_tools_engaged: function() {
						for(idx in this.tools) {
							if(!this.tools[idx].engaged) {
								return true;
							}
						}
						return false;
					},

					entries: {},
					tools: {}, //"moving" parts, current tools
					known_tools: [] //gs-ids; a tool is "new", if not listed here
				},
				entry: {
					id: null,
					removeable: false,
					engaged: false,
					hidden: false,
					isTopLevel: function() {return this.id.split(':').length === 2;}
				}
			},
			factories = {
				entry: (id) => factories.cloned(classes.entry, {id: id}),
				cloned: (state, params) => Object.assign({}, state, params),
				state: function(nu_state) {
					var tmp_state = factories.cloned(state, nu_state);
					for(idx in tmp_state.entries) {
						tmp_state.entries[idx].isTopLevel = classes.entry.isTopLevel;
					}
					for(idx in tmp_state.tools) {
						tmp_state.tools[idx].isTopLevel = classes.entry.isTopLevel;
					}
					state = tmp_state;
				}
			},
			reducers = {
				entry: {
					engage: (entry) => {
						entry.engaged = true;
						entry.hidden = false;
						return entry;
					},
					disengage: (entry) => {entry.engaged = false; return entry;},
					mb_show: (entry) => {entry.hidden = false; return entry;},
					mb_hide: (entry) => {
						entry.hidden = true;
						entry.engaged = false;
						return entry;
					}
				},
				bar:  {
					engageTools: (bar) => {bar.tools_engaged = true; return bar;},
					disengageTools: (bar) => {bar.tools_engaged = false; return bar;},
					anySlates: (bar) => {bar.any_entry_engaged = true; return bar;},
					noSlates: (bar) => {bar.any_entry_engaged = false; return bar;},
					withMoreButton: (bar) => {bar.more_available = true; return bar;},
					withoutMoreButton: (bar) => {bar.more_available = false; return bar;}
				},
				entries: {
					disengageTopLevel: function(entries) {
						for(id in entries) {
							if(entries[id].isTopLevel()) {
								entries[id] = reducers.entry.disengage(entries[id]);
							}
						}
						return entries;
					},
					engageEntryPath: function(entries, entry_id) {
						var hops = entry_id.split(':');
						hops.map(function(v, idx, hops) {
							var id = hops.slice(0, idx+1).join(':');
							if(id && id != '0') {
								entries[id] = reducers.entry.engage(entries[id]);
							}
						});
						return entries;
					}
				}
			},
			helpers = {
				getTopLevelEntries: function() {
					var ret = [];
					for(id in state.entries) {
						if(state.entries[id].isTopLevel()) {
							ret.push(state.entries[id]);
						}
					}
					return ret;
				}
			},
			actions = {
				addEntry: function (entry_id) {
					state.entries[entry_id] = factories.entry(entry_id);
				},
				addTool: function (entry_id, removeable, hidden) {
					var tool = factories.entry(entry_id);
					tool.removeable = removeable ? true : false;
					tool.hidden = hidden ? true : false;
					state.tools[entry_id] = tool;
				},
				engageEntry: function (entry_id) {
					state.tools = reducers.entries.disengageTopLevel(state.tools);
					state.entries = reducers.entries.disengageTopLevel(state.entries);
					state.entries = reducers.entries.engageEntryPath(state.entries, entry_id);
					state = reducers.bar.disengageTools(state);
					state = reducers.bar.anySlates(state);

				},
				disengageEntry: function (entry_id) {
					state.entries[entry_id] = reducers.entry.disengage(state.entries[entry_id]);
					if(state.entries[entry_id].isTopLevel()) {
						state = reducers.bar.noSlates(state);
					}
				},
				hideEntry: function (entry_id) {
					state.entries[entry_id] = reducers.entry.mb_hide(state.entries[entry_id]);
				},
				showEntry: function (entry_id) {
					state.entries[entry_id] = reducers.entry.mb_show(state.entries[entry_id]);
				},
				engageTool: function (entry_id) {
					state.entries = reducers.entries.disengageTopLevel(state.entries);
					state.tools = reducers.entries.disengageTopLevel(state.tools);
					state.tools[entry_id] = reducers.entry.engage(state.tools[entry_id]);
					state = reducers.bar.engageTools(state);
					state = reducers.bar.anySlates(state);
				},
				engageTools: function() {
					state = reducers.bar.engageTools(state);
				},
				disengageTools: function() {
					state = reducers.bar.disengageTools(state)
				},
				removeTool: function (entry_id) {
					state.tools[entry_id] = reducers.entry.mb_hide(state.tools[entry_id]);
					for(idx in state.tools) {
						tool = state.tools[idx];
						if(!tool.hidden) {
							state.tools[tool.id] = reducers.entry.engage(tool);
							break;
						}
					}
					if(!state.any_tools_visible()) {
						actions.disengageAll();
					}
				},
				toggleTools: function() {
					if(state.tools_engaged) {
						actions.disengageAll();
					} else {
						for(idx in state.tools) {
							var tool = state.tools[idx];
							if(tool.engaged) {
								actions.engageTool(tool.id);
								return;
							}
						}
						var tool_id = Object.keys(state.tools)[0];
						actions.engageTool(tool_id);
					}
				},
				disengageAll: function () {
					state.entries = reducers.entries.disengageTopLevel(state.entries)
					state.tools = reducers.entries.disengageTopLevel(state.tools)
					state = reducers.bar.noSlates(state);
					state = reducers.bar.disengageTools(state);
				},
				initMoreButton: function(max_buttons) {
					var entry_ids = Object.keys(state.entries),
						last_entry_id = entry_ids[entry_ids.length - 1],
						more = state.entries[last_entry_id];

					if(state.any_tools_visible()) {
						max_buttons--
					};

					//get length of top-level entries (w/o) more-button
					amount_toplevel = helpers.getTopLevelEntries().length - 1;

					if(amount_toplevel > max_buttons) {
						state.entries[more.id] = reducers.entry.mb_show(more);
						state = reducers.bar.withMoreButton(state);
					} else {
						state.entries[more.id] = reducers.entry.mb_hide(more);
						state = reducers.bar.withoutMoreButton(state);
					}
				}
			},
			public_interface = {
				actions: actions,
				getState: () => factories.cloned(state),
				setState: factories.state,
				getTopLevelEntries: helpers.getTopLevelEntries
			},
			init = function() {
				state = factories.cloned(classes.bar);
			};

		init();
		return public_interface;
	})($);
})($, il.UI.maincontrols.mainbar);


(function($, mainbar) {
	mainbar.persistence = (function($) {
		var cs,
			storage = function() {
				if(cs) { return cs; }
				cookie_name = hash(entry_ids());
				return new il.Utilities.CookieStorage(cookie_name);
			},
			model_state = function() {
				return il.UI.maincontrols.mainbar.model.getState();
			},
			entry_ids = function() {
				var entries = model_state().entries,
					base = '';
				for(idx in entries) {
					base = base + idx;
				}
				return base;
			},
			hash = function(str) {
				var hash = 0,
					len = str.length,
					i, chr;

				for (i = 0; i < len; i = i + 1) {
					chr = str.charCodeAt(i);
					hash  = ((hash << 5) - hash) + chr;
					hash |= 0; // Convert to 32bit integer
				}
				return hash;
			},
			storeStates = function(state) {
				cs = storage();
				for(idx in state) {
					cs.add(idx, state[idx]);
				}
				cs.store();
				storePageState(state.any_entry_engaged || state.tools_engaged);
			},
			readStates = function() {
				cs = storage();
				return cs.items;
			},
			/**
			 * The information wether slates are engaged or not is shared
			 * with the page's renderer, so the space can be reserverd very early.
			 */
			storePageState = function(engaged) {
				var shared = new il.Utilities.CookieStorage('il_mb_slates');
				shared.add('engaged', engaged);
				shared.store();
			},

			public_interface = {
				read: readStates,
				store: storeStates
			};

		return public_interface;
	})($);
})($, il.UI.maincontrols.mainbar);



(function($, mainbar) {
	mainbar.renderer = (function($) {
		var css = {
				engaged: 'engaged'
				,disengaged: 'disengaged'
				,hidden: 'hidden'
				,page_div: 'il-layout-page'
				,page_has_engaged_slated: 'with-mainbar-slates-engaged'
				,tools_btn: 'il-mainbar-tools-button'
				,toolentries_wrapper: 'il-mainbar-tools-entries'
				,remover_class: 'il-mainbar-remove-tool'
				,mainbar: 'il-mainbar'
				,mainbar_buttons: '.il-mainbar .il-mainbar-entries .btn-bulky'
				,mainbar_entries: 'il-mainbar-entries'
			},

			dom_references = {},
			dom_element = {
				withHtmlId: function (html_id) {
					return Object.assign({}, this, {html_id: html_id});
				},
				getElement: function(){
					return $('#' + this.html_id);
				},
				engage: function() {
					this.getElement().addClass(css.engaged);
					this.getElement().removeClass(css.disengaged);
					this.getElement().trigger('in_view'); //this is most important for async loading of slates,
														  //it triggers the GlobalScreen-Service.
					if(il.UI.page.isSmallScreen() && il.UI.maincontrols.metabar) {
						il.UI.maincontrols.metabar.disengageAll();
					}
				},
				disengage: function() {
					this.getElement().addClass(css.disengaged);
					this.getElement().removeClass(css.engaged);
				},
				mb_hide: function(on_parent) {
					var element = this.getElement();
					if(on_parent) {
						element = element.parent();
					}
					element.addClass(css.hidden);
				},
				mb_show: function(on_parent) {
					var element = this.getElement();
					if(on_parent) {
						element = element.parent();
					}
					element.removeClass(css.hidden);
				}
			},
			parts = {
				triggerer: Object.assign({}, dom_element, {
					remove: function() {}
				}),
				slate: Object.assign({}, dom_element, {
					remove: null,
					mb_hide: null,
					mb_show: null
				}),
				remover: Object.assign({}, dom_element, {
					engage: null,
					disengage:null,
					mb_show: function(){this.getElement().parent().show();}
				}),
				page: {
					getElement: function(){
						return $('.' + css.page_div);
					},
					slatesEngaged: function(engaged) {
						if(engaged) {
							this.getElement().addClass(css.page_has_engaged_slated);
						} else {
							this.getElement().removeClass(css.page_has_engaged_slated);
						}
					}
				},
				removers: {
					getElement: function(){
						return $('.' + css.remover_class);
					},
					mb_hide: function() {
						this.getElement().hide();
					}

				},
				tools_area: Object.assign({}, dom_element, {
					getElement: function(){
						return $(' .' + css.toolentries_wrapper);
					}
				}),
				tools_button: Object.assign({}, dom_element, {
					getElement: function(){
						return $('.' + css.tools_btn + ' .btn');
					},
					remove: null
				}),
				mainbar: {
					getElement: function(){
						return $('.' + css.mainbar);
					},
					getOffsetTop: function() {
						return this.getElement().offset().top;
					}
				}
			},

			//more-slate
			more = {
				calcAmountOfButtons: function() {
					var window_height = $(window).height(),
						window_width = $(window).width(),
						horizontal = il.UI.page.isSmallScreen(),
						btn = $(css.mainbar_buttons).first()
						btn_height = btn.height(),
						btn_width = btn.width(),
						amount_buttons = Math.floor(
							(window_height - parts.mainbar.getOffsetTop()) / btn_height
						);

					if(horizontal) {
						amount_buttons = Math.floor(window_width / btn_width);
					}
					return amount_buttons - 1;
				}
			},

			actions = {
				addEntry: function (entry_id, part, html_id) {
					dom_references[entry_id] = dom_references[entry_id] || {};
					dom_references[entry_id][part] = html_id;
				},
				renderEntry: function (entry, is_tool) {
					if(!dom_references[entry.id]){
						return;
					}

					var triggerer = parts.triggerer.withHtmlId(dom_references[entry.id].triggerer),
						slate = parts.slate.withHtmlId(dom_references[entry.id].slate);

					if(entry.hidden) {
						triggerer.mb_hide(is_tool);
					} else {
						triggerer.mb_show(is_tool);
					}

					if(entry.engaged) {
						triggerer.engage();
						slate.engage();
						if(entry.removeable) {
							remover = parts.remover.withHtmlId(dom_references[entry.id].remover);
							remover.mb_show(true);
						}
					} else {
						triggerer.disengage();
						slate.disengage();
					}
				},

				moveToplevelTriggerersToMore: function (model_state) {
					var entry_ids = Object.keys(model_state.entries),
						last_entry_id = entry_ids[entry_ids.length - 1],
						more_entry = model_state.entries[last_entry_id],
						more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate),
						root_entries = il.UI.maincontrols.mainbar.model.getTopLevelEntries(),
						root_entries_length = root_entries.length - 1,
						max_buttons = more.calcAmountOfButtons() - 1; //room for the more-button

					if(model_state.any_tools_visible()) { max_buttons--};

					for(i = max_buttons; i < root_entries_length; i++) {
						btn = parts.triggerer.withHtmlId(dom_references[root_entries[i].id].triggerer);
						btn.getElement().appendTo(more_slate.getElement().children('.il-maincontrols-slate-content'));
					}
				},
				render: function (model_state) {
					var entry_ids = Object.keys(model_state.entries),
						last_entry_id = entry_ids[entry_ids.length - 1],
						more_entry = model_state.entries[last_entry_id],
						more_button = parts.triggerer.withHtmlId(dom_references[more_entry.id].triggerer),
						more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate);
						//reset
						more_slate.getElement().find('.btn-bulky').insertBefore(more_button.getElement());

					if(model_state.more_available) {
						actions.moveToplevelTriggerersToMore(model_state);
					}

					parts.page.slatesEngaged(model_state.any_entry_engaged || model_state.tools_engaged);

					if(model_state.any_tools_visible()) {
						parts.tools_button.mb_show();
					} else {
						parts.tools_button.mb_hide();
					}

					if(model_state.tools_engaged){
						parts.tools_button.engage();
						parts.tools_area.engage();
					} else {
						parts.tools_button.disengage();
						parts.tools_area.disengage();
					}

					for(idx in model_state.entries) {
						actions.renderEntry(model_state.entries[idx], false);
					}
					for(idx in model_state.tools) {
						actions.renderEntry(model_state.tools[idx], true);
					}
					//unfortunately, this does not work properly via a class
					$('.' + css.mainbar_entries).css('visibility', 'visible');
				}
			},
			public_interface = {
				addEntry: actions.addEntry,
				calcAmountOfButtons: more.calcAmountOfButtons,
				render: actions.render
			};

		return public_interface;
	})($);
})($, il.UI.maincontrols.mainbar);
