@extends('layout.main')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-lead">
                <div class="panel-heading">
                    <h4><i class="fa fa-bullhorn"> </i> Leads @lang('labels.dashboard')</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Assign Lead</h4>
                            What do you want to give lead today?

                            <br />
                            <div class="form-inline">
                                <select name="data[Lead][lead_assigned_to]" id="LeadLeadAssignedTo"  class="form-control">
                                    <option value="41">CAC - Carsten Christensen</option>
                                    <option value="67">CML - Claus Martin Larsen</option>
                                    <option value="124">IBN - Iben Lykke Neustrup</option>
                                    <option value="127">JAF - Jan Furbo</option>
                                    <option value="118">JEK - Jesper Kristensen</option>
                                    <option value="44">KAT - Kasper Thomsen</option>
                                    <option value="134">KRS - Kristian Søltoft</option>
                                    <option value="146">LSP - Lukas Spanggaard</option>
                                    <option value="34">Michael - Michael Sørensen</option>
                                    <option value="145">MJL - Mads Jeppesen Larsen</option>
                                    <option value="114">MSO - Michael Sørensen</option>
                                    <option value="136">NBC - Nicholai Bach</option>
                                    <option value="143">RAI - Rasmus Ib</option>
                                    <option value="106">RME - Rolf M. Eskildsen</option>
                                    <option value="53">THF - Thomas Holst Frederiksen</option>
                                    <option value="140">TLJ - Tommy Lynge Jensen</option>
                                    <option value="39">TSD - Thomas Stokkendal</option>
                                </select>

                                <select name="data[Lead][lead_count]" id="LeadLeadCount" class="form-control" title="Antal">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                </select>

                                <select name="data[Lead][lead_type]" id="LeadLeadType" class="form-control" title="Type">
                                    <option value="0">New</option>
                                    <option value="1">Rotate</option>
                                </select>
                                <input class="btn btn-green form-control" type="submit" value="ASSIGN">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>Status Leads - DK</h4>
                            New: 1024<br />
                            Rotate: 400 <br />
                        </div>
                    </div>
                    <hr />
                    TODO: Leads stat, assign lead, move leads, edit leads, add leads, view leads
                </div>
            </div>
        </div>
    </div>

@stop