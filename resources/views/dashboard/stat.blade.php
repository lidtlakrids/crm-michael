@extends('layout.main')

@section('content')


    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-shield"> </i>  STAT</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <h3>Hello {{ Auth::user()->name }}</h3>
                    What do you want to stat today?

                    <hr />
                    new sales, resales, renvoiced, goals, reminders, debt collection. calls, leads, pipeline, paid, commison, product sold, coustomers lost, new coustomer, contract reinvoice ammount


                </div>
            </div>
        </div>
    </div>
@stop