<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientExport;
use App\Exports\ClientTemplateExport;
use App\Imports\ClientImport;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        $clients = Client::where('company_id', $company->id)->paginate(15);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.form', ['client' => new Client()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tin' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $validated['company_id'] = $request->user()->current_company_id;
        
        Client::create($validated);
        
        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function edit(Request $request, Client $client)
    {
        abort_if($client->company_id !== $request->user()->current_company_id, 403);
        return view('clients.form', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        abort_if($client->company_id !== $request->user()->current_company_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tin' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $client->update($validated);
        
        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Request $request, Client $client)
    {
        abort_if($client->company_id !== $request->user()->current_company_id, 403);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }

    public function export(Request $request)
    {
        return Excel::download(new ClientExport($request->user()->current_company_id), 'clients.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ClientTemplateExport(), 'clients_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        Excel::import(new ClientImport($request->user()->current_company_id), $request->file('file'));

        return redirect()->route('clients.index')->with('success', 'Clients imported successfully.');
    }
}
