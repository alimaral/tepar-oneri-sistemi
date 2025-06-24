<?php

namespace App\Exports;

use App\Models\Suggestion;
use Maatwebsite\Excel\Concerns\FromCollection; // Veri kaynağı Collection ise
// use Maatwebsite\Excel\Concerns\FromQuery;    // Veri kaynağı Eloquent Query ise
// use Maatwebsite\Excel\Concerns\FromArray;    // Veri kaynağı basit bir dizi ise
use Maatwebsite\Excel\Concerns\WithHeadings;   // Başlık satırı eklemek için
use Maatwebsite\Excel\Concerns\WithMapping;    // Her satırı formatlamak için
use Illuminate\Support\Facades\Storage;        // Dosya linkleri için

class SuggestionsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Suggestion::with(['user', 'department'])->get(); // Tüm önerileri ilişkili verilerle çek
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Excel dosyasındaki sütun başlıkları
        return [
            'ID',
            'Başlık',
            'Açıklama',
            'Kullanıcı Adı',
            'Kullanıcı Email',
            'Bölüm',
            'Durum',
            'Ek Dosya Linki',
            'Oluşturulma Tarihi',
            'Güncellenme Tarihi',
        ];
    }

    /**
     * @param mixed $suggestion // collection() metodundan gelen her bir Suggestion modeli
     * @return array
     */
    public function map($suggestion): array
    {
        // Durumları daha okunabilir hale getirmek için
        $statusTranslations = [
            'new' => 'Yeni',
            'in_progress' => 'İnceleniyor',
            'accepted' => 'Kabul Edildi',
            'rejected' => 'Reddedildi',
        ];

        $attachmentLink = $suggestion->attachment_path ? Storage::url($suggestion->attachment_path) : 'Yok';

        // Excel'e yazılacak her bir satırın verisi
        return [
            $suggestion->id,
            $suggestion->title,
            strip_tags($suggestion->body),
            $suggestion->user->name ?? 'Bilinmeyen Kullanıcı',
            $suggestion->user->email ?? 'N/A',
            $suggestion->department->name ?? 'Bilinmeyen Bölüm',
            $statusTranslations[$suggestion->status] ?? ucfirst(str_replace('_', ' ', $suggestion->status)),
            $attachmentLink,
            $suggestion->created_at->format('d/m/Y H:i:s'),
            $suggestion->updated_at->format('d/m/Y H:i:s'),
        ];
    }
}
