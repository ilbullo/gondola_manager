<?php

// tests/Feature/Middleware/CheckRoleTest.php
namespace Tests\Feature\Middleware;

use App\Enums\UserRole;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_access_for_correct_role()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        $middleware = new CheckRole();
        
        $response = $middleware->handle(
            $request,
            function ($request) {
                return response('OK');
            },
            UserRole::ADMIN->value
        );

        $this->assertEquals('OK', $response->getContent());
    }

        #[Test]
    public function it_denies_access_for_incorrect_role()
    {
        $user = User::factory()->create(['role' => UserRole::USER]);
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new CheckRole();
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        
        $middleware->handle(
            $request,
            function ($request) {
                return response('OK');
            },
            UserRole::ADMIN->value
        );
    }

    #[Test]
    public function it_redirects_to_login_if_not_authenticated()
    {
        $request = Request::create('/test', 'GET');
        
        $middleware = new CheckRole();
        
        $response = $middleware->handle(
            $request,
            function ($request) {
                return response('OK');
            },
            UserRole::ADMIN->value
        );

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    #[Test]
    public function it_allows_access_for_multiple_roles()
    {
        $bancale = User::factory()->create(['role' => UserRole::BANCALE]);
        
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($bancale) {
            return $bancale;
        });

        $middleware = new CheckRole();
        
        $response = $middleware->handle(
            $request,
            function ($request) {
                return response('OK');
            },
            UserRole::ADMIN->value,
            UserRole::BANCALE->value
        );

        $this->assertEquals('OK', $response->getContent());
    }
}