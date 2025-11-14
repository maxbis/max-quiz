document.addEventListener('DOMContentLoaded', () => {
    const exportButton = document.getElementById('export-btn');
    const tableBody = document.getElementById('student-table');
    const table = document.querySelector('table');
    const exportData = window.quizExport || {};

    if (!exportButton || !exportData) {
        return;
    }

    const { headers = [], rows = [] } = exportData;
    let sortState = { index: null, direction: 'asc' };

    const normalizeValue = (text) => {
        const trimmed = text.trim();
        if (!trimmed || trimmed === '–') {
            return null;
        }
        const numeric = Number(trimmed.replace('%', ''));
        return Number.isNaN(numeric) ? trimmed.toLowerCase() : numeric;
    };

    const applySort = (columnIndex) => {
        const direction =
            sortState.index === columnIndex && sortState.direction === 'asc'
                ? 'desc'
                : 'asc';
        sortState = { index: columnIndex, direction };

        const rowsArray = Array.from(tableBody.querySelectorAll('tr'));
        rowsArray.sort((rowA, rowB) => {
            const cellA = rowA.cells[columnIndex];
            const cellB = rowB.cells[columnIndex];
            const valueA = normalizeValue(cellA?.innerText ?? '');
            const valueB = normalizeValue(cellB?.innerText ?? '');

            if (valueA === valueB) {
                return 0;
            }
            if (valueA === null) {
                return direction === 'asc' ? 1 : -1;
            }
            if (valueB === null) {
                return direction === 'asc' ? -1 : 1;
            }
            if (typeof valueA === 'number' && typeof valueB === 'number') {
                return direction === 'asc' ? valueA - valueB : valueB - valueA;
            }
            return direction === 'asc'
                ? String(valueA).localeCompare(String(valueB))
                : String(valueB).localeCompare(String(valueA));
        });

        const fragment = document.createDocumentFragment();
        rowsArray.forEach((row) => fragment.appendChild(row));
        tableBody.appendChild(fragment);

        updateSortIndicators(columnIndex, direction);
    };

    const clearSortIndicators = () => {
        table.querySelectorAll('.sort-trigger[data-sort-index]').forEach((trigger) => {
            trigger.classList.remove('sort-asc', 'sort-desc');
        });
    };

    const updateSortIndicators = (columnIndex, direction) => {
        clearSortIndicators();
        const header = table.querySelector(`.sort-trigger[data-sort-index="${columnIndex}"]`);
        if (header) {
            header.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
        }
    };

    const attachSorting = () => {
        table.querySelectorAll('.sort-trigger[data-sort-index]').forEach((trigger) => {
            const columnIndex = Number(trigger.dataset.sortIndex);
            trigger.addEventListener('click', () => applySort(columnIndex));
        });
    };

    const handleExport = () => {
        if (!headers.length) {
            window.alert('There is no data to export yet.');
            return;
        }

        const input = window.prompt('Choose field separator (use , or ;):', ',');
        if (input === null) {
            return;
        }

        const separator = input.trim() === ';' ? ';' : ',';

        const escapeCell = (value) => {
            const cell = String(value ?? '');
            if (cell.includes('"') || cell.includes('\n') || cell.includes(separator)) {
                return `"${cell.replace(/"/g, '""')}"`;
            }
            return cell;
        };

        const lines = [
            headers.map(escapeCell).join(separator),
            ...rows.map((row) => row.map(escapeCell).join(separator)),
        ].join('\n');

        const blob = new Blob([lines], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        const timestamp = new Date().toISOString().split('T')[0];
        link.href = url;
        link.download = `quiz-overview-${timestamp}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    };

    if (exportButton) {
        exportButton.addEventListener('click', handleExport);
    }

    if (table && tableBody) {
        attachSorting();
    }
});
