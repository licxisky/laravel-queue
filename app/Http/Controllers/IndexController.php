<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Jobs\CreateNovelJob;

class IndexController extends Controller
{
    public function index(Chapter $chapter)
    {
        CreateNovelJob::dispatch('http://www.biquge.com.tw/17_17281/');
    }
}
