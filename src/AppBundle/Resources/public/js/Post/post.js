(function() {

    var postHashId;             // The hashId of this post
    var commentsList;           // <div/> containing the list of comments
    var newComment;             // Form to submit a new comment
    var newCommentEditor;       // CKEDITOR <textarea> for new comment
    var toolbar;                // Toolbar on top of posts
    var toolbarButton;          // Toolbar buttons
    var newCommentSubmit;       // "Submit" button
    var newCommentCancel;       // "Cancel" button
    var loadingGif;             // Loading GIF when submitting post
    
    var upvote = function(hashId, callback)
    {
        var url = Routing.generate(
            "freepost_comment_upvote",
            {
                commentHashId: hashId
            },
            true
        );
        
        $.ajax({
            type:   "post",
            url:    url,
            data:   {},
            dataType:	"json"
        })
        .done(function(response) {
        })
        .fail(function(response) {
        })
        .always(function(response) {
            callback(response);
        });
    };
    
    var downvote = function(hashId, callback)
    {
        var url = Routing.generate(
            "freepost_comment_downvote",
            {
                commentHashId: hashId
            },
            true
        );
        
        $.ajax({
            type:   "post",
            url:    url,
            data:   {},
            dataType:	"json"
        })
        .done(function(response) {
        })
        .fail(function(response) {
        })
        .always(function(response) {
            callback(response);
        });
    };
    
    var showNewCommentForm = function()
    {
        newComment.slideToggle();
        toolbarButton.submit.addClass("selected");
    };

    var hideNewCommentForm = function()
    {
        newComment.slideToggle();
        toolbarButton.submit.removeClass("selected");
    };

    
    $(document).ready(function() {
        
        postHashId          = $("#newComment input[name=postHashId]").val();
        commentsList        = $("#comments");
        newComment          = $("#newComment");
        newCommentSubmit    = $("#newComment input[type=submit]");
        newCommentCancel    = $("#newComment > .menu > .cancel");
        loadingGif          = $("#newComment #loading");
        toolbar             = $("#toolbar");
        toolbarButton       = {
            submit: $("#toolbar > #submit")
        };
        
        // Pointer to the editor
        CKEDITOR.on('instanceReady', function(evt) {
            if (evt.editor.name == "newCommentEditor")
                newCommentEditor = evt.editor;
        });
        
        // Button to show/hide new comment form
        toolbarButton.submit.click(function() {
            newComment.is(":visible") ? hideNewCommentForm() : showNewCommentForm();
        });
        
        // Submit new comment
        newCommentSubmit.click(function() {
            
            var text  = newCommentEditor.getData();
            
            if (text.length < 1)
                return;
            
            // URL used to POST the new comment
            var url = Routing.generate(
                "freepost_comment_submit_new",    // route
                {                           // route params
                    postHashId: postHashId
                },
                true                        // absolute URL
            );
            
            newCommentSubmit.hide();
            loadingGif.show();
            
            // Submit new comment
            $.ajax({
                type:   "post",
                url:    url,
                data:   {
                    text:   text
                },
                dataType:	"json"
            })
            .done(function(response) {
                hideNewCommentForm();
                
                newCommentEditor.setData("");
                
                response.html && commentsList.prepend(response.html);
            })
            .fail(function(response) {
            })
            .always(function(response) {
                loadingGif.hide();
                newCommentSubmit.show();
            });
        });
        
        newCommentCancel.click(function() {
            hideNewCommentForm();
            newCommentEditor.setData("");
        });
        
        // Loop comments
        $("#comments > .comment").each(function(index, aComment) {
            
            aComment = $(aComment);
            
            var hashId          = aComment.data("hashid");
            var userVote        = aComment.data("uservote");
            var text            = aComment.find(".text");
            var button          = {
                cancel:     aComment.find(".menu .cancel"),
                downvote:   aComment.find(".menu .downvote"),
                edit:       aComment.find(".menu .edit"),
                editSave:   aComment.find(".menu .editSave"),
                editCancel: aComment.find(".menu .editCancel"),
                link:       aComment.find(".menu .link"),
                loading:    aComment.find(".menu .loading"),
                points:     aComment.find(".menu .points"),
                reply:      aComment.find(".menu .reply"),
                submit:     aComment.find(".menu .submit"),
                upvote:     aComment.find(".menu .upvote")
            };
            var replyTextarea   = aComment.find(".replyTextarea");
            var editTextarea    = aComment.find(".editTextarea");
            var replyCkeditor   = null;
            var paddingLeft     = Number(aComment.css("padding-left").replace(/[^-\d\.]/g, ""));
            
            // Show CKEDITOR to send a reply
            var showReplyCkeditor = function() {
                var editorId = "replyEditor" + hashId;
                
                $("<textarea/>", {
                    id:     editorId,
                    name:   editorId,
                    class:  "ckeditor"
                }).appendTo(replyTextarea);
                
                replyCkeditor = CKEDITOR.replace(editorId);
                
                button.cancel.show();
                button.edit.hide();
                button.editSave.hide();
                button.editCancel.hide();
                button.downvote.hide();
                button.link.hide();
                button.loading.hide();
                button.points.hide();
                button.reply.hide();
                button.submit.show();
                button.upvote.hide();
            };
            var hideReplyCkeditor = function() {
                button.cancel.hide();
                button.edit.show();
                button.editSave.hide();
                button.editCancel.hide();
                button.downvote.show();
                button.link.show();
                button.loading.hide();
                button.points.show();
                button.reply.show();
                button.submit.hide();
                button.upvote.show();
                
                replyTextarea.empty();
                replyCkeditor = null;
            };
            
            // Show CKEDITOR to edit this comment
            var showEditCkeditor = function() {
                var editorId = "editEditor" + hashId;
                
                $("<textarea/>", {
                    id:     editorId,
                    name:   editorId,
                    class:  "ckeditor"
                }).appendTo(editTextarea);
                
                editCkeditor = CKEDITOR.replace(editorId);
                editCkeditor.setData(text.html());
                
                text.hide();
                
                button.cancel.hide();
                button.edit.hide();
                button.editSave.show();
                button.editCancel.show();
                button.downvote.hide();
                button.link.hide();
                button.loading.hide();
                button.points.hide();
                button.reply.hide();
                button.submit.hide();
                button.upvote.hide();
            };
            var hideEditCkeditor = function() {
                button.cancel.hide();
                button.edit.show();
                button.editSave.hide();
                button.editCancel.hide();
                button.downvote.show();
                button.link.show();
                button.loading.hide();
                button.points.show();
                button.reply.show();
                button.submit.hide();
                button.upvote.show();
                
                text.show();
                
                editTextarea.empty();
                editCkeditor = null;
            };
            
            button.upvote.click(function(event) {
                var commentVote = parseInt(button.points.text());
                
                // Already upvoted
                if (userVote == 1)
                {
                    button.points.text(commentVote - 1);
                    
                    // Set this comment as not voted
                    userVote = 0;
                    
                    button.upvote.removeClass("selected");
                }
                // Upvote. +2 if downvoted earlier
                else
                {
                    button.points.text(commentVote + (userVote == 0 ? 1 : 2));
                
                    // Set this comment as upvoted
                    userVote = 1;
                    
                    button.downvote.removeClass("selected");
                    button.upvote.addClass("selected");
                }
                
                upvote(hashId, function(response) {
                    if (response.done)
                    {
                    }
                });
            });
            
            button.downvote.click(function(event) {
                var commentVote = parseInt(button.points.text());
                
                // Already downvoted
                if (userVote == -1)
                {
                    button.points.text(commentVote + 1);
                    
                    // Set this comment as not voted
                    userVote = 0;
                    
                    button.downvote.removeClass("selected");
                }
                // Downvote. -2 if upvoted earlier
                else
                {
                    button.points.text(commentVote - (userVote == 0 ? 1 : 2));
                
                    // Set this comment as downvoted
                    userVote = -1;
                    
                    button.upvote.removeClass("selected");
                    button.downvote.addClass("selected");
                }
                
                downvote(hashId, function(response) {
                    if (response.done)
                    {
                    }
                });
            });
            
            // Write a new comment reply
            button.reply.click(function() {
                showReplyCkeditor();
            });
            
            // Submit comment reply
            button.submit.click(function() {
                var text  = replyCkeditor.getData();
                
                if (text.length < 1) return;
                
                // URL used to POST the new post
                var url             = Routing.generate(
                    "freepost_comment_submit_new",    // route
                    {                           // route params
                        postHashId: postHashId
                    },
                    true                        // absolute URL
                );
                
                button.submit.hide();
                button.loading.show();
                
                // Submit new post
                $.ajax({
                    type:   "post",
                    url:    url,
                    data:   {
                        parentHashId:   hashId,
                        text:           text
                    },
                    dataType:	"json"
                })
                .done(function(response) {
                    replyCkeditor.setData("");
                    hideReplyCkeditor();
                    
                    response.html && $(response.html).css("padding-left", (paddingLeft+32)+"px").insertAfter(aComment);
                })
                .fail(function(response) {
                    button.submit.show();
                })
                .always(function(response) {
                    button.loading.hide();
                });
            });
            
            // Cancel comment reply
            button.cancel.click(function() {
                hideReplyCkeditor();
            });
            
            // Edit my comment
            button.edit.click(function() {
                showEditCkeditor();
            });
            
            // Save comment edit
            button.editSave.click(function() {
                var newCommentText  = editCkeditor.getData();
                
                if (newCommentText.length < 1) return;
                
                var url = Routing.generate(
                    "freepost_comment_edit",
                    { commentHashId: hashId },
                    true
                );
                
                button.editSave.hide();
                button.loading.show();
                
                // Submit new post edits
                $.ajax({
                    type:   "post",
                    url:    url,
                    data:   {
                        text: newCommentText
                    },
                    dataType:	"json"
                })
                .done(function(response) {
                    editCkeditor.setData("");
                    hideEditCkeditor();
                    text.html(newCommentText);
                })
                .fail(function(response) {
                    button.editSave.show();
                })
                .always(function(response) {
                    button.loading.hide();
                });
            });
            
            // Cancel comment edit
            button.editCancel.click(function() {
                hideEditCkeditor();
            });
        });
        
    });
    
})();


















