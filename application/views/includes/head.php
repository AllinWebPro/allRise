<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="author" content="<?php echo metadata('author', isset($author)?$author:''); ?>">
  <meta name="description" content="<?php echo metadata('description', isset($description)?$description:''); ?>">
  <meta name="keywords" content="<?php echo metadata('keywords', isset($keywords)?$keywords:''); ?>">
  <meta name="robots" content="<?php echo indexpage($this->uri->segment_array()); ?>, follow">
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport'>
  <meta name="viewport" content="width=device-width">
  <meta name="revisit-after" content="1 day">
  <title><?php echo $title; ?> .::. <?php echo SITE_TITLE; ?></title>
  <link rel="stylesheet" href="//yui.yahooapis.com/pure/0.3.0/pure-min.css">
  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css">
  <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:400,400italic,700,700italic">
  <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css">
  <link rel="stylesheet" href="<?php echo site_url('media/css/main.css'); ?>">
  <!--<script src="<?php echo site_url('media/js/vendor/modernizr.min.js'); ?>"></script>-->
  <!--[if lt IE 7]><script>for (x in open);</script><![endif]-->
</head>