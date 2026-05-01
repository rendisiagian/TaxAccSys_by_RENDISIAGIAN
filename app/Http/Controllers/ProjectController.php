<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        $projects = Project::where('company_id', $company->id)
            ->orderBy('status')
            ->orderBy('name')
            ->paginate(15);

        return view('projects.index', compact('projects', 'company'));
    }

    public function create(Request $request)
    {
        return view('projects.form', [
            'project' => new Project(),
            'company' => $request->user()->currentCompany,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:20|unique:projects,code,NULL,id,company_id,' . $company->id,
            'description'     => 'nullable|string',
            'location'        => 'nullable|string|max:255',
            'client_name'     => 'nullable|string|max:255',
            'contract_number' => 'nullable|string|max:255',
            'contract_value'  => 'nullable|numeric|min:0',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'status'          => 'required|in:planning,active,completed,cancelled',
        ]);

        $validated['company_id'] = $company->id;
        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', __('Data saved successfully.'));
    }

    public function edit(Request $request, Project $project)
    {
        abort_if($project->company_id !== $request->user()->current_company_id, 403);
        return view('projects.form', [
            'project' => $project,
            'company' => $request->user()->currentCompany,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $company = $request->user()->currentCompany;
        abort_if($project->company_id !== $company->id, 403);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:20|unique:projects,code,' . $project->id . ',id,company_id,' . $company->id,
            'description'     => 'nullable|string',
            'location'        => 'nullable|string|max:255',
            'client_name'     => 'nullable|string|max:255',
            'contract_number' => 'nullable|string|max:255',
            'contract_value'  => 'nullable|numeric|min:0',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'status'          => 'required|in:planning,active,completed,cancelled',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', __('Data updated successfully.'));
    }

    public function destroy(Request $request, Project $project)
    {
        abort_if($project->company_id !== $request->user()->current_company_id, 403);
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', __('Data deleted successfully.'));
    }
}
