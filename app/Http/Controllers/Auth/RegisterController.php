<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'school_name' => 'required|string|max:255',
            'firstname'   => 'required|string|max:100',
            'lastname'    => 'required|string|max:100',
            'email'       => 'required|string|email|max:255|unique:users',
              'password'    => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $user = User::create([
              'school_name' => $request->school_name,
              'firstname'   => $request->firstname,
              'lastname'    => $request->lastname,
              'name'        => $request->firstname . ' ' . $request->lastname,
              'email'       => $request->email,
              'password'    => Hash::make($request->password),
        ]);

        // Store credentials in session for display
        $credentials = [
            'school_name' => $user->school_name,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'password' => $request->password, // plain password for display only
        ];
        return redirect()->route('register')->with('show_credentials', $credentials);
    }
}
