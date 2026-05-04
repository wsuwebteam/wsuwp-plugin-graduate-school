(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
			return;
		}
		document.addEventListener('DOMContentLoaded', fn);
	}

	function setupImportSourceToggles() {
		var radios = document.querySelectorAll('input[name="import_source"]');
		if (!radios.length) {
			return;
		}

		function showSource(value) {
			document.querySelectorAll('.gs-import-source').forEach(function (el) {
				el.classList.remove('is-active');
			});
			var target = document.querySelector('.gs-source-' + value);
			if (target) {
				target.classList.add('is-active');
			}
		}

		radios.forEach(function (radio) {
			radio.addEventListener('change', function () {
				showSource(radio.value);
			});
			if (radio.checked) {
				showSource(radio.value);
			}
		});
	}

	function setupExportControls() {
		var format = document.getElementById('gs-export-format');
		var delimiterWrap = document.getElementById('gs-export-delimiter-wrap');
		var tableSelect = document.getElementById('gs-export-table-select');
		var zip = document.getElementById('gs-export-zip');
		var selectAll = document.getElementById('gs-export-select-all');
		if (!format || !tableSelect || !zip) {
			return;
		}

		function syncFormat() {
			if (delimiterWrap) {
				delimiterWrap.style.display = format.value === 'csv' ? '' : 'none';
			}
		}

		function syncZip() {
			var selectedCount = Array.from(tableSelect.options).filter(function (opt) {
				return opt.selected;
			}).length;
			if (selectedCount > 1) {
				zip.checked = true;
				zip.disabled = true;
				return;
			}
			zip.disabled = false;
		}

		format.addEventListener('change', syncFormat);
		tableSelect.addEventListener('change', syncZip);
		if (selectAll) {
			selectAll.addEventListener('change', function () {
				Array.from(tableSelect.options).forEach(function (opt) {
					opt.selected = selectAll.checked;
				});
				syncZip();
			});
		}
		syncFormat();
		syncZip();
	}

	function setupGridSelection() {
		var table = document.querySelector('.gs-grid-table[data-gs-grid="1"]');
		var hiddenWrap = document.getElementById('gs-grid-hidden-inputs');
		var form = document.getElementById('gs-table-editor-form');
		if (!table || !hiddenWrap || !form) {
			return;
		}

		var selectedCell = null;
		table.addEventListener('click', function (event) {
			var cell = event.target.closest('td[contenteditable="true"]');
			if (!cell) {
				return;
			}
			if (selectedCell) {
				selectedCell.classList.remove('is-selected');
			}
			selectedCell = cell;
			selectedCell.classList.add('is-selected');
		});

		table.addEventListener('paste', function (event) {
			if (!selectedCell) {
				return;
			}
			event.preventDefault();
			var text = (event.clipboardData || window.clipboardData).getData('text');
			document.execCommand('insertText', false, text);
		});

		function syncHiddenInputs() {
			var cells = table.querySelectorAll('td[contenteditable="true"]');
			cells.forEach(function (cell) {
				var row = cell.getAttribute('data-row');
				var col = cell.getAttribute('data-col');
				var selector = 'input[name="gs_table_rows[' + row + '][' + col + '][text]"]';
				var input = hiddenWrap.querySelector(selector);
				if (input) {
					input.value = cell.textContent.trim();
				}
			});
		}

		form.addEventListener('submit', syncHiddenInputs);

		document.querySelectorAll('.gs-action').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!selectedCell) {
					return;
				}
				var action = btn.getAttribute('data-gs-action');
				if (action === 'insert-link') {
					var url = window.prompt('Link URL');
					if (url) {
						selectedCell.textContent = url;
					}
				}
				if (action === 'insert-image') {
					var imgUrl = window.prompt('Image URL');
					if (imgUrl) {
						selectedCell.textContent = imgUrl;
					}
				}
				if (action === 'advanced-editor') {
					var html = window.prompt('Cell HTML', selectedCell.innerHTML);
					if (html !== null) {
						selectedCell.innerHTML = html;
					}
				}
				if (action === 'merge-cells') {
					selectedCell.setAttribute('colspan', '2');
				}
			});
		});
	}

	ready(function () {
		setupImportSourceToggles();
		setupExportControls();
		setupGridSelection();
	});
})();
