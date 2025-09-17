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
                    notification.textContent = 'Unsaved data restored. You are viewing unsaved changes.';
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

    // Input Masking Feature - imaskjs integration
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof IMask === 'undefined') {
            console.warn('IMask library not loaded.');
            return;
        }
        document.querySelectorAll('input[data-mask]').forEach(function(input) {
            var mask = input.getAttribute('data-mask');
            if (mask === 'phone') {
                IMask(input, { mask: '+0-000-000-0000' });
            }
            // Add more masks as needed, e.g.:
            // if (mask === 'currency') { IMask(input, { mask: Number, ... }); }
        });
    });



    // AJAX Save Feature - JS
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[data-ajax-save="true"]').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent normal browser submission

                // This is the key change: Read the URL directly from the form's action attribute.
                //const fetchUrl = form.action;
                const fetchUrl = form.dataset.ajaxAction || form.action;

                var spinner = document.getElementById('ajax-save-spinner');
                if (spinner) {
                    spinner.style.display = 'inline-block';
                }

                // Clear previous validation errors
                form.querySelectorAll('.live-error').forEach(el => el.textContent = '');
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                var formData = new FormData(form);
                var data = Object.fromEntries(formData.entries());

                // Find the CSRF token from the hidden input field in the form
                const csrfToken = form.querySelector('input[name="csrf_token"]')?.value || '';


                fetch(fetchUrl, {
                    method: 'POST', // Use POST for compatibility as we decided
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (spinner) spinner.style.display = 'none';
                    // Return the response status and the parsed JSON body together
                    // return response.json().then(json => ({ status: response.status, body: json }));
                    // TEMPORARY DEBUGGING: Log the raw text response to the console.
                    return response.text().then(text => {
                        console.log('--- RAW SERVER RESPONSE ---');
                        console.log(text);
                        console.log('---------------------------');
                        // Now, try to parse it as JSON. This will likely throw the error if the text is not valid JSON.
                        const json = JSON.parse(text);
                        return { status: response.status, body: json };
                    });
                })
                .then(({ status, body }) => {
                    if (status === 200 && body.success) {
                        // SUCCESS: Display message and redirect if specified
                        alert(body.message || 'Record saved successfully!');
                        if (form.getAttribute('data-use-local-storage') === 'true') {
                            var key = 'draft_' + (form.getAttribute('id') || form.getAttribute('name') || window.location.pathname);
                            localStorage.removeItem(key); // Clear auto-saved data
                        }
                        if (body.redirect_url) {
                            window.location.href = body.redirect_url;
                        }
                    } else if (status === 422 && body.errors) {
                        // VALIDATION FAILURE: Display errors next to fields
                        /*
                        // TODO revisit error containers, try to reuse the same way they are done in tradional js'less forms
                        -- Always generate an error container (e.g., <div class="live-error"></div>) for each field in your form template, even if it’s empty by default.
                        -- When rendering server-side errors, populate this container with the error message.
                        -- When handling AJAX errors, your JS should find and update this same container.
                        */
                            Object.entries(body.errors).forEach(([field, messages]) => {
                            var input = form.elements[field];
                            if (input) {
                                input.classList.add('is-invalid');
                                var errorContainer = input.parentNode.querySelector('.live-error');
                                if (!errorContainer) {
                                    errorContainer = document.createElement('div');
                                    errorContainer.className = 'live-error text-danger mt-1';
                                    input.parentNode.appendChild(errorContainer);
                                }
                                errorContainer.textContent = Array.isArray(messages) ? messages.join(', ') : messages;
                            }
                        });
                        // Accessibility: Focus first invalid field
                        var firstInvalid = form.querySelector('.is-invalid');
                        if (firstInvalid) {
                            firstInvalid.focus();
                        }
                        alert(body.message || 'Please correct the errors and try again.');
                    } else {
                        // OTHER ERRORS (500, 403, etc.)
                        alert(body.message || 'An unexpected error occurred.');
                    }
                })
                .catch(error => {
                    if (spinner) spinner.style.display = 'none';
                    console.error('AJAX Error:', error);
                    alert('A network or server error occurred. Please try again.');
                });
            });
        });
    });



    // document.addEventListener('DOMContentLoaded', function() {
    //     document.querySelectorAll('form[data-ajax-save="true"]').forEach(function(form) {
    //         // Save draft on input (or you can use a "Save Draft" button)
    //         form.addEventListener('submit', function(event) {
    //             event.preventDefault(); // Prevent normal submit

    //             var spinner = document.getElementById('ajax-save-spinner');
    //             if (spinner) {
    //                 spinner.style.display = 'block';
    //             }


    //             var data = {};
    //             Array.from(form.elements).forEach(function(el) {
    //                 if (el.name && typeof el.value !== 'undefined') {
    //                     data[el.name] = el.value;
    //                 }
    //             });

    //             // Add CSRF token if present
    //             var csrfInput = form.querySelector('input[name="csrf_token"]');
    //             var csrfToken = csrfInput ? csrfInput.value : '';
    //             data['csrf_token'] = csrfToken;


    //             fetch('/testys/ajax-save-draft', {
    //                 method: 'POST',
    //                 headers: {
    //                     'Content-Type': 'application/json',
    //                     'X-Requested-With': 'XMLHttpRequest'
    //                 },
    //                 body: JSON.stringify(data)
    //             })
    //             //.then(response => response.json())
    //             .then(response => {
    //                 // Try to parse as JSON, but handle non-JSON error gracefully
    //                 return response.json().catch(() => {
    //                     throw new Error('Non-JSON response (likely CSRF error)');
    //                 });
    //             })
    //             .then(result => {
    //                 if (spinner) {
    //                     spinner.style.display = 'none';
    //                 }
    //                 if (result.success) {
    //                     alert('Record saved via AJAX!');
    //                     if (form.getAttribute('data-use-local-storage') === 'true') {
    //                         var key = 'draft_' + (form.getAttribute('id') || form.getAttribute('name') || window.location.pathname);
    //                         localStorage.removeItem(key);
    //                     }
    //                     // Optionally: window.location.href = '/testys'; // redirect
    //                 } else {
    //                     alert('Failed to save record.');
    //                 }
    //             })
    //             .catch(error => {
    //                 if (spinner) {
    //                     spinner.style.display = 'none';
    //                 }
    //                 alert('AJAX error: ' + error);
    //             });
    //         });
    //     });
    // });
    // TODO: Add more features here (e.g., live validation, input masking)
})();