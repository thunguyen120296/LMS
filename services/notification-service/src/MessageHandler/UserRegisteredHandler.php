<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Notification;
use App\Enum\NotificationChannel;
use App\Message\UserRegisteredMessage;
use App\Repository\NotificationRepository;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserRegisteredHandler
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('user_registered_handler');
    }

    public function __invoke(UserRegisteredMessage $message): void
    {
        $displayName = $message->fullName ?? $message->email;

        $notification = new Notification(
            userId: $message->userId,
            templateCode: 'user.welcome',
            channel: NotificationChannel::InApp,
            title: 'Chào mừng đến với LMS',
            body: sprintf('Xin chào %s! Tài khoản của bạn đã được tạo thành công.', $displayName),
            payload: [
                'email' => $message->email,
                'occurredAt' => $message->occurredAt,
            ],
        );

        $notification->markSent();
        $this->notificationRepository->save($notification, true);

        $this->logger->info('Welcome notification created', new LogContext(
            action: 'notification.user_registered',
            userId: $message->userId,
            extra: ['email' => $message->email],
        ));
    }
}
