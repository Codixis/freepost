(function() {
    
    var communityHashId;
    var postsList;              // <div> container of all posts
    var postsListLoading;       // Display this loading icon when loading the list of posts
    var newPost;                // Form to submit a new post
    var newPostTitle;
    var newPostEditor;          // CKEDITOR <textarea> for new post
    var toolbar;                // Toolbar on top of posts
    var toolbarButton;          // Toolbar buttons
    var newPostSubmit;          // "Submit" button
    var newPostCancel;          // "Cancel" button
    var loadingGif;             // Loading GIF when submitting post
    
    var upvote = function(postHashId, callback)
    {
        var url = Routing.generate(
            "freepost_post_upvote",
            {
                postHashId: postHashId
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
    
    var downvote = function(postHashId, callback)
    {
        var url = Routing.generate(
            "freepost_post_downvote",
            {
                postHashId: postHashId
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
    
    var showNewPostForm = function()
    {
        newPost.slideToggle();
        toolbarButton.submit.addClass("selected");
    };

    var hideNewPostForm = function()
    {
        newPost.slideToggle();
        toolbarButton.submit.removeClass("selected");
    };

    var loadPosts = function(sort)
    {
        var url;
        
        toolbarButton.hot.removeClass("selected");
        toolbarButton.new.removeClass("selected");
        
        switch (sort.toUpperCase())
        {
            case "HOT":
                url = "freepost_posts_list_hot";
                toolbarButton.hot.addClass("selected");
                break;
            default:
                url = "freepost_posts_list_new";
                toolbarButton.new.addClass("selected");
        }
        
        var url = Routing.generate(url, { communityHashId: communityHashId }, true);
        
        postsList.empty();
        postsListLoading.show();
        
        $.ajax({
            type:   "get",
            url:    url,
            data:   {},
            dataType:	"json"
        })
        .done(function(response) {
            if (response.html)
            {
                var newPostsList = $(response.html);
                postsList.replaceWith(newPostsList);
                postsList = newPostsList;
            }
        })
        .fail(function(response) {
        })
        .always(function(response) {
            postsListLoading.hide();
        });
    }
    
    var init = function()
    {
        communityHashId  = $("#newPost input[name=communityHashId]").val();
        postsList        = $("#postsList");
        postsListLoading = $("#postsListLoading");
        newPost          = $("#newPost");
        newPostEditor    = null;
        newPostSubmit    = $("#newPost input[type=submit]");
        newPostCancel    = $("#newPost > .menu > .cancel");
        newPostTitle     = $("#newPost input[name=title]");
        loadingGif       = $("#newPost #loading");
        toolbar          = $("#toolbar");
        toolbarButton    = {
            submit: $("#toolbar > #submit"),
            hot:    $("#toolbar > .hot"),
            new:    $("#toolbar > .new")
        };
        
        // Pointer to the editor
        CKEDITOR.on('instanceReady', function(evt) {
            if (evt.editor.name == "newPostEditor")
                newPostEditor = evt.editor;
        });
        
        /* CKEDITOR is loaded before this script. So I need this code to create the
         * new instance when posts are loaded through ajax (and CKEDITOR.on('instanceReady'
         * is not re-initialized).
         */
        (newPostEditor == null) && (newPostEditor = CKEDITOR.replace("newPostEditor"));
        
        // Button to show/hide new post form
        toolbarButton.submit.click(function() {
            newPost.is(":visible") ? hideNewPostForm() : showNewPostForm();
        });
        toolbarButton.hot.click(function() {
            loadPosts("hot");
        });
        toolbarButton.new.click(function() {
            loadPosts("new");
        });
        newPostCancel.click(function() {
            newPost.is(":visible") ? hideNewPostForm() : showNewPostForm();
        });
        
        // Submit new post
        newPostSubmit.click(function() {
            
            var title = newPostTitle.val();
            var text  = newPostEditor.getData();
            
            // URL used to POST the new post
            var url             = Routing.generate(
                "freepost_submit_new_post",    // route
                {                           // route params
                    communityHashId: communityHashId
                },
                true                        // absolute URL
            );
            
            newPostSubmit.hide();
            loadingGif.show();
            
            // Submit new post
            $.ajax({
                type:   "post",
                url:    url,
                data:   {
                    title:  title,
                    text:   text
                },
                dataType:	"json"
            })
            .done(function(response) {
                hideNewPostForm();
                
                newPostTitle.val("");
                newPostEditor.setData("");
                
                response.html && postsList.prepend(response.html);
            })
            .fail(function(response) {
            })
            .always(function(response) {
                loadingGif.hide();
                newPostSubmit.show();
            });
        });
        
        // Loop posts
        postsList.children(".post").each(function(index, aPost) {
            
            aPost = $(aPost);
            var button = {
                upvote:   aPost.find(".upvote"),
                downvote: aPost.find(".downvote"),
                points:   aPost.find(".points")
            };
            var hashId = aPost.data("hashid");
            var userVote = parseInt(aPost.data("uservote"));
            
            button.upvote.click(function(event) {
                var postVote = parseInt(button.points.text());
                
                // Already upvoted
                if (userVote == 1)
                {
                    button.points.text(postVote - 1);
                    
                    // Set this post as not voted
                    userVote = 0;
                    
                    button.upvote.removeClass("selected");
                }
                // Upvote. +2 if downvoted earlier
                else
                {
                    button.points.text(postVote + (userVote == 0 ? 1 : 2));
                
                    // Set this post as upvoted
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
                var postVote = parseInt(button.points.text());
                
                // Already downvoted
                if (userVote == -1)
                {
                    button.points.text(postVote + 1);
                    
                    // Set this post as not voted
                    userVote = 0;
                    
                    button.downvote.removeClass("selected");
                }
                // Downvote. -2 if upvoted earlier
                else
                {
                    button.points.text(postVote - (userVote == 0 ? 1 : 2));
                
                    // Set this post as downvoted
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
        });
        
    }
    
    $(document).ready(function() {
        init();
    });
    
})();










