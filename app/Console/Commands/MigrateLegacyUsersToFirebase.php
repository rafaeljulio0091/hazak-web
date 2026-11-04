<?php

namespace App\Console\Commands;

use App\Models\User as LegacyUser;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class MigrateLegacyUsersToFirebase extends Command
{
    protected $signature = 'users:migrate-to-firebase';

    protected $description = 'Copia perfis locais antigos para Firebase Auth e Realtime Database.';

    public function handle(FirebaseAuth $auth, Database $database): int
    {
        $migrated = 0;
        $skipped = 0;

        LegacyUser::query()->orderBy('id')->chunkById(200, function ($users) use ($auth, $database, &$migrated, &$skipped): void {
            foreach ($users as $user) {
                $uid = (string) $user->getKey();

                try {
                    $firebaseUser = $auth->getUser($uid);
                    if (strcasecmp((string) $firebaseUser->email, (string) $user->email) !== 0) {
                        throw new \RuntimeException("UID {$uid} já pertence a outro endereço de e-mail.");
                    }
                    $skipped++;
                } catch (UserNotFound) {
                    // Preserve IDs used by existing users/{id}/sharing paths.
                    $auth->createUser([
                        'uid' => $uid,
                        'email' => $user->email,
                        'displayName' => $user->name,
                        'emailVerified' => $user->email_verified_at !== null,
                    ]);
                    $migrated++;
                }

                $database->getReference("users/{$uid}/profile")->set([
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->value ?? 'user',
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                    'migrated_at' => now()->toIso8601String(),
                ]);

            }
        });

        $this->info("Perfis copiados: {$migrated}; já existentes no Firebase: {$skipped}.");
        $this->warn('As senhas locais não foram copiadas. Usuários migrados devem redefinir a senha pelo fluxo Firebase.');

        return self::SUCCESS;
    }
}
