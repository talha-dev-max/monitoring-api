<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->has('paginate') && $request->boolean('paginate')) {
                $perPage = $request->input('per_page', 15);
                $suppliers = $this->supplierService->getPaginatedSuppliers($perPage);
            } else {
                $suppliers = $this->supplierService->getAllSuppliers();
            }

            return response()->json([
                'status' => 'success',
                'data' => $suppliers,
                'message' => 'Suppliers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve suppliers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Supplier $supplier): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $supplier,
                'message' => 'Supplier retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->createSupplier($request->validated());

            return response()->json([
                'status' => 'success',
                'data' => $supplier,
                'message' => 'Supplier created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        try {
            $updatedSupplier = $this->supplierService->updateSupplier($supplier->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'data' => $updatedSupplier,
                'message' => 'Supplier updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        try {
            $this->supplierService->deleteSupplier($supplier->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function active(): JsonResponse
    {
        try {
            $suppliers = $this->supplierService->getActiveSuppliers();

            return response()->json([
                'status' => 'success',
                'data' => $suppliers,
                'message' => 'Active suppliers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve active suppliers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function backup(): JsonResponse
    {
        try {
            $suppliers = $this->supplierService->getBackupSuppliers();

            return response()->json([
                'status' => 'success',
                'data' => $suppliers,
                'message' => 'Backup suppliers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve backup suppliers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Supplier $supplier): JsonResponse
    {
        try {
            $updatedSupplier = $this->supplierService->toggleSupplierStatus($supplier->id);

            return response()->json([
                'status' => 'success',
                'data' => $updatedSupplier,
                'message' => 'Supplier status toggled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle supplier status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}