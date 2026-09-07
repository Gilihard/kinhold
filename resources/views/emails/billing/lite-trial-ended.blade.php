@extends('emails.billing.layout')
@section('content')
<h1 style="margin:0 0 16px;font-size:22px;font-weight:600;color:#1C1C1E;">Пробная версия AI Lite завершилась</h1>
<p style="margin:0 0 16px;">Привет, {{ $userName }}!</p>
<p style="margin:0 0 16px;">Во время бесплатной пробной версии Kinhold для <strong>{{ $familyName }}</strong> доступ к AI Lite был включён без дополнительной платы. Теперь, когда пробный период завершился, ваш ИИ-ассистент переведён на бесплатный тариф (меньший дневной лимит сообщений).</p>
<p style="margin:0 0 16px;">Если вы хотите сохранить AI Lite или перейти на тариф Standard или Pro, выберите подходящий тариф в настройках оплаты. В остальном в вашем аккаунте ничего не изменилось.</p>
<p style="margin:0 0 24px;">
<a href="{{ $portalUrl }}" style="display:inline-block;background-color:#1C1C1E;color:#FAF8F5;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Выбрать тариф ИИ</a>
</p>
<p style="margin:0;color:#6B6966;">Спасибо, что попробовали Kinhold.</p>
@endsection
