(function ($) {
	'use strict';

	$(function () {
		var $wrap        = $('#gsdp-eam-wrap');
		var $modeInput   = $('#gsdp_team_access_mode');
		var $modeButtons = $wrap.find('.gsdp-eam-mode-btn');
		var $panels      = $('#gsdp-eam-panels');
		var $tabs        = $('#gsdp-eam-tabs');
		var $rolesSelect = $('#gsdp_team_roles');
		var $usersSelect = $('#gsdp_team_members');
		var $statusWrap  = $wrap.find('.gsdp-eam-status');
		var $rolesCount  = $('#gsdp-eam-roles-count');
		var $usersCount  = $('#gsdp-eam-users-count');

		if (!$rolesSelect.length || !$usersSelect.length) {
			return;
		}

		// ---------------------------------------------------------------
		// Custom multi-select widget (replaces Chosen)
		// ---------------------------------------------------------------

	function buildMultiSelect($select, opts) {
		var placeholder = opts.placeholder || 'Select...';
		var noResultsText = opts.noResultsText || 'No results found';

		$select.hide();

		var $container = $('<div class="gsdp-multiselect">');
		var $inner = $('<div class="gsdp-multiselect-inner">');
		var $chips = $('<div class="gsdp-multiselect-chips">');
		var $searchWrap = $('<div class="gsdp-multiselect-search-wrap">');
		var $search = $('<input type="text" class="gsdp-multiselect-search" placeholder="' + placeholder + '" autocomplete="off">');
		var $dropdown = $('<div class="gsdp-multiselect-dropdown">');
		var $list = $('<ul class="gsdp-multiselect-results">');
		var $noResults = $('<li class="gsdp-multiselect-no-results">' + noResultsText + '</li>');

		$searchWrap.append($search);
		$inner.append($chips).append($searchWrap);
		$dropdown.append($list);
		$container.append($inner).append($dropdown);
		$select.after($container);

		function getOptions() {
			var items = [];
			$select.find('option').each(function () {
				var $opt = $(this);
				items.push({
					value: $opt.val(),
					text: $opt.text(),
					selected: $opt.prop('selected'),
					disabled: $opt.prop('disabled')
				});
			});
			return items;
		}

		function isSelected(value) {
			value = String(value);
			return $select.find('option[value="' + value.replace(/"/g, '\\"') + '"]').prop('selected');
		}

		function setSelected(value, selected) {
			value = String(value);
			var $opt = $select.find('option[value="' + value.replace(/"/g, '\\"') + '"]');
			if ($opt.length && !$opt.prop('disabled')) {
				$opt.prop('selected', !!selected);
			}
		}

		function renderChips() {
			var html = '';
			$select.find('option:selected').each(function () {
				var $opt = $(this);
				var v = $opt.val();
				var text = $opt.text();
				var disabled = $opt.prop('disabled');
				html += '<span class="gsdp-multiselect-chip' + (disabled ? ' is-disabled' : '') + '" data-value="' + $('<div>').text(v).html() + '">';
				html += '<span class="gsdp-multiselect-chip-text">' + $('<div>').text(text).html() + '</span>';
				if (!disabled) {
					html += '<button type="button" class="gsdp-multiselect-chip-remove" aria-label="Remove">&times;</button>';
				}
				html += '</span>';
			});
			$chips.html(html);
		}

		function filterList(searchVal) {
			searchVal = (searchVal || '').toLowerCase();
			var visible = 0;
			$list.find('li[data-value]').each(function () {
				var $li = $(this);
				var text = $li.data('text') || '';
				var show = !searchVal || text.toLowerCase().indexOf(searchVal) !== -1;
				$li.toggle(show);
				if (show) visible++;
			});
			$list.find('.gsdp-multiselect-no-results').toggle(visible === 0);
		}

		function renderList() {
			$list.empty();
			var options = getOptions();
			options.forEach(function (o) {
				var $li = $('<li>')
					.attr('data-value', o.value)
					.data('text', o.text)
					.data('disabled', o.disabled)
					.text(o.text)
					.toggleClass('is-selected', o.selected)
					.toggleClass('is-disabled', o.disabled);
				if (!o.disabled) {
					$li.attr('tabindex', 0).css('cursor', 'pointer');
				}
				$list.append($li);
			});
			$list.append($noResults);
			$noResults.hide();
			filterList($search.val());
		}
		// Remove loader that was shown until widget was built
		$select.prev('.gsdp-multiselect-loader').remove();

		function openDropdown() {
			$container.addClass('is-open');
				$dropdown.show();
			$search.focus();
			filterList($search.val());
		}

		function closeDropdown() {
			$container.removeClass('is-open');
			$dropdown.hide();
			$search.val('');
			filterList('');
		}

		function toggleOption(value) {
			value = String(value);
			var $opt = $select.find('option[value="' + value.replace(/"/g, '\\"') + '"]');
			if ($opt.length && !$opt.prop('disabled')) {
				$opt.prop('selected', !$opt.prop('selected'));
				renderChips();
				renderList();
				$select.trigger('change');
			}
		}

		// Chips remove
		$container.on('click', '.gsdp-multiselect-chip-remove', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var val = $(this).closest('.gsdp-multiselect-chip').data('value');
			setSelected(String(val), false);
			renderChips();
			renderList();
			$select.trigger('change');
		});

		// List item click
		$list.on('click', 'li[data-value]', function (e) {
			e.preventDefault();
			var $li = $(this);
			if ($li.hasClass('is-disabled')) return;
			toggleOption($li.data('value'));
		});

		// Search
		$search.on('input', function () {
			filterList($(this).val());
		});

		$search.on('focus', function () {
			openDropdown();
		});

		$inner.on('click', function (e) {
			if (!$(e.target).closest('.gsdp-multiselect-chip-remove').length) {
				openDropdown();
			}
		});

		// Close on outside click
		$(document).on('click.gsdpMultiselect', function (e) {
			if (!$(e.target).closest($container).length) {
				closeDropdown();
			}
		});

		// Initial render
		renderChips();
		renderList();

		// Re-render when select is changed programmatically
		$select.on('change', function () {
			renderChips();
			renderList();
		});

		return {
			destroy: function () {
				$(document).off('click.gsdpMultiselect');
				$container.remove();
				$select.show();
			},
			refresh: function () {
				renderChips();
				renderList();
			}
		};
	}

	// Init custom multi-selects
	buildMultiSelect($rolesSelect, { placeholder: 'Select roles...', noResultsText: 'No roles found matching' });
	buildMultiSelect($usersSelect, { placeholder: 'Search and select users...', noResultsText: 'No users found matching' });

	// ---------------------------------------------------------------
	// Status badge
	// ---------------------------------------------------------------

	function updateStatus() {
		var mode = $modeInput.val();
		var html = '';

		if (mode === 'off') {
			html = '<span class="gsdp-eam-badge gsdp-eam-badge--off">' +
				'<span class="dashicons dashicons-unlock"></span> Open access</span>';
		} else if (mode === 'roles') {
			var rc = $rolesSelect.val() ? $rolesSelect.val().length : 0;
			html = '<span class="gsdp-eam-badge gsdp-eam-badge--active">' +
				'<span class="dashicons dashicons-groups"></span> ' +
				rc + (rc === 1 ? ' role' : ' roles') + ' assigned</span>';
		} else {
			var uc = $usersSelect.val() ? $usersSelect.val().length : 0;
			html = '<span class="gsdp-eam-badge gsdp-eam-badge--active">' +
				'<span class="dashicons dashicons-admin-users"></span> ' +
				uc + (uc === 1 ? ' user' : ' users') + ' assigned</span>';
		}

		$statusWrap.html(html);
	}

	function updateCounts() {
		var rc = $rolesSelect.val() ? $rolesSelect.val().length : 0;
		var uc = $usersSelect.val() ? $usersSelect.val().length : 0;
		$rolesCount.text(rc);
		$usersCount.text(uc);
	}

	$rolesSelect.on('change', function () { updateCounts(); updateStatus(); });
	$usersSelect.on('change', function () { updateCounts(); updateStatus(); });

	// ---------------------------------------------------------------
	// Tab switching
	// ---------------------------------------------------------------

	function activateTab(panelId) {
		$tabs.find('li').removeClass('active');
		$tabs.find('a[href="' + panelId + '"]').parent('li').addClass('active');

		$panels.find('.gsdp-eam-tab-panel').hide();
		$(panelId).show();
	}

	$tabs.on('click', 'a', function (e) {
		e.preventDefault();
		var panel = $(this).attr('href');
		activateTab(panel);

		var newMode = (panel === '#gsdp-team-panel-roles') ? 'roles' : 'users';
		$modeInput.val(newMode);
		$modeButtons.removeClass('active');
		$modeButtons.filter('[data-mode="' + newMode + '"]').addClass('active');
		updateStatus();
	});

	// ---------------------------------------------------------------
	// Segmented-control mode buttons
	// ---------------------------------------------------------------

	$modeButtons.on('click', function () {
		var mode = $(this).data('mode');
		$modeInput.val(mode);
		$modeButtons.removeClass('active');
		$(this).addClass('active');

		if (mode === 'off') {
			$panels.slideUp(150);
		} else {
			$panels.slideDown(150, function () {
				if (mode === 'roles') {
					activateTab('#gsdp-team-panel-roles');
				} else {
					activateTab('#gsdp-team-panel-users');
				}
			});
		}

		updateStatus();
	});

	// ---------------------------------------------------------------
	// Initial state
	// ---------------------------------------------------------------

		(function init() {
			var mode = $modeInput.val();

			if (mode === 'off') {
				$panels.hide();
			} else if (mode === 'roles') {
				activateTab('#gsdp-team-panel-roles');
			} else {
				activateTab('#gsdp-team-panel-users');
			}

			updateCounts();
		})();

		// Before submit: inject hidden inputs so the selection is reliably
		// included in $_POST even though the native <select> is hidden.
		$('#post').on('submit', function () {
			$('.gsdp-multiselect').each(function () {
				var $container = $(this);
				var $select = $container.prev('select');
				if (!$select.length) {
					return;
				}

				var fieldName = $select.attr('name');
				if (!fieldName) {
					return;
				}

				// Remove the name from the native select so it doesn't
				// double-submit alongside the hidden inputs.
				$select.removeAttr('name');

				// Collect selected (non-disabled) values from the select.
				$select.find('option:selected:not(:disabled)').each(function () {
					$('<input type="hidden">')
						.attr('name', fieldName)
						.val($(this).val())
						.appendTo($select.closest('form'));
				});
			});
		});
	});

})(jQuery);
