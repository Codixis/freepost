(function() {

    var userImage;              // The picture in the side bar
    var pictureFile;            // <input type=file> to select community picture 
    var pictureFileLoading;     // Loading GIF to display while uploading community picture
    var pictureForm;            // <form> containing pictureFile
    var displayName;            // <input type=text> with the community name
    
    /* This is used to receive a response from iframenull when a user changes
     * user picture, so that I know when it finished uploading.
     */
    window.addEventListener(
        "message",
        function(event) {
            // Do we trust the sender of this message?  (might be
            // different from what we originally opened, for example).
            //if (event.origin !== "http://example.org")
            //    return;
            
            // event.source
            // event.data
            
            switch (event.data.action.toUpperCase())
            {
                // Community picture uploaded
                case "UPDATEUSERPICTURE":
                    pictureFileLoading.hide();
                    pictureForm.show();
                    
                    // Reload menu picture on side bar
                    if (event.data.status.toUpperCase() == "DONE")
                        userImage.attr("src", userImage.attr("src") + "?" + Math.random());
                    
                    break;
                default:
                    break;
            }
        },
        false
    );
    
    $(document).ready(function() {
        
        userImage           = $(".userMenu > .picture > .image");
        pictureFile         = $("#pictureFile");
        pictureFileLoading  = $("#pictureFileLoading");
        pictureForm         = $("#pictureForm");
        displayName         = $("#displayName");
        
        pictureForm.on("submit", function() {
            pictureForm.hide();
            pictureFileLoading.show();
        });
        
        pictureFile.on("change", function() {
            pictureForm.submit();
        });
        
        displayName.on("change", function() {
            
            // URL used to POST the new name
            var url = Routing.generate(
                "freepost_user_update_name",
                {},
                true
            );
            
            $.ajax({
                type:   "post",
                url:    url,
                data:   {
                    displayName: displayName.val()
                },
                dataType:	"json"
            })
            .done(function(response) {
                console.log(response);
            })
            .fail(function(response) {
                console.log(response);
            });
        });
        
    });
    
})();