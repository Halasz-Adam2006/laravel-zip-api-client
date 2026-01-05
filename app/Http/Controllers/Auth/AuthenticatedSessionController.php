<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Pail\ValueObjects\Origin\Console;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Use the configured API base URL (config/services.php -> api.base_url)
        // and make the request with the HTTP client. The project previously
        // tried to call a non-existent `api()` macro on the Http client.
        $response = Http::baseUrl(config('services.api.base_url'))->post('/login', [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $responseBody = json_decode($response->body(), true);
            $token = $responseBody['token'];
            $user = $responseBody['user'];
            session([
                'api_token' => $token,
                'user_name' => $user['name'],
                'user_email'=> $user['email']
            ]);

            // Return with a flash value so the frontend can log the username
            // to the browser console after a successful login.
            return redirect()->intended('/counties')->with('user_name', $user['name'])->with('user_email', $user['email'])->with('just_logged_in_username', $user['name'])->with('api_token', $token);
        }

        return redirect()->intended('/login')->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        session()->forget('api_token');

        return redirect('/');
    }
}
