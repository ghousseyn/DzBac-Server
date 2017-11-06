<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    
      <meta property="fb:app_id"                content="768513439884976" />
	    <meta property="og:url"                content="<?php echo 'http://squalala.xyz/dz_bac/posts/' . $id ?>" />
	<meta property="og:type"               content="article" />
	<meta property="og:title"              content="<?php echo  $subject . ' - ' . $title ?>" />
	<meta property="og:description"        content=" <?php echo $content; ?>" />
	<meta property="og:image"              content="<?php if (count($url) > 0) echo $url[0]; else echo 'http://squalala.xyz/dz_bac/images/1.png'; ?>" />
	
	
	<meta name="description" content="<?php echo $content; ?>" />
	
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?php echo  $subject . ' - ' . $title ?></title>

    <!-- Bootstrap -->
   <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<link rel="stylesheet" href="https://blueimp.github.io/Gallery/css/blueimp-gallery.min.css">
<link rel="stylesheet" href="https://blueimp.github.io/Bootstrap-Image-Gallery/css/bootstrap-image-gallery.css">



    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">

      body {
        padding-top: 50px;
      }
      .starter-template {
        padding: 40px 15px;
        text-align: center;
      }
      #links {
        padding-top: 50px;
      }

      #image-auteur {
      }


      .navbar-inverse .navbar-nav > li > a, .navbar-inverse > .navbar-header > a {
        color: #FFFFFF;
      }

   </style>
  </head>

  <body>

  <script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.5&appId=768513439884976";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

   <div id="fb-root"></div>


   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>


        <nav class="navbar navbar-inverse navbar-fixed-top">

      <div style=" background-color: #038465; border-color: #000000">

      <div class="container" >

        <div class="navbar-header" >
           <a class="navbar-brand linknavbar" href="https://play.google.com/store/apps/details?id=com.squalala.dzbac" >
              <b style="color: white">DzBac</b>
          </a>
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
         
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a class="linknavbar" href="https://play.google.com/store/apps/details?id=com.squalala.dzbac">About</a></li> 
            <li><a class="linknavbar" href="mailto:team.dzbac@gmail.com?Subject=DzBac-App">Contact</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>

      </div>
    </nav>

  

     <div id="blueimp-gallery" class="blueimp-gallery  blueimp-gallery-controls" data-use-bootstrap-modal="false">
    <!-- The container for the modal slides -->
    <div class="slides"></div>
    <!-- Controls for the borderless lightbox -->
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
    <!-- The modal dialog, which will be used to wrap the lightbox content -->
    <div class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body next"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left prev">
                        <i class="glyphicon glyphicon-chevron-left"></i>
                        Previous
                    </button>
                    <button type="button" class="btn btn-primary next">
                        Next
                        <i class="glyphicon glyphicon-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- The Bootstrap Image Gallery lightbox, should be a child element of the document body -->



 <div class="container">

 <div class="row-fluid">
    <div class="span12">

        <!-- Changed from `hidden` to `auto`. -->


            <img width="80" height="80" id="image-auteur" src="<?php echo $url_avatar ?>" alt="Image de l'auteur" class="img-circle" style="padding: 10px">
  <p style="display:inline-block;" class="blog-post-meta"><?php echo $date_creation ?> by <a href="#"><?php echo $username ?></a></p>

     

    </div>
</div>



 <div id="links">
 
	<?php for($i = 0; $i < count($url); $i++): ?>

		   <a href="<?php echo $url[$i] ?>" title="Image DzBac App" data-gallery>
			<img width="100" height="100" class="img-rounded" src="<?php echo $url[$i] ?>" alt="Image DzBac App">
		    </a>
		<!--<div class="col-xs-6 col-md-3">
			<a href="<?php echo '/images/annonce/'.$id_annonce_encoded . '/' .
			$posts[0]['url_images'][$i]; ?> " title="<?php echo $posts[0]['url_images'][$i]; ?>" data-gallery>
	
				<img class="img-thumbnail" src="<?php echo '/images/annonce/'. $id_annonce_encoded . '/thumbnail/' .
			$posts[0]['url_images'][$i]; ?> " title="<?php echo $posts[0]['url_images'][$i]; ?>" alt="<?php echo $posts[0]['url_images'][$i]; ?>">
			</a>
		</div>-->


	<?php endfor; ?>
   
   
</div>


      <div class="starter-template">
        <h1><?php echo  $subject . ' - ' . $title ?></h1>
        
        <?php 
       
        
        if ($hide) echo '<!--'; 
         ?>
        
          <button type="button" class="btn btn-default btn-sm">
          <a href="<?php echo 'http://squalala.xyz/dz_bac/uploads/' . $filename ?>">
          <span class="glyphicon glyphicon-download-alt"></span> Télécharger
          </a>
         </button>
         
         <?php if ($hide) echo '-->' ?>


        
        <?php echo html_entity_decode($content);
        /*htmlentities($content, ENT_QUOTES | ENT_IGNORE, "UTF-8"); */?>
        
        </p>
        
           <?php 
       
        
        if ($hide_audio) echo '<!--'; 
         ?>

          <audio controls> 
            <source src="<?php echo 'http://squalala.xyz/dz_bac/audio/' . $audio ?>" /> 
          </audio>
        
         
         <?php if ($hide_audio) echo '-->'; ?>
      </div>


      <div class="col-md-6 center-block fb-page" data-href="https://www.facebook.com/dzbac.app.mobile/" data-tabs="timeline" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/dzbac.app.mobile/"><a href="https://www.facebook.com/dzbac.app.mobile/">DzBac</a></blockquote></div></div>


    </div><!-- /.container -->


  


<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="https://blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
<script src="https://blueimp.github.io/Bootstrap-Image-Gallery/js/bootstrap-image-gallery.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-58949456-2', 'auto');
  ga('send', 'pageview');

</script>


  </body>
</html>
