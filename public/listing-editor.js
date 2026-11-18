(() => {
    const editor = document.querySelector('.listing-editor');
    if (!editor) return;
    const form = editor.querySelector('form');
    const fees = document.querySelector('#fees');
    const add = document.querySelector('#addfee');
    let index = Date.now();
    function notice(message) {
        let stack = document.querySelector('.toast-stack');
        if (!stack) { stack = document.createElement('div'); stack.className = 'toast-stack'; document.body.append(stack); }
        const toast = document.createElement('div');
        toast.className = 'app-toast app-toast--error';
        toast.setAttribute('role', 'alert');
        const text = document.createElement('p'); text.textContent = message; toast.append(text); stack.append(toast);
        setTimeout(() => { toast.remove(); if (!stack.children.length) stack.remove(); }, 2000);
    }
    function mode(row) {
        const value = row.querySelector('[data-fee-mode]:checked')?.value || 'tetap';
        const rate = row.querySelector('[data-fee-rate]');
        rate.disabled = value === 'termasuk';
        row.querySelector('[data-rate-label]').textContent = value === 'pemakaian' ? 'Tarif per unit (Rp)' : 'Nominal per periode (Rp)';
        row.querySelector('[data-rate-help]').textContent = value === 'termasuk' ? 'Sudah termasuk harga sewa' : value === 'pemakaian' ? 'Tarif harus lebih dari 0' : 'Isi 0 jika tidak ada';
        const usage = row.querySelector('[data-usage-only]');
        usage.hidden = value !== 'pemakaian';
        usage.querySelectorAll('input').forEach(input => {
            input.disabled = value !== 'pemakaian';
            input.required = value === 'pemakaian' && input.name.endsWith('[satuan_pemakaian]');
        });
        row.querySelectorAll('[data-period-fields] input').forEach(input => { input.disabled = value === 'termasuk'; });
    }
    fees.querySelectorAll('[data-fee-row]').forEach(mode);
    add.addEventListener('click', () => {
        if (fees.children.length >= 50) { notice('Maksimal 50 biaya tambahan.'); return; }
        const fragment = document.querySelector('#fee-template').content.cloneNode(true);
        fragment.querySelectorAll('[name]').forEach(input => { input.name = input.name.replace('__INDEX__', 'new_' + index); });
        index++;
        const row = fragment.querySelector('[data-fee-row]');
        const period = form.querySelector('[name=satuan_harga]:checked')?.value;
        const periodOption = row.querySelector('[name$="[periode]"][value="' + period + '"]');
        if (periodOption) periodOption.checked = true;
        fees.append(fragment); mode(row); row.querySelector('[name$="[nama]"]').focus();
    });
    fees.addEventListener('change', event => { if (event.target.matches('[data-fee-mode]')) mode(event.target.closest('[data-fee-row]')); });
    fees.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-fee]');
        if (!button) return;
        const row = button.closest('[data-fee-row]');
        const focus = row.nextElementSibling?.querySelector('input') || row.previousElementSibling?.querySelector('input') || add;
        row.remove(); focus.focus();
    });

    const fileInput = document.querySelector('#listing-photos');
    const grid = document.querySelector('[data-photo-grid]');
    const drop = document.querySelector('[data-photo-drop]');
    let uploads = [];
    const remainingExisting = () => [...grid.querySelectorAll('[data-existing-photo] input')].filter(input => !input.checked).length;
    function closeIcon(button) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('class', 'table-icon');
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '1.7');
        svg.setAttribute('stroke-linecap', 'round');
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', 'm6 6 12 12M18 6 6 18');
        svg.append(path);
        button.replaceChildren(svg);
    }
    function updateExisting(tile) {
        const removed = tile.querySelector('input').checked;
        tile.classList.toggle('is-removed', removed);
        const button = tile.querySelector('button');
        if (removed) button.textContent = 'Batal hapus';
        else closeIcon(button);
        button.setAttribute('aria-label', removed ? 'Batalkan penghapusan foto' : 'Hapus foto');
    }
    grid.querySelectorAll('[data-existing-photo]').forEach(updateExisting);
    function renderUploads() {
        grid.querySelectorAll('[data-new-photo]').forEach(tile => { URL.revokeObjectURL(tile.querySelector('img').src); tile.remove(); });
        const data = new DataTransfer();
        uploads.forEach((file, n) => {
            data.items.add(file);
            const tile = document.createElement('div'); tile.className = 'photo-tile'; tile.dataset.newPhoto = n;
            const image = document.createElement('img'); image.src = URL.createObjectURL(file); image.alt = 'Foto baru ' + (n + 1);
            const button = document.createElement('button'); button.type = 'button'; button.className = 'photo-remove'; closeIcon(button); button.setAttribute('aria-label', 'Hapus foto baru ' + (n + 1));
            button.addEventListener('click', () => { uploads.splice(n, 1); renderUploads(); document.querySelector('[data-choose-photos]').focus(); });
            tile.append(image, button); grid.append(tile);
        });
        fileInput.files = data.files;
    }
    function acceptFiles(files) {
        let rejected = false;
        for (const file of files) {
            if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > 2 * 1024 * 1024) { rejected = true; continue; }
            if (uploads.some(existing => existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified)) continue;
            if (remainingExisting() + uploads.length >= 8) { notice('Maksimal 8 foto. Hapus salah satu foto sebelum menambah lagi.'); break; }
            uploads.push(file);
        }
        if (rejected) notice('Gunakan foto JPG atau PNG dengan ukuran maksimal 2 MB.');
        renderUploads();
    }
    document.querySelector('[data-choose-photos]').addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => acceptFiles([...fileInput.files]));
    grid.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-photo]');
        if (!button) return;
        const tile = button.closest('[data-existing-photo]'); const checkbox = tile.querySelector('input');
        if (checkbox.checked && remainingExisting() + uploads.length >= 8) { notice('Maksimal 8 foto. Hapus foto lain sebelum membatalkan penghapusan.'); return; }
        checkbox.checked = !checkbox.checked; updateExisting(tile);
    });
    ['dragover', 'dragenter'].forEach(name => drop.addEventListener(name, event => { event.preventDefault(); drop.classList.add('is-dragging'); }));
    drop.addEventListener('dragleave', () => drop.classList.remove('is-dragging'));
    drop.addEventListener('drop', event => { event.preventDefault(); drop.classList.remove('is-dragging'); acceptFiles([...event.dataTransfer.files]); });
    form.addEventListener('submit', event => {
        if (remainingExisting() + uploads.length < 1) { event.preventDefault(); notice('Tambahkan minimal 1 foto tempat.'); document.querySelector('[data-choose-photos]').focus(); }
    });
    const links = [...editor.querySelectorAll('.partnav a')];
    const markSection = () => {
        const header = document.querySelector('.sitehead').getBoundingClientRect().height;
        const current = links.filter(link => document.querySelector(link.hash).getBoundingClientRect().top <= header + 32).at(-1) || links[0];
        links.forEach(link => { if (link === current) link.setAttribute('aria-current','location'); else link.removeAttribute('aria-current'); });
    };
    document.addEventListener('scroll', markSection, { passive: true }); markSection();
})();
