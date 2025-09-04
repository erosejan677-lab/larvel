@php($cta = $reviewUrl)
<p>Hi {{ $displayName }},</p>
<p>Thanks for your order! We’d love your feedback. Click the button below to leave a quick review.</p>
<p><a href="{{ $cta }}" style="background:#5f1e64;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none;">Leave a Review</a></p>
<p>If the button doesn’t work, paste this in your browser: {{ $cta }}</p>
