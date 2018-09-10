<?php
/**
 * NOTICE OF LICENSE
 *
 * UNIT3D is open-sourced software licensed under the GNU General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Group;
use App\Rules\Captcha;
use \Toastr;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // Upon Successful Login
    protected $redirectTo = '/';

    // Max Attempts Until Lockout
    public $maxAttempts = 5;

    // Minutes Lockout
    public $decayMinutes = 60;

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function username()
    {
        return 'username';
    }

    /**
     * Validate The User Login Request
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function validateLogin(Request $request)
    {
        if (config('captcha.enabled') == true) {
            $this->validate($request, [
                $this->username() => 'required|string',
                'password' => 'required|string',
                'g-recaptcha-response' => new Captcha()
            ]);
        }

        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);

    }

    protected function authenticated(Request $request, $user)
    {
        $bannedGroup = Group::where('slug', '=', 'banned')->first();
        $validatingGroup = Group::where('slug', '=', 'validating')->first();
        $disabledGroup = Group::where('slug', '=', 'disabled')->first();

        if ($user->active == 0 || $user->group_id == $validatingGroup->id) {
            auth()->logout();
            $request->session()->flush();
            return redirect()->route('login')
                ->with(Toastr::error('This account has not been activated and is still in validating group. Please check your email for activation link. If you did not receive the activation code, please click "forgot password" and complete the steps.', 'Whoops!', ['options']));
        }

        if ($user->group_id == $bannedGroup->id) {
            auth()->logout();
            $request->session()->flush();
            return redirect()->route('login')
                ->with(Toastr::error('This account is Banned!', 'Whoops!', ['options']));
        }

        if ($user->group_id == $disabledGroup->id) {
            $user->group_id = 3;
            $user->can_upload = 1;
            $user->can_download = 1;
            $user->can_comment = 1;
            $user->can_invite = 1;
            $user->can_request = 1;
            $user->can_chat = 1;
            $user->disabled_at = null;
            $user->save();
            return redirect('/')
                ->with(Toastr::info('Welcome Back! Your Account Is No Longer Disabled!', $user->username, ['options']));
        }

        return redirect('/')
            ->with(Toastr::info('Welcome Back!', $user->username, ['options']));
    }
}
