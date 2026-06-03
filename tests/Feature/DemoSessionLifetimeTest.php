<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSessionLifetimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_mode_uses_shorter_session_lifetime(): void
    {
        config([
            'app.demo_mode' => true,
            'app.demo_session_lifetime' => 30,
            'session.lifetime' => 120,
        ]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame(30, config('session.lifetime'));
    }
}
