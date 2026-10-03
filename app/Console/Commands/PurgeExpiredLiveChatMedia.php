<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredLiveChatMedia extends Command
{
    protected $signature = 'live-chat:purge-media {--days=}';
    protected $description = 'Verwijder verlopen grote live-chatmedia volgens de bewaartermijn.';

    public function handle(): int
    {
        $days = max(1, (int) ($this->option('days') ?: config('live_chat.media_retention_days', 30)));
        $cutoff = now()->subDays($days);
        $query = DB::table('live_chat_messages')
            ->whereNotNull('attachment_path')
            ->where('created_at', '<', $cutoff)
            ->where(function ($q): void {
                $q->where('type', 'video')->orWhere('attachment_size', '>=', 100 * 1024 * 1024);
            })
            ->orderBy('id');

        $count = 0;
        $query->chunkById(100, function ($messages) use (&$count): void {
            foreach ($messages as $message) {
                if ($message->attachment_path) {
                    Storage::disk('public')->delete($message->attachment_path);
                }
                DB::table('live_chat_messages')->where('id', $message->id)->update([
                    'attachment_path' => null,
                    'attachment_name' => ($message->attachment_name ?: 'media').' (verlopen)',
                    'attachment_size' => null,
                ]);
                $count++;
            }
        });

        $this->info("{$count} verlopen media-items verwijderd.");
        return self::SUCCESS;
    }
}
