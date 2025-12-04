<?php
$page_title = 'Submit Request - LEYECO III Requisition System';
$additional_css = ['/assets/css/request_form.css'];
$additional_js = ['/assets/js/request_form.js'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📝 Requisition Request Form</h2>
                <p>Fill out the form below to submit a new material requisition request. All fields marked with <span class="required"></span> are required.</p>
            </div>
            
            <form id="requisitionForm">
                <!-- Requester Information -->
                <div class="form-section">
                    <h3>Requester Information</h3>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="requester_name" class="required">Requester Name</label>
                                <input type="text" id="requester_name" name="requester_name" required>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="form-group">
                                <label for="department" class="required">Department</label>
                                <select id="department" name="department" required>
                                    <option value="">-- Select Department --</option>
                                    <?php foreach (DEPARTMENTS as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept); ?>">
                                            <?php echo htmlspecialchars($dept); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="purpose" class="required">Purpose of Request</label>
                        <textarea id="purpose" name="purpose" rows="4" placeholder="Provide detailed purpose for the materials requested..." required></textarea>
                    </div>
                </div>
                
                <!-- Requisition Items -->
                <div class="form-section">
                    <div class="section-header">
                        <h3>Requisition Items</h3>
                        <button type="button" class="btn btn-secondary btn-sm" id="addItemBtn">
                            ➕ Add Item
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Qty</th>
                                    <th style="width: 15%;">Unit</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 30%;">Remarks</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be added dynamically -->
                            </tbody>
                        </table>
                    </div>
                    
                    <p class="help-text">
                        <strong>Note:</strong> Warehouse inventory and balance for purchase will be filled by the Warehouse Section Head during approval.
                    </p>
                </div>
                
                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="window.location.href='<?php echo BASE_URL; ?>/'">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="success-icon">✅</div>
        <h2>Request Submitted Successfully!</h2>
        <p>Your requisition request has been submitted and is now pending approval.</p>
        
        <div class="rf-number-display">
            <label>Your RF Control Number:</label>
            <div class="rf-number" id="rfNumber"></div>
        </div>
        
        <p class="help-text">
            Please save this RF Control Number for tracking your request.
        </p>
        
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="window.location.href='<?php echo BASE_URL; ?>/public/track_request.php'">
                Track Request
            </button>
            <button class="btn btn-primary" onclick="window.location.href='<?php echo BASE_URL; ?>/public/request_form.php'">
                Submit Another Request
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
