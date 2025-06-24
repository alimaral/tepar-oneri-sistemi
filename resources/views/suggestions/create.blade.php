<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Yeni Öneri Ver') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('suggestions.store') }} " enctype="multipart/form-data">
                        @csrf

                        <!-- Department -->
                        <div class="mt-4">
                            <x-input-label for="department_id" :value="__('Bölüm')"/>
                            <select id="department_id" name="department_id"
                                    class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Bölüm Seçiniz</option>
                                @foreach ($departments as $department)
                                    <option
                                        value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('department_id')" class="mt-2"/>
                        </div>

                        <!-- Title -->
                        <div class="mt-4">
                            <x-input-label for="title" :value="__('Öneri Başlığı')"/>
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                          :value="old('title')" required autofocus/>
                            <x-input-error :messages="$errors->get('title')" class="mt-2"/>

                        </div>

                        <!-- Body -->
                        <div class="mt-4">
                            <x-input-label for="body" :value="__('Öneri Detayı')"/>
                            <textarea id="body" name="body" rows="5"
                                      class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                      required>{{ old('body') }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2"/>
                        </div>
                        <!-- Attachment -->
                        <div class="mt-4">
                            <x-input-label for="attachment"
                                           :value="__('Dosya Eki (Opsiyonel - Resim, PDF, Word vb.)')"/>
                            <input id="attachment" class="block mt-1 w-full text-sm text-slate-500
            file:mr-4 file:py-2 file:px-4
            file:rounded-full file:border-0
            file:text-sm file:font-semibold
            file:bg-violet-50 file:text-violet-700
            hover:file:bg-violet-100"
                                   type="file" name="attachment"/>
                            <x-input-error :messages="$errors->get('attachment')" class="mt-2"/>
                            <p class="mt-1 text-xs text-gray-500">İzin verilen dosya türleri: jpg, jpeg, png, pdf, doc,
                                docx. Maksimum boyut: 2MB.</p>
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Gönder') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
