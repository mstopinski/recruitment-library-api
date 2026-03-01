<?php

namespace App\Console\Commands;

use App\Models\Author;
use Illuminate\Console\Command;

class CreateAuthorCommand extends Command
{
    protected $signature = 'author:create
        {first_name? : Imię autora}
        {last_name? : Nazwisko autora}
        {--force : Pomiń sprawdzanie duplikatów}';

    protected $description = 'Create a new author by providing first and last name';

    public function handle(): int
    {
        $firstName = trim($this->argument('first_name') ?? $this->ask('Podaj imię autora'));
        $lastName = trim($this->argument('last_name') ?? $this->ask('Podaj nazwisko autora'));

        if (!$this->option('force')) {
            $existing = Author::whereRaw('LOWER(first_name) = ?', [strtolower($firstName)])
                ->whereRaw('LOWER(last_name) = ?', [strtolower($lastName)])
                ->first();

            if ($existing) {
                $this->warn("Uwaga: Autor o imieniu '{$firstName} {$lastName}' już istnieje (ID: {$existing->id}).");
                if (!$this->confirm('Czy mimo to chcesz utworzyć duplikat?')) {
                    $this->info('Tworzenie autora anulowane.');
                    return self::SUCCESS;
                }
            }
        }

        $author = Author::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        $this->info("Autor '{$author->first_name} {$author->last_name}' został utworzony (ID: {$author->id}).");

        return self::SUCCESS;
    }
}