<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"> 
<meta name="theme-color" content="#0093d1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<title>Rest Speedtest</title>
<link rel="stylesheet" href="<?php echo $this->config->item('plugins_directory');?>bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $this->config->item('plugins_directory');?>font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo $this->config->item('plugins_directory');?>animate.css/animate.min.css">
<link rel="stylesheet" href="/assets/css/style.css">

<?php if ($this->config->item('analytics_enabled')) : ?>
<!-- Analytics -->
<script type="text/javascript">
  var _paq = _paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['setCustomUrl', 'http://pocketfi-cloud.rmrcom.com']);
  _paq.push(['setDocumentTitle', window.location.pathname]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);

  (function() {
    var u="//analytics.rmrcom.com/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '6']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Analytics Code -->
<?php endif; ?>
</head>
<body class="top-navigation pace-done">
<div id="wrapper">