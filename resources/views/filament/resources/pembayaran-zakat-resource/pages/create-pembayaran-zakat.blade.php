<x-filament::page>
     <!-- Loading Overlay hanya untuk submit form -->
     <div wire:loading.class.remove="hidden" 
         wire:target="create"
         class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-primary-500"></div>
    </div>
    <form wire:submit.prevent="create">
        {{ $this->form }}

       <div class="mt-6">
        <x-filament::button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="create">Bayar Zakat</span>
                <span wire:loading.flex class="items-center" wire:target="create">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </x-filament::button>
       </div>
    </form>
</x-filament::page>