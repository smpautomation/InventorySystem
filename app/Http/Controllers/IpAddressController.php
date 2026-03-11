<?php

namespace App\Http\Controllers;

use App\Models\IpDetails;
use Illuminate\Http\Request;

class IpAddressController extends Controller
{
    public function getIpDetails(Request $request){
        try{
            $clientIp = $request->ip();

            $ipDetails = IpDetails::where('ip', $clientIp)
                        ->first();

            if ($ipDetails){
                return response()->json([
                    'ip' => $clientIp,
                    'location' => $ipDetails->location
                ]);
            }

            return response()->json([
                'ip' => $clientIp,
                'location' => 'Location not set'
            ]);

        } catch(\Exception $e){
            return response()->json([
                'error' => 'Failed to fetch IP Location',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
