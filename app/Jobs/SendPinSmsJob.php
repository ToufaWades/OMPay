<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SendPinSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telephone;
    protected $code_pin;

    public function __construct($telephone, $code_pin)
    {
        $this->telephone = $telephone;
        $this->code_pin = $code_pin;
    }

    public function handle()
    {
        // Vérification du format international Orange Sénégal : +2217XXXXXXXX
        if (!preg_match('/^\+2217\d{7}$/', $this->telephone)) {
            Log::error('Numéro de téléphone invalide pour SMS OMPay : ' . $this->telephone);
            return;
        }
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $client = new Client($sid, $token);
        $client->messages->create($this->telephone, [
            'from' => $from,
            'body' => 'Votre code PIN OMPay : ' . $this->code_pin
        ]);
    }
}
