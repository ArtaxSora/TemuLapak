(() => {
    const selector = 'input[data-money]';
    const digits = value => value.replace(/[^0-9]/g, '').replace(/^0+(?=\d)/, '');
    const format = value => digits(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    function update(input, keepCaret = false) {
        const before = input.value;
        const caret = input.selectionStart ?? before.length;
        const digitsAfter = before.slice(caret).replace(/[^0-9]/g, '').length;
        input.value = format(before);
        const raw = digits(input.value);
        input.setCustomValidity(raw.length > 14 ? 'Nominal maksimal 99.999.999.999.999.' :
            (input.dataset.moneyMin === '1' && raw === '0' ? 'Harga sewa minimal Rp 1.' : ''));
        if (keepCaret) {
            let position = input.value.length;
            let remaining = digitsAfter;
            while (position > 0 && remaining > 0) {
                position--;
                if (/\d/.test(input.value[position])) remaining--;
            }
            input.setSelectionRange(position, position);
        }
    }

    document.querySelectorAll(selector).forEach(input => update(input));
    document.addEventListener('beforeinput', event => {
        const input = event.target;
        if (!input.matches?.(selector)) return;
        if (event.inputType === 'insertText' && event.data && /[^0-9]/.test(event.data)) {
            event.preventDefault();
            return;
        }
        // Skip grouping dots when deleting so Backspace/Delete still removes a digit.
        const caret = input.selectionStart;
        if (caret !== input.selectionEnd) return;
        if (event.inputType === 'deleteContentBackward' && input.value[caret - 1] === '.') {
            input.setSelectionRange(Math.max(0, caret - 2), caret);
        } else if (event.inputType === 'deleteContentForward' && input.value[caret] === '.') {
            input.setSelectionRange(caret, caret + 2);
        }
    });
    document.addEventListener('input', event => {
        if (event.target.matches?.(selector)) update(event.target, true);
    }, true);
    document.addEventListener('moneyformat', event => {
        if (event.target.matches?.(selector)) update(event.target);
    });
    document.addEventListener('focusout', event => {
        if (event.target.matches?.(selector)) update(event.target);
    });
    document.addEventListener('reset', event => {
        setTimeout(() => event.target.querySelectorAll(selector).forEach(input => update(input)), 0);
    });
    document.addEventListener('formdata', event => {
        event.target.querySelectorAll(selector).forEach(input => {
            if (input.name && !input.disabled) event.formData.set(input.name, digits(input.value));
        });
    });
})();
