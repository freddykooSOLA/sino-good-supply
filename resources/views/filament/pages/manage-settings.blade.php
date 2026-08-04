<x-filament-panels::page>
    <form wire:submit="save" class="fi-sc space-y-6">
        {{ $this->form }}

        <div class="fi-form-actions flex justify-end gap-3">
            <x-filament::button type="submit" color="primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">保存设置</span>
                <span wire:loading wire:target="save">保存中...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
