@props(['rows' => []])
@foreach ($rows as $label => $value)
@if ($value !== null && $value !== '')
{{ $label }}: {{ $value }}
@endif
@endforeach
