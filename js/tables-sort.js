(function () {
	'use strict';

	function toComparable(value) {
		var trimmed = (value || '').trim();
		var number = Number(trimmed.replace(/,/g, ''));
		if (!Number.isNaN(number) && trimmed !== '') {
			return { type: 'number', value: number };
		}
		return { type: 'string', value: trimmed.toLowerCase() };
	}

	function sortTableByColumn(table, columnIndex, direction) {
		var tbody = table.tBodies[0];
		if (!tbody) {
			return;
		}

		var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
		rows.sort(function (a, b) {
			var aCell = a.children[columnIndex] ? a.children[columnIndex].textContent : '';
			var bCell = b.children[columnIndex] ? b.children[columnIndex].textContent : '';
			var aVal = toComparable(aCell);
			var bVal = toComparable(bCell);

			var comparison = 0;
			if (aVal.type === 'number' && bVal.type === 'number') {
				comparison = aVal.value - bVal.value;
			} else {
				comparison = aVal.value.localeCompare(bVal.value);
			}

			return direction === 'asc' ? comparison : -comparison;
		});

		rows.forEach(function (row) {
			tbody.appendChild(row);
		});
	}

	function initTable(table) {
		var sortableHeaders = table.querySelectorAll('th[data-gs-sort-col]');
		if (!sortableHeaders.length) {
			return;
		}

		sortableHeaders.forEach(function (header) {
			header.setAttribute('tabindex', '0');
			header.setAttribute('role', 'button');
			header.setAttribute('aria-sort', 'none');

			function triggerSort() {
				var current = header.getAttribute('data-sort-dir') || 'none';
				var next = current === 'asc' ? 'desc' : 'asc';
				var colIndex = Number(header.getAttribute('data-gs-sort-col'));
				sortTableByColumn(table, colIndex, next);

				sortableHeaders.forEach(function (otherHeader) {
					otherHeader.removeAttribute('data-sort-dir');
					otherHeader.setAttribute('aria-sort', 'none');
				});
				header.setAttribute('data-sort-dir', next);
				header.setAttribute('aria-sort', next === 'asc' ? 'ascending' : 'descending');
			}

			header.addEventListener('click', triggerSort);
			header.addEventListener('keydown', function (event) {
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					triggerSort();
				}
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var tables = document.querySelectorAll('table[data-gs-sortable="1"]');
		tables.forEach(initTable);
	});
})();
