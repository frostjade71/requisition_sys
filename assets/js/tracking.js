/**
 * Tracking Page JavaScript
 * Handles request search and timeline display
 */

document.addEventListener('DOMContentLoaded', function () {
    const trackingForm = document.getElementById('trackingForm');
    const resultsContainer = document.getElementById('resultsContainer');
    const noResults = document.getElementById('noResults');

    trackingForm.addEventListener('submit', handleSearch);

    /**
     * Handle search form submission
     */
    async function handleSearch(e) {
        e.preventDefault();

        const rfNumber = document.getElementById('rfNumber').value.trim();

        if (!rfNumber) {
            Toast.show('Please enter an RF Control Number', 'warning');
            return;
        }

        // Validate format
        const rfPattern = /^RF-\d{8}-\d{4}$/;
        if (!rfPattern.test(rfNumber)) {
            Toast.show('Invalid RF Control Number format. Expected: RF-YYYYMMDD-XXXX', 'error');
            return;
        }

        Loading.show();

        try {
            const response = await Ajax.get(`/api/get_request_status.php?rf_number=${encodeURIComponent(rfNumber)}`);

            Loading.hide();

            if (response.success) {
                displayResults(response.data);
            } else {
                showNoResults();
                Toast.show(response.message || 'Request not found', 'error');
            }
        } catch (error) {
            Loading.hide();
            console.error('Search error:', error);
            showNoResults();
            Toast.show('An error occurred while searching', 'error');
        }
    }

    /**
     * Display search results
     */
    function displayResults(data) {
        noResults.style.display = 'none';
        resultsContainer.style.display = 'block';

        // Display request details
        displayRequestDetails(data.request);

        // Display items
        displayItems(data.items);

        // Display approval timeline
        displayTimeline(data.approvals);

        // Scroll to results
        resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Display request details
     */
    function displayRequestDetails(request) {
        const detailsContainer = document.getElementById('requestDetails');

        const statusBadge = getStatusBadgeHTML(request.status);

        detailsContainer.innerHTML = `
            <div class="detail-item">
                <div class="detail-label">RF Control Number</div>
                <div class="detail-value rf-number">${request.rf_control_number}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Requester Name</div>
                <div class="detail-value">${request.requester_name}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Department</div>
                <div class="detail-value">${request.department}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">${statusBadge}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Current Level</div>
                <div class="detail-value">Level ${request.current_approval_level} of 5</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Submitted Date</div>
                <div class="detail-value">${formatDate(request.created_at)}</div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <div class="detail-label">Purpose</div>
                <div class="detail-value" style="font-weight: normal; font-size: 1rem;">${request.purpose}</div>
            </div>
        `;
    }

    /**
     * Display requisition items
     */
    function displayItems(items) {
        const tbody = document.getElementById('itemsTableBody');

        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No items found</td></tr>';
            return;
        }

        tbody.innerHTML = items.map(item => `
            <tr>
                <td>${item.quantity}</td>
                <td>${item.unit}</td>
                <td>${item.description}</td>
                <td>${item.warehouse_inventory || 'N/A'}</td>
                <td>${item.balance_for_purchase || 'N/A'}</td>
                <td>${item.remarks || '-'}</td>
            </tr>
        `).join('');
    }

    /**
     * Display approval timeline
     */
    function displayTimeline(approvals) {
        const timeline = document.getElementById('approvalTimeline');

        if (!approvals || approvals.length === 0) {
            timeline.innerHTML = '<p class="text-center">No approval data available</p>';
            return;
        }

        timeline.innerHTML = approvals.map(approval => {
            const statusClass = approval.status;
            const icon = getStatusIcon(approval.status);

            let metaHTML = '';
            if (approval.status === 'approved' || approval.status === 'rejected') {
                metaHTML = `
                    <div class="timeline-meta">
                        <div class="timeline-approver">
                            <strong>Approver:</strong> ${approval.approver_name || 'N/A'}
                        </div>
                        <div class="timeline-date">
                            <strong>Date:</strong> ${formatDate(approval.approved_at)}
                        </div>
                        ${approval.remarks ? `
                            <div class="timeline-remarks">
                                <strong>Remarks:</strong> ${approval.remarks}
                            </div>
                        ` : ''}
                    </div>
                `;
            }

            return `
                <div class="timeline-item ${statusClass}">
                    <div class="timeline-dot">${icon}</div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div>
                                <div class="timeline-level">Level ${approval.approval_level}</div>
                                <div class="timeline-title">${approval.approver_role}</div>
                            </div>
                            <div>${getStatusBadgeHTML(approval.status)}</div>
                        </div>
                        ${metaHTML}
                    </div>
                </div>
            `;
        }).join('');
    }

    /**
     * Show no results message
     */
    function showNoResults() {
        resultsContainer.style.display = 'none';
        noResults.style.display = 'block';
        noResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /**
     * Get status icon - now handled by CSS background images
     */
    function getStatusIcon() {
        return ''; // Empty string since we're using background images
    }

    /**
     * Get status badge HTML
     */
    function getStatusBadgeHTML(status) {
        const badges = {
            pending: '<span class="badge badge-warning">Pending</span>',
            approved: '<span class="badge badge-success">Approved</span>',
            rejected: '<span class="badge badge-danger">Rejected</span>',
            completed: '<span class="badge badge-info">Completed</span>'
        };
        return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
    }
});
