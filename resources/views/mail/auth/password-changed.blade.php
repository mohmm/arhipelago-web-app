<x-mail::message>
# {{ __('Your password was changed') }}

{{ __('Hi :name,', ['name' => $name]) }}

{{ __('The password on your :app account was just changed.', ['app' => config('app.name')]) }}

@if ($ipAddress)
<x-mail::details :rows="[__('Changed from') => $ipAddress]" />
@endif

{{ __('If this was you, there is nothing to do.') }}

{{ __('If it was not, write to :email straight away — do not use any link in this message to sign in.', ['email' => $supportEmail]) }}
</x-mail::message>
