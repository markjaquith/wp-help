<?php defined( 'ABSPATH' ) or die(); ?>

<style><?php echo $this->inline_file( 'dist/dashboard.css' ); ?></style>
<ul id="cws-wp-help-dashboard-listing">
<?php echo $this->help_topics_html; ?>
</ul>
