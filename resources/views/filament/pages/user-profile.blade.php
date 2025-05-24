<x-filament-panels::page>
    @if(!$isEditing)
        <!-- Edit Profile Button -->
        <div class="flex justify-end">
        <button 
            type="button" 
            wire:click="editProfile"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-md font-medium text-sm text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Ubah Profil
        </button>
    </div>

        <!-- Static Profile Information Display -->
        <div class="space-y-6">
            <!-- User Information Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Informasi Akun
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Nama
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $this->getUserData()['user']->name ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Email
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $this->getUserData()['user']->email ?: '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Owner Information Section -->
             <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Informasi Pemilik
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Nama Perusahaan
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $this->getUserData()['pemilik']?->nama_perusahaan ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Alamat Toko
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $this->getUserData()['pemilik']?->alamat_toko ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                Jenis Usaha
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $this->getUserData()['pemilik']?->jenis_usaha ?: '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                No. Telepon
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $this->getUserData()['pemilik']?->no_telp ?: '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>
        </div>
    @else
        <!-- Edit Form -->
        <form wire:submit="save">
            {{ $this->form }}
            
            <div class="mt-6">
                <x-filament-panels::form.actions
                    :actions="$this->getFormActions()"
                />
            </div>
        </form>
    @endif
</x-filament-panels::page>