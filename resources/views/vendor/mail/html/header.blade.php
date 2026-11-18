@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{{-- Images are blocked by default in most mailboxes, so the wordmark is
     spelled out in the alt text rather than leaving a broken box where the
     sender's name should be. The width attribute is for Outlook, which ignores
     the stylesheet's size. --}}
<img src="{{ config('mail.logo_url') ?: asset('images/logo.png') }}" class="logo" width="240" alt="{{ config('app.name') }}">
</a>
</td>
</tr>
