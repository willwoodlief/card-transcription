(function() {
'use strict';

var Invert = Darkroom.Transformation.extend({
  applyTransformation: function(canvas, image, next) {

    image.filters.push(new fabric.Image.filters.Invert());

      image.applyFilters(canvas.renderAll.bind(canvas));
      var angle = (image.getAngle() + 180) % 360;
   //   image.rotate(angle);

      var width, height;
      height = Math.abs(image.getWidth()*(Math.sin(angle*Math.PI/180)))+Math.abs(image.getHeight()*(Math.cos(angle*Math.PI/180)));
      width = Math.abs(image.getHeight()*(Math.sin(angle*Math.PI/180)))+Math.abs(image.getWidth()*(Math.cos(angle*Math.PI/180)));

      canvas.setWidth(width);
      canvas.setHeight(height);

      canvas.centerObject(image);
      image.setCoords();
      canvas.renderAll();
      //alert(window.frameElement.getAttribute("id"));

      var b = $('div .darkroom-image-container');
      var c = $('div .darkroom-source-container');

      b.hide();
      c.show();

      next(image);





      // apply filters and re-render canvas when done
      //image.applyFilters(canvas.renderAll.bind(canvas));







        console.log('applied invert');


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

      this.darkroom.refresh();
  },


    invert: function invert() {
        this.darkroom.applyTransformation(
          new Invert()
        );
        

  }

});

})();
