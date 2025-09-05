jQuery(document).ready(function($) {
    Fancybox.bind(".fancybox", {
        caption: function (fancybox, carousel, slide) {
            let caption = slide.$trigger.dataset.caption 
                ? "<br />" + slide.$trigger.dataset.caption 
                : "";

            return slide.$trigger.title
                ? "<strong>" + slide.$trigger.title + "</strong>" + caption
                : caption;
        },
        Toolbar: {
            display: [
                "close"
            ],
        },
        Thumbs: false,
        dragToClose: true,
        compact: false,
        animated: true,
    });  
});
