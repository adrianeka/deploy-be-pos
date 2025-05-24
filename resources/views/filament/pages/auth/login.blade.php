<x-filament-panels::page.simple heading="Selamat Datang" subheading="Masuk ke akun Point of Sales Anda">
    <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
        <div class="fi-simple-main max-w-md w-full">
            <x-filament-panels::form wire:submit="authenticate">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()" />
            </x-filament-panels::form>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Belum punya akun?
                    <a href="{{ filament()->getRegistrationUrl() }}" class="text-primary-600 hover:text-primary-500 font-semibold">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>