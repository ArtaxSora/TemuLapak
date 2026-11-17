<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title','Beranda') · TemuLapak</title><link rel="stylesheet" href="/market.css"><link rel="stylesheet" href="/prototype.css">@stack('styles')</head>
<body>@include('market.header')
@if(session('ok'))<div class="wrap notice" role="status">{{session('ok')}}</div>@endif
@if($errors->any())<div class="wrap error" role="alert"><b>Periksa kembali isian Anda.</b><ul>@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
<main>@yield('content')</main>@include('market.footer')@if($errors->any())<script>
const messages = {{ Illuminate\Support\Js::from($errors->messages()) }};
for (const [name, errors] of Object.entries(messages)) {
    const field = [...document.querySelectorAll('input,textarea,select')].find(el => el.name === name);
    if (!field) continue;
    const hint = document.createElement('small');
    hint.className = 'field-error';
    hint.id = 'error-' + name;
    hint.textContent = errors[0];
    field.setAttribute('aria-invalid','true');
    field.setAttribute('aria-describedby',hint.id);
    field.after(hint);
}
</script>@endif</body></html>
