
        function updateColumnCount() {
            const checkedCount = document.querySelectorAll('input[name="columns[]"]:checked').length;
            document.getElementById('selectedCount').textContent = checkedCount;
        }

        function selectAllColumns() {
            document.querySelectorAll('input[name="columns[]"]').forEach(cb => {
                cb.checked = true;
            });
            updateColumnCount();
        }

        function deselectAllColumns() {
            document.querySelectorAll('input[name="columns[]"]').forEach(cb => {
                cb.checked = false;
            });
            updateColumnCount();
        }

        function goBack() {
            history.back();
        }

        document.querySelectorAll('input[name="columns[]"]').forEach(cb => {
            cb.addEventListener('change', updateColumnCount);
        });

        document.getElementById('selectorForm').addEventListener('submit', function(e) {
            const checkedCount = document.querySelectorAll('input[name="columns[]"]:checked').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert('Please select at least one column to export.');
            }
        });