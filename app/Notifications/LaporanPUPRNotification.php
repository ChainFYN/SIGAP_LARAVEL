<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanPUPRNotification extends Notification
{
    use Queueable;

    public $tipe;
    public $judul;
    public $pesan;
    public $idLaporan;
    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $tipe, string $judul, string $pesan, int $idLaporan, string $url)
    {
        $this->tipe = $tipe;       // 'disposisi_baru', 'bast_ditolak', 'bast_disetujui'
        $this->judul = $judul;
        $this->pesan = $pesan;
        $this->idLaporan = $idLaporan;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tipe' => $this->tipe,
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'id_laporan' => $this->idLaporan,
            'url' => $this->url,
            'waktu' => now()->toISOString(),
        ];
    }
}
