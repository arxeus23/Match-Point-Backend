<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TenantCrudController extends Controller
{
    private function organizationId(Request $request): int
    {
        abort_if($request->user()->organization_id === null, 403, 'An organization workspace is required.');

        return $request->user()->organization_id;
    }

    private function owned(Request $request, string $model, int $id): Model
    {
        return $model::where('organization_id', $this->organizationId($request))->findOrFail($id);
    }

    public function departments(Request $request): JsonResponse
    {
        return response()->json(Department::where('organization_id', $this->organizationId($request))->withCount('jobs')->orderBy('name')->get());
    }

    public function storeDepartment(Request $request): JsonResponse
    {
        $org = $this->organizationId($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:120', Rule::unique('departments')->where('organization_id', $org)], 'description' => 'nullable|string|max:1000']);

        return response()->json(Department::create([...$data, 'organization_id' => $org]), 201);
    }

    public function updateDepartment(Request $request, int $department): JsonResponse
    {
        $record = $this->owned($request, Department::class, $department);
        $record->update($request->validate(['name' => ['sometimes', 'string', 'max:120', Rule::unique('departments')->where('organization_id', $record->organization_id)->ignore($record->id)], 'description' => 'nullable|string|max:1000']));

        return response()->json($record->fresh()->loadCount('jobs'));
    }

    public function deleteDepartment(Request $request, int $department): Response
    {
        $record = $this->owned($request, Department::class, $department);
        abort_if($record->jobs()->exists(), 409, 'Move or delete this department’s jobs first.');
        $record->delete();

        return response()->noContent();
    }

    public function jobs(Request $request): JsonResponse
    {
        return response()->json(Job::where('organization_id', $this->organizationId($request))->with('department')->withCount('applications')->latest()->paginate(50));
    }

    public function storeJob(Request $request): JsonResponse
    {
        $org = $this->organizationId($request);
        $data = $request->validate($this->jobRules());
        if (isset($data['department_id'])) {
            $this->owned($request, Department::class, (int) $data['department_id']);
        }

return response()->json(Job::create([...$data, 'organization_id' => $org]), 201);
    }

    public function updateJob(Request $request, int $job): JsonResponse
    {
        $record = $this->owned($request, Job::class, $job);
        $data = $request->validate($this->jobRules(true));
        if (isset($data['department_id'])) {
            $this->owned($request, Department::class, (int) $data['department_id']);
        } $record->update($data);

        return response()->json($record->fresh()->load('department')->loadCount('applications'));
    }

    public function deleteJob(Request $request, int $job): Response
    {
        $this->owned($request, Job::class, $job)->delete();

        return response()->noContent();
    }

    public function candidates(Request $request): JsonResponse
    {
        return response()->json(Candidate::where('organization_id', $this->organizationId($request))->withCount('applications')->latest()->paginate(50));
    }

    public function storeCandidate(Request $request): JsonResponse
    {
        $org = $this->organizationId($request);
        $data = $request->validate($this->candidateRules($org));

        return response()->json(Candidate::create([...$data, 'organization_id' => $org]), 201);
    }

    public function updateCandidate(Request $request, int $candidate): JsonResponse
    {
        $record = $this->owned($request, Candidate::class, $candidate);
        $record->update($request->validate($this->candidateRules($record->organization_id, true, $record->id)));

        return response()->json($record->fresh()->loadCount('applications'));
    }

    public function deleteCandidate(Request $request, int $candidate): Response
    {
        $this->owned($request, Candidate::class, $candidate)->delete();

        return response()->noContent();
    }

    public function applications(Request $request): JsonResponse
    {
        return response()->json(Application::where('organization_id', $this->organizationId($request))->with(['job:id,title', 'candidate:id,first_name,last_name,email'])->latest()->paginate(50));
    }

    public function storeApplication(Request $request): JsonResponse
    {
        $org = $this->organizationId($request);
        $data = $request->validate($this->applicationRules());
        $this->owned($request, Job::class, (int) $data['job_id']);
        $this->owned($request, Candidate::class, (int) $data['candidate_id']);

        return response()->json(Application::create([...$data, 'organization_id' => $org]), 201);
    }

    public function updateApplication(Request $request, int $application): JsonResponse
    {
        $record = $this->owned($request, Application::class, $application);
        $record->update($request->validate($this->applicationRules(true)));

        return response()->json($record->fresh()->load(['job:id,title', 'candidate:id,first_name,last_name,email']));
    }

    public function deleteApplication(Request $request, int $application): Response
    {
        $this->owned($request, Application::class, $application)->delete();

        return response()->noContent();
    }

    public function users(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['super_admin', 'organization_admin'], true), 403);
        $query = User::query()->with('organization:id,name')->latest();
        if (! $request->user()->isSuperAdmin()) {
            $query->where('organization_id', $this->organizationId($request));
        }

return response()->json($query->paginate(50));
    }

    public function updateUser(Request $request, int $user): JsonResponse
    {
        abort_unless(in_array($request->user()->role, ['super_admin', 'organization_admin'], true), 403);
        $record = User::findOrFail($user);
        if (! $request->user()->isSuperAdmin()) {
            abort_unless($record->organization_id === $this->organizationId($request), 404);
        } $record->update($request->validate(['name' => 'sometimes|string|max:255', 'role' => 'sometimes|in:organization_admin,recruiter,hiring_manager,interviewer', 'status' => 'sometimes|in:active,inactive']));

        return response()->json($record->fresh());
    }

    public function deleteUser(Request $request, int $user): Response
    {
        abort_unless(in_array($request->user()->role, ['super_admin', 'organization_admin'], true), 403);
        $record = User::findOrFail($user);
        abort_if($record->is($request->user()), 422, 'You cannot delete your own account.');
        if (! $request->user()->isSuperAdmin()) {
            abort_unless($record->organization_id === $this->organizationId($request), 404);
        } $record->tokens()->delete();
        $record->delete();

        return response()->noContent();
    }

    private function jobRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return ['title' => "$required|string|max:255", 'description' => "$required|string|max:10000", 'department_id' => 'nullable|integer', 'location' => 'nullable|string|max:255', 'workplace_type' => 'sometimes|in:remote,hybrid,onsite', 'employment_type' => 'sometimes|in:full_time,part_time,contract,internship', 'status' => 'sometimes|in:draft,open,closed', 'required_skills' => 'sometimes|array', 'required_skills.*' => 'string|max:100', 'openings' => 'sometimes|integer|min:1|max:100'];
    }

    private function candidateRules(int $org, bool $partial = false, ?int $ignore = null): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return ['first_name' => "$required|string|max:100", 'last_name' => "$required|string|max:100", 'email' => [$required, 'email', Rule::unique('candidates')->where('organization_id', $org)->ignore($ignore)], 'phone' => ['nullable', 'regex:/^\+[1-9]\d{7,14}$/'], 'current_title' => 'nullable|string|max:255', 'location' => 'nullable|string|max:255', 'experience_years' => 'sometimes|integer|min:0|max:80', 'skills' => 'sometimes|array', 'skills.*' => 'string|max:100', 'source' => 'sometimes|in:direct,referral,linkedin,agency,career_site', 'status' => 'sometimes|in:active,archived'];
    }

    private function applicationRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return ['job_id' => "$required|integer", 'candidate_id' => "$required|integer", 'stage' => 'sometimes|in:applied,screening,technical,practical,behavioral,offer,hired,rejected', 'match_score' => 'nullable|numeric|min:0|max:100', 'notes' => 'nullable|string|max:5000'];
    }
}
