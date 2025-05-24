<x-filament-panels::page.simple heading="Buat Akun Baru" subheading="Bergabunglah dengan Point of Sales">
    <div class="fi-simple-main-ctn flex w-full flex-grow items-center justify-center">
        <div class="fi-simple-main max-w-2xl w-full">
            <x-filament-panels::form wire:submit="register">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="{{ filament()->getLoginUrl() }}" class="text-primary-600 hover:text-primary-500 font-semibold">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
