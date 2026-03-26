<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransferRequest;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class TransferController extends Controller
{
    protected TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * @OA\Post(
     *     path="/api/transfers",
     *     tags={"Transfers"},
     *     summary="Create a new transfer",
     *     description="Initiate a money transfer from one account to another",
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"from_account_id","to_account_id","amount"},
     *             @OA\Property(property="from_account_id", type="integer", example=1),
     *             @OA\Property(property="to_account_id", type="integer", example=2),
     *             @OA\Property(property="amount", type="number", format="float", example=500.00),
     *             @OA\Property(property="description", type="string", example="Payment for services")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transfer initiated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Transfer")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or business rule violation"
     *     )
     * )
     */
    public function store(CreateTransferRequest $request): JsonResponse
    {
        $transfer = $this->transferService->createTransfer($request->validated(), $request->user());
        
        return response()->json($transfer, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/transfers/{id}",
     *     tags={"Transfers"},
     *     summary="Get transfer details",
     *     description="Retrieve detailed information about a specific transfer",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer details retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Transfer")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transfer not found"
     *     )
     * )
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $transfer = $this->transferService->getTransferDetails($id, $request->user());
        
        return response()->json($transfer);
    }

    /**
     * @OA\Get(
     *     path="/api/accounts/{id}/transactions",
     *     tags={"Transactions"},
     *     summary="Get account transactions",
     *     description="Retrieve transaction history for a specific account",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         @OA\Schema(type="string", enum={"TRANSFER","FEE","INTEREST"})
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transactions retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Transaction")
     *         )
     *     )
     * )
     */
    public function accountTransactions(int $id, Request $request): JsonResponse
    {
        $filters = $request->only(['type', 'date_from', 'date_to']);
        $transactions = $this->transferService->getAccountTransactions($id, $filters, $request->user());
        
        return response()->json($transactions);
    }

    /**
     * @OA\Get(
     *     path="/api/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Get transaction details",
     *     description="Retrieve detailed information about a specific transaction",
     *     security={{"api_key": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction details retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Transaction")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transaction not found"
     *     )
     * )
     */
    public function transactionDetails(int $id, Request $request): JsonResponse
    {
        $transaction = $this->transferService->getTransactionDetails($id, $request->user());
        
        return response()->json($transaction);
    }
}
