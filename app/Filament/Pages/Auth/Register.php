<?php

namespace App\Filament\Pages\Auth;

use App\Models\Pemilik;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Register extends BaseRegister
{
    protected static string $view = 'filament.pages.auth.register';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Akun')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ]),

                Section::make('Informasi Usaha (Opsional)')
                    ->schema([
                        TextInput::make('nama_perusahaan')
                            ->label('Nama Perusahaan')
                            ->maxLength(255)
                            ->placeholder('Masukan nama perusahaan Anda'),
                        TextInput::make('alamat_toko')
                            ->label('Alamat Toko')
                            ->maxLength(255)
                            ->placeholder('Masukan alamat toko Anda'),
                        TextInput::make('jenis_usaha')
                            ->label('Jenis Usaha')
                            ->maxLength(255)
                            ->placeholder('Masukan jenis usaha Anda'),
                        TextInput::make('no_telp')
                            ->label('No. Telepon')
                            ->tel()
                            ->minLength(10)
                            ->maxLength(13)
                            ->regex('/^[0-9]+$/')
                            ->placeholder('Masukan nomor telepon Anda'),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nama Lengkap')
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->placeholder('Masukan nama lengkap Anda');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel())
            ->placeholder('Masukan email Anda');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->required()
            ->revealable()
            ->rule(\Illuminate\Validation\Rules\Password::default())
            ->same('passwordConfirmation')
            ->validationAttribute('kata sandi')
            ->placeholder('Minimal 8 karakter');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Konfirmasi Kata Sandi')
            ->password()
            ->required()
            ->revealable()
            ->dehydrated(false)
            ->placeholder('Ulangi kata sandi Anda');
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $pemilikData = [
            'nama_pemilik' => $data['name'],
            'nama_perusahaan' => $data['nama_perusahaan'] ?? '',
            'alamat_toko' => $data['alamat_toko'] ?? '',
            'jenis_usaha' => $data['jenis_usaha'] ?? '',
            'no_telp' => $data['no_telp'] ?? '',
        ];

        $hasBusinessData = array_filter($pemilikData, function ($value) use ($data) {
            return !empty(trim($value)) && $value !== $data['name'];
        });


        if (!empty($hasBusinessData)) {
            Pemilik::create(array_merge(['id_user' => $user->id], $pemilikData));
        }

        return $user;
    }
}
