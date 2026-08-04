<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = '编辑登录用户';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('删除')
                ->visible(fn (): bool => UserResource::canDelete($this->record)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var User $record */
        $record = $this->record;

        if (
            $record->isAdmin()
            && ($data['role'] ?? null) === User::ROLE_USER
            && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1
        ) {
            $data['role'] = User::ROLE_ADMIN;
        }

        return $data;
    }
}
