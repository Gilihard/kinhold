@extends('emails.billing.layout')
@section('content')
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#1C1C1E;">Функции Kinhold AI приостановлены</h1>
<p style="margin:0 0 16px;">Привет, {{ $userName }}!</p>
<p style="margin:0 0 16px;">В течение недели нам не удалось списать оплату за {{ $familyName }}, поэтому мы приостановили вашу подписку на ИИ и ограничили новые загрузки бесплатным тарифом (5 ГБ).</p>
<p style="margin:0 0 16px;"><strong>Ваши данные в целости.</strong> Календарь, задачи, сейф, рецепты — всё на месте. Мы никогда ничего не удаляем из-за проблем с оплатой.</p>
<p style="margin:0 0 24px;">Как только оплата пройдёт, мы автоматически восстановим ваш прежний тариф.</p>
<p style="margin:0 0 24px;">
<a href="{{ $portalUrl }}" style="display:inline-block;background-color:#C4975A;color:#FAF8F5;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Восстановить подписку</a>
</p>
<p style="margin:0;color:#6B6966;">Мы на связи, когда будете готовы.</p>
@endsection
