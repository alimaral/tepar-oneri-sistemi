<?php

namespace App\Notifications;

use App\Models\Suggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuggestionStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Suggestion $suggestion;

    public function __construct(Suggestion $suggestion)
    {
        $this->suggestion = $suggestion;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable) // $notifiable burada öneriyi veren kullanıcı
    {
        $suggestion = $this->suggestion;
        $user = $notifiable; // Bildirimi alan kullanıcı

        $statusTR = [
            'new' => 'Yeni',
            'in_progress' => 'İnceleniyor',
            'accepted' => 'Kabul Edildi',
            'rejected' => 'Reddedildi',
        ];
        $currentStatusTR = $statusTR[$suggestion->status] ?? ucfirst(str_replace('_', ' ', $suggestion->status));

        $subject = "Önerinizin Durumu Güncellendi: " . $suggestion->title . " - Tepar Öneri Sistemi";
        $greeting = "Merhaba " . $user->name . ",";
        $introLines = [
            "\"" . $suggestion->title . "\" başlıklı önerinizin durumu güncellenmiştir.",
        ];
        $updateDetails = [
            "Yeni Durum" => $currentStatusTR,
            "Güncellenme Tarihi" => $suggestion->updated_at->format('d/m/Y H:i'),
        ];
        // Yönetici bir yorum/not eklediyse onu da buraya dahil edebilirsin
        // if ($suggestion->admin_comment) {
        //    $updateDetails["Yönetici Notu"] = $suggestion->admin_comment;
        // }
        $actionText = "Önerinizi Görüntüleyin";
        // ÖNERİ SAHİBİNİ KENDİ ÖNERİ DETAY SAYFASINA YÖNLENDİR
        $actionUrl = route('suggestions.show', $suggestion->id);
        $outroLines = [
            "Tepar Öneri Sistemi'ni kullandığınız için teşekkür ederiz.",
        ];

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting);

        foreach ($introLines as $line) {
            $mailMessage->line($line);
        }

        $mailMessage->line("\n--- Güncelleme Detayları ---");
        foreach ($updateDetails as $key => $value) {
            $mailMessage->line("**{$key}:** {$value}");
        }
        $mailMessage->line("--- --- --- --- --- ---");

        if ($actionText && $actionUrl) {
            $mailMessage->action($actionText, $actionUrl);
        }

        foreach ($outroLines as $line) {
            $mailMessage->line($line);
        }

        return $mailMessage;
    }
    public function toArray(object $notifiable): array
    {
        $statuses = ['new' => 'Yeni', 'in_progress' => 'İnceleniyor', 'accepted' => 'Kabul Edildi', 'rejected' => 'Reddedildi'];
        $statusText = $statuses[$this->suggestion->status] ?? ucfirst(str_replace('_', ' ', $this->suggestion->status));

        return [
            'suggestion_id' => $this->suggestion->id,
            'suggestion_title' => $this->suggestion->title,
            'status' => $this->suggestion->status, // Ham durumu sakla
            'status_text' => $statusText,      // Kullanıcı arayüzü için metin
            'message' => '"' . $this->suggestion->title . '" başlıklı önerinizin durumu "' . $statusText . '" olarak güncellendi.',
            'link' => route('suggestions.show', $this->suggestion->id),
            'icon' => 'fa-check-circle', // veya duruma göre fa-times-circle vb.
        ];
    }
}
