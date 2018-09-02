<?php

namespace App\Jobs;

use App\Chapter;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Yangqi\Htmldom\Htmldom;

class CreateChapterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chapter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Chapter $chapter)
    {
        $this->chapter = $chapter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->chapter->content != null) {
            Log::notice('ID:' . $this->chapter->id . ' 章节内容不为空！');
            return;
        }

        $url = $this->chapter->host . $this->chapter->url;

        $html = new Htmldom($url);

        $as = $html->find('div[class=bottem1]', 0)->find('a');

        $content = iconv('gbk', 'utf-8', $html->find('div[id=content]', 0)->innertext);

        $this->chapter->prev_chapter_url = $as[1];
        $this->chapter->next_chapter_url = $as[3];
        $this->chapter->content = $content;

        $this->chapter->save();
    }
}
