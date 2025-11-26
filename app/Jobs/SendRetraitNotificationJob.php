<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;

class SendRetraitNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $telephone;
    protected $montant;
    protected $numero_distributeur;
    protected $frais;

    public function __construct($telephone, $montant, $numero_distributeur, $frais)
    {
        $this->telephone = $telephone;
        $this->montant = $montant;
        $this->numero_distributeur = $numero_distributeur;
        $this->frais = $frais;
    }

    public function handle()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $client = new Client($sid, $token);
        $client->messages->create($this->telephone, [
            'from' => $from,
            'body' => 'Retrait OMPay : ' . $this->montant . ' FCFA, Distributeur : ' . $this->numero_distributeur . ', Frais : ' . $this->frais . ' FCFA.'
        ]);
    }
}
