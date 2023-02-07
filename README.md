# streeview downloader an opensource Google Street View Equirectangular 360 Panoramic images downloader

## What does

streview-dl is a simple PHP script to download 360 panoramic equirectangular images from Google Street View.  

It downloads with the highest resolution/quality/zoom, the result is a 13000x6500 pixels equirectangular image.
It also creates a smaller resize images of 8192x4096 pixels and 1300x650.  

Exif tags about 360º image are added automatically.  

## Dependencies

php, aria2, imagemagick, exiftool  

This script is made to run over Linux.  

## Running

    $ php ./streetview-dl.php <url between single quotes>  


e.g:  

    $ php php ./streeview-dl.php 'https://www.google.com/maps/@41.3881837,2.1698939,3a,75y,143.45h,92.72t/data=!3m6!1e1!3m4!1sr3vUp9U2ss5fwoq1Roxizw!2e0!7i16384!8i8192'  

## Known issues

"montage-im6.q16: cache resources exhausted " can be resolved changing ImageMagick configuration, more info here: https://github.com/ImageMagick/ImageMagick/issues/396  

## Viewers

Android: Ricoh Theta App https://play.google.com/store/apps/details?id=com.theta360&hl=en&gl=US  

PC: Linux/Mac/Windows: Panini https://github.com/lazarus-pkgs/panini  

Web Browser: Chrome and Firefox: three.js (webgl based): https://github.com/pljhonglu/threejs-panorama https://threejs.org/examples/webgl_panorama_equirectangular.html  

## Credits

Created by fdd  
Send feedback and questions to fdd4776s@gmail.com  
Support future improvements of this software https://www.buymeacoffee.com/fdd4s  
All files are public domain https://unlicense.org/  
