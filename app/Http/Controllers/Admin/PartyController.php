<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartyController extends Controller
{
    /**
     * Display a listing of the parties.
     */
    public function index()
    {
        $parties = Party::withCount('candidates')->latest()->get();
        return view('admin.parties.index', compact('parties'));
    }

    /**
     * Show the form for creating a new party.
     */
    public function create()
    {
        return view('admin.parties.create');
    }

    /**
     * Store a newly created party in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:parties,name',
            'acronym' => 'required|string|max:10|unique:parties,acronym',
            'color' => 'required|string|max:7',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Party::create($validated);

        return redirect()->route('admin.parties.index')
            ->with('success', 'Party created successfully!');
    }

    /**
     * Display the specified party.
     */
    public function show(Party $party)
    {
        $party->load('candidates.position');
        return view('admin.parties.show', compact('party'));
    }

    /**
     * Show the form for editing the specified party.
     */
    public function edit(Party $party)
    {
        return view('admin.parties.edit', compact('party'));
    }

    /**
     * Update the specified party in storage.
     */
    public function update(Request $request, Party $party)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:parties,name,' . $party->id,
            'acronym' => 'required|string|max:10|unique:parties,acronym,' . $party->id,
            'color' => 'required|string|max:7',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($party->logo) {
                Storage::disk('public')->delete($party->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $party->update($validated);

        return redirect()->route('admin.parties.index')
            ->with('success', 'Party updated successfully!');
    }

    /**
     * Remove the specified party from storage.
     */
    public function destroy(Party $party)
    {
        // Check if party has candidates
        if ($party->candidates()->count() > 0) {
            return redirect()->route('admin.parties.index')
                ->with('error', 'Cannot delete party with existing candidates!');
        }

        // Delete logo if exists
        if ($party->logo) {
            Storage::disk('public')->delete($party->logo);
        }

        $party->delete();

        return redirect()->route('admin.parties.index')
            ->with('success', 'Party deleted successfully!');
    }
}
