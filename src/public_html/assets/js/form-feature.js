/**
 * Form Features JS Scaffold
 * For MVC LIXO Framework
 * - Character counters
 * - Live validation
 * - Input masking
 * - Other enhancements
 */

// Immediately Invoked Function Expression (IIFE) to avoid polluting global scope
(function() {
    'use strict';

    // Character Counter Feature - JS
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.char-counter').forEach(function (counter) {
            counter.style.display = 'inline';
            var inputId = counter.id.replace('-counter', '');
            var input = document.getElementById(inputId);
            if (input) {
                var maxlength = parseInt(input.getAttribute('maxlength'), 10) || 30;
                var updateCounter = function () {
                    counter.textContent = input.value.length + ' / ' + maxlength;
                };
                input.addEventListener('input', updateCounter);
                updateCounter();
            }
        });
    });

    // Live Validation Feature - JS
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[data-live-validation], textarea[data-live-validation]').forEach(function(field) {
            // Find the error container within the same parent
            var errorContainer = field.parentNode.querySelector('.live-error');
            if (!errorContainer) {
                errorContainer = document.createElement('div');
                errorContainer.className = 'live-error text-danger mt-1';
                field.parentNode.appendChild(errorContainer);
            }

            function showValidationError() {
                if (!field.checkValidity()) {
                    errorContainer.textContent = field.validationMessage;
                    field.classList.add('is-invalid');
                } else {
                    errorContainer.textContent = '';
                    field.classList.remove('is-invalid');
                }
            }

            field.addEventListener('input', showValidationError);
            field.addEventListener('blur', showValidationError);
            showValidationError();
        });
    });

    // Auto Save / Draft Feature - JS
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[data-auto-save="true"]').forEach(function(form) {
            if (form.getAttribute('data-use-local-storage') !== 'true') {
                return;
            }
            var key = 'draft_' + (form.getAttribute('id') || form.getAttribute('name') || window.location.pathname);
            var draft = localStorage.getItem(key);

            var notification = document.getElementById('draft-notification');
            var discardBtn = document.getElementById('discard-draft-btn');

            if (draft) {
                Object.entries(JSON.parse(draft)).forEach(([name, value]) => {
                    var field = form.elements[name];
                    if (field && typeof field.value !== 'undefined') {
                        field.value = value;
                        // Trigger validation after restoring value
                        if (typeof field.dispatchEvent === 'function') {
                            field.dispatchEvent(new Event('input', { bubbles: true }));
                            field.dispatchEvent(new Event('blur', { bubbles: true }));
                        }
                    }
                });
                if (notification) {
                    notification.textContent = 'Draft restored. You are viewing unsaved changes.';
                    notification.style.display = 'block';
                }
                if (discardBtn) {
                    discardBtn.style.display = 'inline-block';
                    discardBtn.onclick = function() {
                        localStorage.removeItem(key);
                        window.location.reload();
                    };
                }
            }

            form.addEventListener('input', function() {
                var data = {};
                Array.from(form.elements).forEach(function(el) {
                    if (el.name && typeof el.value !== 'undefined') {
                        data[el.name] = el.value;
                    }
                });
                localStorage.setItem(key, JSON.stringify(data));
            });
        });
    });

    // AJAX Save Feature - JS
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[data-ajax-save="true"]').forEach(function(form) {
            // Save draft on input (or you can use a "Save Draft" button)
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent normal submit

                var spinner = document.getElementById('ajax-save-spinner');
                if (spinner) {
                    spinner.style.display = 'block';
                }


                var data = {};
                Array.from(form.elements).forEach(function(el) {
                    if (el.name && typeof el.value !== 'undefined') {
                        data[el.name] = el.value;
                    }
                });

                // Add CSRF token if present
                var csrfInput = form.querySelector('input[name="csrf_token"]');
                var csrfToken = csrfInput ? csrfInput.value : '';
                data['csrf_token'] = csrfToken;


                fetch('/testys/ajax-save-draft', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                })
                //.then(response => response.json())
                .then(response => {
                    // Try to parse as JSON, but handle non-JSON error gracefully
                    return response.json().catch(() => {
                        throw new Error('Non-JSON response (likely CSRF error)');
                    });
                })
                .then(result => {
                    if (spinner) {
                        spinner.style.display = 'none';
                    }
                    if (result.success) {
                        alert('Record saved via AJAX!');
                        if (form.getAttribute('data-use-local-storage') === 'true') {
                            var key = 'draft_' + (form.getAttribute('id') || form.getAttribute('name') || window.location.pathname);
                            localStorage.removeItem(key);
                        }
                        // Optionally: window.location.href = '/testys'; // redirect
                    } else {
                        alert('Failed to save record.');
                    }
                })
                .catch(error => {
                    if (spinner) {
                        spinner.style.display = 'none';
                    }
                    alert('AJAX error: ' + error);
                });
            });
        });
    });
    // TODO: Add more features here (e.g., live validation, input masking)
})();