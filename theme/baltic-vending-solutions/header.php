<?php defined('ABSPATH') || exit; ?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php echo esc_html(bvs_ui('skip')); ?></a>
<header class="site-header"><div class="header-inner">
<div class="site-identity"><?php if (has_custom_logo()) { the_custom_logo(); } else { ?><a href="<?php echo esc_url(function_exists('pll_home_url') ? pll_home_url() : home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a><?php } ?></div>
<button class="menu-toggle" aria-expanded="false" aria-controls="site-navigation" hidden><?php echo esc_html(bvs_ui('menu')); ?><span aria-hidden="true">☰</span></button>
<nav id="site-navigation" class="site-navigation" aria-label="<?php echo esc_attr(bvs_ui('menu')); ?>"><?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'fallback_cb' => false, 'depth' => 1]); ?>
<div class="header-actions"><?php dynamic_sidebar('bvs-header-' . bvs_language()); ?></div></nav>
</div></header>
<main id="main" tabindex="-1">
