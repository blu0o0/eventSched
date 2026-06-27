<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's name.
     */
    public function updateName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->name = $request->name;
        $user->save();

        return response()->json([
            'message' => 'Name updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Update the authenticated user's role.
     */
    public function updateRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|in:SBO BSIT WMAD,SBO BSIT NETSEC,SBO BSA,SBL BSLEA,SSC OFFICER,FACULTY/STAFF,STUDENT',
        ]);

        $user = $request->user();
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'Role updated successfully.',
            'user' => $user,
        ]);
    }
}