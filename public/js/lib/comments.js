/**
 * Created by dib on 03-Apr-16.
 */
var model = getModel();
var modelId = getModelId();
var body = $('body');

//Escapes html elements from string, with exception to some allowed tags
function escapeHtml(input) {
    // list of all allowed tags
    var allowedTags = ['<b>','</b>','<strong>','</strong>','<p>','</p>','<br />','</br>','<br>','<br/>',
        '<table>','</table>','<table','<thead>','</thead>','<tbody>','</tbody>','<td>','<td','<tr','</td>','<tr>','</tr>',
        '<ol>','</ol>','<ul>','</ul>','<li>','</li>',
        '<span>','</span>','<div>','</div>','<i>','</i>','<a>','</a>','<a','<div','<span','>'];
    // replace each tag with a temporary string, which will later be replaced back to the original tag
    $.each(allowedTags,(function(a,b){
        input = input.replaceAll(b,"{"+a+'}');
    }));
    //escape the rest of the
    input = input.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

    $.each(allowedTags,(function(a,b){
        input = input.replaceAll('\\{'+a+'\\}',b);
    }));
    return input;
}

$(document).ready((function(){

// initalize comments
    if($('#comments').length > 0){
        initializeComments();
    }
    $('#commentSortingChange').on('change',(function () {
        sortComments();
    }));
    /**
     * changing comments to show
     */
    body.on('change','#commentTypeChange',(function(event){
        var type = $(event.target).val();
        relatedComments(type,getModel(),getModelId(),JSON.parse($(event.target).data('relations')));
    }));
    /**
     * Hide a comment
     */
    body.on('click','.deleteComment',(function(event){
        deleteComment(event);
        return false;
    }));
    /**
     * stick/unstick a comment
     */
    body.on('click','.stickComment',(function(event){
        var btn = $(event.currentTarget);
        btn.prop('disabled',true);
        var icon = $(event.currentTarget).find("i");
        var commentLi = icon.closest('li');
        var commentContainer = $(commentLi);
        var sticky = commentContainer.attr('data-sticky');
        $.when(stickComment(event,sticky)).then(function (data) {
            if(sticky == 'true'){
                new PNotify({
                    title: Lang.get('labels.success'),
                    text: Lang.get('The comment has been unstuck'),
                    type: 'success'
                });
                commentContainer.attr('data-sticky', false);
                commentContainer.css('background-color', 'transparent');
                icon.removeClass('fa-times');
                icon.addClass('fa-thumb-tack');
            }else if(sticky == 'false'){
                new PNotify({
                    title: Lang.get('labels.success'),
                    text:Lang.get('The comment has been stuck to the top'),
                    type: 'success'
                });
                commentContainer.attr('data-sticky', true);
                commentContainer.css('background-color', '#ffffe6');
                icon.removeClass('fa-thumb-tack');
                icon.addClass('fa-times');
            }
            btn.prop('disabled',false);
            sortComments();
        })

    }));
    /**
     * Start showing a comment again
     */
    body.on('click','.showComment',(function(event){
        showComment(event);
        return false;
    }));
    /**
     * opens a texarea for the comment and then renders it after save
     */
    body.on('click','.saveCommentReply',(function(event){
        addCommentReply(event);
        return false;
    }));
    body.on('change','#showHiddenComments', (function (event) {
        toggleHiddenComments(event);
        return false;
    }));
    body.on('click','.addReplyToComment',(function(event){
        createCommentReplyPlaceholder(event);
        return false;
    }));
    body.on('submit','#newCommentForm',(function(event){
        event.preventDefault();
        var formData = convertSerializedArrayToHash($(this).serializeArray());
        saveComment(formData);
    }));
    body.on('submit','#commentReplyForm',(function(event){
        event.preventDefault();
        var formData = convertSerializedArrayToHash($(this).serializeArray());
        saveCommentReply(event,formData);
    }));
}));

/**
 *sorts the comments by the date they were created
 *
 */
function sortComments(){
    var order = $('#commentSortingChange').val();
    var up,down;
    if(order == "asc"){
        up = 1;
        down = -1;
    }else{
        up = -1;
        down = 1;
    }
    var $wrapper = $('.panel-comments');
    $wrapper.find('.parentCommentContainer').sort((function(a, b){
        var sticky1 = a.dataset['sticky'];
        var sticky2 = b.dataset['sticky'];
        var date1 = new Date(a.dataset.commentDate);
        var date2 = new Date(b.dataset.commentDate);
        if(sticky1 > sticky2) {
            if(order == 'asc'){
                return down;
            }else {
                return up;
            }
        }else if(sticky1 < sticky2) {
            if(order == 'desc'){
                return down;
            }else {
                return up;
            }
        }else if(date2 < date1) {
            return up;
        }else if(date2 > date1) {
            return down;
        }
    })).appendTo($wrapper);
}

/**
 * Initialize the comments. a lot of stuff going on
 */
function initializeComments(){

    $.when($.get(base_url+"/app/relatedEntities/"+model).success((function (a) {
        return a;
    }))).then((function (data) {
        data = JSON.parse(data);
        // create a select with information about the relations
        createSelectCommentType(data);

        //render all comments on load
        relatedComments('all',model,modelId,data);

        sortComments();
    }));
}

/**
 * Initializes the select for a comment type, depending on the related models
 *
 * @param relatedEntities
 */
function createSelectCommentType(relatedEntities){

    var select = $('#commentTypeChange');
    select.data('relations',JSON.stringify(relatedEntities));
    var options = '';
    for(var key in relatedEntities){
        relatedEntities[key].forEach((function (model) {
            options += "<option value='"+key+"-"+model+"'>"+Lang.get("labels."+model.toLowerCase())+"</option>"
        }))
    }
    select.append(options);
}
/**
 * Renders one or many comments with their children
 *
 * @param data
 */
function renderComments(data) {
    //$('#comments').find('.panel-comments').empty();
    data.forEach((function (comment) {
        var date = new Date(comment.Created);
        var userName = "System";
        if(comment.hasOwnProperty('User')){
            userName = (comment.User != null)? comment.User.FullName : $('#user-UserName').val();
        }

        $('#comments').find('.panel-comments').loadTemplate(base_url + '/templates/comments/comment.html',
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
                overwriteCache:true,
                prepend:true,
                success:function(){
                    var commentElement = $('.panel-comments').find('li.parentCommentContainer[data-comment-id='+comment.Id+']');

                    if (comment.hasOwnProperty('Children')){
                        //// sort the replies
                        //comment.Children = comment.Children.sort(function (a, b) {
                        //    return b - a;
                        //});
                        var replies = [];
                        //replies to this comment will go on a different row and with offset
                        comment.Children.forEach((function (children) {
                            // hide or show the comment
                            var userName = (children.hasOwnProperty('User') ? children.User.UserName : $('#user-UserName').val());
                            // hide or show the comment
                            var date = new Date(children.Created);
                            replies.push({ParentId:children.ParentCommentId,CommentId:children.Id,Comment:children.Message,UserName:userName,Created:date.toDateTime(),
                            });
                        }));
                        var repliesPlaceholder = commentElement.find('.replies');
                        repliesPlaceholder.loadTemplate(base_url+'/templates/comments/commentReply.html',replies);
                    }
                    var icon = commentElement.find(".stickComment > i");
                    if(comment.Sticky){
                        icon.addClass('fa-times');
                        commentElement.css('background-color', '#ffffe6');
                    }else{
                        icon.addClass(' fa-thumb-tack');
                    }
                    //
                    sortComments();
                }
            }
        );
    }));
}
/**stick a comment**/
function stickComment(event,sticky){
    var commentContainer = $(event.target).closest('li');
    var id = commentContainer.data('comment-id');
    var stick = sticky == 'true' ? false : true;
    return $.ajax({
       type: "PATCH",
        url: api_address + 'Comments('+id+')',
        data : JSON.stringify({Sticky:stick}),
        success: function (data){
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
// /**
//  * unsticks a comment
//  */
// function unstickComment(event){
//     var commentContainer = $(event.target).closest('li');
//     var id = commentContainer.data('comment-id');
//     return $.ajax({
//         type: "PATCH",
//         url: api_address + 'Comments('+id+')',
//         data : JSON.stringify({Sticky:false}),
//         success: function (data){
//         },
//         beforeSend: function (request) {
//             request.setRequestHeader("Content-Type", "application/json");
//         }
//     });
// }

/**
 * Hides a comment. Not deleting it
 *
 */
function deleteComment(event){

    var commentContainer = $(event.target).closest('li');
    var commentId = commentContainer.data('comment-id');
    $.ajax({
        type: "POST",
        url: api_address + 'Comments('+commentId+')/action.Hide',
        success: function (data) {
            new PNotify({
                title: Lang.get('labels.success'),
                text:Lang.get('messages.comment-was-hid'),
                type: 'success'
            });
            if(commentContainer.hasClass('parentComment')){
                commentContainer.parent('.parentCommentContainer').remove();
            }else{commentContainer.remove()}
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });

    return false;
}
/**
 * shows  a comment, previously hidden
 *
 */
function showComment(event){

    var commentContainer = $(event.target).closest('li');
    var commentId = commentContainer.data('comment-id');
    $.ajax({
        type: "POST",
        url: api_address + 'Comments('+commentId+')/action.Show',
        success: function (data) {
            // change the button to delete comment
            var comment_btn = $(event.target).closest('.showComment');
            comment_btn.removeClass('showComment').addClass('deleteComment');
            comment_btn.prop('title',Lang.get('labels.delete'));
            comment_btn.find('.fa-check').removeClass('fa-check').addClass('fa-times')
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });

    return false;
}

/**
 * Toggle hidden comments on and off
 */
function toggleHiddenComments(event){
    //current model
    var model   = $('#Model').val();
    var modelId = $('#ModelId').val();
    var hidden  = $(event.target).prop('checked');
    var relations;
    getAllRelatedComments(model,modelId,relations,hidden)
}

/**
 * gets comments for the specified item and reners them
 *
 * @param model
 * @param modelId
 * @param hidden
 */
function getComments(model,modelId,hidden){

    if(typeof model == 'undefined'){
        var model = $('#Model').val();
    }

    if(typeof modelId == 'undefined'){
        var modelId = $('#ModelId').val();
    }

    // if(typeof hidden == 'undefined'){
    //     hidden = $('#showHiddenComments').prop('checked');
    // }
    // var parent = hidden ? '':' and Hidden eq false';
    // var children = hidden ? '': ";$filter=Hidden eq false";
    if(Array.isArray(modelId) ){
        modelId = "(ModelId eq "+modelId.join(' or ModelId eq ')+')';
    }else{
        modelId = "ModelId eq "+modelId;
    }

    $.when(
        $.ajax({
            type: "GET",
            url: api_address + "Comments?$filter=(Type eq 'Public') and Model eq '"+model+"' and "+modelId+"&$expand=Children($select=Id,Message,Created,ParentCommentId;$expand=User($select=UserName,FullName)),User($select=FullName,UserName)" +
            "&$select=Id,Message,Model,ModelId,Created,Sticky&$orderby=Created desc",
            success: function (result){
                //render all comments
                return result;
            },
            beforeSend: function (request) {
                request.setRequestHeader("Content-Type", "application/json");
            }
        })
    ).then((function(data){

        if(data.value.length > 0){renderComments(data.value);}
    }));
}
/**
 * saves a comment
 * The data must consist of Message,Model ,ModelId
 *
 * @param data
 */
function saveComment(data)
{
    var model = $('#Model').val();
    var modelId = $('#ModelId').val();
    data.Model = typeof data.Model == 'undefined' ? model : data.Model;
    data.ModelId = typeof data.ModelId == 'undefined' ? modelId : data.ModelId;
    // data.Message = data.Message;
    $.ajax({
        type: "POST",
        url: api_address + 'Comments',
        data: JSON.stringify(data),
        success: function (data) {
            // remember to send array even for one comment
            data.User = {UserName :getUserName()};
            renderComments([data]);
            new PNotify({
                title: Lang.get('labels.success'),
                text:"Comment added",
                type: 'success'
            });
            clearCommentTextarea();
            $('textarea.autosize').css('height','30px')
        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
/**
 * clears the textarea content
 */
function clearCommentTextarea(){
    if($('#newCommentForm').length>0){
        $('#newCommentForm')[0].reset();
    }
}
/**
 * saves comment reply and adds it to the placeholder
 *
 * @param event
 */
function addCommentReply(event)
{
    var replyPlaceholder = $(event.target).closest('.replyPlaceholder');
    var parentId  = $(event.target).closest('.parentCommentContainer').data('parent-comment-id');
    var comment   = $(replyPlaceholder).find('.replyContent').val();
    if(comment == ""){
        new PNotify({
            title: Lang.get('labels.error'), text: "Enter comment", type: 'error'
        });
        return false;
    }
    saveCommentReply(parentId,comment);
}
/**
 * save comment reply and render it
 *
 *
 */
function saveCommentReply(event,formData){
    //find the container for the reply

    var container = $(event.target).closest('.replies');
    var parentId = $(event.target).closest('.parentCommentContainer').data('comment-id');
    $.ajax({
        type: "POST",
        url: api_address + 'Comments('+parentId+')/Children',
        data: JSON.stringify(formData),
        success: function (data) {
            renderReplyComment(container,data)

        },
        beforeSend: function (request) {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}
/**
 * render the reply
 *
 */
function renderReplyComment(container,data){

    var created = new Date(data.Created);

    var userName = (data.hasOwnProperty('User') ? comment.User.UserName : $('#user-UserName').val());
    // hide or show the comment
    var hide_or_show = (data.Hidden) ? "btn btn-default btn-xs showComment" : "btn btn-default btn-xs deleteComment";
    //icon for the hide or show button
    var btn_icon = (data.Hidden) ? "fa fa-check" : "fa fa-times";
    //title text
    var btn_title = (data.Hidden) ? "Show" : "Delete";

    container.loadTemplate(base_url+'/templates/comments/commentReply.html',
        {ParentId:data.ParentCommentId,
            CommentId:data.Id,
            Comment:escapeHtml(data.Message),
            Created:created.toDateTime(),
            UserName:userName,
            ShowHideClasses:hide_or_show,
            ShowHideTitle:btn_title,
            IconClass:btn_icon},
        {
            append:true,
            success:function(){
                $('#commentReplyForm').remove();
            }
        })
}
/**
 * creates a div for the comment reply, with save button and textarea in it
 */
function createCommentReplyPlaceholder(event){

    // remove old placeholders and
    if($('.replyPlaceholder').length > 0)
    {
        $('.replyPlaceholder').remove();
    }

    //find where do we need the reply box
    var replyPlaceholder =$(event.target).closest('.parentCommentContainer').find('.replies');

    //put the reply form at the end
    replyPlaceholder.loadTemplate(base_url+'/templates/comments/replyForm.html',{},
        {append:true,success:function(){
            replyPlaceholder.find('textarea').focus();
        }
        });

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

/**
 * hides all other comment types, different then the type selected
 *
 */
function showOneCommentType(type){
    if(getModel() != "ClientAlias" && type == "one-Client"){
        type = "ClientAlias";
    }else if(type == 'own'){
        type = getModel();
    }else{
        var a = type.split('-');
        type = a[1];
    }
    $('.panel-comments > li').hide();
    $('.panel-comments > li[data-comment-type='+type+']').show();
    //find the siblings of this type and hide them
}
/**
 * goes through all realations and gathers comments
 *
 * @param model
 * @param modelId
 * @param relations
 * @param hidden
 */
function getAllRelatedComments(model,modelId,relations,hidden) {

    if (typeof relations == 'undefined') {
        relations = JSON.parse($('#commentTypeChange').data('relations'));
    }
    var commentsContainer = $('.panel-comments');
    var requests =[];
    relations['many'].forEach((function (data) {
        if (data == "Optimize") {
            getOptimizeComments(modelId);
        } else if (model == "Contract" && data == "Invoice") {
            getInvoicesFromLines(modelId);
        } else if (model == "Invoice" && data == "Contracts") {
            getContractsFromInvoiceLines(modelId);
        }else if (model == 'Draft' && data=='Contract'){
            getContractsFromDraftLines(modelId);
        }
        else {
            getManyComments(model, modelId, data);
        }
    }));

    relations['one'].forEach((function (data) {
        if (data == "Optimize") {
            getOptimizeComments(modelId);
        } else if (model == "Contract" && data == "Client") {
            getOneComments(model, modelId, "ClientAlias");
        } else {
            getOneComments(model, modelId, data);
        }
    }));
}
/**
 * Invoices Specific function. Gets contracts from invoiceLines
 */
function getContractsFromInvoiceLines(invoiceId){

    $.when(
        $.get(api_address+'Invoices('+invoiceId+')?$select=Id&$expand=InvoiceLine($select=Contract_Id)')
            .success((function(result){
                return result;
            }))
    ).then((function(data){
        var ids = $.map(data.InvoiceLine,function (data) {
            return data.Contract_Id
        });
        if(ids.length > 0) getComments('Contract',ids);

    }));
}
/**
 * Draft Specific function. Gets contracts from draft lines
 */
function getContractsFromDraftLines(draftId){
    $.when(
        $.get(api_address+'Drafts('+draftId+')?$select=Id&$expand=DraftLine($select=Contract_Id)')
            .success((function(result){
                return result;
            }))
    ).then((function(data){

        var ids = $.map(data.DraftLine,function (data) {
            return data.Contract_Id
        });

        if(ids.length > 0) getComments('Contract',ids);

    }));
}
function chunkify(a, n, balanced) {

    if (n < 2)
        return [a];

    var len = a.length,
        out = [],
        i = 0,
        size;

    if (len % n === 0) {
        size = Math.floor(len / n);
        while (i < len) {
            out.push(a.slice(i, i += size));
        }
    }

    else if (balanced) {
        while (i < len) {
            size = Math.ceil((len - i) / n--);
            out.push(a.slice(i, i += size));
        }
    }

    else {

        n--;
        size = Math.floor(len / n);
        if (len % size === 0)
            size--;
        while (i < size * n) {
            out.push(a.slice(i, i += size));
        }
        out.push(a.slice(size * n));

    }

    return out;
}

/**
 * gets comments from 1 to many association
 */
function getManyComments(model,modelId,relation){
    $.when(
        $.get(api_address+model+(model!='ClientAlias'?'s':'')+'('+modelId+')?$select=Id&$expand='+relation+'($select=Id)')
            .success((function (result) {
                return result.value;
            }))
    ).then((function(data){
        if(data[relation].length>0){
            switch (model){
                case 'TaskList':
                    if(relation == 'Children'){
                        relation = 'TaskList';
                    }
                    var ids = $.map(data["Children"],function (data) {
                        return data.Id
                    });

                    if(ids.length > 20){
                        var times  = ids.length / 20;
                        var chunks = chunkify(ids,times,true);
                        $.each(chunks,function (index,arr) {
                            getComments(relation,arr)
                        })
                    }else{
                        getComments(relation, ids);
                    }
                    break;
                default:
                    var ids = $.map(data[relation],function (data) {
                        return data.Id
                    });
                    if(ids.length > 20){
                        var times  = ids.length / 20;
                        var chunks = chunkify(ids,times,true);
                        $.each(chunks,function (index,arr) {
                            getComments(relation,arr)
                        })
                    }else{
                        getComments(relation, ids);
                    }

                    break;
            }
        }
    }));
}
/**
 * gets comments from 1 to many association
 */
function getOneComments(model,modelId,relation){
    $.when(
        $.get(api_address+model+(model!='ClientAlias'?'s':'')+'('+modelId+')?$select=Id&$expand='+relation+'($select=Id)')
            .success((function (data) {
                return data;
            }))
    ).then((function(data){
        if(data[relation]!= null){
            getComments(relation,data[relation].Id);
        }
    }));
}
/**
 * contract specific function. gets invoices from invoice lines
 */
function getInvoicesFromLines(contractId){
    $.when(
        $.get(api_address+'Contracts('+contractId+')?$select=Id&$expand=InvoiceLines($select=Invoice_Id)')
            .success((function(result){
                return result;
            }))
    ).then((function(data){
       var ids = $.map(data.InvoiceLines,function (line) {
           return line.Invoice_Id
       });
        if(ids.length  > 0){
            getComments('Invoice',ids);
        }
    }));
}
/**
 * gets the comments for each activity with type Optimization for a contract
 */
function getOptimizeComments(contractId){
    $('.panel-comments').children().hide();
    var hidden;
    if(typeof hidden == 'undefined'){
        hidden = $('#showHiddenComments').prop('checked');
    }
    var data = {};
    data.Model = "Contract";
    data.Item  = contractId;
    $.ajax({
        url: api_address+"Comments/ForItem?$filter=ContractActivity/any(d:d/ActivityType eq \'Optimize\')&$select=Id,Message,Model,ModelId,Created,Sticky&$expand=User($select=FullName,UserName,Id),Children($expand=User($select=UserName,FullName,Id))",
        type: "POST",
        data:JSON.stringify(data),
        success : function(data)
        {
            renderComments(data.value);
        },
        beforeSend: function (request)
        {
            request.setRequestHeader("Content-Type", "application/json");
        }
    });
}

