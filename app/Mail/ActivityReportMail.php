<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivityReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $activity;
    public $fotos;

    public function __construct(array $activity, array $fotos)
    {
        $this->activity = $activity;
        $this->fotos = $fotos;
    }

    public function build()
    {
        $email = $this->subject('Laporan Aktivitas: ' . $this->activity['SUBJECT'])
                      ->view('emails.activity')
                      ->with([
                          'activity' => $this->activity,
                          'fotos' => $this->fotos
                      ]);

        // Lampirkan semua foto dari folder storage/uploads
        foreach ($this->fotos as $foto) {
            if (!empty($foto['NM_ACTIVITY_FOTO'])) {
                $path = storage_path('app/public/uploads/' . $foto['NM_ACTIVITY_FOTO']);
                if (file_exists($path)) {
                    $email->attach($path, [
                        'as' => $foto['NM_ACTIVITY_FOTO'],
                        'mime' => mime_content_type($path)
                    ]);
                }
            }
        }

        return $email;
    }
}
