<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\HelpResource;
use App\Models\Help;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        if ($request->vendor) {
            $data = Help::where('vendor', 1)->get();
        } else {
            $data = Help::where('vendor', 0)->get();
        }
        return HelpResource::collection($data)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
