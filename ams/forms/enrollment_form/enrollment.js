let autocompleteReady = Promise.resolve();

document.addEventListener('DOMContentLoaded', async function() {
    autocompleteReady = initAutocomplete();
    await autocompleteReady;
    forceUppercaseOnInputs();
    setupFormValidation();
    setupAddressCopyButton();

    window.debugLookups = function() {
        console.log('\n🔍 CURRENT LOOKUP VALUES:');
        const fields = [
            { id: 'pi_mother_tongue_id', label: 'Mother Tongue' },
            { id: 'pi_religion_id', label: 'Religion' },
            { id: 'ac_indigenous_group_id', label: 'Indigenous Group' }
        ];
        fields.forEach(field => {
            const hiddenInput = document.querySelector(`input[type="hidden"][name="${field.id}"]`);
            const searchInput = document.getElementById(field.id + '_search');
            
            console.log(`\n${field.label}:`);
            if (hiddenInput) {
                console.log(`  Hidden Input Value: "${hiddenInput.value}"`);
            } else {
                console.log(`  ❌ Hidden input NOT found in DOM`);
            }
            if (searchInput) {
                console.log(`  Search Input Value: "${searchInput.value}"`);
            } else {
                console.log(`  ❌ Search input NOT found in DOM`);
            }
        });
    };
    
    console.log('💡 Tip: Run debugLookups() in console to check current lookup values');
});

function setupFormValidation() {
    const form = document.getElementById('enrollmentForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const lookupFields = [
                { id: 'pi_mother_tongue_id', label: 'Mother Tongue' },
                { id: 'pi_religion_id', label: 'Religion' },
                { id: 'ac_indigenous_group_id', label: 'Indigenous Group' }
            ];

            console.log('📋 ========== FORM SUBMISSION VALIDATION ==========');
            let allValid = true;

            lookupFields.forEach(field => {
                const hiddenInput = document.querySelector(`input[type="hidden"][name="${field.id}"]`);
                if (hiddenInput) {
                    const value = hiddenInput.value;
                    const hasValue = value && value.toString().trim() !== '';
                    
                    console.log(`\n📌 ${field.label}:`);
                    console.log(`   Element ID: ${hiddenInput.id}`);
                    console.log(`   Element Type: ${hiddenInput.type}`);
                    console.log(`   Current Value: "${value}"`);
                    console.log(`   Value Type: ${typeof value}`);
                    console.log(`   Value Length: ${value ? value.toString().length : 0}`);
                    console.log(`   Is Valid: ${hasValue ? '✅ YES' : '❌ NO'}`);

                    if (!hasValue) {
                        console.warn(`   ⚠️  MISSING! This field is required.`);
                        allValid = false;
                    }
                } else {
                    console.error(`   ❌ Hidden input not found in DOM for ${field.id}!`);
                    allValid = false;
                }
            });

            const formData = new FormData(form);
            console.log('\n📤 Form Data Being Submitted:');
            for (let [key, value] of formData) {
                console.log(`   ${key}: "${value}"`);
            }

            console.log('\n' + (allValid ? '✅ VALIDATION PASSED' : '❌ VALIDATION FAILED'));
            console.log('================================================\n');

            if (!allValid) {
                e.preventDefault();
                alert('❌ Please select values for all lookup fields:\n- Mother Tongue\n- Religion\n- Indigenous Group\n\nCheck browser console for details.');
                return false;
            }
        });
    }
}


async function initAutocomplete() {
    const lookupFields = [
        { id: 'pi_mother_tongue_id', endpoint: 'mother-tongue', label: 'Mother Tongue' },
        { id: 'pi_religion_id', endpoint: 'religions', label: 'Religion' },
        { id: 'ac_indigenous_group_id', endpoint: 'indigenous-groups', label: 'Indigenous Group' }
    ];

    for (const field of lookupFields) {
        const selectElement = document.getElementById(field.id);
        if (selectElement) {
            const allOptions = await fetchLookupOptions(field.endpoint);
            
            if (allOptions && allOptions.length > 0) {

                selectElement.dataset.allOptions = JSON.stringify(allOptions);
                selectElement.dataset.endpoint = field.endpoint;

                createSearchableSelect(selectElement, allOptions, field.label);
            }
        }
    }
}

async function fetchLookupOptions(endpoint) {
    try {
        const url = `/GEMS-AMES/ams/search/search.php?type=${endpoint}&limit=10000`;
        console.log(`🔍 Fetching: ${url}`);
        
        const response = await fetch(url);
        const data = await response.json();
        
        console.log(`📨 Response for ${endpoint}:`, data);
        
        if (!response.ok) {
            console.error(`❌ HTTP ${response.status} for ${endpoint}`);
            return null;
        }
        
        if (data.success && data.data && Array.isArray(data.data)) {
            console.log(`✓ ${endpoint}: ${data.count || data.data.length} records`);
            return data.data;
        } else {
            console.error(`❌ ${endpoint}: No success or data`, data);
            return null;
        }
    } catch (error) {
        console.error(`❌ Failed to load lookup options for ${endpoint}:`, error);
    }
    return null;
}

function createSearchableSelect(selectElement, options, label) {
    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select-wrapper';
    
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = `Type to search ${label}...`;
    searchInput.className = 'lookup-search-input';
    searchInput.id = selectElement.id + '_search';
    searchInput.autocomplete = 'off';

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = selectElement.name;
    hiddenInput.id = selectElement.id;
    hiddenInput.value = '';
    hiddenInput.dataset.options = JSON.stringify(options);

    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'lookup-suggestions';
    
    let currentSuggestions = [];
    let selectedIndex = -1;

    function updateSuggestions(query) {
        suggestionsContainer.innerHTML = '';
        selectedIndex = -1;
        
        if (!query || query.length < 1) {
            currentSuggestions = [];
            return;
        }

        const queryLower = query.toLowerCase();
        currentSuggestions = options.filter(item => 
            (item.name || item.label).toLowerCase().includes(queryLower)
        ).slice(0, 5);

        if (currentSuggestions.length === 0) {
            const noResults = document.createElement('div');
            noResults.className = 'lookup-suggestion';
            noResults.textContent = 'No matches found';
            noResults.style.color = '#999';
            noResults.style.pointerEvents = 'none';
            suggestionsContainer.appendChild(noResults);
            return;
        }

        currentSuggestions.forEach((item, index) => {
            const suggestionEl = document.createElement('div');
            suggestionEl.className = 'lookup-suggestion';
            suggestionEl.textContent = item.name || item.label;
            suggestionEl.dataset.index = index;
            suggestionEl.dataset.value = item.id || item.value;
            
            suggestionEl.addEventListener('click', () => {
                selectSuggestion(item);
            });
            
            suggestionsContainer.appendChild(suggestionEl);
        });
    }

    function selectSuggestion(item) {
        if (!item) return;
        searchInput.value = item.name || item.label;
        const idValue = item.id || item.value;
        hiddenInput.value = idValue;
        
        console.log(`✅ Selected ${label}: "${item.name || item.label}" -> ID: "${idValue}"`);
        console.log(`   Hidden input now has value: "${hiddenInput.value}"`);
        
        suggestionsContainer.innerHTML = '';
        currentSuggestions = [];
        selectedIndex = -1;
    }

    searchInput.addEventListener('input', (e) => {
        const query = e.target.value;
        updateSuggestions(query);

        if (currentSuggestions.length === 1) {
            const autoSelectCandidate = currentSuggestions[0];
            setTimeout(() => {
                if (autoSelectCandidate && currentSuggestions[0] === autoSelectCandidate) {
                    selectSuggestion(autoSelectCandidate);
                }
            }, 300);
        }
    });

    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, currentSuggestions.length - 1);
            highlightSuggestion();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            highlightSuggestion();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && selectedIndex < currentSuggestions.length) {
                selectSuggestion(currentSuggestions[selectedIndex]);
            }
        } else if (e.key === 'Escape') {
            suggestionsContainer.innerHTML = '';
            currentSuggestions = [];
            selectedIndex = -1;
        }
    });

    function highlightSuggestion() {
        const suggestions = suggestionsContainer.querySelectorAll('.lookup-suggestion');
        suggestions.forEach((el, idx) => {
            el.classList.toggle('highlighted', idx === selectedIndex);
        });
    }

    searchInput.addEventListener('focus', () => {
        if (searchInput.value && !hiddenInput.value) {
            searchInput.value = '';
        }
    });

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            suggestionsContainer.innerHTML = '';
            currentSuggestions = [];
        }
    });

    selectElement.parentNode.insertBefore(wrapper, selectElement);
    wrapper.appendChild(searchInput);
    wrapper.appendChild(suggestionsContainer);
    wrapper.appendChild(hiddenInput);
    selectElement.remove();
}
function forceUppercaseOnInputs(container = document) {
    const selector = 'input[type="text"]:not(.lookup-search-input), textarea, input[data-uppercase]';
    const inputs = container.querySelectorAll(selector);

    inputs.forEach(input => {
        input.addEventListener('input', (e) => {
            const el = e.target;
            const value = el.value;
            const upper = value.toUpperCase();
            if (value === upper) return;

            const start = el.selectionStart;
            const end = el.selectionEnd;
            el.value = upper;
            try {
                el.setSelectionRange(start, end);
            } catch (err) {
            }
        }, { passive: true });
    });
}

function resetForm() {
    window.history.replaceState({}, document.title, window.location.pathname);
    document.getElementById('enrollmentForm').reset();

    const searchInputs = document.querySelectorAll('.lookup-search-input');
    searchInputs.forEach(input => {
        input.value = '';
    });

    const hiddenInputs = document.querySelectorAll('input[type="hidden"][name*="tongue"], input[type="hidden"][name*="religion"], input[type="hidden"][name*="indigenous"]');
    hiddenInputs.forEach(input => {
        input.value = '';
    });

    window.scrollTo(0, 0);
}

async function fillDummyData() {
    await autocompleteReady;
    const set = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.value = value;
    };

    const selectByValue = (id, value) => {
        const el = document.getElementById(id);
        if (!el) {
            console.warn(`selectByValue: Element with id "${id}" not found`);
            return;
        }
        try {
            el.value = String(value);
            el.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (err) {
            console.error(`selectByValue error for ${id}:`, err);
        }
    };

    const checkBoxes = (name, values) => {
        document.querySelectorAll(`input[type="checkbox"][name="${name}[]"]`).forEach(cb => {
            cb.checked = values.includes(cb.value);
        });
    };

    const setLookup = (fieldId, label) => {
        const searchInput = document.getElementById(fieldId + '_search');
        const hiddenInput = document.querySelector(`input[type="hidden"][name="${fieldId}"]`);
        if (!searchInput || !hiddenInput) return;

        try {
            const options = JSON.parse(hiddenInput.dataset.options || '[]');
            const match = options.find(o =>
                (o.name || o.label || '').toLowerCase() === label.toLowerCase()
            );
            if (match) {
                searchInput.value = match.name || match.label;
                hiddenInput.value = match.id || match.value;
            } else {
                console.warn(`setLookup: no match found for "${label}" in field "${fieldId}"`);
            }
        } catch (e) {
            console.error(`setLookup: failed to parse options for "${fieldId}"`, e);
        }
    };

    // --- Section 1: Enrollment Details ---
    selectByValue('ed_grade_level', '2');
    set('ed_lrn', '123456789012');
    set('ed_school_year', '2025-2026');
    set('rl_last_grade_level_completed', 'Grade 2');
    set('rl_last_school_year_completed', '2024-2025');
    set('rl_school_attended', 'Baguio Central School');
    set('rl_school_id', '123456');
    checkBoxes('li_learning_modality', ['MODULAR (PRINT)']);

    // --- Section 2: Personal Information ---
    set('pi_last_name', 'Santos');
    set('pi_first_name', 'Maria');
    set('pi_middle_name', 'Reyes');
    set('pi_extension', '');
    set('pi_birth_date', '2016-03-15');
    selectByValue('pi_sex', 'FEMALE');
    set('pi_place_of_birth', 'Baguio City');
    selectByValue('pi_learning_classification', 'GRADED');
    set('pi_psa_bcn', '123456789012');
    set('ac_4ps_household_number', '');
    set('pi__attended_early_learning_program_name', 'Sunshine Daycare Center');

    setLookup('pi_mother_tongue_id', 'Ilocano');
    setLookup('pi_religion_id', 'Roman Catholic');
    setLookup('ac_indigenous_group_id', 'Not Applicable');

    // --- Section 3: Address ---
    set('ca_house_number', '12');
    set('ca_street_name', 'Magsaysay Ave');
    set('ca_barangay', 'Brgy. Lualhati');
    set('ca_municipality', 'Baguio City');
    set('ca_provice', 'Benguet');
    set('ca_country', 'Philippines');
    set('ca_zipcode', '2600');
    selectByValue('ca_address_status', 'Owned');

    set('pa_house_number', '12');
    set('pa_street_name', 'Magsaysay Ave');
    set('pa_barangay', 'Brgy. Lualhati');
    set('pa_municipality', 'Baguio City');
    set('pa_province', 'Benguet');
    set('pa_country', 'Philippines');
    set('pa_zip_code', '2600');
    selectByValue('pa_address_status', 'Owned');

    // --- Section 4: Medical ---
    set('mf_a_medicine', '');
    set('mf_a_pollen', '');
    set('mf_a_food', 'Shrimp');
    set('mf_a_others', '');
    checkBoxes('mf_o_medical_conditions', ['ASTHMA']);
    set('mf_o_others', '');
    set('mf_sh_surgery_date', '');
    set('mf_sh_hospital_name', '');
    set('mf_sh_bodypart_affected', '');
    set('mf_tm_type', '');
    set('mf_tm_dosage_schedule', '');
    checkBoxes('mf_mc_conditions', ['HYPERTENSION']);
    set('mf_mc_cancer_type', '');
    set('mf_mc_others', '');
    set('mf_o_pertinent_information', 'Student has mild seafood allergy. No medication required.');
    selectByValue('mf_exposure_c_v', '0');

    // --- Section 5: Parents / Guardian ---
    set('fi_last_name', 'Santos');
    set('fi_first_name', 'Ricardo');
    set('fi_middle_name', 'Cruz');
    set('fi_contact_number', '09171234567');
    set('fi_occupation', 'Engineer');
    set('fi_relationship_status', 'Married');
    set('fi_communication', 'Mobile');

    set('mi_last_name', 'Santos');
    set('mi_first_name', 'Lourdes');
    set('mi_middle_name', 'Reyes');
    set('mi_contact_number', '09187654321');
    set('mi_occupation', 'Teacher');
    set('mi_relationship_status', 'Married');
    set('mi_communication', 'Mobile');

    set('gi_last_name', '');
    set('gi_first_name', '');
    set('gi_middle_name', '');
    set('gi_contact_number', '');
    set('gi_occupation', '');
    set('gi_relationship_status', '');
    set('gi_communication', '');

    selectByValue('ec_to_contact', 'MOTHER');

    // --- Section 6: Special Needs ---
    checkBoxes('snep_a1_diagnosis', ['NONE']);
    checkBoxes('snep_a2_manifestations', ['NONE']);
    selectByValue('snep_a1_sub_shpcd', 'NONE');
    selectByValue('snep_a1_sub_vi', 'NONE');
    selectByValue('snep_pwd_id', '0');
}

function setupAddressCopyButton() {
    const copyBtn = document.getElementById('copyAddressBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            copyCurrentToPermanentAddress();
        });
    }
}

function copyCurrentToPermanentAddress() {
    const fieldMappings = [
        { current: 'ca_house_number', permanent: 'pa_house_number' },
        { current: 'ca_street_name', permanent: 'pa_street_name' },
        { current: 'ca_barangay', permanent: 'pa_barangay' },
        { current: 'ca_municipality', permanent: 'pa_municipality' },
        { current: 'ca_provice', permanent: 'pa_province' },
        { current: 'ca_country', permanent: 'pa_country' },
        { current: 'ca_zipcode', permanent: 'pa_zip_code' },
        { current: 'ca_address_status', permanent: 'pa_address_status' }
    ];

    let errorCount = 0;
    fieldMappings.forEach(mapping => {
        const currentField = document.getElementById(mapping.current);
        const permanentField = document.getElementById(mapping.permanent);
        
        if (currentField && permanentField) {
            permanentField.value = currentField.value;
            // Trigger change event in case there are listeners
            permanentField.dispatchEvent(new Event('change', { bubbles: true }));
        } else {
            errorCount++;
        }
    });

    if (errorCount > 0) {
        alert('Error: Failed to copy some address fields. Please check the form.');
    }
}