<?php

namespace App\Filament\Pages;

use App\Models\Pemilik;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $title = 'Profil Pengguna';
    protected static string $view = 'filament.pages.user-profile';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public ?array $profileData = [];
    public bool $isEditing = false;

    public function mount(): void
    {
        $this->fillForms();
    }

    protected function fillForms(): void
    {
        $user = Filament::auth()->user();
        $pemilik = Pemilik::where('id_user', $user->id)->first();

        $this->profileData = [
            'name' => $user->name,
            'email' => $user->email,
            'nama_pemilik' => $user->name,
            'nama_perusahaan' => $pemilik?->nama_perusahaan ?? '',
            'alamat_toko' => $pemilik?->alamat_toko ?? '',
            'jenis_usaha' => $pemilik?->jenis_usaha ?? '',
            'no_telp' => $pemilik?->no_telp ?? '',
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function getUserData(): array
    {
        $user = Filament::auth()->user();
        $pemilik = Pemilik::where('id_user', $user->id)->first();

        return [
            'user' => $user,
            'pemilik' => $pemilik,
        ];
    }

    public function editProfile(): void
    {
        $this->isEditing = true;
    }

    public function cancelEdit(): void
    {
        $this->isEditing = false;
        $this->fillForms();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('ProfilTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('profil')
                            ->label('Informasi Profil')
                            ->schema([
                                Forms\Components\Section::make('Informasi Akun')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->rules([
                                                'email',
                                                'required',
                                                'max:255',
                                                \Illuminate\Validation\Rule::unique('users', 'email')->ignore(Filament::auth()->id()),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Informasi Pemilik')
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_perusahaan')
                                            ->label('Nama Perusahaan')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('alamat_toko')
                                            ->label('Alamat Toko')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('jenis_usaha')
                                            ->label('Jenis Usaha')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('no_telp')
                                            ->label('No. Telepon')
                                            ->tel()
                                            ->minLength(10)
                                            ->maxLength(13)
                                            ->regex('/^[0-9]+$/'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('kataSandi')
                            ->label('Ubah Kata Sandi')
                            ->schema([
                                Forms\Components\Section::make('Ubah Password')
                                    ->schema([
                                        Forms\Components\TextInput::make('current_password')
                                            ->label('Password Saat Ini')
                                            ->password()
                                            ->rules(['current_password'])
                                            ->revealable()
                                            ->requiredWith('password'),
                                        Forms\Components\TextInput::make('password')
                                            ->label('Password Baru')
                                            ->password()
                                            ->rule(Password::default())
                                            ->revealable()
                                            ->same('password_confirmation'),
                                        Forms\Components\TextInput::make('password_confirmation')
                                            ->label('Konfirmasi Password Baru')
                                            ->password()
                                            ->revealable()
                                            ->requiredWith('password')
                                            ->dehydrated(false),
                                    ]),
                            ]),
                    ])
                    ->activeTab(1)
                    ->persistTabInQueryString(),
            ])
            ->statePath('profileData');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save'),
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->action('cancelEdit'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $user = Filament::auth()->user();

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            $pemilikData = [
                'nama_pemilik' => $data['name'],
                'nama_perusahaan' => $data['nama_perusahaan'] ?? '',
                'alamat_toko' => $data['alamat_toko'] ?? '',
                'jenis_usaha' => $data['jenis_usaha'] ?? '',
                'no_telp' => $data['no_telp'] ?? '',
            ];

            $pemilik = Pemilik::where('id_user', $user->id)->first();

            if ($pemilik) {
                $pemilik->update($pemilikData);
            } else {
                $hasData = array_filter($pemilikData, function ($value) {
                    return !empty(trim($value));
                });

                if (!empty($hasData)) {
                    Pemilik::create(array_merge(['id_user' => $user->id], $pemilikData));
                }
            }

            $passwordUpdated = false;
            if (!empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);
                $passwordUpdated = true;
            }

            if ($passwordUpdated) {
                Notification::make()
                    ->success()
                    ->title('Kata sandi diperbarui')
                    ->body('Perubahan telah disimpan.')
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title('Profil diperbarui')
                    ->body('Perubahan telah disimpan.')
                    ->send();
            }

            $this->isEditing = false;
            $this->fillForms();
        } catch (Halt $exception) {
            return;
        }
    }
}
