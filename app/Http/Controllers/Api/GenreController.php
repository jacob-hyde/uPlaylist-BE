<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * @OA\Get(
     *      path="/genre",
     *      operationId="getGenres",
     *      tags={"Genre"},
     *      summary="Get list of genres",
     *      description="Returns list of genres",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/GenreResource")
     *       )
     *     )
     */
    public function index()
    {
        return GenreResource::collection(Genre::all())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
