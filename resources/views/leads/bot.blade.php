@extends('layout.main')
@section('page-title',Lang::get('Lead bot'))

@section('styles')
@stop

@section('scripts')
    <script>


        /*
         * jQuery.ajaxQueue - A queue for ajax requests
         *
         * (c) 2011 Corey Frang
         * Dual licensed under the MIT and GPL licenses.
         *
         * Requires jQuery 1.5+
         */
        (function($) {

// jQuery on an empty object, we are going to use this as our Queue
            var ajaxQueue = $({});

            $.ajaxQueue = function( ajaxOpts ) {
                var jqXHR,
                        dfd = $.Deferred(),
                        promise = dfd.promise();

                // queue our ajax request
                ajaxQueue.queue( doRequest );

                // add the abort method
                promise.abort = function( statusText ) {

                    // proxy abort to the jqXHR if it is active
                    if ( jqXHR ) {
                        return jqXHR.abort( statusText );
                    }

                    // if there wasn't already a jqXHR we need to remove from queue
                    var queue = ajaxQueue.queue(),
                            index = $.inArray( doRequest, queue );

                    if ( index > -1 ) {
                        queue.splice( index, 1 );
                    }

                    // and then reject the deferred
                    dfd.rejectWith( ajaxOpts.context || ajaxOpts,
                            [ promise, statusText, "" ] );

                    return promise;
                };

                // run the actual query
                function doRequest( next ) {
                    jqXHR = $.ajax( ajaxOpts )
                            .done( dfd.resolve )
                            .fail( dfd.reject )
                            .then( next, next );
                }

                return promise;
            };

        })(jQuery);

        function shuffle(array) {
            var currentIndex = array.length, temporaryValue, randomIndex;

            // While there remain elements to shuffle...
            while (0 !== currentIndex) {

                // Pick a remaining element...
                randomIndex = Math.floor(Math.random() * currentIndex);
                currentIndex -= 1;

                // And swap it with the current element.
                temporaryValue = array[currentIndex];
                array[currentIndex] = array[randomIndex];
                array[randomIndex] = temporaryValue;
            }

            return array;
        }

        $(document).ready(function(){
            var wordsCount = $('.wordsCount');

            $('#keywordsSearch').on('submit',function (event) {
                event.preventDefault();
                var form = $(event.target);
                var btn = form.find(':submit');
                var data = form.serializeJSON();
                var words = data.Keywords.split('\n');
                var resultsPlaceholder = $('#leadResults');
                var keywordsPlaceholder = $('#keywordsResults');
                var random = $('#RandomOrder').prop('checked');

                if(words.length > 0){
//                    btn.prop('disabled',true);
                    if(random){
                        words = shuffle(words);
                    }
                    var count = words.length;
                    wordsCount.text(count);
                    $.each(words,function (index,word) {
                        $.ajaxQueue({
                            url: base_url+"/leads/bot",
                            type: "POST",
                            data: JSON.stringify(word.replace('\r','')),
                            suppressErrors: true,// if this request fails, we will have infinite loop of errors
                            success : function(data)
                            {
                                var existing = $.map(data['existing'],function (value,index) {
                                    return value.Website;
                                });
                                keywordsPlaceholder.prepend("<li>"+word+" -  New : "+data['new'].length+" / <span title='"+existing.join('\n')+"'>  Existing : "+data['existing'].length+" <span></li>");
                                if(data['new'].length > 0){
                                    $.each(data['new'],function (index,value) {
                                        resultsPlaceholder.prepend('<li><a target="_blank" href="'+base_url+'/leads/show/'+value.Id+'">'+value.Website+  ' - '+word+'</a></li>')
                                    })
                                }
                                count--;
                                wordsCount.text(count);
                                if(count == 0){
                                    btn.prop('disabled',false);
                                }
                            },
                            error:function () {
                                btn.prop('disabled',false);
                            },
                            beforeSend: function (request)
                            {
                                request.setRequestHeader("Content-Type", "application/json");
                            }
                        });
                    })
                }

            });
        });
    </script>
@stop
@section('content')
    <div class="row">
        <div class="panel panel-adwords">
            <div class="panel-heading">
                <h4>Lead Bot</h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="form-horizontal col-md-4">
                        Searching from IP :<a target='_blank' href='http://ip-tracker.org/locator/ip-lookup.php?ip={{$ip}}'>{{$ip}}</a>
                        <form id="keywordsSearch">
                            <div class="form-group">
                                <label class="control-label col-md-3" for="keywords">Keywords</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" placeholder="Keywords, each on a new line" name="Keywords"></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3" for="keywords">Search in random order?</label>
                                <div class="col-md-6">
                                    <input class="form-control" id="RandomOrder" type="checkbox">
                                </div>
                            </div>

                            <div class="btn-toolbar">
                                <button type="submit" class="btn btn-adwords">Go</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="row">
                        <div class="col-md-3">Keywords in queue : <span class="wordsCount">0</span></div>
                    </div>
                    <div class="col-md-6">
                        Results:
                        <ol id="leadResults" reversed>

                        </ol>
                    </div>
                    <div class="col-md-6">
                        Keywords performance (only new leads):
                        <ol id="keywordsResults" reversed></ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop