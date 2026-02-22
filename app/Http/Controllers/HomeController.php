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
        try {
            ini_set('memory_limit', '-1');
            $file = public_path($path);

            // Check if file exists before processing
            if (!file_exists($file) || !is_file($file)) {
                // Return a default placeholder image or 404 response
                return response()->json([
                    'error' => 'Image not found',
                    'message' => 'The requested image file does not exist'
                ], 404);
            }

            $img = Image::cache(function ($image) use ($file, $height, $width) {
                $image->make($file);
                if ($width != 0 && $height != 0) {
                    $image->resize($width, $height);
                }
            }, 10, true);

            return $img->response('jpg');
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('Image processing error: ' . $e->getMessage(), [
                'path' => $path,
                'width' => $width,
                'height' => $height
            ]);
            
            return response()->json([
                'error' => 'Image processing failed',
                'message' => 'Unable to process the requested image'
            ], 500);
        }
    }
}
