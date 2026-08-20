<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class SystemUpdater
{
    public function update(): string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            throw new RuntimeException(
                'A atualização automática está disponível apenas no Windows.'
            );
        }

        $batchFile = base_path('update-system.bat');

        if (! is_file($batchFile)) {
            throw new RuntimeException('O arquivo de atualização não foi encontrado.');
        }

        $lock = fopen(storage_path('framework/system-update.lock'), 'c');

        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }

            throw new RuntimeException('Uma atualização já está em andamento.');
        }

        try {
            $process = new Process(
                ['cmd.exe', '/D', '/C', $batchFile],
                base_path()
            );
            $process->setTimeout(600);
            $process->run();

            if (! $process->isSuccessful()) {
                $details = trim($process->getErrorOutput() ?: $process->getOutput());

                throw new RuntimeException(
                    'A atualização falhou'.($details ? ': '.$details : '.')
                );
            }

            return trim($process->getOutput());
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
