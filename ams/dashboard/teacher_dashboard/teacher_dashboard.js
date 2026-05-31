        function getGradeSortValue(text) {
            const match = text.match(/\d+/);
            if (!match) {
                if (/K|KD|Kindergarten/i.test(text)) {
                    return 0;
                }
                return Number.MAX_SAFE_INTEGER;
            }
            return parseInt(match[0], 10);
        }

        function sortStudentTables(direction) {
            document.querySelectorAll('.student-table tbody').forEach(tbody => {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    const aGrade = getGradeSortValue(a.dataset.grade || '');
                    const bGrade = getGradeSortValue(b.dataset.grade || '');
                    if (aGrade === bGrade) {
                        return a.textContent.trim().localeCompare(b.textContent.trim());
                    }
                    return direction === 'asc' ? aGrade - bGrade : bGrade - aGrade;
                });
                rows.forEach(row => tbody.appendChild(row));
            });
        }

        document.getElementById('sortOrder').addEventListener('change', function () {
            sortStudentTables(this.value);
        });