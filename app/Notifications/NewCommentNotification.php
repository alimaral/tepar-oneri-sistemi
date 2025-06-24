<?php

namespace App\Notifications;

use App\Models\Comment; // Bu satırı ekle
use App\Models\User;    // Bu satırı ekle
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str; // Str::limit için


class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Comment $comment;
    public User $recipient; // Bildirimi alan kişi
    public User $commenter; // Yorumu yapan kişi

    /**
     * Create a new notification instance.
     *
     * @param Comment $comment
     * @param User $recipient Bildirimi alacak kişi
     * @param User $commenter Yorumu yapan kişi
     */
    public function __construct(Comment $comment, User $recipient, User $commenter)
    {
        $this->comment = $comment;
        $this->recipient = $recipient;
        $this->commenter = $commenter;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Bu kontrol CommentController'da daha etkin yönetiliyor.
        // Eğer bildirimi alacak kişi aynı zamanda yorumu yapansa (ve öneri sahibi değilse),
        // ve bu durum controller'da engellenmediyse, burada sadece 'database' dönebilir.
        // Ancak şimdilik controller'a güveniyoruz.
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable (Bildirimi alan kişi, yani $this->recipient)
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) // $notifiable bildirimi alan kişi (öneri sahibi veya yönetici)
    {
        $comment = $this->comment;
        $suggestion = $comment->suggestion;
        $commenter = $this->commenter; // Yorumu yapan kişi (constructor'dan geliyor)
        $recipient = $notifiable; // Bildirimi alan kişi

        $commenterRoleInfo = "";
        if ($commenter->id === $suggestion->user_id) {
            $commenterRoleInfo = " (Öneri Sahibi)";
        } elseif ($commenter->role === 'admin') {
            $commenterRoleInfo = " (Yönetici)";
        }

        $subject = "Yeni Yorum: \"" . Str::limit($suggestion->title, 30) . "\" Önerisine - Tepar Öneri Sistemi";
        $greeting = "Merhaba " . $recipient->name . ",";
        $introLines = [
            $commenter->name . $commenterRoleInfo . ", \"" . $suggestion->title . "\" başlıklı öneriye yeni bir yorum yaptı.",
        ];
        $commentDetails = [
            "Yorum İçeriği" => Str::limit($comment->body, 250),
            "Yorum Tarihi" => $comment->created_at->format('d/m/Y H:i'),
        ];
        $actionText = "Yorumu ve Öneriyi Görüntüle";

        // Bildirimi alan kişiye göre doğru linki oluştur
        $actionUrl = '';
        if ($recipient->role === 'admin') {
            $actionUrl = route('admin.suggestions.show', $suggestion->id);
        } else { // Öneri sahibi veya diğer kullanıcılar
            $actionUrl = route('suggestions.show', $suggestion->id);
        }
        $actionUrl .= '#comment-' . $comment->id; // Yorumun olduğu yere git

        $outroLines = [
            "Lütfen yorumu inceleyiniz.",
        ];


        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting);

        foreach ($introLines as $line) {
            $mailMessage->line($line);
        }

        $mailMessage->line("\n--- Yorum Detayları ---");
        foreach ($commentDetails as $key => $value) {
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $comment = $this->comment;
        $suggestion = $comment->suggestion;
        $commenter = $this->commenter;
        $recipient = $notifiable;

        $commenterRoleInfo = "";
        if ($commenter->id === $suggestion->user_id) {
            $commenterRoleInfo = " (Öneri Sahibi)";
        } elseif ($commenter->role === 'admin') {
            $commenterRoleInfo = " (Yönetici)";
        }

        $url = '';
        if ($recipient->role === 'admin') {
            $url = route('admin.suggestions.show', $suggestion->id);
        } else {
            $url = route('suggestions.show', $suggestion->id);
        }
        $url .= '#comment-' . $comment->id;

        return [
            'comment_id' => $comment->id,
            'suggestion_id' => $suggestion->id,
            'suggestion_title' => $suggestion->title,
            'commenter_name' => $commenter->name . $commenterRoleInfo,
            'message' => $commenter->name . $commenterRoleInfo . ', "' . Str::limit($suggestion->title, 30) . '" başlıklı öneriye yorum yaptı.',
            'link' => $url
        ];
    }
}
