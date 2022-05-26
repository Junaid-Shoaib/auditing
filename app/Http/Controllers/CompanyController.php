<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountGroup;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Year;
use App\Models\Setting;
use Egulias\EmailValidator\Warning\Warning;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;


use App;
use App\Models\AccountType;
use App\Models\Document;
use App\Models\Entry;
use App\Models\DocumentType;
use Carbon\Carbon;


class CompanyController extends Controller
{


    public function index()
    {
        //Validating request
        request()->validate([
            'direction' => ['in:asc,desc'],
            'field' => ['in:name,email']
        ]);

        $query =
        // auth()->user()->companies()->getQuery()->paginate(10)
            Company::getQuery()->paginate(10)
            ->withQueryString()
            ->through(
                fn ($comp) =>
                [
                    'id' => $comp->id,
                    'name' => $comp->name,
                    'address' => $comp->address,
                    'email' => $comp->email,
                    'web' => $comp->web,
                    'phone' => $comp->phone,
                    'fiscal' => $comp->fiscal,
                    'incorp' => $comp->incorp,
                    'delete' => Year::where('company_id', $comp->id)->first() != null ? true : false,
                ],
            );

        if (request('search')) {
            $query->where('name', 'LIKE', '%' . request('search') . '%');
        }

        if (request()->has(['field', 'direction'])) {
            $query->orderBy(
                request('field'),
                request('direction')
            );
        }


        return Inertia::render('Company/Index', [
            // 'can' => [
            //     'edit' => auth()->user()->can('edit'),
            //     'create' => auth()->user()->can('create'),
            //     'delete' => auth()->user()->can('delete'),
            //     'read' => auth()->user()->can('read'),
            // ],
            'balances' => $query,
            'filters' => request()->all(['search', 'field', 'direction'])
        ]);
    }

    public function create()
    {
        $fiscals = ['June', 'March', 'September', 'December'];
        $fiscal_first = 'June';

        return Inertia::render('Company/Create', [
            'fiscals' => $fiscals, 'fiscal_first' => $fiscal_first
        ]);
    }

    public function store()
    {
        Request::validate([
            'name' => ['required', 'unique:companies'],
            'fiscal' => ['required'],
        ]);
        DB::transaction(function () {
            $company = Company::create([
                'name' => strtoupper(Request::input('name')),
                'address' => Request::input('address'),
                'email' => Request::input('email'),
                'web' => Request::input('web'),
                'phone' => Request::input('phone'),
                'fiscal' => Request::input('fiscal'),
                'incorp' => Request::input('incorp'),
            ]);
            $company->users()->attach(auth()->user()->id);

            //Start Month & End Month
            $startMonth = Carbon::parse($company->fiscal)->month + 1;
            $endMonth = Carbon::parse($company->fiscal)->month;
            if ($startMonth == 13) {
                $startMonth = 1;
            }

            //Start Month Day & End Month Day
            $startMonthDays = 1;
            $endMonthDays = Carbon::create()->month($endMonth)->daysInMonth;

            // Year Get
            $today = Carbon::today();
            $startYear = 0;
            $endYear = 0;
            if ($startMonth == 1) {
                $startYear = $today->year;
                $endYear = $today->year;
            } else {
                $endYear = ($today->month >= $startMonth) ? $today->year + 1 : $today->year;
                $startYear = $endYear - 1;
            }

            $startDate = $startYear . '-' . '0' . $startMonth . '-' . $startMonthDays;
            $endDate = $endYear . '-' . '0' . $endMonth . '-' . $endMonthDays;

            $year = Year::create([
                'begin' => $startDate,
                'end' => $endDate,
                'company_id' => $company->id,
            ]);
            Setting::create([
                'key' => 'active_company',
                'value' => $company->id,
                'user_id' => Auth::user()->id,
            ]);

            Setting::create([
                'key' => 'active_year',
                'value' => $year->id,
                'user_id' => Auth::user()->id,
            ]);

            session(['company_id' => $company->id]);
            session(['year_id' => $year->id]);

            // Storage::makeDirectory('/public/' . $company->id);
            // Storage::makeDirectory('/public/' . $company->id . '/' . $year->id);
        });
        return Redirect::route('companies')->with('success', 'Company created');
    }

    public function edit(Company $company)
    {
        return Inertia::render('Company/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'address' => $company->address,
                'email' => $company->email,
                'web' => $company->web,
                'phone' => $company->phone,
                'fiscal' => $company->fiscal,
                'incorp' => $company->incorp,
            ],
        ]);
    }

    public function update(Company $company)
    {
        Request::validate([
            'name' => ['required'],
            'address' => ['nullable'],
            'email' => ['nullable'],
            'web' => ['nullable'],
            'phone' => ['nullable'],
            'fiscal' => ['required'],
            'incorp' => ['nullable'],
        ]);

        $company->name = Request::input('name');
        $company->address = Request::input('address');
        $company->email = Request::input('email');
        $company->web = Request::input('web');
        $company->phone = Request::input('phone');
        $company->fiscal = Request::input('fiscal');

        $incorp = new carbon(Request::input('incorp'));
        $company->incorp = $incorp->format('Y-m-d');

        $company->save();

        return Redirect::route('companies')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return Redirect::back()->with('success', 'Company deleted.');
    }

    //TO CHANGE THE COMPANY IN SESSION FROM DROPDOWN
    public function coch($id)
    {
        $active_co = Setting::where('user_id', Auth::user()->id)->where('key', 'active_company')->first();
        // $coch_hold = Company::where('id', $active_co->value)->first();
        // $active_co = Setting::all();
        // where('user_id', Auth::user()->id)->where('key', 'active_company')->first();
        // dd($active_co);

        $active_co->value = $id;

        $active_co->save();
        session(['company_id' => $id]);

        if (Year::where('company_id', $id)->latest()->first()) {
            $active_yr = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();
            $active_yr->value = Year::where('company_id', $id)->latest()->first()->id;
            $active_yr->save();

            $active_yr = Year::where('company_id', $id)->latest()->first()->id;

            session(['year_id' => $active_yr]);
            // session(['year_id' => $active_yr->value]);
            // $active_co->save();
            // session(['company_id' => $id]);
            return Redirect::back();
        } else {
            session(['year_id' => null]);
            return Redirect::route('years.create')->with('success', 'YEAR NOT FOUND. Please create an Year for selected Company.');
        }
    }


    // FOR PDF FROM MZAUDIT --------
    public function pd()
    {
        // $a = "hello world";
        // dd(AccountType::where('company_id', session('company_id'))->first());
        $voucher = Entry::all()
            ->where('id', 2)
            // ->where('company_id', session('company_id'))
            //     ->where('year_id', session('year_id'))

            ->map(function ($comp) {
                return [
                    'id' => $comp->id,
                    'debit' => $comp->debit,
                    'credit' => $comp->credit,
                    'description' => 'description',
                    'ref' => 'ref',
                    'name' => 'name',
                    // 'ref' => $comp->document->ref,
                    // 'description' => $comp->document->description,
                    // 'name' => $comp->document->documentType->name,
                ];
            })
            ->first();

        $data['entry_obj'] = Entry::all()->where('company_id', session('company_id'))->where('year_id', session('year_id'));

        $i = 0;
        foreach ($data['entry_obj'] as $entry) {
            if ($entry) {
                $data['entries'][$i] = $entry;
                $i++;
            }
        }
        $data['doc'] = Document::all()->where('id', $data['entries'][0]->document_id)->first();
        $data['doc_type'] = DocumentType::all()->where('id', $data['doc']->type_id)->first();
        $a = Company::where('id', session('company_id'))->first();
        $pdf = App::make('dompdf.wrapper');
        // $pdf->loadView('pdf', compact('a'));
        $pdf->loadView('pdf', $data);
        return $pdf->stream('v.pdf');
    }
    // FOR PDF FROM MZAUDIT --------

}
