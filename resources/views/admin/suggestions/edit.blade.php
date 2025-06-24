<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Öneriyi Düzenle: <span class="italic">{{ $suggestion->title }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 md:p-8 bg-white dark:bg-gray-800">
                    <div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Öneri Bilgileri</h3>
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <p><strong>Kullanıcı:</strong> {{ $suggestion->user->name }} ({{ $suggestion->user->email }})</p>
                            <p><strong>Bölüm:</strong> {{ $suggestion->department->name }}</p>
                            <p><strong>Mevcut Durum:</strong> {{ $statuses[$suggestion->status] ?? ucfirst(str_replace('_', ' ', $suggestion->status)) }}</p>
                            <p class="mt-2"><strong>Öneri İçeriği:</strong></p>
                            <p class="pl-2 border-l-2 border-gray-300 dark:border-gray-600 italic">{{ Str::limit($suggestion->body, 200) }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.suggestions.update', $suggestion) }}">
                        @csrf
                        @method('PUT')

                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Yeni Durum')" class="text-lg"/>
                            <select id="status" name="status" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-base">
                                @foreach ($statuses as $key => $value)
                                    <option value="{{ $key }}" {{ $suggestion->status == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        {{-- İleride yönetici yorumu için alan eklenebilir --}}
                        {{-- <div class="mt-4">
                            <x-input-label for="admin_comment" :value="__('Yönetici Yorumu (İsteğe Bağlı)')" />
                            <textarea id="admin_comment" name="admin_comment" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">{{ old('admin_comment', $suggestion->admin_comment ?? '') }}</textarea>
                            <x-input-error :messages="$errors->get('admin_comment')" class="mt-2" />
                        </div> --}}

                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('admin.suggestions.show', $suggestion) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('İptal') }}
                            </a>
                            <x-primary-button>
                                {{ __('Durumu Güncelle') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
