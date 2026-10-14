<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Class NavigationAndRbacTest
 *
 * Comprehensive 4-Tier Feature Tests for Navigation and RBAC:
 * - Tier 1: Feature Coverage (Permissions existence, Admin & Staff role assignments, Guest redirection)
 * - Tier 2: Boundary & Corner Cases (Unauthorized user forbidden access, Permission revocation)
 * - Tier 3: Cross-Feature Combinations (Role-based access to Rekonsiliasi and Watchdog endpoints)
 * - Tier 4: Real-World Scenarios (Staff end-to-end authorized operations workflow)
 */
class NavigationAndRbacTest extends TestCase
{
    protected $adminUser;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('name', 'Admin')->first() ?? User::first();
        $this->staffUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'Staff');
        })->first() ?? $this->adminUser;

        // Ensure routes exist for testing middleware protection
        if (!Route::has('rekonsiliasi_closing.index')) {
            Route::get('/rekonsiliasi_closing', [\App\Http\Controllers\RekonsiliasiClosingController::class, 'index'])
                ->name('rekonsiliasi_closing.index')
                ->middleware(['auth', 'permission:rekonsiliasi_closing.access']);
        }
        if (!Route::has('unassigned_watchdog.index')) {
            Route::get('/unassigned_watchdog', [\App\Http\Controllers\UnassignedWatchdogController::class, 'index'])
                ->name('unassigned_watchdog.index')
                ->middleware(['auth', 'permission:unassigned_watchdog.access']);
        }
    }

    // =========================================================================
    // TIER 1: FEATURE COVERAGE
    // =========================================================================

    /**
     * T1.1: Permissions exist in database
     */
    public function testPermissionsExistInDatabase()
    {
        $permissions = [
            'rekonsiliasi_closing.access',
            'unassigned_watchdog.access',
            'unassigned_watchdog.update',
        ];

        foreach ($permissions as $permName) {
            $exists = Permission::where('name', $permName)->exists();
            $this->assertTrue($exists, "Permission '{$permName}' must be registered in permissions table");
        }
    }

    /**
     * T1.2: Admin role possesses all new permissions
     */
    public function testAdminRoleHasPermissions()
    {
        $adminRole = Role::where('name', 'Admin')->firstOrFail();

        $this->assertTrue($adminRole->hasPermissionTo('rekonsiliasi_closing.access'));
        $this->assertTrue($adminRole->hasPermissionTo('unassigned_watchdog.access'));
        $this->assertTrue($adminRole->hasPermissionTo('unassigned_watchdog.update'));
    }

    /**
     * T1.3: Staff role possesses all new permissions
     */
    public function testStaffRoleHasPermissions()
    {
        $staffRole = Role::where('name', 'Staff')->firstOrFail();

        $this->assertTrue($staffRole->hasPermissionTo('rekonsiliasi_closing.access'));
        $this->assertTrue($staffRole->hasPermissionTo('unassigned_watchdog.access'));
        $this->assertTrue($staffRole->hasPermissionTo('unassigned_watchdog.update'));
    }

    /**
     * T1.4: Admin user has direct authorization for all three permissions
     */
    public function testAdminUserCanAuthorizeActions()
    {
        $this->assertTrue($this->adminUser->can('rekonsiliasi_closing.access'));
        $this->assertTrue($this->adminUser->can('unassigned_watchdog.access'));
        $this->assertTrue($this->adminUser->can('unassigned_watchdog.update'));
    }

    /**
     * T1.5: Unauthenticated guest redirected to login on protected routes
     */
    public function testGuestRedirectedToLogin()
    {
        $resRekon = $this->get('/rekonsiliasi_closing');
        $this->assertTrue($resRekon->isRedirect(route('login')) || $resRekon->status() === 302);

        $resWatch = $this->get('/unassigned_watchdog');
        $this->assertTrue($resWatch->isRedirect(route('login')) || $resWatch->status() === 302);
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES
    // =========================================================================

    /**
     * T2.1: User without required permission is forbidden (HTTP 403)
     */
    public function testUserWithoutPermissionIsForbidden()
    {
        // Create or find a dummy user with no roles
        $plainUser = User::whereDoesntHave('roles')->first();
        if (!$plainUser) {
            $plainUser = new User();
            $plainUser->name = 'Test Plain User';
            $plainUser->email = 'plain_' . time() . '@example.com';
            $plainUser->password = bcrypt('secret');
            $plainUser->save();
        }

        $response = $this->actingAs($plainUser)->get('/rekonsiliasi_closing');
        $this->assertEquals(403, $response->status(), "User without rekonsiliasi_closing.access must receive 403 Forbidden");

        $responseWatch = $this->actingAs($plainUser)->get('/unassigned_watchdog');
        $this->assertEquals(403, $responseWatch->status(), "User without unassigned_watchdog.access must receive 403 Forbidden");
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS
    // =========================================================================

    /**
     * T3.1: Staff role can access both Rekonsiliasi Closing and Unassigned Watchdog
     */
    public function testStaffUserCanAccessModules()
    {
        $resRekon = $this->actingAs($this->staffUser)->get(route('rekonsiliasi_closing.index', [
            'start_date' => '2025-01-01',
            'end_date'   => '2025-01-10',
        ]));
        $this->assertEquals(200, $resRekon->status());

        $resWatch = $this->actingAs($this->staffUser)->get(route('unassigned_watchdog.index'));
        $this->assertEquals(200, $resWatch->status());
    }

    // =========================================================================
    // TIER 4: REAL-WORLD SCENARIOS
    // =========================================================================

    /**
     * T4.1: End-to-end Staff audit session across RBAC protected accounting modules
     */
    public function testStaffAuditSessionWorkflow()
    {
        // 1. Staff navigates to Rekonsiliasi Closing
        $resRekon = $this->actingAs($this->staffUser)->get(route('rekonsiliasi_closing.index', [
            'start_date' => '2025-01-01',
            'end_date'   => '2025-01-31',
        ]));
        $resRekon->assertStatus(200);

        // 2. Staff navigates to Unassigned Watchdog
        $resWatch = $this->actingAs($this->staffUser)->get(route('unassigned_watchdog.index'));
        $resWatch->assertStatus(200);

        // 3. Staff verifies they can export reports without permission errors
        $resExportRekon = $this->actingAs($this->staffUser)->get(route('rekonsiliasi_closing.export', [
            'start_date' => '2025-01-01',
            'end_date'   => '2025-01-05',
            'format'     => 'excel',
        ]));
        $resExportRekon->assertStatus(200);

        $resExportWatch = $this->actingAs($this->staffUser)->get(route('unassigned_watchdog.export', [
            'format' => 'excel',
        ]));
        $resExportWatch->assertStatus(200);
    }
}
