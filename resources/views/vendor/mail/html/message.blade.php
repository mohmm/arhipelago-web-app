<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{{-- Who is actually writing. A transactional mail from an unfamiliar domain
     with no identifiable sender is what both spam filters and people treat as
     suspicious. --}}
{{ __('Smartsense is a product of :company.', ['company' => config('company.legal_name')]) }}
{{ config('company.address') }}, {{ config('company.country') }}

© {{ date('Y') }} {{ config('company.legal_name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
