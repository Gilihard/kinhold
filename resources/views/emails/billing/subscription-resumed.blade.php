@extends('emails.billing.layout')
@section('content')
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#1C1C1E;">Всё готово — с возвращением!</h1>
<p style="margin:0 0 16px;">Привет, {{ $userName }}!</p>
<p style="margin:0 0 16px;">Оплата за {{ $familyName }} прошла успешно, и мы восстановили ваш прежний тариф. Всё снова работает как обычно.</p>
@if($restoredTier)
<p style="margin:0 0 16px;">Ваш тариф ИИ <strong>{{ ucfirst($restoredTier) }}</strong> снова активен, и полный объём хранилища восстановлен.</p>
@else
<p style="margin:0 0 16px;">Ваш полный объём хранилища восстановлен.</p>
@endif
<p style="margin:0 0 24px;">Спасибо, что остались с нами.</p>
<p style="margin:0 0 24px;">
<a href="{{ $appUrl }}" style="display:inline-block;background-color:#1C1C1E;color:#FAF8F5;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Открыть Kinhold</a>
</p>
@endsection
