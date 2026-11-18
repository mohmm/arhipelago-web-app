@props(['rows' => []])
{{-- Label/value pairs — an amount, a plan, a date. A markdown table would give
     these a header row they do not need, and the header is what makes a
     two-line receipt read like a spreadsheet. --}}
<table class="details" width="100%" cellpadding="0" cellspacing="0" role="presentation">
@foreach ($rows as $label => $value)
@if ($value !== null && $value !== '')
<tr>
<td class="label">{{ $label }}</td>
<td class="value">{{ $value }}</td>
</tr>
@endif
@endforeach
</table>
