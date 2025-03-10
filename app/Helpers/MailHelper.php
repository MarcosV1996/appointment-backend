<?php 
namespace App\Helpers;

use Illuminate\Support\Facades\Config;

class MailHelper
{
    public static function configureMailProvider($email)
    {
        if (str_contains($email, '@outlook.com') || str_contains($email, '@hotmail.com')) {
            Config::set('mail.mailer', 'smtp');
            Config::set('mail.host', env('OUTLOOK_MAIL_HOST'));
            Config::set('mail.port', env('OUTLOOK_MAIL_PORT'));
            Config::set('mail.username', env('OUTLOOK_MAIL_USERNAME'));
            Config::set('mail.password', env('OUTLOOK_MAIL_PASSWORD'));
            Config::set('mail.encryption', env('OUTLOOK_MAIL_ENCRYPTION'));
            Config::set('mail.from.address', env('OUTLOOK_MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', env('OUTLOOK_MAIL_FROM_NAME'));
        } elseif (str_contains($email, '@yahoo.com')) {
            Config::set('mail.mailer', 'smtp');
            Config::set('mail.host', env('YAHOO_MAIL_HOST'));
            Config::set('mail.port', env('YAHOO_MAIL_PORT'));
            Config::set('mail.username', env('YAHOO_MAIL_USERNAME'));
            Config::set('mail.password', env('YAHOO_MAIL_PASSWORD'));
            Config::set('mail.encryption', env('YAHOO_MAIL_ENCRYPTION'));
            Config::set('mail.from.address', env('YAHOO_MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', env('YAHOO_MAIL_FROM_NAME'));
        } elseif (str_contains($email, '@seudominio.com')) {
            Config::set('mail.mailer', 'smtp');
            Config::set('mail.host', env('EMPRESA_MAIL_HOST'));
            Config::set('mail.port', env('EMPRESA_MAIL_PORT'));
            Config::set('mail.username', env('EMPRESA_MAIL_USERNAME'));
            Config::set('mail.password', env('EMPRESA_MAIL_PASSWORD'));
            Config::set('mail.encryption', env('EMPRESA_MAIL_ENCRYPTION'));
            Config::set('mail.from.address', env('EMPRESA_MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', env('EMPRESA_MAIL_FROM_NAME'));
        } else {
            // Gmail (ou padrão)
            Config::set('mail.mailer', 'smtp');
            Config::set('mail.host', env('MAIL_HOST'));
            Config::set('mail.port', env('MAIL_PORT'));
            Config::set('mail.username', env('MAIL_USERNAME'));
            Config::set('mail.password', env('MAIL_PASSWORD'));
            Config::set('mail.encryption', env('MAIL_ENCRYPTION'));
            Config::set('mail.from.address', env('MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', env('MAIL_FROM_NAME'));
        }
    }
}
