<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private Order $order, private string $previousStatus)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $recipientName = $notifiable->name
            ?? data_get($notifiable, 'routes.mail.0')
            ?? data_get($notifiable, 'email')
            ?? __('Cliente');

        return (new MailMessage())
            ->subject(__('Actualización de estado para el pedido :reference', ['reference' => $this->order->reference]))
            ->greeting(__('Hola :name', ['name' => $recipientName]))
            ->line(__('El pedido :reference cambió de :from a :to.', [
                'reference' => $this->order->reference,
                'from' => __($this->previousStatus),
                'to' => __($this->order->status),
            ]))
            ->line(__('Fecha estimada de entrega: :date', [
                'date' => optional($this->order->delivery_date)->format('d/m/Y H:i') ?? __('No definida'),
            ]))
            ->action(__('Ver pedido'), route('orders.edit', $this->order))
            ->line(__('Gracias por confiar en nosotros.'));
    }
}
