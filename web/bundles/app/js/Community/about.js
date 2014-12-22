(function() {

    var description;            // The community description text
    var descriptionEditor;      // CKEDITOR <textarea> for new post
    var editor;                 // <div> containing descriptionEditor
    var menu;                   // menu on top of posts
    var menuButton;             // menu buttons
    var loadingGif;             // Loading GIF when submitting post
    
    var updateDescription = function(newDescription, callback) {
        
        // URL used to POST the new description
        var url = Routing.generate(
            "freepost_community_update_description",   // route
            {                                       // route params
                communityHashId: $("input[name=communityHashId]").val()
            },
            true                                    // absolute URL
        );
        
        $.ajax({
            type:   "post",
            url:    url,
            data:   {
                description: newDescription
            },
            dataType:	"json"
        })
        .done(function(response) {
            callback(response);
        })
        .fail(function(response) {
            callback(response);
        });
    };
    
    $(document).ready(function() {
        
        description     = $(".about > .text");
        editor          = $(".about > .editor");
        loadingGif      = $("#menu > .loading");
        menu            = $("#menu");
        menuButton      = {
            edit:       $("#menu > .edit"),
            discard:    $("#menu > .discard"),
            save:       $("#menu > .save"),
            loading:    $("#menu > .loading")
        };
        
        // Pointer to the editor
        CKEDITOR.on('instanceReady', function(evt) {
            if (evt.editor.name == "descriptionEditor")
                descriptionEditor = evt.editor;
        });
        
        // Button to show/hide description editor
        menuButton.edit.click(function() {
            descriptionEditor.setData(description.html());
            description.hide();
            editor.show();
            
            menuButton.edit.hide();
            menuButton.discard.show();
            menuButton.save.show();
        });
        
        // Button to discard description edits
        menuButton.discard.click(function() {
            editor.hide();
            description.show();
            
            menuButton.discard.hide();
            menuButton.save.hide();
            menuButton.loading.hide();
            menuButton.edit.show();
        });
        
        // Button to save description edits
        menuButton.save.click(function() {
            
            menuButton.save.hide();
            menuButton.loading.show();
            
            updateDescription(descriptionEditor.getData(), function(response) {
                if (!response) return;
                
                if (response.done)
                {
                    editor.hide();
                    description.html(descriptionEditor.getData()).show();
                    descriptionEditor.setData("");
                    
                    menuButton.discard.hide();
                    menuButton.loading.hide();
                    menuButton.edit.show(); 
                }
            });
        });

    });
    
})();