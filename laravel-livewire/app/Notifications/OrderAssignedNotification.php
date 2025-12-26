<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private Assignment $assignment)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->assignment->order;
        $truck = $this->assignment->truck;
        $driver = $this->assignment->driver;

        $recipientName = $notifiable->name
            ?? data_get($notifiable, 'routes.mail.0')
            ?? data_get($notifiable, 'email')
            ?? __('Cliente');

        return (new MailMessage())
            ->subject(__('Nueva orden asignada: :reference', ['reference' => $order->reference]))
            ->greeting(__('Hola :name', ['name' => $recipientName]))
            ->line(__('Se te ha asignado la Orden :reference.', ['reference' => $order->reference]))
            ->line(__('Vehículo: :truck', ['truck' => $truck?->plate_number ?? __('No disponible')]))
            ->line(__('Chofer: :driver', ['driver' => $driver?->full_name ?? __('No disponible')]))
            ->line(__('Fecha de recogida: :date', ['date' => optional($order->pickup_date)->format('d/m/Y H:i')]))
            ->line(__('Ventana de entrega: :start - :end', [
                'start' => optional($order->delivery_window_start)->format('d/m/Y H:i') ?? __('No definida'),
                'end' => optional($order->delivery_window_end)->format('d/m/Y H:i') ?? __('No definida'),
            ]))
            ->action(__('Ver Orden'), route('orders.edit', $order))
            ->line(__('Gracias por usar nuestro sistema logístico.'));
    }
}
