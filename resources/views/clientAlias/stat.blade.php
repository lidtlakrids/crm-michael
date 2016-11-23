@extends('layout.main')
@section('page-title','Client Stats')
{{--
@section('scripts')
    @include('scripts.dataTablesScripts')
--}}
@section('content')


    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <link href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css" rel="stylesheet" />
    <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-apple"></i> Client Stat</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <p>
                        TODO: Lost Clients, New Clients, New contracts, Lost Contracts, all by months / year / Graphs / Value
                    <hr />
                    </p>
                   TODO: SELECT DATE/YEAR (Range selector)
                    <div class="table-responsive">
                        <table class="table" data-graph-container-before="1" data-graph-type="column">
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
                                <td>28</td>
                                <td>40</td>
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
                                <td>1</td>
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
    </div>


    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4>Line Graph</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="morris-line-chart" style="height: 250px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        Morris.Line({
            element: 'morris-line-chart',
            data: [{
                m: '2015-01', // <-- valid timestamp strings
                a: 15,
                b: 25
            }, {
                m: '2015-02',
                a: 10,
                b: 20
            }, {
                m: '2015-03',
                a: 18,
                b: 15
            }, {
                m: '2015-04',
                a: 25,
                b: 25
            }, {
                m: '2015-05',
                a: 23,
                b: 28
            }, {
                m: '2015-06',
                a: 39,
                b: 40
            }, {
                m: '2015-07',
                a: 21,
                b: 21
            }, {
                m: '2015-08',
                a: 8,
                b: 18
            }, {
                m: '2015-09',
                a: 10,
                b: 12
            }, {
                m: '2015-10',
                a: 3,
                b: 13
            }, {
                m: '2015-11',
                a: 5,
                b: 15
            }, {
                m: '2015-12',
                a: 11,
                b: 12
            }, ],
            xkey: 'm',
            ykeys: ['a', 'b'],
            labels: ['Lost Clients', 'New Clients'],
            lineColors:['red','green'],
            xLabelFormat: function(x) { // <--- x.getMonth() returns valid index
                var month = months[x.getMonth()];
                return month;
            },
            dateFormat: function(x) {
                var month = months[new Date(x).getMonth()];
                return month;
            },
        });
    </script>
    @stop
{{--
http://codepen.io/andreic/pen/CJoze
http://jsbin.com/ENiCaHIv/3/edit?html,js,output
--}}