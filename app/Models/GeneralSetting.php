<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class GeneralSetting extends Model
{
    protected $table = 'general_settings';

    public function logoUrl(): string
    {
        return $this->logo_path
            ? asset('storage/'.$this->logo_path)
            : asset('assets/img/pvc_logo.png');
    }

    public function faviconUrl(): string
    {
        return $this->favicon_path
            ? asset('storage/'.$this->favicon_path)
            : asset('favicon.ico');
    }

    public function whatsappContactUrl(): ?string
    {
        $contactUrl = $this->whatsapp_contact_url === null
            ? config('services.whatsapp_contact_url')
            : (filled($this->whatsapp_contact_url) ? $this->whatsapp_contact_url : null);

        $parts = is_string($contactUrl) ? parse_url($contactUrl) : false;
        if ($parts === false
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || ! in_array(strtolower($parts['host'] ?? ''), ['wa.me', 'api.whatsapp.com', 'chat.whatsapp.com', 'whatsapp.com', 'www.whatsapp.com'], true)) {
            return null;
        }

        return $contactUrl;
    }

    public function whatsappSupportUrl(string $message): ?string
    {
        $contactUrl = $this->whatsappContactUrl();
        $parts = is_string($contactUrl) ? parse_url($contactUrl) : false;

        if ($parts === false) {
            return null;
        }

        $phone = null;
        $host = strtolower($parts['host'] ?? '');

        if ($host === 'wa.me') {
            $candidate = trim($parts['path'] ?? '', '/');
            $phone = preg_match('/^\+?[0-9]{7,15}$/D', $candidate) ? $candidate : null;
        } elseif ($host === 'api.whatsapp.com' && ($parts['path'] ?? '') === '/send') {
            parse_str($parts['query'] ?? '', $query);
            $phone = is_string($query['phone'] ?? null) ? $query['phone'] : null;
        }

        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (! is_string($digits) || ! preg_match('/^[0-9]{7,15}$/D', $digits)) {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    public function helplineTelUrl(): ?string
    {
        $number = trim((string) $this->helpline_number);
        $digits = preg_replace('/\D+/', '', $number);

        if (! is_string($digits) || ! preg_match('/^[0-9]{7,15}$/D', $digits)) {
            return null;
        }

        return 'tel:'.(str_starts_with($number, '+') ? '+' : '').$digits;
    }
}