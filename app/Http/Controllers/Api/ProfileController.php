<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesStudent;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ResolvesStudent;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $this->resolveStudent($request);

        return response()->json([
            'user' => new UserResource($user),
            'siswa' => new StudentResource($student),
            'anak' => StudentResource::collection($this->linkedStudents($user)),
        ]);
    }

    public function children(Request $request): JsonResponse
    {
        return response()->json([
            'data' => StudentResource::collection($this->linkedStudents($request->user())),
        ]);
    }
}
