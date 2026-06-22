<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Notification;
use App\Enum\NotificationChannel;
use App\Message\UserVerifiedMessage;
use App\Repository\NotificationRepository;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserVerifiedHandler
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('user_verified_handler');
    }

    public function __invoke(UserVerifiedMessage $message): void
    {
        $notification = new Notification(
            userId: $message->userId,
            templateCode: 'user.verified_congrats',
            channel: NotificationChannel::InApp,
            title: 'Email đã được xác minh',
            body: sprintf(
                'Chúc mừng! Email %s của bạn đã được xác minh thành công. Bạn có thể đăng nhập và bắt đầu học ngay.',
                $message->email,
            ),
            payload: [
                'email' => $message->email,
                'ssoSubject' => $message->ssoSubject,
                'occurredAt' => $message->occurredAt,
            ],
        );

        $notification->markSent();
        $this->notificationRepository->save($notification, true);

        $this->logger->info('Email verified congratulation notification created', new LogContext(
            action: 'notification.user_verified',
            userId: $message->userId,
            extra: ['email' => $message->email],
        ));
    }
}
