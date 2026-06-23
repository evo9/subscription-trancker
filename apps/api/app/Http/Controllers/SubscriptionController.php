<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Subscriptions\CreateSubscription;
use App\Actions\Subscriptions\PauseSubscription;
use App\Actions\Subscriptions\ResumeSubscription;
use App\Actions\Subscriptions\UpdateSubscription;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SubscriptionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Subscription::class);

        /** @var User $user */
        $user = $request->user();

        $query = Subscription::query()
            ->forUser($user)
            ->with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('due_within')) {
            $query->dueWithin($request->integer('due_within'));
        }

        return SubscriptionResource::collection($query->latest()->paginate());
    }

    public function store(StoreSubscriptionRequest $request, CreateSubscription $action): JsonResponse
    {
        $this->authorize('create', Subscription::class);

        /** @var User $user */
        $user = $request->user();

        $subscription = $action->handle($request, $user);

        return SubscriptionResource::make($subscription->load('category'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Subscription $subscription): SubscriptionResource
    {
        $this->authorize('view', $subscription);

        return SubscriptionResource::make($subscription->load(['category', 'payments']));
    }

    public function update(
        UpdateSubscriptionRequest $request,
        Subscription $subscription,
        UpdateSubscription $action,
    ): SubscriptionResource {
        $this->authorize('update', $subscription);

        return SubscriptionResource::make($action->handle($request, $subscription)->load('category'));
    }

    public function destroy(Subscription $subscription): JsonResponse
    {
        $this->authorize('delete', $subscription);

        $subscription->delete();

        return response()->json(null, 204);
    }

    public function pause(Subscription $subscription, PauseSubscription $action): SubscriptionResource
    {
        $this->authorize('pause', $subscription);

        return SubscriptionResource::make($action->handle($subscription)->load('category'));
    }

    public function resume(Subscription $subscription, ResumeSubscription $action): SubscriptionResource
    {
        $this->authorize('resume', $subscription);

        return SubscriptionResource::make($action->handle($subscription)->load('category'));
    }

    public function payments(Subscription $subscription): AnonymousResourceCollection
    {
        $this->authorize('view', $subscription);

        return PaymentResource::collection(
            $subscription->payments()->latest('paid_at')->get(),
        );
    }
}
