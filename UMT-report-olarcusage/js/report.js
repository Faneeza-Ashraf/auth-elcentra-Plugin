// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course data source for the Custom Report plugin.
 *
 * @package    report_olarcusage
 * @copyright  2025 Faneeza Muskan <faneeza.muskan@paktaleem.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


(function() {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeReport();
    });

    /**
     * Initialize all report functionality
     */
    function initializeReport() {
        initializeTableSearch();
        initializeTableSorting();
        initializeTableEnhancements();
        updateResultsInfo();
    }

    /**
     * Initialize table search functionality
     */
    function initializeTableSearch() {
        const searchInput = document.getElementById('table-search');
        const table = document.getElementById('olarcusage-table');

        if (!searchInput || !table) {
            return;
        }

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;

            rows.forEach(function(row) {
                const cells = row.querySelectorAll('td');
                let rowText = '';

                cells.forEach(function(cell) {
                    rowText += cell.textContent.toLowerCase() + ' ';
                });

                if (searchTerm === '' || rowText.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                    highlightSearchTerm(row, searchTerm);
                } else {
                    row.style.display = 'none';
                }
            });

            updateResultsInfo(visibleCount, rows.length);
        });
    }

    /**
     * Highlight search terms in table cells
     */
    function highlightSearchTerm(row, searchTerm) {
        const cells = row.querySelectorAll('td');

        cells.forEach(function(cell) {
            let content = cell.innerHTML;

            // Remove existing highlights
            content = content.replace(/<span class="highlight">(.*?)<\/span>/gi, '$1');

            if (searchTerm && searchTerm.length > 0) {
                const regex = new RegExp('(' + escapeRegExp(searchTerm) + ')', 'gi');
                content = content.replace(regex, '<span class="highlight">$1</span>');
            }

            cell.innerHTML = content;
        });
    }

    /**
     * Escape special characters for regex
     */
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Initialize table sorting functionality
     */
    function initializeTableSorting() {
        const table = document.getElementById('olarcusage-table');

        if (!table) {
            return;
        }

        const headers = table.querySelectorAll('th.sortable-header');

        headers.forEach(function(header, index) {
            header.addEventListener('click', function() {
                sortTable(table, index, header);
            });
        });
    }

    /**
     * Sort table by column
     */
    function sortTable(table, columnIndex, header) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const sortIndicator = header.querySelector('.sort-indicator');

        // Determine sort direction
        let ascending = true;
        if (sortIndicator.classList.contains('asc')) {
            ascending = false;
        }

        // Clear all sort indicators
        table.querySelectorAll('.sort-indicator').forEach(function(indicator) {
            indicator.classList.remove('asc', 'desc');
        });

        // Set current sort indicator
        if (ascending) {
            sortIndicator.classList.add('asc');
        } else {
            sortIndicator.classList.add('desc');
        }

        // Sort rows
        rows.sort(function(a, b) {
            const aValue = getCellValue(a, columnIndex);
            const bValue = getCellValue(b, columnIndex);

            // Handle numeric values
            if (isNumeric(aValue) && isNumeric(bValue)) {
                return ascending ?
                    parseFloat(aValue) - parseFloat(bValue) :
                    parseFloat(bValue) - parseFloat(aValue);
            }

            // Handle string values
            const comparison = aValue.localeCompare(bValue);
            return ascending ? comparison : -comparison;
        });

        // Reorder rows in DOM
        rows.forEach(function(row) {
            tbody.appendChild(row);
        });

        // Add sorting animation
        tbody.style.opacity = '0.7';
        setTimeout(function() {
            tbody.style.opacity = '1';
        }, 150);
    }

    /**
     * Get cell value for sorting
     */
    function getCellValue(row, columnIndex) {
        const cell = row.cells[columnIndex];
        if (!cell) return '';

        // Extract text content, removing HTML tags
        let value = cell.textContent || cell.innerText || '';

        // Handle percentage values
        if (value.includes('%')) {
            value = value.replace('%', '');
        }

        // Handle badge content (extract number)
        if (cell.querySelector('.badge')) {
            const badgeText = cell.querySelector('.badge').textContent;
            const match = badgeText.match(/^(\d+)/);
            if (match) {
                value = match[1];
            }
        }

        return value.trim();
    }

    /**
     * Check if value is numeric
     */
    function isNumeric(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }

    /**
     * Initialize additional table enhancements
     */
    function initializeTableEnhancements() {
        // Add row hover effects
        const table = document.getElementById('olarcusage-table');
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(function(row) {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });

            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });

        // Add click-to-select functionality
        rows.forEach(function(row, index) {
            row.addEventListener('click', function() {
                // Remove previous selection
                table.querySelectorAll('tbody tr.selected').forEach(function(selectedRow) {
                    selectedRow.classList.remove('selected');
                });

                // Add selection to current row
                this.classList.add('selected');

                // Optional: Show row details or perform action
                console.log('Selected row:', index, this);
            });
        });
    }

    /**
     * Update results information
     */
    function updateResultsInfo(visibleCount, totalCount) {
        const resultsInfo = document.getElementById('results-info');

        if (!resultsInfo) {
            return;
        }

        if (typeof visibleCount === 'undefined') {
            const table = document.getElementById('olarcusage-table');
            if (table) {
                const allRows = table.querySelectorAll('tbody tr');
                const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
                visibleCount = visibleRows.length;
                totalCount = allRows.length;
            } else {
                return;
            }
        }

        if (visibleCount === totalCount) {
            resultsInfo.textContent = `Showing ${totalCount} courses`;
        } else {
            resultsInfo.textContent = `Showing ${visibleCount} of ${totalCount} courses`;
        }
    }

    /**
     * Export functionality helpers
     */
    window.olarcusage = {
        /**
         * Show loading state for export buttons
         */
        showExportLoading: function(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading"></span> Exporting...';
            button.disabled = true;

            // Reset after 3 seconds (fallback)
            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 3000);
        },

        /**
         * Filter table by column value
         */
        filterByColumn: function(columnIndex, value) {
            const table = document.getElementById('olarcusage-table');
            if (!table) return;

            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;

            rows.forEach(function(row) {
                const cell = row.cells[columnIndex];
                const cellValue = cell ? cell.textContent.toLowerCase() : '';

                if (value === '' || cellValue.includes(value.toLowerCase())) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            updateResultsInfo(visibleCount, rows.length);
        },

        /**
         * Reset all filters
         */
        resetFilters: function() {
            const table = document.getElementById('olarcusage-table');
            const searchInput = document.getElementById('table-search');

            if (searchInput) {
                searchInput.value = '';
            }

            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    row.style.display = '';
                    // Remove highlights
                    const cells = row.querySelectorAll('td');
                    cells.forEach(function(cell) {
                        cell.innerHTML = cell.innerHTML.replace(/<span class="highlight">(.*?)<\/span>/gi, '$1');
                    });
                });

                updateResultsInfo();
            }
        },

        /**
         * Print table
         */
        printTable: function() {
            window.print();
        }
    };

    // Add CSS for selected rows
    const style = document.createElement('style');
    style.textContent = `
        .report-table tbody tr.selected {
            background-color: #e3f2fd !important;
            border-left: 4px solid #2196f3;
        }

        .report-table tbody tr.selected:hover {
            background-color: #e3f2fd !important;
        }
    `;
    document.head.appendChild(style);

})();

