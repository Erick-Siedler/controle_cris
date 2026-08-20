<?php

namespace Tests\Feature;

use App\Services\SystemUpdater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class SystemUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_displays_the_system_update_button(): void
    {
        $response = $this->get(route('groups.index'));

        $response->assertOk();
        $response->assertSee('Atualizar sistema');
        $response->assertSee(route('system.update'), false);
    }

    public function test_update_action_reports_success(): void
    {
        $this->mock(SystemUpdater::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->once()->andReturn('Tudo certo.');
        });

        $response = $this->from(route('groups.index'))
            ->post(route('system.update'));

        $response->assertRedirect(route('groups.index'));
        $response->assertSessionHas('success');
    }

    public function test_update_action_reports_a_readable_error(): void
    {
        $this->mock(SystemUpdater::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->once()->andThrow(
                new RuntimeException('Falha simulada.')
            );
        });

        $response = $this->from(route('groups.index'))
            ->post(route('system.update'));

        $response->assertRedirect(route('groups.index'));
        $response->assertSessionHas('error', fn ($message) => (
            str_contains($message, 'Falha simulada.')
        ));
    }
}
