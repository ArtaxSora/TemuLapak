const registrationPassword = document.getElementById('register-password');

function updatePasswordRequirements() {
    const value = registrationPassword.value;
    const checks = {
        length: Array.from(value).length >= 8,
        case: /\p{Lu}/u.test(value) && /\p{Ll}/u.test(value),
        symbol: /[\p{P}\p{S}]/u.test(value),
    };

    for (const [rule, fulfilled] of Object.entries(checks)) {
        const item = document.querySelector('[data-password-rule="' + rule + '"]');
        if (item.classList.contains('fulfilled') === fulfilled) continue;
        item.classList.toggle('fulfilled', fulfilled);
        item.querySelector('.requirement-icon').textContent = fulfilled ? '✓' : '○';
        item.querySelector('.requirement-state').textContent = fulfilled ? ' — Terpenuhi' : ' — Belum terpenuhi';
    }

    const valid = Object.values(checks).every(Boolean);
    const withinHashLimit = new TextEncoder().encode(value).length <= 72;
    registrationPassword.classList.toggle('password-ready', valid && withinHashLimit);
    registrationPassword.setCustomValidity(
        !withinHashLimit ? 'Kata sandi terlalu panjang, gunakan maksimal 72 byte.' :
        value && !valid ? 'Penuhi ketiga syarat kata sandi di bawah kolom ini.' : ''
    );
}

registrationPassword.addEventListener('input', updatePasswordRequirements);
registrationPassword.addEventListener('change', updatePasswordRequirements);
window.addEventListener('pageshow', updatePasswordRequirements);
registrationPassword.form.addEventListener('submit', event => {
    updatePasswordRequirements();
    if (!registrationPassword.reportValidity()) event.preventDefault();
});
updatePasswordRequirements();
