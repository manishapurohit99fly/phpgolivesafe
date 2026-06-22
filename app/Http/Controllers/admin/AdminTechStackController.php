<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TechStack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTechStackController extends Controller
{
    public function index(): View
    {
        $techStacks = TechStack::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.tech-stack.index', compact('techStacks'));
    }

    public function create(): View
    {
        return view('admin.tech-stack.add');
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'       => ['required', 'string', 'max:100', 'unique:' . config('tables.tech_stacks') . ',name'],
                'status'     => ['required', 'in:0,1'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ]);

            TechStack::create([
                'name'       => $validated['name'],
                'status'     => $validated['status'],
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Tech Stack created successfully.',
                'redirect_url' => route('admin.tech-stack.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    public function edit(int $id): View
    {
        $techStack = TechStack::findOrFail($id);
        return view('admin.tech-stack.edit', compact('techStack'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $techStack = TechStack::findOrFail($id);

            $validated = $request->validate([
                'name'       => ['required', 'string', 'max:100', 'unique:' . config('tables.tech_stacks') . ',name,' . $id],
                'status'     => ['required', 'in:0,1'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ]);

            $techStack->update([
                'name'       => $validated['name'],
                'status'     => $validated['status'],
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Tech Stack updated successfully.',
                'redirect_url' => route('admin.tech-stack.index'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $techStack = TechStack::find($request->id);

        if (!$techStack) {
            return response()->json(['status' => 'error', 'message' => 'Tech Stack not found.']);
        }

        // Nullify categories instead of blocking delete
        $techStack->categories()->update(['tech_stack_id' => null]);
        $techStack->delete();

        return response()->json(['status' => 'success', 'message' => 'Tech Stack deleted successfully.']);
    }
}
