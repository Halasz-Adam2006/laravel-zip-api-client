<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Register with the API
        $response = Http::baseUrl(config('services.api.base_url'))->post('/register', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        if (!$response->successful()) {
            $errorBody = json_decode($response->body(), true);
            $errorMsg = $errorBody['message'] ?? 'Registration failed. Please try again.';
            return redirect()->back()->withErrors([
                'email' => $errorMsg,
            ]);
        }

        $responseBody = json_decode($response->body(), true);
        $token = $responseBody['token'] ?? null;
        $user = $responseBody['user'] ?? null;

        if (!$token || !$user) {
            return redirect()->back()->withErrors([
                'email' => 'Invalid API response. Token or user data missing.',
            ]);
        }

        // Create local user record
        $localUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($localUser));

        // Store API token in session for authenticated requests
        session([
            'api_token' => $token,
            'user_name' => $localUser->name,
            'user_email' => $localUser->email
        ]);

        Auth::login($localUser);

        return redirect(route('dashboard', absolute: false));
    }
}
