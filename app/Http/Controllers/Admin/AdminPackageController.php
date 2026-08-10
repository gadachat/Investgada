<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvestmentPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPackageController extends Controller
{
    public function index()
    {
        $packages = InvestmentPackage::orderBy('sort_order')->orderBy('min_amount')->paginate(15);
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.form', ['package' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'category'       => 'required|in:crypto,forex,stocks,bonds,binary,mixed',
            'type'           => 'required|in:fixed,variable,profit_share',
            'min_amount'     => 'required|numeric|min:0',
            'max_amount'     => 'nullable|numeric|gt:min_amount',
            'return_rate'    => 'required|numeric|min:0|max:100',
            'return_type'    => 'required|in:daily,weekly,monthly,maturity',
            'duration_days'  => 'required|integer|min:1',
            'cycle_days'     => 'required|integer|min:1',
            'total_return_cap' => 'nullable|numeric|min:0',
        ]);

        InvestmentPackage::create([
            'name'             => $request->name,
            'slug'             => Str::slug($request->name) . '-' . Str::random(6),
            'description'      => $request->description,
            'category'         => $request->category,
            'type'             => $request->type,
            'min_amount'       => $request->min_amount,
            'max_amount'       => $request->max_amount,
            'return_rate'      => $request->return_rate,
            'return_type'      => $request->return_type,
            'duration_days'    => $request->duration_days,
            'cycle_days'       => $request->cycle_days,
            'total_return_cap' => $request->total_return_cap ?? 0,
            'principal_return' => $request->boolean('principal_return'),
            'compounding'      => $request->boolean('compounding'),
            'is_active'        => $request->boolean('is_active', true),
            'featured'         => $request->boolean('featured'),
            'sort_order'       => $request->integer('sort_order', 0),
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(InvestmentPackage $package)
    {
        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $request, InvestmentPackage $package)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string',
            'category'       => 'required|in:crypto,forex,stocks,bonds,binary,mixed',
            'type'           => 'required|in:fixed,variable,profit_share',
            'min_amount'     => 'required|numeric|min:0',
            'max_amount'     => 'nullable|numeric|gt:min_amount',
            'return_rate'    => 'required|numeric|min:0|max:100',
            'return_type'    => 'required|in:daily,weekly,monthly,maturity',
            'duration_days'  => 'required|integer|min:1',
            'cycle_days'     => 'required|integer|min:1',
            'total_return_cap' => 'nullable|numeric|min:0',
        ]);

        $package->update($request->only([
            'name', 'description', 'category', 'type', 'min_amount', 'max_amount',
            'return_rate', 'return_type', 'duration_days', 'cycle_days',
            'total_return_cap', 'principal_return', 'compounding', 'is_active',
            'featured', 'sort_order',
        ]));

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    public function toggle(InvestmentPackage $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        return back()->with('success', 'Package ' . ($package->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroy(InvestmentPackage $package)
    {
        if ($package->investments()->count() > 0) {
            return back()->with('error', 'Cannot delete a package with existing investments.');
        }
        $package->delete();
        return back()->with('success', 'Package deleted.');
    }
}
