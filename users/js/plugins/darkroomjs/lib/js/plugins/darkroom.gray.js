(function() {
'use strict';

var GrayScale = Darkroom.Transformation.extend({
  applyTransformation: function(canvas, image, next) {

    image.filters.push(new fabric.Image.filters.Grayscale());

      image.applyFilters(canvas.renderAll.bind(canvas));
      canvas.renderAll();

     // alert(window.frameElement.getAttribute("id"));

      var b = $('div .darkroom-image-container');
      var c = $('div .darkroom-source-container');

      b.hide();
      c.show();

      next(image);




      // apply filters and re-render canvas when done
      //image.applyFilters(canvas.renderAll.bind(canvas));


        console.log('applied gray');

  }
});

Darkroom.plugins['grayscale'] = Darkroom.Plugin.extend({

  initialize: function InitDarkroomRotatePlugin() {
        var buttonGroup = this.darkroom.toolbar.createButtonGroup();

        var filterButton = buttonGroup.createButton({
          image: 'filter'
        });

        filterButton.addEventListener('click', this.doGray.bind(this));


  },

  doGray: function filterButton() {
      this.grayscale();
  },


    grayscale: function grayscale() {
        this.darkroom.applyTransformation(
          new GrayScale()
        );


  }

});

})();
