<x-mail::message>
# {{ __('Reset your password') }}

{{ __('Hi :name,', ['name' => $name]) }}

{{ __('Someone asked to reset the password for your Smartsense account. If that was you, choose a new one here.') }}

<x-mail::button :url="$url">
{{ __('Choose a new password') }}
</x-mail::button>

{{ trans_choice('The link works for :minutes minute and can only be used once.|The link works for :minutes minutes and can only be used once.', $minutes, ['minutes' => $minutes]) }}

<x-mail::subcopy>
{{ __('If you did not ask for this, no action is needed — your password has not changed.') }}
</x-mail::subcopy>
</x-mail::message>
