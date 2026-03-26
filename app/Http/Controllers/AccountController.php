<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\AddCoOwnerRequest;
use App\Http\Requests\AssignGuardianRequest;
use App\Http\Requests\ConvertAccountRequest;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class AccountController extends Controller
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * @OA\Get(
     *     path="/api/accounts",
     *     tags={"Accounts"},
     *     summary="Get user accounts",
     *     description="Retrieve all accounts belonging to the authenticated user",
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
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $accounts = $this->accountService->getUserAccounts($request->user());
        
        return response()->json($accounts);
    }

    /**
     * @OA\Post(
     *     path="/api/accounts",
     *     tags={"Accounts"},
     *     summary="Create new account",
     *     description="Create a new bank account for the authenticated user",
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", enum={"COURANT","EPARGNE","MINEUR"}, example="COURANT"),
     *             @OA\Property(property="initial_deposit", type="number", format="float", example=1000.00),
     *             @OA\Property(property="overdraft_limit", type="number", format="float", example=500.00),
     *             @OA\Property(property="interest_rate", type="number", format="float", example=3.5),
     *             @OA\Property(property="guardian_id", type="integer", example=2, description="Required for MINEUR accounts")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Account created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Account")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(CreateAccountRequest $request): JsonResponse
    {
        $account = $this->accountService->createAccount($request->validated(), $request->user());
        
        return response()->json($account, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/accounts/{id}",
     *     tags={"Accounts"},
     *     summary="Get account details",
     *     description="Retrieve detailed information about a specific account",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account details retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Account")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found"
     *     )
     * )
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $account = $this->accountService->getAccountDetails($id, $request->user());
        
        return response()->json($account);
    }

    /**
     * @OA\Post(
     *     path="/api/accounts/{id}/co-owners",
     *     tags={"Accounts"},
     *     summary="Add co-owner to account",
     *     description="Add another user as co-owner of a joint account",
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
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Co-owner added successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function addCoOwner(int $id, AddCoOwnerRequest $request): JsonResponse
    {
        $this->accountService->addCoOwner($id, $request->validated(), $request->user());
        
        return response()->json(['message' => 'Co-owner added successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/api/accounts/{id}/co-owners/{userId}",
     *     tags={"Accounts"},
     *     summary="Remove co-owner from account",
     *     description="Remove a co-owner from a joint account",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Co-owner removed successfully"
     *     )
     * )
     */
    public function removeCoOwner(int $id, int $userId, Request $request): JsonResponse
    {
        $this->accountService->removeCoOwner($id, $userId, $request->user());
        
        return response()->json(['message' => 'Co-owner removed successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/accounts/{id}/guardian",
     *     tags={"Accounts"},
     *     summary="Assign guardian to minor account",
     *     description="Assign an adult user as guardian for a minor account",
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
     *             required={"guardian_id"},
     *             @OA\Property(property="guardian_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Guardian assigned successfully"
     *     )
     * )
     */
    public function assignGuardian(int $id, AssignGuardianRequest $request): JsonResponse
    {
        $this->accountService->assignGuardian($id, $request->validated(), $request->user());
        
        return response()->json(['message' => 'Guardian assigned successfully']);
    }

    /**
     * @OA\Patch(
     *     path="/api/accounts/{id}/convert",
     *     tags={"Accounts"},
     *     summary="Convert minor account to current account",
     *     description="Convert a minor account to a current account when the user turns 18",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account converted successfully"
     *     )
     * )
     */
    public function convert(int $id, ConvertAccountRequest $request): JsonResponse
    {
        $this->accountService->convertMinorAccount($id, $request->user());
        
        return response()->json(['message' => 'Account converted successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/api/accounts/{id}",
     *     tags={"Accounts"},
     *     summary="Request account closure",
     *     description="Request closure of an account (requires all co-owners consent for joint accounts)",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Closure request processed"
     *     )
     * )
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $result = $this->accountService->requestAccountClosure($id, $request->user());
        
        return response()->json($result);
    }
}
