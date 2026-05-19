<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccount\StoreBankAccountRequest;
use App\Http\Requests\BankAccount\UpdateBankAccountRequest;

use App\Http\Resources\BankAccountResource;
use App\Services\BankAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct(private readonly BankAccountService $bankAccountService) {}

    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */

        $perPage = (int) ($request->query('per_page', 15) || 15);
        $data = $this->bankAccountService->list($request->only(['search', 'deleted']), $perPage);
        return response()->json($data);
    }

    public function store(StoreBankAccountRequest $request): BankAccountResource
    {
        return new BankAccountResource(
            $this->bankAccountService->create($request->validated())
        );
    }

    public function show(string $bankAccount): BankAccountResource
    {
        return new BankAccountResource(
            $this->bankAccountService->findOrFail($bankAccount)
        );
    }

    public function update(UpdateBankAccountRequest $request, string $bankAccount): BankAccountResource
    {
        return new BankAccountResource(
            $this->bankAccountService->update($bankAccount, $request->validated())
        );
    }

    public function destroy(string $bankAccount): JsonResponse
    {
        $this->bankAccountService->delete($bankAccount);
        return response()->json(['message' => 'Deleted.']);
    }
}
