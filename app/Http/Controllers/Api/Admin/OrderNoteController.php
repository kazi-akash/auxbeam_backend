<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderNoteController extends Controller
{
    /**
     * Get all notes for an order.
     */
    public function index(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $notes = $order->orderNotes()
            ->with('user:id,first_name,last_name,email')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    /**
     * Add a note to an order.
     */
    public function store(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validator = Validator::make($request->all(), [
            'note' => 'required|string',
            'note_type' => 'required|in:internal,customer,system',
            'is_customer_notified' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $note = $order->addNote(
            $request->note,
            $request->note_type,
            $request->user()->id,
            $request->boolean('is_customer_notified', false)
        );

        $note->load('user:id,first_name,last_name,email');

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully',
            'data' => $note,
        ], 201);
    }

    /**
     * Update a note.
     */
    public function update(Request $request, $orderId, $noteId)
    {
        $order = Order::findOrFail($orderId);
        $note = $order->orderNotes()->findOrFail($noteId);

        $validator = Validator::make($request->all(), [
            'note' => 'sometimes|string',
            'note_type' => 'sometimes|in:internal,customer,system',
            'is_customer_notified' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $note->update($validator->validated());
        $note->load('user:id,first_name,last_name,email');

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'data' => $note,
        ]);
    }

    /**
     * Delete a note.
     */
    public function destroy($orderId, $noteId)
    {
        $order = Order::findOrFail($orderId);
        $note = $order->orderNotes()->findOrFail($noteId);

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }
}
