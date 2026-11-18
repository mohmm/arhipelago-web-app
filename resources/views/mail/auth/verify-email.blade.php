<x-mail::message>
# {{ __('Confirm your email address') }}

{{ __('Hi :name,', ['name' => $name]) }}

{{ __('Please confirm this is the right address for your :app account. Your invoices and any notice about your subscription will be sent here.', ['app' => config('app.name')]) }}

<x-mail::button :url="$url">
{{ __('Confirm my address') }}
</x-mail::button>

{{ trans_choice('The link works for :minutes minute. You can keep using :app in the meantime — nothing is waiting on this.|The link works for :minutes minutes. You can keep using :app in the meantime — nothing is waiting on this.', $minutes, ['minutes' => $minutes, 'app' => config('app.name')]) }}

<x-mail::subcopy>
{{ __('If you did not create a :app account, you can ignore this email.', ['app' => config('app.name')]) }}
</x-mail::subcopy>
</x-mail::message>
