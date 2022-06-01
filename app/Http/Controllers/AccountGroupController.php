<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\Company;
use Database\Seeders\AccountSeeder;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request as Req;

class AccountGroupController extends Controller
{
    public function index()
    {
        //Validating request
        request()->validate([
            'direction' => ['in:asc,desc'],
            'field' => ['in:name,email']
        ]);

        //Searching request
        $query = AccountGroup::query();
        if (request('search')) {
            $query->where('name', 'LIKE', '%' . request('search') . '%');
        }

        // // Ordering request
        // if (request()->has(['field', 'direction'])) {
        //     $query->orderBy(
        //         request('field'),
        //         request('direction')
        //     );
        // }

        $balances = $query
            ->where('company_id', session('company_id'))
            ->paginate(10)
            ->through(
                function ($accountgroup) {
                    return
                        [
                            'id' => $accountgroup->id,
                            'name' => $accountgroup->name,
                            'type_id' => $accountgroup->type_id,
                            'type_name' => $accountgroup->accountType->name,
                            'company_id' => $accountgroup->company_id,
                            'company_name' => $accountgroup->company->name,
                            'delete' => Account::where('group_id', $accountgroup->id)->first() ? false : true,
                        ];
                }
            );

        return Inertia::render('AccountGroups/Index', [
            'filters' => request()->all(['search', 'field', 'direction']),
            'balances' => $balances,
            'exists' => AccountGroup::where('company_id', session('company_id'))->first() ? false : true,
            'company' => Company::where('id', session('company_id'))->first(),
            'companies' => Auth::user()->companies,
        ]);
    }

    // public function generate()
    // {
    //     // $exitCode = Artisan::call('db:seed', [
    //     //     '--class' => 'TypeSeeder'
    //     // ]);
    //     // print_r("hi");
    //     // die();
    //     // $this->call([
    //     //     // UserSeeder::class,
    //     //     // PostSeeder::class,
    //     //     // CommentSeeder::class,
    //     //     // AccountSeeder::class,
    //     //     GroupSeeder::class,
    //     // ]);
    //     return GroupSeeder::class;
    //     return Redirect::back()->with('success', 'Account Group deleted.');
    // }

    public function create(Req $request)
    {
        if ($request->type_id) {
            $first = AccountType::where('id', $request->type_id)->first();
            $name = $request->name;
        } else {
            $name = null;
            $first = AccountType::all('id', 'name')->first();
        }
        $types = AccountType::all()->map->only('id', 'name');
        $data = AccountGroup::where('company_id', session('company_id'))->where('type_id', $first->id)->tree()->get()->toTree();

        return Inertia::render('AccountGroups/Create', [
            'types' => $types,
            'first' => $first,
            'data' => $data,
            'name' => $name,
        ]);
    }

    public function store(Req $request)
    {
        Request::validate([
            'type_id' => ['required'],
            'name' => ['required', 'unique:account_groups'],
            'parent_id' => [],
        ]);
        AccountGroup::create([
            'type_id' => Request::input('type_id'),
            'parent_id' => Request::input('parent_id'),
            'name' => Request::input('name'),
            'company_id' => session('company_id'),
        ]);

        return Redirect::route('accountgroups')->with('success', 'Account Group created.');
    }

    public function edit(AccountGroup $accountgroup)
    {
        $accountgroup = AccountGroup::where('id', $accountgroup->id)->get()
            ->map(function ($accountgroup) {
                    return
                        [
                            'id' => $accountgroup->id,
                            'type_id' => $accountgroup->accountType->name,
                            // 'parent_id' => $accountgroup->parent_id,
                            'parent_id' => $accountgroup->parent_id ? $accountgroup->accountGroup->name : null,
                            'name' => $accountgroup->name,
                            'company_id' => session('company_id'),
                            'delete' => Account::where('group_id', $accountgroup->id)->first() ? false : true,
                        ];
                }
            );
        return Inertia::render('AccountGroups/Edit', [
            'accountgroup' => $accountgroup,
        ]);
    }

    public function update(AccountGroup $accountgroup)
    {
        Request::validate([
            // 'type' => ['required'],
            'name' => ['required'],
        ]);
        // $accountgroup->type_id = Request::input('type');
        // $accountgroup->company_id = session('company_id');
        $accountgroup->name = Request::input('name');
        $accountgroup->save();

        return Redirect::route('accountgroups')->with('success', 'Account Group updated.');
    }

    public function destroy(AccountGroup $accountgroup)
    {
        $accountgroup->delete();
        return Redirect::back()->with('success', 'Account Group deleted.');
    }
}
