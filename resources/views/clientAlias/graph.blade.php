@extends('layout.main')
@section('page-title','Client Stats')
{{--
@section('scripts')
    @include('scripts.dataTablesScripts')
--}}
@section('content')


    <link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

    <script>

        $(document).ready(function () {

            var data = $('table#stat tbody tr').map(function (index) {
                var cols = $(this).find('td');
                return {

                    month: cols[0].innerHTML,
                    age: (cols[1].innerHTML + '') * 1 // parse int
                    //  grade: (cols[2].innerHTML + '') * 1 // parse int
                };
            }).get();
            alert(JSON.stringify(data));
            var xyz = JSON.stringify(data);
            //  alert(xyz);

            Morris.Area({
                element: 'area-new-table',
                data: $.parseJSON(xyz),
                xkey: 'month',
                ykeys: ['age'],
                labels: ['Name']

            });

        });

    </script>

    <?php
        $data = json_decode('http://gcmdev.dk/api/$metadata#Orders(Id,Created,ConfirmedDate,ClientAlias,OrderType,User,ClientAlias(Id,PhoneNumber,Name),OrderType(FormName),User(UserName))');
            print_r($data);


    ?>

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4>Line Graph</h4>
                    <div class="options">
                    </div>
                </div>
                <div class="panel-body">



                    <div id="area-example"></div>
                    <div id="area-new-table"></div>
                    <table id="stat">
                        <thead>
                        <tr>
                            <th style="padding-right:100px">Type</th>
                            <th>Jan</th>
                            <th>Feb</th>
                            <th>Mar</th>
                            <th>Apr</th>
                            <th>May</th>
                            <th>Jun</th>
                            <th>Jul</th>
                            <th>Aug</th>
                            <th>Sep</th>
                            <th>Oct</th>
                            <th>Nov</th>
                            <th>Dec</th>
                        </tr>
                        </thead>
                        <tbody>

                        <tr class="lost-clients" style="color: red;">
                            <th>Lost Clients</th>
                            <td>-15</td>
                            <td>-10</td>
                            <td>-18</td>
                            <td>-25</td>
                            <td>-23</td>
                            <td>-39</td>
                            <td>-21</td>
                            <td>-8</td>
                            <td>-10</td>
                            <td>-3</td>
                            <td>-5</td>
                            <td>-11</td>
                        </tr>

                        <tr class="new-clients" style="color: green;">
                            <th>New Clients</th>
                            <td>25</td>
                            <td>20</td>
                            <td>15</td>
                            <td>25</td>
                            <td>23</td>
                            <td>139</td>
                            <td>21</td>
                            <td>18</td>
                            <td>12</td>
                            <td>13</td>
                            <td>15</td>
                            <td>12</td>
                        </tr>

                        <tr class="total-clients" style="font-weight: bold;">
                            <th>Total</th>
                            <td>10</td>
                            <td>10</td>
                            <td>-3</td>
                            <td>0</td>
                            <td>0</td>
                            <td>100</td>
                            <td>0</td>
                            <td>2</td>
                            <td>10</td>
                            <td>10</td>
                            <td>10</td>
                            <td>1</td>
                        </tr>
                        </tbody>
                        <caption>Monthly stat of Clients </caption>
                    </table>


                </div>
            </div>
        </div>
    </div>
@stop