
document.addEventListener('DOMContentLoaded', async function() {
    await initAutocomplete();
    forceUppercaseOnInputs();
});

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
        const response = await fetch(`/GEMS-AMES/ams/search/search.php?type=${endpoint}`);
        const data = await response.json();
        
        if (data.success && data.data && Array.isArray(data.data)) {
            return data.data;
        }
    } catch (error) {
        console.error('Failed to load lookup options:', endpoint, error);
    }
    return null;
}

function createSearchableSelect(selectElement, options, label) {
    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select-wrapper';
    wrapper.style.position = 'relative';
    
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = `Search ${label}...`;
    searchInput.className = 'lookup-search-input';
    searchInput.style.width = '100%';
    searchInput.style.padding = '8px';
    searchInput.style.marginBottom = '4px';
    searchInput.style.border = '1px solid #ccc';
    searchInput.style.borderRadius = '4px';
    
    searchInput.name = selectElement.name;
    searchInput.id = selectElement.id;

    const dropdownContainer = document.createElement('div');
    dropdownContainer.className = 'lookup-dropdown';
    dropdownContainer.style.position = 'absolute';
    dropdownContainer.style.top = '100%';
    dropdownContainer.style.left = '0';
    dropdownContainer.style.right = '0';
    dropdownContainer.style.backgroundColor = 'white';
    dropdownContainer.style.border = '1px solid #ccc';
    dropdownContainer.style.borderRadius = '4px';
    dropdownContainer.style.maxHeight = '200px';
    dropdownContainer.style.overflowY = 'auto';
    dropdownContainer.style.display = 'none';
    dropdownContainer.style.zIndex = '1000';
    
    function populateDropdown(filteredOptions) {
        dropdownContainer.innerHTML = '';
        
        const clearOption = document.createElement('div');
        clearOption.className = 'lookup-option';
        clearOption.textContent = '-- Clear --';
        clearOption.style.padding = '8px';
        clearOption.style.cursor = 'pointer';
        clearOption.style.borderBottom = '1px solid #eee';
        clearOption.addEventListener('click', () => {
            searchInput.value = '';
            selectElement.value = '';
            dropdownContainer.style.display = 'none';
        });
        dropdownContainer.appendChild(clearOption);
        
        filteredOptions.forEach(item => {
            const option = document.createElement('div');
            option.className = 'lookup-option';
            option.dataset.value = item.id || item.value;
            option.textContent = item.name || item.label;
            option.style.padding = '8px';
            option.style.cursor = 'pointer';
            option.style.borderBottom = '1px solid #eee';
            option.style.transition = 'background-color 0.2s';
            
            option.addEventListener('mouseenter', () => {
                option.style.backgroundColor = '#f0f0f0';
            });
            option.addEventListener('mouseleave', () => {
                option.style.backgroundColor = 'transparent';
            });
            
            option.addEventListener('click', () => {
                searchInput.value = item.name || item.label;
                selectElement.value = item.id || item.value;
                dropdownContainer.style.display = 'none';
            });
            
            dropdownContainer.appendChild(option);
        });
    }
    
    searchInput.addEventListener('focus', () => {
        dropdownContainer.style.display = 'block';
        populateDropdown(options);
    });
    
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        
        if (query.length === 0) {
            populateDropdown(options);
            dropdownContainer.style.display = 'block';
        } else {
            const filtered = options.filter(item => 
                (item.name || item.label).toLowerCase().includes(query)
            );
            populateDropdown(filtered);
            dropdownContainer.style.display = filtered.length > 0 ? 'block' : 'none';
        }
    });

    searchInput.addEventListener('blur', () => {
        setTimeout(() => {
            dropdownContainer.style.display = 'none';
        }, 200);
    });
    
    selectElement.parentNode.insertBefore(wrapper, selectElement);
    wrapper.appendChild(searchInput);
    wrapper.appendChild(dropdownContainer);
    selectElement.style.display = 'none';
}
function forceUppercaseOnInputs(container = document) {
    const selector = 'input[type="text"], textarea, input[data-uppercase]';
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
                // ignore if element doesn't support selection (e.g., input type without selection)
            }
        }, { passive: true });
    });
}
