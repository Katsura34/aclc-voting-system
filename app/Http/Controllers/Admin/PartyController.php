<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:parties,name',
                'acronym' => 'required|string|max:10|unique:parties,acronym',
                'color' => 'required|string|max:7',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'description' => 'nullable|string'
            ]);

            DB::beginTransaction();
            
            try {
                if ($request->hasFile('logo')) {
                    $file = $request->file('logo');
                    if (!$file->isValid()) {
                        throw new \Exception('Invalid logo file uploaded.');
                    }
                    $validated['logo'] = $file->store('logos', 'public');
                }

                Party::create($validated);
                
                DB::commit();

                \Log::info('Party created', ['name' => $validated['name']]);

                return redirect()->route('admin.parties.index')
                    ->with('success', 'Party created successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Clean up uploaded file if exists
                if (isset($validated['logo'])) {
                    Storage::disk('public')->delete($validated['logo']);
                }
                
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Party creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to create party. Please try again.')
                ->withInput();
        }
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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:parties,name,' . $party->id,
                'acronym' => 'required|string|max:10|unique:parties,acronym,' . $party->id,
                'color' => 'required|string|max:7',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'description' => 'nullable|string'
            ]);

            DB::beginTransaction();
            
            try {
                $oldLogo = $party->logo;
                
                if ($request->hasFile('logo')) {
                    $file = $request->file('logo');
                    if (!$file->isValid()) {
                        throw new \Exception('Invalid logo file uploaded.');
                    }
                    $validated['logo'] = $file->store('logos', 'public');
                }

                $party->update($validated);
                
                // Delete old logo only after successful update
                if ($request->hasFile('logo') && $oldLogo) {
                    Storage::disk('public')->delete($oldLogo);
                }
                
                DB::commit();

                \Log::info('Party updated', ['party_id' => $party->id]);

                return redirect()->route('admin.parties.index')
                    ->with('success', 'Party updated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                
                // Clean up newly uploaded file if exists
                if (isset($validated['logo']) && $validated['logo'] !== $oldLogo) {
                    Storage::disk('public')->delete($validated['logo']);
                }
                
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Party update error: ' . $e->getMessage(), [
                'party_id' => $party->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to update party. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified party from storage.
     */
    public function destroy(Party $party)
    {
        try {
            DB::beginTransaction();
            
            try {
                // Check if party has candidates
                if ($party->candidates()->count() > 0) {
                    return redirect()->route('admin.parties.index')
                        ->with('error', 'Cannot delete party with existing candidates!');
                }

                $logo = $party->logo;
                
                $party->delete();
                
                // Delete logo only after successful deletion
                if ($logo) {
                    Storage::disk('public')->delete($logo);
                }
                
                DB::commit();

                \Log::info('Party deleted', ['party_id' => $party->id]);

                return redirect()->route('admin.parties.index')
                    ->with('success', 'Party deleted successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Party deletion error: ' . $e->getMessage(), [
                'party_id' => $party->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.parties.index')
                ->with('error', 'Failed to delete party. Please try again.');
        }
    }
}
