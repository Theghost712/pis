/**
 * Patient Information Sharing System
 * Main JavaScript File
 */

$(document).ready(function() {
    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching records found"
            },
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    }

    // Initialize tooltips
    if ($.fn.tooltip) {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // CSRF token for AJAX
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // AJAX setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    // Form validation
    $('form.validated').on('submit', function(e) {
        let valid = true;
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val() || $(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                valid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Check email format
        $(this).find('input[type="email"]').each(function() {
            const email = $(this).val();
            if (email && !isValidEmail(email)) {
                $(this).addClass('is-invalid');
                valid = false;
            }
        });
        
        // Check password match
        const password = $(this).find('input[name="password"]').val();
        const confirm = $(this).find('input[name="password_confirmation"]').val();
        if (password && confirm && password !== confirm) {
            $(this).find('input[name="password_confirmation"]').addClass('is-invalid');
            valid = false;
        }
        
        if (!valid) {
            e.preventDefault();
            showNotification('error', 'Please fix all validation errors.');
        }
    });

    // Real-time validation feedback
    $('form.validated input, form.validated select, form.validated textarea').on('blur', function() {
        const field = $(this);
        if (field.attr('required') && !field.val()) {
            field.addClass('is-invalid');
        } else if (field.attr('type') === 'email' && field.val() && !isValidEmail(field.val())) {
            field.addClass('is-invalid');
        } else {
            field.removeClass('is-invalid');
        }
    });

    // Password strength indicator
    $('input[name="password"]').on('keyup', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        const indicator = $(this).closest('.mb-3').find('.password-strength');
        
        if (indicator.length) {
            indicator.removeClass('weak medium strong');
            if (password.length === 0) {
                indicator.html('');
            } else if (strength === 'weak') {
                indicator.addClass('weak').html('⚠️ Weak');
            } else if (strength === 'medium') {
                indicator.addClass('medium').html('⚠️ Medium');
            } else {
                indicator.addClass('strong').html('✅ Strong');
            }
        }
    });

    // AJAX form submissions
    $('form.ajax-submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const method = form.attr('method') || 'POST';
        const data = form.serialize();
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');
        
        $.ajax({
            type: method,
            url: url,
            data: data,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    } else if (response.reload) {
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else if (response.reset) {
                        form[0].reset();
                    }
                } else {
                    showNotification('error', response.message || 'An error occurred.');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Confirm delete
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });

    // Confirm action
    $('.confirm-action').on('click', function(e) {
        const message = $(this).data('confirm-message') || 'Are you sure you want to proceed?';
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert:not(.alert-persistent)').fadeOut('slow');
    }, 5000);
});

/**
 * Validate email format
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Check password strength
 */
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[@$!%*?&]/.test(password)) strength++;
    
    if (strength <= 2) return 'weak';
    if (strength <= 4) return 'medium';
    return 'strong';
}

/**
 * Show notification
 */
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                       type === 'error' ? 'alert-danger' : 
                       type === 'warning' ? 'alert-warning' : 
                       'alert-info';
    
    const icon = type === 'success' ? 'fa-check-circle' :
                 type === 'error' ? 'fa-exclamation-circle' :
                 type === 'warning' ? 'fa-exclamation-triangle' :
                 'fa-info-circle';
    
    const html = `
        <div class="alert ${alertClass} alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = $('#notification-area');
    if (container.length) {
        container.html(html);
        $('.alert').fadeIn('fast');
    } else {
        // Fallback - prepend to main content
        $('main').prepend(html);
    }
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Format date
 */
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Format datetime
 */
function formatDateTime(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
    const classes = {
        'active': 'bg-success',
        'pending': 'bg-warning',
        'accepted': 'bg-info',
        'completed': 'bg-success',
        'declined': 'bg-danger',
        'cancelled': 'bg-danger',
        'revoked': 'bg-danger',
        'expired': 'bg-secondary'
    };
    const cls = classes[status] || 'bg-secondary';
    const label = status.charAt(0).toUpperCase() + status.slice(1);
    return `<span class="badge ${cls}">${label}</span>`;
}

/**
 * Export to CSV
 */
function exportToCSV(data, filename) {
    if (!data || data.length === 0) {
        showNotification('warning', 'No data to export.');
        return;
    }
    
    const csv = [];
    const headers = Object.keys(data[0]);
    csv.push(headers.join(','));
    
    data.forEach(row => {
        const values = headers.map(header => {
            let value = row[header] || '';
            // Handle nested objects
            if (typeof value === 'object') {
                value = JSON.stringify(value);
            }
            return `"${String(value).replace(/"/g, '""')}"`;
        });
        csv.push(values.join(','));
    });
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename || 'export.csv';
    link.click();
}

/**
 * Print element
 */
function printElement(elementId) {
    const content = document.getElementById(elementId);
    if (!content) {
        showNotification('error', 'Element not found.');
        return;
    }
    
    const win = window.open('', '_blank', 'width=800,height=600');
    win.document.write(`
        <html>
            <head>
                <title>Print</title>
                <link rel="stylesheet" href="/assets/css/style.css">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container py-4">
                    ${content.innerHTML}
                </div>
                <script>
                    window.onload = function() { window.print(); }
                <\/script>
            </body>
        </html>
    `);
    win.document.close();
}

/**
 * Show loading overlay
 */
function showLoading() {
    const overlay = `
        <div class="spinner-overlay" id="loadingOverlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    if (!$('#loadingOverlay').length) {
        $('body').append(overlay);
    }
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    $('#loadingOverlay').remove();
}

/**
 * Load with AJAX
 */
function loadContent(url, target, callback) {
    showLoading();
    $.ajax({
        type: 'GET',
        url: url,
        success: function(html) {
            $(target).html(html);
            if (typeof callback === 'function') {
                callback();
            }
        },
        error: function() {
            showNotification('error', 'Failed to load content.');
        },
        complete: function() {
            hideLoading();
        }
    });
}

// Export functions for global use
window.showNotification = showNotification;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.getStatusBadge = getStatusBadge;
window.exportToCSV = exportToCSV;
window.printElement = printElement;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.loadContent = loadContent;
window.checkPasswordStrength = checkPasswordStrength;
window.isValidEmail = isValidEmail;