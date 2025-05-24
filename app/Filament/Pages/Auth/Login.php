<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;

class Login extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.login';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
            ->placeholder('Masukkan email Anda');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 2])
            ->placeholder('Masukkan password Anda');
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        if (! Filament::auth()->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $data['remember'] ?? false)) {
            // Cek apakah email valid
            $userExists = \App\Models\User::where('email', $data['email'])->exists();

            if (! $userExists) {
                throw ValidationException::withMessages([
                    'data.email' => 'Email tidak ditemukan.',
                ]);
            }

            // Email valid, tapi password salah
            throw ValidationException::withMessages([
                'data.password' => 'Kata sandi salah.',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Ingat saya');
    }
}
