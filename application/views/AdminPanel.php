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
   echo @$internalCss;
   echo '<style>';
   echo $this->minify->css('assets/css/custom.css');
   echo '</style>';
   ?>
   <!--[if lt IE 9]>
   <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
   <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body class="fix-header card-no-border">
   <div class="preloader">
      <svg class="circular" viewBox="25 25 50 50">
         <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
      </svg>
   </div>
   <div id="main-wrapper">
      <div id="topbar"></div>
      <div id="sidebar"></div>
      <div class="page-wrapper">
         <div id="root"></div>
         <footer class="footer">
            Dibiayai oleh : Direktorat Riset dan Pengabdian Masyarakat Direktorat Jenderal Penguatan Riset dan Pengembangan
            Kementerian Riset, Teknologi, dan Pendidikan Tinggi Sesuai dengan Kontrak Penelitian Tahun Anggaran 2019
         </footer>
      </div>
   </div>
   <?php
   echo script_tag([
      'assets/plugins/jquery/jquery.min.js',
      'assets/plugins/popper/popper.min.js',
      'assets/plugins/bootstrap/js/bootstrap.min.js',
      'assets/js/jquery.slimscroll.js',
      'assets/js/waves.js',
      'assets/js/sidebarmenu.js',
      'assets/plugins/sticky-kit-master/dist/sticky-kit.min.js',
      'assets/plugins/sparkline/jquery.sparkline.min.js',
      'assets/js/custom.min.js'
   ]);
   echo '<script>';
   echo 'var baseUrl = "'.$this->config->item('base_url').'",';
   echo 'indexPage = "'.$this->config->item('index_page').'",';
   echo 'siteUrl = baseUrl+(indexPage !== "" ? indexPage+"/" : ""),';
   echo 'segment = '.json_encode($this->uri->segment_array()).',';
   echo 'pageType = "'.@$pageType.'",';
   echo 'manifest = '.json_encode([
      'appName' => settings('appName'),
      'appLogo' => base_url('assets/images/' . settings('appLogo'))
   ]).',';
   echo 'user = '.json_encode([
      'avatar' => base_url('assets/images/'.usersLogin('avatar')),
      'fullname' => usersLogin('fullname'),
      'email' => usersLogin('email'),
      'role' => usersLogin('role')
   ]).';';
   echo @$footerJs;
   echo '</script>';
   echo script_tag([
      'assets/js/admin/vendor.bundle.js',
      'assets/js/admin/topbar.bundle.js',
      'assets/js/admin/navigation.bundle.js'
   ]);
   echo @$internalJs;
   ?>
</body>
</html>