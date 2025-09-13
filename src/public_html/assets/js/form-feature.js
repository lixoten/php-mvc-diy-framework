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

    // Character Counter Feature
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-char-counter]').forEach(function(input) {
            const counterId = input.getAttribute('data-char-counter');
            const counter = document.getElementById(counterId);
            if (!counter) return;

            const maxlength = parseInt(input.getAttribute('maxlength'), 10) || 30;
            const minlength = parseInt(input.getAttribute('minlength'), 10) || 0;

            function updateCounter() {
                const currentLength = input.value.length;
                counter.textContent = `${currentLength} / ${maxlength}`;

                // Validation feedback
                if (currentLength < minlength) {
                    counter.classList.add('text-danger');
                    counter.classList.remove('text-success');
                    counter.title = `Minimum ${minlength} characters required.`;
                } else if (currentLength > maxlength) {
                    counter.classList.add('text-danger');
                    counter.classList.remove('text-success');
                    counter.title = `Maximum ${maxlength} characters allowed.`;
                } else {
                    counter.classList.remove('text-danger');
                    counter.classList.add('text-success');
                    counter.title = '';
                }
            }

            input.addEventListener('input', updateCounter);
            updateCounter();
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[data-live-validation], textarea[data-live-validation]').forEach(function(field) {
            // // Build error container ID based on field ID
            // var errorId = field.id + '-error';
            // var errorContainer = document.getElementById(errorId);
            // if (!errorContainer) {
            //     errorContainer = document.createElement('div');
            //     errorContainer.className = 'live-error text-danger mt-1';
            //     errorContainer.id = errorId;
            //     field.parentNode.appendChild(errorContainer);
            // }
//..........................................................................
//..........................................................................
//..........................................................................

            // Find the error container within the same parent
            var errorContainer = field.parentNode.querySelector('.live-error');
            if (!errorContainer) {
                errorContainer = document.createElement('div');
                errorContainer.className = 'live-error text-danger mt-1';
                field.parentNode.appendChild(errorContainer);
            }
//..........................................................................
//..........................................................................
//..........................................................................


            // // Only validate fields with live_validation enabled (optional: use data-live-validation)
            // if (!field.hasAttribute('data-live-validation')) {
            //     return;
            // }

            // // Find or create error container
            // let errorContainer = field.nextElementSibling;
            // if (!errorContainer || !errorContainer.classList.contains('live-error')) {
            //     errorContainer = document.createElement('div');
            //     errorContainer.className = 'live-error text-danger mt-1';
            //     field.parentNode.insertBefore(errorContainer, field.nextSibling);
            // }

//..........................................................................
//..........................................................................
//..........................................................................



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

    // LocalStorage
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

    // // LocalStorage
    // document.addEventListener('DOMContentLoaded', function() {
    //     document.querySelectorAll('form[data-auto-save="true"]').forEach(function(form) {
    //         if (form.getAttribute('data-use-local-storage') !== 'true') {
    //             return;
    //         }
    //         var key = 'draft_' + (form.getAttribute('id') || form.getAttribute('name') || 'default');
    //         // Restore draft
    //         var draft = localStorage.getItem(key);
    //         if (draft) {
    //             Object.entries(JSON.parse(draft)).forEach(([name, value]) => {
    //                 var field = form.elements[name];
    //                 if (field) {
    //                     field.value = value;
    //                 }
    //             });
    //         }
    //         // Save draft on input
    //         form.addEventListener('input', function() {
    //             var data = {};
    //             Array.from(form.elements).forEach(function(el) {
    //                 if (el.name) {
    //                     data[el.name] = el.value;
    //                 }
    //             });
    //             localStorage.setItem(key, JSON.stringify(data));
    //         });
    //     });
    // });

    // TODO: Add more features here (e.g., live validation, input masking)
})();