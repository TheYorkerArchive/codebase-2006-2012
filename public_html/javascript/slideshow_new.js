/**
 *	Creates a slideshow of organisation's photos
 *	--------------------------------------------
 *	@author Chris Travis (cdt502 - ctravis@gmail.com)
 *
 *	Pages used:	- /register/societies
 *				- /register/au
 *				- /directory/#org_name#
 *	Requires:	- Scriptaculous.js
 *				- Prototype.js
 *				- div	:id = 'ss'
 *				- img	:id = 'change_me'
 *						:parent = 'ss'
 *				- all slideshow images to have dimensions 220px x 165px
 *	Usage:		<script type='text/javascript' src='/javascript/prototype.js'></script>
 *				<script type='text/javascript' src='/javascript/scriptaculous.js'></script>
 *				<script type='text/javascript' src='/javascript/slideshow.js'></script>
 *				<script type='text/javascript'>
 *				Slideshow.add('##IMAGE_URL_1##');
 *				Slideshow.add('##IMAGE_URL_2##');
 *				... etc ...
 *				Slideshow.load();
 *				</script>
 *				<div id='SlideShow' style='text-align:left;'>
 *					<img id='SlideShowImage' src='/images/prototype/prefs/image_load.jpg' alt='Society Image' title='Society Image' />
 *				</div>
 *
 *	Makes use of a simple image preloader copied from:
 *		http://warpspire.com/journal/interface-scripting/image-preloading-revisited/
 */

var Slideshow = {
  callbacks: [],
  images: [],
  loadedImages: [],
  imagesLoaded: 0,
  showImages: [],
  current: 0,
  timer: 0,
  shownFirst: 0,
  
  add: function(image){
    if (typeof image == 'string') this.images.push(image);
    if (typeof image == 'array' || typeof image == 'object'){
      for (var i=0; i< image.length; i++){
        this.images.push(image[i]);
      }
    }
  },
  onFinish: function(func){
    if (typeof func == 'function') this.callbacks.push(func);
    if (typeof func == 'array' || typeof func == 'object'){
      for (var i=0; i< func.length; i++){
        this.callbacks.push(func[i]);
      }
    }
  },
  load: function(){
    for(var i=0; i<this.images.length; i++){
      this.loadedImages[i] = new Image();
      if (this.loadedImages[i].addEventListener){
        eval("this.loadedImages[i].addEventListener('load', function () { Slideshow.showImages[Slideshow.imagesLoaded] = '" + this.images[i] + "'; Slideshow.imagesLoaded++; }, false);");
      } else if (this.loadedImages[i].attachEvent){
        eval("this.loadedImages[i].attachEvent('onload', function () { Slideshow.showImages[Slideshow.imagesLoaded] = '" + this.images[i] + "'; Slideshow.imagesLoaded++; });");
      } else {
        eval("this.loadedImages[i].onload = this.checkFinished('" + this.images[i] + "');");
      }
      this.loadedImages[i].src = this.images[i];
    }
    timer=setTimeout('Slideshow.nextImage()',1000);
  },
  
  checkFinished: function(imgpath){
    Slideshow.showImages[Slideshow.imagesLoaded] = imgpath;
    Slideshow.imagesLoaded++;
    /*if (this.imagesLoaded == this.images.length) this.fireFinish();*/
  },

  fireFinish: function(){
    for (var i=0; i<this.callbacks.length; i++){
      this.callbacks[i]();
    }
    this.images = [];
    this.loadedImages = [];
    this.imagesLoaded = 0;
    this.callbacks = [];
  },

  nextImage: function(){
    if (this.imagesLoaded > 0) {
	  if ((this.shownFirst == 0) || (this.imagesLoaded > 1)) {
        document.getElementById('SlideShow').style.background = "url('" + this.showImages[this.current] + "')";
        Effect.Shrink('SlideShowImage', {queue: 'end'});
      }
    }
    timer=setTimeout('Slideshow.resetImage()',3000);
  },

  resetImage: function(){
    if (this.imagesLoaded > 0) {
	  if ((this.shownFirst == 0) || (this.imagesLoaded > 1)) {
        Effect.Appear('SlideShowImage', {queue: 'end'});
        document.getElementById('SlideShowImage').src = this.showImages[this.current];
        this.current = (this.current + 1) % this.imagesLoaded
        this.shownFirst = 1;
      }
    }
    timer=setTimeout('Slideshow.nextImage()',2000);
  },

  reset: function(){
    this.callbacks = [];
    this.images = [];
    this.loadedImages = [];
    this.imagesLoaded = 0;
    this.showImages = [];
    this.current = 0;
	this.shownFirst = 0;
    clearTimeout(timer);
	document.getElementById('SlideShowImage').src = '/images/prototype/prefs/image_load.jpg';
	document.getElementById('SlideShow').style.background = "url('/images/prototype/prefs/image_load.jpg')";
	Effect.Appear('SlideShowImage', {queue: 'end'});
  }

}