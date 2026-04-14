<?php
namespace App\Service;

class NotificationService
{
    public function sendEmail(string $to, string $message): bool
    {
        // En production, ici on enverrait un vrai email
        return true;
    }
}
