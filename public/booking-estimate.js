(() => {
    const form = document.querySelector('[data-booking-form]');
    if (!form) return;
    const start = form.elements.tanggal_mulai;
    const end = form.elements.tanggal_selesai;
    const duration = form.querySelector('[data-duration]');
    const durationBox = duration.parentElement;
    const lines = form.querySelector('[data-cost-lines]');
    const initialLines = lines.innerHTML;
    const total = form.querySelector('[data-estimate-total]');
    const button = form.querySelector('[data-booking-submit]');
    const retry = form.querySelector('[data-estimate-retry]');
    const note = form.querySelector('[data-estimate-note]');
    const originalNote = note.textContent;
    const currency = value => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value);
    let pending;
    let revision = 0;

    async function refresh() {
        const current = ++revision;
        pending?.abort();
        button.disabled = true;
        retry.hidden = true;
        lines.innerHTML = initialLines;
        total.textContent = '—';
        note.textContent = originalNote;
        durationBox.classList.remove('is-invalid');
        durationBox.removeAttribute('aria-busy');
        end.setCustomValidity('');
        end.min = start.value || start.min;
        if (!start.value || !end.value) {
            duration.textContent = 'Pilih tanggal mulai dan selesai untuk menghitung durasi sewa.';
            return;
        }
        if (start.value < start.min || end.value < start.value) {
            duration.textContent = start.value < start.min ? 'Tanggal mulai tidak boleh sebelum hari ini.' : 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
            durationBox.classList.add('is-invalid');
            return;
        }
        pending = new AbortController();
        duration.textContent = 'Menghitung durasi dan biaya sewa…';
        durationBox.setAttribute('aria-busy', 'true');
        try {
            const params = new URLSearchParams({ tanggal_mulai: start.value, tanggal_selesai: end.value });
            const response = await fetch(form.dataset.estimateUrl + '?' + params, { signal: pending.signal, headers: { Accept: 'application/json' } });
            const estimate = await response.json();
            if (current !== revision) return;
            if (!response.ok) throw new Error(Object.values(estimate.errors || {}).flat()[0] || estimate.message || 'Perhitungan belum berhasil. Coba lagi.');
            lines.replaceChildren();
            estimate.lines.forEach(line => {
                const row = document.createElement('div');
                const label = document.createElement('dt');
                const calculation = document.createElement('small');
                const amount = document.createElement('dd');
                label.textContent = line.label;
                calculation.textContent = line.calculation || estimate.units + ' ' + estimate.unit + ' × ' + currency(line.rate);
                amount.textContent = line.total === null ? 'Sesuai pemakaian' : currency(line.total);
                label.append(calculation);
                row.append(label, amount);
                lines.append(row);
            });
            total.textContent = currency(estimate.total);
            note.textContent = 'Setiap biaya mengikuti periode masing-masing. Sisa periode dibulatkan ke atas. ' + (estimate.unknown_usage ? 'Total belum termasuk pemakaian yang belum diperkirakan. ' : '') + 'Tagihan pemakaian mengikuti penggunaan sebenarnya. Tidak ada pembayaran di langkah ini.';
            const charged = estimate.units + ' ' + estimate.unit;
            duration.textContent = estimate.rounded
                ? 'Durasi sewa: ' + estimate.days + ' hari · dihitung ' + charged + ' (pembulatan ke atas).'
                : 'Durasi sewa: ' + charged + (estimate.minimum_end ? ' (sesuai durasi minimal ' + estimate.minimum + ').' : '.');
            if (!estimate.eligible) {
                const message = 'Durasi belum memenuhi minimal ' + estimate.minimum + '. Pilih tanggal selesai paling cepat ' + new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date(estimate.minimum_end + 'T12:00:00')) + '.';
                duration.textContent = message;
                durationBox.classList.add('is-invalid');
                end.setCustomValidity(message);
            }
            button.disabled = !estimate.eligible;
        } catch (error) {
            if (error.name === 'AbortError' || current !== revision) return;
            duration.textContent = error instanceof TypeError || error instanceof SyntaxError ? 'Perhitungan belum berhasil. Periksa koneksi lalu coba lagi.' : error.message;
            durationBox.classList.add('is-invalid');
            retry.hidden = false;
        } finally {
            if (current === revision) durationBox.removeAttribute('aria-busy');
        }
    }
    start.addEventListener('input', refresh);
    end.addEventListener('input', refresh);
    start.addEventListener('change', refresh);
    end.addEventListener('change', refresh);
    retry.addEventListener('click', refresh);
    form.addEventListener('submit', event => {
        if (button.disabled) event.preventDefault();
        else { button.disabled = true; button.textContent = 'Mengirim pengajuan…'; }
    });
    window.addEventListener('pageshow', refresh);
})();
