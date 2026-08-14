<?php

namespace ProjectsManager\Http\Controllers\Auth;

use ProjectsManager\User;
use ProjectsManager\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'timezones' => $this->timezones(),
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'timezone' => ['required', 'string', 'max:255', Rule::in(array_keys($this->timezones()))],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $user = User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'avatar' => null,
            'job_title' => $request->input('job_title'),
            'department' => $request->input('department'),
            'status' => 'active',
            'timezone' => $request->input('timezone'),
            'password' => Hash::make($request->input('password')),
        ]);

        Auth::login($user);

        return redirect()->intended('/home');
    }

    /**
     * Get supported timezones for registration.
     *
     * @return array
     */
    private function timezones(): array
    {
        return [
            'UTC' => 'UTC',
            'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh',
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Asia/Shanghai' => 'Asia/Shanghai',
            'Asia/Singapore' => 'Asia/Singapore',
            'Europe/London' => 'Europe/London',
            'Europe/Paris' => 'Europe/Paris',
            'Europe/Berlin' => 'Europe/Berlin',
            'America/New_York' => 'America/New_York',
            'America/Chicago' => 'America/Chicago',
            'America/Los_Angeles' => 'America/Los_Angeles',
        ];
    }
}
