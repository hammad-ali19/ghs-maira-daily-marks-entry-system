<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SetupAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-admin 
                            {--name= : Admin full name} 
                            {--email= : Admin email address} 
                            {--password= : Admin account password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update the initial school administrator account';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Enter administrator name', 'Headmaster / Admin');
        $email = $this->option('email') ?: $this->ask('Enter administrator email', 'admin@ghsmaira.edu.pk');
        $password = $this->option('password') ?: $this->secret('Enter administrator password (min 8 characters)');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]);
            $this->info("Administrator account [{$email}] updated successfully.");
        } else {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
            ]);
            $this->info("Administrator account [{$email}] created successfully.");
        }

        return self::SUCCESS;
    }
}
