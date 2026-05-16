<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderReminderController extends Controller
{
    /**
     * Get all reminders for an order.
     */
    public function index(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $reminders = $order->reminders()
            ->with(['creator:id,first_name,last_name,email', 'assignee:id,first_name,last_name,email'])
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $reminders,
        ]);
    }

    /**
     * Add a reminder to an order.
     */
    public function store(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'remind_at' => 'required|date|after:now',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reminder = $order->addReminder(
            $request->title,
            new \DateTime($request->remind_at),
            $request->description,
            $request->user()->id,
            $request->assigned_to
        );

        $reminder->load(['creator:id,first_name,last_name,email', 'assignee:id,first_name,last_name,email']);

        return response()->json([
            'success' => true,
            'message' => 'Reminder created successfully',
            'data' => $reminder,
        ], 201);
    }

    /**
     * Update a reminder.
     */
    public function update(Request $request, $orderId, $reminderId)
    {
        $order = Order::findOrFail($orderId);
        $reminder = $order->reminders()->findOrFail($reminderId);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'remind_at' => 'sometimes|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reminder->update($validator->validated());
        $reminder->load(['creator:id,first_name,last_name,email', 'assignee:id,first_name,last_name,email']);

        return response()->json([
            'success' => true,
            'message' => 'Reminder updated successfully',
            'data' => $reminder,
        ]);
    }

    /**
     * Mark reminder as completed.
     */
    public function complete($orderId, $reminderId)
    {
        $order = Order::findOrFail($orderId);
        $reminder = $order->reminders()->findOrFail($reminderId);

        $reminder->markAsCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Reminder marked as completed',
            'data' => $reminder,
        ]);
    }

    /**
     * Delete a reminder.
     */
    public function destroy($orderId, $reminderId)
    {
        $order = Order::findOrFail($orderId);
        $reminder = $order->reminders()->findOrFail($reminderId);

        $reminder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reminder deleted successfully',
        ]);
    }

    /**
     * Get all pending reminders (across all orders).
     */
    public function pending(Request $request)
    {
        $reminders = OrderReminder::pending()
            ->with(['order:id,order_number,customer_name', 'creator:id,first_name,last_name', 'assignee:id,first_name,last_name'])
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $reminders,
        ]);
    }

    /**
     * Get upcoming reminders.
     */
    public function upcoming(Request $request)
    {
        $reminders = OrderReminder::upcoming()
            ->with(['order:id,order_number,customer_name', 'creator:id,first_name,last_name', 'assignee:id,first_name,last_name'])
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $reminders,
        ]);
    }
}
