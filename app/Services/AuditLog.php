<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLog
{
    public static function log(string $action, string $description, array $properties = [], $subject = null): void
    {
        $request = app(Request::class);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject ? ($subject->id ?? null) : null,
            'ip_address'   => $request->ip() ?? '0.0.0.0',
            'user_agent'   => $request->userAgent(),
            'properties'   => $properties,
        ]);
    }

    public static function logAdmin(string $action, string $description, array $properties = []): void
    {
        $request = app(Request::class);

        ActivityLog::create([
            'user_id'      => Auth::id(),
            'admin_id'     => Auth::id(),
            'action'       => $action,
            'description'   => $description,
            'ip_address'   => $request->ip() ?? '0.0.0.0',
            'user_agent'   => $request->userAgent(),
            'properties'   => $properties,
        ]);
    }

    public static function login($user, bool $success): void
    {
        self::log('login', $success ? "User logged in: {$user->email}" : "Failed login attempt: {$user->email}", [
            'success' => $success,
        ]);
    }

    public static function logout($user): void
    {
        self::log('logout', "User logged out: {$user->email}");
    }

    public static function passwordChanged($user): void
    {
        self::log('password_changed', "Password changed for: {$user->email}");
    }

    public static function settingsChanged(string $key, $oldValue, $newValue): void
    {
        self::logAdmin('settings_changed', "Setting '{$key}' changed from '{$oldValue}' to '{$newValue}'", [
            'key' => $key,
            'old' => $oldValue,
            'new' => $newValue,
        ]);
    }
}
