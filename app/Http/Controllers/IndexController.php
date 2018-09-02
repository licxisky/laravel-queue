<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Jobs\CreateNovelJob;
use App\Novel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Yangqi\Htmldom\Htmldom;

class IndexController extends Controller
{
    public function index()
    {
        CreateNovelJob::dispatch('http://www.biquge.com.tw/16_16367/');
    }
}
