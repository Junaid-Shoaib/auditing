<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Year;
use App\Models\Setting;
use App\Models\Company;
// use App\Models\Document;
use Inertia\Inertia;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\Account;
use App\Models\Entry;
use App\Models\FileManager;

class YearController extends FileMangementController
{
    public function index()
    {
        if(Company::first())
        {
            $query = Year::query();
            return Inertia::render('Years/Index', [
                // 'data' => Year::all()
                'balances' => $query
                    ->where('company_id', session('company_id'))
                    // ->map(
                    ->paginate(10)
                    ->through(
                        function ($year) {
                            return [
                                $begin = new Carbon($year->begin),
                                $end = new Carbon($year->end),
                                'id' => $year->id,
                                'closed' => $year->closed,
                                'begin' => $begin->format('F,j Y'),
                                'end' => $end->format('F,j Y'),
                                'company_name' => $year->company->name,
                                'company_id' => $year->company_id,
                                // 'delete' => Document::where('year_id', $year->id)->first() || $year->id == Year::where('company_id', session('company_id'))->first()->id ? false : true,
                            ];
                        },
                    ),
                'company' => Company::where('id', session('company_id'))->first(),
                // 'companies' => Company::all()
                //     ->map(function ($com) {
                //         return [
                //             'id' => $com->id,
                //             'name' => $com->name,
                //         ];
                //     }),
                'companies' => Auth::user()->companies,

            ]);
        } else {
            return Redirect::route('companies')->with('warning', 'Create Company first');
        }
    }

    public function create()
    {
        $year = Year::where('company_id', session('company_id'))->latest()->first();
        $begin = explode('-', $year->begin);
        $begin[0]++;
        $end = explode('-', $year->end);
        $end[0]++;
        $newBegin = implode('-', $begin);
        $newEnd = implode('-', $end);

        $year = Year::create([
            'begin' => $newBegin,
            'end' => $newEnd,
            'company_id' => session('company_id'),
        ]);
        session(['year_id' => $year->id]);

        Storage::makeDirectory('/public/' . $year->company_id . '/' . $year->id);

        // Calling the function from DefaultFoldersCreation controller ---- to generate the default folder
        $this->defaultFolders();
        return Redirect::back()->with('success', 'Year created.');
    }

    // public function store(Req $request)
    // {
    //     Request::validate([
    //         'begin' => ['required', 'date'],
    //         'end' => ['required', 'date'],
    //     ]);

    //     $year = Year::create([
    //         'begin' => $request->begin,
    //         'end' => $request->end,
    //         'company_id' => session('company_id'),
    //     ]);

    //     Setting::create([
    //         'key' => 'active_year',
    //         'value' => $year->id,
    //         'user_id' => Auth::user()->id,
    //     ]);

    //     session(['year_id' => $year->id]);
    //     return Redirect::route('years')->with('success', 'Year created.');
    // }

    public function edit(Year $year)
    {
        return Inertia::render('Years/Edit', [
            'year' => [
                'id' => $year->id,
                'begin' => $year->begin,
                'end' => $year->end,
                'company_id' => session('company_id'),
            ],
        ]);
    }

    public function update(Year $year)
    {
        Request::validate([
            'begin' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $begin = new carbon(Request::input('begin'));
        $end = new carbon(Request::input('end'));

        $year->begin = $begin->format('Y-m-d');
        $year->end = $end->format('Y-m-d');
        $year->company_id = session('company_id');
        $year->save();

        return Redirect::route('years')->with('success', 'Year updated.');
    }

    public function destroy(Year $year)
    {
        // if (Document::where('year_id', $year->id)->first()) {
        //     return Redirect::back()->with('success', 'Can\'t DELETE this Year.');
        // } else {
        $year->delete();
        if (Year::where('company_id', session('company_id'))->first()) {
            return Redirect::back()->with('success', 'Year deleted.');
        } else {
            session(['year_id' => null]);
            return Redirect::route('years.create')->with('success', 'YEAR NOT FOUND. Please create an Year for selected Company.');
        }
        // }
    }

    public function yrch($id)
    {
        $active_yr = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();

        $active_yr->value = $id;
        $active_yr->save();
        session(['year_id' => $id]);

        return Redirect::back();
    }

    public function close($id)
    {
        DB::transaction(function () use ($id) {
            $year =  \App\Models\Year::where('company_id', session('company_id'))->where('id', $id)->first();
            $yearef = Carbon::parse($year->end);
            $claccount = null;
            // $clgroup = null;
            // if (!Account::where('company_id', session('company_id'))->where('name', 'Accumulated Profit')->first()) {
                // if (!AccountGroup::where('company_id', session('company_id'))->where('name', 'Reserves')->first()) {
                //     $clgroup = AccountGroup::create([
                //         'name' => 'Reserves',
                //         'type_id' => AccountType::where('name', 'Capital')->first()->id,
                //         'company_id' => session('company_id'),
                //     ]);
                // } else {
                //     $clgroup = AccountGroup::where('company_id', session('company_id'))->where('name', 'Reserves')->first();
                // }
            //     $claccount = Account::create([
            //         'name' => 'Accumulated Profit',
            //         'group_id' => $clgroup->id,
            //         'company_id' => session('company_id'),
            //     ]);
            // } else {
                $claccount = Account::where('company_id', session('company_id'))->where('name', 'Accumulated Profit')->first();
            // }



            $doc = \App\Models\Document::create([
                'ref' => 'CL/' . $yearef->year . '/' . $yearef->month . '/00',
                'date' => $year->end,
                'description' => 'To the record the closing entry.',
                'type_id' => \App\Models\DocumentType::where('company_id', session('company_id'))->first()->id,
                'company_id' => session('company_id'),
                'year_id' => $year->id,
            ]);

            $id4 = \App\Models\AccountType::where('name', 'Revenue')->first()->id;
            $id5 = \App\Models\AccountType::where('name', 'Expenses')->first()->id;
            $grps4 = AccountGroup::where('company_id', session('company_id'))
                ->where(function (Builder $query)  use ($id4, $id5) {
                    return $query->where('type_id', $id4)
                        ->orWhere('type_id', $id5);
                })->get();

            $balance4 = [];
            $ite4 = 0;
            foreach ($grps4 as $group) {
                foreach ($group->accounts as $account) {
                    $balance = 0;
                    $lastbalance = 0;

                    $entries = DB::table('documents')
                        ->join('entries', 'documents.id', '=', 'entries.document_id')
                        ->whereDate('documents.date', '>=', $year->begin)
                        ->whereDate('documents.date', '<=', $year->end)
                        ->where('documents.company_id', session('company_id'))
                        ->where('entries.account_id', '=', $account->id)
                        ->select('entries.debit', 'entries.credit')
                        ->get();

                    foreach ($entries as $entry) {
                        $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                        $lastbalance = $balance;
                    }

                    $balance4[$ite4++] = $balance;
                    $balance = -1 * $balance;
                    Entry::create([
                        'document_id' => $doc->id,
                        'account_id' => $account->id,
                        'debit' => ($balance < 0) ? '0' : $balance,
                        'credit' => ($balance > 0) ? '0' : abs($balance),
                        'company_id' => session('company_id'),
                        'year_id' => $year->id,

                    ]);
                }
            }


            //            $profit = abs(array_sum($gbalance4)) - array_sum($gbalance5);

            Entry::create([
                'document_id' => $doc->id,
                'account_id' => $claccount->id,
                'debit' => (array_sum($balance4) < 0) ? '0' : array_sum($balance4),
                'credit' => (array_sum($balance4) > 0) ? '0' : abs(array_sum($balance4)),
                'company_id' => session('company_id'),
                'year_id' => $year->id,
            ]);

            $year->update(['closed' => 1]);
        });
        session()->flash('message', 'Fiscal Year closed successfully.');
        return Redirect::route('reports');
    }
}
