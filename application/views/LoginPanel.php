<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="icon" type="image/png" href="<?php echo base_url('assets/images/' . settings('appFavicon'));?>">
   <title><?php echo $title;?></title>
   <?php
   echo css_tag([
      'assets/plugins/bootstrap/css/bootstrap.min.css',
      'assets/css/style.css',
      'assets/css/colors/blue.min.css'
   ]);
   ?>
   <!--[if lt IE 9]>
   <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
   <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
   <div class="preloader">
      <svg class="circular" viewBox="25 25 50 50">
         <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
      </svg>
   </div>
   <section id="wrapper"></section>
   <?php
   echo '<script>';
   echo 'var baseUrl = "'.$this->config->item('base_url').'",';
   echo 'indexPage = "'.$this->config->item('index_page').'",';
   echo 'siteUrl = baseUrl+(indexPage !== "" ? indexPage+"/" : "");';
   echo @$footerJs;
   echo '</script>';
   echo script_tag([
      'assets/plugins/jquery/jquery.min.js',
      'assets/plugins/popper/popper.min.js',
      'assets/plugins/bootstrap/js/bootstrap.min.js',
      'assets/js/waves.js',
      'assets/js/login/vendor.bundle.js'
   ]);
   echo @$internalJs;
   ?>
</body>
</html>