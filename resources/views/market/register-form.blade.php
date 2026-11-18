<form class="panel stack auth register-form" method="post" action="/daftar">
    @csrf
    <h1>Buat akun baru</h1>
    <fieldset class="register-roles">
        <legend>Daftar sebagai *</legend>
        @foreach(['PENYEWA'=>['Penyewa','Saya mencari tempat berjualan'],'PEMILIK'=>['Pemilik Tempat','Saya ingin menyewakan tempat']] as $value=>[$label,$caption])
            <label>
                <input type="radio" name="peran" value="{{$value}}" required @checked(old('peran',request('peran','PENYEWA'))===$value)>
                <span><b>{{$label}}</b><small>{{$caption}}</small></span>
            </label>
        @endforeach
    </fieldset>
    <label>Nama lengkap *
        <input name="name" required maxlength="255" autocomplete="name" placeholder="Rina Lestari" value="{{old('name')}}" aria-describedby="name-help">
        <small id="name-help">Tulis nama sesuai identitas Anda</small>
    </label>
    <label>Email *
        <input name="email" type="email" required maxlength="255" autocomplete="email" placeholder="nama@contoh.id" value="{{old('email')}}" aria-describedby="email-help">
        <small id="email-help">Email dipakai untuk masuk</small>
    </label>
    <label>Nomor telepon *
        <input name="no_telepon" type="tel" required autocomplete="tel" inputmode="numeric" pattern="[0-9]{10,15}" minlength="10" maxlength="15" placeholder="081234567890" value="{{old('no_telepon')}}" aria-describedby="phone-help">
        <small id="phone-help">10 sampai 15 digit angka</small>
    </label>
    <div class="register-password">
        <label for="register-password">Kata sandi *</label>
        <input id="register-password" name="password" type="password" autocomplete="new-password" required minlength="8" maxlength="72" aria-describedby="password-requirements" @error('password') aria-invalid="true" @enderror>
        <ul id="password-requirements" class="password-requirements" aria-live="polite" aria-atomic="true">
            <li data-password-rule="length"><span class="requirement-icon" aria-hidden="true">○</span>Minimal 8 karakter<span class="requirement-state sr-only"> — Belum terpenuhi</span></li>
            <li data-password-rule="case"><span class="requirement-icon" aria-hidden="true">○</span>Huruf besar dan huruf kecil<span class="requirement-state sr-only"> — Belum terpenuhi</span></li>
            <li data-password-rule="symbol"><span class="requirement-icon" aria-hidden="true">○</span>Simbol khusus, misalnya ! @ #<span class="requirement-state sr-only"> — Belum terpenuhi</span></li>
        </ul>
    </div>
    <button type="submit">Daftar</button>
    <p class="register-login">Sudah punya akun? <a href="/masuk">Masuk</a></p>
</form>
<script defer src="/register.js"></script>
