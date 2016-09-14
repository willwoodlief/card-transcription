(function() {
'use strict';

var GrayScale = Darkroom.Transformation.extend({
  applyTransformation: function(canvas, image, next) {

    image.filters.push(new fabric.Image.filters.Grayscale());

      image.applyFilters();
      canvas.renderAll();




      // apply filters and re-render canvas when done
      //image.applyFilters(canvas.renderAll.bind(canvas));







        console.log('applied gray');

    next();
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
