@extends('layout.main')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-apple"></i> Bogholderi</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    TODO: REGISTER PAYMENTS, MISSING INVOICE, Reminders, Inkasso, Sallery, ovedue invoices, recurring invoices, approved orders. approv order
                    <hr />
                </div>
            </div>
        </div>
    </div>

    <hr />

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-check-circle"></i> Registrer Betalinger</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="panel">

                            <div class="col-xs-2">
                                <label class="">Upload File</label>
                            </div>
                            <div class="col-xs-8">
                                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                    <div class="form-control" data-trigger="fileinput">
                                        <i class="glyphicon glyphicon-file fileinput-exists"></i>
                                        <span class="fileinput-filename"></span>
                                    </div>
                                    <div class="input-group-btn btn-file">
                                        <span class="fileinput-new btn btn-default">Select file</span>
                                        <span class="fileinput-exists btn btn-default">Change</span>
                                        <input type="file">
                                        <a href="#" class="btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                    </div>
                                </div>

                            </div>
                            <div class="col-xs-2">
                               <input type="button" class="form-control btn-primary" value="Upload" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                           <div class="panel">
                                <div class="table-responsive"  style="margin-top:20px; border-top: solid 1px #ccc;">
                                    <table class="table-condensed table-hover" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Invoice</th>
                                                <th>Debitor</th>
                                                <th>Contract</th>
                                                <th>Text</th>
                                                <th>Paid</th>
                                                <th>Amount</th>
                                                <th>Options</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="checkbox"></td>
                                                <td>11.02.2016</td>
                                                <td><a href="#" title="View Invoice" target="_blank">20546</a></td>
                                                <td><a href="#" title="View Debitor" target="_blank">mirapass</a></td>
                                                <td><a href="#" title="View Contract" target="_blank">12345</a></td>
                                                <td>n/a</td>
                                                <td>no</td>
                                                <td>4.500 / 4.500</td>
                                                <td> <input type="button" class="form-control btn-green btn-xs" value="PAID" /></td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox"></td>
                                                <td>11.02.2016</td>
                                                <td><a href="#" title="View Invoice" target="_blank">n/a</a></td>
                                                <td><a href="#" title="View Debitor" target="_blank">Di&M</a></td>
                                                <td><a href="#" title="View Contract" target="_blank">n/a</a></td>
                                                <td><a data-toggle="modal" href="#findInvoiceModal" title="Søg efter faktura">Faktura 20643</a></td> <!-- launch modal window to make wildcards search on text from bank to find invoice, then assign the invoice to the contract -->
                                                <td>no</td>
                                                <td>40.500 / 40.500</td>
                                                <td><input type="button" class="form-control btn-green btn-xs" value="PAID" /></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="7">I alt:</th>
                                                <th  style="">45.000 kr.</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                           </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <input type="button" class="form-control btn-green btn-md" value="SET ALL PAID" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="findInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title"><i class="fa fa-search"></i> Find Faktura / Kontrakt</h4>
                </div>
                <div class="modal-body">
                    <p>
                    <form action="#">
                        <div class="input-group well">
                            <input type="text" value="Faktura 20643" class="form-control"><!-- The text from payments table -->
		                    <span class="input-group-btn">
		                        <button type="button" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
		                     </span>
                        </div>
                    </form>

                    </p>
                    <p>

                    <h4>Displaying results for 'Faktura 20643'</h4>
                    <hr />

                    <input type="radio" value="20643" name="select-invoice" /> <a href="" title="Se Invoice" target="_blank">Faktura 20643 - Di&M - 40.500 kr. </a>
                    <hr />
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Vælg</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    <hr />



<!--
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-grape">
                <div class="panel-heading">
                    <h4><i class="fa fa-apple"></i> Bogholderi - Missing Payments</h4>
                    <div class="info-bar"></div>
                    <div class="options">
                        <a href="#" title=""><i class="fa fa-cog"></i></a>
                        <a href="javascript:;" title="Refresh"><i class="fa fa-refresh"></i></a>
                    </div>
                </div>
                <div class="panel-body">


                    <div class="col-md-2">
                        <a class="info-tiles tiles-success" href="#">
                            <div class="tiles-heading">
                                <div class="pull-left">Not Overdue</div>
                                <div class="pull-right"></div>
                            </div>
                            <div class="tiles-body">
                                <div class="pull-left"></div>
                                <div class="pull-right">370.000</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a class="info-tiles tiles-primary" href="#">
                            <div class="tiles-heading">
                                <div class="pull-left">< 10 days</div>
                                <div class="pull-right"></div>
                            </div>
                            <div class="tiles-body">
                                <div class="pull-left"></div>
                                <div class="pull-right">270.000</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a class="info-tiles tiles-warning" href="#">
                            <div class="tiles-heading">
                                <div class="pull-left">< 20 days</div>
                                <div class="pull-right"></div>
                            </div>
                            <div class="tiles-body">
                                <div class="pull-left"></div>
                                <div class="pull-right">150.000</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a class="info-tiles tiles-orange" href="#">
                            <div class="tiles-heading">
                                <div class="pull-left">< 30 days</div>
                                <div class="pull-right"></div>
                            </div>
                            <div class="tiles-body">
                                <div class="pull-left"></div>
                                <div class="pull-right">70.000</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a class="info-tiles tiles-magenta" href="#">
                            <div class="tiles-heading">
                                <div class="pull-left">< 60 days</div>
                                <div class="pull-right"></div>
                            </div>
                            <div class="tiles-body">
                                <div class="pull-left"></div>
                                <div class="pull-right">30.000</div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-2">
                        <a class="info-tiles tiles-danger" href="#">
                            <div class="tiles-heading">
                                <div class="pull-left">+ 60 days</div>
                                <div class="pull-right"></div>
                            </div>
                            <div class="tiles-body">
                                <div class="pull-left"></div>
                                <div class="pull-right">110.000</div>
                            </div>
                        </a>
                    </div>


                    <div class="clearfix"></div>



                    <div id="accordioninpanel" class="accordion-group">
                        <div class="accordion-item">
                            <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapseinOne"><h4>Collapsible Group Item #1</h4></a>
                            <div id="collapseinOne" class="collapse" style="height: 0px;">
                                <div class="accordion-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapseinTwo"><h4>Collapsible Group Item #2</h4></a>
                            <div id="collapseinTwo" class="collapse" style="height: 0px;">
                                <div class="accordion-body ">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <a class="accordion-title collapsed" data-toggle="collapse" data-parent="#accordioninpanel" href="#collapseinThree"><h4>Collapsible Group Item #3</h4></a>
                            <div id="collapseinThree" class="collapse" style="height: 0px;">
                                <div class="accordion-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</div>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>

                    <dl class="dl-vertical">
                        <dt>Not Overdue</dt>
                        <dd>10 kr</dd>
                        <dt>< 10 days</dt>
                        <dd>20</dd>
                        <dt>< 20 days</dt>
                        <dd></dd>
                        <dt>< 30 days</dt>
                        <dd></dd>
                        <dt>60+ days</dt>
                        <dd></dd>
                        <dt>Inkasso</dt>
                        <dd></dd>
                        <dt>Overdue Total</dt>
                        <dd></dd>
                        <dt>Total</dt>
                        <dd></dd>
                    </dl>
                    <div class="clearfix"></div>
                    <hr />
                    LIST
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table-condensed">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Debetor</th>
                                        <th>Net Value</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Seller</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Invoice</td>
                                        <td>Debetor N</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
-->
    <svg style="color: #000; width: 250px;" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180.1 23">
        <path fill="#000" d="M37,23a7.94,7.94,0,0,1-3.2-.6,7.16,7.16,0,0,1-2.5-1.6,7.76,7.76,0,0,1-1.6-2.4,7.65,7.65,0,0,1-.6-3V14.8a9,9,0,0,1,.6-3.4,9.53,9.53,0,0,1,1.6-2.6,8.07,8.07,0,0,1,2.4-1.7,7.17,7.17,0,0,1,2.9-.6,7.45,7.45,0,0,1,3.1.6,6,6,0,0,1,2.2,1.6,8.57,8.57,0,0,1,1.4,2.5,10.73,10.73,0,0,1,.5,3.2v1.5H32.7a4.8,4.8,0,0,0,.5,1.7,4.51,4.51,0,0,0,1,1.4,4.19,4.19,0,0,0,1.4.9,4.84,4.84,0,0,0,1.8.3,7,7,0,0,0,2.5-.5,3.85,3.85,0,0,0,1.8-1.5L43.6,20a5.24,5.24,0,0,1-1,1.1,7.1,7.1,0,0,1-1.4,1,7.51,7.51,0,0,1-1.8.7A19.42,19.42,0,0,1,37,23ZM36.6,9.3a3.19,3.19,0,0,0-1.4.3,2.84,2.84,0,0,0-1.1.8,5,5,0,0,0-.8,1.2,6.87,6.87,0,0,0-.5,1.7h7.5V13a3.53,3.53,0,0,0-.3-1.4,7.46,7.46,0,0,0-.7-1.2,4.44,4.44,0,0,0-1.1-.8A4.15,4.15,0,0,0,36.6,9.3ZM54.7,23a7.94,7.94,0,0,1-3.2-.6A7.16,7.16,0,0,1,49,20.8a7.76,7.76,0,0,1-1.6-2.4,7.65,7.65,0,0,1-.6-3V14.8a9,9,0,0,1,.6-3.4A9.53,9.53,0,0,1,49,8.8a8.07,8.07,0,0,1,2.4-1.7,7.17,7.17,0,0,1,2.9-.6,7.45,7.45,0,0,1,3.1.6,6,6,0,0,1,2.2,1.6A8.57,8.57,0,0,1,61,11.2a10.73,10.73,0,0,1,.5,3.2v1.5H50.3a4.8,4.8,0,0,0,.5,1.7,4.51,4.51,0,0,0,1,1.4,4.19,4.19,0,0,0,1.4.9,4.84,4.84,0,0,0,1.8.3,7,7,0,0,0,2.5-.5,3.85,3.85,0,0,0,1.8-1.5L61.2,20a5.24,5.24,0,0,1-1,1.1,7.1,7.1,0,0,1-1.4,1,7.51,7.51,0,0,1-1.8.7A17.85,17.85,0,0,1,54.7,23ZM54.3,9.3a3.19,3.19,0,0,0-1.4.3,2.84,2.84,0,0,0-1.1.8,5,5,0,0,0-.8,1.2,6.87,6.87,0,0,0-.5,1.7H58V13a3.53,3.53,0,0,0-.3-1.4,7.46,7.46,0,0,0-.7-1.2,4.44,4.44,0,0,0-1.1-.8A4.15,4.15,0,0,0,54.3,9.3Zm48.5,6.8a8.23,8.23,0,0,1-.7,2.8,6,6,0,0,1-1.6,2.2,5.94,5.94,0,0,1-2.3,1.4,9.29,9.29,0,0,1-2.9.5,7.12,7.12,0,0,1-3.4-.8,6.11,6.11,0,0,1-2.4-2.1,14,14,0,0,1-1.5-3,15,15,0,0,1-.5-3.7V10.5A15,15,0,0,1,88,6.8a8.14,8.14,0,0,1,1.5-3.1,7.91,7.91,0,0,1,2.4-2.1A7.42,7.42,0,0,1,95.3.8a9.43,9.43,0,0,1,3,.5,6.78,6.78,0,0,1,2.3,1.4,8.55,8.55,0,0,1,1.5,2.2,9.47,9.47,0,0,1,.7,2.9H99.3a8.47,8.47,0,0,0-.4-1.7,4,4,0,0,0-.7-1.3A2,2,0,0,0,97,4a4.31,4.31,0,0,0-1.7-.3,3.94,3.94,0,0,0-2,.5A4.71,4.71,0,0,0,92,5.7a6.49,6.49,0,0,0-.7,2.1,12.22,12.22,0,0,0-.3,2.6v3a21.12,21.12,0,0,0,.2,2.6,6.49,6.49,0,0,0,.7,2.1,3.53,3.53,0,0,0,1.3,1.4,3.15,3.15,0,0,0,2,.5A3.61,3.61,0,0,0,98,18.9a5.92,5.92,0,0,0,1.2-3h3.6v0.2Zm3-16.1h8.6V19.8h4.9v2.9H105.8V19.8h5.1V2.9h-5.1V0Zm16.5,6.7h8.5V19.8h4.7v2.9H122.2V19.8h5V9.6h-5V6.7h0.1Zm22.9,13.5a5.85,5.85,0,0,0,1.3-.2,2.38,2.38,0,0,0,1.1-.6,1.6,1.6,0,0,0,.7-0.9,2,2,0,0,0,.3-1.1h3.3a5.45,5.45,0,0,1-.5,2.2,5.17,5.17,0,0,1-1.5,1.8,7.19,7.19,0,0,1-2.1,1.2,7.27,7.27,0,0,1-2.5.4,7.94,7.94,0,0,1-3.2-.6,6.58,6.58,0,0,1-2.3-1.7,7.22,7.22,0,0,1-1.4-2.6,10.59,10.59,0,0,1-.5-3.1V14.5a10.59,10.59,0,0,1,.5-3.1,8.16,8.16,0,0,1,1.4-2.6,6.58,6.58,0,0,1,2.3-1.7,7.94,7.94,0,0,1,3.2-.6,7.66,7.66,0,0,1,2.7.4,7.19,7.19,0,0,1,2.1,1.2,5.22,5.22,0,0,1,1.4,1.9,6,6,0,0,1,.5,2.4h-3.3a5,5,0,0,0-.2-1.2,3.59,3.59,0,0,0-.7-1,4.13,4.13,0,0,0-1.1-.7,3.46,3.46,0,0,0-3.2.2,3.45,3.45,0,0,0-1.2,1.2,4.28,4.28,0,0,0-.6,1.7,12.25,12.25,0,0,0-.2,1.9V15a12.25,12.25,0,0,0,.2,1.9,4.92,4.92,0,0,0,.6,1.7,3.45,3.45,0,0,0,1.2,1.2A3.87,3.87,0,0,0,145.2,20.2Zm15.2-4.5-1.9,1.8v5.2H155V0h3.5V13.4l1.5-1.7,4.7-5H169l-6.2,6.6,7.1,9.3h-4.4Zm15.5-.9a4.2,4.2,0,0,1-2.1-.6,5.36,5.36,0,0,1-1.5-1.5,3.7,3.7,0,0,1-.6-2.1,4.2,4.2,0,0,1,.6-2.1A5.36,5.36,0,0,1,173.8,7a3.7,3.7,0,0,1,2.1-.6A4.2,4.2,0,0,1,178,7a5.36,5.36,0,0,1,1.5,1.5,3.7,3.7,0,0,1,.6,2.1,4.2,4.2,0,0,1-.6,2.1,5.36,5.36,0,0,1-1.5,1.5A4.2,4.2,0,0,1,175.9,14.8Zm0-7.4a2.93,2.93,0,0,0-1.6.4A3.45,3.45,0,0,0,173.1,9a3.17,3.17,0,0,0-.4,1.6,4.19,4.19,0,0,0,.4,1.6,3.45,3.45,0,0,0,1.2,1.2,3.4,3.4,0,0,0,3.2,0,3.45,3.45,0,0,0,1.2-1.2,3.17,3.17,0,0,0,.4-1.6,2.93,2.93,0,0,0-.4-1.6,3.45,3.45,0,0,0-1.2-1.2A4.19,4.19,0,0,0,175.9,7.4Zm1,5.6-1.7-2v2h-0.7V8.3h1.4a1.5,1.5,0,0,1,1.1.4,1.28,1.28,0,0,1,.4,1,1.28,1.28,0,0,1-.4,1,1.78,1.78,0,0,1-1,.4l1.7,2h-0.8V13Zm-1-2.6a1.45,1.45,0,0,0,.7-0.2,0.56,0.56,0,0,0,.2-0.6,0.76,0.76,0,0,0-.2-0.5,1.42,1.42,0,0,0-.6-0.2h-0.7v1.5h0.6ZM8,11.9v2.8h3.9l-0.1,1.4a4.58,4.58,0,0,1-1.2,3,3.61,3.61,0,0,1-2.8,1.1,3.94,3.94,0,0,1-2-.5,4.36,4.36,0,0,1-1.3-1.4,6.49,6.49,0,0,1-.7-2.1,21.12,21.12,0,0,1-.2-2.6v-3a19.48,19.48,0,0,1,.2-2.5A6.49,6.49,0,0,1,4.5,6,4.71,4.71,0,0,1,5.8,4.5a3.15,3.15,0,0,1,2-.5,4.67,4.67,0,0,1,1.7.3,3.6,3.6,0,0,1,1.2.8,3.29,3.29,0,0,1,.7,1.3,8.47,8.47,0,0,1,.4,1.7h3.5a8.82,8.82,0,0,0-.7-2.9A7.12,7.12,0,0,0,13.1,3a5.94,5.94,0,0,0-2.3-1.4,4.79,4.79,0,0,0-3-.7,7.42,7.42,0,0,0-3.4.8A6.44,6.44,0,0,0,2,3.7,9.93,9.93,0,0,0,.5,6.8,14.38,14.38,0,0,0,0,10.5v2.9a14.38,14.38,0,0,0,.5,3.7,7.08,7.08,0,0,0,1.5,3,6.89,6.89,0,0,0,2.4,2.1,7.42,7.42,0,0,0,3.4.8,8.36,8.36,0,0,0,2.9-.5A6.78,6.78,0,0,0,13,21.1a7.35,7.35,0,0,0,1.6-2.2,8.87,8.87,0,0,0,.7-2.8V11.9H8Zm75.4,7.2a1.8,1.8,0,1,1-1.8,1.8A1.8,1.8,0,0,1,83.4,19.1ZM129.1,0A2.1,2.1,0,1,1,127,2.1,2.1,2.1,0,0,1,129.1,0ZM78.6,22.7H75.2V12.8a4.45,4.45,0,0,0-.8-2.8A3,3,0,0,0,72,9.1a3.82,3.82,0,0,0-3.2,1.3c-0.7.9-1,2.3-1,4.3v8H64.4V6.7h2.7l0.5,2h0.2a4.63,4.63,0,0,1,2.1-1.8,7.31,7.31,0,0,1,3-.6c3.9,0,5.8,2,5.8,5.9V22.7H78.6ZM64.4,6.7h3.3v5.6H64.4V6.7ZM26.6,6.1a8.12,8.12,0,0,1,1.7.1L28,9.5a7.72,7.72,0,0,0-1.5-.2,4.6,4.6,0,0,0-3.4,1.4,4.75,4.75,0,0,0-1.3,3.5v8.5H18.3V6.7H21l0.5,2.6h0.2A5.83,5.83,0,0,1,23.8,7,4.9,4.9,0,0,1,26.6,6.1Zm-8.2.6h3.4v5.6H18.4V6.7Z"></path>
    </svg>
@stop