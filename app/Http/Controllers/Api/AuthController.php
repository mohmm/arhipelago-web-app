<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\MeResource;
use App\Models\User;
use App\Services\Accounts\ProfileQuery;
use App\Services\Legal\RecordLegalAcceptance;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Signing up, in and out of the app.
 *
 * Every sign-in answers with the traveller's profile and a token the app
 * sends as `Authorization: Bearer …` from then on.
 */
class AuthController extends Controller
{
    /** Wrong passwords allowed per address and network before a pause. */
    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private ProfileQuery $profiles,
        private RecordLegalAcceptance $acceptances,
    ) {}

    /**
     * Creating an account accepts the Terms and the Privacy Policy linked on
     * the sign-up screen, so the acceptance is recorded with the account.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'first_name' => $request->validated('first_name'),
                'last_name' => $request->validated('last_name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'locale' => $request->validated('locale') ?? app()->getLocale(),
            ]);

            $user->assignRole('customer');
            $this->acceptances->atRegistration($user, $request);

            return $user;
        });

        event(new Registered($user));

        return $this->signedIn($user, $request, Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $key = Str::transliterate(Str::lower($request->validated('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => RateLimiter::availableIn($key),
                    'minutes' => ceil(RateLimiter::availableIn($key) / 60),
                ]),
            ]);
        }

        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user == null || ! Hash::check($request->validated('password'), $user->password)) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($key);

        return $this->signedIn($user, $request);
    }

    /**
     * Signs this device out; other devices stay signed in.
     */
    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    /**
     * Emails a link to choose a new password on the website.
     *
     * Answers the same whether or not the address has an account, so the
     * form cannot be used to find out who uses the app.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink(['email' => $request->validated('email')]);

        return response()->json([
            'message' => __('If an account exists for this address, we have emailed a link to choose a new password.'),
        ]);
    }

    private function signedIn(User $user, Request $request, int $status = Response::HTTP_OK): JsonResponse
    {
        $token = $user->createToken($request->input('device_name') ?: 'Smartsense app')->plainTextToken;

        return MeResource::make($this->profiles->load($user, $user))
            ->additional(['token' => $token])
            ->response()
            ->setStatusCode($status);
    }
}
