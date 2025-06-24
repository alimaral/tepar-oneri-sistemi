<?php

namespace App\Notifications;

use App\Models\Suggestion;
use App\Models\User; // Bildirim alacak yöneticiye ismiyle hitap etmek için
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // E-postaları kuyruğa almak için
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewSuggestionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Suggestion $suggestion;

    public function __construct(Suggestion $suggestion)
    {
        $this->suggestion = $suggestion;
    }



    public function via($notifiable)
    {
        return ['mail', 'database']; // E-posta gönderimi için 'mail' olmalı
    }

    public function toMail($notifiable) // $notifiable burada yönetici
    {
        $suggestion = $this->suggestion; // Daha okunaklı olması için

        // E-posta için dinamik veriler
        $subject = "Yeni Öneri: " . $suggestion->title . " - Tepar Öneri Sistemi";
        $greeting = "Merhaba Yönetici,";
        $introLines = [ // Giriş satırları
            "Tepar Öneri Sistemi'ne yeni bir öneri gönderildi.",
        ];
        $suggestionDetails = [ // Öneri detayları (anahtar-değer olarak)
            "Başlık" => $suggestion->title,
            "Gönderen Kullanıcı" => $suggestion->user->name . " (" . $suggestion->user->email . ")",
            "Bölüm" => $suggestion->department->name,
            "Öneri İçeriği" => Str::limit($suggestion->body, 200), // İlk 200 karakter
            "Gönderilme Tarihi" => $suggestion->created_at->format('d/m/Y H:i'),
        ];
        $actionText = "Öneriyi Detaylı İncele";
        $actionUrl = route('admin.suggestions.show', $suggestion->id); // Yönetici paneline yönlendir
        $outroLines = [ // Kapanış satırları
            "Lütfen öneriyi en kısa sürede değerlendirin.",
            "İyi çalışmalar.",
        ];

        // MailMessage objesini oluştur
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting);

        // Giriş satırlarını ekle
        foreach ($introLines as $line) {
            $mailMessage->line($line);
        }

        // Öneri detaylarını ekle (istersen tablo veya liste olarak formatlayabilirsin)
        $mailMessage->line("\n--- Öneri Bilgileri ---"); // Ayırıcı
        foreach ($suggestionDetails as $key => $value) {
            $mailMessage->line("**{$key}:** {$value}"); // Markdown ile bold yapabilirsin
        }
        $mailMessage->line("--- --- --- --- --- ---"); // Ayırıcı

        // Eylem düğmesini ekle
        if ($actionText && $actionUrl) {
            $mailMessage->action($actionText, $actionUrl);
        }

        // Kapanış satırlarını ekle
        foreach ($outroLines as $line) {
            $mailMessage->line($line);
        }

        return $mailMessage;
    }
    public function toArray(object $notifiable): array // Veritabanı bildirimi için
    {
        // Bu veriler 'notifications' tablosunun 'data' sütununda JSON olarak saklanacak.
        return [
            'suggestion_id' => $this->suggestion->id,
            'suggestion_title' => $this->suggestion->title,
            'sender_id' => $this->suggestion->user->id,
            'sender_name' => $this->suggestion->user->name,
            'message' => $this->suggestion->user->name . ' tarafından "' . $this->suggestion->title . '" başlıklı yeni bir öneri gönderildi.',
            'link' => route('admin.suggestions.show', $this->suggestion->id),
            'icon' => 'fa-lightbulb', // Frontend'de isterseniz kullanabileceğiniz bir ikon class'ı
        ];
    }
}
