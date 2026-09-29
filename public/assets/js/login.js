(() => {
    'use strict';

    const form = document.querySelector('#login-form');

    if (!form) {
        return;
    }

    const fieldNames = ['email', 'password'];
    const summary = document.querySelector('#form-error-summary');
    const summaryList = summary.querySelector('[data-error-list]');
    const password = form.elements.namedItem('password');
    const passwordToggle = document.querySelector('#password-toggle');
    const credentialsError = document.querySelector('#credentials_error');

    // Field errors and the general credentials error are displayed in the same summary.
    // This mirrors LoginValidator for quick feedback; the server remains authoritative.
    const messageFor = (name) => {
        const value = form.elements.namedItem(name).value;

        if (name === 'email') {
            const email = value.trim();

            if (!email) {
                return 'Please enter your email address.';
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                return 'Please enter a valid email address, for example name@example.com.';
            }

            if (email.length > 254) {
                return 'Email address must not exceed 254 characters.';
            }
        }

        if (name === 'password' && value === '') {
            return 'Please enter your password.';
        }

        return '';
    };

    const setError = (name, message) => {
        const element = form.elements.namedItem(name);
        const error = document.querySelector('#' + name + '_error');

        element.setAttribute('aria-invalid', 'true');
        element.setAttribute('aria-describedby', name + '_error');
        error.textContent = message;
        error.hidden = false;
    };

    const clearError = (name) => {
        const element = form.elements.namedItem(name);
        const error = document.querySelector('#' + name + '_error');

        element.removeAttribute('aria-invalid');
        element.removeAttribute('aria-describedby');
        error.textContent = '';
        error.hidden = true;
    };

    // Remove the generic server error as soon as either credential is changed.
    const clearCredentialsError = () => {
        credentialsError.textContent = '';
        credentialsError.hidden = true;
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

    // Build links to each problem for keyboard and screen-reader users.
    const renderSummary = () => {
        summaryList.replaceChildren();

        fieldNames.forEach((name) => {
            const error = document.querySelector('#' + name + '_error');

            if (!error.hidden && error.textContent) {
                const item = document.createElement('li');
                const link = document.createElement('a');
                link.href = '#' + name;
                link.textContent = error.textContent;
                item.append(link);
                summaryList.append(item);
            }
        });

        if (!credentialsError.hidden && credentialsError.textContent) {
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = '#email';
            link.textContent = credentialsError.textContent;
            item.append(link);
            summaryList.append(item);
        }

        summary.hidden = summaryList.children.length === 0;
    };

    fieldNames.forEach((name) => {
        const element = form.elements.namedItem(name);

        element.addEventListener('blur', () => {
            validateField(name);
            renderSummary();
        });

        element.addEventListener('input', () => {
            clearCredentialsError();

            if (element.getAttribute('aria-invalid') === 'true') {
                validateField(name);
            }

            renderSummary();
        });
    });

    // Local checks avoid a round trip, while PHP still validates every submission.
    form.addEventListener('submit', (event) => {
        let hasErrors = false;

        clearCredentialsError();

        fieldNames.forEach((name) => {
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

    passwordToggle.addEventListener('click', () => {
        const showing = password.type === 'text';
        password.type = showing ? 'password' : 'text';
        passwordToggle.textContent = showing ? 'Show' : 'Hide';
        passwordToggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
        password.focus();
    });
})();
