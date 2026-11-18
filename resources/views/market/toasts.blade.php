@php
$notifications = [];
foreach (['ok'=>'success', 'success'=>'success', 'info'=>'info', 'status'=>'info', 'warning'=>'warning', 'error'=>'error'] as $key=>$type) {
    if (is_string(session($key)) && session($key) !== '') {
        $notifications[] = ['type'=>$type, 'messages'=>[session($key)]];
    }
}
if (session('submitted')) {
    $notifications[] = ['type'=>'success', 'messages'=>['Pengajuan Anda tersimpan. Pemilik tempat akan mengonfirmasi pengajuan Anda.']];
}
if ($errors->any()) {
    $notifications[] = ['type'=>'error', 'title'=>request()->routeIs('login') ? 'Gagal masuk' : 'Periksa kembali isian Anda', 'messages'=>array_unique($errors->all())];
}
@endphp
@if(count($notifications))
<div class="toast-stack">
    @foreach($notifications as $notification)
        <div class="app-toast app-toast--{{$notification['type']}}" role="{{$notification['type']==='error' ? 'alert' : 'status'}}" aria-atomic="true">
            <span class="app-toast-icon" aria-hidden="true">@include('market.table-icon', ['icon'=>match($notification['type']) {'success'=>'check', 'error'=>'close', 'warning'=>'alert', default=>'info'}])</span>
            <div>
                <strong>{{$notification['title'] ?? match($notification['type']) {'success'=>'Berhasil', 'error'=>'Gagal', 'warning'=>'Perhatian', default=>'Informasi'} }}</strong>
                @foreach($notification['messages'] as $message)<p>{{$message}}</p>@endforeach
            </div>
        </div>
    @endforeach
</div>
<script>
{
    const stack = document.querySelector('.toast-stack');
    const removeToast = toast => {
        toast.remove();
        if (!stack.children.length) stack.remove();
    };
    stack.querySelectorAll('.app-toast').forEach(toast => {
        toast.addEventListener('animationend', event => {
            if (event.target === toast) removeToast(toast);
        }, { once: true });
        setTimeout(() => removeToast(toast), 2000);
    });
}
</script>
@endif
