/**
 * Request Form JavaScript
 * Handles dynamic item rows and form submission
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('requisitionForm');
    const itemsTableBody = document.getElementById('itemsTableBody');
    const addItemBtn = document.getElementById('addItemBtn');
    const submitBtn = document.getElementById('submitBtn');

    let itemCount = 0;

    // Add first item row on page load
    addItemRow();

    // Add item button click handler
    addItemBtn.addEventListener('click', addItemRow);

    // Form submission handler
    form.addEventListener('submit', handleFormSubmit);

    /**
     * Add a new item row to the table
     */
    function addItemRow() {
        itemCount++;

        const row = document.createElement('tr');
        row.className = 'item-row';
        row.dataset.itemId = itemCount;

        row.innerHTML = `
            <td>
                <input type="number" 
                       name="items[${itemCount}][quantity]" 
                       min="1" 
                       required 
                       placeholder="0">
            </td>
            <td>
                <select name="items[${itemCount}][unit]" required>
                    <option value="">Unit</option>
                    <option value="pcs">pcs</option>
                    <option value="kg">kg</option>
                    <option value="meters">meters</option>
                    <option value="liters">liters</option>
                    <option value="boxes">boxes</option>
                    <option value="rolls">rolls</option>
                    <option value="sets">sets</option>
                    <option value="pairs">pairs</option>
                    <option value="units">units</option>
                </select>
            </td>
            <td>
                <input type="text" 
                       name="items[${itemCount}][description]" 
                       required 
                       placeholder="Item description/specification">
            </td>
            <td>
                <input type="text" 
                       name="items[${itemCount}][remarks]" 
                       placeholder="Optional remarks">
            </td>
            <td>
                <button type="button" 
                        class="btn btn-danger btn-sm remove-item-btn" 
                        onclick="removeItemRow(${itemCount})">
                    🗑️
                </button>
            </td>
        `;

        itemsTableBody.appendChild(row);
    }

    /**
     * Remove an item row from the table
     */
    window.removeItemRow = function (itemId) {
        const rows = itemsTableBody.querySelectorAll('.item-row');

        // Prevent removing the last row
        if (rows.length <= 1) {
            Toast.show('At least one item is required', 'warning');
            return;
        }

        const row = itemsTableBody.querySelector(`[data-item-id="${itemId}"]`);
        if (row) {
            row.remove();
        }
    };

    /**
     * Handle form submission
     */
    async function handleFormSubmit(e) {
        e.preventDefault();

        // Validate form
        if (!FormValidator.validate(form)) {
            Toast.show('Please fill in all required fields', 'error');
            return;
        }

        // Validate at least one item
        const itemRows = itemsTableBody.querySelectorAll('.item-row');
        if (itemRows.length === 0) {
            Toast.show('Please add at least one item', 'error');
            return;
        }

        // Collect form data
        const formData = {
            requester_name: document.getElementById('requester_name').value.trim(),
            department: document.getElementById('department').value,
            purpose: document.getElementById('purpose').value.trim(),
            items: []
        };

        // Collect items data
        itemRows.forEach(row => {
            const itemId = row.dataset.itemId;
            const quantity = row.querySelector(`[name="items[${itemId}][quantity]"]`).value;
            const unit = row.querySelector(`[name="items[${itemId}][unit]"]`).value;
            const description = row.querySelector(`[name="items[${itemId}][description]"]`).value.trim();
            const remarks = row.querySelector(`[name="items[${itemId}][remarks]"]`).value.trim();

            if (quantity && unit && description) {
                formData.items.push({
                    quantity: parseInt(quantity),
                    unit: unit,
                    description: description,
                    remarks: remarks
                });
            }
        });

        // Validate items
        if (formData.items.length === 0) {
            Toast.show('Please add at least one valid item', 'error');
            return;
        }

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        Loading.show();

        try {
            // Submit form data
            const response = await Ajax.post('/api/submit_request.php', formData);

            Loading.hide();

            if (response.success) {
                // Show success modal with RF number
                showSuccessModal(response.data.rf_control_number);
            } else {
                Toast.show(response.message || 'Failed to submit request', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            }
        } catch (error) {
            Loading.hide();
            console.error('Submission error:', error);
            Toast.show('An error occurred while submitting the request', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Request';
        }
    }

    /**
     * Show success modal with RF Control Number
     */
    function showSuccessModal(rfNumber) {
        const modal = document.getElementById('successModal');
        const rfNumberElement = document.getElementById('rfNumber');

        rfNumberElement.textContent = rfNumber;
        modal.style.display = 'flex';

        // Reset form
        form.reset();
        itemsTableBody.innerHTML = '';
        itemCount = 0;
        addItemRow();

        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Request';
    }
});
