(function() {
'use strict';

var Invert = Darkroom.Transformation.extend({
  applyTransformation: function(canvas, image, next) {

    image.filters.push(new fabric.Image.filters.Invert());

      image.applyFilters();
      canvas.renderAll();




      // apply filters and re-render canvas when done
      //image.applyFilters(canvas.renderAll.bind(canvas));







        console.log('applied invert');

    next();
  }
});

Darkroom.plugins['invert'] = Darkroom.Plugin.extend({

  initialize: function InitDarkroomRotatePlugin() {
        var buttonGroup = this.darkroom.toolbar.createButtonGroup();

        var filterButton = buttonGroup.createButton({
          image: 'plus'
        });

        filterButton.addEventListener('click', this.doInvert.bind(this));


  },

  doInvert: function doInvert() {
      this.invert();
  },


    invert: function invert() {
        this.darkroom.applyTransformation(
          new Invert()
        );
        

  }

});

})();
