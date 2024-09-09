<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $changelogPath = base_path('CHANGELOG.md');
        $changelogContent = file_get_contents($changelogPath);
        // Version Extraction
        preg_match('/\d+\.\d+\.\d+/', $changelogContent, $matches);
        $version = @$matches[0]?? '';

        // Date Extraction
        preg_match('/\d{4}-\d{2}-\d{2}/', $changelogContent, $matches);
        $date = @$matches[0] ?? '';

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('Secret')->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Authentication Successful',
                'server_version' => $version,
                'last_release_date' => $date,
                'token' => $token,
                'user_id' => Auth::user()->id,
                'user_name' => Auth::user()->name,
                'loggedIn_user_role' => Auth::user()->role,
                'clinic_ids' => explode(',', Auth::user()->clinic_id),
                'password_update' => Auth::user()->password_updated,
                'program_id' => @explode(',', Auth::user()->program_id) ?? [],
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }
}