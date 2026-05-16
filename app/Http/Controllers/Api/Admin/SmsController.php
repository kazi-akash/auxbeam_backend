<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsConfiguration;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    // ========== SMS Templates ==========

    public function templates(Request $request)
    {
        $query = SmsTemplate::query();

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $templates = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $template = SmsTemplate::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SMS template created successfully',
            'data' => $template,
        ], 201);
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = SmsTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'type' => 'string|max:50',
            'content' => 'string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $template->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SMS template updated successfully',
            'data' => $template,
        ]);
    }

    public function deleteTemplate($id)
    {
        $template = SmsTemplate::findOrFail($id);
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'SMS template deleted successfully',
        ]);
    }

    // ========== SMS Logs ==========

    public function logs(Request $request)
    {
        $query = SmsLog::with('user', 'order');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('provider')) {
            $query->byProvider($request->provider);
        }

        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 15);
        $logs = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function retryLog($id)
    {
        $success = $this->smsService->retryFailed($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'SMS retry initiated' : 'Failed to retry SMS',
        ]);
    }

    // ========== SMS Configuration ==========

    public function configurations()
    {
        $configs = SmsConfiguration::orderBy('priority', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $configs,
        ]);
    }

    public function storeConfiguration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:bulksmsbd,twilio|unique:sms_configurations,provider',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'credentials' => 'required|array',
            'settings' => 'nullable|array',
            'priority' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // If setting as default, unset other defaults
        if ($request->is_default) {
            SmsConfiguration::where('is_default', true)->update(['is_default' => false]);
        }

        $config = SmsConfiguration::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SMS configuration created successfully',
            'data' => $config,
        ], 201);
    }

    public function updateConfiguration(Request $request, $id)
    {
        $config = SmsConfiguration::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'credentials' => 'array',
            'settings' => 'nullable|array',
            'priority' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // If setting as default, unset other defaults
        if ($request->is_default) {
            SmsConfiguration::where('id', '!=', $id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $config->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'SMS configuration updated successfully',
            'data' => $config,
        ]);
    }

    public function deleteConfiguration($id)
    {
        $config = SmsConfiguration::findOrFail($id);
        $config->delete();

        return response()->json([
            'success' => true,
            'message' => 'SMS configuration deleted successfully',
        ]);
    }

    // ========== SMS Sending ==========

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string|max:500',
            'provider' => 'nullable|string|in:bulksmsbd,twilio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->smsService->send(
            $request->phone,
            $request->message,
            null,
            null,
            $request->provider
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'SMS sent successfully' : 'Failed to send SMS',
        ]);
    }

    // ========== SMS Statistics ==========

    public function statistics(Request $request)
    {
        $filters = [];

        if ($request->has('start_date')) {
            $filters['start_date'] = $request->start_date;
        }

        if ($request->has('end_date')) {
            $filters['end_date'] = $request->end_date;
        }

        if ($request->has('provider')) {
            $filters['provider'] = $request->provider;
        }

        $stats = $this->smsService->getStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
