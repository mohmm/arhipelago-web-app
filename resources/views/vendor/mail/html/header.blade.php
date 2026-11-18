@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{{-- Images are blocked by default in most mailboxes, so the wordmark is
     spelled out in the alt text rather than leaving a broken box where the
     sender's name should be. --}}
<img src="{{ config('mail.logo_url') ?: asset('images/smartsense_logo.png') }}" class="logo" alt="Smartsense">
</a>
</td>
</tr>
