@extends('emails.billing.layout')
@section('content')
@php
    $d = abs($daysRemaining) % 100;
    $last = $d % 10;
    $daysWord = $last === 1 && $d !== 11 ? 'день' : ($last >= 2 && $last <= 4 && ($d < 12 || $d > 14) ? 'дня' : 'дней');
@endphp
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#1C1C1E;">Напоминание об оплате Kinhold</h1>
<p style="margin:0 0 16px;">Привет, {{ $userName }}!</p>
<p style="margin:0 0 16px;">Прошло несколько дней с тех пор, как мы сообщили вам о неудачном платеже за {{ $familyName }}. Полный доступ сохранён, но если нам не удастся списать оплату в течение следующих {{ $daysRemaining }} {{ $daysWord }}, функции ИИ будут приостановлены, а новые загрузки — ограничены.</p>
<p style="margin:0 0 16px;">В любом случае ваши данные в безопасности. Мы никогда не удаляем файлы из-за проблем с оплатой.</p>
<p style="margin:0 0 24px;">
<a href="{{ $portalUrl }}" style="display:inline-block;background-color:#1C1C1E;color:#FAF8F5;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Обновить способ оплаты</a>
</p>
<p style="margin:0;color:#6B6966;">Спасибо — мы будем рады и дальше помогать вашей семье оставаться организованной.</p>
@endsection
