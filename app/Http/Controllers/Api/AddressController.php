<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->addresses()->orderByDesc('is_default')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'street' => 'required|string|max:255',
            'street_2' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'nullable|string|size:2',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();

        // Première adresse → par défaut
        if ($user->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        // Si on définit cette adresse par défaut, retirer le défaut des autres
        if (! empty($validated['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        if ($address->is_default) {
            $user->update(['default_address_id' => $address->id]);
        }

        return response()->json($address, 201);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'street' => 'required|string|max:255',
            'street_2' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'nullable|string|size:2',
            'is_default' => 'boolean',
        ]);

        // Si on définit cette adresse par défaut
        if (! empty($validated['is_default'])) {
            // Retirer le défaut des autres adresses
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            // Mettre à jour l'utilisateur
            $request->user()->update(['default_address_id' => $address->id]);
        }

        // Si on retire le défaut de cette adresse
        if (isset($validated['is_default']) && $validated['is_default'] === false) {
            if ($request->user()->default_address_id === $address->id) {
                $request->user()->update(['default_address_id' => null]);
            }
        }

        $address->update($validated);

        return response()->json($address);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $newDefault = $request->user()->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
                $request->user()->update(['default_address_id' => $newDefault->id]);
            } else {
                $request->user()->update(['default_address_id' => null]);
            }
        }

        return response()->json(['message' => 'Adresse supprimée.']);
    }
}