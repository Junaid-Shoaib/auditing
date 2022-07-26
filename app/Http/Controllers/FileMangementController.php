<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\FileManager;
use App\Models\Company;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FileMangementController extends Controller
{
    public function filing($parent_name_id)
    {
        //condition to deal with url parameter- it will be name if hiting the link from dashboard otherwise it will be id
        if($parent_name_id == 'planing' || $parent_name_id == 'execution' || $parent_name_id == 'completion')
        {
            $parent = FileManager::all()->where('company_id', session('company_id'))
                ->where('year_id', session('year_id'))
                ->where('name', $parent_name_id)
                ->map(function ($obj) {
                    return [
                        'id' => $obj->id,
                        'name' => ucfirst($obj->name),
                        'is_folder' => $obj->is_folder,
                        'parent_id' => $obj->parent_id,
                        'type' => $obj->name == 'execution' ? 'Folder' : 'File',
                    ];
                })
                ->first();
        } else {
            $parent = FileManager::all()->where('company_id', session('company_id'))
                ->where('year_id', session('year_id'))
                ->where('id', $parent_name_id)
                ->map(function ($obj) {
                    return [
                        'id' => $obj->id,
                        'name' => ucfirst($obj->name),
                        'is_folder' => $obj->is_folder,
                        'parent_id' => $obj->parent_id,
                        'type' => $obj->name == 'execution' ? 'Folder' : 'File',
                    ];
                })
                ->first();
        }

        //if get parent then we can show their childrens otherwise we can't track folders or file
        if($parent)
        {
            //Validating request
            request()->validate([
                'direction' => ['in:asc,desc'],
                'field' => ['in:name,email']
            ]);

            //Searching request
            $query = FileManager::query();
            if (request('search')) {
                $query->where('name', 'LIKE', '%' . request('search') . '%');
            }

            $balances = $query
                ->where('company_id', session('company_id'))
                ->where('year_id', session('year_id'))
                ->where('parent_id', $parent['id'])
                ->paginate(10)
                ->through(
                    function ($obj) {
                        return
                            [
                                'id' => $obj->id,
                                'name' => $obj->name,
                                'is_folder' => $obj->is_folder,
                                'parent_id' => $obj->parent_id,
                                // 'delete' => Entry::where('account_id', $account->id)->first() ? false : true,
                            ];
                    }
                );

            $first = FileManager::where('company_id', session('company_id'))
                ->where('year_id', session('year_id'))
                ->where('parent_id', $parent['id'])
                ->first();

            return Inertia::render('Filing/Index', [
                'balances' => $balances,
                'first' => $first,
                'company' => Company::where('id', session('company_id'))->first(),
                'companies' => Auth::user()->companies,
                'parent' => $parent,
            ]);
        } else {
            return Redirect::route('trial.index')->with('warning', 'Please upload Excel to generate Accounts and Account Groups.');
        }
    }

    public function createFolder()
    {
        return Inertia::render('Filing/CreateFolder');
    }

    public function storeFolder()
    {
        Request::validate([
            'name' => ['required'],
        ]);

        $parent = FileManager::where('company_id', session('company_id'))
            ->where('year_id', session('year_id'))
            ->where('name', 'execution')
            ->first();

        $folderObj = FileManager::create([
            // 'name' => strtoupper(Request::input('name')),   //FOLDER
            'name' => ucfirst(strtolower(Request::input('name'))),  //Folder
            'is_folder' => 0,
            'parent_id' => $parent->id,
            'year_id' => session('year_id'),
            'company_id' => session('company_id'),
        ]);
        Storage::makeDirectory('/public/' . $folderObj->company_id . '/' . $folderObj->year_id . '/' . $folderObj->parent_id . '/' . $folderObj->id);
        //sending parameter value "execution" because we can only create folder/directories in Executino folder that's why redirecting their
        return Redirect::route("filing", ["execution"])->with('success', 'Folder created.');
    }

    public function uploadFile($folder_id)
    {
        $parent = FileManager::find($folder_id);
        return Inertia::render('Filing/UploadFile', [
            'parent' => $parent,
        ]);
    }

    public function storeFile(Request $request, $parent_id)
    {
        Request::validate([
            'avatar'=> ['required'],
        ]);

        $parent = FileManager::find($parent_id);
        $grand_parent = FileManager::where('id', $parent->parent_id)->first();
        if($grand_parent)
        {
            $path = session('company_id') . '/' . session('year_id') . '/' . $grand_parent->id . '/' . $parent_id;
        } else {
            $path = session('company_id') . '/' . session('year_id') . '/' . $parent_id;
        }
        $name = time() . '_' . Request::file('avatar')->getClientOriginalName();

        $pathWithFileName = Request::file('avatar')->storeAs($path, $name, 'public');

        $folderObj = FileManager::create([
            'name' => $name,
            'is_folder' => 1,
            'parent_id' => $parent_id,
            'year_id' => session('year_id'),
            'company_id' => session('company_id'),
        ]);
        //sending parameter value "$parent->id" because we have to show the folder where we upload the file
        return Redirect::route("filing", [$parent->id])->with('success', 'File upload.');
    }

    public function downloadFile($file_id)
    {
        $file_obj = FileManager::find($file_id);
        $file_parent_obj = FileManager::find($file_obj->parent_id);
        $file_grand_parent_obj = FileManager::find($file_parent_obj->parent_id);
        if($file_grand_parent_obj)
        {
            $path = public_path() . '/storage/' . session('company_id') . '/' . session('year_id') . '/' . $file_grand_parent_obj->id . '/' . $file_parent_obj->id . '/' . $file_obj->name;

        } else {
            $path = public_path() . '/storage/' . session('company_id') . '/' . session('year_id') . '/' . $file_parent_obj->id . '/' . $file_obj->name;
        }
        // return response()->download($path);
        // if (File::isFile($path))
        // {
        //     $file = File::get($path);
        //     $response = Response::make($file, 200);
        //     $response->header('Content-Type', 'application/pdf');
        //     return Response::download($file, $file_obj->name);
        //     return $response;
        // }
        // $filePath = public_path($path);
        return Response::download($path);
        // return Response::download($filePath);
    }

}
