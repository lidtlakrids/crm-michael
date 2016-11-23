@extends('layout.main')
@section('page-title', 'White Lists')
@section('styles')
@stop
@section('scripts')
    @stop
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <i class="fa fa-list-ul"></i> White list info
                    <div class="options">
                        <a href="{{url('white-lists/edit',$whiteList->Id)}}"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-condensed">
                                    <h3><strong>White list {{ $whiteList->Id }}</strong></h3>
                                    <p class="hidden" id="whiteListId">{{$whiteList->Id}}</p>
                                    <tbody>
                                    <tr>
                                        <td>Created</td>
                                        <td>
                                            @if($whiteList->Created != null)
                                                {{date('d-m-Y',strtotime($whiteList->Created))}}
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Modified</td>
                                        <td>
                                            @if($whiteList->Modified != null)
                                                {{date('d-m-Y',strtotime($whiteList->Modified))}}
                                            @else
                                                --
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>IP Address</td>
                                        <td><a target='_blank' href='http://www.ip-tracker.org/locator/ip-lookup.php?ip{{$whiteList->ipaddress}}'>{{$whiteList->ipaddress}}</a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>Permanent</td>
                                        <td>
                                            @if($whiteList->Permanent)
                                                Yes
                                            @else
                                                No
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Active</td>
                                        <td>
                                            @if($whiteList->Active)
                                                Yes
                                            @else
                                                No
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Nearest relatives</td>
                                        <td>
                                            {{$whiteList->User->FullName or '--'}}
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop