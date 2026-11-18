<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function loginForm()
    {
        return view('market.account', ['mode' => 'Masuk']);
    }

    public function registerForm()
    {
        return view('market.account', ['mode' => 'Daftar']);
    }

    public function profile()
    {
        return view('market.account', ['mode' => 'Profil']);
    }

    public function dashboard(Request $r)
    {
        return redirect(match ($r->user()->peran) {
            'ADMIN' => '/admin/verifikasi', 'PEMILIK' => '/pemilik/tempat', default => '/cari'
        });
    }

    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        $data['email'] = strtolower($data['email']);
        if (! Auth::attempt($data + ['aktif' => true])) {
            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }
        $r->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    private function identity(Request $r, ?int $id = null): array
    {
        $r->merge(['email' => strtolower((string) $r->email)]);

        return $r->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($id)], 'no_telepon' => 'required|string|regex:/^[+0-9() -]{6,32}$/']);
    }

    public function register(Request $r)
    {
        $data = $this->identity($r);
        $extra = $r->validate([
            'peran' => 'required|in:PENYEWA,PEMILIK',
            'no_telepon' => 'required|string|regex:/^[0-9]{10,15}$/',
            'password' => [
                'required', 'string', 'min:8', 'max:72',
                'regex:/\p{Lu}/u', 'regex:/\p{Ll}/u', 'regex:/[\p{P}\p{S}]/u',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && strlen($value) > 72) {
                        $fail('Kata sandi terlalu panjang, gunakan maksimal 72 byte.');
                    }
                },
            ],
        ], [
            'password.regex' => 'Kata sandi wajib mengandung huruf besar, huruf kecil, dan simbol khusus.',
            'no_telepon.regex' => 'Nomor telepon harus terdiri dari 10 sampai 15 digit angka.',
        ]);
        $user = new User;
        $user->forceFill($data + $extra)->save();
        Auth::login($user->refresh());
        $r->session()->regenerate();

        return redirect('/dashboard');
    }

    public function saveProfile(Request $r)
    {
        $r->user()->forceFill($this->identity($r, $r->user()->id))->save();

        return back()->with('ok', 'Profil tersimpan.');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/');
    }

    public function notifications(Request $r)
    {
        return view('market.notifications', ['items' => $r->user()->notifikasi()->orderByDesc('id_notifikasi')->paginate(30)]);
    }

    public function readNotifications(Request $r)
    {
        $r->user()->notifikasi()->whereNull('dibaca_pada')->update(['dibaca_pada' => now()]);

        return back()->with('ok', 'Semua notifikasi sudah dibaca.');
    }

    public function users(Request $r)
    {
        $query = User::whereIn('peran', ['PEMILIK', 'PENYEWA']);
        if ($r->filled('q')) {
            $query->where(function ($q) use ($r) {
                $q->whereLike('name', '%'.$r->q.'%')->orWhereLike('email', '%'.$r->q.'%');
            });
        }
        if ($r->filled('peran')) {
            $query->where('peran', $r->peran);
        }

        return view('market.users', ['items' => $query->orderBy('name')->paginate(25)->withQueryString()]);
    }

    public function saveUser(Request $r, int $id)
    {
        $user = User::whereIn('peran', ['PEMILIK', 'PENYEWA'])->findOrFail($id);
        $user->forceFill($this->identity($r, $id) + ['aktif' => $r->boolean('aktif')])->save();

        return back()->with('ok', 'Data pengguna tersimpan.');
    }
}
