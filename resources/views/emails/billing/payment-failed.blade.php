@extends('emails.billing.layout')
@section('content')
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#1C1C1E;">Не удалось обработать ваш платёж</h1>
<p style="margin:0 0 16px;">Привет, {{ $userName }}!</p>
<p style="margin:0 0 16px;">Последний платёж за Kinhold ({{ $familyName }}) не прошёл. Обычно это означает, что срок действия карты истёк или возникла временная проблема на стороне банка.</p>
<p style="margin:0 0 16px;"><strong>Пока ничего не изменилось.</strong> У вас и вашей семьи по-прежнему есть полный доступ. Мы будем автоматически повторять попытку списания в течение следующих нескольких дней.</p>
<p style="margin:0 0 24px;">Чтобы избежать перебоев, вы можете обновить данные карты уже сейчас в настройках оплаты.</p>
<p style="margin:0 0 24px;">
<a href="{{ $portalUrl }}" style="display:inline-block;background-color:#1C1C1E;color:#FAF8F5;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Обновить способ оплаты</a>
</p>
<p style="margin:0;color:#6B6966;">Вопросы? Просто ответьте на это письмо.</p>
@endsection
