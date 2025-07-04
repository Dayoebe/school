<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public $admin;

    public function __construct(AdminService $admin)
    {
        $this->admin = $admin;
    }

    public function index(): View
    {
        $this->authorize('viewAny', [User::class, 'admin']);
        return view('pages.admin.index');
    }

    public function create(): View
    {
        $this->authorize('create', [User::class, 'admin']);
        return view('pages.admin.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', [User::class, 'admin']);
        $this->admin->createAdmin($request);
        return back()->with('success', 'Admin Created Successfully');
    }

    public function show(User $admin): View
    {
        $this->authorize('view', [$admin, 'admin']);
        return view('pages.admin.show', compact('admin'));
    }

    public function edit(User $admin): View
    {
        $this->authorize('update', [$admin, 'admin']);
        return view('pages.admin.edit', compact('admin'));
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        $this->authorize('update', [$admin, 'admin']);
        $this->admin->updateAdmin($admin, $request->except('_token', '_method'));
        return back()->with('success', 'Admin Updated Successfully');
    }

    public function destroy(User $admin): RedirectResponse
    {
        $this->authorize('delete', [$admin, 'admin']);
        $this->admin->deleteAdmin($admin);
        return back()->with('success', 'Admin Deleted Successfully');
    }
}
