<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DeleteAccountRequest;
use App\Http\Requests\Api\UpdateAvatarRequest;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\MeResource;
use App\Models\User;
use App\Services\Accounts\DeleteAccount;
use App\Services\Accounts\ProfileQuery;
use App\Services\Media\ImageStore;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The signed-in traveller's own account: profile, language, what Maya knows
 * about them, password, avatar, and deleting it all.
 */
class MeController extends Controller
{
    /** Avatars are stored as squares of this many pixels. */
    private const AVATAR_SIZE = 512;

    public function __construct(
        private ProfileQuery $profiles,
        private ImageStore $images,
    ) {}

    public function show(Request $request): MeResource
    {
        return $this->me($request->user());
    }

    public function update(UpdateProfileRequest $request): MeResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->me($user);
    }

    public function updatePassword(UpdatePasswordRequest $request): Response
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);

        // Signs out every other device, which is what changing a password is for.
        $user->tokens()->whereKeyNot($user->currentAccessToken()->getKey())->delete();

        event(new PasswordReset($user));

        return response()->noContent();
    }

    public function updateAvatar(UpdateAvatarRequest $request): MeResource
    {
        $user = $request->user();
        $previous = $user->avatar_path;

        $user->forceFill([
            'avatar_path' => $this->images->storeSquare($request->file('avatar'), User::AVATAR_DIRECTORY, self::AVATAR_SIZE),
        ])->save();

        $this->images->delete($previous);

        return $this->me($user);
    }

    public function destroyAvatar(Request $request): MeResource
    {
        $user = $request->user();

        $this->images->delete($user->avatar_path);
        $user->forceFill(['avatar_path' => null])->save();

        return $this->me($user);
    }

    /**
     * Deletes the account and everything it published. Asks for the password,
     * so a phone left unlocked cannot do it.
     */
    public function destroy(DeleteAccountRequest $request, DeleteAccount $deleteAccount): Response
    {
        $deleteAccount->handle($request->user());

        return response()->noContent();
    }

    private function me(User $user): MeResource
    {
        return MeResource::make($this->profiles->load($user, $user));
    }
}
