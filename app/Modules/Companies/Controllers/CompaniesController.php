<?php

namespace ProjectsManager\Modules\Companies\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Company;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Companies\Handlers\DeleteCompanyHandler;
use ProjectsManager\Modules\Companies\Handlers\ListCompaniesHandler;
use ProjectsManager\Modules\Companies\Handlers\ShowCompanyHandler;
use ProjectsManager\Modules\Companies\Handlers\StoreCompanyHandler;
use ProjectsManager\Modules\Companies\Handlers\UpdateCompanyHandler;

class CompaniesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ListCompaniesHandler $listCompaniesHandler
     * @param StoreCompanyHandler $storeCompanyHandler
     * @param ShowCompanyHandler $showCompanyHandler
     * @param UpdateCompanyHandler $updateCompanyHandler
     * @param DeleteCompanyHandler $deleteCompanyHandler
     */
    public function __construct(
        private readonly ListCompaniesHandler $listCompaniesHandler,
        private readonly StoreCompanyHandler $storeCompanyHandler,
        private readonly ShowCompanyHandler $showCompanyHandler,
        private readonly UpdateCompanyHandler $updateCompanyHandler,
        private readonly DeleteCompanyHandler $deleteCompanyHandler
    ) {
    }

    /**
     * Display a listing of companies.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return view('auth.login');
        }

        $companies = $this->listCompaniesHandler->handle(Auth::id());

        return view('companies.index', ['companies' => $companies]);
    }

    /**
     * Show the form for creating a new company.
     *
     * @return View
     */
    public function create(): View
    {
        return view('companies.create');
    }

    /**
     * Store a newly created company in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return back()->withInput()->with('errors', 'Error creating new company');
        }

        $request->validate($this->rules());

        $company = $this->storeCompanyHandler->handle(Auth::id(), $request->all());

        return redirect()->route('companies.show', ['company' => $company->id])
            ->with('success', 'Company created successfully');
    }

    /**
     * Display the specified company.
     *
     * @param Company $company
     *
     * @return View
     */
    public function show(Company $company): View
    {
        $company = $this->showCompanyHandler->handle($company);

        return view('companies.show', ['company' => $company]);
    }

    /**
     * Show the form for editing the specified company.
     *
     * @param Company $company
     *
     * @return View
     */
    public function edit(Company $company): View
    {
        $company = $this->showCompanyHandler->handle($company);

        return view('companies.edit', ['company' => $company]);
    }

    /**
     * Update the specified company in storage.
     *
     * @param Request $request
     * @param Company $company
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        $request->validate($this->rules());
        $updated = $this->updateCompanyHandler->handle($company, $request->all());

        if ($updated) {
            return redirect()->route('companies.show', ['company' => $company->id])
                ->with('success', 'Company updated successfully');
        }

        return back()->withInput();
    }

    private function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string'], 'contact_phone' => ['nullable', 'string', 'max:50'], 'contact_email' => ['nullable', 'email', 'max:255'], 'contact_address' => ['nullable', 'string', 'max:500'], 'contact_note' => ['nullable', 'string', 'max:1000']];
    }

    /**
     * Remove the specified company from storage.
     *
     * @param Company $company
     *
     * @return RedirectResponse
     */
    public function destroy(Company $company): RedirectResponse
    {
        abort_unless($company->user_id === Auth::id(), 403);

        if ($company->projects()->exists()) {
            return back()->with('error', 'Không thể xóa khách hàng đang có dự án. Hãy chuyển hoặc xóa các dự án trước.');
        }

        $deleted = $this->deleteCompanyHandler->handle($company);

        if ($deleted) {
            return redirect()->route('companies.index')
                ->with('success', 'Đã xóa khách hàng.');
        }

        return back()->withInput()->with('error', 'Không thể xóa khách hàng. Vui lòng thử lại.');
    }
}
