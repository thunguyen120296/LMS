<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Message\UserRegisteredMessage;
use App\Message\UserVerifiedMessage;
use Lms\Shared\Logger\BaseLogService;
use Lms\Shared\Logger\LogContext;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class IamEventPublisher
{
    private readonly BaseLogService $logger;

    public function __construct(
        private readonly MessageBusInterface $bus,
        BaseLogService $logger,
    ) {
        $this->logger = $logger->for('iam_events');
    }

    public function publishUserRegistered(User $user): void
    {
        $message = new UserRegisteredMessage(
            userId: $user->getId(),
            email: $user->getEmail(),
            fullName: $user->getFullName() !== '' ? $user->getFullName() : null,
            occurredAt: (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        );

        $this->bus->dispatch($message, [new AmqpStamp('user.registered')]);

        $this->logger->info('Published user.registered event', new LogContext(
            action: 'iam_events.user_registered',
            userId: $user->getId(),
            extra: ['email' => $user->getEmail()],
        ));
    }

    public function publishUserVerified(User $user): void
    {
        if ($user->getSsoSubject() === null) {
            return;
        }

        $message = new UserVerifiedMessage(
            userId: $user->getId(),
            email: $user->getEmail(),
            ssoSubject: $user->getSsoSubject(),
            occurredAt: (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        );

        $this->bus->dispatch($message, [new AmqpStamp('user.verified')]);

        $this->logger->info('Published user.verified event', new LogContext(
            action: 'iam_events.user_verified',
            userId: $user->getId(),
            extra: ['email' => $user->getEmail(), 'ssoSubject' => $user->getSsoSubject()],
        ));
    }
}
