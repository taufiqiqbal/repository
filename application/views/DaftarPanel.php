<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="ie=edge">
   <title><?php echo $title;?></title>
   <link rel="icon" type="image/png" href="https://wrappixel.com/demos/admin-templates/material-pro/assets/images/favicon.png" />
   <?php
   echo css_tag([
      'https://fonts.googleapis.com/css?family=Poppins:300,400,500',
      'assets/css/vendor/bootstrap.min.css',
      'assets/css/metisMenu.min.css',
      'assets/css/vendor/switchery-npm/index.min.css',
      'assets/css/vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css',
      'assets/css/icons/line-awesome.min.css',
      'assets/css/icons/dripicons.min.css',
      'assets/css/icons/material-design-iconic-font.min.css',
      'assets/css/main.bundle.min.css',
      'assets/css/layouts/vertical/core/main.min.css',
      'assets/css/layouts/vertical/menu-type/content.min.css',
      'assets/css/layouts/vertical/themes/theme-i.min.css'
   ]);
   ?>
</head>
<body class="content-menu">
   <div id="root"></div>
   <?php
   echo script_tag([
      'assets/vendor/jquery/dist/jquery.min.js',
      'assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js',
      'assets/vendor/js-cookie/src/js.cookie.js',
      'assets/vendor/pace/pace.js',
      'assets/vendor/switchery-npm/index.js'
   ]);
   echo '<script>';
   echo 'var baseUrl = "'.$this->config->item('base_url').'",';
   echo 'indexPage = "'.$this->config->item('index_page').'",';
   echo 'siteUrl = baseUrl+(indexPage !== "" ? indexPage+"/" : "");';
   echo '</script>';
   echo script_tag([
      'assets/daftar/bundle/vendor.bundle.js',
      'assets/daftar/bundle/main.bundle.js'
   ]);
   ?>
</body>
</html>