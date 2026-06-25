<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Stats\BuildStatsSummary;
use App\Actions\Stats\GetUpcomingRenewals;
use App\Http\Resources\SubscriptionResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class StatsController extends Controller
{
    public function summary(Request $request, BuildStatsSummary $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(['data' => $action->handle($user)]);
    }

    public function upcoming(Request $request, GetUpcomingRenewals $action): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return SubscriptionResource::collection($action->handle($user));
    }
}
