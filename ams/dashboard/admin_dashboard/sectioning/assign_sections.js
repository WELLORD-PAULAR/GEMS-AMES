const students = {};
const allStudents = [];
const changedSections = {};
const grades = ['K', '1', '2', '3', '4', '5', '6'];

function showGradeTab(grade) {
    document.querySelectorAll('.grade-tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });

    const tabId = grade === 'K' ? 'tabK' : 'tab' + grade;
    document.getElementById(tabId).classList.add('active');
    document.getElementById('tab-' + grade).classList.add('active');
}

function loadStudents() {
    const statusFilter = document.getElementById('statusFilter').value;
    const loadBtn = document.getElementById('loadBtn');
    const spinner = document.getElementById('loadingSpinner');
    
    // Clear search input when loading
    document.getElementById('searchInput').value = '';

    loadBtn.style.display = 'none';
    spinner.style.display = 'inline-block';

    const params = new URLSearchParams();
    params.append('action', 'get_students');
    if (statusFilter) {
        params.append('status', statusFilter);
    }

    fetch('section_assignment_handler.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.students) {
                renderStudents(data.students);
            }
            loadBtn.style.display = 'inline';
            spinner.style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading students');
            loadBtn.style.display = 'inline';
            spinner.style.display = 'none';
        });
}

function renderStudents(studentList) {
    allStudents.length = 0;
    allStudents.push(...studentList);
    
    const gradeStudents = {
        'K': [], '1': [], '2': [], '3': [], '4': [], '5': [], '6': []
    };

    studentList.forEach(student => {
        const grade = student.ed_grade_level || 'K';
        if (gradeStudents[grade]) {
            gradeStudents[grade].push(student);
        }
    });

    grades.forEach(grade => {
        const tabId = grade === 'K' ? 'tabK' : 'tab' + grade;
        const container = document.getElementById(tabId);
        const gradeStudentList = gradeStudents[grade];

        if (!gradeStudentList || gradeStudentList.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533l1.36-6.513z"/>
                        <circle cx="8" cy="4.5" r="1"/>
                    </svg>
                    <p>No students found in Grade ${grade}</p>
                </div>
            `;
        } else {
            container.innerHTML = '<div class="section-assignment-grid"></div>';
            const grid = container.querySelector('.section-assignment-grid');

            gradeStudentList.forEach(student => {
                const studentId = student.fk_full_name_bd;
                const currentSection = student.section || '';
                const gradeSection = sections[grade] || [];

                const card = document.createElement('div');
                card.className = 'student-card';
                card.setAttribute('data-student-id', studentId);
                card.setAttribute('data-name', student.full_name.toLowerCase());
                card.setAttribute('data-lrn', student.ed_lrn);
                card.innerHTML = `
                    <div class="student-name">${escapeHtml(student.full_name)}</div>
                    <div class="student-details">
                        <span><strong>Grade:</strong> ${grade}</span>
                        <span><strong>LRN:</strong> ${student.ed_lrn}</span>
                        <span>
                            <strong>Status:</strong> 
                            <span class="status-badge status-${student.verification.toLowerCase()}">
                                ${student.verification}
                            </span>
                        </span>
                    </div>
                    <select class="form-control section-dropdown" onchange="onSectionChange('${studentId}', this.value)">
                        <option value="">-- Select Section --</option>
                        ${gradeSection.map(sec => `
                            <option value="${sec}" ${currentSection === sec ? 'selected' : ''}>
                                ${sec}
                            </option>
                        `).join('')}
                    </select>
                `;

                grid.appendChild(card);
            });
        }
    });

    updateSaveButton();
}

function onSectionChange(studentId, section) {
    changedSections[studentId] = section;
    updateSaveButton();
}

function updateSaveButton() {
    const saveBtn = document.getElementById('saveAllBtn');
    if (Object.keys(changedSections).length > 0) {
        saveBtn.classList.add('visible');
    } else {
        saveBtn.classList.remove('visible');
    }
}

function saveAllSections() {
    const saveBtn = document.getElementById('saveAllBtn');
    const originalText = saveBtn.innerText;
    saveBtn.disabled = true;
    saveBtn.innerText = 'Saving...';

    const assignments = Object.entries(changedSections).reduce((acc, [studentId, section]) => {
        acc[studentId] = section;
        return acc;
    }, {});

    fetch('section_assignment_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'bulk_assign',
            assignments: assignments
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('All sections saved successfully!');
            Object.keys(changedSections).forEach(key => delete changedSections[key]);
            updateSaveButton();
            loadStudents();
        } else {
            alert('Error saving sections: ' + (data.message || 'Unknown error'));
        }
        saveBtn.disabled = false;
        saveBtn.innerText = originalText;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving sections');
        saveBtn.disabled = false;
        saveBtn.innerText = originalText;
    });
}

function resetFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('searchInput').value = '';
    loadStudents();
}

function performSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    
    if (!searchTerm) {
        document.querySelectorAll('.student-card').forEach(card => {
            card.classList.remove('hidden');
        });
        document.querySelectorAll('.empty-state').forEach(state => {
            state.style.display = '';
        });
        return;
    }

    let totalFound = 0;
    
    document.querySelectorAll('.student-card').forEach(card => {
        const name = card.getAttribute('data-name');
        const lrn = card.getAttribute('data-lrn');
        
        if (name.includes(searchTerm) || lrn.includes(searchTerm)) {
            card.classList.remove('hidden');
            totalFound++;
        } else {
            card.classList.add('hidden');
        }
    });

    document.querySelectorAll('.empty-state').forEach(state => {
        state.style.display = 'none';
    });

    if (totalFound === 0) {
        const activeTabs = document.querySelectorAll('.grade-tab-content.active');
        if (activeTabs.length > 0) {
            const activeTab = activeTabs[0];
            const grid = activeTab.querySelector('.section-assignment-grid');
            if (grid && grid.querySelectorAll('.student-card:not(.hidden)').length === 0) {
                grid.innerHTML = `
                    <div class="no-search-results" style="grid-column: 1/-1;">
                        <p>No students found matching "${escapeHtml(searchTerm)}"</p>
                    </div>
                `;
            }
        }
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

document.addEventListener('DOMContentLoaded', function() {
    loadStudents();
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', performSearch);
    }
});
        function resetFilters() {
            document.getElementById('statusFilter').value = '';
            loadStudents();
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Load students on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStudents();
        });