<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\BarberProfile;
use App\Models\BarberShop;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        // Si tiene rol barber, crear BarberProfile automáticamente
        if ($user->hasRole('barber')) {
            $barberShopId = $this->data['barber_shop_id'] ?? null;

            // Si no se eligió barbería, intentar con la del dueño actual
            if (empty($barberShopId) && auth()->user()?->hasRole('owner')) {
                $barberShopId = BarberShop::where('owner_id', auth()->id())->value('id');
            }

            // Fallback: primera barbería activa
            if (empty($barberShopId)) {
                $barberShopId = BarberShop::where('is_active', true)->value('id');
            }

            if ($barberShopId) {
                BarberProfile::create([
                    'user_id' => $user->id,
                    'barber_shop_id' => $barberShopId,
                    'display_name' => $user->name,
                    'is_active' => true,
                ]);

                Log::info("BarberProfile creado automaticamente para usuario barbero: {$user->email}");
            }
        }
    }
}
