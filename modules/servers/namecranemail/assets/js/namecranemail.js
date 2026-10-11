/*
 * NameCrane Mail client area: password fields ([data-ncm-password]) start hidden, with a
 * show/hide button and a generate button. A generated password is shown so it can be noted.
 * Passwords mix lower case, upper case, digits and symbols, as the mail server wants.
 */
(function () {
    'use strict';

    var sets = ['abcdefghjkmnpqrstuvwxyz', 'ABCDEFGHJKMNPQRSTUVWXYZ', '23456789', '!#$%&()*+-.;<=>?@^_~'];

    function random(max) {
        var value = new Uint32Array(1);
        window.crypto.getRandomValues(value);
        return value[0] % max;
    }

    function generate(length) {
        var all = sets.join('');
        var chars = sets.map(function (set) { return set.charAt(random(set.length)); });

        while (chars.length < length) {
            chars.push(all.charAt(random(all.length)));
        }

        for (var i = chars.length - 1; i > 0; i--) {
            var j = random(i + 1);
            var swap = chars[i];
            chars[i] = chars[j];
            chars[j] = swap;
        }

        return chars.join('');
    }

    function reveal(field, visible) {
        var toggle = field.querySelector('[data-ncm-toggle]');
        field.querySelector('input').type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', visible ? 'true' : 'false');
        toggle.setAttribute('title', visible ? 'Hide password' : 'Show password');
        toggle.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-ncm-generate], [data-ncm-toggle]');
        var field = button && button.closest('[data-ncm-password]');

        if (!field) {
            return;
        }

        var input = field.querySelector('input');

        if (button.hasAttribute('data-ncm-generate')) {
            input.value = generate(16);
            reveal(field, true);
            input.focus();
            input.select();
        } else {
            reveal(field, input.type === 'password');
        }
    });
})();
