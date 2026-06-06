<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = Cache::remember('role_all', 60, function () {
            return Role::select('id', 'title', 'created_by')->get()->toArray();
        });

        return response()->json($role);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
            ]);

            $validated['created_by'] = $request->user()->id;

            $role = Role::create($validated);

            return response()->json([
                'status' => 'success',
                'data' => $role
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        $data = $request->only([
            'title',
        ]);

        $role->update($data);

        return response()->json([
            'status' => 'success',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not found'
            ], 404);
        }

        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'This role deleted successfully'
        ], 200);
    }
}
