<x-filament::page>
    <form wire:submit="create">
        {{ $this->form }}

       <div class="mt-6">
            <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
       </div>
    </form>
</x-filament::page>