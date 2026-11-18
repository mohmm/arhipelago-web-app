<x-mail::message>
# {{ __('Confirm your email address') }}

{{ __('Hi :name,', ['name' => $name]) }}

{{ __('Please confirm this is the right address for your Smartsense account. Your invoices and any notice about your subscription will be sent here.') }}

<x-mail::button :url="$url">
{{ __('Confirm my address') }}
</x-mail::button>

{{ trans_choice('The link works for :minutes minute. You can keep using Smartsense in the meantime — nothing is waiting on this.|The link works for :minutes minutes. You can keep using Smartsense in the meantime — nothing is waiting on this.', $minutes, ['minutes' => $minutes]) }}

<x-mail::subcopy>
{{ __('If you did not create a Smartsense account, you can ignore this email.') }}
</x-mail::subcopy>
</x-mail::message>
