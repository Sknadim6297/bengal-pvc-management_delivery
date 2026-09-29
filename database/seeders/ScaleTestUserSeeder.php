<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ScaleTestUserSeeder extends Seeder
{
    private const RECORDS = 1_000_000;
    private const CHUNK_SIZE = 1_000;

    public function run(): void
    {
        $connection = DB::connection();
        $database = (string) $connection->getDatabaseName();

        if (! app()->environment(['local', 'testing'])
            || $connection->getDriverName() !== 'mysql'
            || ! str_ends_with($database, '_benchmark')) {
            throw new RuntimeException('Scale data can only be seeded in a local/testing MySQL database whose name ends with _benchmark.');
        }

        if (DB::table('users')->where('email', 'like', 'scale-user-%@benchmark.invalid')->exists()) {
            throw new RuntimeException('Scale test users already exist; use a fresh dedicated benchmark database.');
        }

        $passwordHash = Hash::make(bin2hex(random_bytes(32)));
        $timestamp = now()->toDateTimeString();

        for ($offset = 0; $offset < self::RECORDS; $offset += self::CHUNK_SIZE) {
            $rows = [];
            $limit = min($offset + self::CHUNK_SIZE, self::RECORDS);

            for ($index = $offset + 1; $index <= $limit; $index++) {
                $rows[] = [
                    'name' => sprintf('Scale User %07d', $index),
                    'email' => sprintf('scale-user-%07d@benchmark.invalid', $index),
                    'whatsapp_number' => '91'.sprintf('%010d', 9_000_000_000 + $index),
                    'district' => 'Nadia',
                    'email_verified_at' => null,
                    'password' => $passwordHash,
                    'role' => User::ROLE_USER,
                    'status' => User::STATUS_ACTIVE,
                    'remember_token' => null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::table('users')->insert($rows);
        }

        $this->command?->info('Inserted 1,000,000 scale-test users into the dedicated benchmark database.');
    }
}
