@extends('layout.main')


@section('styles')
    {!! Html::style('css/dataTables.css') !!}
    {!! Html::style('css/dataTables.editor.css') !!}
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/tabletools/2.2.4/css/dataTables.tableTools.css">
@stop

@section('scripts')

    <script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/tabletools/2.2.4/js/dataTables.tableTools.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="{{asset('/js/lib/dataTables.editor.min.js')}}"></script>

    <script>
        (function($){

            $(document).ready(function() {
                var editor = new $.fn.dataTable.Editor( {
                    'ajax' : {
                        url:   '/invoices/updateDraftLine',
                        data : {
                         //_token : $('#token1').val()
                        }
                    },
                    "table": "#ddd",
                    i18n: {
                        create: {
                            button: Lang.get('labels.create'),
                            title:  Lang.get('labels.create'),
                            submit: Lang.get('labels.create')
                        }
                    },

                    "fields": [
                        {
                            "label": Lang.get('labels.description'),
                            "name": "Description"
                        },
                        {
                            "label": Lang.get('labels.quantity'),
                            "name": "Quantity"
                        },
                        {
                            "label": Lang.get('labels.unit-price'),
                            "name": "UnitPrice"
                        },
                        {
                            "label": Lang.get('labels.net-amount'),
                            "name": "NetAmount"
                        },
                        {
                            "label": Lang.get('labels.seller'),
                            "name": "User",
                            type:  "select",
                            options: <?php echo $userSelect ?>
                        }
                    ]
                } );


                editor.on( 'preSubmit', function ( e, o, action ) {
                    if ( action !== 'remove' ) {
                        if ( o.data.Description === '' ) {
                            this.error('Description', 'required');
                            return false;
                        }
                    else if ( o.data.Quantity ==='')  {
                            this.error('Quantity', 'type number');
                            return false;
                        }
                        else if ( o.data.UnitPrice ==='') {
                            this.error('Quantity', 'type number');
                            return false;
                        }
                        else if ( o.data.NetAmount ==='' ) {
                            this.error('Quantity', 'type number');
                            return false;
                        }

                        // ... etc
                    }
                } );


                $('#ddd').DataTable( {
                    "dom": "Tfrtip",
                    "columns": [
                        {
                            "data": "Description"
                        },
                        {
                            "data": "Quantity"
                        },
                        {
                            "data": "UnitPrice"
                        },
                        {
                            "data": "NetAmount"
                        },
                        {
                            "data":"User"
                        }
                    ],
                    "tableTools": {
                        "sRowSelect": "os",
                        "aButtons": [
                            { "sExtends": "editor_create", "editor": editor },
                            { "sExtends": "editor_edit",   "editor": editor },
                            { "sExtends": "editor_remove", "editor": editor }
                        ]
                    }
                } );
            } );

        }(jQuery));

       function  isNumeric( obj ) {
            return !jQuery.isArray( obj ) && (obj - parseFloat( obj ) + 1) >= 0;
        }
    </script>

@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="panel-primary">
            <div class="panel-body">
                {{$draft->Name}} <br/>
                {{$draft->Address}} <br/>
                {{$draft->City}} <br/>
                {{$draft->ZipCode}} <br/>s
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="panel-primary">
            <div class="panel-heading">
                <h4>Form Tables Editing</h4>
                <div class="options">
                    <a href="javascript:;"><i class="fa fa-cog"></i></a>
                    <a href="javascript:;"><i class="fa fa-wrench"></i></a>
                    <a href="javascript:;" class="panel-collapse"><i class="fa fa-chevron-down"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <table class="table" id="ddd">
                    <thead>
                    <tr>
                        <th>@lang('labels.description')</th>
                        <th>@lang('labels.quantity')</th>
                        <th>@lang('labels.unit-price')</th>
                        <th>@lang('labels.total')</th>
                        <th>@lang('labels.seller')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($draft->DraftLine as $line)
                        <tr id="{{$line->Id}}">
                            <td>
                                {{$line->Description}}
                            </td>

                            <td>
                                {{$line->Quantity}}
                            </td>

                            <td>
                                {{$line->UnitPrice}}
                            </td>

                            <td>
                                {{$line->NetAmount}}
                            </td>
                            <td>
                                @if($line->User){{$line->User->UserName}}@endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
@stop
