<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Accounts\DeleteAccount;
use Database\Seeders\DemoCommunitySeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Deletes the demonstration travellers and everything they published, the
 * same way a traveller deleting their own account would. Run it before launch.
 */
#[Signature('community:remove-demo')]
#[Description('Delete the demonstration travellers and their posts, comments, likes, reviews and follows')]
class RemoveDemoCommunity extends Command
{
    public function handle(DeleteAccount $deleteAccount): int
    {
        $travellers = User::query()->where('email', 'like', '%@'.DemoCommunitySeeder::EMAIL_DOMAIN)->get();

        foreach ($travellers as $traveller) {
            $deleteAccount->handle($traveller);
        }

        $this->info("Removed {$travellers->count()} demonstration travellers.");

        return self::SUCCESS;
    }
}
