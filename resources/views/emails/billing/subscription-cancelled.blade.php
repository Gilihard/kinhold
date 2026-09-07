@extends('emails.billing.layout')
@section('content')
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#1C1C1E;">Ваша подписка Kinhold закончилась</h1>
<p style="margin:0 0 16px;">Привет, {{ $userName }}!</p>
<p style="margin:0 0 16px;">Ваша подписка для {{ $familyName }} закончилась. Жаль, что вы уходите.</p>
<p style="margin:0 0 16px;"><strong>Данные вашей семьи в безопасности.</strong> Войдите в любой момент, чтобы выгрузить их, или оформить новую подписку, если передумаете.</p>
<p style="margin:0 0 24px;">Если что-то не оправдало ваших ожиданий, нам правда важно услышать об этом — просто ответьте на это письмо.</p>
<p style="margin:0 0 24px;">
<a href="{{ $appUrl }}" style="display:inline-block;background-color:#1C1C1E;color:#FAF8F5;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Войти в Kinhold</a>
</p>
<p style="margin:0;color:#6B6966;">Спасибо, что попробовали Kinhold.</p>
@endsection
