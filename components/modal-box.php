<?php
/**
 * Modal Box Component
 * Reusable confirmation/acknowledgment modal dialog
 * 
 * Usage:
 * 1. Include this component in your page: include 'components/modal-box.php';
 * 2. Include modal-box.js script tag
 * 3. Call showModalBox function from JavaScript with options:
 *    - title: string
 *    - message: string  
 *    - type: 'yesno' or 'acknowledge'
 *    - onConfirm: callback function
 *    - onCancel: callback function (optional, for yesno type)
 */

// Only create container if it doesn't exist yet
// This prevents duplicate containers when component is included multiple times
?>

<div class="modal-box-overlay" id="modal-box-overlay">
    <div class="modal-box-container" id="modal-box-container">
        <div class="modal-box-header">
            <h2 class="modal-box-title" id="modal-box-title"></h2>
        </div>
        <div class="modal-box-body">
            <p class="modal-box-message" id="modal-box-message"></p>
        </div>
        <div class="modal-box-footer" id="modal-box-footer">
            <!-- Buttons will be dynamically added here by JavaScript -->
        </div>
    </div>
</div>
