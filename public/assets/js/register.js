(() => {
    'use strict';

    const form = document.querySelector('#registration-form');

    if (!form) {
        return;
    }

    const countryCodes = {
        cyprus: '357',
        greece: '30',
        united_kingdom: '44',
    };

    const fieldOrder = [
        'first_name',
        'last_name',
        'country',
        'country_code',
        'phone',
        'email',
        'password',
        'terms',
    ];
    // This order is also used when building the error summary and choosing the first problem.

    const summary = document.querySelector('#form-error-summary');
    const summaryList = summary?.querySelector('[data-error-list]');
    const country = form.elements.namedItem('country');
    const countryCode = form.elements.namedItem('country_code');
    const password = form.elements.namedItem('password');
    const passwordToggle = document.querySelector('#password-toggle');

    const elementFor = (name) => form.elements.namedItem(name);

    // Keep these messages aligned with RegistrationValidator on the server.
    const messageFor = (name) => {
        const element = elementFor(name);
        const value = element.type === 'checkbox'
            ? element.checked
            : name === 'password' ? element.value : element.value.trim();

        switch (name) {
            case 'first_name':
                if (value.length <= 3) {
                    return 'First name must contain more than 3 characters.';
                }
                if (value.length > 100) {
                    return 'First name must not exceed 100 characters.';
                }
                break;
            case 'last_name':
                if (value.length <= 3) {
                    return 'Last name must contain more than 3 characters.';
                }
                if (value.length > 100) {
                    return 'Last name must not exceed 100 characters.';
                }
                break;
            case 'country':
                if (!countryCodes[value]) {
                    return 'Please select a valid country.';
                }
                break;
            case 'country_code':
                if (!/^\d+$/.test(value)) {
                    return 'Country code must contain numbers only.';
                }
                if (countryCodes[country.value] && value !== countryCodes[country.value]) {
                    return 'Country code does not match the selected country.';
                }
                break;
            case 'phone':
                if (!/^\d+$/.test(value)) {
                    return 'Phone number must contain numbers only.';
                }
                if (value.length < 6 || value.length > 15) {
                    return 'Phone number must be between 6 and 15 digits.';
                }
                break;
            case 'email':
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    return 'Please enter a valid email address, for example name@example.com.';
                }
                if (value.length > 254) {
                    return 'Email address must not exceed 254 characters.';
                }
                break;
            case 'password':
                if (value.length < 8) {
                    return 'Password must be at least 8 characters.';
                }
                if (!/[A-Z]/.test(value)) {
                    return 'Password must include at least one capital letter.';
                }
                if (!/[0-9]/.test(value)) {
                    return 'Password must include at least one number.';
                }
                if (!/[^A-Za-z0-9]/.test(value)) {
                    return 'Password must include at least one symbol.';
                }
                break;
            case 'terms':
                if (!value) {
                    return 'You must accept the Privacy Policy and Terms and Conditions.';
                }
                break;
        }

        return '';
    };

    const setError = (name, message) => {
        const element = elementFor(name);
        const error = document.querySelector('#' + name + '_error');

        element.setAttribute('aria-invalid', 'true');
        element.setAttribute('aria-describedby', name + '_error');
        error.textContent = message;
        error.hidden = false;
    };

    const clearError = (name) => {
        const element = elementFor(name);
        const error = document.querySelector('#' + name + '_error');

        element.removeAttribute('aria-invalid');
        element.removeAttribute('aria-describedby');
        error.textContent = '';
        error.hidden = true;
    };

    const validateField = (name) => {
        const message = messageFor(name);

        if (message) {
            setError(name, message);
            return message;
        }

        clearError(name);
        return '';
    };

    // Rebuild the summary from visible field errors so its links never become stale.
    const renderSummary = () => {
        if (!summary || !summaryList) {
            return;
        }

        summaryList.replaceChildren();

        fieldOrder.forEach((name) => {
            const error = document.querySelector('#' + name + '_error');

            if (error.hidden || !error.textContent) {
                return;
            }

            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = '#' + elementFor(name).id;
            link.textContent = error.textContent;
            item.append(link);
            summaryList.append(item);
        });

        summary.hidden = summaryList.children.length === 0;
    };

    // CSS draws the plus sign; the input itself contains only the numeric calling code.
    country.addEventListener('change', () => {
        countryCode.value = countryCodes[country.value] ?? '';
        validateField('country');
        validateField('country_code');
        renderSummary();
    });

    if (country.value && !countryCode.value) {
        countryCode.value = countryCodes[country.value] ?? '';
    }

    fieldOrder.forEach((name) => {
        const element = elementFor(name);
        const eventName = element.type === 'checkbox' || element.tagName === 'SELECT'
            ? 'change'
            : 'blur';

        element.addEventListener(eventName, () => {
            validateField(name);
            renderSummary();
        });

        if (eventName === 'blur') {
            element.addEventListener('input', () => {
                if (element.getAttribute('aria-invalid') === 'true') {
                    validateField(name);
                    renderSummary();
                }
            });
        }
    });

    // Stop only invalid browser submissions; PHP repeats every rule before saving.
    form.addEventListener('submit', (event) => {
        let hasErrors = false;

        fieldOrder.forEach((name) => {
            if (validateField(name)) {
                hasErrors = true;
            }
        });

        renderSummary();

        if (hasErrors) {
            event.preventDefault();
            summary.focus();
        }
    });

    passwordToggle?.addEventListener('click', () => {
        const showing = password.type === 'text';
        password.type = showing ? 'password' : 'text';
        passwordToggle.textContent = showing ? 'Show' : 'Hide';
        passwordToggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
        password.focus();
    });
})();
