<?php

namespace App\Services;

use App\Models\User;

class UserDashboardStatistics
{
    public static function forUser(User $user): object
    {
        return (object) [
            'user_id' => $user->getKey(),
            'processing_cards' => 0,
            'delivered_cards' => 0,
        ];
    }
}