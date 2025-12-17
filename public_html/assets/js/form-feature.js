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

    // This is your old commented-out character counter code, preserved as requested.
    // document.addEventListener('DOMContentLoaded', function() {
    //     document.querySelectorAll('.char-counter').forEach(function (counter) {
    //         counter.style.display = 'inline';
    //         var inputId = counter.id.replace('-counter', '');
    //         var input = document.getElementById(inputId);
    //         if (input) {
    //             var maxlength = parseInt(input.getAttribute('maxlength'), 10) || 30;

    //             // var updateCounter = function () {
    //             //     counter.textContent = input.value.length + ' / ' + maxlength;
    //             // };
    //             var updateCounter = function () {
    //                 var currentLength = 0;

    //                 // Check if the input has an IMask instance attached.
    //                 // The 'imask' property is added by the library.
    //                 if (input.imask) {
    //                     // If it's masked, count the length of the raw, unmasked value.
    //                     currentLength = input.imask.unmaskedValue.length;
    //                 } else {
    //                     // Otherwise, use the default behavior of counting the visible value's length.
    //                     currentLength = input.value.length;
    //                 }

    //                 counter.textContent = currentLength + ' / ' + maxlength;
    //             };

    //             input.addEventListener('input', updateCounter);
    //             updateCounter();
    //         }
    //     });
    // });

     /**
     * Initializes character counters.
     * This feature is a true progressive enhancement. It finds inputs that opt-in
     * via `data-char-counter="true"` and dynamically creates the counter element.
     */
    function initCharacterCounters() {
        // Find all inputs that have opted-in to having a character counter.
        document.querySelectorAll('input[data-char-counter], textarea[data-char-counter]').forEach(function (input) {

            const maxlength = parseInt(input.getAttribute('maxlength'), 10);
            if (!maxlength) {
                console.warn('Input for character counter is missing a maxlength attribute.', input);
                return;
            }

            // 1. Create the counter element dynamically.
            const counter = document.createElement('small');
            counter.className = 'form-text char-counter';
            counter.style.display = 'inline';

            // 2. Insert the new counter element into the DOM right after the input field.
            // This is more robust than assuming a specific parent structure.
            if (input.nextSibling) {
                input.parentNode.insertBefore(counter, input.nextSibling);
            } else {
                input.parentNode.appendChild(counter);
            }

            // 3. The rest of the logic remains the same.
            const updateCounter = function () {
                let currentLength = 0;
                if (input.mvcLixoMask) {
                    currentLength = input.mvcLixoMask.unmaskedValue.length;
                } else {
                    currentLength = input.value.length;
                }
                counter.textContent = currentLength + ' / ' + maxlength;
            };

            const onMaskInit = function(event) {
                if (event.detail && event.detail.maskInstance) {
                    input.mvcLixoMask = event.detail.maskInstance;
                    input.mvcLixoMask.on('accept', updateCounter);
                    input.removeEventListener('input', updateCounter);
                    updateCounter();
                }
            };

            input.addEventListener('input', updateCounter);
            input.addEventListener('imask:init', onMaskInit);

            updateCounter(); // Initial update.
        });
    }

    /**
     * Initializes live validation.
     */
    function initLiveValidation() {
        document.querySelectorAll('input[data-live-validation], textarea[data-live-validation]').forEach(function(field) {
            // Find the error container within the same parent
            var errorContainer = field.parentNode.querySelector('.live-error');
            if (!errorContainer) {
                errorContainer = document.createElement('div');
                errorContainer.className = 'live-error text-danger mt-1';
                field.parentNode.appendChild(errorContainer);
            }

            function showValidationError() {
                // Set a custom message for telephone fields with pattern mismatch
                if (field.type === 'checkbox' && field.required && !field.checked) {
                    field.setCustomValidity('Thisdddd field is required.');
                } else if (field.type === 'tel' && field.validity.patternMismatch) {
                    field.setCustomValidity('Please enter a valid international phone number (e.g., +15551234567)');
                } else {
                    field.setCustomValidity('');
                }

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
    }

    /**
     * Initializes auto-save/draft feature.
     */
    function initAutoSave() {
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
    }

    /**
     * Progressive enhancement for phone fields using intl-tel-input.
     * - Initializes intl-tel-input on all <input type="tel" data-intl-tel-input>
     * - Accepts E.164 numbers as initial value and displays them formatted.
     * - Gracefully degrades if the library is not loaded.
     */
    function initIntlTelInput() {
        if (typeof window.intlTelInput === 'undefined') {
            console.warn('intl-tel-input library not loaded.');
            return;
        }

        document.querySelectorAll('input[type="tel"][data-intl-tel-input]').forEach(function(input) {

            console.log('[intl-tel-input] Initial input value:', input.value);

            // Initialize intl-tel-input with sensible defaults
            const iti = window.intlTelInput(input, {
                initialCountry: "auto",
                nationalMode: true,
                formatOnDisplay: true,
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19/build/js/utils.js"
            });

            console.log('[intl-tel-input] After initialization, input value:', input.value);

            // If the input already has a value (E.164 from backend), set and format it
            if (input.value && input.value.startsWith('+')) {
                console.log('[intl-tel-input] Setting number for formatting:', input.value);

                iti.setNumber(input.value);
                console.log('[intl-tel-input] Formatted display value:', input.value, 'Displayed as:', iti.getNumber(intlTelInputUtils.numberFormat.INTERNATIONAL));

            }

            // Optional: On form submit, replace the value with the E.164 format
            const form = input.form;
            if (form) {
                form.addEventListener('submit', function() {
                    if (iti.isValidNumber()) {
                        input.value = iti.getNumber(); // E.164 format
                    }
                });
            }
        });
    }

    function initEmailEnhancements() {
        const domains = ['gmail.com', 'ggg.com', 'geez.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com'];

        document.querySelectorAll('input[type="email"]').forEach(function(input) {
            let isProcessing = false;

            // --- Core Fix: Helper for Lowercase and Cursor Restoration ---
            // <--- RENAMED to reflect that it now sanitizes (removes spaces)
            const sanitizeAndEnforce = () => {
                if (isProcessing) return;
                    isProcessing = true;

                // 1. Capture cursor position BEFORE value change
                const start = input.selectionStart;
                const end = input.selectionEnd;
                const originalValue = input.value;

                // 2. Change value to lowercase AND remove all spaces
                // <--- FIX: Added .replace(/\s/g, '') to strip all whitespace globally
                input.value = originalValue.toLowerCase().replace(/\s/g, '');

                // 3. Immediately try to restore cursor position
                if (originalValue !== input.value) {
                    try {
                        // This is the critical line that fixes the jump, if supported.
                        input.setSelectionRange(start, end);
                    } catch (e) {
                        // This catch is necessary for environments that block setSelectionRange on type="email"
                        // but on most modern desktop/mobile browsers, this should now succeed.
                    }
                }
                isProcessing = false;
            };

            // --- Datalist Setup (for Domain Suggestions) ---
            const datalistId = input.id + '-suggestions';
            if (!document.getElementById(datalistId)) {
                const datalist = document.createElement('datalist');
                datalist.id = datalistId;
                // Append datalist to the body, making it global and accessible
                document.body.appendChild(datalist);
                input.setAttribute('list', datalistId); // Link input to datalist
            }
            const datalist = document.getElementById(datalistId);

            // --- Logic for Updating Suggestions ---
            const updateSuggestions = (value) => {
                datalist.innerHTML = '';

                if (value.includes('@')) {
                    const [local, partialDomain] = value.split('@');

                    if (partialDomain) {
                        // Filter domains that start with the user's partial input
                        const matchingDomains = domains.filter(d => d.startsWith(partialDomain));

                        matchingDomains.forEach(domain => {
                            // Suggest the entire email address (user + domain)
                            const suggestionText = local + '@' + domain;
                            const option = document.createElement('option');
                            option.value = suggestionText;
                            datalist.appendChild(option);
                        });
                    }
                }
            };

            // --- Event Listeners ---

            // 1. Handle regular typing (input)
            input.addEventListener('input', function() {
                sanitizeAndEnforce();
                updateSuggestions(input.value);
            });

            // 2. Handle paste events
            input.addEventListener('paste', function(e) {
                // Use setTimeout to ensure the pasted content is fully in the input
                setTimeout(() => {
                    sanitizeAndEnforce(); // Enforce lowercase and restore cursor
                    updateSuggestions(input.value);
                }, 0);
            });

            // 3. Accessibility Enhancement (if no label is present)
            // If a label is not present, use an aria-label.
            if (!input.hasAttribute('aria-label') && !input.labels[0]) {
                input.setAttribute('aria-label', 'Email address');
            }

            // Initialize suggestions on load if the field is pre-filled
            updateSuggestions(input.value);
        });
    }


    /**
     * Initializes input masking.
     */
    function initInputMasking() {
        if (typeof IMask === 'undefined') {
            console.warn('IMask library not loaded.');
            return;
        }
        document.querySelectorAll('input[data-mask]').forEach(function(input) {
            var mask = input.getAttribute('data-mask');
            var maskInstance = null;

            if (mask === 'phone') {
                maskInstance = IMask(input, {
                    mask: [
                        { mask: '+1 000 000 0000' },
                        { mask: '+000 000 0000' },
                        { mask: '+000 000 0000' },
                        { mask: '+0-000-000-0000-000000000' }
                    ]
                });
            }

            if (maskInstance) {
                // Dispatch the event and pass the instance in the 'detail' property.
                var event = new CustomEvent('imask:init', {
                    bubbles: true,
                    detail: { maskInstance: maskInstance }
                });
                input.dispatchEvent(event);
            }
            // Add more masks as needed, e.g.:
            // if (mask === 'currency') { IMask(input, { mask: Number, ... }); }
        });
    }

    /**
     * Initializes AJAX form submission.
     */
    function initAjaxSave() {
        document.querySelectorAll('form[data-ajax-save="true"]').forEach(function(form) {
            const submitButton = form.querySelector('button[type="submit"]');

            if (!submitButton) {
                console.warn('AJAX form is missing a submit button.', form);
                return;
            }

            // Function to update the submit button's state based on form validity.
            const updateButtonState = () => {
                // The button is disabled if the form is invalid.
                submitButton.disabled = !form.checkValidity();
            };

            // Listen for any input on the form to re-evaluate its validity.
            form.addEventListener('input', updateButtonState);

            // Set the initial state of the button on page load.
            updateButtonState();



            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent normal browser submission


                // Manually trigger browser validation. If the form is invalid, stop right here.
                // The browser will automatically display the validation bubbles on the invalid fields.
                if (!form.checkValidity()) {
                    return;
                }


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
                // .then(result => {
                //     console.log('Result before destructuring:', result);
                //     return result;
                // })
                .then(({ status, body }) => {
                    if (status === 200 && body.success) {

                        // --- Add this block: Re-format phone input after AJAX save ---
                        document.querySelectorAll('input[type="tel"][data-intl-tel-input]').forEach(function(input) {
                        if (window.intlTelInput && input.value && input.value.startsWith('+')) {
                            // Get the existing intl-tel-input instance
                            if (input.intlTelInput) {
                            input.intlTelInput.setNumber(input.value);
                            } else {
                            // If not present, re-initialize (shouldn't be needed, but safe fallback)
                            const iti = window.intlTelInput(input, {
                                initialCountry: "auto",
                                nationalMode: true,
                                formatOnDisplay: true,
                                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19/build/js/utils.js"
                            });
                            iti.setNumber(input.value);
                            }
                        }
                        });
                        // --- End block ---



                        // SUCCESS: Display message and redirect if specified
                        // alert(body.message || 'Record saved successfully!');
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
                        // alert(body.message || 'Please correct the errors and try again.');
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
    }

    /**
     * Main entry point: Initializes all form features once the DOM is ready.
     * This single listener ensures a predictable execution order.
     */
    document.addEventListener('DOMContentLoaded', function() {
        initCharacterCounters(); // Sets up listeners first
        initLiveValidation();
        initAutoSave();
        initInputMasking();      // Runs second, dispatching events that the counters can now hear
        initIntlTelInput();
        initEmailEnhancements();
        initAjaxSave();
    });


    // This is your old commented-out AJAX save code, preserved as requested.
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


    //             fetch('/testy/ajax-save-draft', {
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
    //                     // Optionally: window.location.href = '/testy'; // redirect
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