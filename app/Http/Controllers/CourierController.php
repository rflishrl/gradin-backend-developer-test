<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCourierRequest;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CourierController extends Controller
{
    public function index(IndexCourierRequest $request): AnonymousResourceCollection
    {
        [$column, $direction] = $request->sortOrder();

        $couriers = Courier::query()
            ->search($request->validated('search'))
            ->withLevels($request->levels())
            ->orderBy($column, $direction)
            ->orderBy('id') // stable order for equal values, so pages never overlap
            ->paginate($request->perPage())
            ->withQueryString();

        return CourierResource::collection($couriers);
    }

    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());

        return (new CourierResource($courier))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier);
    }

    public function destroy(Courier $courier): Response
    {
        $courier->delete();

        return response()->noContent();
    }
}
