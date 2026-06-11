<?php

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTPayloadSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onJWTCreated',
        ];
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
{
    $payload = $event->getData();
    /** @var \App\Entity\User $user */
    $user = $event->getUser();

    $permissions = [];
    
    // Duyệt qua các UserRole
    foreach ($user->getUserRoles() as $userRole) {
        $role = $userRole->getRole();
        
        // Duyệt qua các RolePermission (đã kiểm tra trong Role.php, bạn dùng getRolePermissions())
        foreach ($role->getRolePermissions() as $rolePermission) {
            $permission = $rolePermission->getPermission();
            // Lấy key bằng phương thức bạn đã định nghĩa trong Permission.php
            $permissions[] = $permission->getKey(); 
        }
    }
    
    $payload['permissions'] = array_unique($permissions);
    $event->setData($payload);
}
}
