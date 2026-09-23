<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $orders = Order::with('items.menuItem')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());
        $order->load('items.menuItem');

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order): OrderResource
    {
        $order->load('items.menuItem');

        return new OrderResource($order);
    }

    public function confirm(Order $order): OrderResource
    {
        $order = $this->orderService->confirmOrder($order);

        return new OrderResource($order);
    }

    public function cancel(Order $order): OrderResource
    {
        $order = $this->orderService->cancelOrder($order);

        return new OrderResource($order);
    }
}
