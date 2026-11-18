<x-mail::message>
# {{ __('You have been added to :app', ['app' => config('app.name')]) }}

{{ __('Hi :name,', ['name' => $name]) }}

@if ($invitedBy)
{{ __(':person has created a :app account for you.', ['person' => $invitedBy, 'app' => config('app.name')]) }}
@else
{{ __('An account has been created for you on :app.', ['app' => config('app.name')]) }}
@endif

{{ __('Choose your own password to finish setting it up — nobody else will know it.') }}

<x-mail::button :url="$url">
{{ __('Set my password') }}
</x-mail::button>

{{ trans_choice('The link works for :minutes minute. If it has expired by the time you open this, use "Forgot password" on the sign-in page and you will get a fresh one.|The link works for :minutes minutes. If it has expired by the time you open this, use "Forgot password" on the sign-in page and you will get a fresh one.', $minutes, ['minutes' => $minutes]) }}

<x-mail::subcopy>
{{ __('Sign in at :url', ['url' => $loginUrl]) }}
</x-mail::subcopy>
</x-mail::message>
