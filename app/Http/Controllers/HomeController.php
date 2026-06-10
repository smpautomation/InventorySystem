<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    private string $apiBase = 'http://172.17.2.235/inventory/api';

    public function index(Request $request){
        try {
            $response = Http::timeout(15)->get("{$this->apiBase}/home.php", [
                'init' => 1
            ]);
            if ($response->failed()) {
                throw new \Exception("External API error: {$response->status()}");
            }
            return $response->json();
        } catch (\Exception $e) {

        }

        return $response->json();
    }

    public function show(){


    }
}
