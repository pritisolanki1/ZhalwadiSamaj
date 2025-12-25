<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Image;

class HomeController extends Controller
{
    public function __construct()
    {
        //            $this->middleware('auth');
    }

    public function index(): Renderable
    {
        return view('welcome');
    }

    public function dashboard(): Factory|View|Application
    {
        return view('dashboard');
    }

    public function getImage($width, $height, $path)
    {
        ini_set('memory_limit', '-1');
        $file = public_path($path);

        // dd($file);
        $img = Image::cache(function ($image) use ($file, $height, $width) {
            $image->make($file);
            if ($width != 0 && $height != 0) {
                $image->resize($width, $height);
            }
        }, 10, true);

        return $img->response('jpg');
    }
}
