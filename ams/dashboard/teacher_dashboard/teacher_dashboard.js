document.addEventListener('DOMContentLoaded', function () {
    const gradeFilter = document.getElementById('gradeFilter');
    const studentSearch = document.getElementById('studentSearch');

    function normalizeGrade(gradeText) {
        if (!gradeText) return '';
        if (/Kindergarten|KD|^K$/i.test(gradeText)) {
            return 'Kindergarten';
        }
        const match = gradeText.match(/\d+/);
        return match ? match[0] : '';
    }

    function applyFilters() {
        const selectedGrade = gradeFilter.value;
        const searchText = studentSearch.value.toLowerCase();
        
        console.log('🔍 Applying filters - Grade:', selectedGrade, 'Search:', searchText);
        
        document.querySelectorAll('.student-table tbody').forEach((tbody) => {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.forEach(row => {
                const rowGrade = row.getAttribute('data-grade') || '';
                const normalizedRowGrade = normalizeGrade(rowGrade);
                const studentName = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                
                const gradeMatch = !selectedGrade || normalizedRowGrade === selectedGrade;
                const nameMatch = !searchText || studentName.includes(searchText);
                const shouldShow = gradeMatch && nameMatch;
                
                row.style.display = shouldShow ? '' : 'none';
            });
        });
    }

    if (gradeFilter) {
        gradeFilter.addEventListener('change', applyFilters);
    }
    
    if (studentSearch) {
        studentSearch.addEventListener('keyup', applyFilters);
    }
});