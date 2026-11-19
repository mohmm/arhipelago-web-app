<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Makes the app's fixed set of accounts, when config/accounts.php gives it one:
 * user1@<domain> to user<limit>@<domain>. Accounts that already exist are left
 * as they are, so running it again is safe. The new accounts' passwords are
 * written once, to a private file, for whoever hands the accounts out.
 */
class CreateAccounts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'accounts:create';

    /**
     * @var string
     */
    protected $description = 'Make the app\'s fixed set of accounts and save their passwords to a private file';

    public function handle(): int
    {
        $limit = config('accounts.limit');
        $domain = config('accounts.domain');

        if (! is_int($limit) || $limit < 1 || ! is_string($domain) || $domain === '') {
            $this->components->error('This app has no fixed set of accounts: anyone can sign up. See config/accounts.php.');

            return self::FAILURE;
        }

        $emails = collect(range(1, $limit))->mapWithKeys(fn (int $number): array => [$number => "user{$number}@{$domain}"]);
        $missing = $emails->diff(User::query()->whereIn('email', $emails)->pluck('email'));

        if ($missing->isEmpty()) {
            $this->components->info("All {$limit} accounts already exist.");

            return self::SUCCESS;
        }

        $file = 'accounts/'.now()->format('Y-m-d-His').'.csv';

        DB::transaction(function () use ($missing, $file): void {
            $lines = ['email,password'];

            foreach ($missing as $number => $email) {
                $password = Str::password(16, symbols: false);

                $user = User::create([
                    'first_name' => 'User',
                    'last_name' => (string) $number,
                    'email' => $email,
                    'password' => $password,
                    'locale' => config('app.locale'),
                ]);

                // Nobody confirms these addresses by email: whoever owns the
                // app hands the accounts out and vouches for them.
                $user->forceFill(['email_verified_at' => now()])->save();
                $user->assignRole('customer');

                $lines[] = "{$email},{$password}";
            }

            // Written inside the transaction, so no account is ever made
            // without its password being saved somewhere.
            if (! Storage::disk('local')->put($file, implode("\n", $lines)."\n", 'private')) {
                throw new RuntimeException("Could not write storage/app/private/{$file}; no account was made.");
            }
        });

        $this->components->info(sprintf(
            '%d accounts made. Their passwords are in %s: hand them out, then delete the file.',
            $missing->count(),
            Storage::disk('local')->path($file),
        ));

        return self::SUCCESS;
    }
}
