<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Öneri Detayı: ') }} <span class="italic">{{ $suggestion->title }}</span>
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.suggestions.edit', $suggestion) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Durumu Düzenle
                </a>
                <a href="{{ route('admin.suggestions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 active:bg-gray-500 dark:active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    « Listeye Dön
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 md:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $suggestion->title }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm text-gray-600 dark:text-gray-400 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div><strong>Gönderen:</strong> {{ $suggestion->user->name }} ({{ $suggestion->user->email }})</div>
                        <div><strong>Bölüm:</strong> {{ $suggestion->department->name }}</div>
                        <div>
                            <strong>Durum:</strong>
                            @php
                                $statusClass = '';
                                $statuses = ['new' => 'Yeni', 'in_progress' => 'İnceleniyor', 'accepted' => 'Kabul Edildi', 'rejected' => 'Reddedildi'];
                                $statusTextUI = $statuses[$suggestion->status] ?? ucfirst(str_replace('_', ' ', $suggestion->status));
                                if ($suggestion->status == 'new') { $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; }
                                elseif ($suggestion->status == 'in_progress') { $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'; }
                                elseif ($suggestion->status == 'accepted') { $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'; }
                                elseif ($suggestion->status == 'rejected') { $statusClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; }
                            @endphp
                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                {{ $statusTextUI }}
                            </span>
                        </div>
                        <div><strong>Gönderilme:</strong> {{ $suggestion->created_at->isoFormat('LLLL') }} ({{ $suggestion->created_at->diffForHumans() }})</div>
                        <div><strong>Son Güncelleme:</strong> {{ $suggestion->updated_at->isoFormat('LLLL') }} ({{ $suggestion->updated_at->diffForHumans() }})</div>
                    </div>

                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Öneri İçeriği:</h4>
                        <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
                            {!! nl2br(e($suggestion->body)) !!}
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



                    {{-- Örnek: Eğer suggestions tablosunda admin_comment diye bir alan varsa --}}
                    {{-- @if($suggestion->admin_comment)
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Yönetici Yorumu:</h4>
                        <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
                            {!! nl2br(e($suggestion->admin_comment)) !!}
                        </div>
                    </div>
                    @endif --}}

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
