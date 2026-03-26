<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class AdminController extends Controller
{
    protected AdminService $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * @OA\Get(
     *     path="/api/admin/accounts",
     *     tags={"Admin"},
     *     summary="Get all accounts (Admin only)",
     *     description="Retrieve all bank accounts in the system",
     *     security={{"api_key": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Accounts retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Account")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function getAllAccounts(Request $request): JsonResponse
    {
        $accounts = $this->adminService->getAllAccounts();
        
        return response()->json($accounts);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/accounts/{id}/block",
     *     tags={"Admin"},
     *     summary="Block account (Admin only)",
     *     description="Block a bank account with a specified reason",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", example="Suspicious activity detected")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account blocked successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function blockAccount(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        
        $this->adminService->blockAccount($id, $request->reason);
        
        return response()->json(['message' => 'Account blocked successfully']);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/accounts/{id}/unblock",
     *     tags={"Admin"},
     *     summary="Unblock account (Admin only)",
     *     description="Unblock a previously blocked bank account",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account unblocked successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function unblockAccount(int $id, Request $request): JsonResponse
    {
        $this->adminService->unblockAccount($id);
        
        return response()->json(['message' => 'Account unblocked successfully']);
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/accounts/{id}/close",
     *     tags={"Admin"},
     *     summary="Close account (Admin only)",
     *     description="Force close a bank account (admin override)",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account closed successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     )
     * )
     */
    public function closeAccount(int $id, Request $request): JsonResponse
    {
        $this->adminService->closeAccount($id);
        
        return response()->json(['message' => 'Account closed successfully']);
    }
}
