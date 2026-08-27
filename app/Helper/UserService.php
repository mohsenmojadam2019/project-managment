<?php

namespace App\Helper;

use App\Models\ClientContact;

class UserService
{
    public static function getUserId()
    {
        $currentUser = user();

        if (!$currentUser) {
            return null;
        }

        if ((int) $currentUser->is_client_contact === 1) {
            $clientContact = ClientContact::where('client_id', $currentUser->id)->first();

            // A stale/incomplete contact mapping must not turn permission checks
            // into a null-property 500. Fall back to the authenticated identity.
            return $clientContact?->user_id ?? $currentUser->id;
        }

        return $currentUser->id;
    }
}
