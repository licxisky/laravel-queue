<?php

namespace App\Jobs;

use App\Chapter;
use App\Novel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Yangqi\Htmldom\Htmldom;

class CreateNovelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = $this->url;
        $purl = parse_url($url);
        $host = $purl['scheme'] . '://' . $purl['host'] . (isset($purl['port']) ? ':' . $purl['port'] : '');

        $html = new Htmldom($url);

        $metas = array_reduce($html->find('meta'), function ($carry, $item) {
            $carry[$item->property] = $item->content;
            return $carry;
        }, []);

        $validator = \Validator::make($metas, [
            'og:type' => 'required',
            'og:title' => 'required',
            'og:description' => 'required',
            'og:image' => 'required',
            'og:novel:category' => 'required',
            'og:novel:author' => 'required',
            'og:book_name' => 'required',
            'og:url' => 'required',
            'og:novel:status' => 'required',
            'og:novel:update_time' => 'required',
        ]);

        if($validator->failed()) {
            Log::error('链接：' . $url . '无法获取小说信息');
            return;
        }

        $data = [
            'host' => $host,
            'type' => $metas['og:type'],
            'title' => $metas['og:title'],
            'description' => $metas['og:description'],
            'image' => $metas['og:image'],
            'category' => $metas['og:novel:category'],
            'author' => $metas['og:novel:author'],
            'book_name' => $metas['og:novel:book_name'],
            'url' => $metas['og:url'],
            'status' => $metas['og:novel:status'],
            'update_time' => $metas['og:novel:update_time'],
            'latest_chapter_name' => $metas['og:novel:latest_chapter_name'],
            'latest_chapter_url' => $metas['og:novel:latest_chapter_url'],
        ];

        $count = Novel::where('host', $host)->where('url', $url)->count();

        if($count > 0) {
            Log::notice('小说链接' . $url . '已经抓取过');
            return;
        }

        $novel = Novel::create($data);

        $time = date('Y-m-d H:i:s');

        $chapters = [];
        foreach ($html->find('div[id=list]', 0)->children(0)->find('dd') as $item) {
            $chapters[] = [
                'host' => $host,
                'novel_id' => $novel->id,
                'url' => $item->children(0)->href,
                'name' => iconv('gbk', 'utf-8', $item->children(0)->innertext),
                'created_at' => $time,
                'updated_at' => $time,
            ];
        }

        Chapter::insert($chapters);

        $chapters = Chapter::where('novel_id', $novel->id)
            ->where('host', $host)
            ->get();

        foreach ($chapters as $chapter) {
            CreateChapterJob::dispatch($chapter);
        }
    }
}
