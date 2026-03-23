<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Audit\Services\AuditLogService;
use App\Domain\Security\Services\PermissionMatrixService;
use App\Http\Controllers\Controller;
use App\Models\RolePermissionMatrix;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AdminPermissionMatrixController extends Controller
{
    public function __construct(
        private readonly PermissionMatrixService $permissionMatrix,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function index(Request $request): View
    {
        $roles = $this->permissionMatrix->roles();
        $keys = $this->permissionMatrix->permissionKeys();
        $matrix = $this->permissionMatrix->matrixSnapshot();

        return view('admin.permissions.matrix-index', [
            'roles' => $roles,
            'keys' => $keys,
            'matrix' => $matrix,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $roles = $this->permissionMatrix->roles();
        $keys = $this->permissionMatrix->permissionKeys();
        $input = (array) $request->input('matrix', []);

        DB::transaction(function () use ($roles, $keys, $input): void {
            RolePermissionMatrix::query()
                ->whereIn('role', $roles)
                ->whereIn('permission_key', $keys)
                ->delete();

            foreach ($roles as $role) {
                foreach ($keys as $key) {
                    $isAllowed = (bool) ((int) ($input[$role][$key] ?? 0));

                    RolePermissionMatrix::query()->create([
                        'role' => $role,
                        'permission_key' => $key,
                        'is_allowed' => $isAllowed,
                    ]);
                }
            }
        });

        $this->auditLogService->write([
            'event_type' => 'PERMISSION_MATRIX_UPDATED_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'PERMISSION_MATRIX',
            'entity_id' => null,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'roles' => $roles,
                'permission_keys' => $keys,
            ],
            'occurred_at' => now(),
        ]);

        return back()->with('notice', 'Permission matrix berhasil diperbarui.');
    }
}
