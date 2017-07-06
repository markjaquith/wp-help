jQuery ($) ->
	load = undefined
	do ->
		whens = []
		deferred = undefined
		timeout = undefined
		load =
			start: (sensitivity=0) ->
				# Create the timer deferred: the read/write timer state.
				timer = $.Deferred()

				# Show the spinner.
				data.loading.show()

				# Start the timer.
				setTimeout (-> timer.resolve()), sensitivity

				# Return the promise: the read-only timer state.
				timer.promise()

			stop: ->
				deferred.reject() if deferred
				deferred = null
				whens = []
				data.loading.hide()

			###
			Show the loading spinner until all the promises succeed.
			Optionally show the spinner for a minimum amount of time.

			until( [sensitivity], promises* );
			@param  {number} sensitivity The number of milliseconds to show the spinner, at minimum.
			@param  {object} promises*   Any number of jQuery promises.
			@return {object}             A jQuery promise representing the state of all input promises.
			###
			until: (sensitivity = 0, promises...) ->
				deferred = deferred or $.Deferred().always api.load.stop
				index = whens.push(false) - 1

				# Add the start promise onto the the stack.
				promises.push api.load.start(sensitivity)

				# When all the promises are complete (including the timer), hide the timer.
				# $.when returns a promise object.
				$.when.apply($, promises).always ->
					whens[index] = true

					# Are there any pending requests?
					deferred.resolve()  if -1 is $.inArray false, whens

				deferred.promise()
	api =
		p: (i) -> $ "#cws-wp-help-#{i}"

		load: load
		bindH2Updates: ->

			# Refresh this in case we just moved the menu
			data.menu = $("#adminmenu a.current .wp-menu-name") # WordPress 3.5+
			# WordPress 3.4.x and lower
			data.menu = $("#adminmenu a.current")  unless data.menu.length
			data.menu.text data.h2.edit.input.val()

			# Send h2 updates to the menu item as we type
			data.h2.edit.input.bind "keyup", ->
				data.menu.text $(this).val()

		sortable: ->
			$(this).sortable
				opacity: 0.8
				placeholder: "cws-wp-help-placeholder"
				axis: "y"
				cursor: "move"
				cursorAt:
					left: 0
					top: 0

				distance: 10
				delay: 50
				handle: ".sort-handle"
				items: "> li.cws-wp-help-local, > div#cws-wp-help-remote-docs-block"
				start: (e, ui) ->
					item = $(ui.item)
					placeholder = $(".cws-wp-help-placeholder")
					offset = undefined
					if item.attr("id") is "cws-wp-help-remote-docs-block"
						offset = 4
					else
						offset = -2
					placeholder.height item.height() + offset

				update: (e, ui) ->
					request = $.post(ajaxurl,
						action: "cws_wp_help_reorder"
						_ajax_nonce: data.ul.data("nonce")
						order: $(this).sortable("toArray")
					)
					api.load.until 200, request

			$(this).find("> li:not(.cws-wp-help-is-slurped) > ul > li:nth-child(2)").parent("ul").each api.sortable

		sortableInit: ->
			# Wrap remote docs
			data.ul.find("> #cws-wp-help-remote-docs-block > li").unwrap()
			data.ul.find("> li.cws-wp-help-is-slurped:first").before "<div id=\"cws-wp-help-remote-docs-block\"></div>"
			data.ul.find("> li.cws-wp-help-is-slurped").detach().appendTo "#cws-wp-help-remote-docs-block"

			# Sortable
			data.ulSortable.each api.sortable

		init: ->
			# Small CSS Tweaks for Firefox
			# body = $ 'body'
			# if $.browser.mozilla
			# 	data.h2.edit.input.css
			# 		top: '-5px'
			# 		left: '-7px'
			# 		'margin-bottom': '-3px'
			# 	data.h3.edit.input.css
			# 		'margin-top': '1px'
			# 		'margin-bottom': '-5px'
			# 		left: '-8px'

			# Sortable
			api.sortableInit()

			# Add IDs to the list
			data.ul.find("li.page_item").each ->
				$(@).attr "id", "page-#{$(@).attr("class").match(/page-item-([0-9]+)/)[1]}"

			# Clicking the source API URI
			data.apiURL.click -> @select()

			# Clicking the "Save Changes" button
			data.saveButton.click -> api.saveSettings()

			# Clicking the "Cancel" button (settings)
			data.cancelLink.click (e) ->
				e.preventDefault()
				api.restoreSettings()
				api.hideSettings()

			# Clicking the "Settings" button
			data.settingsButton.click (e) ->
				e.preventDefault()
				api.revealSettings true # true = autofocus on the h2 with no highlighting

			# Doubleclick the h2
			data.h2.display.text.dblclick ->
				api.revealSettings()
				data.h2.edit.input.focus().select()

			# Doubleclick the h3
			data.h3.display.text.dblclick ->
				api.revealSettings()
				data.h3.edit.input.focus().select()

			# Monitor for "return" presses in our text inputs
			data.returnMonitor.bind "keydown", (e) ->
				if 13 is e.which
					$(this).blur()
					api.saveSettings()

			api.bindH2Updates()

			# Preview menu placement "live"
			data.menuLocation.change ->
				newLocation = String(window.location)
				if data.menuLocation.val().indexOf("submenu") is -1
					newLocation = newLocation.replace("/index.php", "/admin.php")
				else
					newLocation = newLocation.replace("/admin.php", "/index.php")
				newLocationPreview = "#{String(newLocation)}&wp-help-preview-menu-location=" + data.menuLocation.val()
				commonScript = String(newLocation).replace(/\/wp-admin\/.*$/, "/wp-admin/js/common.js")
				$("#adminmenu").load newLocationPreview + " #adminmenu", ->
					window.history.replaceState null, null, newLocation  if window.history.replaceState
					$.getScript commonScript # Makes the menu work again
					api.bindH2Updates() # Makes live H2 previewing work again

		fadeOutIn: (first, second) ->
			first.fadeOut 150, -> second.fadeIn 150

		hideShow: (hide, show) ->
			hide.hide()
			show.show()

		revealSettings: (autofocus) ->
			api.hideShow item.display.wrap, item.edit.wrap for item in [data.h2, data.h3]

			data.actions.fadeTo 200, 0.3
			data.ul.fadeTo 200, 0.3
			api.fadeOutIn data.doc, data.settings
			if autofocus
				data.h2.edit.input.focus().select()
				###
				((h2) ->
					h2.focus().select()
				) data.h2.edit.input
				###

		restoreSettings: ->
			$("input, select", data.settings).each ->
				i = $(@)
				i.val(i.data("original-value")).change() if i.data "original-value"

		saveSettings: ->
			api.clearError()
			$([data.h2, data.h3]).each ->
				@display.text.text @edit.input.val()

			request = $.post ajaxurl,
				action: "cws_wp_help_settings"
				_ajax_nonce: $("#_cws_wp_help_nonce").val()
				h2: data.h2.edit.input.val()
				h3: data.h3.edit.input.val()
				menu_location: data.menuLocation.val()
				slurp_url: data.slurp.val()
			request.success (result) ->
				result = $.parseJSON(result)
				data.slurp.val result.slurp_url
				if result.error
					api.error result.error
					data.slurp.focus()
				else
					api.hideSettings()
				if result.topics
					api.p("nodocs").remove()
					data.ul.html result.topics
					api.sortableInit()
			api.load.until 200, request

		hideSettings: ->
			api.hideShow item.edit.wrap, item.display.wrap for item in [data.h2, data.h3]
			data.actions.fadeTo 200, 1
			data.ul.fadeTo 200, 1
			api.fadeOutIn data.settings, data.doc

		clearError: -> data.slurpError.html("").hide()

		error: (msg) -> data.slurpError.html("<p>" + msg + "</p>").fadeIn 150

	data =
		menu: -> $ "#adminmenu a.current"
		h2:
			edit:
				input: api.p "h2-label"
				wrap: api.p "h2-label-wrap"
			display:
				text: $ ".wrap h1:first"
				wrap: $ ".wrap h1:first"
		h3:
			edit:
				input: api.p "listing-label"
				wrap: api.p "listing-labels"
			display:
				text: api.p "listing h3"
				wrap: api.p "listing h3"
		settingsButton: api.p "settings-on"
		doc:            api.p "document"
		ul:             api.p "listing-wrap > ul"
		ulSortable:     api.p "listing-wrap > ul.can-sort"
		actions:        api.p "actions"
		settings:       api.p "settings"
		listing:        api.p "listing"
		apiURL:         api.p "api-url"
		slurp:          api.p "slurp-url"
		slurpError:     api.p "slurp-error"
		saveButton:     api.p "settings-save"
		cancelLink:     api.p "settings-cancel"
		menuLocation:   api.p "menu-location"
		loading:        api.p "loading"
		returnMonitor:  $ ".wrap input[type=\"text\"]"

	# Bootstrap everything
	api.init()
