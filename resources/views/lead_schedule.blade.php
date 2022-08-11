<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Lead Schedule Report</title>
</head>

<body>

    <?php
    $dt = \Carbon\Carbon::now(new DateTimeZone('Asia/Karachi'))->format('M d, Y - h:m a');
    $amt = new NumberFormatter('en_GB', NumberFormatter::SPELLOUT);
    ?>
<div style="position: relative">
    <table style="width: 50%; float: left;">
        <tr>
            <td style="width:15%; font-size:16px;">
                Client.
            </td>
            <td style="width:35%; font-size:15px; font-weight: bold; border-bottom: 1px solid black;">
                {{-- {{ $file[0]['bond_no'] }} --}}
            </td>
        </tr>
        <tr>
            <td style="width:15%;font-size:15px;">
                Subject.
            </td>
            <td style="width:35%; font-size:15px; font-weight: bold; border-bottom: 1px solid black;">
                {{-- {{ $file[0]['date']}} --}}
            </td>
        </tr>
        <tr>
            <td style="width:15%; font-size:15px;">
                Period.
            </td>
            <td style="width:35%; font-size:15px; font-weight: bold; border-bottom: 1px solid black;">
                {{-- {{ $file[0]['igm_no'] }} --}}
            </td>
        </tr>
        <tr>
            <td style="width:15%; font-size:15px;">
                Prepared By.
            </td>
            <td style="width:35%; font-size:15px; font-weight: bold; border-bottom: 1px solid black;">
                {{ auth()->user()->name}}
            </td>
        </tr>
        <tr>
            <td style="width:15%; font-size:15px;">
                Reviewed By.
            </td>
            <td style="width:35%; font-size:15px; font-weight: bold; border-bottom: 1px solid black;">
                {{-- {{ auth()->user()->name}} --}}
            </td>
        </tr>
        <tr>
            <td style="width:15%; font-size:15px;">
                Reviewed By.
            </td>
            <td style="width:35%; font-size:15px; font-weight: bold; border-bottom: 1px solid black;">
                {{-- {{ auth()->user()->name}} --}}
            </td>
        </tr>
        <tr>
            <td style="width:15%; font-size:15px;">

            </td>
            <td style="width:35%; font-size:15px; font-weight: bold;">
             LEAD SCHEDULE
            </td>
        </tr>
    </table>
    <div style="width: 50%; text-align: center; float:right; ">
    <p style="font-family: Times New Roman; font-size:50px; color:#0C4866">B K R</p>
    {{-- <p style="font-family: Arial; font-size:15px; font-weight:bold; color:#0C4866">I N T E R N A T I O N A L</p> --}}

    </div>
</div>

<div class="information" style="margin-top: 140px">
    <br />
    <table width="100%" style="border-collapse: collapse; border: 1px solid black;">
        <thead Style="background-color: #a8b3b8;">
            <tr>
                <th
                    style="width: 7%;  font-size: 15px; border-collapse: collapse;  border: 1px solid black; padding: 15px 0px 15px 0px; ">
                    <strong>S.No</strong>
                </th>

                <th
                    style="width: 30%;   font-size: 15px; border-collapse: collapse;  border: 1px solid black; padding: 15px 0px 15px 0px; ">
                    <strong>Particular</strong>
                </th>
                <th
                    style="width: 10%; solid black;    font-size: 15px; border: 1px solid black; padding: 15px 0px 15px 0px;">
                    <strong>Refs </strong>
                </th>
                <th
                    style="width: 15%; border-collapse: collapse;   font-size: 15px;  border: 1px solid black; padding: 15px 0px 15px 0px;">
                    <strong>Ending year</strong>
                </th>
                <th
                    style="width: 15%; border-collapse: collapse;  font-size: 15px;  border: 1px solid black; padding: 15px 0px 15px 0px;">
                    <strong>Begining</strong>
                </th>
                <th
                    style="width: 15%;border:2px; solid black;    font-size: 15px; border: 1px solid black; padding: 15px 0px 15px 0px;">
                    <strong>Diffrence</strong>
                </th>

                <th
                    style="width: 8%; solid black;   font-size: 15px;  border: 1px solid black; padding: 15px 0px 15px 0px; ">
                    <strong>%</strong>
                </th>
            </tr>

        </thead>

        <tbody>

            <?php $number = 1;

            ?>



            {{-- {{ $balance = 0; }} --}}
            {{-- @foreach ($data as $item) --}}
                {{-- {{ $balance += $item['qty']; }} --}}

            <tr style="">
                <td style="width: 7%; font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px solid black;
                padding: 15px 0px 15px 0px;">
                    {{ $number }}
                </td>
                <td style="width: 25%; font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px solid black;
        padding: 15px 0px 15px 0px; ">
        Ijaraha (lease) Asset.
                    {{-- {{ $item['date'] }} --}}
                </td>

                <td style="width: 10%; font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px solid black;
        padding: 15px 0px 15px 0px; ">
                    {{-- {{ $item['item'] }} --}}
                A2
                </td>

                <td style="width: %15; font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px solid black;
        padding: 15px 0px 15px 0px; ">
                    {{-- {{ $item['qty'] }} --}}
               2,440,975,610
                </td>


                <td style="width: 15%;font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px solid black;
        padding: 15px 0px 15px 0px; ">
                    2,725,212,037
                  {{-- {{ $item['t_qty'] - $balance }} --}}
                </td>
                <td style="width: 15%; font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px solid black;
        padding: 15px 0px 15px 0px; ">
                    (284,236,427){{-- {{ 'sign' }} --}}
                </td>
                <td style="width: 8%; font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px solid black;
            padding: 15px 0px 15px 0px;">
                    -10%    {{-- {{  $item['vehicle_no']  }} --}}
                </td>

                <?php $number++; ?>
            </tr>
             {{-- @endforeach --}}

             <tr style="">
                <td style="width: 7%; font-size: 12px; text-align: center; border-collapse: collapse;  border: 1px double black ">

                </td>
                <td style="width: 25%; font-size: 15px; font-weight:bold; text-align: center; border-collapse: collapse; border: 1px double black">
        Total
                    {{-- {{ $item['date'] }} --}}
                </td>

                <td style="width: 10%; font-size: 12px; text-align: center;  font-weight:bold; border-collapse: collapse;  border: 1px double black;
        padding: 15px 0px 15px 0px; ">
                    {{-- {{ $item['item'] }} --}}

                </td>

                <td style="width: %15; font-size: 12px; text-align: center;  font-weight:bold; border-collapse: collapse;  border: 1px double black;
        padding: 15px 0px 15px 0px; ">
                    {{-- {{ $item['qty'] }} --}}
               2,440,975,610
                </td>


                <td style="width: 15%;font-size: 12px; text-align: center; font-weight:bold; border-collapse: collapse;  border: 1px double black;
        padding: 15px 0px 15px 0px; ">
                    2,725,212,037
                  {{-- {{ $item['t_qty'] - $balance }} --}}
                </td>
                <td style="width: 15%; font-size: 12px; text-align: center;  font-weight:bold; border-collapse: collapse;  border: 1px double black;
        padding: 15px 0px 15px 0px; ">
                    (284,236,427){{-- {{ 'sign' }} --}}
                </td>
                <td style="width: 8%; font-size: 12px; text-align: center;  font-weight:bold; border-collapse: collapse;  border: 1px double black;
            padding: 15px 0px 15px 0px;">
                    -10%    {{-- {{  $item['vehicle_no']  }} --}}
                </td>

                <?php $number++; ?>
            </tr>

        </tbody>


    </table>
</div>

</body>

</html>
