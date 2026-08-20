<?php

namespace App\Http\Controllers;

use App\Services\SystemUpdater;
use Throwable;

class SystemUpdateController extends Controller
{
    public function __invoke(SystemUpdater $updater)
    {
        try {
            $updater->update();

            return back()->with(
                'success',
                'Sistema atualizado e arquivos de produção gerados com sucesso.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Não foi possível atualizar o sistema. '.$exception->getMessage()
            );
        }
    }
}
