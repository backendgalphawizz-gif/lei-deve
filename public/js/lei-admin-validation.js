(function () {
    'use strict';

    var PHONE_NAMES = ['phone', 'support_phone', 'mobile', 'mobile_no', 'mobile_number'];
    var OTP_NAMES = ['code', 'otp'];

    function isPhoneField(input) {
        if (!input || !input.name) return false;
        if (input.getAttribute('data-type') === 'phone') return true;
        if ((input.getAttribute('data-rules') || '').indexOf('phone') !== -1) return true;
        return PHONE_NAMES.indexOf(input.name) !== -1;
    }

    function isOtpField(input) {
        if (!input || !input.name) return false;
        if (input.getAttribute('data-type') === 'otp') return true;
        if ((input.getAttribute('data-rules') || '').indexOf('digits:') !== -1) return true;
        return OTP_NAMES.indexOf(input.name) !== -1;
    }

    function rulesFor(input) {
        var rules = [];
        var explicit = input.getAttribute('data-rules');
        if (explicit) {
            explicit.split('|').forEach(function (rule) {
                if (rule) rules.push(rule.trim());
            });
        }
        if (input.required && rules.indexOf('required') === -1) {
            rules.push('required');
        }
        return rules;
    }

    function applyHtmlConstraints(input) {
        var rules = rulesFor(input);

        if (rules.indexOf('required') !== -1) {
            input.required = true;
            input.setAttribute('required', 'required');
        }

        rules.forEach(function (rule) {
            var parts = rule.split(':');
            var name = parts[0];
            var arg = parts[1];

            if (name === 'maxLen' && arg) {
                input.maxLength = parseInt(arg, 10);
            }
            if (name === 'minLen' && arg) {
                input.minLength = parseInt(arg, 10);
            }
            if (name === 'min' && arg && input.type === 'number') {
                input.min = arg;
            }
            if (name === 'max' && arg && input.type === 'number') {
                input.max = arg;
            }
            if (name === 'email') {
                input.type = 'email';
            }
            if (name === 'url') {
                input.type = 'url';
            }
        });

        if (isPhoneField(input)) {
            input.type = 'tel';
            input.inputMode = 'numeric';
            input.maxLength = 10;
            input.pattern = '[0-9]{10}';
            input.title = 'Enter a 10-digit mobile number.';
        }

        if (isOtpField(input)) {
            input.inputMode = 'numeric';
            input.maxLength = 6;
            input.pattern = '[0-9]{6}';
            input.title = 'Enter the 6-digit verification code.';
        }

        if ((input.getAttribute('data-rules') || '').indexOf('password:strong') !== -1
            || (input.getAttribute('data-rules') || '').indexOf('password:8') !== -1) {
            input.minLength = 8;
            input.title = 'Password must be at least 8 characters.';
        }

        if ((input.getAttribute('data-rules') || '').indexOf('slug') !== -1) {
            input.pattern = '[a-z0-9]+(?:-[a-z0-9]+)*';
            input.title = 'Use lowercase letters, numbers, and hyphens only.';
        }
    }

    function bindDigitsOnly(input, max) {
        input.addEventListener('input', function () {
            var digits = input.value.replace(/\D/g, '').slice(0, max);
            if (input.value !== digits) {
                input.value = digits;
            }
            input.setCustomValidity('');
        });
    }

    function bindPasswordMatch(form) {
        var password = form.querySelector('[name="password"]');
        var confirm = form.querySelector('[name="password_confirmation"]');
        if (!password || !confirm) return;

        function check() {
            if (confirm.value && confirm.value !== password.value) {
                confirm.setCustomValidity('Passwords do not match.');
            } else {
                confirm.setCustomValidity('');
            }
        }

        password.addEventListener('input', check);
        confirm.addEventListener('input', check);
    }

    function bindForm(form) {
        if (form.dataset.leiValidateBound === '1') return;
        form.dataset.leiValidateBound = '1';

        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            applyHtmlConstraints(field);

            if (isPhoneField(field)) {
                bindDigitsOnly(field, 10);
            }
            if (isOtpField(field)) {
                bindDigitsOnly(field, 6);
            }

            field.addEventListener('input', function () {
                field.setCustomValidity('');
            });
        });

        bindPasswordMatch(form);

        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
            }
        });
    }

    function init() {
        var selector = [
            '.lei-content form[method="post"]:not([data-no-validate])',
            'body.lei-login-page form[method="post"]:not([data-no-validate])',
            'body.lei-public form[method="post"]:not([data-no-validate])',
        ].join(', ');

        document.querySelectorAll(selector).forEach(function (form) {
            if ((form.getAttribute('action') || '').indexOf('logout') !== -1) return;
            bindForm(form);
        });
    }

    window.LeiFormValidate = {
        init: init,
        bindForm: bindForm,
    };
    window.LeiAdminValidate = window.LeiFormValidate;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
