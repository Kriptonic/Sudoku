<?php

namespace Kriptonic\App\Controllers;

use Kriptonic\App\Core\Request;
use Kriptonic\App\Models\User;

/**
 * Class AccountController
 *
 * This controller is used for account related activities.
 *
 * @package Kriptonic\App\Controllers
 * @author Christopher Sharman <christopher.p.sharman@gmail.com>
 */
class AccountController
{
    /**
     * Show the register page.
     *
     * @return \Kriptonic\App\Core\Response|\Kriptonic\App\Core\View
     */
    public function register()
    {
        return view('register');
    }

    /**
     * Create a new user account.
     *
     * @return \Kriptonic\App\Core\Redirect|\Kriptonic\App\Core\Response
     */
    public function store()
    {
        // Validate that we have the required fields.
        if (!Request::input('name') || !Request::input('password')) {
            // TODO: Proper flash system.
            $message = 'A username and password is required.';

            return view('register', compact('message'));
        }

        // Check to see if our unique fields have been taken already.
        $usernameCheck = User::query()->where('username', '=', Request::input('username'))->get();
        if ($usernameCheck->count()) {
            return view('register', ['message' => 'That username is already taken.']);
        }

        $emailCheck = User::query()->where('email', '=', Request::input('email'))->get();
        if ($emailCheck->count()) {
            return view('register', ['message' => 'That email is already taken.']);
        }

        $user = new User();
        $user->username = Request::input('name');
        $user->email = Request::input('email');
        $user->password = password_hash(Request::input('password'), PASSWORD_BCRYPT);
        $user->save();

        return redirect('login');
    }

    /**
     * Show the login page.
     *
     * @return \Kriptonic\App\Core\Response|\Kriptonic\App\Core\View
     */
    public function login()
    {
        return view('login');
    }

    /**
     * Verify a user's credentials and log them in if they match.
     *
     * @return \Kriptonic\App\Core\Redirect|\Kriptonic\App\Core\Response|\Kriptonic\App\Core\View
     */
    public function doLogin()
    {
        /** @var User $user */
        $user = User::query()
            ->where('username', '=', Request::input('username'))
            ->first();

        // Verify that the passwords match.
        if (!$user || !password_verify(Request::input('password'), $user->password)) {
            // TODO: Use a proper flash system so we can redirect back with the errors.
            $message = 'Account details incorrect.';

            return view('login', compact('message'));
        }

        $_SESSION['user_id'] = $user->id;

        // Back to the homepage.
        return redirect('');
    }

    /**
     * Log the user out.
     *
     * @return \Kriptonic\App\Core\Redirect|\Kriptonic\App\Core\Response
     */
    public function logout()
    {
        session_destroy();

        return redirect('');
    }
}
