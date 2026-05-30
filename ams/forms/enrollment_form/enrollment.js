/**
 * Enrollment Form - Autocomplete Only
 * Traditional form submission with AJAX autocomplete for search boxes
 */

document.addEventListener('DOMContentLoaded', async function() {
    // Initialize autocomplete for lookup fields
    await initAutocomplete();
    // Force uppercase on text inputs and textareas as user types
    forceUppercaseOnInputs();
});

/**
 * Initialize autocomplete for lookup fields
 */
async function initAutocomplete() {
    const lookupFields = [
        { id: 'pi_mother_tongue_id', endpoint: 'mother-tongue' },
        { id: 'pi_religion_id', endpoint: 'religions' },
        { id: 'ac_indigenous_group_id', endpoint: 'indigenous-groups' }
    ];

    for (const field of lookupFields) {
        const element = document.getElementById(field.id);
        if (element) {
            // Load all options on page load
            await loadLookupOptions(field.endpoint, field.id);
            
            // Add autocomplete listener for search as user types
            element.addEventListener('input', function(e) {
                searchLookup(field.endpoint, e.target.value, field.id);
            });
        }
    }
}

/**
 * Load all options for a lookup field
 */
async function loadLookupOptions(endpoint, selectId) {
    try {
        const response = await fetch(`/GEMS-AMES/ams/api/search.php?type=${endpoint}`);
        const data = await response.json();
        
        if (data.success && data.data && Array.isArray(data.data)) {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">-- Select --</option>';
            
            data.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id || item.value;
                option.textContent = item.name || item.label;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load lookup options:', endpoint, error);
    }
}

/**
 * Search in lookup table
 */
async function searchLookup(endpoint, query, selectId) {
    if (!query || query.length < 2) {
        // Reload all options if query is cleared
        await loadLookupOptions(endpoint, selectId);
        return;
    }

    try {
        const response = await fetch(`/GEMS-AMES/ams/api/search.php?type=${endpoint}&q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.data && Array.isArray(data.data)) {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">-- Select --</option>';
            
            data.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id || item.value;
                option.textContent = item.name || item.label;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Search failed:', error);
    }
}

/**
 * Force uppercase for inputs and textareas while preserving cursor
 */
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
