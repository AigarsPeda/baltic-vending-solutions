<?php defined('ABSPATH') || exit; ?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php echo esc_html(bvs_ui('skip')); ?></a>
<header class="site-header"><div class="header-inner">
<div class="site-identity"><?php if (has_custom_logo()) { the_custom_logo(); } else { ?><a href="<?php echo esc_url(function_exists('pll_home_url') ? pll_home_url() : home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a><?php } ?></div>
<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-haspopup="dialog" hidden><?php echo esc_html(bvs_ui('menu')); ?><svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
<nav id="site-navigation" class="site-navigation" aria-label="<?php echo esc_attr(bvs_ui('menu')); ?>"><?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'fallback_cb' => false, 'depth' => 1]); ?>
<div class="header-actions"><?php dynamic_sidebar('bvs-header-' . bvs_language()); ?></div></nav>
</div></header>
<dialog id="mobile-menu" class="navigation-drawer" aria-labelledby="mobile-menu-title">
<div class="drawer-header"><span id="mobile-menu-title"><?php echo esc_html(bvs_ui('menu')); ?></span><button class="drawer-close" type="button" aria-label="<?php echo esc_attr(bvs_language() === 'lv' ? 'Aizvērt izvēlni' : 'Close menu'); ?>" autofocus><svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M6 18 18 6"/></svg></button></div>
</dialog>
<main id="main" tabindex="-1">
