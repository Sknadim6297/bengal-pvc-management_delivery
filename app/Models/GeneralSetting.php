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
        return $this->whatsapp_contact_url === null
            ? config('services.whatsapp_contact_url')
            : (filled($this->whatsapp_contact_url) ? $this->whatsapp_contact_url : null);
    }
}