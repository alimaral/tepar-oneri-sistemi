<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Tüm Öneriler') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200 rounded-md shadow">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200 rounded-md shadow">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filtreleme Formu -->
            <div class="mb-6 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6">
                <form method="GET" action="{{ route('admin.suggestions.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <x-input-label for="status_filter" :value="__('Durum')" />
                        <select name="status_filter" id="status_filter" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            <option value="">Tüm Durumlar</option>
                            @foreach ($statuses as $key => $value)
                                <option value="{{ $key }}" {{ request('status_filter') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="department_filter" :value="__('Bölüm')" />
                        <select name="department_filter" id="department_filter" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                            <option value="">Tüm Bölümler</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_filter') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="search_term" :value="__('Ara (Başlık/İçerik)')" />
                        <x-text-input type="text" name="search_term" id="search_term" class="mt-1 block w-full" :value="request('search_term')" placeholder="Arama terimi..."/>
                    </div>
                    <div class="flex space-x-2">
                        <x-primary-button type="submit">
                            {{ __('Filtrele') }}
                        </x-primary-button>
                        <a href="{{ route('admin.suggestions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Temizle
                        </a>

                        <a href="{{ route('admin.suggestions.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 ...">
                            Excel'e Aktar
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-0 sm:p-2 md:p-4">
                    @if ($suggestions->isEmpty())
                        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                            Filtrelerinize uygun öneri bulunmamaktadır veya henüz hiç öneri gönderilmemiştir.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Başlık</th>
                                    <th scope="col" class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gönderen</th>
                                    <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bölüm</th>
                                    <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Durum</th>
                                    <th scope="col" class="hidden lg:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tarih</th>
                                    <th scope="col" class="relative px-4 sm:px-6 py-3">
                                        <span class="sr-only">İşlemler</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($suggestions as $suggestion)
                                    <tr>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <a href="{{ route('admin.suggestions.show', $suggestion) }}" class="hover:underline">
                                                {{ Str::limit($suggestion->title, 30) }}
                                            </a>
                                        </td>
                                        <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $suggestion->user->name }}
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $suggestion->department->name }}
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClass = '';
                                                $statusTextUI = $statuses[$suggestion->status] ?? ucfirst(str_replace('_', ' ', $suggestion->status));
                                                if ($suggestion->status == 'new') { $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; }
                                                elseif ($suggestion->status == 'in_progress') { $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'; }
                                                elseif ($suggestion->status == 'accepted') { $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'; }
                                                elseif ($suggestion->status == 'rejected') { $statusClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; }
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ $statusTextUI }}
                                            </span>
                                        </td>
                                        <td class="hidden lg:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $suggestion->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.suggestions.show', $suggestion) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 mr-2">Detay</a>
                                            <a href="{{ route('admin.suggestions.edit', $suggestion) }}" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">Düzenle</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4">
                            {{ $suggestions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
