jQuery(document).ready(function($){
"use strict";
//Load the colorpicker
    $('.color-picker').iris({
        width: $("#color").width()-2,
        palettes: true
    });

//Set the target
  $(".color-picker").click(function() {
    var bg = $(this).val();
    var id = $(this).attr('data-id');
    var color = $(this).val();
    //Toggle the picker
      $(this).next('.iris-picker').toggle();
    //Change the BG color
      $(this).css( "background-color", bg );
      $(this).css({"background-color":bg,"color":invertColor(bg)});

      var data = { action: 'update_color_options_array',
                   categoryId: id,
                   categoryColor: color,
                   colornonce: ajaxObject.colornonce

      };

      jQuery.post(ajaxObject.ajaxUrl, data, function() {
        //Update field with new values
        $(this).css( "background-color", $(this).val() );

      });

  });

  function invertColor(hexTripletColor) {
      var color = hexTripletColor;
      color = color.substring(1);           // remove #
      color = parseInt(color, 16);          // convert to integer
      color = 0xFFFFFF ^ color;             // invert three bytes
      color = color.toString(16);           // convert to hex
      color = ("000000" + color).slice(-6); // pad with leading zeros
      color = "#" + color;                  // prepend #
      return color;
  }

});//END jQuery