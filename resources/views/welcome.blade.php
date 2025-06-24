<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tepar Tekstil Öneri Sistemi</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    {{-- Kullandığın fontu buraya ekleyebilirsin, Figtree güzel bir başlangıç --}}
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        /* Temel sıfırlama ve sayfa ayarları */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%; /* Sayfanın tamamını kaplaması için */
        }

        body {
            font-family: 'Figtree', sans-serif; /* Font ailesi */
            line-height: 1.6;
            color: #333; /* Varsayılan metin rengi */
            background-color: #f3f4f6; /* Arka plan resmi yüklenemezse görünecek renk */
            /* Arka plan resmi, asset() helper'ı ile public klasöründen çağrılacak */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column; /* Navigasyon ve içerik için */
            align-items: center; /* İçeriği yatayda ortala */
            justify-content: center; /* İçeriği dikeyde ortala (eğer sadece ana içerik varsa) */
            min-height: 100vh; /* Viewport yüksekliği kadar olmasını sağla */
            position: relative; /* Auth linklerinin konumlandırılması için */
            text-align: center; /* İçerikteki metinleri ortala */
        }

        .main-logo-link {
            position: absolute;
            top: 25px;
            left: 25px;
            z-index: 10;
        }

        .main-logo-link img {
            height: 40px; /* Logonuzun yüksekliğini ayarlayın */
            width: auto; /* Genişlik otomatik ayarlansın */
        }

        /* Sağ üstteki Giriş/Kayıt linkleri için stil */
        .auth-links {
            position: absolute;
            top: 25px;
            right: 25px;
            z-index: 10; /* Diğer içeriklerin üzerinde olması için */
        }

        .auth-links a {
            font-weight: 600;
            color: #ffffff; /* Link rengi (arka plana göre ayarlanabilir) */
            background-color: rgba(0, 0, 0, 0.4); /* Linkler için yarı saydam koyu arka plan */
            padding: 10px 18px;
            margin-left: 10px;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 0.9rem;
        }

        .auth-links a:hover {
            background-color: rgba(0, 0, 0, 0.6); /* Hover durumunda daha koyu arka plan */
            color: #f0f0f0;
        }

        /* Ortadaki içerik alanı için stil */
        .content-wrapper {
            background-color:black; /* İçerik için yarı saydam beyaz arka plan */
            padding: 30px 40px; /* Daha fazla padding */
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 700px; /* Biraz daha geniş */
            width: 90%;
            margin-top: 60px; /* auth-links ile çakışmaması için (opsiyonel) */


        }

        .content-wrapper h1 {
            font-size: 2.8rem; /* Biraz daha büyük başlık */
            font-weight: 600;
            color: #1a202c; /* Koyu başlık rengi */
            margin-bottom: 25px;
        }

        .content-wrapper p {
            font-size: 1.2rem; /* Biraz daha büyük paragraf */
            color: #4a5568; /* Orta gri metin */
            margin-bottom: 30px;
        }

        /* Opsiyonel: Ana Sayfa Butonu için */
        .btn-primary {
            display: inline-block;
            background-color: #4F46E5; /* Örnek birincil renk (Indigo) */
            color: white;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            transition: background-color 0.3s ease;

        }

        .btn-primary:hover {
            background-color: #4338CA; /* Hover durumunda biraz daha koyu */
        }


    </style>
</head>
{{-- Arka plan resmini body etiketine style ile ekliyoruz --}}
{{-- 'images/backgrounds/default_background.jpg' yolunu kendi resminize göre güncelleyin --}}
<body style="background-image: url('{{ asset(env('WELCOME_BACKGROUND_IMAGE', 'images/backgrounds/back4.jpg')) }}');">

{{-- Sol Üst Logo --}}
<a href="{{ url('/') }}" class="main-logo-link">
    <img src="{{ asset('images/backgrounds/teparlogo1.png') }}" alt="Tepar Tekstil Logo"> {{-- Logo yolunu güncelleyin --}}
</a>


@if (Route::has('login'))
    <div class="auth-links">

        @auth
            {{-- Kullanıcı giriş yapmışsa Dashboard'a (veya ana öneri sayfasına) yönlendir --}}
            <a href="{{ url('/dashboard') }}">Ana Sayfa</a> {{-- veya route('suggestions.index') --}}
        @else
            <a href="{{ route('login') }}">Giriş Yap</a>

            @if (Route::has('register'))
                <a href="{{ route('register') }}">Kayıt Ol</a>
            @endif
        @endauth
    </div>
@endif



<div class="content-wrapper">
    <h1>Tepar Tekstil Öneri Sistemi</h1>
    <p>Fikirlerinizi bizimle paylaşarak şirketimizin gelişimine katkıda bulunun.</p>

    @guest {{-- Sadece misafir kullanıcılar görsün --}}
    <a href="{{ route('login') }}" class="btn-primary">
        Öneri Vermek İçin Giriş Yapın
    </a>
    @endguest
    @auth {{-- Sadece giriş yapmış kullanıcılar görsün --}}
    <a href="{{ route('suggestions.create') }}" class="btn-primary">
        Yeni Öneri Ver
    </a>
    @endauth
</div>

</body>
</html>
