<!DOCTYPE html>
<html>
<head>
    <title>Test Approval API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            padding: 12px 24px;
            margin: 10px 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-approve {
            background: #4CAF50;
            color: white;
        }
        .btn-reject {
            background: #f44336;
            color: white;
        }
        .response {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
            font-family: monospace;
        }
        .success {
            background: #D4EDDA;
            color: #155724;
        }
        .error {
            background: #F8D7DA;
            color: #721C24;
        }
    </style>
</head>
<body>
    <h1>Test Approval API Directly</h1>
    
    <div class="form-group">
        <label>Requisition ID:</label>
        <input type="number" id="requisition_id" value="6">
    </div>
    
    <div class="form-group">
        <label>Approval Level:</label>
        <input type="number" id="approval_level" value="1" min="1" max="5">
    </div>
    
    <div class="form-group">
        <label>Remarks (Optional):</label>
        <textarea id="remarks" rows="3" placeholder="Enter your remarks..."></textarea>
    </div>
    
    <div>
        <button class="btn-approve" onclick="testApproval('approved')">✓ Test Approve</button>
        <button class="btn-reject" onclick="testApproval('rejected')">✗ Test Reject</button>
    </div>
    
    <div id="response"></div>
    
    <script>
        async function testApproval(action) {
            const data = {
                requisition_id: document.getElementById('requisition_id').value,
                approval_level: document.getElementById('approval_level').value,
                action: action,
                remarks: document.getElementById('remarks').value
            };
            
            console.log('Sending data:', data);
            
            const responseDiv = document.getElementById('response');
            responseDiv.innerHTML = 'Sending request...';
            responseDiv.className = 'response';
            
            try {
                const response = await fetch('../api/process_approval.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                console.log('Response:', result);
                
                responseDiv.className = 'response ' + (result.success ? 'success' : 'error');
                responseDiv.textContent = JSON.stringify(result, null, 2);
                
                if (result.success) {
                    alert('Success! Check the diagnostics page to see the changes.');
                }
            } catch (error) {
                console.error('Error:', error);
                responseDiv.className = 'response error';
                responseDiv.textContent = 'Error: ' + error.message;
            }
        }
    </script>
    
    <hr>
    
    <h2>Current Session Info</h2>
    <pre><?php
    session_start();
    print_r($_SESSION);
    ?></pre>
    
    <p><a href="diagnostics.php">← Back to Diagnostics</a></p>
</body>
</html>
