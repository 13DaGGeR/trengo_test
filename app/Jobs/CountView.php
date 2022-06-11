<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Views\ViewCountManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CountView implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int The number of seconds after which the job's unique lock will be released */
    public int $uniqueFor = 3600;

    public function __construct(public int $articleId, private string $ip, private int $timestamp)
    {
        $this->onQueue('views');
    }

    public function handle(ViewCountManager $manager): void
    {
        $manager->register($this->articleId, $this->ip, $this->timestamp);
    }

    public function uniqueId(): string
    {
        return $this->articleId . '|' . $this->ip;
    }
}
