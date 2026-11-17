const quickType = document.querySelector('[data-quick-type]');
const typeChips = [...document.querySelectorAll('input[name="jenis[]"]')];
const quickMax = document.querySelector('[data-quick-max]');
const maxPrice = document.querySelector('[name=max]');
const formatter = new Intl.NumberFormat('id-ID');

function rupiah(value) {
    const digits = value.replace(/\D/g, '');
    return digits ? 'Rp ' + formatter.format(BigInt(digits)) : '';
}

function syncQuickType() {
    const selected = typeChips.filter(chip => chip.checked);
    quickType.value = selected.length > 1 ? 'multi' : selected[0]?.value || '';
}

typeChips.forEach(chip => chip.addEventListener('change', syncQuickType));
quickType.addEventListener('change', () => {
    typeChips.forEach(chip => { chip.checked = chip.value === quickType.value; });
});
syncQuickType();

document.querySelectorAll('[data-rupiah]').forEach(input => {
    input.addEventListener('input', () => {
        if (input === maxPrice) quickMax.value = input.value.replace(/\D/g, '');
    });
    input.addEventListener('blur', () => { input.value = rupiah(input.value); });
});
quickMax.addEventListener('input', () => { maxPrice.value = rupiah(quickMax.value); });
