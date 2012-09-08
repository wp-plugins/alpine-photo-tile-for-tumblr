/*
 * Alpine PhotoTile for Tumblr: jQuery Tile Display Functions
 * By: Eric Burger, http://thealpinepress.com
 * Version: 1.0.0
 * Updated: August  2012
 * 
 */

(function( w, s ) {
  s.fn.APTFTbyTAPTilesPlugin = function( options ) {
  
    options = s.extend( {}, s.fn.APTFTbyTAPTilesPlugin.options, options );
  
    return this.each(function() {  
      var parent = s(this);
      var imageList = s(".APTFTbyTAP_image_list_class",parent);
      var images = s('.APTFTbyTAP-image',imageList);
      var allPerms = s('.APTFTbyTAP-link',imageList);
      var width = parent.width();
      
      var currentRow,img,newDiv,newDivContainer,src,url,height,theClasses,theHeight,theWidth,perm;
      
      if( 'square' == options.shape && 'windows' == options.style ){
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if(i%3 == 0){
            
            theClasses = "APTFTbyTAP-tile";
            theWidth = (width-8);
            theHeight = theWidth;
            newRow( theHeight );
            addDiv(i);
            
          }else if(i%3 == 1){

            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-first";
            theWidth = (width/2-4-4/2);
            theHeight = theWidth;
            newRow( theHeight );
            addDiv(i);
     
          }else if(i%3 == 2){
        
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-last";
            theWidth = (width/2-4-4/2);
            theHeight = theWidth;
            addDiv(i);
          }
          
          
        });
      }
      else if( 'rectangle' == options.shape && 'windows' == options.style ){
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if(i%3 == 0){
            theWidth = (width-8);
            height = theWidth*img.naturalHeight/img.naturalWidth;
            height = (height?height:width);
            
            newRow(height);
                        
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-tile-rectangle";
            theHeight = (height);

            addDiv(i);
            
          }else if(i%3 == 1){
            theWidth = (width/2-4-4/2);
            height = theWidth*img.naturalHeight/img.naturalWidth;
            height = (height?height:width);
            newRow( height );
            
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-first APTFTbyTAP-tile-rectangle";
            theHeight = (height);
            theWidth = (width/2-4-4/2);
            addDiv(i);
            
          }else if(i%3 == 2){
            theWidth = (width/2-4-4/2);
            var nextHeight = theWidth*img.naturalHeight/img.naturalWidth;
            nextHeight = (nextHeight?nextHeight:theWidth);
            if(nextHeight && nextHeight<height){
              height = nextHeight;
              updateHeight(newDivContainer,height);
              currentRow.css({'height':height+'px'});
            }
                        
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-last APTFTbyTAP-tile-rectangle";
            theHeight = (height);
            addDiv(i);
          }

        });
      }      
      else if( 'floor' == options.style){
        parent.css({'width':'100%'});
        width = parent.width();
        theWidth = (width/options.perRow-4-4/options.perRow);
        theHeight = (width/options.perRow);
          
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if(i%options.perRow == 0){
            newRow(width/options.perRow); 
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-first";            
            addDiv(i);
          }else if(i%options.perRow == (options.perRow -1) ){
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-last";
            addDiv(i);
          }else{    
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile";
            addDiv(i);
          }
        });
      }
      else if( 'wall' == options.style ){
        parent.css({'width':'100%'});
        width = parent.width();
        var imageRow=[],currentImage,sumWidth=0,maxHeight=0;
        theHeight = (width/options.perRow);
        
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];

          currentImage = {
            "width":img.naturalWidth,
            "height":img.naturalHeight,
            "url":url,
            "perm":perm
          } 
          sumWidth += img.naturalWidth;
          imageRow[imageRow.length] = currentImage;  
          
          if(i%options.perRow == (options.perRow -1) || (images.length-1)==i ){
            if( (images.length-1)==i ){
              sumWidth += (options.perRow - i%options.perRow -1)*imageRow[imageRow.length-1].width;
            }
            
            newRow(theHeight);

            var pos = 0;
            s.each(imageRow,function(j){
              var normalWidth = this.width/sumWidth*width;

              url = this.url;
              perm = this.perm;
              theClasses = "APTFTbyTAP-tile";
              theWidth = (normalWidth-4-4/options.perRow);
              addDiv(j);
              
              newDivContainer.css({
                'left':pos+'px'
              });
              
              pos += normalWidth;
            });
          
            imageRow=[];sumWidth=0;
          } 
        });
      }
      else if( 'bookshelf' == options.style ){
        parent.css({'width':'100%'});
        width = parent.width();
        var imageRow=[],currentImage,sumWidth=0,maxHeight=0;
        
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          currentImage = {
            "width":img.naturalWidth,
            "height":img.naturalHeight,
            "url":url,
            "perm":perm
          } 
          sumWidth += img.naturalWidth;
          imageRow[imageRow.length] = currentImage;  
          
          if(i%options.perRow == (options.perRow -1) || (images.length-1)==i ){
            if( (images.length-1)==i ){
              sumWidth += (options.perRow - i%options.perRow -1)*imageRow[imageRow.length-1].width;
            }
            
            newRow(10);
            currentRow.addClass('APTFTbyTAP-bookshelf');
            var pos = 0;
            s.each(imageRow,function(j){
              var normalWidth = this.width/sumWidth*width;
              var normalHeight = normalWidth*this.height/this.width;
              if( normalHeight > maxHeight ){
                maxHeight = normalHeight;
                currentRow.css({'height':normalHeight+"px"});
              }
              
              url = this.url;
              perm = this.perm;
              theClasses = "APTFTbyTAP-book";
              theWidth = (normalWidth-4-4/options.perRow);
              theHeight = normalHeight;
              addDiv(j);
              
              newDivContainer.css({
                'left':pos+'px'
              });
              
              pos += normalWidth;
            });
          
            imageRow=[];sumWidth=0;maxHeight=0;
          } 
        });
      }      
      else if( 'rift' == options.style ){
        parent.css({'width':'100%'});
        width = parent.width();
        var imageRow=[],currentImage,sumWidth=0,maxHeight=0,row=0;
        
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          currentImage = {
            "width":img.naturalWidth,
            "height":img.naturalHeight,
            "url":url,
            "perm":perm
          } 
          sumWidth += img.naturalWidth;
          imageRow[imageRow.length] = currentImage;  
          
          if(i%options.perRow == (options.perRow -1) || (images.length-1)==i ){
            if( (images.length-1)==i ){
              sumWidth += (options.perRow - i%options.perRow -1)*imageRow[imageRow.length-1].width;
            }
            newRow(10);
            currentRow.addClass('APTFTbyTAP-riftline');
            var pos = 0;
            s.each(imageRow,function(j){
              var normalWidth = this.width/sumWidth*width;
              var normalHeight = normalWidth*this.height/this.width;
              if( normalHeight > maxHeight ){
                maxHeight = normalHeight;
                currentRow.css({'height':normalHeight+"px"});
              }
                            
              url = this.url;
              perm = this.url;
              theClasses = 'APTFTbyTAP-rift APTFTbyTAP-float-'+row;
              theWidth = (normalWidth-4-4/options.perRow);
              theHeight = normalHeight;
              addDiv(j);
              
              newDivContainer.css({
                'left':pos+'px'
              });
              
              pos += normalWidth;
            });
          
            imageRow=[];sumWidth=0;maxHeight=0,row=(row?0:1);
          }          
          
        });
      }   
      else if( 'gallery' == options.style ){
        parent.css({'width':'100%','opacity':0});
        width = parent.width();
        var originalImages = s('img.APTFTbyTAP-original-image',parent);
        
        var gallery,galleryContainer,galleryHeight;
        theWidth = (width/options.perRow-4-4/options.perRow);
        theHeight = (width/options.perRow);
             
        s.each(images, function(i){
          img = this;
          src = img.src;
          url = 'url("'+src+'")';
          perm = allPerms[i];
          
          if( 0 == i ){
            galleryHeight = width/options.perRow*options.galleryHeight;
            
            newRow(galleryHeight); 
                 
            galleryContainer = s('<div class="APTFTbyTAP-image-div-container APTFTbyTAP-gallery-container"></div>');
            galleryContainer.css({
              "height":galleryHeight+"px",
              "width":(width-8)+"px",
            });
            
            currentRow.append(galleryContainer);
                             
            if(options.imageBorder){
              galleryContainer.addClass('APTFTbyTAP-border-div');
              galleryContainer.width( galleryContainer.width()-10 );
              galleryContainer.height( galleryContainer.height()-10 );
            }
            if(options.imageShadow){
              galleryContainer.addClass('APTFTbyTAP-shadow-div');
            }
            if(options.imageCurve){
              galleryContainer.addClass('APTFTbyTAP-curve-div');
            }
            if(options.imageHighlight && !options.imageBorder){
              galleryContainer.addClass('APTFTbyTAP-highlight-div');
              galleryContainer.width( galleryContainer.width()-4 );
              galleryContainer.height( galleryContainer.height()-4 );
            }

          }
                    
          if(i%options.perRow == 0){     
            newRow(width/options.perRow); 
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-first";            
            addDiv(i);
          }else if(i%options.perRow == (options.perRow -1) ){           
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile APTFTbyTAP-half-tile-last";            
            addDiv(i);
          }else{
            theClasses = "APTFTbyTAP-tile APTFTbyTAP-half-tile";            
            addDiv(i);
          }
          
          var storeUrl = url;
          if( originalImages[i] ){
            if( originalImages[i].src ){
              storeUrl = 'url("'+originalImages[i].src+'")';
            }
          }

          gallery = s('<div id="'+parent.attr('id')+'-image-'+i+'-gallery" class="APTFTbyTAP-image-div APTFTbyTAP-image-gallery"></div>');   
          gallery.css({
            'background-image':storeUrl,
          });
          if( 0 != i ){
            gallery.hide();
          }
          galleryContainer.append(gallery);
          
        });  

        var allThumbs = s('.APTFTbyTAP-image-div',parent);
        var allGalleries = s('.APTFTbyTAP-image-gallery',parent);
        s.each(allThumbs,function(){
          var theThumb = s(this);
          if( !theThumb.hasClass('APTFTbyTAP-image-gallery') ){
            theThumb.hover(function() {
              allGalleries.hide();
              s("#"+theThumb.attr('id')+"-gallery").show();
            }); 
          }
        });
        
        parent.ready(function(){
          parent.css({'opacity':1});
        });
      }

      function newRow(height){
        currentRow = s('<div class="APTFTbyTAP-row"></div>');
        currentRow.css({'height':height+'px'});
        parent.append(currentRow);
      }
      function addDiv(i){
        newDiv = s('<div id="'+parent.attr('id')+'-image-'+i+'" class="APTFTbyTAP-image-div"></div>');   
        newDiv.css({
          'background-image':url,
        });
            
        newDivContainer = s('<div class="APTFTbyTAP-image-div-container '+theClasses+'"></div>');
        newDivContainer.css({
          "height":theHeight+"px",
          "width":theWidth+"px",
        });
        
        currentRow.append(newDivContainer);
        newDivContainer.append(newDiv);

        if(perm){
          newDiv.wrap('<a href="'+perm.href+'" class="APTFTbyTAP-link" target="_blank"></a>');
        }
        if(options.imageBorder){
          newDivContainer.addClass('APTFTbyTAP-border-div');
          newDivContainer.width( newDivContainer.width()-10 );
          newDivContainer.height( newDivContainer.height()-10 );
        }
        if(options.imageHighlight){
          if(!options.imageBorder){
            newDivContainer.addClass('APTFTbyTAP-highlight-div');
            newDivContainer.width( newDivContainer.width()-4 );
            newDivContainer.height( newDivContainer.height()-4 );
          }
          newDivContainer.hover(function(){
            s(this).css({
              "background": options.highlight,
            });
          },function(){
            s(this).css({
              "background-color": "#fff",
            });
          });
        }
        if(options.imageShadow){
          newDivContainer.addClass('APTFTbyTAP-shadow-div');
        }
        if(options.imageCurve){
          newDivContainer.addClass('APTFTbyTAP-curve-div');
        }
      }
      
      function updateHeight(aDiv,aHeight){
        aDiv.height(aHeight);
        if(options.imageBorder){
          aDiv.height( aDiv.height()-10 );
        }
      }

    });
  }
  
  s.fn.APTFTbyTAPTilesPlugin.options = {
    backgroundClass: 'northbynorth_background',
    parentID: 'parent'
  }    
})( window, jQuery );
  
  
(function( w, s ) {
  s.fn.APTFTbyTAPAdjustBordersPlugin = function( options ) {
    return this.each(function() {  
      var parent = s(this);
      var images = s('img',parent);

      s.each(images,function(){
        var currentImg = s(this);
        var width = currentImg.parent().width();
        
        // Remove and replace ! important classes
        if( currentImg.hasClass('APTFTbyTAP-img-border') ){
          width -= 10;
          currentImg.removeClass('APTFTbyTAP-img-border');
          currentImg.css({
            'max-width':(width)+'px',
            'padding':'4px',
            "margin-left": "1px",
            "margin-right": "1px",
          });
        }else if( currentImg.hasClass('APTFTbyTAP-img-noborder') ){
          currentImg.removeClass('APTFTbyTAP-img-noborder');
          currentImg.css({
            'max-width':(width)+'px',
            'padding':'0px',
          });
        }
        
        if( currentImg.hasClass('APTFTbyTAP-img-shadow') ){
          width -= 2;
          currentImg.removeClass('APTFTbyTAP-img-shadow');
          currentImg.css({
            "box-shadow": "0 1px 3px rgba(34, 25, 25, 0.4)",
            "margin-left": "1px",
            "margin-right": "1px",
            'max-width':(width)+'px',
          });
        }else if( currentImg.hasClass('APTFTbyTAP-img-noshadow') ){
          currentImg.removeClass('APTFTbyTAP-img-noshadow');
          currentImg.css({
            'max-width':(width)+'px',
            "box-shadow":"none",
          });
        }
        
        if( currentImg.hasClass('APTFTbyTAP-img-highlight') ){
          currentImg.removeClass('APTFTbyTAP-img-highlight');
          
          if( '4px' != currentImg.css('padding-right') ){
            width -= 6;
            currentImg.css({
              'max-width':(width)+'px',
              'padding':'2px',
              "margin-left": "1px",
              "margin-right": "1px",
            });
          }

          currentImg.hover(function(){
            console.log(options.highlight);
            s(this).css({
              "background-color": options.highlight,
            });
          },function(){
            s(this).css({
              "background-color": "#fff",
            });
          });
        }
      });
    });
  }
    
})( window, jQuery );