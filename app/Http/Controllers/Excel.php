<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Models\Salary;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\Account;
use App\Models\Trial;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class Excel extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // return Inertia::render('TrialExcel/Index');
        $request->validate([
            'file'=> 'required|mimes:xlsx, xls'
        ]);


        $reader = ReaderEntityFactory::createXLSXReader();
        // $reader->open('trial.xlsx');
        $reader->open($request->file('file'));

        foreach ($reader->getSheetIterator() as $sheet) {
            // only read data from 1st sheet
            if ($sheet->getIndex() === 0) { // index is 0-based
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if($rowIndex === 1) continue; // skip headers row
                    $total_col = count($row->getCells());
                    for($i=0 ; $i <= $total_col-6 ; $i++){
                         $cols[$i]= $row->getCellAtIndex($i)->getValue();
                        //   $col.$i
                    }

                    // $col1 = $row->getCellAtIndex(0)->getValue();
                    // $col2 = $row->getCellAtIndex(1)->getValue();
                    // $col3 = $row->getCellAtIndex(2)->getValue();
                    // $col4 = $row->getCellAtIndex(3)->getValue();
                    // $col5 = $row->getCellAtIndex(4)->getValue();
                    $check_cols = false;
                    foreach($cols as $col){
                        if($col)
                        {
                            $check_cols = true;
                            break;
                        }
                    }
                    if($check_cols)
                    {
                        // dd($cols);

                        //Account Type
                        $acc_type_name = $row->getCellAtIndex(0)->getValue();
                        // dd($acc_type_name);
                        if($acc_type_name){
                            $acc_type = AccountType::where('name', $acc_type_name)->first();
                        }

                        //fgn_grp_id Its Mean Parent ID
                        $fgn_grp_id;
                        //Account Group
                        $acc_grp_name = $row->getCellAtIndex(1)->getValue();
                        if($acc_grp_name)
                        {
                            $acc_grp_exist = AccountGroup::where('name', $acc_grp_name)->
                                where('company_id', session('company_id'))->
                                first();
                            if(!$acc_grp_exist)
                            {
                                $acc_grp = AccountGroup::create([
                                    'type_id' => $acc_type->id,
                                    'parent_id' => null,
                                    'name' => $acc_grp_name,
                                    'company_id' => session('company_id'),
                                ]);
                            } else {
                                $acc_grp = $acc_grp_exist;
                            }
                            $fgn_grp_id = $acc_grp->id;
                            $parent[1] = $fgn_grp_id;
                        }
                        for($j= 2 ; $j <= $total_col-8 ; $j++){

                            $acc_sub_grp_name = $row->getCellAtIndex($j)->getValue();
                            if($acc_sub_grp_name)
                            {
                                $acc_sub_grp_exist = AccountGroup::where('name', $acc_sub_grp_name)->
                                    where('parent_id', $acc_grp->id)->
                                    where('company_id', session('company_id'))->
                                    first();
                                if(!$acc_sub_grp_exist)
                                {
                                    $acc_sub_grp = AccountGroup::create([
                                        'type_id' => $acc_type->id,
                                        // 'parent_id' => $acc_grp->id,
                                        'parent_id' => $parent[$j-1],
                                        'name' => $acc_sub_grp_name,
                                        'company_id' => session('company_id'),
                                    ]);
                                } else {
                                    $acc_sub_grp = $acc_sub_grp_exist;
                                }
                                $fgn_grp_id = $acc_sub_grp->id;
                                $parent[$j] = $fgn_grp_id;
                            }
                        }


                        //Accounts
                        $acc_name = $row->getCellAtIndex($total_col-7)->getValue();
                        if($acc_name)
                        {
                            $acc_exist = Account::where('name', $acc_name)->
                                where('group_id', $fgn_grp_id)->
                                where('company_id', session('company_id'))->
                                first();
                            if(!$acc_exist)
                            {
                                $acc = Account::create([
                                    'name' => $acc_name,
                                    'group_id' => $fgn_grp_id,
                                    'company_id' => session('company_id'),
                                ]);
                                $accountGroupforFolder = AccountGroup::find($fgn_grp_id);
                                Storage::makeDirectory('/public/' . session('company_id') .
                                     '/' . session('year_id') . '/execution/' . $accountGroupforFolder->name);
                            } else {
                                $acc = $acc_exist;
                            }

                            //For Trial table ----------------------------------------- START ---------------------------------
                            $opn_debit = $row->getCellAtIndex($total_col-6)->getValue() ? $row->getCellAtIndex($total_col-6)->getValue() : 0;
                            $opn_credit = $row->getCellAtIndex($total_col-5)->getValue() ? $row->getCellAtIndex($total_col-5)->getValue() : 0;

                            $remain_debit = $row->getCellAtIndex($total_col-4)->getValue() ? $row->getCellAtIndex($total_col-4)->getValue() :  0;
                            $remain_credit = $row->getCellAtIndex($total_col-3)->getValue() ? $row->getCellAtIndex($total_col-3)->getValue() : 0;

                            $cls_debit = $row->getCellAtIndex($total_col-2)->getValue() ? $row->getCellAtIndex($total_col-2)->getValue() : 0;
                            $cls_credit = $row->getCellAtIndex($total_col-1)->getValue() ? $row->getCellAtIndex($total_col-1)->getValue() : 0;

                            $trial_exists = Trial::where('company_id', session('company_id'))
                                ->where('account_id', $acc->id)->first();

                            if($trial_exists)
                            {
                                $trial_exists->opn_debit = $opn_debit;
                                $trial_exists->opn_credit = $opn_credit;

                                $trial_exists->remain_debit = $remain_debit;
                                $trial_exists->remain_credit = $remain_credit;

                                $trial_exists->cls_debit = $cls_debit;
                                $trial_exists->cls_credit = $cls_credit;

                                $trial_exists->account_id = $acc->id;
                                $trial_exists->company_id = session('company_id');

                            } else {
                                Trial::create([
                                    'opn_debit' => $opn_debit,
                                    'opn_credit' => $opn_credit,

                                    'remain_debit' => $remain_debit,
                                    'remain_credit' => $remain_credit,

                                    'cls_debit' => $cls_debit,
                                    'cls_credit' => $cls_credit,

                                    'account_id' => $acc->id,
                                    'company_id' => session('company_id'),
                                ]);
                            }
                            //For Trial table ----------------------------------------- START ---------------------------------
                        }
                    }





                }
                break; // no need to read more sheets
            }
            $reader->close();
        }

        return Redirect::route('accounts');
    }


    public function lead(){
        $acc_grps =  AccountGroup::with('accounts','accounts.trials')->where('company_id', session('company_id'))
        ->tree()->get()->toTree()->toArray();
        // dd($acc_grps);
            $spreadsheet = new Spreadsheet();
            foreach($acc_grps as $key =>$acc_grp){
                $this->excel1($acc_grp, $key,$spreadsheet);
            }

    $writer = new Xlsx($spreadsheet);
    $writer->save(storage_path('app/public/' . 'lead.xlsx'));
    return response()->download(storage_path('app/public/'. 'lead.xlsx'));

    //-----------------------------------------------------------------

}



public function excel1($acc_grp, $key, $spreadsheet){
    // $spreadsheet = new Spreadsheet();
//        foreach($acc_grps as $key =>$acc_grp){
            // if($key != 0){
                $worksheet1 = $spreadsheet->createSheet($key);
                $worksheet1->setTitle($acc_grp['name']);
            // }

            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            // $drawing->setDescription('Paid');
            $drawing->setPath('images/logo.png'); /* put your path and image here */
            $drawing->setCoordinates('D1');
            $drawing->setOffsetX(70);
            // $drawing->setRotation(90);
            $drawing->setHeight(200);
            $drawing->setWorksheet($spreadsheet->getSheet($key));
            foreach (range('A', 'G') as $k => $col) {
                $spreadsheet->getSheet($key)->getColumnDimension($col)->setAutoSize(true);

            }

            $spreadsheet->getSheet($key)->getStyle('A11:G30')->getAlignment()->setHorizontal('center');
            $spreadsheet->getSheet($key)->getStyle('B11:B30')->getAlignment()->setHorizontal('left');
            $spreadsheet->getSheet($key)->getStyle('A11:G30')->getAlignment()->setVertical('center');



            $spreadsheet->getSheet($key)->fromArray(['CLIENT:'], NULL, 'A3');
            $spreadsheet->getSheet($key)->fromArray(['MZK CORPORATION'], NULL, 'B3');
            //  $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
            $spreadsheet->getSheet($key)->fromArray(['SUBJECT:'], NULL, 'A4');
            $spreadsheet->getSheet($key)->fromArray([$acc_grp['name']], NULL, 'B4');
            $spreadsheet->getSheet($key)->fromArray(['PERIOD:'], NULL, 'A5');
            $spreadsheet->getSheet($key)->fromArray(['23-8-2022'], NULL, 'B5');
            $spreadsheet->getSheet($key)->fromArray(['PREPARED BY:'], NULL, 'A6');
            $spreadsheet->getSheet($key)->fromArray([auth()->user()->name], NULL, 'B6');
            $spreadsheet->getSheet($key)->fromArray(['REVIEWED BY:'], NULL, 'A7');
            // $spreadsheet->getSheet($key)->fromArray([], NULL, 'B');
            $spreadsheet->getSheet($key)->fromArray(['MK'], NULL, 'B7');
            $spreadsheet->getSheet($key)->fromArray(['REVIEWED BY:'], NULL, 'A8');
            $spreadsheet->getSheet($key)->fromArray(['ASAD'], NULL, 'B8');
            $spreadsheet->getSheet($key)->fromArray(['LEAD SCHEDULE'], NULL, 'B9');
            $spreadsheet->getSheet($key)->fromArray([''], NULL, 'B10');
            $spreadsheet->getSheet($key)->fromArray(['S.NO'], NULL, 'A11');
            $spreadsheet->getSheet($key)->fromArray(['PARTICULARS'], NULL, 'B11');
            $spreadsheet->getSheet($key)->fromArray(['REFS'], NULL, 'C11');
            $spreadsheet->getSheet($key)->fromArray(['ENDING YEAR'], NULL, 'D11');
            $spreadsheet->getSheet($key)->fromArray(['BEG YEAR'], NULL, 'E11');
            $spreadsheet->getSheet($key)->fromArray(['DIFFERENCE'], NULL, 'F11');
            $spreadsheet->getSheet($key)->fromArray(['%'], NULL, 'G11');
            $spreadsheet->getSheet($key)->getStyle('A11:G11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);
            $spreadsheet->getSheet($key)->getStyle('A12:G12')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);
            $j = 13;
            $open = $clos = $diff = 0;
            foreach($acc_grp['children'] as $k => $children){
            $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);
                $spreadsheet->getSheet($key)->fromArray([$k+1], NULL, 'A'.$j);
                $spreadsheet->getSheet($key)->fromArray([$children['name']], NULL, 'B'.$j);
                $spreadsheet->getSheet($key)->fromArray([$children['path']], NULL, 'C'.$j);
                $this->opn=0;
                $this->cls=0;
                $this->dif=0;
                $this->acc_sum($children);
                $spreadsheet->getSheet($key)->fromArray([$this->cls], NULL, 'D'.$j);
                $spreadsheet->getSheet($key)->fromArray([$this->opn], NULL, 'E'.$j);
                $spreadsheet->getSheet($key)->fromArray([$this->cls - $this->opn], NULL, 'F'.$j);
                $div = $this->opn == 0 ? 1 : $this->opn;
                $res = ($this->cls/$div)*100;
                $spreadsheet->getSheet($key)->fromArray([round($res, 2) . '%'], NULL, 'G'.$j);
                $open += $this->opn;
                $clos += $this->cls;
                $diff += $this->dif;


            // $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_DOUBLE);
            // $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getRight()->setBorderStyle(Border::BORDER_DOUBLE);
            // $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
                $j++;
            }
            $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);

            // $j++;
            $spreadsheet->getSheet($key)->fromArray(['TOTAL'], NULL, 'B'.$j);
            $spreadsheet->getSheet($key)->fromArray([$clos], NULL, 'D'.$j);
            $spreadsheet->getSheet($key)->fromArray([$open], NULL, 'E'.$j);
            $spreadsheet->getSheet($key)->fromArray([$clos - $open], NULL, 'F'.$j);
            $divi = $open == 0 ? 1 : $open;
            $resu = ($clos/$divi)*100;
            $spreadsheet->getSheet($key)->fromArray([round($resu, 2) . '%'], NULL, 'G'.$j);

            $acc_cls = $acc_opn = 0;
            foreach($acc_grp['accounts'] as $k => $acc)
            // foreach($acc_grp['children'] as $k => $children)
            {
            $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);

                // foreach($children['accounts'] as $acc)
                // {
                    $spreadsheet->getSheet($key)->fromArray([$k+1], NULL, 'A'.$j);
                    $spreadsheet->getSheet($key)->fromArray([$acc['name']], NULL, 'B'.$j);
                    $spreadsheet->getSheet($key)->fromArray([$acc['number']], NULL, 'C'.$j);
                    // $this->sum=0;
                    // $this->acc_sum($children);
                    $spreadsheet->getSheet($key)->fromArray([abs($acc['trials']['cls_credit'] - $acc['trials']['cls_debit'])], NULL, 'E'.$j);
                    $spreadsheet->getSheet($key)->fromArray([abs($acc['trials']['opn_credit'] - $acc['trials']['opn_debit'])], NULL, 'F'.$j);
                    // $spreadsheet->getSheet($key)->fromArray(['0'], NULL, 'G'.$j);
                    // $spreadsheet->getSheet($key)->fromArray(['0'], NULL, 'H'.$j);
                    $j++;
                // }
                $acc_cls += abs($acc['trials']['cls_credit'] - $acc['trials']['cls_debit']);
                $acc_opn += abs($acc['trials']['opn_credit'] - $acc['trials']['opn_debit']);



            }
            $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);

            $j++;
            $spreadsheet->getSheet($key)->getStyle('A'.$j.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);


            $spreadsheet->getSheet($key)->fromArray(['TOTAL'], NULL, 'B'.$j);
            $spreadsheet->getSheet($key)->fromArray([$acc_cls], NULL, 'D'.$j);
            $spreadsheet->getSheet($key)->fromArray([$acc_opn], NULL, 'E'.$j);

            foreach($acc_grp['children'] as $k => $children){
	            $this->excel1($children, $k,$spreadsheet);
	    }


    }

    public $opn=0;
    public $cls=0;
    public $dif=0;
    public function acc_sum($acc_grp)
    {
        if(count($acc_grp['accounts']) >> 0)
        {
            foreach($acc_grp['accounts'] as $acc)
            {
                $this->opn += abs($acc['trials']['opn_debit'] - $acc['trials']['opn_credit']);
                $this->cls  += abs($acc['trials']['cls_debit'] - $acc['trials']['cls_credit']);
                // $this->dif += $this->cls - $this->opn;
            }

        }
        if(count($acc_grp) >> 0)
        {
            foreach($acc_grp['children'] as $k => $children)
            {
                $this->acc_sum($children);
            }

        }
        return;
    }


}
