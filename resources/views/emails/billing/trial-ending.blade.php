@extends('emails.billing.layout')
@section('content')
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#1C1C1E;">Пробная версия заканчивается через 3 дня</h1>
<p style="margin:0 0 16px;">Привет, {{ $userName }}!</p>
<p style="margin:0 0 16px;">Бесплатная пробная версия Kinhold для <strong>{{ $familyName }}</strong> заканчивается <strong>{{ $trialEndsAt }}</strong>. После этого мы автоматически начнём вашу подписку, используя сохранённый способ оплаты.</p>
<p style="margin:0 0 24px;">Никаких действий не требуется. Но если вы хотите изменить тариф, обновить карту или отменить подписку, сейчас самое время.</p>
<p style="margin:0 0 24px;">
<a href="{{ $portalUrl }}" style="display:inline-block;background-color:#1C1C1E;color:#FAF8F5;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Управлять подпиской</a>
</p>
<p style="margin:0;color:#6B6966;">Спасибо, что попробовали Kinhold.</p>
@endsection
