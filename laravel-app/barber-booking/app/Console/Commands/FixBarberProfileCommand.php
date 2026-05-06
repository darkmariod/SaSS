<?php

namespace App\Console\Commands;

use App\Models\BarberProfile;
use App\Models\BarberAvailability;
use App\Models\User;
use Illuminate\Console\Command;

class FixBarberProfileCommand extends Command
{
    protected $signature = 'fix:barber-profile';
    protected $description = 'Fix barber profile for barber@test.com';

    public function handle(): int
    {
        $barber = User::where('email', 'barber@test.com')->first();

        if (! $barber) {
            $this->error('Barber user not found.');
            return self::FAILURE;
        }

        $profile = BarberProfile::where('user_id', $barber->id)->first();

        if (! $profile) {
            $profile = BarberProfile::create([
                'user_id' => $barber->id,
                'barber_shop_id' => 1,
                'display_name' => $barber->name ?? 'Barber Test',
                'is_active' => true,
            ]);
            $this->info('BarberProfile created: ID=' . $profile->id);
        } else {
            $this->info('BarberProfile already exists: ID=' . $profile->id);
        }

        $availabilitiesCount = BarberAvailability::where('barber_profile_id', $profile->id)->count();

        if ($availabilitiesCount === 0) {
            $days = [1, 2, 3, 4, 5, 6, 7]; // Monday to Sunday
            foreach ($days as $day) {
                BarberAvailability::create([
                    'barber_profile_id' => $profile->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'is_active' => true,
                ]);
            }
            $this->info('Availabilities created.');
        } else {
            $this->info('Availabilities already exist: ' . $availabilitiesCount);
        }

        return self::SUCCESS;
    }
}
