<?php
$page_title = 'Track Request - LEYECO III Requisition System';
$additional_css = ['/assets/css/tracking.css'];
$additional_js = ['/assets/js/tracking.js'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="tracking-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🔍 Track Requisition Request</h2>
                <p>Enter your RF Control Number to track the status of your requisition request.</p>
            </div>
            
            <!-- Search Form -->
            <form id="trackingForm" class="tracking-form">
                <div class="search-box">
                    <input type="text" 
                           id="rfNumber" 
                           name="rf_number" 
                           placeholder="Enter RF Control Number (e.g., RF-20251203-0001)" 
                           required
                           pattern="RF-\d{8}-\d{4}">
                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>
                </div>
                <p class="format-help">
                    Format: RF-YYYYMMDD-XXXX (e.g., RF-20251203-0001)
                </p>
            </form>
        </div>
        
        <!-- Results Container -->
        <div id="resultsContainer" style="display: none;">
            <!-- Request Details -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Request Details</h3>
                </div>
                
                <div class="request-details" id="requestDetails">
                    <!-- Details will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Requisition Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Requisition Items</h3>
                </div>
                
                <div class="table-responsive">
                    <table id="itemsTable">
                        <thead>
                            <tr>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Description</th>
                                <th>Warehouse Stock</th>
                                <th>Balance to Purchase</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- Items will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Approval Timeline -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Approval Timeline</h3>
                </div>
                
                <div class="timeline" id="approvalTimeline">
                    <!-- Timeline will be populated by JavaScript -->
                </div>
            </div>
        </div>
        
        <!-- No Results Message -->
        <div id="noResults" class="card" style="display: none;">
            <div class="no-results">
                <div class="no-results-icon">
                    <img src="<?php echo BASE_URL; ?>/assets/css/icons/decline.png" alt="Not Found" style="width: 64px; height: 64px;">
                </div>
                <h3>Request Not Found</h3>
                <p>No requisition request found with the provided RF Control Number.</p>
                <p>Please check the number and try again.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
