<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('registration rejects passwords missing any required character group', function ($password) {
    $this->post('/daftar', [
        'name' => 'Rina', 'email' => 'rina@example.test', 'peran' => 'PENYEWA',
        'no_telepon' => '081234567890', 'password' => $password,
    ])->assertSessionHasErrors('password');
    expect(User::where('email', 'rina@example.test')->exists())->toBeFalse();
})->with(['Aa!abcd', 'abcdefgh!', 'ABCDEFGH!', 'Abcdefgh', 'Abcdefg ', str_repeat('Ä', 36).'a!']);

test('a valid password registers without a confirmation field and is hashed', function () {
    $this->get('/daftar')->assertOk()->assertDontSee('name="password_confirmation"', false);
    $this->post('/daftar', [
        'name' => 'Rina', 'email' => 'rina@example.test', 'peran' => 'PENYEWA',
        'no_telepon' => '081234567890', 'password' => 'Abcdefg!',
    ])->assertSessionHasNoErrors()->assertRedirect('/dashboard');
    $user = User::where('email', 'rina@example.test')->firstOrFail();
    expect(Hash::check('Abcdefg!', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});
