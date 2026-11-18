const quickType = document.querySelector('[data-quick-type]');
const typeChips = [...document.querySelectorAll('input[name="jenis[]"]')];
const quickMax = document.querySelector('[data-quick-max]');
const maxPrice = document.querySelector('[name=max]');


function syncQuickType() {
    const selected = typeChips.filter(chip => chip.checked);
    quickType.value = selected.length > 1 ? 'multi' : selected[0]?.value || '';
}

typeChips.forEach(chip => chip.addEventListener('change', syncQuickType));
quickType.addEventListener('change', () => {
    typeChips.forEach(chip => { chip.checked = chip.value === quickType.value; });
});
syncQuickType();

function syncPrice(source, target) {
    target.value = source.value;
    target.dispatchEvent(new Event('moneyformat', { bubbles: true }));
}
maxPrice.addEventListener('input', () => syncPrice(maxPrice, quickMax));
quickMax.addEventListener('input', () => syncPrice(quickMax, maxPrice));
