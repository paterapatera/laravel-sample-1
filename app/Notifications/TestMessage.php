<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
// キューに投入したい場合はこれをimplementsする
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestMessage extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    /**
     * 通知するチャンネルを設定
     * 
     * `database`で`notifications`データベースに登録できる。
     * その他に`broadcast`・`vonage`・`slack`も利用可能。
     *
     * @param  mixed  $notifiable notify()を実行したインスタンス
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * メール通知用の文章
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting(trans('Reset Password Notification'))
            ->subject(trans('Reset Password Notification'))
            ->line(trans('You are receiving this email because we received a password reset request for your account.'))
            ->line('CODE : ')
            ->line(trans('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]))
            ->line(trans('If you did not request a password reset, no further action is required.'));
        // return (new MailMessage)->markdown(
        //     'emails.name.html',
        //     ['invoice' => $this->invoice]
        // );
    }

    /**
     * DB通知用
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
