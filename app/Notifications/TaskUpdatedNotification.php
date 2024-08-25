<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskUpdatedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $task;
    public $assignee;
    /**
     * Create a new notification instance.
     */
    public function __construct($task)
    {
//        \Log::info('Task passed to TaskUpdatedNotification:', ['task' => $task]);
        $this->task = $task['data'];
        $this->assignee = $task['assign'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'task status updated!'.$this->assignee,
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'assigned_to' => $this->task->assigned_to,
            'assigned_by' => $this->task->assigned_by,
            'due_date' => $this->task->due_date,
            'updated_at' => $this->task->updated_at,
        ];
    }

    public function toBroadcast($notifiable)
    {
        $user = User::find($this->assignee);
        return new BroadcastMessage([
            'message' => 'task status updated!'.$this->assignee,
            'user' => $user->notifications,
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'assigned_to' => $this->task->assigned_to,
            'assigned_by' => $this->task->assigned_by,
            'due_date' => $this->task->due_date,
            'updated_at' => $this->task->updated_at,
        ]);
    }
    public function databaseType(object $notifiable): string
    {
        return 'Task_status_update'.$this->assignee;
    }
    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->assignee);
    }
    public function broadcastType(): string
    {
        return 'task.assigned';
    }
}
