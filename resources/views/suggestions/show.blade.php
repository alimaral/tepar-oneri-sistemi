<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Öneri Detayı: ') }} {{ $suggestion->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ $suggestion->title }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        <strong>Gönderen:</strong> {{ $suggestion->user->name }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        <strong>Bölüm:</strong> {{ $suggestion->department->name }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        <strong>Durum:</strong>
                        @php
                            $statusClass = '';
                            if ($suggestion->status == 'new') $statusClass = 'bg-blue-100 text-blue-800';
                            elseif ($suggestion->status == 'in_progress') $statusClass = 'bg-yellow-100 text-yellow-800';
                            elseif ($suggestion->status == 'accepted') $statusClass = 'bg-green-100 text-green-800';
                            elseif ($suggestion->status == 'rejected') $statusClass = 'bg-red-100 text-red-800';
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                            {{ ucfirst(str_replace('_', ' ', $suggestion->status)) }}
                        </span>
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        <strong>Gönderilme Tarihi:</strong> {{ $suggestion->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mt-1 text-sm text-gray-600">
                        <strong>Son Güncelleme:</strong> {{ $suggestion->updated_at->format('d/m/Y H:i') }}
                    </p>

                    <div class="mt-4 prose max-w-none">
                        {!! nl2br(e($suggestion->body)) !!}
                    </div>

                    <div class="mt-6">
                        <a href="{{ url()->previous() }}" class="text-indigo-600 hover:text-indigo-900">« Geri Dön</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Yorumlar Bölümü --}}
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <h4 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Yorumlar ({{ $suggestion->comments->count() }})</h4>

        @if (session('success_comment')) {{-- Yorum eklendi mesajı için --}}
        <div class="mb-4 p-3 bg-green-100 dark:bg-green-700 text-green-700 dark:text-green-100 rounded-md text-sm">
            {{ session('success_comment') }}
        </div>
        @endif

        {{-- Yeni Yorum Ekleme Formu --}}
        @auth {{-- Sadece giriş yapmış kullanıcılar yorum ekleyebilsin --}}
        <form action="{{ route('suggestions.comments.store', $suggestion) }}" method="POST" class="mb-6">
            @csrf
            <div>
                <x-input-label for="comment_body" :value="__('Yorumunuzu Ekleyin')" class="sr-only" />
                <textarea name="body" id="comment_body" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600" placeholder="Yorumunuzu buraya yazın..." required></textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>
            <div class="mt-3">
                <x-primary-button>
                    {{ __('Yorum Gönder') }}
                </x-primary-button>
            </div>
        </form>
        @endauth

        @if ($suggestion->attachment_path)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700"><strong>Ek Dosya:</strong></p>
                <a href="{{ Storage::url($suggestion->attachment_path) }}" target="_blank"
                   class="text-indigo-600 hover:text-indigo-900 underline">
                    {{-- Sadece dosya adını göstermek için: --}}
                    {{ basename($suggestion->attachment_path) }}
                </a>
                {{-- Eğer resimse direkt göstermek istersen (basit kontrol): --}}
                @php
                    $extension = pathinfo(Storage::url($suggestion->attachment_path), PATHINFO_EXTENSION);
                @endphp
                @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']))
                    <div class="mt-2">
                        <img src="{{ Storage::url($suggestion->attachment_path) }}" alt="Ekli Resim" class="max-w-xs rounded">
                    </div>
                @endif
            </div>
        @endif

        {{-- Mevcut Yorumlar --}}
        <div class="space-y-4">
            @forelse ($suggestion->comments as $comment)
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-1">
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            {{ $comment->user->name }}
                            @if($comment->user->role == 'admin')
                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 rounded-full">Yönetici</span>
                            @endif
                            @if($suggestion->user_id == $comment->user_id && $comment->user->role != 'admin')
                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 rounded-full">Öneri Sahibi</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400" title="{{ $comment->created_at->isoFormat('LLLL') }}">
                            {{ $comment->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ $comment->body }}</p>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400">Henüz hiç yorum yapılmamış.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
