<?php

namespace App\Console\Commands;

use App\Support\DeviceId;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Exception\Auth\UserNotFound;

/**
 * Read-only diagnostic for the "map does not show up" class of problems:
 * checks, for one account, every Realtime Database node the dashboard
 * reads, using shallow reads (key names only). Coordinates, labels and
 * share contents are never printed, so the output is safe to paste into
 * an issue.
 */
class InspectTrackingStructure extends Command
{
    protected $signature = 'tracking:inspect {email : E-mail da conta (a mesma usada no app móvel)}';

    protected $description = 'Mostra (só nomes de chaves) os nós do Realtime Database lidos pelo dashboard para uma conta.';

    private const LOCATION_FIELDS = ['latitude', 'longitude', 'updatedAt'];

    public function handle(FirebaseAuth $auth, Database $database): int
    {
        try {
            $uid = (string) $auth->getUserByEmail((string) $this->argument('email'))->uid;
        } catch (UserNotFound) {
            $this->error('Nenhuma conta Firebase Auth com esse e-mail.');

            return self::FAILURE;
        }

        if (! DeviceId::isValid($uid)) {
            $this->error('O UID da conta tem formato que não pode ser usado como chave de dispositivo.');

            return self::FAILURE;
        }

        $this->line('UID: '.$uid);
        $this->newLine();

        $this->keys($database, '', 'Nós na raiz do banco');
        $this->keys($database, "users/{$uid}", "users/{$uid}");
        $this->keys($database, "users/{$uid}/sharing", "users/{$uid}/sharing (dispositivos compartilhados com a conta)");
        $this->keys($database, "devices/{$uid}", "devices/{$uid} (dispositivo da própria conta)");

        $location = $database->getReference("devices/{$uid}/location")->getValue();
        $missing = is_array($location)
            ? array_values(array_diff(self::LOCATION_FIELDS, array_keys($location)))
            : self::LOCATION_FIELDS;

        $this->newLine();
        if ($missing === []) {
            $this->info("devices/{$uid}/location: OK (latitude, longitude, updatedAt presentes).");
        } else {
            $this->warn("devices/{$uid}/location: ausente ou incompleto, faltam: ".implode(', ', $missing).'.');
            $this->line('O app móvel precisa gravar a posição nesse nó para o dashboard exibi-la.');
        }

        return self::SUCCESS;
    }

    private function keys(Database $database, string $path, string $title): void
    {
        $keys = $database->getReference($path === '' ? '/' : $path)->getChildKeys();

        $this->line($title.': '.($keys === [] ? '(vazio)' : implode(', ', array_slice($keys, 0, 50))));
    }
}
