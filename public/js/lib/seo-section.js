/**
 * Created by dib on 03-Apr-16.
 */
var modelId = getModelId();
var body = $('body');

$(document).ready(function(){

//initalize comments
    if($('.panel-seo-comments').length > 0){
        initializeSeoComments();
    }
    $('#seoCommentSortingChange').on('change',function () {
        sortSeoComments();
    });
    body.on('submit','#newSeoCommentForm',function(event){
        event.preventDefault();
        var formData = convertSerializedArrayToHash($(this).serializeArray());
        saveSeoComment(formData);
    });

    if($('#clientLoginsTable').length > 0){
        clientLoginsTable = $('#clientLoginsTable').DataTable(
            {
                "bPaginate":false,
                'bInfo':false,
                'bFilter':false,
                responsive:true,
                stateSave:true,
                aaSorting:[[0,"desc"]], // shows the newest items first
                "bProcessing": true,
                "bServerSide": true,
                "deferRender": true, // testing if speed is better with this
                'filter' : "DeletedDate eq null and Contract_Id eq "+getModelId(),
                'select':'Id',
                "sAjaxSource": api_address+"ClientLogins?$expand=User($select=FullName)",
                "aoColumns": [
                    {mData:"Title"},
                    {mData:"Protocol",searchable:false},
                    {mData:"Host",mRender:function (host,display,obj) {
                        return "<a target='_blank' href='"+(isInArray(obj.Protocol,['http','https']) ? addhttp(host) : obj.Protocol+':'+host)+"'>"+host+"</a>"
                    }},
                    {mData:"Username"},
                    {mData:"Password",searchable:false,"sClass": "relative-position",sortable:false,mRender:function (pass,display,obj) {
                        return "<span class='decryptPassword pseudolink' data-client-login-id='"+obj.Id+"' title='See Password'>**********</span>";
                    }},
                    {mData:null,oData:"User/FullName",mRender:function (obj) {
                        return obj.User.FullName
                    }},
                    {mData:'Description',sClass:'multiline'
                    },
                    {mData:null,searchable:false,sortable:false,mRender:function (obj) {
                        return "<i class='fa fa-times deleteClientLogin' data-client-login-id='"+obj.Id+"' title='Delete Login'></i>"
                    }}
                ],
                "fnServerData": fnServerOData,
                "iODataVersion": 4,
                "bUseODataViaJSONP": false
            });
    }

    body.on('submit','#clientLoginsForm',function (event) {
        saveClientLogin(event);
    });

    body.on('click','.decryptPassword',function (event) {
        decryptPassword(event)
    });

    body.on('click','.deleteClientLogin',function (event) {
        deleteClientLogin(event)
    })

});


function decryptPassword(event) {
    var target = $(event.target);
    target.html('').addClass('spinner');
    var id = target.data('client-login-id');
    $.post(base_url+"/client-logins/"+id+'/decrypt-password').
    success(function(data){
        target.removeClass('spinner');
        target.text(data.value);
        setTimeout(function () {
            target.html("<span class='decryptPassword pseudolink' data-client-login-id='"+id+"' title='See Password'>**********</span>")
        },15000)
    }).error(function (err) {
        target.removeClass('spinner');
        target.html("<span class='decryptPassword pseudolink' data-client-login-id='"+id+"' title='See Password'>**********</span>")
    });
}

function deleteClientLogin(event) {
    var target = $(event.target);
    var id = target.data('client-login-id');
    bootbox.confirm("Are you sure?", function(result)
    {
        if(result){
            $.ajax({
                url: api_address+"ClientLogins("+id+")",
                type: "PATCH",
                data:JSON.stringify({'DeletedDate':getIsoDate(),'DeletedUser_Id':getUserId()}),
                success : function()
                {
                    clientLoginsTable.draw();
                },
                beforeSend: function (request)
                {
                    request.setRequestHeader("Content-Type", "application/json");
                }
            });
        }
    });
}


function saveClientLogin(event) {
    event.preventDefault();
    var form = $(event.target);
    var btn = form.find(':submit');
    btn.prop('disabled',true);
    var data = form.serializeJSON();

    $.post(base_url+"/client-logins",data).
    success(function(data){
        form[0].reset();
        clientLoginsTable.draw();
        form[0].reset();
        btn.prop('disabled',false);
    }).error(function (error) {
        btn.prop('disabled',false);
    });
}



/**
 *sorts the comments by the date they were created
 */
function sortSeoComments(){
    var order = $('#commentSortingChange').val();
    var up,down;
    if(order == "asc"){
        up = 1;
        down = -1;
    }else{
        up = -1;
        down = 1;
    }
    var $wrapper = $('.panel-seo-comments');
    $wrapper.find('.parentCommentContainer').sort(function(a, b){
        var date1 = new Date(a.dataset.commentDate);
        var date2 = new Date(b.dataset.commentDate);
        return date2 < date1 ? up :  down;
    }).appendTo($wrapper);
}

/**
 * Initialize the comments. a lot of stuff going on
 */
function initializeSeoComments(){
    getSeoComments(modelId)
}

function renderSeoComments(data) {
    data.forEach(function (comment) {
        var date = new Date(comment.Created);
        var userName = "System";
        if(comment.hasOwnProperty('User')){
            userName = (comment.User != null)? comment.User.UserName : $('#user-UserName').val();
        }

        $('#seo').find('.panel-seo-comments').loadTemplate(base_url + '/templates/comments/comment.html',
            {
                UserLink:"#",
                Created:date.toDateTime(),
                CreatedIso:date,
                FullName:userName,
                ItemLink:linkToItem(comment.Model,comment.ModelId,true),
                ItemTitle:comment.Model+":"+comment.ModelId,
                Comment:escapeHtml(comment.Message),
                ParentId:comment.Id,
                Type:comment.Model,
                Sticky: comment.Sticky
            },
            {
                prepend:true,
                overwriteCache:true,
                success:function(){
                    var commentElement = $('.panel-seo-comments').find('li.parentCommentContainer[data-comment-id='+comment.Id+']');

                    if (comment.hasOwnProperty('Children')){
                        //// sort the replies
                        //comment.Children = comment.Children.sort(function (a, b) {
                        //    return b - a;
                        //});
                        var replies = [];
                        //replies to this comment will go on a different row and with offset
                        comment.Children.forEach(function (children) {
                            // hide or show the comment
                            var userName = (children.hasOwnProperty('User') ? children.User.UserName : $('#user-UserName').val());
                            // hide or show the comment
                            var date = new Date(children.Created);
                            replies.push({ParentId:children.Parent_Id,CommentId:children.Id,Comment:children.Message,UserName:userName,Created:date.toDateTime()
                            });
                        });
                        var repliesPlaceholder = $('.panel-seo-comments').find('li.parentCommentContainer[data-comment-id='+comment.Id+']').find('.replies');
                        repliesPlaceholder.loadTemplate(base_url+'/templates/comments/commentReply.html',replies,{overwriteCache:true})
                    }
                    var icon = commentElement.find(".stickComment > i");
                    if(comment.Sticky){
                        icon.addClass('fa-times');
                        commentElement.css('background-color', '#ffffe6');
                    }else{
                        icon.addClass(' fa-thumb-tack');
                    }
                    //
                    //
                    sortSeoComments();
                }
            }
        );
    });
}

/**
 * gets comments for the Seo type and renders them
 *
 * @param modelId
 */
function getSeoComments(modelId){

    if(typeof modelId == 'undefined'){
        var modelId = $('#ModelId').val();
    }

    $.when(
        $.ajax({
            type: "GET",
            url: api_address + "Comments?$expand=Children($expand=User($select=UserName,FullName)),User($select=FullName,UserName)&$orderby=Created desc&$filter=(Type eq 'Seo') and Model eq 'Contract' and ModelId eq "+modelId,
            success: function (result){
                //render all comments
                return result;
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        })
    ).then(function(data){
        if(data.value.length > 0){renderSeoComments(data.value);}
    });
}
/**
 * saves a comment
 * The data must consist of Message,Model ,ModelId
 *
 * @param data
 */
function saveSeoComment(data)
{
    var model = $('#Model').val();
    var modelId = $('#ModelId').val();
    data.Model = typeof data.Model == 'undefined' ? model : data.Model;
    data.ModelId = typeof data.ModelId == 'undefined' ? modelId : data.ModelId;
    $.ajax({
        type: "POST",
        url: api_address + 'Comments',
        data: JSON.stringify(data),
        success: function (data) {
            // remember to send array even for one comment
            data.User = {UserName :getUserName()};
            renderSeoComments([data]);
            new PNotify({
                title: Lang.get('labels.success'),
                text:"Comment added",
                type: 'success'
            });
            clearSeoCommentTextarea();
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
/**
 * clears the textarea content
 */
function clearSeoCommentTextarea(){
    if($('#newSeoCommentForm').length>0){
        $('#newSeoCommentForm')[0].reset();
    }
}

/**
 * shows comments related to the current item
 */
function relatedComments(type,model,modelId,relations){
    switch(type) {
        case "all":
            // clear the old comments
            $('.panel-comments').empty();
            //get its own comments
            getComments(model,modelId);
            //get the related to this comments
            getAllRelatedComments(model,modelId,relations);

            break;

        case "many-Optimize":
        case "one-Optimize":
            //modelId = (typeof modelId == 'undefined')? $('#ModelId').val():modelId;
            // this is just for contracts so id is enough
            getOptimizeComments(modelId);
            break;
        default:
            showOneCommentType(type);
            break;
    }
}

